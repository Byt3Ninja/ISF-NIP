@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h3 class="fw-bold mb-4">Program Details</h3>
    <div class="card card-body">
      <div class="d-flex flex-column flex-md-row align-items-start gap-3">
        <img src="/images/background/logo.png" class="rounded object-fit-contain border shadow-xs"
          style="width: 110px; height: 110px;" alt="Logo">
        <div class="flex-grow-1">
          <h5 class="fw-semibold line-clamp-2 mb-3 w-100 w-md-75">
            Social entrepreneurship accelerator in the fields of energy and sustainability
          </h5>
          <span class="badge bg-light text-dark fw-semibold rounded-pill mb-1">Growth Labs incubator</span>
          <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-4 mt-3">
            <div class="d-flex align-items-center gap-1">
              <i class="ti ti-building-skyscraper text-primary"></i>
              <span>Startups</span><span class="fw-bold ms-1">5</span>
            </div>
            <div class="d-flex align-items-center gap-1">
              <i class="ti ti-picture-in-picture-top text-primary"></i>
              <span>Comming Sessions</span><span class="fw-bold ms-1">5</span>
            </div>
            <div class="d-flex align-items-center gap-1">
              <i class="ti ti-archive text-primary"></i>
              <span>Sessions Without Tasks</span><span class="fw-bold ms-1">5</span>
            </div>
          </div>
        </div>
        <div class="text-start text-md-end">
          <span class="badge bg-label-secondary text-dark rounded-pill">
            <i class="ti ti-star-filled text-warning me-1"></i> 4.8
          </span>
        </div>
      </div>

      <div class="mt-4 p-3 bg-label-light mb-4 rounded">
        <h6 class="fw-bold mb-1">
          <i class="ti ti-message-2 text-primary me-1"></i>
          Description
        </h6>
        <p class="text-muted mb-0">
          Lorem ipsum dolor sit amet consectetur. Laoreet ac sagittis sem porttitor vel. Eget imperdiet sit vitae
          volutpat suspendisse cursus etiam.
        </p>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex gap-3">
          <div class="d-flex align-items-center gap-2">
            <i class="ti ti-calendar text-primary"></i>
            <strong>From:</strong>
            <strong>2024-03-01</strong>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="ti ti-calendar text-primary"></i>
            <strong>To:</strong>
            <strong>2024-03-01</strong>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-2">
        <small class="text-muted me-2">88%</small>
        <div class="progress flex-grow-1 me-2" style="height: 8px;">
          <div class="progress-bar progress-bar-animated bg-primary" style="width: 88%;"></div>
        </div>
        <div class="text-end">
          <small class="text-muted">1/8</small>
        </div>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header border-bottom">
        <ul class="nav nav-pills">
          <li class="nav-item">
            <a class="nav-link active" id="mentorship-tab" data-bs-toggle="tab" href="#mentorship-sessions">Mentorship
              Sessions</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="guideline-tab" data-bs-toggle="tab" href="#program-guideline">Program Guideline</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="policy-tab" data-bs-toggle="tab" href="#financial-policy">Financial Policy</a>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content px-0">
          <div class="tab-pane fade show active" id="mentorship-sessions" role="tabpanel"
            aria-labelledby="mentorship-tab">
            <h5 class="fw-bold mb-3">My Next Mentorship Sessions</h5>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="bg-label-light">
                  <tr>
                    <th>Company</th>
                    <th>User</th>
                    <th>Date / Time</th>
                    <th>Admin Notes</th>
                    <th>Meeting Link</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <a href="/company_profile" class="d-flex align-items-center gap-2">
                        <img src="/images/background/logo.png" alt="Company Logo" class="rounded-circle border"
                          style="width: 40px; height: 40px;">
                        <span class="text-dark line-clamp-2">Company Name</span>
                      </a>
                    </td>
                    <td>
                      <a href="/show/myProfile" class="d-flex align-items-center gap-2">
                        <img src="/icons/account.svg" alt="User Avatar" class="rounded-circle"
                          style="width: 40px; height: 40px;">
                        <span class="text-dark line-clamp-2">User Name</span>
                      </a>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-calendar-event text-primary"></i>
                        <span>2024-03-01 10:00 AM</span>
                        <small class="text-muted">(1 hour)</small>
                      </div>
                    </td>
                    <td>Admin notes go here.</td>
                    <td>
                      <a href="#" class="btn btn-sm btn-light">
                        <i class="ti ti-video me-2"></i>
                        <span>Join Meeting</span>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <a href="/company_profile" class="d-flex align-items-center gap-2">
                        <img src="/images/background/logo.png" alt="Company Logo" class="rounded-circle border"
                          style="width: 40px; height: 40px;">
                        <span class="text-dark line-clamp-2">Company Name</span>
                      </a>
                    </td>
                    <td>
                      <a href="/show/myProfile" class="d-flex align-items-center gap-2">
                        <img src="/icons/account.svg" alt="User Avatar" class="rounded-circle"
                          style="width: 40px; height: 40px;">
                        <span class="text-dark line-clamp-2">User Name</span>
                      </a>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-calendar-event text-primary"></i>
                        <span>2024-03-01 10:00 AM</span>
                        <small class="text-muted">(1 hour)</small>
                      </div>
                    </td>
                    <td>Admin notes go here.</td>
                    <td>
                      <a href="#" class="btn btn-sm btn-light">
                        <i class="ti ti-video me-2"></i>
                        <span>Join Meeting</span>
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="program-guideline" role="tabpanel" aria-labelledby="guideline-tab">
            <div class="mb-6 pb-4 border-bottom">
              <h5 class="fw-bold mb-3">Program Guideline</h5>
              <p class="text-muted mb-0">
                Lorem ipsum dolor sit amet consectetur. Laoreet ac sagittis sem porttitor vel. Eget imperdiet sit vitae
                volutpat suspendisse cursus etiam.
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Molestiae sequi commodi non fuga aliquam
                inventore ipsum, animi officia asperiores sed tempora veritatis laboriosam porro perferendis!
                Consectetur veniam assumenda officia explicabo.
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nisi nemo ipsam porro beatae sunt. Similique
                provident perferendis magni, repellendus quibusdam quos quisquam asperiores dolorem, ipsum id optio,
                tenetur eaque distinctio!
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Aperiam laudantium, repellat dolor illo nihil
                totam alias veniam, placeat ullam praesentium natus eveniet ut pariatur reiciendis rem, nesciunt
                accusamus ratione voluptatum?
              </p>
            </div>
            <div>
              <h5 class="fw-bold mb-3">Documents</h5>
              <ul class="list-group">
                <li class="list-group-item d-flex align-items-center gap-3">
                  <i class="ti ti-file-text text-primary"></i>
                  <a href="/documents/document1.pdf" class="text-dark" target="_blank">Document1.pdf</a>
                </li>
                <li class="list-group-item d-flex align-items-center gap-3">
                  <i class="ti ti-file-text text-primary"></i>
                  <a href="/documents/document2.pdf" class="text-dark" target="_blank">Document2.pdf</a>
                </li>
                <li class="list-group-item d-flex align-items-center gap-3">
                  <i class="ti ti-file-text text-primary"></i>
                  <a href="/documents/document3.pdf" class="text-dark" target="_blank">Document3.pdf</a>
                </li>
              </ul>
            </div>
          </div>
          <div class="tab-pane fade" id="financial-policy" role="tabpanel" aria-labelledby="policy-tab">
            <div class="mb-6 pb-4 border-bottom">
              <h5 class="fw-bold mb-3">
                Financial Policy
                <span class="badge bg-label-primary text-white ms-2">$10,000</span>
              </h5>
              <p class="text-muted mb-0">
                Lorem ipsum dolor sit amet consectetur. Laoreet ac sagittis sem porttitor vel. Eget imperdiet sit vitae
                volutpat suspendisse cursus etiam.
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Molestiae sequi commodi non fuga aliquam
                inventore ipsum, animi officia asperiores sed tempora veritatis laboriosam porro perferendis!
                Consectetur veniam assumenda officia explicabo.
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nisi nemo ipsam porro beatae sunt. Similique
                provident perferendis magni, repellendus quibusdam quos quisquam asperiores dolorem, ipsum id optio,
                tenetur eaque distinctio!
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Aperiam laudantium, repellat dolor illo nihil
                totam alias veniam, placeat ullam praesentium natus eveniet ut pariatur reiciendis rem, nesciunt
                accusamus ratione voluptatum?
              </p>
            </div>
            <div>
              <h5 class="fw-bold mb-3">Documents</h5>
              <ul class="list-group">
                <li class="list-group-item d-flex align-items-center gap-3">
                  <i class="ti ti-file-text text-primary"></i>
                  <a href="/documents/document1.pdf" class="text-dark" target="_blank">Document1.pdf</a>
                </li>
                <li class="list-group-item d-flex align-items-center gap-3">
                  <i class="ti ti-file-text text-primary"></i>
                  <a href="/documents/document2.pdf" class="text-dark" target="_blank">Document2.pdf</a>
                </li>
                <li class="list-group-item d-flex align-items-center gap-3">
                  <i class="ti ti-file-text text-primary"></i>
                  <a href="/documents/document3.pdf" class="text-dark" target="_blank">Document3.pdf</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop