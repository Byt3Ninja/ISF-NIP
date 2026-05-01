<div class="col-md-4">
  <div class="card shadow-sm" style="height: 300px; overflow-y: hidden;">
    <div class="py-4 px-8">
      <h5 class="card-title mb-3">{{ __('admin.Founders') }} ( {{count($founders)}} )</h5>
      @if (count($founders) > 0)
      <div class="row" style="position: relative; height: 90%;">
        @if (count($founders) > 2)
        <button id="founder-prev-2" class="btn btn-sm btn-icon btn-white rounded-circle shadow border d-none"
          style="position: absolute; left: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
          <i class="ti ti-chevron-left"></i>
        </button>
        <button id="founder-next-2" class="btn btn-sm btn-icon btn-white rounded-circle shadow border"
          style="position: absolute; right: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
          <i class="ti ti-chevron-right"></i>
        </button>
        @endif

        @foreach ($founders as $fonder)
        <div class="founder-col-2 col-12 mb-2">
          <div class="p-3 shadow-sm border overflow-hidden">
            <div class="d-flex align-items-center">
              <div class="border d-flex radius-15 d-flex flex-shrink-0" style="width: 80px; height: 80px;">
                <img src="/images/placeholder.jpg" class="img-fluid m-auto" alt="logo" />
              </div>
              <div class="mx-3 d-flex flex-column justify-content-center gap-1" style="height: 76px;">
                <div class="text-black fs-6">{{ $fonder->Name }}</div>
                <div class="d-flex align-items-center gap-2">
                  <i class="ti ti-mail text-primary"></i>
                  <span class="text-muted text-xs line-clamp-1">{{ $fonder->Email }}</span>
                </div>
                <a href="{{ $fonder->LinkedIn }}" class="d-flex align-items-center gap-2" style="width: 200px;">
                  <img src="/icons/linkedin.svg" width="18" height="18" />
                  <span class="text-muted text-xs line-clamp-1">{{ $fonder->LinkedIn }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="d-flex flex-column">
        <div class="text-center">
          <img src="/icons/no-founders.svg" class="img-fluid" alt="empty" width="200" height="200" />
          <h6 class="text-muted">{{ __('admin.No Founders') }}</h6>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const founderCols2 = document.querySelectorAll('.founder-col-2');
    const founderPrevBtn2 = document.querySelector('#founder-prev-2');
    const founderNextBtn2 = document.querySelector('#founder-next-2');

    // Only proceed if there are founders
    if (founderCols2.length === 0) {
      return;
    }

    // Only set up pagination if navigation buttons exist (more than 2 founders)
    if (!founderPrevBtn2 || !founderNextBtn2) {
      // Show all founders when there are 2 or fewer
      founderCols2.forEach((col) => {
        col.style.display = 'block';
      });
      return;
    }

    let currentIndex2 = 0;
    const itemsPerPage2 = 2;

    function updateVisibility2() {
      founderCols2.forEach((col, index) => {
        col.style.display = (index >= currentIndex2 && index < currentIndex2 + itemsPerPage2) ? 'block' : 'none';
      });

      if (founderPrevBtn2) {
        founderPrevBtn2.classList.toggle('d-none', currentIndex2 === 0);
      }
      if (founderNextBtn2) {
        founderNextBtn2.classList.toggle('d-none', currentIndex2 + itemsPerPage2 >= founderCols2.length);
      }
    }

    // Add event listeners with additional null checks
    if (founderPrevBtn2) {
      founderPrevBtn2.addEventListener('click', () => {
        if (currentIndex2 > 0) {
          currentIndex2 -= itemsPerPage2;
          updateVisibility2();
        }
      });
    }

    if (founderNextBtn2) {
      founderNextBtn2.addEventListener('click', () => {
        if (currentIndex2 + itemsPerPage2 < founderCols2.length) {
          currentIndex2 += itemsPerPage2;
          updateVisibility2();
        }
      });
    }

    updateVisibility2();
  });
</script>
