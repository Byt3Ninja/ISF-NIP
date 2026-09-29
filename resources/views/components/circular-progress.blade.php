<style>
  .progress-circle {
    position: relative;
    display: inline-block;
  }

  .circle-bg {
    stroke: #e6e6e6;
  }

  .circle {
    stroke: {{ $color }};
    /* Progress color */
    transition: stroke-dasharray 0.3s ease;
  }

  .text-xs {
    font-size: 0.65rem;
  }
</style>

<!-- Circular Progress -->
<div class="progress-circle p-3 position-relative d-flex align-items-center justify-content-center"
  style="width: {{ $size }}px; height: {{ $size }}px;">
  <span class="position-absolute text-xs fw-bold">{{ number_format($progress, 1) }}%</span>
  <svg class="position-absolute" width="50" height="50" viewBox="0 0 36 36">
    <path class="circle-bg" d="M18 2.0845
    a 15.9155 15.9155 0 0 1 0 31.831
    a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e6e6e6" stroke-width="3.5" />
    <path 
    
      style="transition: stroke-dasharray 0.3s ease; stroke: {{ $color }};"
    d="M18 2.0845
    a 15.9155 15.9155 0 0 1 0 31.831
    a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#007bff" stroke-width="3.5" stroke-dasharray="{{$progress}}, 100" />
  </svg>
</div>
<!-- Circular Progress -->