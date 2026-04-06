<div class="col-md-4">
  <div class="card shadow-sm" style="height: 320px; overflow-y: hidden;">
    <div class="py-4 px-8" style="height: 300px;">
      <h5 class="card-title mb-2">{{ __('admin.shareholders') }} ( {{count($Shareholders)}} )</h5>
      @if (count($Shareholders) > 0)
      <div class="row" style="position: relative; height: 90%;">
        @if (count($Shareholders) > 3)
          <button id="shareholder-prev" class="btn btn-sm btn-icon btn-white rounded-circle shadow border d-none" style="position: absolute; left: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
            <i class="ti ti-chevron-left"></i>
          </button>            
          <button id="shareholder-next" class="btn btn-sm btn-icon btn-white rounded-circle shadow border" style="position: absolute; right: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
            <i class="ti ti-chevron-right"></i>
          </button>
        @endif

        @foreach ($Shareholders as $Shareholder)
        <div id="shareholder-col" class="col-12 mb-2">
          <div class="card item-card shadow-none border mb-0">
            <div class="card-body py-2 px-3 d-flex align-items-center">
              <div class="avatar">
                <div class="avatar-initial text-dark rounded flex-shrink-0">
                  <i class="ti ti-user-dollar"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="mb-0">{{ $Shareholder->Name }}</h6>
                <small class="text-secondary d-block">{{ $Shareholder->RoleShereholder ?? 'Shareholder' }}</small>
                <div class="d-flex align-items-center gap-2 mt-1">
                  <div class="progress flex-grow-1" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar"
                      style="width: {{ $Shareholder->PresentagShereholder }}%"
                      aria-valuenow="{{ $Shareholder->PresentagShereholder }}"
                      aria-valuemin="0" aria-valuemax="100">
                    </div>
                  </div>
                  <small class="text-dark fw-semibold text-nowrap">{{ $Shareholder->PresentagShereholder }}%</small>
                </div>
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

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const shareholderCols = document.querySelectorAll('#shareholder-col');
    const shareholderPrevBtn = document.querySelector('#shareholder-prev');
    const shareholderNextBtn = document.querySelector('#shareholder-next');

    // Only proceed if there are shareholders
    if (shareholderCols.length === 0) {
      return;
    }

    // Only set up pagination if navigation buttons exist (more than 2 shareholders)
    if (!shareholderPrevBtn || !shareholderNextBtn) {
      // Show all shareholders when there are 2 or fewer
      shareholderCols.forEach((col) => {
        col.style.display = 'block';
      });
      return;
    }

    let currentIndex = 0;
    const itemsPerPage = 3; // Number of items to display at a time

    function updateVisibility() {
      shareholderCols.forEach((col, index) => {
        col.style.display = (index >= currentIndex && index < currentIndex + itemsPerPage) ? 'block' : 'none';
      });

      // Hide prev button if at the start
      if (shareholderPrevBtn) {
        shareholderPrevBtn.classList.toggle('d-none', currentIndex === 0);
      }

      // Hide next button if at the last set
      if (shareholderNextBtn) {
        shareholderNextBtn.classList.toggle('d-none', currentIndex + itemsPerPage >= shareholderCols.length);
      }
    }

    // Add event listeners with additional null checks
    if (shareholderPrevBtn) {
      shareholderPrevBtn.addEventListener('click', () => {
        if (currentIndex > 0) {
          currentIndex -= itemsPerPage;
          updateVisibility();
        }
      });
    }

    if (shareholderNextBtn) {
      shareholderNextBtn.addEventListener('click', () => {
        if (currentIndex + itemsPerPage < shareholderCols.length) {
          currentIndex += itemsPerPage;
          updateVisibility();
        }
      });
    }

    updateVisibility(); // Initial check on page load
  });
</script>
@endpush