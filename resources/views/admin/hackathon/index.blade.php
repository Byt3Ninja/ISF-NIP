<!DOCTYPE html>
<html 
  lang="{{ $l }}" 
  dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" 
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-compact" 
  data-theme="theme-default" 
  data-assets-path="/vuexy/assets/"
  data-template="vertical-menu-template" 
  data-style="light"
>

<head>
  @include('admin.includes.head')

  <!-- Additional Styles -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
</head>

@php
  $hackathons = [
    [
      'title' => 'Future of AI Hackathon',
      'date' => 'March 15 - 17, 2024',
      'location' => 'Virtual',
      'status' => 'Upcoming',
      'description' => 'Innovate solutions using artificial intelligence to address urgent industrial challenges.',
      'tags' => ['AI', 'Big Data'],
    ],
    [
      'title' => 'Sustainability Hackathon',
      'date' => 'April 20 - 22, 2024',
      'location' => 'Hybrid (Riyadh & Online)',
      'status' => 'Ongoing',
      'description' => 'Develop innovative solutions for sustainable development goals.',
      'tags' => ['Sustainability', 'Green Tech'],
    ],
    [
      'title' => 'HealthTech Hackathon',
      'date' => 'May 10 - 12, 2024',
      'location' => 'Virtual',
      'status' => 'Upcoming',
      'description' => 'Create innovative health tech solutions to improve patient care.',
      'tags' => ['HealthTech', 'AI'],
    ],
    [
      'title' => 'FinTech Innovation Challenge',
      'date' => 'June 5 - 7, 2024',
      'location' => 'Hybrid (Dubai & Online)',
      'status' => 'Upcoming',
      'description' => 'Develop cutting-edge financial technology solutions.',
      'tags' => ['FinTech', 'Blockchain'],
    ],
    [
      'title' => 'Smart Cities Hackathon',
      'date' => 'July 15 - 17, 2024',
      'location' => 'Virtual',
      'status' => 'Upcoming',
      'description' => 'Innovate solutions for smart city challenges.',
      'tags' => ['Smart Cities', 'IoT'],
    ],
  ];
@endphp

