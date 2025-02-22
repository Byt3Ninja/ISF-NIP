<div class="col-md-4 mt-6">
  <div class="card shadow-sm" style="border-radius: 15px; height: 300px; overflow-y: hidden;">
    <div class="py-4 px-8">
      <h5 class="card-title mb-3">{{ __('admin.investors') }} ( {{count($Shareholders)}} )</h5>
      @if (count($Investors) > 0)
      <div class="row" style="position: relative; height: 90%;">
        @if (count($Investors) > 2)
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
        <div class="investor-col col-12 mb-2">
          <div class="card shadow-sm border" style="border-radius: 15px;">
            <div class="p-3">
              <div class="d-flex align-items-center">
                <div class="border d-flex radius-15 d-flex flex-shrink-0" style="width: 80px; height: 80px;">
                  <img src="/images/background/logo.png" class="img-fluid m-auto" alt="logo" />
                </div>
                <div class="mx-3 d-flex flex-column justify-content-center gap-1">
                  <div class="text-black fs-6">{{ $Investor->Name }}</div>
                  <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-drone text-info"></i>
                    <button class="btn btn-outline-primary btn-xs rounded-pill fw-bold" style="height: 18px;">
                      {{ $Investor->investorType }}
                    </button>
                  </div>
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
          <img src="/icons/no-investors.svg" class="img-fluid" alt="empty" width="200" height="200" />
          <h6 class="text-muted">{{ __('admin.No Investors') }}</h6>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const investorCols = document.querySelectorAll('.investor-col');
    const investorPrevBtn = document.querySelector('#investor-prev');
    const investorNextBtn = document.querySelector('#investor-next');

    let currentIndex = 0;
    const itemsPerPage = 2; // Number of items to show at a time

    function updateVisibility() {
      investorCols.forEach((col, index) => {
        col.style.display = (index >= currentIndex && index < currentIndex + itemsPerPage) ? 'block' : 'none';
      });

      investorPrevBtn.classList.toggle('d-none', currentIndex === 0);
      investorNextBtn.classList.toggle('d-none', currentIndex + itemsPerPage >= investorCols.length);
    }

    investorPrevBtn.addEventListener('click', () => {
      if (currentIndex > 0) {
        currentIndex -= itemsPerPage;
        updateVisibility();
      }
    });

    investorNextBtn.addEventListener('click', () => {
      if (currentIndex + itemsPerPage < investorCols.length) {
        currentIndex += itemsPerPage;
        updateVisibility();
      }
    });

    updateVisibility(); // Initial state on page load
  });
</script>
