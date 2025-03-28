@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-email.css" />
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="app-email card">
        <div class="row g-0">
          @include('admin.clients.messages.partials.sidebar')

          <!-- Emails List -->
          <div class="col app-emails-list">
            <div class="card shadow-none border-0 rounded-0">
              <div class="card-body emails-list-header p-3 py-2">
                <!-- Email List: Search -->
                <div class="d-flex justify-content-between align-items-center px-3 mt-2">
                  <div class="d-flex align-items-center w-100">
                    <i class="ti ti-menu-2 ti-lg cursor-pointer d-block d-lg-none me-4 mb-4" data-bs-toggle="sidebar"
                      data-target="#app-email-sidebar" data-overlay></i>
                    <div class="mb-4 w-100">
                      <div class="input-group input-group-merge shadow-none">
                        <span class="input-group-text border-0 ps-0 py-0" id="email-search">
                          <i class="ti ti-search ti-lg"></i>
                        </span>
                        <input type="text" class="form-control email-search-input border-0 py-0"
                          placeholder="Search mail" aria-label="Search mail" aria-describedby="email-search" />
                      </div>
                    </div>
                  </div>
                </div>
                <hr class="mx-n3 emails-list-header-hr mb-2" />
                <!-- Email List: Actions -->
                <div class="d-flex justify-content-between align-items-center ps-1">
                  <div class="d-flex align-items-center">
                    <div class="form-check mb-0 ms-2">
                      <input class="form-check-input" type="checkbox" id="email-select-all" />
                      <label class="form-check-label" for="email-select-all"></label>
                    </div>
                    <div class="btn btn-text-secondary btn-icon rounded-pill me-1">
                      <i class="ti ti-trash ti-md email-list-delete cursor-pointer"></i>
                    </div>
                    <div class="btn btn-text-secondary btn-icon rounded-pill me-1">
                      <i class="ti ti-mail-opened ti-md email-list-read cursor-pointer"></i>
                    </div>
                    <div class="dropdown me-1">
                      <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button"
                        id="dropdownMenuFolderOne" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-folder ti-md"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuFolderOne">
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-info-circle ti-sm me-1"></i>
                          <span class="align-middle">Spam</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-file ti-sm me-1"></i>
                          <span class="align-middle">Draft</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-trash ti-sm me-1"></i>
                          <span class="align-middle">Trash</span>
                        </a>
                      </div>
                    </div>
                    <div class="dropdown mx-1">
                      <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                        data-bs-toggle="dropdown" aria-expanded="true" id="dropdownLabelOne">
                        <i class="ti ti-tag ti-md"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLabelOne">
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="badge badge-dot bg-success me-1"></i>
                          <span class="align-middle">Workshop</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="badge badge-dot bg-primary me-1"></i>
                          <span class="align-middle">Company</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="badge badge-dot bg-warning me-1"></i>
                          <span class="align-middle">Important</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="badge badge-dot bg-danger me-1"></i>
                          <span class="align-middle">Private</span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <span class="btn btn-icon btn-text-secondary rounded-pill me-1">
                      <i class="ti ti-refresh ti-md scaleX-n1-rtl cursor-pointer email-refresh"></i>
                    </span>
                    <div class="dropdown">
                      <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button" id="emailsActions"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-md"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="emailsActions">
                        <a class="dropdown-item" href="javascript:void(0)">Mark as read</a>
                        <a class="dropdown-item" href="javascript:void(0)">Mark as unread</a>
                        <a class="dropdown-item" href="javascript:void(0)">Delete</a>
                        <a class="dropdown-item" href="javascript:void(0)">Archive</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <hr class="container-m-nx m-0" />
              <!-- Email List: Items -->
              <div class="email-list pt-0" id="receivedMessages">
                <ul class="list-unstyled m-0">
                  @foreach ($receivedMessages as $message)
                    <li class="email-list-item email-marked-read d-flex align-items-center" data-starred="true"
                      data-bs-toggle="sidebar" data-target="#app-email-view">
                      <div class="d-flex align-items-center w-100">
                        <div class="form-check mb-0 ms-2">
                          <input class="email-list-item-input form-check-input" type="checkbox"
                            id="email-{{ $message['id'] }}" />
                          <label class="form-check-label" for="email-{{ $message['id'] }}"></label>
                        </div>
                        <span class="ms-sm-3 me-4 d-sm-inline-block d-none"><i
                            class="email-list-item-bookmark ti ti-star ti-md cursor-pointer"></i></span>
                        <img src="/icons/account.svg" alt="user-avatar"
                          class="d-block flex-shrink-0 rounded-circle me-sm-2 me-0" height="32" width="32" />
                        <div class="email-list-item-content ms-2 ms-sm-0 me-2">
                          <span class="email-list-item-username me-2 text-heading">
                            {{ $message['first_name_' . $l] }}
                            {{ $message['last_name_' . $l] }}
                          </span>
                          <small class="email-list-item-subject d-xl-inline-block d-block">
                            {{ $message['subject'] }}
                          </small>
                        </div>
                        <div class="email-list-item-meta ms-auto d-flex align-items-center">
                          <span class="email-list-item-label badge badge-dot bg-danger d-none d-md-inline-block me-2"
                            data-label="private"></span>
                          <small
                            class="email-list-item-time text-muted">{{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}</small>
                          <ul class="list-inline email-list-item-actions">
                            <li class="list-inline-item email-read btn btn-icon btn-text-secondary rounded-pill">
                              <i class="ti ti-mail ti-md"></i>
                            </li>
                            <li class="list-inline-item email-delete btn btn-icon btn-text-secondary rounded-pill">
                              <i class="ti ti-trash ti-md"></i>
                            </li>
                            <li class="list-inline-item btn btn-icon btn-text-secondary rounded-pill">
                              <i class="ti ti-info-circle ti-md"></i>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </li>
                  @endforeach
                </ul>
                <ul class="list-unstyled m-0">
                  <li class="email-list-empty text-center d-none">No items found.</li>
                </ul>
              </div>
            </div>
            <div class="app-overlay"></div>
          </div>
          <!-- /Emails List -->

          <!-- Email View -->
          <div class="col app-email-view flex-grow-0 bg-lighter" id="app-email-view">
            <div class="card shadow-none border-0 rounded-0 app-email-view-header p-5 pt-md-4 py-2">
              <!-- Email View : Title  bar-->
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center overflow-hidden">
                  <span class="ms-sm-2 me-4"><i class="ti ti-chevron-left ti-md cursor-pointer"
                      data-bs-toggle="sidebar" data-target="#app-email-view"></i></span>
                  <h6 class="text-truncate mb-0 me-2 fw-normal">Focused impactful open issues</h6>
                  <span class="badge bg-label-warning rounded-pill">Important</span>
                </div>
                <!-- Email View : Action bar-->
                <div class="d-flex align-items-center">
                  <span class="btn btn-icon p-0 me-2 text-muted">
                    <i class="ti ti-chevron-left ti-md"></i>
                  </span>
                  <span class="btn btn-icon p-0">
                    <i class="ti ti-chevron-right ti-md"></i>
                  </span>
                </div>
              </div>
              <hr class="app-email-view-hr mx-n5 mb-2" />
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <span class="btn btn-icon btn-text-secondary rounded-pill me-1"><i
                      class="ti ti-trash ti-md cursor-pointer"></i></span>
                  <span class="btn btn-icon btn-text-secondary rounded-pill me-1"><i
                      class="ti ti-mail ti-md cursor-pointer" data-bs-toggle="sidebar"
                      data-target="#app-email-view"></i></span>
                  <div class="dropdown">
                    <button class="btn btn-icon btn-text-secondary rounded-pill p-0 me-1" type="button"
                      id="dropdownMenuFolderTwo" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-folder ti-md"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuFolderTwo">
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="ti ti-alert-octagon me-1"></i>
                        <span class="align-middle">Spam</span>
                      </a>
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="ti ti-edit me-1"></i>
                        <span class="align-middle">Draft</span>
                      </a>
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="ti ti-trash me-1"></i>
                        <span class="align-middle">Trash</span>
                      </a>
                    </div>
                  </div>
                  <div class="dropdown">
                    <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button"
                      id="dropdownLabelTwo" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-tag ti-md"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLabelTwo">
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="badge badge-dot bg-success me-1"></i>
                        <span class="align-middle">Workshop</span>
                      </a>
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="badge badge-dot bg-primary me-1"></i>
                        <span class="align-middle">Company</span>
                      </a>
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="badge badge-dot bg-info me-1"></i>
                        <span class="align-middle">Important</span>
                      </a>
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <span class="btn btn-icon btn-text-secondary rounded-pill p-0">
                    <i class="ti ti-star ti-md"></i>
                  </span>
                  <div class="dropdown ms-1">
                    <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button"
                      id="dropdownMoreOptions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-dots-vertical ti-md"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMoreOptions">
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="ti ti-mail me-1"></i>
                        <span class="align-middle">Mark as unread</span>
                      </a>
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="ti ti-mail-opened me-1"></i>
                        <span class="align-middle">Mark as read</span>
                      </a>
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="ti ti-star ti-sm me-1"></i>
                        <span class="align-middle">Add star</span>
                      </a>
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="ti ti-calendar me-1"></i>
                        <span class="align-middle">Create Event</span>
                      </a>
                      <a class="dropdown-item" href="javascript:void(0)">
                        <i class="ti ti-volume-off me-1"></i>
                        <span class="align-middle">Mute</span>
                      </a>
                      <a class="dropdown-item d-sm-none d-block" href="javascript:void(0)">
                        <i class="ti ti-printer me-1"></i>
                        <span class="align-middle">Print</span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <hr class="m-0" />
            <!-- Email View : Content-->
            <div class="app-email-view-content py-4">
              <p class="email-earlier-msgs text-center cursor-pointer mb-12">1 Earlier Message</p>
              <!-- Email View : Previous mails-->
              <div class="card email-card-prev mx-sm-6 mx-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap border-bottom">
                  <div class="d-flex align-items-center mb-sm-0 mb-3">
                    <img src="/vuexy/assets/img/avatars/2.png" alt="user-avatar"
                      class="flex-shrink-0 rounded-circle me-4" height="38" width="38" />
                    <div class="flex-grow-1 ms-1">
                      <h6 class="m-0 fw-normal">Ross Geller</h6>
                      <small class="text-body">rossGeller@email.com</small>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <p class="mb-0 me-4 text-muted">June 20th 2020, 08:30 AM</p>
                    <span class="btn btn-icon btn-text-secondary rounded-pill"><i
                        class="ti ti-paperclip ti-md cursor-pointer"></i></span>
                    <span class="btn btn-icon btn-text-secondary rounded-pill"><i
                        class="email-list-item-bookmark ti ti-star ti-md cursor-pointer"></i></span>
                    <div class="dropdown">
                      <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button"
                        id="dropdownEmailOne" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-md"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownEmailOne">
                        <a class="dropdown-item scroll-to-reply" href="javascript:void(0)">
                          <i class="ti ti-corner-up-left me-1"></i>
                          <span class="align-middle">Reply</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-corner-up-right me-1"></i>
                          <span class="align-middle">Forward</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-alert-octagon me-1"></i>
                          <span class="align-middle">Report</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body pt-5">
                  <p class="fw-medium">Greetings!</p>
                  <p>
                    It is a long established fact that a reader will be distracted by the readable content of a
                    page when looking at its layout.The point of using Lorem Ipsum is that it has a more-or-less
                    normal distribution of letters, as opposed to using 'Content here, content here',making it
                    look like readable English.
                  </p>
                  <p>
                    There are many variations of passages of Lorem Ipsum available, but the majority have
                    suffered alteration in some form, by injected humour, or randomised words which don't look
                    even slightly believable.
                  </p>
                  <p class="mb-0">Sincerely yours,</p>
                  <p class="fw-medium mb-0">Envato Design Team</p>
                  <hr />
                  <p class="text-muted mb-2">Attachments</p>
                  <div class="cursor-pointer">
                    <i class="ti ti-file"></i>
                    <span class="align-middle ms-1">report.xlsx</span>
                  </div>
                </div>
              </div>
              <!-- Email View : Last mail-->
              <div class="card email-card-last mx-sm-6 mx-3 mt-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap border-bottom">
                  <div class="d-flex align-items-center mb-sm-0 mb-3">
                    <img src="/vuexy/assets/img/avatars/1.png" alt="user-avatar"
                      class="flex-shrink-0 rounded-circle me-4" height="38" width="38" />
                    <div class="flex-grow-1 ms-1">
                      <h6 class="m-0 fw-normal">Chandler Bing</h6>
                      <small class="text-body">iAmAhoot@email.com</small>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <p class="mb-0 me-4 text-muted">June 20th 2020, 08:10 AM</p>
                    <span class="btn btn-icon btn-text-secondary rounded-pill"><i
                        class="ti ti-paperclip ti-md cursor-pointer"></i></span>
                    <span class="btn btn-icon btn-text-secondary rounded-pill"><i
                        class="email-list-item-bookmark ti ti-star ti-md cursor-pointer"></i></span>
                    <div class="dropdown">
                      <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                        id="dropdownEmailTwo" data-bs-toggle="dropdown" aria-expanded="true">
                        <i class="ti ti-dots-vertical ti-md"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownEmailTwo">
                        <a class="dropdown-item scroll-to-reply" href="javascript:void(0)">
                          <i class="ti ti-corner-up-left me-1"></i>
                          <span class="align-middle">Reply</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-corner-up-right me-1"></i>
                          <span class="align-middle">Forward</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-alert-octagon me-1"></i>
                          <span class="align-middle">Report</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body pt-5">
                  <p class="fw-medium">Greetings!</p>
                  <p>
                    It is a long established fact that a reader will be distracted by the readable content of a
                    page when looking at its layout.The point of using Lorem Ipsum is that it has a more-or-less
                    normal distribution of letters, as opposed to using 'Content here, content here',making it
                    look like readable English.
                  </p>
                  <p>
                    There are many variations of passages of Lorem Ipsum available, but the majority have
                    suffered alteration in some form, by injected humour, or randomised words which don't look
                    even slightly believable.
                  </p>
                  <p class="mb-0">Sincerely yours,</p>
                  <p class="fw-medium mb-0">Envato Design Team</p>
                  <hr />
                  <p class="text-muted mb-2">Attachments</p>
                  <div class="cursor-pointer">
                    <i class="ti ti-file"></i>
                    <span class="align-middle ms-1">report.xlsx</span>
                  </div>
                </div>
              </div>
              <!-- Email View : Reply mail-->
              <div class="email-reply card mt-4 mx-sm-6 mx-3 mb-4">
                <h6 class="card-header border-0 fw-normal pb-4">Reply to Ross Geller</h6>
                <div class="card-body pt-0 ps-3">
                  <div class="d-flex justify-content-start">
                    <div class="email-reply-toolbar border-0 w-100 ps-0 pb-4">
                      <span class="ql-formats me-0">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-list" value="ordered"></button>
                        <button class="ql-list" value="bullet"></button>
                        <button class="ql-link"></button>
                        <button class="ql-image"></button>
                      </span>
                    </div>
                  </div>
                  <div class="email-reply-editor"></div>
                  <div class="d-flex justify-content-end align-items-center mt-4">
                    <div class="cursor-pointer btn btn-text-secondary text-secondary me-4">
                      <i class="ti ti-paperclip ti-16px text-heading me-2"></i>
                      <span class="align-middle">Attachments</span>
                    </div>
                    <button class="btn btn-primary">
                      <span class="align-middle">Send</span>
                      <i class="ti ti-send ti-16px ms-2 scaleX-n1-rtl"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Email View -->
        </div>

        @include('admin.clients.messages.partials.send-message')
      </div>
    </div>
    <!-- / Content -->

    <div class="content-backdrop fade"></div>
  </div>
  <!-- Content wrapper -->
@endsection

@section('scripts')
  <script>
    const receivedMessages = @json($receivedMessages);
    const sentMessages = @json($sentMessages);
    console.log('Received Messages:', receivedMessages);
    console.log('Sent Messages:', sentMessages);
  </script>
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/block-ui/block-ui.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-email.js"></script>
@stop
