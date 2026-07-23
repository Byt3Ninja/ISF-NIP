"use strict";

document.addEventListener("DOMContentLoaded", function () {
  // console.log("Form Page Close - Initialized");

  const forms = document.querySelectorAll("form");

  if (!forms.length) {
    // console.log("No forms found");
    return;
  }

  let allowNavigation = false;
  let isDirty = false;
  let initialValues = new Map();
  let select2InitComplete = false;

  /*
  ---------------------------------
  Initialize tracking for fields
  ---------------------------------
  */

  function initializeFieldTracking() {
    // console.log("Initializing field tracking...");
    initialValues.clear(); // Clear previous values
    
    forms.forEach(form => {
      const fields = form.querySelectorAll("input, textarea, select");
      // console.log(`Found ${fields.length} fields in form`);
      
      fields.forEach(field => {
        if (field.name && field.name !== "_token") {
          // Check if it's a Select2 field
          if ($(field).data('select2') || $(field).hasClass('select2-hidden-accessible')) {
            const val = $(field).val();
            initialValues.set(field, val);
            // console.log(`✓ Select2 field: ${field.name}, initial value:`, val);
          } else {
            initialValues.set(field, field.value);
            // console.log(`✓ Regular field: ${field.name}, initial value:`, field.value);
          }
        }
      });
    });

    select2InitComplete = true;
    // console.log("Field tracking initialized with", initialValues.size, "fields");
  }

  /*
  ---------------------------------
  Recalculate dirty state
  ---------------------------------
  */

  function recalcDirtyState() {
    if (!select2InitComplete) {
      // console.log("Skipping dirty check - Select2 not ready");
      return;
    }

    isDirty = false;

    initialValues.forEach((initialValue, field) => {
      let currentValue;

      // Check if it's a Select2 field
      if ($(field).data('select2') || $(field).hasClass('select2-hidden-accessible')) {
        currentValue = $(field).val();
        
        // Handle multiple select comparison
        if (Array.isArray(currentValue) || Array.isArray(initialValue)) {
          // Convert to arrays if needed
          const current = Array.isArray(currentValue) ? currentValue : (currentValue ? [currentValue] : []);
          const initial = Array.isArray(initialValue) ? initialValue : (initialValue ? [initialValue] : []);
          
          // Compare arrays
          if (current.length !== initial.length || 
              !current.every((val, index) => val === initial[index])) {
            isDirty = true;
            // console.log(`✗ Select2 field ${field.name} is dirty:`, {initial, current});
          }
        } else {
          // Single select comparison (including null/empty)
          const current = currentValue === null ? "" : String(currentValue);
          const initial = initialValue === null ? "" : String(initialValue);
          
          if (current !== initial) {
            isDirty = true;
            // console.log(`✗ Select2 field ${field.name} is dirty:`, {initial: initialValue, current: currentValue});
          }
        }
      } else {
        // Regular field comparison
        if (field.value !== initialValue) {
          isDirty = true;
          // console.log(`✗ Field ${field.name} is dirty:`, {initial: initialValue, current: field.value});
        }
      }
    });

    // console.log("Dirty state:", isDirty);
  }

  /*
  ---------------------------------
  Attach event listeners to fields
  ---------------------------------
  */

  function attachFieldListeners() {
    // console.log("Attaching field listeners...");
    
    document.querySelectorAll("input, textarea, select").forEach(field => {
      // Check if it's a Select2 field
      const isSelect2 = $(field).data('select2') || $(field).hasClass('select2-hidden-accessible');
      
      if (isSelect2) {
        // console.log(`Attaching Select2 events to ${field.name}`);
        
        // Remove any existing listeners first
        $(field).off('select2:select.dirtycheck select2:unselect.dirtycheck select2:clear.dirtycheck change.dirtycheck');
        
        // Attach Select2 events with namespace
        $(field).on('select2:select.dirtycheck select2:unselect.dirtycheck select2:clear.dirtycheck', function(e) {
          // console.log(`Select2 event: ${e.type} on ${field.name}`);
          recalcDirtyState();
          activateHistoryGuard();
        });

        // Also listen to regular change event as fallback
        $(field).on('change.dirtycheck', function(e) {
          // console.log(`Change event on Select2 ${field.name}`);
          recalcDirtyState();
          activateHistoryGuard();
        });
      } else {
        // Regular fields
        field.addEventListener("input", function() {
          // console.log(`Input event on ${field.name}`);
          recalcDirtyState();
          activateHistoryGuard();
        });
        
        field.addEventListener("change", function() {
          // console.log(`Change event on ${field.name}`);
          recalcDirtyState();
          activateHistoryGuard();
        });
      }
    });
  }

  /*
  ---------------------------------
  Wait for Select2 to initialize
  ---------------------------------
  */

  function waitForSelect2() {
    // console.log("Waiting for Select2 initialization...");
    
    // Check if any select has Select2 initialized
    const checkSelect2Ready = setInterval(function() {
      const select2Fields = document.querySelectorAll('select.select2, select[data-select2-id]');
      const hasSelect2 = Array.from(select2Fields).some(field => 
        $(field).data('select2') || $(field).hasClass('select2-hidden-accessible')
      );

      if (hasSelect2 || document.querySelectorAll('.select2-container').length > 0) {
        // console.log("✓ Select2 detected, initializing tracking...");
        clearInterval(checkSelect2Ready);
        
        // Wait a bit more to ensure all Select2 instances are ready
        setTimeout(function() {
          initializeFieldTracking();
          attachFieldListeners();
        }, 300);
      }
    }, 100);

    // Fallback: if no Select2 after 5 seconds, initialize anyway
    setTimeout(function() {
      if (!select2InitComplete) {
        // console.log("⚠ Timeout waiting for Select2, initializing anyway...");
        clearInterval(checkSelect2Ready);
        initializeFieldTracking();
        attachFieldListeners();
      }
    }, 5000);
  }

  // Start waiting for Select2
  waitForSelect2();

  /*
  ---------------------------------
  Show confirmation dialog
  ---------------------------------
  */

  function showConfirmation(callback) {
    Swal.fire({
      title: document.documentElement.lang === "ar" ? "تغييرات غير محفوظة" : "Unsaved Changes",
      text: document.documentElement.lang === "ar" ? "لديك تغييرات غير محفوظة. هل أنت متأكد من رغبتك في المغادرة؟" : "You have unsaved changes. Are you sure you want to leave?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: document.documentElement.lang === "ar" ? "مغادرة" : "Leave Anyway",
      cancelButtonText: document.documentElement.lang === "ar" ? "البقاء" : "Stay",
      customClass: {
        confirmButton: 'btn btn-primary ms-3',
        cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
      reverseButtons: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
    }).then((result) => {
      if (result.isConfirmed) {
        allowNavigation = true;
        callback();
      }
    });
  }

  /*
  ---------------------------------
  Check if element should be ignored
  ---------------------------------
  */

  function shouldIgnoreDirtyCheck(element) {
    if (element && element.hasAttribute && element.hasAttribute("data-ignore-dirty")) {
      return true;
    }
    
    if (element && element.closest) {
      return element.closest("[data-ignore-dirty]") !== null;
    }
    
    return false;
  }

  /*
  ---------------------------------
  Check if button should allow navigation
  ---------------------------------
  */

  function isNavigationButton(button) {
    // Ignore Select2 internal buttons
    if (button.closest('.select2-container') || 
        button.closest('.select2-dropdown')) {
      // console.log("Button is Select2 internal - ignoring");
      return false;
    }

    // Ignore buttons inside SweetAlert
    if (button.closest('.swal2-container')) {
      // console.log("Button is SweetAlert internal - ignoring");
      return false;
    }

    // Check if button has data-ignore-dirty
    if (shouldIgnoreDirtyCheck(button)) {
      // console.log("Button has data-ignore-dirty");
      return true;
    }

    // Allow all buttons inside forms (they should be able to submit/navigate without warning)
    if (button.closest('form')) {
      // console.log("Button is inside form - allowing");
      return true;
    }

    // Only allow navigation for buttons that are:
    // 1. Submit buttons in forms (handled above)
    // 2. Buttons with explicit navigation attributes
    // 3. Buttons with data-ignore-dirty
    const hasNavigationIntent = 
      button.hasAttribute('data-href') ||
      button.hasAttribute('data-url') ||
      button.getAttribute('onclick')?.includes('location') ||
      button.getAttribute('onclick')?.includes('redirect') ||
      button.hasAttribute('data-ignore-dirty');

    if (hasNavigationIntent) {
      // console.log("Button has navigation intent");
    }

    return hasNavigationIntent;
  }

  /*
  ---------------------------------
  Browser close / refresh
  ---------------------------------
  */

  window.addEventListener("beforeunload", function (e) {
    if (allowNavigation || !isDirty) return;
    // console.log("beforeunload - preventing");
    e.preventDefault();
    e.returnValue = "";
  });

  /*
  ---------------------------------
  Intercept browser back/forward buttons
  ---------------------------------
  */

  let historyGuardActive = false;
  let isNavigatingBack = false;

  function activateHistoryGuard() {
    if (!historyGuardActive && isDirty) {
      history.pushState(null, "", location.href);
      historyGuardActive = true;
      // console.log("History guard activated");
    }
  }

  window.addEventListener("popstate", function (e) {
    // console.log("popstate event", {isDirty, allowNavigation, isNavigatingBack});
    
    if (isNavigatingBack) {
      return;
    }

    if (!isDirty || allowNavigation) {
      historyGuardActive = false;
      return;
    }

    history.pushState(null, "", location.href);

    showConfirmation(() => {
      historyGuardActive = false;
      isNavigatingBack = true;
      
      window.removeEventListener("popstate", arguments.callee);
      
      history.back();
      
      setTimeout(() => {
        isNavigatingBack = false;
      }, 100);
    });
  });

  /*
  ---------------------------------
  Disable dirty check on specific button clicks
  ---------------------------------
  */

  document.addEventListener("click", function(e) {
    const button = e.target.closest("button, [role='button'], input[type='button'], input[type='submit']");
    
    if (button) {
      // console.log("Button clicked:", button);
      if (isNavigationButton(button)) {
        // console.log("Allowing navigation for button");
        allowNavigation = true;
        return;
      }
    }

    if (!button && shouldIgnoreDirtyCheck(e.target)) {
      // console.log("Element has data-ignore-dirty");
      allowNavigation = true;
    }
  }, true);

  /*
  ---------------------------------
  Intercept all links
  ---------------------------------
  */

  document.addEventListener("click", function(e) {
    const link = e.target.closest("a");
    
    if (!link) return;

    // console.log("Link clicked:", link.href);

    if (shouldIgnoreDirtyCheck(link)) {
      // console.log("Link has data-ignore-dirty");
      allowNavigation = true;
      return;
    }

    const href = link.getAttribute("href");

    if (!href || 
        href.startsWith("#") || 
        href.startsWith("javascript:") ||
        link.target === "_blank") {
      // console.log("Link allowed (hash/javascript/blank)");
      return;
    }

    if (!isDirty || allowNavigation) {
      // console.log("Link allowed (not dirty or navigation allowed)");
      return;
    }

    // console.log("Link blocked - showing confirmation");
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    showConfirmation(() => {
      window.location.href = href;
    });
  }, true);

  /*
  ---------------------------------
  Disable guard when submitting forms
  ---------------------------------
  */

  forms.forEach(form => {
    form.addEventListener("submit", function (e) {
      // console.log("Form submitted - allowing navigation");
      allowNavigation = true;
    });

    // Also handle buttons inside forms explicitly
    form.querySelectorAll("button, input[type='submit'], input[type='button']").forEach(button => {
      button.addEventListener("click", function(e) {
        // console.log("Form button clicked - allowing navigation");
        allowNavigation = true;
      });
    });
  });

});