<body>
  <!-- Include original navbar -->
  <nav class="navbar navbar-expand-lg navbar-light sticky-top bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="/dashboard/hackathon">
        <i class="ti ti-tournament text-primary me-2"></i> {{ $l == 'en' ? 'Hackathon Platform' : 'منصة الهاكثونات' }}
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="/dashboard/hackathon">
              <i class="ti ti-home"></i>
              {{ $l == 'en' ? 'Home' : 'الرئيسية' }}</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#past-hackathons"><i class="ti ti-calendar-check"></i> {{ $l == 'en' ? 'Past
              Hackathons' : 'الهاكثونات السابقة' }}</a>
          </li>
        </ul>
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="ti ti-user-circle"></i> {{ $l == 'en' ? 'Hello, Entrepreneur' : 'مرحباً، رائد أعمال' }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
              <li><a class="dropdown-item" href="#"><i class="ti ti-user"></i> {{ $l == 'en' ? 'My Profile' : 'ملفي
                  الشخصي' }}</a></li>
              <li><a class="dropdown-item" href="#"><i class="ti ti-users"></i> {{ $l == 'en' ? 'My Team' : 'فريقي'
                  }}</a></li>
              <li><a class="dropdown-item" href="/dashboard"><i class="ti ti-dashboard"></i> {{ $l == 'en' ? 'Admin
                  Dashboard' : 'لوحة تحكم الإدارة' }}</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="#"><i class="ti ti-logout"></i> {{ $l == 'en' ? 'Logout' : 'تسجيل
                  الخروج' }}</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Header Section -->
  <header class="mb-8 text-white text-center p-12" style="background: linear-gradient(180deg, #7367f0 0%, #6610f2 100%);">
    <div class="container">
      <h1 class="display-4 fw-bold text-white">
        {{ $l == 'en' ? 'Innovate, Collaborate, and Propel into the Future!' : 'ابتكر، تعاون، وانطلق نحو المستقبل!' }}
      </h1>
      <p class="lead">
        {{ $l == 'en' ? 'Join our virtual and hybrid hackathons, develop solutions to real-world problems, and qualify for advanced support stages.' : 'انضم إلى هاكثوناتنا الافتراضية والهجينة، طور حلولاً لمشكلات حقيقية، وتأهل لمراحل دعم متقدمة.' }}
      </p>
      <a href="#upcoming-hackathons" class="btn btn-label-primary btn-lg fw-bold mt-3">
       {{ $l == 'en' ? 'Discover Hackathons' : 'اكتشف الهاكثونات' }} <i class="ti ti-circle-arrow-down ti-md ms-2"></i>
      </a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="overflow-x-hidden mx-auto" style="width: 90%;">

    <!-- Filter Section -->
    <div class="row mb-8" id="upcoming-hackathons">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label fw-bold">{{ $l == 'en' ? 'Search' : 'البحث' }}</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="ti ti-search"></i></span>
                  <input type="text" class="form-control"
                    placeholder="{{ $l == 'en' ? 'Search for hackathon...' : 'ابحث عن هاكثون...' }}">
                </div>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">{{ $l == 'en' ? 'Type' : 'النوع' }}</label>
                <select class="form-select select2">
                  <option selected>{{ $l == 'en' ? 'All Types' : 'جميع الأنواع' }}</option>
                  <option value="1">{{ $l == 'en' ? 'Virtual' : 'افتراضي' }}</option>
                  <option value="2">{{ $l == 'en' ? 'Hybrid' : 'هجين' }}</option>
                  <option value="3">{{ $l == 'en' ? 'In-Person' : 'حضوري' }}</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">{{ $l == 'en' ? 'Theme' : 'المحور' }}</label>
                <select class="form-select select2">
                  <option selected>{{ $l == 'en' ? 'All Themes' : 'جميع المحاور' }}</option>
                  <option value="1">{{ $l == 'en' ? 'FinTech' : 'التقنية المالية' }}</option>
                  <option value="2">{{ $l == 'en' ? 'AI' : 'الذكاء الاصطناعي' }}</option>
                  <option value="3">{{ $l == 'en' ? 'Sustainability' : 'الاستدامة' }}</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="ti ti-filter me-2"></i>
                  {{ $l == 'en' ? 'Filter' : 'تصفية' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming & Ongoing Hackathons -->
    <div class="row">
      <div class="col-12">
        <h5 class="fw-bold">
          <i class="ti ti-calendar-event ti-md me-1"></i> {{ $l == 'en' ? 'Upcoming & Ongoing Hackathons' : 'الهاكثونات القادمة والجارية' }}
        </h5>
      </div>
    </div>

    <div class="row g-4 mb-8">
      @foreach ($hackathons as $hackathon)
        <div class="col-lg-4 col-md-6">
          @include('admin.hackathon.partials.hackathon-card', ['hackathon' => $hackathon, 'l' => $l])
        </div>
      @endforeach
    </div>

    <!-- Past Hackathons -->
    <div class="row">
      <div class="col-12">
        <h5 class="fw-bold">
          <i class="ti ti-calendar-check ti-md me-1"></i> {{ $l == 'en' ? 'Past Hackathons' : 'الهاكثونات السابقة' }}
        </h5>
      </div>
    </div>

    <div class="row g-4 mb-12">
      <div class="col-12">
        <div class="card">
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <a href="#"
                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4">
                <div>
                  <h6 class="mb-1 fw-semibold">
                    {{ $l == 'en' ? 'FinTech Hackathon 2023' : 'هاكثون التقنية المالية 2023' }}
                  </h6>
                  <small class="text-muted">
                    <i class="ti ti-calendar me-1"></i>
                    {{ $l == 'en' ? 'November 10 - 12, 2023' : '10 - 12 نوفمبر 2023' }}
                  </small>
                </div>
                <div class="d-flex align-items-center">
                  <span class="badge bg-label-success me-2">{{ $l == 'en' ? 'Completed' : 'مكتمل' }}</span>
                  <span class="badge bg-label-warning">
                    <i class="ti ti-trophy me-1"></i>
                    {{ $l == 'en' ? 'View Results' : 'عرض النتائج' }}
                  </span>
                </div>
              </a>
              <a href="#"
                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4">
                <div>
                  <h6 class="mb-1 fw-semibold">
                    {{ $l == 'en' ? 'Mobile Apps Challenge 2023' : 'تحدي تطبيقات الموبايل 2023' }}
                  </h6>
                  <small class="text-muted">
                    <i class="ti ti-calendar me-1"></i>
                    {{ $l == 'en' ? 'September 15 - 17, 2023' : '15 - 17 سبتمبر 2023' }}
                  </small>
                </div>
                <div class="d-flex align-items-center">
                  <span class="badge bg-label-success me-2">{{ $l == 'en' ? 'Completed' : 'مكتمل' }}</span>
                  <span class="badge bg-label-warning">
                    <i class="ti ti-trophy me-1"></i>
                    {{ $l == 'en' ? 'View Results' : 'عرض النتائج' }}
                  </span>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Include original footer -->
  <footer class="footer mt-auto py-3 bg-dark text-white">
    <div class="container text-center">
      <span>© 2024 {{ $l == 'en' ? 'Incubator Hackathon Platform. All rights reserved.' : 'منصة المسرعة للهاكثونات. جميع
        الحقوق محفوظة.' }}</span>
    </div>
  </footer>

  <!-- Apply Modal -->
  <div class="modal fade" id="applyModal1" tabindex="-1" aria-labelledby="applyModal1Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="applyModal1Label">
            <i class="ti ti-send me-2"></i>
            {{ $l == 'en' ? 'Apply for Future of AI Hackathon' : 'تقديم طلب الانضمام لهاكثون مستقبل الذكاء الاصطناعي' }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="ti ti-info-circle me-2"></i>
            <div>
              {{ $l == 'en' ? 'You are about to apply for the "Future of AI Hackathon". Please review the terms and
              conditions before proceeding.' : 'أنت على وشك تقديم طلب للانضمام إلى "هاكثون مستقبل الذكاء الاصطناعي".
              يرجى مراجعة شروط وأحكام الهاكثون قبل المتابعة.' }}
            </div>
          </div>

          <form id="hackathonApplicationForm">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="applicantName" class="form-label fw-bold">{{ $l == 'en' ? 'Full Name' : 'الاسم الكامل' }}</label>
                <input type="text" class="form-control" id="applicantName"
                  value="{{ $l == 'en' ? 'Entrepreneur (From Profile)' : 'رائد أعمال (من الملف الشخصي)' }}" readonly>
              </div>
              <div class="col-md-6 mb-3">
                <label for="applicantEmail" class="form-label fw-bold">{{ $l == 'en' ? 'Email' : 'البريد الإلكتروني' }}</label>
                <input type="email" class="form-control" id="applicantEmail"
                  value="{{ $l == 'en' ? 'user@example.com (From Profile)' : 'user@example.com (من الملف الشخصي)' }}"
                  readonly>
              </div>
            </div>

            <div class="mb-3">
              <label for="motivation" class="form-label fw-bold">{{ $l == 'en' ? 'Why do you want to participate? (Optional)' :
                'لماذا ترغب في المشاركة؟ (اختياري)' }}</label>
              <textarea class="form-control" id="motivation" rows="3"
                placeholder="{{ $l == 'en' ? 'Tell us about your motivation...' : 'أخبرنا عن دافعك...' }}"></textarea>
            </div>

            <div class="form-check mb-3">
              <input type="checkbox" class="form-check-input" id="externalTeamCheck">
              <label class="form-check-label" for="externalTeamCheck">
                {{ $l == 'en' ? 'I am part of an external team (if allowed)' : 'أنا جزء من فريق مشارك من خارج المسرعة
                (إذا كان مسموحاً)' }}
              </label>
            </div>

            <div id="externalTeamDetails" class="mb-3" style="display:none;">
              <label for="externalTeamName" class="form-label fw-bold">{{ $l == 'en' ? 'External Team Name' : 'اسم الفريق
                الخارجي' }}</label>
              <input type="text" class="form-control" id="externalTeamName"
                placeholder="{{ $l == 'en' ? 'Team name' : 'اسم الفريق' }}">
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" value="" id="termsAndConditions" required>
              <label class="form-check-label" for="termsAndConditions">
                {{ $l == 'en' ? 'I agree to the' : 'أوافق على' }}
                <a href="#terms" data-bs-toggle="modal" data-bs-target="#termsModal" class="text-primary">
                  {{ $l == 'en' ? 'Terms and Conditions' : 'الشروط والأحكام' }}
                </a>
                {{ $l == 'en' ? 'of the hackathon.' : 'الخاصة بالهاكثون.' }}
              </label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i>
            {{ $l == 'en' ? 'Close' : 'إغلاق' }}
          </button>
          <button type="button" class="btn btn-primary" onclick="submitApplication()">
            <i class="ti ti-check me-1"></i>
            {{ $l == 'en' ? 'Confirm Application' : 'تأكيد التقديم' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Terms Modal -->
  <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="termsModalLabel">
            <i class="ti ti-file-text me-2"></i>
            {{ $l == 'en' ? 'Hackathon Terms and Conditions' : 'شروط وأحكام الهاكثون' }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>{{ $l == 'en' ? 'This is placeholder text for terms and conditions...' : 'هذا نص وهمي للشروط والأحكام...'
            }}</p>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam tincidunt arcu vel arcu fermentum, eget
            accumsan dolor dignissim. Nam eget quam mauris. In hac habitasse platea dictumst.</p>
          <ol>
            <li>{{ $l == 'en' ? 'First term: ...' : 'البند الأول: ...' }}</li>
            <li>{{ $l == 'en' ? 'Second term: ...' : 'البند الثاني: ...' }}</li>
            <li>{{ $l == 'en' ? 'Third term: ...' : 'البند الثالث: ...' }}</li>
          </ol>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-target="#applyModal1" data-bs-toggle="modal"
            data-bs-dismiss="modal">
            <i class="ti ti-check me-1"></i>
            {{ $l == 'en' ? 'I Agree and Return to Application' : 'أوافق وأعود للتقديم' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Core JS -->
  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
  <script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
  <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
  <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
  <script src="/vuexy/assets/vendor/js/menu.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
  <script src="/vuexy/assets/js/main.js"></script>
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>

  <!-- Custom JS -->
  <script src="/vuexy/assets/js/forms-selects.js"></script>

  <script>
    // Initialize tooltips
    $(document).ready(function() {
      $('[data-bs-toggle="tooltip"]').tooltip();
      
      // Handle external team checkbox
      $('#externalTeamCheck').on('change', function() {
        $('#externalTeamDetails').toggle(this.checked);
      });
    });

    function submitApplication() {
      // Basic validation
      if (!document.getElementById('termsAndConditions').checked) {
        // Using SweetAlert2 for better UX
        Swal.fire({
          icon: 'warning',
          title: '{{ $l == 'en' ? 'Terms Required' : 'الشروط مطلوبة' }}',
          text: '{{ $l == 'en' ? 'Please agree to the terms and conditions.' : 'يرجى الموافقة على الشروط والأحكام.' }}',
          confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}'
        });
        return;
      }
      
      // Show success message
      Swal.fire({
        icon: 'success',
        title: '{{ $l == 'en' ? 'Application Submitted!' : 'تم إرسال الطلب!' }}',
        text: '{{ $l == 'en' ? 'Your application has been submitted successfully! You will be redirected to the hackathon details page.' : 'تم إرسال طلبك بنجاح! سيتم توجيهك إلى صفحة تفاصيل الهاكثون.' }}',
        confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}'
      }).then((result) => {
        // Close modal
        $('#applyModal1').modal('hide');
        // Redirect or update UI
        // window.location.href = "hackathon-details.html?id=1&applied=true";
      });
    }
  </script>
</body>

</html>