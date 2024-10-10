@extends('layouts.admin')

@section('style')
  <style>
    .radius-15 {
      border-radius: 15px;
    }
    .light-shadow {
      box-shadow: 0px 2px 0px 0px #F2F1F3;
    }
    .section-3 {
      width: 100%;
    }
    .card-icon {
      width: 55px;
      height: 55px;
    }
    .sm-card-icon {
      width: 35px;
      height: 35px;
    }
    .text-xs {
      font-size: 12px;
    }
    .progress-circle {
      position: relative;
      display: inline-block;
    }
    .circle-bg {
      stroke: #e6e6e6; /* Background circle color */
    }
    .circle {
      stroke: rgba(115, 103, 240, 0.9); /* Progress color */
      transition: stroke-dasharray 0.3s ease;
    }
    .flex-container {
      display: flex;
      gap: 10px;
    }
    .flex-item {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    @media screen and (min-width: 768px) {
      .section-3 {
        width: 75%;
      }
    }
  </style>
@stop

@php
  $dummyText = "Lorem ipsum dolor, sit amet consectetur adipisicing elit. Laborum, ipsum cumque earum veniam voluptatem animi. Eligendi officiis cupiditate mollitia temporibus aspernatur cum aliquid asperiores iste quasi itaque inventore, vitae recusandae?"
@endphp

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold mb-4">My Company</h4>
      <section>
        <!-- Company Info Card -->
        <div class="card shadow-sm" style="border-radius: 15px;">
          <div class="card-body row">
            <div class="col-md-10">
              <div class="d-flex align-items-center" style="gap: 1rem;">
                <div 
                  class="border d-flex radius-15 d-flex flex-shrink-0" 
                  style="width: 90px; height: 90px;"
                >
                  <img src="/images/background/logo.png" class="img-fluid m-auto" alt="logo" />
                </div>
                <div class="d-flex flex-column pt-3">
                  <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center">
                      <i class="ti ti-building-skyscraper ti-24px me-2 text-primary"></i>
                      <span class="fw-bold fs-5">Company Name</h5>
                    </div>
                    <div class="d-flex align-items-center mx-8" style="gap: 1rem;">
                      <a href="#" class="d-flex align-items-center">
                        <i class="fa-brands fa-facebook-f fs-4"></i>
                      </a>
                      <a href="#" class="d-flex align-items-center">
                        <i class="fa-brands fa-whatsapp fs-3 text-success"></i>
                      </a>
                      <a href="#" class="d-flex align-items-center">
                        <img src="/icons/linkedin.svg" />
                      </a>
                    </div>
                  </div>
                  <p class="text-light mt-2 fw-medium line-clamp-1">
                    Lorem ipsum, dolor sit amet consectetur adipisicinonsectetur adipisicinonsectetur adipisicin
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-2 d-flex align-items-center justify-content-end">
              <a 
                href="/dashboard/myCompany" 
                class="btn btn-label-primary shadow-sm text-primary fw-bold"
                style="border-radius: 15px;"
              >
                Edit <i class="ti ti-edit mx-2"></i>
              </a>
            </div>
          </div>
        </div>
        <!-- / Company Info Card -->
      </section>
      <section class="mt-6">
        <div class="row">
          <div class="col-md-6">
            <div class="card shadow-sm" style="border-radius: 15px; height: 196px;">
              <div class="card-body d-flex flex-column h-100 justify-content-between">
                <div class="d-flex">
                  <div 
                    class="p-2 bg-label-warning radius-15 d-flex align-items-center"
                    style="width: 150px; height: 74px; gap: 10px;"
                  >
                    <i class="ti ti-users ti-28px"></i>
                    <div class="mx-1 d-flex flex-column">
                      <span class="fw-bold text-black fs-6">100</span>
                      <span class="fw-bold mt-2" style="font-size: 12px;">Team number</span>
                    </div>
                  </div>
                  <div 
                    class="p-2 bg-label-info mx-4 radius-15"
                    style="width: 150px; height: 74px;"
                  >
                    <div class="d-flex flex-column align-items-center justif-content-center">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-calendar-time ti-28px"></i>
                        <span class="mx-1 fw-bold text-black fs-6">2024/12/30</span>
                      </div>
                      <span class="text-nowrap" style="font-size: 12px;">Establishment date</span>
                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  <span class="fw-bold">Working Team</span>
                  <p class="text-muted line-clamp-2 p-2">
                    Lorem, ipsum dolor sit amet consectetur adipisicing elit. Eum sunt officiis rerum 
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 mt-6 mt-md-0">
            <div class="card shadow-sm" style="border-radius: 15px; height: 196px;">
              <div class="card-body d-flex flex-column h-100 justify-content-between">
                <div class="d-flex justify-content-between">
                  <div class="">
                    <i class="ti ti-map-2 ti-28px text-primary me-2"></i> Country
                  </div>
                  <div class="">
                    <i class="ti ti-map-pin ti-28px text-primary me-2"></i> State
                  </div>
                  <div class="">
                    <i class="ti ti-current-location ti-28px text-primary me-2"></i> City
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <i class="ti ti-phone ti-28px text-primary me-2"></i> 01098765897
                </div>
                <div class="d-flex align-items-center">
                  <i class="ti ti-mail ti-28px text-primary me-2"></i> kareem.khaled@gmail.com
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="w-100 mt-6 d-flex flex-column flex-md-row" style="gap: 2rem;">
        <div class="section-3">
          <div class="row">
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm" style="background-color: #c4b5fd; border-radius: 15px;">
                <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/development-stages.svg" alt="icon" />
                    </div>
                    <span class="text-black">Stage of Development</span>
                    <span class="text-primary">Growth Stage</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm" style="background-color: #7dd3fc; border-radius: 15px;">
                 <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/headquarter.svg" alt="icon" />
                    </div>
                    <span class="text-black">Headquarters</span>
                    <span class="text-primary">Egypt</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm" style="background-color: #fdba74; border-radius: 15px;">
                 <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/coin.svg" alt="icon" />
                    </div>
                    <span class="text-black">Current Funding Stage</span>
                    <span class="text-primary">Seed</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm" style="background-color: #5eead4; border-radius: 15px;">
                 <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/target-market.svg" alt="icon" />
                    </div>
                    <span class="text-black">Target Market</span>
                    <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 5px;">
                      <button class="btn btn-outline-primary btn-xs rounded-pill">Saudi Arabia</button>
                      <button class="btn btn-outline-primary btn-xs rounded-pill">Global</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm" style="background-color: #fca5a5; border-radius: 15px;">
                 <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/business-modal.svg" alt="icon" />
                    </div>
                    <span class="text-black">Business Modal</span>
                    <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 5px;">
                      <button class="btn btn-outline-primary btn-xs rounded-pill">Saudi Arabia</button>
                      <button class="btn btn-outline-primary btn-xs rounded-pill">Global</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card bg-light">
                 <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/industries.svg" alt="icon" />
                    </div>
                    <span class="text-black">Industries</span>
                    <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 5px;">
                      <button class="btn btn-outline-primary btn-xs rounded-pill">Saudi Arabia</button>
                      <button class="btn btn-outline-primary btn-xs rounded-pill">Global</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex flex-grow-1">
          <div class="row d-flex flex-column w-100" style="gap: 1rem;">
            <div 
              class="border-primary w-100" 
              style="border: 2px dashed; height: 100px;"
            >
              <div class="p-2" style="display: grid; place-items: center;">
                <img class="sm-card-icon mb-2" src="/icons/coin.svg" alt="icon" />
                <span class="text-black" style="font-size: 12px;">Funding Target</span>
                <span class="text-primary" style="font-size: 12px;">$1000</span>
              </div>
            </div>
            <div 
              class="border-primary w-100" 
              style="border: 2px dashed; height: 100px;"
            >
              <div class="p-2" style="display: grid; place-items: center;">
                <img class="sm-card-icon mb-2" src="/icons/coin.svg" alt="icon" />
                <span class="text-black" style="font-size: 12px;">Pre-money valuation</span>
                <span class="text-primary" style="font-size: 12px;">$1000</span>
              </div>
            </div>
            <div 
              class="border-primary w-100" 
              style="border: 2px dashed; height: 100px;"
            >
              <div class="p-2" style="display: grid; place-items: center;">
                <img class="sm-card-icon mb-2" src="/icons/coin.svg" alt="icon" />
                <span class="text-black" style="font-size: 12px;">Raised Fund</span>
                <span class="text-primary" style="font-size: 12px;">$1000</span>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="mt-6">
        <div class="row">
          <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 15px; height: 290px;">
              <div class="p-3">
                <h5 class="card-title mb-3">Founders</h5>
                <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                  <div class="p-3">
                    <div class="d-flex align-items-center">
                      <div 
                        class="border d-flex radius-15 d-flex flex-shrink-0" 
                        style="width: 80px; height: 80px;"
                      >
                        <img src="/images/background/logo.png" class="img-fluid m-auto" alt="logo" />
                      </div>
                      <div 
                        class="mx-3 d-flex flex-column justify-content-center gap-1" 
                        style="height: 76px;"
                      >
                        <div class="text-black fs-6">Name founder</div>
                        <div class="d-flex align-items-center gap-2">
                          <i class="ti ti-mail text-primary"></i>
                          <span class="text-muted text-xs">Lorem ipsum dolor</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <img src="/icons/linkedin.svg" width="18" height="18" />
                          <span class="text-muted text-xs">Lorem ipsum dolor</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card shadow-sm" style="border-radius: 15px;">
                  <div class="p-3">
                    <div class="d-flex align-items-center">
                      <div 
                        class="border d-flex radius-15 d-flex flex-shrink-0" 
                        style="width: 70px; height: 70px;"
                      >
                        <img src="/images/background/logo.png" class="img-fluid m-auto" alt="logo" />
                      </div>
                      <div 
                        class="mx-3 d-flex flex-column justify-content-center gap-1" 
                        style="height: 76px;"
                      >
                        <div class="text-black fs-6">Name founder</div>
                        <div class="d-flex align-items-center gap-2">
                          <i class="ti ti-mail text-primary"></i>
                          <span class="text-muted text-xs">Lorem ipsum dolor</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <img src="/icons/linkedin.svg" width="18" height="18" />
                          <span class="text-muted text-xs">Lorem ipsum dolor</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 15px; height: 290px;">
              <div class="p-3">
                <h5 class="card-title mb-3">Shareholders</h5>
                <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                  <div class="p-3">
                    <div class="d-flex align-items-center justify-content-between">
                      <div 
                        class="mx-3 d-flex flex-column justify-content-center gap-1" 
                        style="height: 76px;"
                      >
                        <div class="text-black fs-6">Course content</div>
                        <button 
                          class="btn btn-outline-primary btn-xs rounded-pill mt-1 fw-bold"
                          style="height: 22px; width: fit-content"
                        >
                          Founder
                        </button>
                      </div>
                      <div 
                        class="mx-3 d-flex align-items-center gap-3" 
                        style="height: 76px;"
                      >
                        <i class="ti ti-chevrons-right ti-28px;"></i>
                        <!-- Circular Progress -->
                        <div 
                          class="progress-circle p-3 position-relative d-flex align-items-center justify-content-center" 
                          style="width: 54px; height: 54px;"
                        >
                          <span class="position-absolute text-black fw-bold" style="font-size: 13px;">72%</span>
                          <svg class="position-absolute" width="54" height="54" viewBox="0 0 36 36">
                            <path class="circle-bg"
                              d="M18 2.0845
                                  a 15.9155 15.9155 0 0 1 0 31.831
                                  a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#e6e6e6" stroke-width="2.5" />
                            <path class="circle"
                              d="M18 2.0845
                                  a 15.9155 15.9155 0 0 1 0 31.831
                                  a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#007bff" stroke-width="2.5" stroke-dasharray="72, 100" />
                          </svg>
                        </div>
                        <!-- Circular Progress -->
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card shadow-sm" style="border-radius: 15px;">
                  <div class="p-3">
                    <div class="d-flex align-items-center justify-content-between">
                      <div 
                        class="mx-3 d-flex flex-column justify-content-center gap-1" 
                        style="height: 76px;"
                      >
                        <div class="text-black fs-6">Course content</div>
                        <button 
                          class="btn btn-outline-primary btn-xs rounded-pill mt-1 fw-bold"
                          style="height: 22px; width: fit-content"
                        >
                          Angel group
                        </button>
                      </div>
                      <div 
                        class="mx-3 d-flex align-items-center gap-3" 
                        style="height: 76px;"
                      >
                        <i class="ti ti-chevrons-right ti-28px;"></i>
                        <!-- Circular Progress -->
                        <div 
                          class="progress-circle p-3 position-relative d-flex align-items-center justify-content-center" 
                          style="width: 54px; height: 54px;"
                        >
                          <span class="position-absolute text-black fw-bold" style="font-size: 13px;">72%</span>
                          <svg class="position-absolute" width="54" height="54" viewBox="0 0 36 36">
                            <path class="circle-bg"
                              d="M18 2.0845
                                  a 15.9155 15.9155 0 0 1 0 31.831
                                  a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#e6e6e6" stroke-width="2.5" />
                            <path class="circle"
                              d="M18 2.0845
                                  a 15.9155 15.9155 0 0 1 0 31.831
                                  a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#007bff" stroke-width="2.5" stroke-dasharray="72, 100" />
                          </svg>
                        </div>
                        <!-- Circular Progress -->
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 15px; height: 290px;">
              <div class="p-3">
                <h5 class="card-title mb-3">Investors</h5>
                <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                  <div class="p-3">
                    <div class="d-flex align-items-center">
                      <div 
                        class="border d-flex radius-15 d-flex flex-shrink-0" 
                        style="width: 80px; height: 80px;"
                      >
                        <img src="/images/background/logo.png" class="img-fluid m-auto" alt="logo" />
                      </div>
                      <div 
                        class="mx-3 d-flex flex-column justify-content-center gap-1" 
                        style="height: 76px;"
                      >
                        <div class="text-black fs-6">Name founder</div>
                        <div class="d-flex align-items-center gap-2">
                          <i class="ti ti-drone text-info"></i>
                          <button class="btn btn-outline-primary btn-xs rounded-pill fw-bold"
                           style="height: 18px;">
                            Seed
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card shadow-sm" style="border-radius: 15px;">
                  <div class="p-3">
                    <div class="d-flex align-items-center">
                      <div 
                        class="border d-flex radius-15 d-flex flex-shrink-0" 
                        style="width: 70px; height: 70px;"
                      >
                        <img src="/images/background/logo.png" class="img-fluid m-auto" alt="logo" />
                      </div>
                      <div 
                        class="mx-3 d-flex flex-column justify-content-center gap-1" 
                        style="height: 76px;"
                      >
                        <div class="text-black fs-6">Name founder</div>
                        <div class="d-flex align-items-center gap-2">
                          <i class="ti ti-drone text-info"></i>
                          <button class="btn btn-outline-primary btn-xs rounded-pill fw-bold" 
                          style="height: 18px;">
                            Seriea A
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="mt-6">
        <div class="row">
          <div class="col-md-6">
            <div class="card shadow-sm" style="border-radius: 15px; height: 520px;">
              <div class="p-3">
                <h5 class="card-title">Pitch deck:</h5>
                <div class="card-body text-center">
                  <div class="d-flex flex-column justify-content-between align-items-center">
                    <img src="/images/deck.svg" style="width: 350px; height: 350px;" />
                    <div class="mt-auto d-flex flex-column">
                      <span class="text-muted">To get analysis for your pitch deck please</span>
                      <a href="#" class="text-primary fw-medium text-decoration-underline mt-1">Click here</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card shadow-sm" style="border-radius: 15px; height: 520px;">
              <div class="p-3">
                <h5 class="card-title">Pitch deck video:</h5>
                <div class="card-body text-center d-grid justify-content-between">
                  <div class="d-flex flex-column justify-content-between align-items-center">
                    <img src="/images/deck-video.svg" style="width: 350px; height: 350px;" />
                    <div class="mt-auto d-flex flex-column">
                      <span class="text-muted">
                        To get analysis for your pitch deck please upload your pitch deck video
                      </span>
                      <a href="#" class="text-primary fw-medium text-decoration-underline mt-1">Click here</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="mt-6">        
        <div class="flex-container">
          <div class="flex-item">
            <div class="card shadow-sm" style="border-radius: 15px;">
              <div class="p-3">
                <h5 class="card-title">Pitch deck information</h5>
                <div class="d-flex flex-column gap-2">
                  @foreach([1, 2, 3] as $key => $document)
                    <div class="card shadow-sm" style="height: 132px; border-radius: 15px;">
                      <div class="card-body">
                        <h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                          <div
                            style="width: 32px; height: 32px;" 
                            class="rounded-circle bg-primary text-white d-flex 
                            justify-content-center align-items-center me-2"
                          >
                            {{ $key + 1 }}
                          </div>
                          Pitch deck summery
                        </h6>
                        <p class="text-muted line-clamp-2 fs-6">
                        {{$dummyText}}
                        </p>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="card shadow-sm" style="border-radius: 15px;">
              <div class="p-3">
                <h5 class="card-title">Documents</h5>
                <div class="d-flex flex-column gap-2">
                  @foreach([1, 2, 3] as $key => $document)
                    <div class="card shadow-sm" style="border-radius: 15px;">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                          <div class="d-flex align-items-center gap-3">
                            <i class="ti ti-file fs-1 text-warning"></i>
                            <span>Document.pdf</span>
                          </div>
                          <button class="btn btn-transparent">
                            <i class="ti ti-download fs-3 text-primary"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
          <div class="flex-item">
            <div class="card shadow-sm" style="border-radius: 15px; height: 350px;">
              <div class="p-3">
                <h5 class="card-title">Achieved milestones</h5>
                <div class="d-flex flex-column gap-2">
                  @foreach([1, 2, 3] as $key => $document)
                    <div class="card shadow-sm" style="border-radius: 15px;">
                      <div class="card-body">
                        <div class="d-flex gap-2 d-flex align-items-center justify-content-center">
                          <div
                            style="width: 32px; height: 32px;" 
                            class="rounded-circle bg-primary text-white d-flex 
                            flex-shrink-0 justify-content-center align-items-center me-2"
                          >
                            {{ $key + 1 }}
                          </div>
                          <div class="text-muted line-clamp-2 fs-6">
                          {{$dummyText}}
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="card shadow-sm" style="border-radius: 15px;">
              <div class="p-3">
                <h5 class="card-title">Planned milestones</h5>
                <div class="d-flex flex-column gap-2">
                  @foreach([1, 2, 3] as $key => $document)
                    <div class="card shadow-sm" style="border-radius: 15px;">
                      <div class="card-body">
                        <div class="d-flex gap-2 d-flex align-items-center justify-content-center">
                          <div
                            style="width: 32px; height: 32px;" 
                            class="rounded-circle bg-primary text-white d-flex 
                            flex-shrink-0 justify-content-center align-items-center me-2"
                          >
                            {{ $key + 1 }}
                          </div>
                          <div class="text-muted line-clamp-2 fs-6">
                          {{$dummyText}}
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="mt-6">
        <div class="row">
        </div>
      </section>
    </div>
  </div>
@stop
