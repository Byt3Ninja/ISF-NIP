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
              @foreach ($pastHackathons as $hackathon)
                @include('admin.hackathon.partials.hackathon-item', ['hackathon' => $hackathon, 'l' => $l])
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Include original footer -->
  <footer class="footer mt-auto py-3 bg-dark text-white">
    <div class="container text-center">
      <span>
        © 2024 {{ $l == 'en' ? 'Incubator Hackathon Platform. All rights reserved.' : 'منصة المسرعة للهاكثونات. جميع الحقوق محفوظة.' }}
      </span>
    </div>
  </footer>

  <!-- Apply Modal -->
  @include('admin.hackathon.partials.hackathon-apply', ['l' => $l])

  <!-- Terms Modal -->
  @include('admin.hackathon.partials.hackathon-terms', ['l' => $l])

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