@extends('layouts.admin')

@section('style')
<style>
  .rounded,
  .card,
  .nav-link {
    border-radius: 1rem !important;
  }

  .text-sm {
    font-size: 0.85rem;
  }

  .text-xs {
    font-size: 0.65rem;
  }

  .light-shadow {
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .assessment-tab:hover {
    background-color: #F2F1F3;
    border: 1px solid #000;
    cursor: pointer;
  }
</style>
@stop

@php
$pills = ['analysis-assessment', 'question-assessment', 'history'];
$mobilePills = ['analysis', 'questions', 'history'];

$assessments = [
  ['title' => 'business-concept', 'progress' => 50, 'status' => 'in-progress'],
  ['title' => 'organization', 'progress' => 75, 'status' => 'in-progress'],
  ['title' => 'operations', 'progress' => 100, 'status' => 'completed'],
  ['title' => 'customer-relation', 'progress' => 20, 'status' => 'not-started'],
];
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Pills (Mobile) -->
    <div class="row mb-4 d-block d-md-none">
      <div class="col-12">
        <ul class="nav nav-pills">
          @foreach($mobilePills as $key => $pill)
          <li class="nav-item me-1">
            <a href="/dashboard/{{ $pill }}" class="nav-link {{ $key == 1 ? 'active' : '' }}">
              <span>{{ $pill }}</span>
            </a>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
    <!-- Pills (Mobile) -->
    <!-- Pills -->
    <div class="row mb-4 d-none d-md-block">
      <div class="col-12">
        <div class="card shadow-sm">
          <ul class="nav nav-pills flex-nowrap px-2 py-3">
            @foreach($pills as $key => $pill)
            <li class="nav-item me-1">
              <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}" class="nav-link {{ $key == 1 ? 'active' : '' }}">
                <span class="fs-6">{{ str_replace('-', ' ', $pill) }}</span>
              </a>
            </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
    <!-- / Pills -->
    <div class="row">
      <div class="col-md-3 col-12">
        @foreach($assessments as $key => $assessment)
        <div id="tab{{ $key + 1 }}" class="assessment-tab card shadow-sm mb-3 {{$key == 0 ? 'border border-dark' : ''}}">
          <div class="d-flex justify-content-between align-items-center p-4">
            <div class="d-flex flex-column">
              <h6 class="mb-0 text-capitalize">
                {{ str_replace('-', ' ', $assessment['title']) }}
              </h6>
              <span class="text-xs text-secondary">20/25 questions answered</span>
            </div>
            <x-circular-progress size="40" color="success" />
          </div>
        </div>
        @endforeach
      </div>
      <div class="col-md-9 col-12">
        @foreach($assessments as $key => $assessment)
        <section id="tab-content-{{ $key + 1 }}" class="{{ $key == 0 ? 'd-block' : 'd-none' }}">
          @if ($assessment['title'] == 'business-concept')
            @include('admin.clients.assessment.partials.business-concept')
            @elseif ($assessment['title'] == 'organization')
            @include('admin.clients.assessment.partials.organization')
          @elseif ($assessment['title'] == 'operations')
          {{-- <h1>Operations</h1> --}}
            @include('admin.clients.assessment.partials.operations')
          @elseif ($assessment['title'] == 'customer-relation')
          {{-- <h1>customer-relation</h1> --}}
            @include('admin.clients.assessment.partials.customer-relation')
          @endif
        </section>
        @endforeach
      </div>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  $(document).ready(() => {
    // Handle assessment tab click
    $('.assessment-tab').click(function() {
      const tabId = $(this).attr('id').replace('tab', '');
      $('.assessment-tab').removeClass('border border-dark');
      $(this).addClass('border border-dark');
      $('[id^="tab-content"]').addClass('d-none');
      $(`#tab-content-${tabId}`).removeClass('d-none');
    });
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const categoryTab = document.querySelectorAll('#category-tab');

    // Handle category tab click
    categoryTab.forEach(tab => {
      tab.addEventListener('click', () => {
        categoryTab.forEach(tab => {
          tab.classList.remove('active-cat');
        });

        tab.classList.add('active-cat');
      });
    })

  });
  // Handle show questions
  const showQuestions = (category) => {
    document.querySelectorAll('.question-card').forEach(card => {
      card.style.display = card.getAttribute('data-category') === category ? 'block' : 'none';
    });
  }
</script>
@stop