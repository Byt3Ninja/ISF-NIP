{{--
  One assessment row in the founder's history list.

  $assessment  App\Assessment
  $score       float   total weighted score
  $state       string  'open' | 'done'
--}}
@php
  $isOpen = $state === 'open';
  $stamp = $isOpen ? $assessment->created_at : $assessment->end_date;
@endphp
<div class="history-card {{ $isOpen ? 'history-card--open' : '' }}">
  <div class="history-card__score">
    <span class="history-card__score-value">{{ $score > 0 ? number_format($score, 2) : '0' }}</span>
    <span class="history-card__score-label">{{ __('admin.Overall_Weighted_Score') }}</span>
  </div>

  <div class="history-card__body">
    <p class="history-card__recommendation-label">{{ __('admin.Overall_Recommendation') }}</p>
    <p class="history-card__recommendation">Good progress, but room for scaling</p>
  </div>

  <div class="history-card__meta">
    @if($isOpen)
      <span class="history-card__status">{{ __('admin.In_Progress') }}</span>
    @endif
    <span class="history-card__date">
      <i class="ti ti-calendar ti-16px"></i>
      <span class="history-card__date-label">{{ $isOpen ? __('admin.Created') : __('admin.Ended') }}</span>
      <time>{{ $stamp ? \Carbon\Carbon::parse($stamp)->format('Y-m-d') : '—' }}</time>
    </span>
  </div>
</div>
