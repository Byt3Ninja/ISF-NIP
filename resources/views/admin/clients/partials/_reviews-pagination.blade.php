@if($lastPage > 1)
<nav class="mt-6">
  <ul class="pagination justify-content-center mb-0">
    <!-- Previous -->
    <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
      <a class="page-link" href="javascript:;" data-page="{{ $currentPage - 1 }}">
        <i class="ti ti-chevron-left ti-xs"></i>
      </a>
    </li>

    @php
      $start = max(1, $currentPage - 2);
      $end = min($lastPage, $currentPage + 2);
      if ($end - $start < 4) {
        $start = max(1, $end - 4);
        $end = min($lastPage, $start + 4);
      }
    @endphp

    @for($i = $start; $i <= $end; $i++)
      <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
        <a class="page-link" href="javascript:;" data-page="{{ $i }}">{{ $i }}</a>
      </li>
    @endfor

    <!-- Next -->
    <li class="page-item {{ $currentPage >= $lastPage ? 'disabled' : '' }}">
      <a class="page-link" href="javascript:;" data-page="{{ $currentPage + 1 }}">
        <i class="ti ti-chevron-right ti-xs"></i>
      </a>
    </li>
  </ul>
</nav>
@endif
