@extends('layouts.admin')

@section('title', __('admin.Builder'))
@section('subTitle', __('admin.Edit form'))

@section('style')
<style>
  .section-questions {
    border-left: 3px solid var(--bs-primary);
  }
  .dragging {
    opacity: 0.5;
}

.drag-handle {
    cursor: move;
}
</style>
@stop

@php
  $user = Auth::user();
  if($user->hasRole('organizationManager')) {
    $organizations = App\Organization::where('id', $user->organization_id)->get();
    $allBuilderForms = App\BuilderForm::where('organization_id', $user->organization_id)->with('organization', 'cycles')->get();
  } else {
    $organizations = App\Organization::all();
    $allBuilderForms = App\BuilderForm::with('organization', 'cycles')->get();
  }

    $mode = !request()->segment(3) ? 'ADD' : 'EDIT';

    $orgIdParam = request()->get('org_id');
//   dd($data['builder_data']);
    //   $builder_data = $data['builder_data'];
@endphp

@section('content')

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
      <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div class="mb-4 mb-md-0">
            <h5 class="card-title mb-0">{{ __('builder.Form_Builder') }}</h5>
            <div class="text-muted">{{ __('builder.Build_custom_forms') }}</div>
        </div>
        <div class="d-flex align-items-center gap-3">
          @if (request()->has('org_id') || request()->has('form_id'))
          <button class="btn btn-outline-secondary" onclick="location.href='/dashboard/show-builder'">
            <i class="ti ti-refresh"></i>
            <span class="d-none d-md-inline ms-2">{{ __('admin.Reset')}}</span>
          </button>
          @endif
          <button class="btn btn-label-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="ti ti-filter"></i>
            <span class="d-none d-md-inline ms-2">{{ __('cycles.Show_Filters') }}</span>
          </button>
           {{-- <button class="btn btn-primary" onclick="showForm(2,0,null)">
            <i class="ti ti-plus"></i> 
            <span class="d-none d-md-inline ms-2">{{ __('admin.Add_New') }}</span>
          </button> --}}
        </div>
      </div>
    </div>

     <!-- Form Header -->
    <div class="card mb-8">
      <div class="bg-primary rounded-top" style="height: 8px;"></div>
      <div class="card-body">
        @if($mode == 'EDIT')
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="card-title mb-4 mb-md-0">{{ $title->form_name }}</h5>
          <div class="d-flex align-items-center gap-2 ms-md-2">
            <div class="dropdown">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti ti-dots-vertical"></i>
            <span class="d-none d-md-inline ms-2">{{ __('builder.Actions') }}</span>
              </button>
              <ul class="dropdown-menu">
            <li><button class="dropdown-item d-flex align-items-center gap-2" onclick="showForm(0,{{ $title->id }},'{{ $title->form_name }}',{{ $title->organization_id }})">
              <i class="ti ti-pencil"></i> {{ __('builder.Edit') }}
            </button></li>
            <li><button class="dropdown-item d-flex align-items-center gap-2" onclick="showForm(1,{{ $title->id }},'{{ $title->form_name }} {{ __('builder.Copy.') }}')">
              <i class="ti ti-copy"></i> {{ __('builder.Copy') }}
            </button></li>
            <li><button class="dropdown-item d-flex align-items-center gap-2 text-danger" onclick="DoChange(3,{{ $title->id }},null)">
              <i class="ti ti-trash"></i> {{ __('builder.Delete') }}
            </button></li>
            <li><a class="dropdown-item d-flex align-items-center gap-2" href="/dashboard/show-form/{{ $title->id }}">
              <i class="ti ti-eye"></i> {{ __('builder.Show_Saved_Form') }}
            </a></li>
              </ul>
            </div>
          </div>
        </div>
        @endif
        <div class="form-group">
          <label class="form-label">{{ __('builder.Form_Title') }}</label>
          <input 
            type="text" 
            id="formTitle" 
            class="form-control"
            placeholder="{{ __('builder.Untitled_form') }}" 
            value="{{ old('title', optional($title)->form_name ?? '') }}"
          />
        </div>
        @if (is_null(auth()->user()->organization_id))
        <div class="form-group mt-4" id="mmenu1">
          <label class="form-label">{{ __('builder.Organization') }}</label>
          <div class="form-group mb-0">           
            <select class="select2 form-control" id="organization_id" name="organization_id" data-placeholder="{{ __('builder.Select_Organization') }}" required>
              <option value="" disabled {{ !$orgIdParam ? 'selected' : '' }}>{{ __('builder.Select_Organization') }}</option>
              @foreach (\App\Organization::all() as $org)
              <option value="{{ $org->id }}" {{ $org->id == $orgIdParam ? 'selected' : '' }}>{{ $org->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        @else
        <input type="hidden" name="organization_id" value="{{ auth()->user()->organization_id }}" id="organization_id">
        @endif
      </div>
    </div>

    <!-- Items Container (Questions and Sections) -->
    <div id="itemsContainer">
      <!-- Empty state - will be populated by user -->
    </div>

    <div class="card card-body">
        <div class="d-flex align-items-center gap-4">
            <!-- Add Section Button -->
            <button type="button" class="btn btn-lg btn-outline-secondary d-flex align-items-center gap-2" onclick="addSection()">
                <i class="ti ti-plus"></i>
                {{ __('builder.Add_Section') }}
            </button>
        
            <button type="button" class="btn btn-lg btn-primary ml-2" onclick="saveForm()">
                <span class="text-nowrap">{{ $mode == 'EDIT' ? __('builder.Edit_Form') : __('builder.Add_Form') }}</span>
                <i class="ti ti-check ms-2"></i>
            </button>
        </div>
    </div>

  </div>
</div>

<!--Modify custom form step Modal -->
<div class="modal fade text-left" id="AddForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="AddFormTitle">{{ __('builder.Add_New_Form') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if (is_null(auth()->user()->organization_id))
        <div class="mb-4" id="mmenu1">
          <label class="form-label">{{ __('builder.Select_Organization') }}: </label>
          <div class="form-group mb-0">
            <select class="select2 form-control" id="organization_id" name="organization_id" data-placeholder="{{ __('builder.Select_Organization') }}" required>
              @foreach (\App\Organization::all() as $org)
              <option value="{{ $org->id }}" {{ $org->id == request('org_id') ? 'selected' : '' }}>{{ $org->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        @else
        <input type="hidden" name="organization_id" value="{{ auth()->user()->organization_id }}" id="organization_id">
        @endif

        <div class="form-group">
            <label class="form-label">{{ __('builder.Form_Title') }}: </label>
          <input id='mtitle' name='title' type="text" placeholder="{{ __('builder.Form_Title') }}" class="form-control"
            required>
        </div>

      </div>
      <div class="modal-footer mt-4">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
        </button>
        <button type="button" class="btn btn-primary ml-1" id="modelSubmit">
          <span class="d-none d-sm-block">{{ __('cycles.Save') }}</span>
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalLabel">{{ __('builder.Filter_Forms') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="GET" action="/dashboard/show-builder">
          <div id="organizationFilterContainer" class="form-group mb-4">
            <label for="organizationFilter" class="form-label">{{ __('builder.Select_Organization') }}</label>
            <select class="form-select select2" id="organizationFilter" name="org_id" data-placeholder="{{ __('builder.Select_Organization') }}">
              <option value="0">{{ __('admin.All_Organizations') }}</option>
              @foreach ($organizations as $organization)
              <option value="{{$organization->id}}" {{ $organization->id == request('org_id') ? 'selected' : '' }}
                >
                {{ $organization->{'name_' . $l} }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="formFilter" class="form-label">{{ __('builder.Select_Form') }}</label>
            <select class="form-select select2" id="formFilter" name="form_id">
              <option value="">{{ __('builder.All_Forms') }}</option>
            </select>
          </div>
          <div class="d-flex justify-content-end align-items-center gap-3 mt-10">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('cycles.Close') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('cycles.Apply_Filters') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{-- /Filter Modal --}}
@stop

@section('pagescripts')
<script>
    $(document).ready(function(){
        const isOrganizationManager = @json(auth()->user()->hasRole('organizationManager'));

        if (isOrganizationManager) {
            $('#organizationFilterContainer').hide();
        }
        
        // Add a section on first render if no existing form data
        @if($mode == 'ADD')
        addSection();
        @endif
    });

  function showForm(set, id, title, organization_id) {
      $('#mtitle').val(title);
      if (typeof(organization_id) != "undefined") {
        $('#organization_id').val(organization_id)
      }
      let modalTitle = '';
      if (set === 0) {
          modalTitle = '{{ __("builder.Edit_Form") }}';
      } else if (set === 1) {
          modalTitle = '{{ __("builder.Copy_Form") }}';
      } else if (set === 2) {
          modalTitle = '{{ __("builder.Add_New_Form") }}';
      }
      $('#AddFormTitle').text(modalTitle);
      $('#modelSubmit').attr('onclick', `DoChange(${set},${id},'${title}',${organization_id})`)
      $('#AddForm').modal('show')
    }

    async function DoChange(set, id, title, organization_id = 0) {
      if (set === 0) {
        title = $('#mtitle').val();
        organization_id = $('#organization_id').val();
        {{-- title = prompt("{{__('builder.new title')}}",title); --}}
      } else if (set === 1) {
        title = $('#mtitle').val();
        organization_id = $('#organization_id').val();
        {{--                title = prompt("{{__('builder.new copied')}}:", title + ' {{__('builder.Copy.')}}'); --}}
      } else if (set === 2) {
        {{-- title = prompt("{{__('builder.new form')}}"); --}}
        title = $('#mtitle').val();
        organization_id = $('#organization_id').val();
      } else if (set === 3) {
        const result = await Swal.fire({
            title: "{{ __('Are you sure?') }}",
            text: "{{ __('builder.deleting form') }}?",
            icon: 'warning',
            showCancelButton: true,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-label-secondary'
            },
            confirmButtonText: "{{ __('admin.DYes') }}",
            cancelButtonText: "{{ __('No') }}"
        });
        if (!result.isConfirmed) {
          return;
        }
        title = true; // Set to true to proceed, as confirmation is handled above
      }
      if (!title) {
        return false;
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/builder/" + set,
        data: {
          "id": id,
          "title": title,
          "organization_id": organization_id
        },
        success: function(response) {
          // console.log(response);
          location.href = '/dashboard/show-builder';
        },
        error: function(error) {
          // console.log(error);
          releaseFailureAlert({ message: '{{ __("builder.Error_Occurred") }}' });
        }
      });
    }

    console.log(@json($builder_data));

    var allForms = @json($allBuilderForms);

    function updateFormSelect() {
        const lang = '{{ $l }}';
        const isOrganizationManager = @json(auth()->user()->hasRole('organizationManager'));

        var selectedOrg = $('#organizationFilter').val();
        var filteredForms = selectedOrg == 0 ? allForms : allForms.filter(function(form) {
            return form.organization_id == selectedOrg;
        });
        var $formSelect = $('#formFilter');
        $formSelect.find('option:not(:first)').remove();
        filteredForms.forEach(function(form) {
            // console.log('form', form);
            var optionText = form.form_name;
            if (form.organization) {
                optionText += isOrganizationManager ? '' : ' - ' + form.organization['name_' + lang];
            }
            if (form.cycles && form.cycles.length > 0) {
                optionText += ' ' + form.cycles.map(function(c) { return c.title; }).join(' - ');
            }
            var selected = ({{ isset($title->id) ? $title->id : 'null' }} !== null && form.id == {{ isset($title->id) ? $title->id : 'null' }}) ? ' selected' : '';
            $formSelect.append('<option value="' + form.id + '"' + selected + '>' + optionText + '</option>');
        });
        if ($formSelect.hasClass('select2-hidden-accessible')) {
            $formSelect.trigger('change');
        }
    }

    $('#organizationFilter').on('change', updateFormSelect);
    updateFormSelect();

    $('#filterModal form').on('submit', function(e) {
        e.preventDefault();
        var org_id = $('#organizationFilter').val();
        var form_id = $('#formFilter').val();
        var url = '/dashboard/show-builder';
        
        if (form_id) {
            url += '/' + form_id;
            // Get organization ID from selected form
            var selectedForm = allForms.find(function(form) {
                return form.id == form_id;
            });
            if (selectedForm) {
                org_id = selectedForm.organization_id;
            }
        }
        
        if (org_id && org_id != '0') {
            url += '?org_id=' + org_id;
        }
        
        location.href = url;
    });
</script>
<script>
  let itemCounter = 0;
        let questionCounters = {};
        let draggedElement = null;

        // Translations for JavaScript
        const translations = {
            section: '{{ __("builder.Section") }}',
            option: '{{ __("builder.Option") }}',
            go_to: '{{ __("builder.Go_to") }}',
            if: '{{ __("builder.If") }}',
            error_occurred: '{{ __("builder.Error_Occurred") }}'
        };

        function setupItemDragAndDrop(element) {
            element.draggable = true;
            
            element.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            element.addEventListener('dragend', function(e) {
                this.classList.remove('dragging');
                document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
            });

            element.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                if (draggedElement !== this && draggedElement.parentElement === this.parentElement) {
                    this.classList.add('drag-over');
                }
            });

            element.addEventListener('dragleave', function(e) {
                this.classList.remove('drag-over');
            });

            element.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                
                if (draggedElement !== this && draggedElement.parentElement === this.parentElement) {
                    const container = this.parentElement;
                    const allItems = [...container.children];
                    const draggedIndex = allItems.indexOf(draggedElement);
                    const targetIndex = allItems.indexOf(this);
                    
                    if (draggedIndex < targetIndex) {
                        container.insertBefore(draggedElement, this.nextSibling);
                    } else {
                        container.insertBefore(draggedElement, this);
                    }
                }
            });
        }


        function getSectionsList() {
            const sections = [];
            document.querySelectorAll('.section-wrapper').forEach((section, index) => {
                const title = section.querySelector('.section-title').value || `${translations.section} ${index + 1}`;
                sections.push({
                    id: section.dataset.itemId,
                    title: title
                });
            });
            return sections;
        }

        function updateQuestionOptions(select) {
            const questionCard = select.closest('.item-card');
            const optionsContainer = questionCard.querySelector('.question-options');
            const type = select.value;

            switch(type) {
                case 'short_answer':
                    optionsContainer.innerHTML = `
                        <input type="text" class="form-control text-muted" placeholder="{{ __('builder.Short_answer_text') }}" disabled>
                    `;
                    break;
                case 'paragraph':
                    optionsContainer.innerHTML = `
                        <textarea class="form-control text-muted" placeholder="{{ __('builder.Long_answer_text') }}" disabled rows="3"></textarea>
                    `;
                    break;
                case 'multiple_choice':
                case 'checkboxes':
                    optionsContainer.innerHTML = `
                        <div class="options-list">
                            ${createOptionHTML(type, 1, '{{ __("builder.Option_1") }}')}
                            ${createOptionHTML(type, 2, '{{ __("builder.Option_2") }}')}
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-link text-primary p-0 font-weight-medium" onclick="addOption(this, '${type}')">{{ __('builder.Add_option') }}</button>
                                <span class="text-muted">{{ __('builder.or') }}</span>
                                <button type="button" class="btn btn-link text-info p-0 font-weight-medium" onclick="addOtherOption(this, '${type}')">{{ __('builder.Add_Other') }}</button>
                            </div>
                        </div>
                    `;
                    
                    break;
                case 'dropdown':
                    optionsContainer.innerHTML = `
                        <div class="options-list">
                            ${createOptionHTML('dropdown', 1, '{{ __("builder.Option_1") }}')}
                            ${createOptionHTML('dropdown', 2, '{{ __("builder.Option_2") }}')}
                            <button type="button" class="btn btn-link text-primary p-0 font-weight-medium mt-2" onclick="addOption(this, 'dropdown')">{{ __('builder.Add_option') }}</button>
                        </div>
                    `;
                    break;
                case 'linear_scale':
                    optionsContainer.innerHTML = `
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <select class="scale-from form-select">
                                    <option value="0">0</option>
                                    <option value="1" selected>1</option>
                                </select>
                                <span class="text-muted small">{{ __('builder.to') }}</span>
                                <select class="scale-to form-select">
                                    <option value="5" selected>5</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="scale-label-low form-control" placeholder="{{ __('builder.Label_low') }}" value="{{ __('builder.Low') }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="scale-label-high form-control" placeholder="{{ __('builder.Label_high') }}" value="{{ __('builder.High') }}">
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                case 'date':
                    optionsContainer.innerHTML = `
                        <input type="date" class="form-control" disabled>
                    `;
                    break;
                case 'time':
                    optionsContainer.innerHTML = `
                        <input type="time" class="form-control" disabled>
                    `;
                    break;
                case 'file':
                    optionsContainer.innerHTML = `
                        <input type="file" class="form-control" disabled>
                    `;
                    break;
            }
            
            // Always call addLogicPanel to show/hide logic based on question type
            addLogicPanel(questionCard);
        }

        function addLogicPanel(questionCard, currentLogic = {}) {
            // Remove existing logic panel if any
            const existingPanel = questionCard.querySelector('.logic-panel');
            if (existingPanel) {
                existingPanel.remove();
            }

            const questionType = questionCard.querySelector('.question-type').value;
            
            // Only show logic for dropdown, multiple choice, and checkboxes
            if (!['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) {
                return;
            }

            const sections = getSectionsList();
            const options = questionCard.querySelectorAll('.option-input');
            
            let logicHTML = '';
            
            // Only multiple choice and dropdown support individual option logic
            if (questionType === 'multiple_choice' || questionType === 'dropdown') {
                logicHTML = `
                    <div class="logic-panel mt-4 p-4 rounded">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small font-weight-medium text-primary">{{ __('builder.Response_Logic') }}</span>
                            <button type="button" onclick="toggleLogicOptions(this)" class="btn btn-link text-primary p-0 small">
                                {{ __('builder.Configure') }}
                            </button>
                        </div>
                        <div class="logic-options d-none">
                            ${Array.from(options).map((opt, idx) => `
                                <div class="d-flex align-items-center gap-3 small mb-3">
                                    <span class="text-muted" style="min-width: 6rem;">${translations.if} "${opt.value || translations.option + ' ' + (idx + 1)}":</span>
                                    <select class="option-logic-${idx} form-select form-select-sm flex-fill">
                                        <option value="continue">{{ __('builder.Continue_next_section') }}</option>
                                        ${sections.map(s => `<option value="section-${s.id}">${translations.go_to} ${s.title}</option>`).join('')}
                                        <option value="submit">{{ __('builder.Submit_form') }}</option>
                                    </select>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            if (logicHTML) {
                const footerDiv = questionCard.querySelector('.mt-4.pt-4.border-top');
                footerDiv.insertAdjacentHTML('beforebegin', logicHTML);
                // Set the values
                const newSelects = questionCard.querySelectorAll('.logic-options select');
                newSelects.forEach((select, idx) => {
                    const optionInput = options[idx];
                    const optionValue = optionInput ? optionInput.value : '';
                    select.value = currentLogic[optionValue] || 'continue';
                });
            }
        }

        function toggleLogicOptions(button) {
            const logicOptions = button.closest('.logic-panel').querySelector('.logic-options');
            logicOptions.classList.toggle('d-none');
            button.textContent = logicOptions.classList.contains('d-none') ? '{{ __("builder.Configure") }}' : '{{ __("builder.Hide") }}';
        }

        function createOptionHTML(type, number, value) {
            let icon = '';
            if (type === 'multiple_choice') {
                icon = '○';
            } else if (type === 'checkboxes') {
                icon = '☐';
            } else if (type === 'dropdown') {
                icon = number + '.';
            }

            return `
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted">${icon}</span>
                    <input type="text" class="option-input form-control form-control-sm border-0 border-bottom flex-fill" placeholder="${translations.option} ${number}" value="${value}" onchange="updateLogicPanelOptions(this)">
                    <button type="button" class="btn btn-link text-muted p-0" onclick="removeOption(this)">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            `;
        }

        function updateLogicPanelOptions(input) {
            const questionCard = input.closest('.item-card');
            const questionType = questionCard.querySelector('.question-type').value;
            
            if (['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) {
                addLogicPanel(questionCard);
            }
        }

        function addOption(button, questionType) {
            const optionsContainer = button.closest('.options-list');
            const currentOptions = optionsContainer.querySelectorAll('.d-flex.align-items-center');
            const optionNumber = currentOptions.length + 1;

            const questionCard = button.closest('.item-card');
            const qt = questionCard.querySelector('.question-type').value;
            let currentLogic = {};
            if (qt === 'multiple_choice' || qt === 'dropdown') {
                const logicSelects = questionCard.querySelectorAll('.logic-options select');
                const optionInputs = questionCard.querySelectorAll('.option-input');
                logicSelects.forEach((select, idx) => {
                    const optionValue = optionInputs[idx]?.value || '';
                    if (optionValue) {
                        currentLogic[optionValue] = select.value;
                    }
                });
            }

            const newOptionDiv = document.createElement('div');
            newOptionDiv.innerHTML = createOptionHTML(questionType, optionNumber, `${translations.option} ${optionNumber}`);
            const newOption = newOptionDiv.firstElementChild;
            
            let referenceNode;
            if (button.parentElement === optionsContainer) {
                referenceNode = button;
            } else {
                referenceNode = button.closest('div');
            }
            
            optionsContainer.insertBefore(newOption, referenceNode);
            
            if (questionType === 'dropdown') {
                updateDropdownNumbers(optionsContainer);
            }
            
            // Update logic panel
            if (['multiple_choice', 'dropdown', 'checkboxes'].includes(qt)) {
                addLogicPanel(questionCard, currentLogic);
            }
        }

        function addOtherOption(button, questionType) {
            const optionsContainer = button.closest('.options-list');
            const currentOptions = optionsContainer.querySelectorAll('.d-flex.align-items-center');
            const optionNumber = currentOptions.length + 1;

            const questionCard = button.closest('.item-card');
            const qt = questionCard.querySelector('.question-type').value;
            let currentLogic = {};
            if (qt === 'multiple_choice' || qt === 'dropdown') {
                const logicSelects = questionCard.querySelectorAll('.logic-options select');
                const optionInputs = questionCard.querySelectorAll('.option-input');
                logicSelects.forEach((select, idx) => {
                    const optionValue = optionInputs[idx]?.value || '';
                    if (optionValue) {
                        currentLogic[optionValue] = select.value;
                    }
                });
            }

            let icon = questionType === 'multiple_choice' ? '○' : '☐';

            const newOption = document.createElement('div');
            newOption.className = 'd-flex align-items-center gap-2';
            newOption.innerHTML = `
                <span class="text-muted">${icon}</span>
                <input type="text" class="option-input form-control form-control-sm border-0 border-bottom flex-fill" value="{{ __('builder.Other_value') }}" readonly>
                <button type="button" class="btn btn-link text-muted p-0 ms-auto" onclick="removeOption(this)">
                    <i class="ti ti-x"></i>
                </button>
            `;
            
            optionsContainer.insertBefore(newOption, button.closest('div'));
            
            const qc = button.closest('.item-card');
            if (['multiple_choice', 'dropdown', 'checkboxes'].includes(qt)) {
                addLogicPanel(qc, currentLogic);
            }
        }

        function removeOption(button) {
            if (!button || !button.parentElement) return;
            const optionDiv = button.parentElement;
            if (!optionDiv) return;
            const optionsContainer = optionDiv.parentElement;
            if (!optionsContainer) return;
            
            const questionCard = button.closest('.item-card');
            const questionType = questionCard.querySelector('.question-type').value;
            let currentLogic = {};
            if (questionType === 'multiple_choice' || questionType === 'dropdown') {
                const logicSelects = questionCard.querySelectorAll('.logic-options select');
                const optionInputs = questionCard.querySelectorAll('.option-input');
                logicSelects.forEach((select, idx) => {
                    const optionValue = optionInputs[idx]?.value || '';
                    if (optionValue) {
                        currentLogic[optionValue] = select.value;
                    }
                });
            }
            
            if (optionsContainer.querySelector('.text-muted')?.textContent.includes('.')) {
                updateDropdownNumbers(optionsContainer);
            }
            
            // Update logic panel
            if (['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) {
                addLogicPanel(questionCard, currentLogic);
            }

            optionDiv.remove();
        }

        function updateDropdownNumbers(container) {
            const options = container.querySelectorAll('.d-flex.align-items-center');
            options.forEach((option, index) => {
                const numberSpan = option.querySelector('.text-muted');
                if (numberSpan && !numberSpan.classList.contains('drag-handle')) {
                    // Only update if it looks like a number (contains a period)
                    if (numberSpan.textContent.includes('.')) {
                        numberSpan.textContent = (index + 1) + '.';
                    }
                }
            });
        }

        function createQuestionHTML(itemId, inSection = false) {
            return `
                <div class="item-card question-card card ${inSection ? '' : 'mb-4'}" data-item-id="${itemId}" data-item-type="question">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <i class="ti ti-grip-vertical text-muted drag-handle cursor-move mt-1"></i>
                            <div class="flex-fill">
                                <input 
                                    type="text" 
                                    class="question-title form-control form-control-lg border-0 border-bottom border-2 mb-4"
                                    placeholder="{{ __('builder.Question') }}"
                                >
                                
                                <select 
                                    class="question-type form-select mb-3"
                                    onchange="updateQuestionOptions(this)"
                                >
                                    <option value="short_answer">{{ __('builder.Short_answer') }}</option>
                                    <option value="paragraph">{{ __('builder.Paragraph') }}</option>
                                    <option value="multiple_choice">{{ __('builder.Multiple_choice') }}</option>
                                    <option value="checkboxes">{{ __('builder.Checkboxes') }}</option>
                                    <option value="dropdown">{{ __('builder.Dropdown') }}</option>
                                    <option value="linear_scale">{{ __('builder.Linear_scale') }}</option>
                                    <option value="date">{{ __('builder.Date') }}</option>
                                    <option value="time">{{ __('builder.Time') }}</option>
                                    <option value="file">{{ __('builder.File_upload') }}</option>
                                </select>

                                <div class="question-options mt-4">
                                    <input 
                                        type="text" 
                                        class="form-control text-muted"
                                        placeholder="Short answer text"
                                        disabled
                                    >
                                </div>
                            </div>

                            <button 
                                type="button" 
                                class="delete-question btn btn-link text-muted p-0"
                                onclick="deleteItem(this)"
                            >
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>

                        <div class="mt-4 pt-4 border-top d-flex align-items-center justify-content-end gap-3">
                            <div class="form-check">
                                <input type="checkbox" class="question-required form-check-input">
                                <label class="form-check-label">{{ __('builder.Required') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function addStandaloneQuestion() {
            const container = document.getElementById('itemsContainer');
            const questionHtml = createQuestionHTML(itemCounter++, false);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = questionHtml;
            const questionElement = tempDiv.firstElementChild;
            container.appendChild(questionElement);
            setupItemDragAndDrop(questionElement);
        }

        function addQuestionToSection(button) {
            const sectionWrapper = button.closest('.section-wrapper');
            const sectionId = sectionWrapper.dataset.itemId;
            const questionsContainer = sectionWrapper.querySelector('.section-questions');
            
            if (!questionCounters[sectionId]) {
                questionCounters[sectionId] = 0;
            }
            
            const questionId = itemCounter++;
            const questionHtml = createQuestionHTML(questionId, true);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = questionHtml;
            const questionElement = tempDiv.firstElementChild;
            
            questionsContainer.appendChild(questionElement);
            setupItemDragAndDrop(questionElement);
        }

        function addSection() {
            const container = document.getElementById('itemsContainer');
            const sectionId = itemCounter++;
            questionCounters[sectionId] = 0;
            
            const sections = getSectionsList();
            
            const sectionHtml = `
                <div class="section-wrapper mb-5 position-relative" data-item-id="${sectionId}" data-item-type="section">
                    <div class="position-absolute start-0 z-1" style="top: -0.5rem;">
                        <span class="badge bg-primary">{{ __('builder.Section') }} ${sectionId + 1}</span>
                    </div>
                    <div class="item-card section-card card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3 mt-3">
                                <i class="ti ti-grip-vertical text-muted drag-handle cursor-move mt-1"></i>
                                <div class="flex-fill">
                                    <input 
                                        type="text" 
                                        class="section-title form-control mb-2"
                                        placeholder="{{ __('builder.Section_title_optional') }}"
                                        onchange="updateAllLogicPanels()"
                                    >
                                    <input 
                                        type="text" 
                                        class="section-description form-control mb-3"
                                        placeholder="{{ __('builder.Section_description_optional') }}"
                                    >
                                </div>

                                <button 
                                    type="button" 
                                    class="delete-section btn btn-link text-muted p-0"
                                    onclick="deleteSection(this)"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ __('builder.Remove_Section') }}"
                                >
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                            
                            <div class="mt-4 pt-4 border-top">
                                <div class="text-muted small font-weight-medium mb-2">{{ __('builder.After_this_section') }}</div>
                                <select class="section-logic form-select">
                                    <option value="continue">{{ __('builder.Continue_next_section') }}</option>
                                    ${sections.map(s => `<option value="section-${s.id}">${translations.go_to} ${s.title}</option>`).join('')}
                                    <option value="submit">{{ __('builder.Submit_form') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-questions d-flex flex-column gap-3 mb-4 px-4 ms-3">
                    </div>

                    <button 
                        type="button"
                        onclick="addQuestionToSection(this)"
                        class="btn btn-outline-primary border-2 border-dashed d-flex align-items-center justify-content-center gap-2 w-100 p-3"
                    >
                        <i class="ti ti-plus"></i>
                        {{ __('builder.Add_Question_Section') }} ${sectionId + 1}
                    </button>
                </div>
            `;
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = sectionHtml;
            const sectionElement = tempDiv.firstElementChild;
            container.appendChild(sectionElement);
            setupItemDragAndDrop(sectionElement);
        }

        function updateAllLogicPanels() {
            // Update all logic panels when section titles change
            document.querySelectorAll('.logic-panel').forEach(panel => {
                const questionCard = panel.closest('.item-card');
                addLogicPanel(questionCard);
            });
            
            // Update all section logic dropdowns
            const sections = getSectionsList();
            document.querySelectorAll('.section-logic').forEach(select => {
                const currentValue = select.value;
                select.innerHTML = `
                    <option value="continue">{{ __('builder.Continue_next_section') }}</option>
                    ${sections.map(s => `<option value="section-${s.id}">${translations.go_to} ${s.title}</option>`).join('')}
                    <option value="submit">{{ __('builder.Submit_form') }}</option>
                `;
                select.value = currentValue;
            });
        }

        function deleteItem(button) {
            const itemCard = button.closest('.question-card');
            itemCard.remove();
        }

        function deleteSection(button) {
            const sectionWrapper = button.closest('.section-wrapper');
            const hasQuestions = sectionWrapper.querySelectorAll('.question-card').length > 0;
            
            if (hasQuestions && !confirm('{{ __("builder.Delete_section_questions") }}')) {
                return;
            }
            
            sectionWrapper.remove();
            updateAllLogicPanels();
        }

        function saveForm() {
            const formTitle = document.getElementById('formTitle').value.trim();
            const organizationId = document.getElementById('organization_id').value;
            
            if (!formTitle) {
                releaseWarningAlert({ message: '{{ __("builder.Form_title_required") }}' });
                return;
            }
            
            if (!organizationId) {
                releaseWarningAlert({ message: '{{ __("builder.Organization_required") }}' });
                return;
            }

            const formData = {
                title: formTitle,
                organization_id: organizationId,
                items: []
            };

            console.log('formData', formData)

            const container = document.getElementById('itemsContainer');
            const children = Array.from(container.children);

            children.forEach(child => {
                const itemType = child.dataset.itemType;

                if (itemType === 'section') {
                    const section = {
                        type: 'section',
                        title: child.querySelector('.section-title').value,
                        description: child.querySelector('.section-description').value,
                        logic: child.querySelector('.section-logic').value,
                        questions: []
                    };

                    child.querySelectorAll('.question-card').forEach(questionCard => {
                        section.questions.push(extractQuestionData(questionCard));
                    });

                    formData.items.push(section);
                } else if (itemType === 'question') {
                    formData.items.push(extractQuestionData(child));
                }
            });

            fetch('{{ route("forms.design_save", ["id" => isset($title->id) ? $title->id : '0']) }}', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                   form_fields_data: formData // must be a JSON string
               }),
            })
            .then(response => response.json())
            .then(data => {
                const currentMode = '{{ isset($mode) ? $mode : '' }}';
                const redirectUrl = `/dashboard/show-builder/${data.id}${data.organization_id ? '?org_id=' + data.organization_id : ''}`;

                releaseSuccessAlert({ message: '{{ __("builder.Form_saved_successfully") }}' });

                if (!data || !data.success) {
                    return;
                }

                // setTimeout(() => {    
                //     if (data.id) {
                //         if (currentMode === 'EDIT') {
                //             window.location.href = `/dashboard/show-form/${data.id}`;
                //             return;
                //         }
                //         window.location.href = redirectUrl;
                //     }

                // }, 2000);

            })
            .catch(error => {
                console.error('Error:', error);
                releaseFailureAlert({ message: '{{ __("builder.Error_saving_form") }}' });
            });
        }

        function extractQuestionData(questionCard) {
            const questionType = questionCard.querySelector('.question-type').value;
            const question = {
                type: 'question',
                title: questionCard.querySelector('.question-title').value,
                question_type: questionType,
                required: questionCard.querySelector('.question-required').checked,
                options: [],
                logic: {}
            };

            if (['multiple_choice', 'checkboxes', 'dropdown'].includes(questionType)) {
                const optionInputs = questionCard.querySelectorAll('.option-input');
                optionInputs.forEach((input, idx) => {
                    if (input.value.trim()) {
                        let optionValue = input.value.trim();
                        if (optionValue === 'Other...') {
                            optionValue = '__OTHER__';
                        }
                        question.options.push(optionValue);
                        
                        // Get logic for this option if it's multiple choice or dropdown
                        if ((questionType === 'multiple_choice' || questionType === 'dropdown') && questionCard.querySelector(`.option-logic-${idx}`)) {
                            const logicSelect = questionCard.querySelector(`.option-logic-${idx}`);
                            if (logicSelect) {
                                question.logic[optionValue] = logicSelect.value;
                            }
                        }
                    }
                });
            } else if (questionType === 'linear_scale') {
                const scaleFrom = questionCard.querySelector('.scale-from')?.value;
                const scaleTo = questionCard.querySelector('.scale-to')?.value;
                const labelLow = questionCard.querySelector('.scale-label-low')?.value;
                const labelHigh = questionCard.querySelector('.scale-label-high')?.value;
                
                question.scale = {
                    from: scaleFrom,
                    to: scaleTo,
                    label_low: labelLow,
                    label_high: labelHigh
                };
            }

            return question;
        }

        function populateForm(formData, builderData) {
            if (formData) {
                document.getElementById('formTitle').value = formData.form_name || '';
            }

            if (builderData && Array.isArray(builderData)) {
                builderData.forEach(field => {
                    populateField(field);
                });
            }
        }

        function populateField(fieldData) {
            const container = document.getElementById('itemsContainer');
            const fieldId = itemCounter++;
            const questionHtml = createQuestionHTML(fieldId, false);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = questionHtml;
            const fieldElement = tempDiv.firstElementChild;
            container.appendChild(fieldElement);
            setupItemDragAndDrop(fieldElement);
            populateFieldData(fieldElement, fieldData);
        }

        function populateFieldData(questionCard, fieldData) {
            // Set title
            const titleInput = questionCard.querySelector('.question-title');
            if (titleInput) titleInput.value = fieldData.label || '';

            // Map type
            let questionType = 'short_answer';
            if (fieldData.type === 'textarea') {
                questionType = 'paragraph';
            } else if (fieldData.type === 'date') {
                questionType = 'date';
            } else if (fieldData.type === 'file') {
                questionType = 'file'; // Assuming we have file type, or add it
            }
            // Add more mappings as needed

            // Set type
            const typeSelect = questionCard.querySelector('.question-type');
            if (typeSelect) {
                typeSelect.value = questionType;
                updateQuestionOptions(typeSelect);
            }

            // Set required
            const requiredCheckbox = questionCard.querySelector('.question-required');
            if (requiredCheckbox) requiredCheckbox.checked = fieldData.isRequired || false;

            // Handle additional config if needed
            if (fieldData.additionalConfig) {
                if (fieldData.additionalConfig.inputType === 'number' && questionType === 'short_answer') {
                    // Perhaps set a data attribute or placeholder
                    const input = questionCard.querySelector('.question-options input');
                    if (input) input.type = 'number';
                }
                if (fieldData.additionalConfig.textAreaRows && questionType === 'paragraph') {
                    const textarea = questionCard.querySelector('.question-options textarea');
                    if (textarea) textarea.rows = fieldData.additionalConfig.textAreaRows;
                }
            }
        }

        function populateSection(sectionData) {
            const container = document.getElementById('itemsContainer');
            const sectionId = itemCounter++;
            questionCounters[sectionId] = 0;
            
            const sections = getSectionsList();
            
            const sectionHtml = `
                <div class="section-wrapper mb-5" data-item-id="${sectionId}" data-item-type="section">
                    <div class="item-card section-card card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3 mt-3">
                                <i class="ti ti-grip-vertical text-muted drag-handle cursor-move mt-1"></i>
                                <div class="flex-fill">
                                    <input 
                                        type="text" 
                                        class="section-title form-control mb-2"
                                        placeholder="{{ __('builder.Section_title_optional') }}"
                                        onchange="updateAllLogicPanels()"
                                        value="${sectionData.title || ''}"
                                    >
                                    <input 
                                        type="text" 
                                        class="section-description form-control mb-3"
                                        placeholder="{{ __('builder.Section_description_optional') }}"
                                        value="${sectionData.description || ''}"
                                    >
                                </div>

                                <button 
                                    type="button" 
                                    class="delete-section btn btn-link text-muted p-0"
                                    onclick="deleteSection(this)"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ __('builder.Remove_Section') }}"
                                >
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                            
                            <div class="mt-4 pt-4 border-top">
                                <div class="text-muted small font-weight-medium mb-2">{{ __('builder.After_this_section') }}</div>
                                <select class="section-logic form-select">
                                    <option value="continue">{{ __('builder.Continue_next_section') }}</option>
                                    ${sections.map(s => `<option value="section-${s.id}">${s.title}</option>`).join('')}
                                    <option value="submit">{{ __('builder.Submit_form') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-questions d-flex flex-column gap-3 mb-4 px-4 ms-3">
                    </div>

                    <button 
                        type="button"
                        onclick="addQuestionToSection(this)"
                        class="btn btn-outline-primary border-2 border-dashed d-flex align-items-center justify-content-center gap-2 w-100 p-3"
                    >
                        <i class="ti ti-plus"></i>
                        {{ __('builder.Add_Question_Section') }}
                    </button>
                </div>
            `;
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = sectionHtml;
            const sectionElement = tempDiv.firstElementChild;
            container.appendChild(sectionElement);
            setupItemDragAndDrop(sectionElement);

            // Set section logic
            const logicSelect = sectionElement.querySelector('.section-logic');
            if (logicSelect && sectionData.logic) {
                logicSelect.value = sectionData.logic;
            }

            // Populate questions
            if (sectionData.questions) {
                const questionsContainer = sectionElement.querySelector('.section-questions');
                sectionData.questions.forEach(questionData => {
                    populateQuestionToSection(questionsContainer, questionData, sectionId);
                });
            }
        }

        function populateStandaloneQuestion(questionData) {
            const container = document.getElementById('itemsContainer');
            const questionId = itemCounter++;
            const questionHtml = createQuestionHTML(questionId, false);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = questionHtml;
            const questionElement = tempDiv.firstElementChild;
            container.appendChild(questionElement);
            setupItemDragAndDrop(questionElement);
            populateQuestionData(questionElement, questionData);
        }

        function populateQuestionToSection(container, questionData, sectionId) {
            if (!questionCounters[sectionId]) {
                questionCounters[sectionId] = 0;
            }
            const questionId = itemCounter++;
            const questionHtml = createQuestionHTML(questionId, true);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = questionHtml;
            const questionElement = tempDiv.firstElementChild;
            container.appendChild(questionElement);
            setupItemDragAndDrop(questionElement);
            populateQuestionData(questionElement, questionData);
        }

        function populateQuestionData(questionCard, questionData) {
            // Set title
            const titleInput = questionCard.querySelector('.question-title');
            if (titleInput) titleInput.value = questionData.title || '';

            // Set type
            const typeSelect = questionCard.querySelector('.question-type');
            if (typeSelect) {
                typeSelect.value = questionData.question_type || 'short_answer';
                updateQuestionOptions(typeSelect);
            }

            // Set required
            const requiredCheckbox = questionCard.querySelector('.question-required');
            if (requiredCheckbox) requiredCheckbox.checked = questionData.required || false;

            // Set options if applicable
            if (['multiple_choice', 'checkboxes', 'dropdown'].includes(questionData.question_type) && questionData.options) {
                const optionInputs = questionCard.querySelectorAll('.option-input');
                questionData.options.forEach((option, idx) => {
                    let displayValue = option;
                    if (option === '__OTHER__') {
                        displayValue = '{{ __("builder.Other_value") }}';
                    }
                    if (optionInputs[idx]) {
                        optionInputs[idx].value = displayValue;
                    } else {
                        // Add more options if needed
                        const addButton = questionCard.querySelector('.options-list .d-flex.gap-2.mt-2 button');
                        if (addButton && addButton.onclick) {
                            addButton.click();
                            const newInputs = questionCard.querySelectorAll('.option-input');
                            if (newInputs[newInputs.length - 1]) {
                                newInputs[newInputs.length - 1].value = displayValue;
                            }
                        }
                    }
                });
            }

            // Set scale if linear_scale
            if (questionData.question_type === 'linear_scale' && questionData.scale) {
                const scaleFrom = questionCard.querySelector('.scale-from');
                const scaleTo = questionCard.querySelector('.scale-to');
                const labelLow = questionCard.querySelector('.scale-label-low');
                const labelHigh = questionCard.querySelector('.scale-label-high');
                if (scaleFrom) scaleFrom.value = questionData.scale.from || 1;
                if (scaleTo) scaleTo.value = questionData.scale.to || 5;
                if (labelLow) labelLow.value = questionData.scale.label_low || '{{ __("builder.Low") }}';
                if (labelHigh) labelHigh.value = questionData.scale.label_high || '{{ __("builder.High") }}';
            }

            // Set logic if applicable
            if (['multiple_choice', 'dropdown', 'checkboxes'].includes(questionData.question_type)) {
                addLogicPanel(questionCard, questionData.logic || {});
            }
        }

        // Populate form on load if editing
        @if(request()->segment(3))
        populateForm(@json($title), @json($builder_data));
        @endif

        console.log('title', @json($title));
</script>
@stop