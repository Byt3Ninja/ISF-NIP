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
 <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #f8f9fa; }
        .navbar { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .footer { background-color: #343a40; color: white; padding: 20px 0; text-align: center; }
        .stat-card { text-align: center; }
        .stat-card .display-4 { font-weight: bold; }
        .chart-container { position: relative; margin: auto; height: 350px; width: 100%; background-color: #fff; padding: 1rem; border-radius: .25rem; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); }
        
        .tags-input-wrapper {
            background: white;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        .tag-item {
            background-color: #0d6efd;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin: 0.25rem;
            display: flex;
            align-items: center;
            font-size: 0.9em;
        }
        .tag-item .remove-tag {
            background: none;
            border: none;
            color: white;
            margin-left: 0.5rem; 
            cursor: pointer;
            font-size: 1.1em;
            line-height: 1;
        }
        .tags-input-wrapper input[type="text"] {
            border: none;
            outline: none;
            flex-grow: 1;
            padding: 0.25rem;
            min-width: 150px;
        }
        .dynamic-entry { 
            border: 1px dashed #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: .25rem;
            position: relative;
            background-color: #fff; /* Explicit white background */
        }
        .remove-entry-btn { 
            position: absolute;
            top: 50%; /* Center vertically */
            transform: translateY(-50%);
            left: 10px; /* Adjusted for RTL and padding */
        }
        /* Specific adjustment for remove button column to align button nicely */
        .dynamic-entry .col-md-2.remove-btn-col, .dynamic-entry .col-auto.remove-btn-col {
            display: flex;
            align-items: center; /* Vertically align button if entry uses align-items-start */
            justify-content: center; /* Center button in its column */
        }
        .total-weight {
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 0.5rem;
        }
    </style>
<head>
  @include('admin.includes.head')

  <!-- Additional Styles -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
</head>



<body>


<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.html">
            <i class="bi bi-lightning-charge-fill text-primary"></i> منصة الهاكثونات
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.html"><i class="bi bi-house-door-fill"></i> الرئيسية (عرض المستخدم)</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> مرحباً، [اسم المدير]
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                        <li><a class="dropdown-item" href="admin-dashboard.html"><i class="bi bi-speedometer2"></i> لوحة التحكم الرئيسية</a></li>
                        <li><a class="dropdown-item" href="#manage-users-section"><i class="bi bi-people-fill"></i> إدارة المستخدمين</a></li>
                        <li><a class="dropdown-item" href="#manage-teams-section"><i class="bi bi-diagram-3-fill"></i> إدارة الفرق</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">لوحة تحكم الإدارة</h1>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createHackathonModal">
            <i class="bi bi-plus-circle-fill me-2"></i> إنشاء هاكثون جديد
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4 g-3 row-cols-1 row-cols-sm-2 row-cols-lg-4">
        <div class="col">
            <div class="card stat-card border-primary shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-calendar3-event display-4 text-primary mb-2"></i>
                    <h5 class="card-title">الهاكثونات النشطة</h5>
                    <p class="card-text display-4">3</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card border-success shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-person-check-fill display-4 text-success mb-2"></i>
                    <h5 class="card-title">إجمالي المسجلين</h5>
                    <p class="card-text display-4">1,250</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card border-info shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-people-fill display-4 text-info mb-2"></i>
                    <h5 class="card-title">الفرق المتكونة</h5>
                    <p class="card-text display-4">280</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card border-secondary shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <i class="bi bi-person-video3 display-4 text-secondary mb-2"></i>
                    <h5 class="card-title">إدارة الإرشاد</h5>
                    <p class="mb-2">طلبات جديدة: <span class="badge bg-danger rounded-pill">5</span></p>
                    <div class="mt-auto">
                        <button class="btn btn-sm btn-outline-secondary mt-1" data-bs-toggle="modal" data-bs-target="#manageMentorsModal"><i class="bi bi-person-badge-fill me-1"></i> إدارة المرشدين</button>
                        <button class="btn btn-sm btn-outline-secondary mt-1" data-bs-toggle="modal" data-bs-target="#viewMentorshipRequestsModal"><i class="bi bi-card-list me-1"></i> طلبات الإرشاد</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="chart-container">
                <canvas id="registrationsChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <canvas id="submissionsPerTrackChart"></canvas>
            </div>
        </div>
    </div>
    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="chart-container">
                <canvas id="teamsStatusChart"></canvas>
            </div>
        </div>
         <div class="col-md-6">
            <div class="chart-container">
                <canvas id="hackathonEngagementChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Manage Hackathons Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-list-task me-2"></i> إدارة الهاكثونات</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>اسم الهاكثون</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>المسجلون</th>
                            <th>الفرق</th>
                            <th>المشاريع</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>هاكثون مستقبل الذكاء الاصطناعي</td>
                            <td>15-17 مارس 2024</td>
                            <td><span class="badge bg-warning text-dark">جاري التقديم</span></td>
                            <td>150</td>
                            <td>30</td>
                            <td>-</td>
                            <td>
                                <a href="hackathon-details.html?id=1" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="عرض التفاصيل"><i class="bi bi-eye-fill"></i></a>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createHackathonModal" data-hackathon-id="1" onclick="loadHackathonForEdit(this)" data-bs-toggle="tooltip" title="تعديل"><i class="bi bi-pencil-fill"></i></button>
                                <a href="judging-portal.html?hackathonId=1" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="بوابة التحكيم"><i class="bi bi-clipboard2-check-fill"></i></a>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="حذف"><i class="bi bi-trash-fill"></i></button>
                            </td>
                        </tr>
                        <!-- More hackathons -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Manage Teams Table -->
    <div class="card shadow-sm mb-4" id="manage-teams-section">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-diagram-3-fill me-2"></i> إدارة الفرق المشاركة</h4>
        </div>
        <div class="card-body">
             <div class="row mb-3">
                <div class="col-md-4">
                    <select class="form-select" id="filterTeamsByHackathon">
                        <option selected value="">فلترة حسب الهاكثون...</option>
                        <option value="1">هاكثون مستقبل الذكاء الاصطناعي</option>
                        <option value="2">هاكثون حلول الاستدامة</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="بحث عن فريق (اسم الفريق, اسم القائد)...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>اسم الفريق</th>
                            <th>الهاكثون</th>
                            <th>قائد الفريق</th>
                            <th>عدد الأعضاء</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>فريق الأبطال</td>
                            <td>هاكثون مستقبل الذكاء الاصطناعي</td>
                            <td>أحمد محمد (ahmad@example.com)</td>
                            <td>4/5</td>
                            <td>05 مارس 2024</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#teamDetailsModal" data-team-id="t001"><i class="bi bi-eye-fill"></i> تفاصيل</button>
                                <button class="btn btn-sm btn-outline-warning"><i class="bi bi-envelope-fill"></i> مراسلة</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4" id="manage-users-section">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> إدارة المستخدمين</h4>
        </div>
        <div class="card-body">
            <p class="text-muted">سيتم هنا عرض جدول لإدارة المستخدمين...</p>
        </div>
    </div>

</div>


<!-- Create/Edit Hackathon Modal -->
<div class="modal fade" id="createHackathonModal" tabindex="-1" aria-labelledby="createHackathonModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createHackathonModalLabel">إنشاء هاكثون جديد</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="createHackathonForm">
            <input type="hidden" id="hackathonEditId">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="hackathonName" class="form-label">اسم الهاكثون <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="hackathonName" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="hackathonType" class="form-label">نوع الهاكثون <span class="text-danger">*</span></label>
                    <select class="form-select" id="hackathonType" required>
                        <option value="virtual">افتراضي</option>
                        <option value="hybrid">هجين</option>
                        <option value="physical">حضوري</option>
                    </select>
                </div>
                 <div class="col-md-3 mb-3">
                    <label for="teamCapacity" class="form-label">الحد الأقصى لأعضاء الفريق <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="teamCapacity" min="1" max="10" value="5" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="hackathonDescription" class="form-label">الوصف <span class="text-danger">*</span></label>
                <textarea class="form-control" id="hackathonDescription" rows="3" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="hackathonStartDate" class="form-label">تاريخ البدء <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" id="hackathonStartDate" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="hackathonEndDate" class="form-label">تاريخ الانتهاء <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" id="hackathonEndDate" required>
                </div>
                 <div class="col-md-4 mb-3">
                    <label for="registrationDeadline" class="form-label">الموعد النهائي للتسجيل <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" id="registrationDeadline" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="hackathonThemesInput" class="form-label">المحاور (اضغط Enter أو فاصلة لإضافة محور)</label>
                <div class="tags-input-wrapper" id="hackathonThemesWrapper">
                    <input type="text" id="hackathonThemesInput" placeholder="أضف محور...">
                </div>
                <input type="hidden" id="hackathonThemesActual" name="hackathonThemesActual">
            </div>

            <div class="mb-3">
                <label class="form-label">الجوائز</label>
                <div id="prizesContainer">
                    <!-- Prize entries will be added here -->
                </div>
                <button type="button" class="btn btn-outline-success btn-sm mt-2" id="addPrizeBtn"><i class="bi bi-plus-circle"></i> إضافة جائزة</button>
            </div>

            <div class="mb-3">
                <label class="form-label">معايير التقييم</label>
                <div id="evaluationCriteriaContainer">
                    <!-- Evaluation criteria entries will be added here -->
                </div>
                <button type="button" class="btn btn-outline-info btn-sm mt-2" id="addCriterionBtn"><i class="bi bi-plus-circle"></i> إضافة معيار تقييم</button>
                <div class="total-weight text-end">إجمالي الأوزان: <span id="totalCriteriaWeight">0</span>%</div>
            </div>

            <div class="mb-3">
                <label for="hackathonBannerImage" class="form-label">صورة البانر للهاكثون</label>
                <input class="form-control" type="file" id="hackathonBannerImage" accept="image/*">
            </div>
             <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="" id="allowExternalTeams">
                <label class="form-check-label" for="allowExternalTeams">
                    السماح للفرق من خارج المسرعة بالمشاركة
                </label>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="button" class="btn btn-primary" onclick="saveHackathon()">حفظ الهاكثون</button>
      </div>
    </div>
  </div>
</div>

<!-- Manage Mentors Modal -->
<div class="modal fade" id="manageMentorsModal" tabindex="-1" aria-labelledby="manageMentorsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="manageMentorsModalLabel">إدارة المرشدين</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
            <button class="btn btn-success mb-3"><i class="bi bi-person-plus-fill me-1"></i> إضافة مرشد جديد</button>
            <p>جدول بالمرشدين الحاليين...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<!-- View Mentorship Requests Modal -->
<div class="modal fade" id="viewMentorshipRequestsModal" tabindex="-1" aria-labelledby="viewMentorshipRequestsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewMentorshipRequestsModalLabel">طلبات جلسات الإرشاد</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
           <p>جدول بطلبات الإرشاد من الفرق...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<!-- Team Details Modal -->
<div class="modal fade" id="teamDetailsModal" tabindex="-1" aria-labelledby="teamDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="teamDetailsModalLabel">تفاصيل الفريق: <span id="modal-team-name-detail"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>الهاكثون:</strong> <span id="modal-team-hackathon"></span></p>
        <p><strong>قائد الفريق:</strong> <span id="modal-team-leader"></span></p>
        <h6>الأعضاء:</h6>
        <ul class="list-group" id="modal-team-members-list"></ul>
        <h6 class="mt-3">فكرة المشروع (إذا توفرت):</h6>
        <p id="modal-team-project-idea" class="text-muted">لم يتم تحديد فكرة بعد.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
        <button type="button" class="btn btn-primary"><i class="bi bi-envelope-fill"></i> مراسلة الفريق</button>
      </div>
    </div>
  </div>
</div>

<footer class="footer mt-auto py-3 bg-dark text-white">
    <div class="container text-center">
        <span>© 2024 منصة المسرعة للهاكثونات. جميع الحقوق محفوظة.</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const mockHackathonDb = {
        "1": {
            name: "هاكثون مستقبل الذكاء الاصطناعي",
            type: "virtual",
            teamCapacity: 5,
            description: "وصف هاكثون الذكاء الاصطناعي...",
            startDate: "2024-03-15T09:00",
            endDate: "2024-03-17T18:00",
            registrationDeadline: "2024-03-10T23:59",
            themes: ["الذكاء الاصطناعي", "البيانات الضخمة", "تعلم الآلة"],
            prizes: [
                { rank: "المركز الأول", description: "20,000 ريال + احتضان" },
                { rank: "المركز الثاني", description: "10,000 ريال" }
            ],
            evaluationCriteria: [
                { name: "الابتكار والأصالة", weight: 30, description: "مدى حداثة الفكرة وقدرتها على تقديم حل فريد." },
                { name: "التطبيق العملي", weight: 25, description: "إمكانية تطبيق الحل على أرض الواقع." },
                { name: "جودة النموذج الأولي", weight: 20, description: "مدى اكتمال وفعالية النموذج الأولي." },
                { name: "نموذج العمل", weight: 15, description: "وضوح نموذج العمل المقترح." },
                { name: "أداء الفريق", weight: 10, description: "مدى فعالية عمل الفريق." }
            ],
            allowExternal: true
        }
    };

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    const themesWrapper = document.getElementById('hackathonThemesWrapper');
    const themesInput = document.getElementById('hackathonThemesInput');
    const themesActualInput = document.getElementById('hackathonThemesActual');
    let themes = [];

    function renderThemes() {
        Array.from(themesWrapper.children).forEach(child => {
            if (child !== themesInput) themesWrapper.removeChild(child);
        });
        themes.forEach((theme, index) => {
            const tagElement = document.createElement('span');
            tagElement.classList.add('tag-item');
            tagElement.textContent = theme;
            const removeBtn = document.createElement('button');
            removeBtn.classList.add('remove-tag');
            removeBtn.innerHTML = '×';
            removeBtn.type = 'button';
            removeBtn.onclick = () => { themes.splice(index, 1); renderThemes(); };
            tagElement.appendChild(removeBtn);
            themesWrapper.insertBefore(tagElement, themesInput);
        });
        themesActualInput.value = themes.join(',');
    }
    
    if(themesInput) {
        themesInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const themeText = themesInput.value.trim();
                if (themeText && !themes.includes(themeText)) {
                    themes.push(themeText);
                    themesInput.value = '';
                    renderThemes();
                }
            }
        });
    }

    const prizesContainer = document.getElementById('prizesContainer');
    const addPrizeBtn = document.getElementById('addPrizeBtn');
    
    function createPrizeEntry(rankValue = '', descriptionValue = '') {
        const prizeDiv = document.createElement('div');
        prizeDiv.classList.add('dynamic-entry', 'prize-entry', 'row', 'g-2'); // Removed 'align-items-center' to let button align with inputs by default in its col
        prizeDiv.innerHTML = `
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="prizeRank[]" placeholder="الترتيب (مثال: المركز الأول)" value="${rankValue}" required>
            </div>
            <div class="col-md-7">
                <input type="text" class="form-control form-control-sm" name="prizeDescription[]" placeholder="وصف الجائزة" value="${descriptionValue}" required>
            </div>
            <div class="col-md-2 remove-btn-col"> {/* Added class for specific styling if needed */}
                <button type="button" class="btn btn-outline-danger btn-sm remove-entry-btn w-100"><i class="bi bi-trash"></i></button>
            </div>
        `;
        prizesContainer.appendChild(prizeDiv);
        prizeDiv.querySelector('.remove-entry-btn').addEventListener('click', () => prizeDiv.remove());
    }

    if(addPrizeBtn) addPrizeBtn.addEventListener('click', () => createPrizeEntry());
    
    const criteriaContainer = document.getElementById('evaluationCriteriaContainer');
    const addCriterionBtn = document.getElementById('addCriterionBtn');
    const totalCriteriaWeightEl = document.getElementById('totalCriteriaWeight');

    function calculateTotalWeight() {
        let totalWeight = 0;
        criteriaContainer.querySelectorAll('input[name="criterionWeight[]"]').forEach(input => {
            totalWeight += parseInt(input.value) || 0;
        });
        totalCriteriaWeightEl.textContent = totalWeight;
        if (totalWeight !== 100) {
            totalCriteriaWeightEl.classList.add('text-danger');
            totalCriteriaWeightEl.classList.remove('text-success');
        } else {
            totalCriteriaWeightEl.classList.remove('text-danger');
            totalCriteriaWeightEl.classList.add('text-success');
        }
    }

    function createCriterionEntry(nameVal = '', weightVal = '', descVal = '') {
        const criterionDiv = document.createElement('div');
        criterionDiv.classList.add('dynamic-entry', 'criterion-entry', 'row', 'g-2'); // Using 'align-items-start' can be good if textareas are taller
        criterionDiv.innerHTML = `
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="criterionName[]" placeholder="اسم المعيار" value="${nameVal}" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control form-control-sm criterion-weight-input" name="criterionWeight[]" placeholder="الوزن %" min="0" max="100" value="${weightVal}" required>
            </div>
            <div class="col-md-4">
                <textarea class="form-control form-control-sm" name="criterionDescription[]" rows="1" placeholder="وصف المعيار">${descVal}</textarea>
            </div>
            <div class="col-md-2 remove-btn-col"> {/* Added class for specific styling if needed */}
                <button type="button" class="btn btn-outline-danger btn-sm remove-entry-btn w-100"><i class="bi bi-trash"></i></button>
            </div>
        `;
        criteriaContainer.appendChild(criterionDiv);
        criterionDiv.querySelector('.remove-entry-btn').addEventListener('click', () => {
            criterionDiv.remove();
            calculateTotalWeight();
        });
        criterionDiv.querySelector('.criterion-weight-input').addEventListener('input', calculateTotalWeight);
    }
    if(addCriterionBtn) addCriterionBtn.addEventListener('click', () => {
        createCriterionEntry();
        calculateTotalWeight();
    });

    const createHackathonModalEl = document.getElementById('createHackathonModal');
    createHackathonModalEl.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const hackathonId = button ? button.getAttribute('data-hackathon-id') : null;
        const form = document.getElementById('createHackathonForm');
        form.reset(); 
        themes = []; renderThemes();
        prizesContainer.innerHTML = '';
        criteriaContainer.innerHTML = ''; calculateTotalWeight();

        document.getElementById('createHackathonModalLabel').textContent = "إنشاء هاكثون جديد";
        document.getElementById('hackathonEditId').value = "";

        if (hackathonId && mockHackathonDb[hackathonId]) { 
            document.getElementById('createHackathonModalLabel').textContent = "تعديل الهاكثون";
            document.getElementById('hackathonEditId').value = hackathonId;
            const data = mockHackathonDb[hackathonId];
            document.getElementById('hackathonName').value = data.name;
            document.getElementById('hackathonType').value = data.type;
            document.getElementById('teamCapacity').value = data.teamCapacity;
            document.getElementById('hackathonDescription').value = data.description;
            document.getElementById('hackathonStartDate').value = data.startDate;
            document.getElementById('hackathonEndDate').value = data.endDate;
            document.getElementById('registrationDeadline').value = data.registrationDeadline;
            document.getElementById('allowExternalTeams').checked = data.allowExternal;

            if(data.themes && data.themes.length > 0){ themes = [...data.themes]; renderThemes(); }
            
            if(data.prizes && data.prizes.length > 0){
                data.prizes.forEach(prize => createPrizeEntry(prize.rank, prize.description));
            } else { createPrizeEntry("المركز الأول", ""); createPrizeEntry("المركز الثاني", ""); }

            if(data.evaluationCriteria && data.evaluationCriteria.length > 0){
                data.evaluationCriteria.forEach(crit => createCriterionEntry(crit.name, crit.weight, crit.description));
            } else { 
                createCriterionEntry("الابتكار", 30, "وصف الابتكار...");
                createCriterionEntry("التنفيذ", 40, "وصف التنفيذ...");
                createCriterionEntry("العرض", 30, "وصف العرض...");
            }
            calculateTotalWeight();

        } else { 
            createPrizeEntry("المركز الأول", "");
            createPrizeEntry("المركز الثاني", "");
            createPrizeEntry("المركز الثالث", "");

            createCriterionEntry("الابتكار والأصالة", 30, "مدى حداثة الفكرة وقدرتها على تقديم حل فريد.");
            createCriterionEntry("التطبيق العملي والقابلية للتنفيذ", 25, "إمكانية تطبيق الحل على أرض الواقع وتأثيره المحتمل.");
            createCriterionEntry("جودة النموذج الأولي/العرض", 20, "مدى اكتمال وفعالية النموذج الأولي وطريقة عرضه.");
            createCriterionEntry("نموذج العمل والاستدامة", 15, "وضوح نموذج العمل المقترح وقدرة المشروع على الاستمرار والنمو.");
            createCriterionEntry("أداء الفريق والتعاون", 10, "مدى فعالية عمل الفريق وتعاونه خلال الهاكثون.");
            calculateTotalWeight();
        }
    });

    function loadHackathonForEdit(buttonElement){ /* Handled by show.bs.modal */ }

    function saveHackathon() {
        if (!document.getElementById('hackathonName').value) {
            alert('يرجى إدخال اسم الهاكثون.'); return;
        }
        if (parseInt(totalCriteriaWeightEl.textContent) !== 100) {
            alert('يجب أن يكون إجمالي أوزان معايير التقييم 100%.'); return;
        }

        const themesValue = themes.join(',');
        console.log("Themes to save:", themesValue);

        const prizeRanks = Array.from(document.querySelectorAll('input[name="prizeRank[]"]')).map(input => input.value);
        const prizeDescriptions = Array.from(document.querySelectorAll('input[name="prizeDescription[]"]')).map(input => input.value);
        const prizesData = prizeRanks.map((rank, index) => ({ rank: rank, description: prizeDescriptions[index] }));
        console.log("Prizes to save:", prizesData);

        const criteriaNames = Array.from(document.querySelectorAll('input[name="criterionName[]"]')).map(input => input.value);
        const criteriaWeights = Array.from(document.querySelectorAll('input[name="criterionWeight[]"]')).map(input => input.value);
        const criteriaDescriptions = Array.from(document.querySelectorAll('textarea[name="criterionDescription[]"]')).map(input => input.value);
        const criteriaData = criteriaNames.map((name, index) => ({ name: name, weight: criteriaWeights[index], description: criteriaDescriptions[index] }));
        console.log("Criteria to save:", criteriaData);
        
        const hackathonEditId = document.getElementById('hackathonEditId').value;
        if(hackathonEditId){
            alert('تم تعديل الهاكثون بنجاح (محاكاة).');
        } else {
            alert('تم حفظ الهاكثون بنجاح (محاكاة).');
        }
        
        var createHackathonModalInstance = bootstrap.Modal.getInstance(createHackathonModalEl);
        createHackathonModalInstance.hide();
    }

    const teamDetailsModalEl = document.getElementById('teamDetailsModal');
    if(teamDetailsModalEl){
        teamDetailsModalEl.addEventListener('show.bs.modal', function(event){
            const button = event.relatedTarget;
            const teamId = button.getAttribute('data-team-id');
            const teamsMockData = {
                "t001": { name: "فريق الأبطال", hackathon: "هاكثون مستقبل الذكاء الاصطناعي", leader: "أحمد محمد (ahmad@example.com)", members: ["أحمد محمد", "علي حسن", "فاطمة خالد", "نور ياسر"], projectIdea: "نظام ذكي لتحليل بيانات السوق."},
                "t002": { name: "نجوم البرمجة", hackathon: "هاكثون مستقبل الذكاء الاصطناعي", leader: "سارة علي (sara@example.com)", members: ["سارة علي", "خالد عبدالله", "ريم فهد", "يوسف إبراهيم", "هند ماجد"], projectIdea: "تطبيق لتتبع اللياقة البدنية."}
            };
            const teamData = teamsMockData[teamId];
            if(teamData){
                document.getElementById('modal-team-name-detail').textContent = teamData.name;
                document.getElementById('modal-team-hackathon').textContent = teamData.hackathon;
                document.getElementById('modal-team-leader').textContent = teamData.leader;
                const membersList = document.getElementById('modal-team-members-list');
                membersList.innerHTML = '';
                teamData.members.forEach(member => {
                    const li = document.createElement('li');
                    li.classList.add('list-group-item');
                    li.textContent = member;
                    membersList.appendChild(li);
                });
                document.getElementById('modal-team-project-idea').textContent = teamData.projectIdea || "لم يتم تحديد فكرة بعد.";
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const chartCanvases = ['registrationsChart', 'submissionsPerTrackChart', 'teamsStatusChart', 'hackathonEngagementChart'];
        chartCanvases.forEach(canvasId => {
            const ctx = document.getElementById(canvasId)?.getContext('2d');
            if (ctx) {
                if (canvasId === 'registrationsChart') {
                     new Chart(ctx, {
                        type: 'line', data: { labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'], datasets: [{ label: 'المسجلون الجدد شهرياً', data: [65, 59, 80, 81, 156, 155], borderColor: 'rgb(75, 192, 192)', tension: 0.1, fill: false }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'نمو التسجيلات' } } }
                    });
                }
                 if (canvasId === 'submissionsPerTrackChart') {
                     new Chart(ctx, {
                        type: 'doughnut', data: { labels: ['التقنية المالية', 'الذكاء الاصطناعي', 'الاستدامة'], datasets: [{ data: [120, 90, 75], backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'] }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'المشاريع حسب المحور' } } }
                    });
                }
                if (canvasId === 'teamsStatusChart') {
                    new Chart(ctx, {
                        type: 'bar', data: { labels: ['هاكثون AI', 'هاكثون الاستدامة'], datasets: [{ label: 'فرق مكتملة', data: [25, 18], backgroundColor: 'rgba(75, 192, 192, 0.7)'},{ label: 'فرق غير مكتملة', data: [5, 7], backgroundColor: 'rgba(255, 159, 64, 0.7)'}] }, options: { responsive: true, maintainAspectRatio: false, scales: {x:{stacked:true}, y:{stacked:true}}, plugins: { title: { display: true, text: 'حالة تكوين الفرق' } } }
                    });
                }
                if (canvasId === 'hackathonEngagementChart') {
                    new Chart(ctx, {
                        type: 'bar', data: { labels: ['المسجلون', 'كونوا فرقاً', 'قدموا مشاريع', 'فائزون'], datasets: [{ label: 'مراحل المشاركة', data: [200, 150, 120, 5], backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'] }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'قمع المشاركة' } } }
                    });
                }
            }
        });
    });
</script>
</body>

</html>