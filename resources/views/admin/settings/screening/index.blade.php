@extends('layouts.admin')
@php
  $status = strtolower( $evaluationForm->status );
  // $status = 'published';
@endphp
@section('style')
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
<style>
  /* Tabs Content */
  .tab-content {
    display: none;
    animation: fadeIn 0.3s ease;
  }

  .tab-content.active {
    display: block;
  }


  /* Form Elements */
  #category-input {
    margin-inline-end: 1rem;
  }

  textarea.form-control {
    resize: vertical;
    min-height: 80px;
  }

  /* Evaluation Model Editor Styles */
  #model-editor {
    margin-top: 1.5rem;
  }

  .category-editor {
    background: var(--card-bg);
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  }

  .category-header {
    display: flex;
    flex-wrap: nowrap;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
  }

  .category-header h3 {
    margin: 0;
    flex-grow: 1;
  }

  .sub-criteria-list {
    margin-top: 1rem;
    padding-left: 1rem;
    /* Indent sub-criteria */
    border-left: 2px solid var(--bs-primary);
  }

  .sub-criterion-editor {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
  }

  .sub-criterion-editor:last-child {
    border-bottom: none;
  }

  .sub-criterion-editor .form-label {
    display: none;
    /* Hide labels, use placeholders */
  }

  .sub-criterion-editor input[type="text"] {
    flex-grow: 1;
  }

  .sub-criterion-editor input[type="number"] {
    width: 80px;
    text-align: center;
  }

  .weight-input {
    width: 80px !important;
    /* Fixed width for weight inputs */
    text-align: center;
  }


  .category-actions {
    margin-top: 1rem;
    display: flex;
    justify-content: flex-end;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .sub-criterion-editor {
      flex-wrap: wrap;
    }

    .sub-criterion-editor input[type="number"] {
      width: 100%;
    }

    .weight-input {
      width: 100% !important;
    }
  }
</style>
@stop

