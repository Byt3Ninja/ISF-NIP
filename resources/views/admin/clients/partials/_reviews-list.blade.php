@forelse($reviews as $review)
<div class="col-12 col-md-6 review-item">
  <div class="bg-card review-card-item rounded-3 p-4 border h-100">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div class="d-flex gap-3">
        @php
          $reviewerImg = $review->reviewer_img
            ? (str_starts_with($review->reviewer_img, 'https://') ? $review->reviewer_img : '/191014/' . $review->reviewer_img)
            : null;
        @endphp
        @if($reviewerImg)
          <img src="{{ $reviewerImg }}" alt="{{ trim($review->reviewer_name) }}"
            class="rounded-circle object-fit-cover"
            style="width: 48px; height: 48px;"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
          <div class="bg-light rounded-circle align-items-center justify-content-center"
            style="width: 48px; height: 48px; display: none;">
            <i class="ti ti-user fs-5"></i>
          </div>
        @else
          <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
            style="width: 48px; height: 48px;">
            <i class="ti ti-user fs-5"></i>
          </div>
        @endif

        <div>
          <h6 class="mb-1 fw-semibold">
            <a class="text-dark" href="{{ route('clients.profile_view', $review->reviewer_user_id) }}"> {{ trim($review->reviewer_name) }} </a>
          </h6>
          @if($review->company_name ?? $review->company_name_en ?? null)
            <div class="text-muted small">
              <a class="text-muted" href="{{ route('company.profile_view', $review->company_id) }}"> {{ $review->company_name ?: $review->company_name_en }} </a>
            </div>
          @endif
          <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
            <span class="badge bg-light-subtle border text-dark rounded-pill">
              {{ $review->service_name ?: $review->service_name_en }}
            </span>
            @if($review->reviewer_joined_at)
              <span class="text-muted small">
                &bull; {{ __('admin.joined') }} {{ \Carbon\Carbon::parse($review->reviewer_joined_at)->format('Y') }}
              </span>
            @endif
          </div>
        </div>
      </div>

      <!-- Stars -->
      <div class="text-warning">
        @php
          $fullStars = floor($review->rate);
          $halfStar = ($review->rate - $fullStars) >= 0.5;
        @endphp
        @for($i = 0; $i < $fullStars; $i++)
          <i class="ti ti-star-filled fs-6"></i>
        @endfor
        @if($halfStar)
          <i class="ti ti-star-half-filled fs-6"></i>
        @endif
      </div>
    </div>

    <!-- Content -->
    @if($review->review_title)
      <h6 class="fw-semibold mb-2">{{ $review->review_title }}</h6>
    @endif
    <div class="mb-7">{{ $review->review_body ?: $review->rate_comment }}</div>

    <!-- Date -->
    <div class="text-muted small reviewed-at-date">
      {{ \Carbon\Carbon::parse($review->reviewed_at)->format('F j, Y') }}
    </div>
  </div>
</div>
@empty
  <div class="col-12">
    @include('includes.no-result', ['message' => __('admin.no_reviews_found')])
  </div>
@endforelse
