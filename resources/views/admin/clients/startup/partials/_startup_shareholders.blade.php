<div class="col-md-4">
  <div class="card shadow-sm" style="height: 300px; overflow-y: hidden;">
  <div class="py-4 px-8" style="height: 300px;">
      <h5 class="card-title mb-4">{{ __('admin.shareholders') }} ( {{count($Shareholders)}} )</h5>
      @if (count($Shareholders) > 0)
      <div class="row" style="position: relative; height: 90%;">
        @if (count($Shareholders) > 2)
          <button id="founder-prev" class="btn btn-sm btn-icon btn-white rounded-circle shadow border d-none" style="position: absolute; left: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
            <i class="ti ti-chevron-left"></i>
          </button>            
          <button id="founder-next" class="btn btn-sm btn-icon btn-white rounded-circle shadow border" style="position: absolute; right: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
            <i class="ti ti-chevron-right"></i>
          </button>
        @endif

        @foreach ($Shareholders as $Shareholder)
        <div id="founder-col" class="col-12 mb-4">
          <div class="p-3 shadow-sm border">
            <div class="d-flex align-items-center justify-content-between">
              <div class="mx-3 d-flex flex-column justify-content-center gap-1">
                <div class="text-black fs-6">
                  {{ $Shareholder->Name }}</div>
                <button class="btn btn-outline-primary btn-xs rounded-pill mt-1 text-nowrap text-xs"
                  style="height: 22px; width: fit-content">
                  {{ $Shareholder->RoleShereholder }}
                </button>
              </div>
              <div class="mx-3 d-flex align-items-center gap-3" style="height: 76px;">
                <i class="ti ti-chevrons-right ti-28px;"></i>
                <x-circular-progress size="54" color="#7367f0" progress="{{ $Shareholder->PresentagShereholder }}" />
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="d-flex flex-column">
        <div class="text-center">
          <img src="/icons/no-shareholders.svg" class="img-fluid" alt="empty" width="200" height="200" />
          <h6 class="text-muted">{{ __('admin.No Shareholders') }}</h6>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const founderCols = document.querySelectorAll('#founder-col');
    const founderPrevBtn = document.querySelector('#founder-prev');
    const founderNextBtn = document.querySelector('#founder-next');

    // Only proceed if there are shareholders
    if (founderCols.length === 0) {
      return;
    }

    // Only set up pagination if navigation buttons exist (more than 2 shareholders)
    if (!founderPrevBtn || !founderNextBtn) {
      // Show all shareholders when there are 2 or fewer
      founderCols.forEach((col) => {
        col.style.display = 'block';
      });
      return;
    }

    let currentIndex = 0;
    const itemsPerPage = 2; // Number of items to display at a time

    function updateVisibility() {
      founderCols.forEach((col, index) => {
        col.style.display = (index >= currentIndex && index < currentIndex + itemsPerPage) ? 'block' : 'none';
      });

      // Hide prev button if at the start
      if (founderPrevBtn) {
        founderPrevBtn.classList.toggle('d-none', currentIndex === 0);
      }

      // Hide next button if at the last set
      if (founderNextBtn) {
        founderNextBtn.classList.toggle('d-none', currentIndex + itemsPerPage >= founderCols.length);
      }
    }

    // Add event listeners with additional null checks
    if (founderPrevBtn) {
      founderPrevBtn.addEventListener('click', () => {
        if (currentIndex > 0) {
          currentIndex -= itemsPerPage;
          updateVisibility();
        }
      });
    }

    if (founderNextBtn) {
      founderNextBtn.addEventListener('click', () => {
        if (currentIndex + itemsPerPage < founderCols.length) {
          currentIndex += itemsPerPage;
          updateVisibility();
        }
      });
    }

    updateVisibility(); // Initial check on page load
  });
</script>