@section('content')
@php $types = ['onetoall'=>'Assign One to all users','grouptoall'=>'Assign Group to all users','grouptogroup'=>'Assign Group to group of users']
@endphp
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-body">
      @if ($cstep)
      <section id="multiple-column-form">
        @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if (session()->has('message2'))
        <div class="alert alert-danger alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message2')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <div class="row match-height">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card mb-4">
              <div class="card-header" style="padding-bottom: 15px;">
                <h4 class="card-title">{{ __('cycles.Portal screening list') }}</h4>
              </div>
              <hr>
              <div class="card-body">
                <label class="form-label fs-5">{{ __('cycles.Select step from list to manage') }}</label>
                <fieldset class="d-flex flex-column flex-md-row gap-3">
                  <select id="cstep" class="select2 form-control rounded"
                    style="margin-left: {{ $l == 'ar' ? '10px' : '0' }}; margin-right: {{ $l == 'ar' ? '0' : '10px' }}; width: 60%;"
                    onchange="window.open('/dashboard/'+this.options[this.selectedIndex].value+'/screening','_self');">
                    @foreach ($steps as $step)
                    <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>{{ $step->title }}
                      ({{ __('cycles.Cycle') }} "{{ $step->cycle->title }}" -
                      {{ __('cycles.Start') }} {{ $step->from->format('d-m-Y') ?? '-' }}) -
                      {{ optional(optional($step->cycle)->organization)->name }}</option>
                    @endforeach
                  </select>
                  <div class="input-group-append">
                    <button class="btn btn-label-secondary"
                      onclick="window.location.href='/dashboard/screening/results/{{ $id }}'">
                      {{-- <i class="ti ti-user"></i> --}}
                      <span class="mx-1">{{ __('cycles.Results') }}</span>
                    </button>
                    <a class="btn btn-label-success" href="/dashboard/cycles/{{ $cstep->cycle_id }}">
                      {{-- <i class="ti ti-eye"></i> --}}
                      <span class="mx-1">{{ __('cycles.Cycle') }}</span>
                    </a>
                    <button class="btn btn-label-primary" onclick="DoChange(1)">
                      <i class="ti ti-pencil"></i> <span class="mx-1">{{ __('cycles.Edit') }}</span>
                    </button>
                    <button class="btn btn-label-danger" onclick="DoChange(2)">
                      <i class="ti ti-trash text-danger"></i> <span class="mx-1">{{ __('cycles.Delete') }}</span>
                    </button>
                  </div>
                </fieldset>
                <br />
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card mb-4">
            <div class="card-header">
              <h4 class="card-title success">{{ $cstep->title }}</h4>
              <p>{{ $cstep->description }}</p>
              <small class="text-muted">
                {{ __('cycles.from') }}
                {{ $cstep->from->format('d-m-Y') ?? '-' }} {{ __('cycles.to') }} {{ $cstep->to->format('d-m-Y') ?? '-' }}
              </small>
            </div>
            <hr style="margin:-10px 0 5px 0;">
            <div class="card-body">
              <div class="card-text">
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Cycle') }}</dt>
                  <dd class="col-sm-9">{{ $cstep->cycle->title }} {{ __('cycles.from') }}
                    {{ $cstep->cycle->start->format('d-m-Y') ?? '-' }} {{ __('cycles.to') }}
                    {{ $cstep->cycle->end->format('d-m-Y') ?? '-' }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Form title') }}</dt>
                  <dd class="col-sm-9">{!! $form->title ?? '<span class="danger">' . __('cycles.NoForm') . '</span>' !!}
                  </dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Screening type') }}</dt>
                  <dd class="col-sm-9">
                    {{ __('cycles.' . $types[$cstep->data]) }}
                  </dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Total registered') }}</dt>
                  <dd class="col-sm-9">{{ \App\Company::whereIn('step', [$form->id ?? '', $cstep->id])->count() }}</dd>
                </dl>

                <dl class="row">
                  <dt class="col-sm-3 text-right">{{ __('cycles.Registered Judges') }}</dt>
                  <dd class="col-sm-9">{{ $cstep->judgers }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card">
            <div class="card-header border-bottom border-light mb-4">
              <h4 class="card-title primary">{{ __('cycles.' . $types[$cstep->data]) }}</h4>
              <p>{{ __('cycles.Select judges from the menu') }}:</p>
            </div>
            <div class="card-body">
              <div class="">
                <form action="/dashboard/screening/update" method="POST" enctype="multipart/form-data">
                  {!! csrf_field() !!}
                  <input type="hidden" value="" name="judges" id="judges">
                  <input type="hidden" value="{{ $cstep->id }}" name="step">
                  <input type="hidden" name="fid" value="{{ $form->id ?? '' }}">
                  <div class="form-group">
                    @if ($cstep->data == 'grouptogroup')
                    @foreach (json_decode($cstep->list) ?? [] as $key => $clist)
                    <div class="form-row">
                      <p style="margin:5px;">{{ __('cycles.panel list') . ' ' . $key }}</p>
                      <div class="row col-md-12">
                        <div class="col-md-11">
                          <select name="judges[{{ $key }}][]" class="select2 form-control panel{{ $key }}"
                            multiple="multiple">
                            @foreach ($judges as $judge)
                            <option value="{{ $judge->id }}" {{ in_array($judge->id, is_array($clist) ? $clist : [],
                              true) ? 'selected' : '' }}>{{ $judge->name }}
                              ({{ $judge->email }})</option>
                            @endforeach
                          </select>
                        </div>
                        @php
                        $panel = \App\Screening::where('step_id', $cstep->id)
                        ->where('panel', $key)
                        ->first();
                        if ($panel && $panel->users) {
                        $panel = json_encode($panel->users);
                        } else {
                        $panel = '';
                        }

                        @endphp
                        <input type="hidden" value="{{ $panel }}" name="panel{{ $key }}">
                        <div class="col-md-1"><button type="button" class="btn btn-warning"
                            onclick="openusers({{ $key }})"><i class="ti ti-edit text-info"></i></button></div>
                      </div>
                    </div>
                    @endforeach
                    @php($key = ($key ?? 0) + 1)
                    <div class="form-row">
                      <p style="margin: 5px">{{ __('cycles.panel list') . ' ' . $key }}</p>
                      <div class="row col-md-12">
                        <div class="col-md-11">
                          <select class="select2 form-control panel{{ $key }}"
                            multiple="multiple">
                            @foreach ($judges as $judge)
                            <option value="{{ $judge->id }}">{{ $judge->{'first_name_' . $l} }}
                              {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})</option>
                            @endforeach
                          </select>
                        </div>

                        <div class="col-md-1 float-right"><button class="btn btn-primary" type="submit"><i
                              class="ti ti-plus"></i></button></div>
                      </div>
                    </div>
                    @else
                    <label class="form-label fs-5">{{ __('cycles.Judges list') }}</label>
                    <select id="judges-select" name="judges[]" class="select2 form-control" {{ $cstep->data == 'onetoall' ? '' :
                      'multiple="multiple"' }}>
                      {!! $cstep->data == 'onetoall'
                      ? '<option selected disabled>' . __('cycles.Please select one name') . '</option>'
                      : '' !!}
                      @foreach ($judges as $judge)
                      <option value="{{ $judge->id }}" {{ is_array($cstep->list) && in_array($judge->id,
                        json_decode($cstep->list) ?? []) ? 'selected' : '' }} >
                        {{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})
                      </option>
                      @endforeach
                    </select>
                    @endif
                  </div>
                  <div class="custom-control custom-switch custom-control-inline mt-4">
                    <span>{{ __('cycles.Notify them') }}</span>&nbsp;
                    <input name="notify" type="checkbox" class="custom-control-input" id="customSwitch1">
                    <label class="custom-control-label mr-1" for="customSwitch1">
                    </label>
                    <br><br>
                  </div>
                  <div class="row">
                    <div class="col-12 d-flex justify-content-end">
                      <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Update') }}</button>
                    </div>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
      @if( $_user->ableTo('screening.evaluation_show', $permissions) )
      <section>
        <!-- Evaluation Model CRUD Tab -->
        <div id="evaluation-model" class="card card-body tab-content active mt-6">
          <!-- Make this active initially -->
          <h4>Manage Evaluation Model</h4>
          <p class="text-muted">Define the categories, sub-criteria, and weights used for startup evaluations.</p>

          <div>
            <div class="d-flex align-items-center gap-4 mb-4">
              @if( $_user->ableTo('screening.evaluation_save_draft', $permissions) && $status == 'draft' )
              <button id="draft-model-btn" class="btn btn-light">
                <i class="fas fa-save me-2"></i> Save as Draft
              </button>
              @endif

              @if( $_user->ableTo('screening.evaluation_publish', $permissions) )
                @if( $status == 'draft' )
                  <button id="save-model-btn" class="btn btn-primary">
                    <i class="ti ti-world me-2"></i> Publish
                  </button>
                @else
                  <button onclick="event.preventDefault();" class="btn btn-primary disabled" disabled>
                    <i class="ti ti-check me-2"></i> Published
                  </button>
                @endif
              @endif
            </div>
            <div id="model-validation-summary" style="display: none;"></div>
          </div>


          <div id="model-editor">
            <!-- Categories and sub-criteria will be rendered here by JavaScript -->
          </div>

          @if( $_user->ableTo('screening.evaluation_add_criteria') && $status == 'draft' )
          <button id="add-category-btn" class="btn btn-label-primary">
            <i class="fas fa-plus me-2"></i> Add New Criteria
          </button>
          @endif
        </div>
      </section>
      @endif

      @else
      <div class="card">
        <div class="card-header border-bottom">
          <h4 class="card-title">{{ __('cycles.Error') }}</h4>
        </div>
        <div class="card-body">
          {{ __('cycles.No records screening from') }}
          <a href="/dashboard/cycles">{{ __('cycles.Cycles') }}</a>.
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
<!-- END: Content-->

@if ($cstep)
<!--Screening form Modal -->
<div class="modal fade text-left" id="stepForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="modal-body">
          <div id="menu2">
            <div class="form-group mb-3">
              <label class="form-label fs-5">{{ __('cycles.Choose screening type') }}: </label>
              <select class="select2 form-control" name="ScreeningSelect" required>
                @foreach ($types as $k => $type)
                <option value="{{ $k }}" {{ $cstep->data == $k ? 'selected' : '' }}>
                  {{ __('cycles.' . $type) }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <input type="hidden" name="sid" value="{{ $cstep->id }}">
          <input type="hidden" name="type" value="8">
          <div class="form-group mb-3">
            <label class="form-label fs-5">{{ __('cycles.Title') }}: </label>
            <input value="{{ $cstep->title }}" name='title' type="text" placeholder="{{ __('cycles.Title') }}"
              class="form-control" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-5">{{ __('cycles.Start From') }}: </label>
            <input value="{{ $cstep->from->format('Y-m-d') }}" name='fromDate' type="text" class="form-control date"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-5">{{ __('cycles.to') }}: </label>
            <input value="{{ $cstep->to->format('Y-m-d') }}" name='toDate' type="text" class="form-control date"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-5">{{ __('cycles.Description') }}: </label>
            <textarea name='description' placeholder="{{ __('cycles.Description') }}"
              class="form-control">{{ $cstep->description }}</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
          </button>
          <button type="submit" value="submit" class="btn btn-primary ml-1">
            <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@if ($cstep->data == 'grouptogroup')
<!--Users form -->
<div class="modal fade text-left" id="usersForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form class="form" action="{{ URL::to('dashboard/screening/add') }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="modal-body">
          <div class="form-group mb-0">
            <select class="select2 form-control" name="panelusers[]" id="panelusers" multiple>

            </select>
          </div>
          <input type="hidden" name="sid" value="{{ $cstep->id }}">
          <input type="hidden" name="pid" value="0" id="pid">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
          </button>
          <button type="submit" value="submit" class="btn btn-primary ml-1">
            <span class="d-none d-sm-block">{{ __('cycles.Save') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endif
@stop

@section('pagescripts')
<script src="/js/jquery.datetimepicker.full.min.js"></script>
<script>
  $('.date').datetimepicker({
      timepicker: false,
      format: 'Y-m-d'
    });

    function DoChange(set) {
      switch (set) {
        case 1:
          $('#stepForm').modal('show');
          break;
        case 2:
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
          if (confirm('{{ __('cycles.Are you sure, Delete current step and all its contents?') }}')) {
            $.ajax({
              type: "POST",
              url: "/dashboard/cycles/modify",
              data: {
                "stepid": {{ $cstep->id ?? 0 }},
                "type": 6,
              },
              success: function(response) {
                console.log(response);
                location.href = '/dashboard/screening';
              },
              error: function(error) {
                console.log(error);
                alert("Error Occurred, Try again!");
              }
            });
          }
      }
    }

    function openusers(panel) {
      $('#pid').val(panel);
      $('#cpanel').text(panel);
      let theurl = "/dashboard/screening/get/{{ $form->id ?? 0 }}/{{ $cstep->id ?? 0 }}/" + panel;
      $.ajax({
        type: "GET",
        url: theurl,
        success: function(response) {
          console.log(response);
          $('#panelusers').html(response);
          $('#usersForm').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // --- Tab Navigation (Copied from ds.html, slight modification for initial active tab) ---
    const navLinks = document.querySelectorAll('.nav-link');
    const tabContents = document.querySelectorAll('.main-content .tab-content');

    function setActiveTab(targetId) {
        navLinks.forEach(l => {
            l.classList.toggle('active', l.getAttribute('href') === `#${targetId}`);
        });
        tabContents.forEach(t => {
            t.classList.toggle('active', t.id === targetId);
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            setActiveTab(targetId);
            // If switching to model editor, ensure it's rendered
            if (targetId === 'evaluation-model') {
                 renderModelEditor();
            }
        });
    });

    // Determine initial active tab based on URL hash or default to evaluation-model
    const initialTab = window.location.hash ? window.location.hash.substring(1) : 'evaluation-model';
    setActiveTab(initialTab);

    // --- Chart Initialization (Copied from ds.html) ---
    // Note: Ensure the canvas exists in the relevant tab if you keep it.
    const revenueChartCanvas = document.getElementById('revenueChart');
    if (revenueChartCanvas) {
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
        const ctx = revenueChartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: { /* ... chart data ... */ },
            options: { /* ... chart options ... */ }
        });
    }

    // --- Modal Handling (Copied from ds.html) ---
    // Adapt if needed, not directly used by model editor in this example
    const modal = document.getElementById('confirmationModal');
    if(modal) {
         const cancelBtn = document.getElementById('cancelBtn');
         const confirmBtn = document.getElementById('confirmBtn');
         // ... (rest of modal logic) ...
    }

    // --- Evaluation Model CRUD Logic ---

    const modelEditorContainer = document.getElementById('model-editor');
    @if( $status == 'draft' )
      @if( $_user->ableTo('screening.evaluation_add_criteria') )
      const addCategoryBtn = document.getElementById('add-category-btn');
      @endif
      @if( $_user->ableTo('screening.evaluation_publish') )
      const saveModelBtn = document.getElementById('save-model-btn');
      @endif
      
      @if( $_user->ableTo('screening.evaluation_save_draft') )
      const draftModelBtn = document.getElementById('draft-model-btn');
      @endif
    @endif

    const modelValidationSummary = document.getElementById('model-validation-summary');

    // In a real app, fetch this from a server
    let evaluationModelData = @json( $evaluationForm );
    // --- Render Function ---
    function renderModelEditor() {
        if (!modelEditorContainer) return;
        modelEditorContainer.innerHTML = ''; // Clear existing content

        evaluationModelData.categories.forEach((category, categoryIndex) => {
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'category-editor';
            categoryDiv.setAttribute('data-category-id', category.id);

            let subCriteriaHTML = '<div class="sub-criteria-list">';
            category.subCriteria.forEach((sub, subIndex) => {
                subCriteriaHTML += `
                    <div class="sub-criterion-editor" data-subcriterion-id="${sub.id}">
                        <input type="text" class="form-control subcriterion-name" placeholder="Sub Criteria Name" value="${sub.name}" data-category-index="${categoryIndex}" data-sub-index="${subIndex}">
                        <input type="number" class="form-control weight-input subcriterion-weight" placeholder="Weight (%)" value="${sub.weight * 100}" min="0" max="100" step="1" data-category-index="${categoryIndex}" data-sub-index="${subIndex}">
                        @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
                        <button class="btn btn-label-danger btn-sm btn-icon delete-subcriterion-btn" data-category-index="${categoryIndex}" data-sub-index="${subIndex}" title="Delete Sub Criteria">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        @endif
                    </div>
                `;
            });
            subCriteriaHTML += '</div>'; // End sub-criteria-list

             categoryDiv.innerHTML = `
                <div class="category-header">
                  <div class="w-100 d-flex justify-content-between align-items-center">
                    <input id="category-input" type="text" class="form-control category-name" placeholder="Category Name" value="${category.name}" data-category-index="${categoryIndex}" style="font-weight: bold; font-size: 1.1rem;">
                    <div class="d-flex align-items-center">
                        <input type="number" class="form-control weight-input category-weight" placeholder="Weight (%)" value="${category.weight * 100}" min="0" max="100" step="1" data-category-index="${categoryIndex}">
                        @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
                        <button class="btn btn-label-danger btn-sm delete-category-btn text-nowrap ms-6" data-category-index="${categoryIndex}" title="Delete Criteria">
                            <i class="fas fa-trash-alt me-2"></i> Delete Criteria
                        </button>
                        @endif
                    </div>
                  </div>
                </div>
                <div class="validation-message category-validation" style="display: none;"></div> <!-- Placeholder for category weight validation -->
                ${subCriteriaHTML}
                @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
                <div class="category-actions">
                    <button class="btn btn-label-secondary btn-sm add-subcriterion-btn" data-category-index="${categoryIndex}">
                        <i class="fas fa-plus me-2"></i> Add Sub-criteria
                    </button>
                </div>
                @endif
            `;
            modelEditorContainer.appendChild(categoryDiv);
        });

        // Add event listeners after rendering
        attachEventListeners();
        validateModel(false); // Run initial validation without showing global summary
    }

    // --- Event Listeners ---
    function attachEventListeners() {
        @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
        // Delete Criteria
        document.querySelectorAll('.delete-category-btn').forEach(btn => {
             // Remove previous listener to avoid duplicates if re-rendering
             btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.delete-category-btn').forEach(btn => {
            btn.addEventListener('click', handleDeleteCategory);
        });
        @endif

        @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
        // Delete Sub-criterion
        document.querySelectorAll('.delete-subcriterion-btn').forEach(btn => {
             btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.delete-subcriterion-btn').forEach(btn => {
            btn.addEventListener('click', handleDeleteSubCriterion);
        });
        @endif

        @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
        // Add Sub-criterion
        document.querySelectorAll('.add-subcriterion-btn').forEach(btn => {
             btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.add-subcriterion-btn').forEach(btn => {
            btn.addEventListener('click', handleAddSubCriterion);
        });
        @endif

        // Input Changes (Name and Weight)
        document.querySelectorAll('.category-name, .category-weight, .subcriterion-name, .subcriterion-weight').forEach(input => {
            input.replaceWith(input.cloneNode(true)); // Clear old listeners
        });
        document.querySelectorAll('.category-name, .category-weight, .subcriterion-name, .subcriterion-weight').forEach(input => {
            input.addEventListener('input', handleInputChange);
            input.addEventListener('change', () => validateModel(false)); // Validate on final change
        });
    }

    // --- Event Handlers ---
    function handleAddCategory() {
        evaluationModelData.categories.push({
            id: null,
            name: "",
            weight: 0, // Default weight
            subCriteria: []
        });
        renderModelEditor();
    }

    @if( $_user->ableTo('screening.evaluation_add_sub_criteria') && $status == 'draft' )
    function handleAddSubCriterion(event) {
        const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
        if (isNaN(categoryIndex) || !evaluationModelData.categories[categoryIndex]) return;

        evaluationModelData.categories[categoryIndex].subCriteria.push({
            id: null,
            name: "",
            weight: 0
        });
        renderModelEditor(); // Re-render the whole editor for simplicity
    }
    @endif

    @if( $_user->ableTo('screening.evaluation_delete_criteria') && $status == 'draft' )
    function handleDeleteCategory(event) {
        const categoryIndex = parseInt(event.target.closest('button').dataset.categoryIndex);
        if (isNaN(categoryIndex)) return;

        const categoryName = evaluationModelData.categories[categoryIndex]?.name || 'this category';
        if (confirm(`Are you sure you want to delete "${categoryName}" and all its sub-criteria?`)) {
            evaluationModelData.categories.splice(categoryIndex, 1);
            renderModelEditor();
        }
    }
    @endif

    @if( $_user->ableTo('screening.evaluation_delete_sub_criteria') && $status == 'draft' )
    function handleDeleteSubCriterion(event) {
        const btn = event.target.closest('button');
        const categoryIndex = parseInt(btn.dataset.categoryIndex);
        const subIndex = parseInt(btn.dataset.subIndex);
        if (isNaN(categoryIndex) || isNaN(subIndex)) return;

        const subName = evaluationModelData.categories[categoryIndex]?.subCriteria[subIndex]?.name || 'this sub-criterion';
         if (confirm(`Are you sure you want to delete "${subName}"?`)) {
             evaluationModelData.categories[categoryIndex].subCriteria.splice(subIndex, 1);
             renderModelEditor();
         }
    }
    @endif

    function handleInputChange(event) {
        const input = event.target;
        const categoryIndex = parseInt(input.dataset.categoryIndex);
        const subIndex = input.dataset.subIndex !== undefined ? parseInt(input.dataset.subIndex) : null;

        if (isNaN(categoryIndex)) return;

        const category = evaluationModelData.categories[categoryIndex];
        if (!category) return;

        if (input.classList.contains('category-name')) {
            category.name = input.value;
        } else if (input.classList.contains('category-weight')) {
            category.weight = parseFloat(input.value) / 100 || 0; // Store as decimal
        } else if (subIndex !== null) {
            const subCriterion = category.subCriteria[subIndex];
            if (!subCriterion) return;
            if (input.classList.contains('subcriterion-name')) {
                subCriterion.name = input.value;
            } else if (input.classList.contains('subcriterion-weight')) {
                subCriterion.weight = parseFloat(input.value) / 100 || 0; // Store as decimal
            }
        }
         // Note: No re-render here, direct data update. Validation will run on 'change'.
    }

    @if( $status == 'draft' )
      @if( $_user->ableTo('screening.evaluation_publish') )
      function handleSaveModel() {
          if (validateModel(true)) { // Run final validation and show summary
              $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
              });
              $.ajax({
                  type: "POST",
                  url: "{{ route('screening.evaluation_publish') }}",
                  data: {
                      "action": "publish",
                      "criteria": evaluationModelData.categories,
                      "stepId": {{ $cstep->id }},
                  },
                  success: function(response) {
                      console.log(response);
                      if (response.status) {
                          evaluationModelData.categories = response.data;
                          modelValidationSummary.textContent = 'Model saved successfully!';
                          modelValidationSummary.className = 'alert alert-success alert-dismissible';
                          modelValidationSummary.style.display = 'block';
                      } else {
                          modelValidationSummary.textContent = response.message;
                          modelValidationSummary.className = 'alert alert-danger alert-dismissible';
                          modelValidationSummary.style.display = 'block';
                      }
                  },
                  error: function(error) {
                      console.error("Error saving model:", error);
                      modelValidationSummary.textContent = 'Error saving model. Please try again.';
                      modelValidationSummary.className = 'alert alert-danger alert-dismissible';
                      modelValidationSummary.style.display = 'block';
                  }
              });
          } else {
              alert("Please fix the validation errors before saving.");
              // Summary message updated by validateModel()
          }
      }
      @endif

      @if( $_user->ableTo('screening.evaluation_save_draft') )
      function handleDraftModel() {
          if (validateModel(true)) { // Run final validation and show summary
              $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
              });
              $.ajax({
                  type: "POST",
                  url: "{{ route('screening.evaluation_save_draft') }}",
                  data: {
                      "action": "draft",
                      "criteria": evaluationModelData.categories,
                      "stepId": {{ $cstep->id }},
                  },
                  success: function(response) {
                      console.log(response);
                      if (response.status) {
                        evaluationModelData.categories = response.data;
                          modelValidationSummary.textContent = 'Model saved successfully!';
                          modelValidationSummary.className = 'alert alert-success alert-dismissible';
                          modelValidationSummary.style.display = 'block';
                      } else {
                          modelValidationSummary.textContent = response.message;
                          modelValidationSummary.className = 'alert alert-danger alert-dismissible';
                          modelValidationSummary.style.display = 'block';
                      }
                  },
                  error: function(error) {
                      console.error("Error saving model:", error);
                      modelValidationSummary.textContent = 'Error saving model. Please try again.';
                      modelValidationSummary.className = 'alert alert-danger alert-dismissible';
                      modelValidationSummary.style.display = 'block';
                  }
              });
          } else {
              alert("Please fix the validation errors before saving.");
              // Summary message updated by validateModel()
          }
      }
      @endif
    @endif

    // --- Validation Logic ---
    function validateModel(showGlobalSummary) {
        let isValid = true;
        let totalCategoryWeight = 0;
        let globalErrorMessages = [];

        evaluationModelData.categories.forEach((category, categoryIndex) => {
            const categoryElement = modelEditorContainer.querySelector(`.category-editor[data-category-id="${category.id}"]`);
            const categoryValidationDiv = categoryElement ? categoryElement.querySelector('.category-validation') : null;
            let categoryErrorMessage = '';

            totalCategoryWeight += category.weight;

            let totalSubCriterionWeight = 0;
            if (category.subCriteria.length > 0) {
                category.subCriteria.forEach(sub => {
                    totalSubCriterionWeight += sub.weight;
                });

                 // Check sub-criterion weights sum within this category
                 if (Math.abs(totalSubCriterionWeight - 1.0) > 0.001) { // Use tolerance for float comparison
                     isValid = false;
                     categoryErrorMessage = `Sub-criterion weights must sum to 100% (currently ${ (totalSubCriterionWeight * 100).toFixed(1)}%).`;
                     globalErrorMessages.push(`Category "${category.name}": ${categoryErrorMessage}`);
                 }
            } else if (category.weight > 0) {
                 // Warn if category has weight but no sub-criteria
                 categoryErrorMessage = `Warning: Category has weight but no sub-criteria. It won't contribute to score.`;
                 // Don't mark as invalid, but show warning
                 if (categoryValidationDiv) {
                      categoryValidationDiv.className = 'alert alert-warning alert-dismissible';
                 }
            }


             // Display category-specific validation
             if (categoryValidationDiv) {
                 if (categoryErrorMessage && categoryErrorMessage.startsWith('Warning:')) {
                     categoryValidationDiv.textContent = categoryErrorMessage;
                     categoryValidationDiv.className = 'alert alert-warning alert-dismissible';
                     categoryValidationDiv.style.display = 'block';
                 } else if(categoryErrorMessage) {
                     categoryValidationDiv.textContent = categoryErrorMessage;
                     categoryValidationDiv.className = 'alert alert-danger alert-dismissible';
                     categoryValidationDiv.style.display = 'block';
                 }
                 else {
                     categoryValidationDiv.style.display = 'none';
                 }
             }
        });

        // Check total category weight sum
        if (Math.abs(totalCategoryWeight - 1.0) > 0.001) {
            isValid = false;
            globalErrorMessages.push(`Total category weights must sum to 100% (currently ${(totalCategoryWeight * 100).toFixed(1)}%).`);
        }

        // Display global summary message if requested
        if (showGlobalSummary) {
             if (!isValid) {
                 modelValidationSummary.innerHTML = '<strong>Validation Errors:</strong><br>' + globalErrorMessages.join('<br>');
                 modelValidationSummary.className = 'alert alert-danger alert-dismissible';
                 modelValidationSummary.style.display = 'block';
             } else {
                 modelValidationSummary.textContent = 'Model structure is valid.';
                 modelValidationSummary.className = 'alert alert-success alert-dismissible';
                 modelValidationSummary.style.display = 'block';
                  // Hide after a few seconds on success?
                 setTimeout(() => {
                     if (modelValidationSummary.classList.contains('alert-success')) {
                        modelValidationSummary.style.display = 'none';
                     }
                 }, 3000);
             }
        } else {
             // Hide global summary if not explicitly requested (e.g., during typing)
             if(!modelValidationSummary.classList.contains('alert-danger') && !modelValidationSummary.classList.contains('alert-success')) {
                modelValidationSummary.style.display = 'none';
             }
        }

        return isValid;
    }

    // --- Initial Setup ---
    @if( $status == 'draft' )
      @if( $_user->ableTo('screening.evaluation_add_criteria') )
      addCategoryBtn.addEventListener('click', handleAddCategory);
      @endif
      @if( $_user->ableTo('screening.evaluation_publish') )
      saveModelBtn.addEventListener('click', handleSaveModel);
      @endif
      @if( $_user->ableTo('screening.evaluation_save_draft') )
      draftModelBtn.addEventListener('click', handleDraftModel);
      @endif
    @endif

    // Render the editor on initial load if the tab is active
    if (document.getElementById('evaluation-model').classList.contains('active')) {
        renderModelEditor();
    }

});
</script>

<script>
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500); // Remove after fade-out
    });
  }, 20000);

  $('#judges-select').on('change', function() {
    const selectedJudgesIds = $(this).val();

    $('#judges').val(selectedJudgesIds);
  });
</script>
@stop