<style>
.btn-linkedin { background-color: #0A66C2; border-color: #0A66C2; color: #fff; }
.btn-linkedin:hover { background-color: #004182; border-color: #004182; color: #fff; }
.btn-twitter { background-color: #000000; border-color: #000000; color: #fff; }
.btn-twitter:hover { background-color: #333333; border-color: #333333; color: #fff; }
.btn-facebook { background-color: #1877F2; border-color: #1877F2; color: #fff; }
.btn-facebook:hover { background-color: #0d65d9; border-color: #0d65d9; color: #fff; }
.share-buttons .btn { min-width: 44px; }
.share-buttons-card .btn { min-width: 32px; padding: 0.25rem 0.5rem; }
@media (max-width: 576px) {
  .share-buttons .btn span { display: none !important; }
  .share-buttons .btn i { margin: 0 !important; }
}
</style>

<script>
$(document).ready(function() {
  var winners = @json( $winners );
  var hackathonId = {{ $currentHackathon['cycle_id'] ?? 'null' }};
  var baseUrl = '{{ url("/") }}';
  var isPublic = {{ (isset($currentHackathon['visibility']) && $currentHackathon['visibility'] === 'public') ? 'true' : 'false' }};
  var csrfToken = $('meta[name="csrf-token"]').attr('content');
  var currentSolutionId = null;

  // Translation strings
  var translations = {
    link_copied: '{{ $l == "en" ? "Link copied to clipboard!" : "تم نسخ الرابط!" }}',
    copy_failed: '{{ $l == "en" ? "Failed to copy link" : "فشل نسخ الرابط" }}',
    share_linkedin: '{{ $l == "en" ? "Share on LinkedIn" : "شارك على لينكدان" }}',
    share_twitter: '{{ $l == "en" ? "Share on X" : "شارك على X" }}',
    share_facebook: '{{ $l == "en" ? "Share on Facebook" : "شارك على فيسبوك" }}',
    copy_link: '{{ $l == "en" ? "Copy Link" : "نسخ الرابط" }}'
  };

  // Open modal and generate share buttons
  $(document).on('click', '.open-details-modal', function(e) {
    e.preventDefault();
    let id = $(this).data('id');
    let winner = winners.find(winner => winner.id == id);

    if (typeof winner !== 'undefined') {
      $("#td-team").html(winner.teamName);
      $("#td-project").html(winner.projectTitle);
      $("#td-description").html(winner.description);

      currentSolutionId = id;

      // Generate share buttons if public
      if (isPublic && hackathonId) {
        generateModalShareButtons(winner, hackathonId);
      }

      $("#solutionDetailsModal").modal('show');
    }
  });

  // Generate share buttons in modal
  function generateModalShareButtons(winner, hackathonId) {
    var shareUrl = baseUrl + '/hackathon/' + hackathonId + '/solution/' + winner.id;
    var title = winner.projectTitle + ' - ' + winner.teamName;

    var encodedUrl = encodeURIComponent(shareUrl);
    var encodedTitle = encodeURIComponent(title);

    var linkedinUrl = 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodedUrl;
    var twitterUrl = 'https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodedTitle + '&hashtags=hackathon,innovation';
    var facebookUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl;

    var html = `
      <button type="button" class="btn btn-linkedin modal-share-btn" data-share-url="${linkedinUrl}" data-platform="linkedin" title="${translations.share_linkedin}">
        <i class="ti ti-brand-linkedin ti-md"></i>
        <span class="d-none d-sm-inline ms-1">LinkedIn</span>
      </button>
      <button type="button" class="btn btn-twitter modal-share-btn" data-share-url="${twitterUrl}" data-platform="twitter" title="${translations.share_twitter}">
        <i class="ti ti-brand-twitter ti-md"></i>
        <span class="d-none d-sm-inline ms-1">X</span>
      </button>
      <button type="button" class="btn btn-facebook modal-share-btn" data-share-url="${facebookUrl}" data-platform="facebook" title="${translations.share_facebook}">
        <i class="ti ti-brand-facebook ti-md"></i>
        <span class="d-none d-sm-inline ms-1">Facebook</span>
      </button>
      <button type="button" class="btn btn-secondary modal-copy-link-btn" data-copy-url="${shareUrl}" title="${translations.copy_link}">
        <i class="ti ti-link ti-md"></i>
        <span class="d-none d-sm-inline ms-1">${translations.copy_link}</span>
      </button>
    `;

    $('#modal-share-buttons').html(html);
  }

  // Modal share button click
  $(document).on('click', '.modal-share-btn', function(e) {
    e.preventDefault();
    var shareUrl = $(this).data('share-url');
    var platform = $(this).data('platform');

    trackShare(currentSolutionId, platform);
    openSharePopup(shareUrl);
  });

  // Modal copy link button click
  $(document).on('click', '.modal-copy-link-btn', function(e) {
    e.preventDefault();
    var url = $(this).data('copy-url');
    var btn = $(this);

    copyToClipboard(url, btn, currentSolutionId);
  });

  // Card share button click
  $(document).on('click', '.card-share-btn', function(e) {
    e.preventDefault();
    var winnerId = $(this).data('winner-id');
    var platform = $(this).data('platform');
    var winner = winners.find(w => w.id == winnerId);

    if (winner && hackathonId) {
      var shareUrl = baseUrl + '/hackathon/' + hackathonId + '/solution/' + winner.id;
      var title = winner.projectTitle + ' - ' + winner.teamName;

      var encodedUrl = encodeURIComponent(shareUrl);
      var encodedTitle = encodeURIComponent(title);

      var platformUrl;
      switch(platform) {
        case 'linkedin':
          platformUrl = 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodedUrl;
          break;
        case 'twitter':
          platformUrl = 'https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodedTitle + '&hashtags=hackathon,innovation';
          break;
        case 'facebook':
          platformUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl;
          break;
      }

      trackShare(winnerId, platform);
      openSharePopup(platformUrl);
    }
  });

  // Card copy link button click
  $(document).on('click', '.card-copy-link-btn', function(e) {
    e.preventDefault();
    var winnerId = $(this).data('winner-id');
    var btn = $(this);

    if (hackathonId) {
      var shareUrl = baseUrl + '/hackathon/' + hackathonId + '/solution/' + winnerId;
      copyToClipboard(shareUrl, btn, winnerId);
    }
  });

  // Open share popup window
  function openSharePopup(url) {
    var width = 600, height = 400;
    var left = (screen.width - width) / 2;
    var top = (screen.height - height) / 2;
    window.open(url, 'share_popup', 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',toolbar=no,menubar=no,scrollbars=yes');
  }

  // Copy to clipboard
  function copyToClipboard(url, btn, solutionId) {
    // Check if clipboard API is available (requires HTTPS or localhost)
    if (typeof navigator !== 'undefined' && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
      try {
        navigator.clipboard.writeText(url).then(function() {
          trackShare(solutionId, 'copy_link');
          showCopySuccess(btn);
        }).catch(function() {
          fallbackCopy(url, btn, solutionId);
        });
      } catch (e) {
        fallbackCopy(url, btn, solutionId);
      }
    } else {
      fallbackCopy(url, btn, solutionId);
    }
  }

  // Fallback copy method
  function fallbackCopy(url, btn, solutionId) {
    var textArea = document.createElement('textarea');
    textArea.value = url;
    textArea.style.position = 'fixed';
    textArea.style.left = '-9999px';
    document.body.appendChild(textArea);
    textArea.select();

    try {
      document.execCommand('copy');
      trackShare(solutionId, 'copy_link');
      showCopySuccess(btn);
    } catch (err) {
      if (typeof toastr !== 'undefined') {
        toastr.error(translations.copy_failed);
      } else {
        alert(translations.copy_failed);
      }
    }

    document.body.removeChild(textArea);
  }

  // Show copy success feedback
  function showCopySuccess(btn) {
    if (typeof toastr !== 'undefined') {
      toastr.success(translations.link_copied);
    } else {
      alert(translations.link_copied);
    }

    // Visual feedback on button
    var icon = btn.find('i');
    var originalClass = icon.attr('class');
    icon.attr('class', 'ti ti-check ti-md');
    setTimeout(function() {
      icon.attr('class', originalClass);
    }, 2000);
  }

  // Track share event
  function trackShare(solutionId, platform) {
    $.ajax({
      url: '{{ route("hackathon.trackShare") }}',
      method: 'POST',
      data: {
        _token: csrfToken,
        solution_id: solutionId,
        platform: platform
      },
      error: function(err) {
        console.error('Failed to track share:', err);
      }
    });
  }
});
</script>
