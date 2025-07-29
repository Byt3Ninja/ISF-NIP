@extends('layouts.hr-admin')

@php
// $teams = $teams->members
// [
// [
// 'name' => 'Rafael Montano',
// 'title' => 'Fullstack Developer',
// 'img_url' =>
// 'https://lablab.ai/_next/image?url=https%3A%2F%2Fstorage.googleapis.com%2Flablab-static-eu%2Fimages%2Fusers%2Fcmchlf8v1006h4q0s8jelyion_picture_9v2hwm0wlz.jpg&w=256&q=75',
// 'linkedin' => 'https://www.linkedin.com/in/rafael-monta%C3%B1o-8b0a4b1a6/',
// 'email' => 'rafael.montano@example.com'
// ],
// [
// 'name' => 'Jesus Lanzarote Rodriguez',
// 'title' => 'CTO',
// 'img_url' =>
// 'https://lablab.ai/_next/image?url=https%3A%2F%2Fstorage.googleapis.com%2Flablab-static-eu%2Fimages%2Fusers%2Fcmchlf8v1006h4q0s8jelyion_picture_9v2hwm0wlz.jpg&w=256&q=75',
// 'linkedin' => 'https://www.linkedin.com/in/jesus-lanzarote-rodriguez-123456789/',
// 'email' => 'jesus.lanzarote@example.com'
// ],
// [
// 'name' => 'Maximiliano Garcia',
// 'title' => 'Frontend Developer',
// 'img_url' =>
// 'https://lablab.ai/_next/image?url=https%3A%2F%2Fstorage.googleapis.com%2Flablab-static-eu%2Fimages%2Fusers%2Fcmchlf8v1006h4q0s8jelyion_picture_9v2hwm0wlz.jpg&w=256&q=75',
// 'linkedin' => 'https://www.linkedin.com/in/maximiliano-garcia-123456789/',
// 'email' => 'maximiliano.garcia@example.com'
// ],
// [
// 'name' => 'Jorge Ordovas',
// 'title' => 'PM Telefonica CEO of Kairos',
// 'img_url' =>
// 'https://lablab.ai/_next/image?url=https%3A%2F%2Fstorage.googleapis.com%2Flablab-static-eu%2Fimages%2Fusers%2Fcmchlf8v1006h4q0s8jelyion_picture_9v2hwm0wlz.jpg&w=256&q=75',
// 'linkedin' => 'https://www.linkedin.com/in/jorge-ordovas-123456789/',
// 'email' => 'jorge.ordovas@example.com'
// ],
// ]
@endphp


@section('content')
<!-- Main Content -->
<div class="container py-5">
  <div class="text-center mb-5">
    <h1 class="display-4 fw-bold">Kairos - Qubic Track team on RAISE YOUR HACK Hackathon</h1>
    <p class="text-secondary fs-5 mb-2">Team Idea</p>
    <p class="text-muted fs-5 mx-auto" style="max-width: 64rem;">
      Startup focused on designing, developing, and launching scalable digital web3 products. 2nd prize winners at MAD
      Hack 2025. Building at Qubic track!
    </p>
  </div>

  <!-- Team Members Grid -->
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4 justify-content-center">
    @foreach($teams as $member)
    <div class="col">
      <div class="card card-body text-center h-100">
        <div class="mx-auto position-relative mb-3 w-px-150 h-px-150 rounded-circle bg-danger">
          <img src="{{ $member['img_url'] }}" alt="img" class="h-100 w-100 rounded-circle img-fluid bg-light"
            style="width:96px;height:96px;object-fit:cover;">
        </div>
        <div>
          <h5 class="fw-semibold mb-1">{{ $member['name'] }}</h5>
          <div class="text-secondary small mb-4">{{ $member['title'] }}</div>
          <div class="d-flex justify-content-center gap-2">
            <button 
              class="btn btn-light btn-sm rounded-circle p-2"
              onclick="window.location.href='mailto:{{ $member['email'] }}'">
              <!-- Gmail -->
              <i class="ti ti-mail-filled"></i>
            </button>
            <a class="btn btn-light btn-sm rounded-circle p-2" href="{{ $member['linkedin'] }}">
              <!-- LinkedIn -->
              <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
</div>
@stop