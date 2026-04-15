@php
$foundersList = (is_iterable($founders) && !is_string($founders)) ? collect($founders)->values() : collect();
@endphp

<div class="col-md-4">
  <div class="card shadow-sm" style="height: 320px; overflow-y: hidden;">
    <div class="py-4 px-8">
      <h5 class="card-title mb-2">{{ __('admin.Founders') }} ( {{ $foundersList->count() }} )</h5>
      @if ($foundersList->count() > 0)
      <div class="row" style="position: relative; height: 90%;">
        @if ($foundersList->count() > 2)
        <button id="founder-prev-2" class="btn btn-sm btn-icon btn-white rounded-circle shadow border d-none"
          style="position: absolute; left: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
          <i class="ti ti-chevron-left"></i>
        </button>
        <button id="founder-next-2" class="btn btn-sm btn-icon btn-white rounded-circle shadow border"
          style="position: absolute; right: -1rem; top: 46%; transform: translateY(-50%); z-index: 1;">
          <i class="ti ti-chevron-right"></i>
        </button>
        @endif

        @foreach ($foundersList as $founder)
        @php
          $typeLabel = match($founder['founder_type'] ?? '') {
              'Founder'    => __('admin.Founder'),
              'Co-founder' => __('admin.Co_founder'),
              default      => $founder['founder_type'] ?? '',
          };
          $commitmentLabel = match($founder['working_commitment'] ?? '') {
              'Full-time' => __('admin.full_time'),
              'Part-time' => __('admin.part_time'),
              default     => '',
          };
        @endphp
        <div class="founder-col-2 col-12 mb-2">
          <div class="card item-card shadow-none border mb-0">
            <div class="card-body py-2 px-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h6 class="mb-0">{{ $founder['name'] }}</h6>
                  <small class="text-muted">{{ $typeLabel }}</small>
                </div>
                @if (!empty($commitmentLabel))
                <span class="badge bg-label-secondary text-dark">{{ $commitmentLabel }}</span>
                @endif
              </div>
              <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <a
                  href="mailto:{{ $founder['email'] }}"
                  class="small text-muted"
                  style="min-width: 0; overflow-wrap: break-word;"
                >
                  <i class="ti ti-mail ti-sm"></i>
                  <span class="ms-1 text-break" style="word-break: break-word;">{{ $founder['email'] }}</span>
                </a>
                @if (!empty($founder['phone']))
                <a
                  href="tel:{{ preg_replace('/[^0-9]/', '', str_replace('+', '', $founder['phone'])) }}"
                  class="small text-muted ms-auto"
                >
                  <i class="ti ti-phone ti-sm"></i>
                  <span class="ms-1">{{ str_replace('+', '', $founder['phone']) }}</span>
                </a>
                @endif
                @if (!empty($founder['join_date']))
                <div class="small text-muted">
                  <i class="ti ti-calendar ti-sm"></i>
                  <span>{{ \Illuminate\Support\Carbon::parse($founder['join_date'])->format('d-m-Y') }}</span>
                </div>
                @endif
                @if (!empty($founder['linkedin']))
                <a
                  href="{{ $founder['linkedin'] }}"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="small text-muted"
                >
                  <i class="ti ti-brand-linkedin ti-sm"></i>
                </a>
                @endif
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

@push('scripts')
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
@endpush
