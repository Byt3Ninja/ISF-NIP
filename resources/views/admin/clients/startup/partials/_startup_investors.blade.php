<div class="col-md-4">
  <div class="card shadow-sm" style="height: 320px; overflow-y: hidden;">
    <div class="py-4 px-8">
      <h5 class="card-title mb-2">{{ __('admin.investors') }} ( {{count($Investors)}} )</h5>
      @if (count($Investors) > 0)
      <div class="row" style="position: relative; height: 90%;">
        @if (count($Investors) > 4)
        <button id="investor-prev" class="btn btn-sm btn-icon btn-white rounded-circle shadow border d-none"
          style="position: absolute; left: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
          <i class="ti ti-chevron-left"></i>
        </button>
        <button id="investor-next" class="btn btn-sm btn-icon btn-white rounded-circle shadow border"
          style="position: absolute; right: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
          <i class="ti ti-chevron-right"></i>
        </button>
        @endif

        @foreach ($Investors as $Investor)
        <div class="investor-col col-12 mb-1">
          <div class="card item-card shadow-none border mb-0">
            <div class="card-body py-2 px-3 d-flex align-items-center">
              <div class="avatar">
                <div class="avatar-initial text-dark rounded flex-shrink-0">
                  <i class="ti ti-building-bank"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="mb-0">{{ $Investor->Name }}</h6>
                @if ($Investor->investorType)
                <small class="text-secondary d-block">{{ __('admin.' . ($Investor->investorType ?? 'Standard')) }}</small>
                @endif
                {{-- <small class="text-success fw-bold">{{ $Investor->investment_amount ?? '' }}</small> --}}
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="d-flex flex-column">
        <div class="text-center">
          <img src="/icons/no-investors.svg" class="img-fluid" alt="empty" width="200" height="200" />
          <h6 class="text-muted">{{ __('admin.No Investors') }}</h6>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const investorCols = document.querySelectorAll('.investor-col');
    const investorPrevBtn = document.querySelector('#investor-prev');
    const investorNextBtn = document.querySelector('#investor-next');

    // Only proceed if there are investors
    if (investorCols.length === 0) {
      return;
    }

    // Only set up pagination if navigation buttons exist (more than 2 investors)
    if (!investorPrevBtn || !investorNextBtn) {
      // Show all investors when there are 2 or fewer
      investorCols.forEach((col) => {
        col.style.display = 'block';
      });
      return;
    }

    let currentIndex = 0;
    const itemsPerPage = 4; // Number of items to show at a time

    function updateVisibility() {
      investorCols.forEach((col, index) => {
        col.style.display = (index >= currentIndex && index < currentIndex + itemsPerPage) ? 'block' : 'none';
      });

      if (investorPrevBtn) {
        investorPrevBtn.classList.toggle('d-none', currentIndex === 0);
      }
      if (investorNextBtn) {
        investorNextBtn.classList.toggle('d-none', currentIndex + itemsPerPage >= investorCols.length);
      }
    }

    // Add event listeners with additional null checks
    if (investorPrevBtn) {
      investorPrevBtn.addEventListener('click', () => {
        if (currentIndex > 0) {
          currentIndex -= itemsPerPage;
          updateVisibility();
        }
      });
    }

    if (investorNextBtn) {
      investorNextBtn.addEventListener('click', () => {
        if (currentIndex + itemsPerPage < investorCols.length) {
          currentIndex += itemsPerPage;
          updateVisibility();
        }
      });
    }

    updateVisibility(); // Initial state on page load
  });
</script>
@endpush