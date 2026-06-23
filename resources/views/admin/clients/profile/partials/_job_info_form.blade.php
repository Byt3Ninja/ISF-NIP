<div id="InfoProfileSection">
  <form 
    id="jobInfoForm"
    method="post" 
    action="{{ route('user.profile_update_bio') }}" 
    enctype="multipart/form-data"
  >
    {!! csrf_field() !!}
    <input type="hidden" name="mentor_section" value="1">
    <div class="card-body">
      <legend class="text-dark fw-bold fs-5 border-bottom pb-3 mb-8">
        {{ __('admin.addbio') }}
      </legend>
      <div class="row job-info-row">
        <div class="col-md-12">
          <div class="form-group mb-6">
            <label for="linkedin" class="form-label fs-6 fw-bold">
              {{ __('admin.Linkedin') }}
            </label>
            <div class="input-group input-group-merge">
              <input 
                value="{{ $client->bio ? $client->bio->linkedin : '' }}" 
                name="linkedin" 
                type="text"
                class="form-control" 
                id="linkedin" 
                style="direction: ltr;"
                placeholder="e.g. https://linkedin.com/in/yourname" 
                autocomplete="linkedin"
              />
            </div>
          </div>
          {{-- <div class="form-group mb-6">
            <label class="form-label fs-6 fw-bold">
              {{ __('admin.arbio') }}: 
            </label>
            <div class="input-group input-group-merge">
              <textarea 
                class="form-control" 
                id="bio_ar" 
                name='bio_ar' 
                rows="3" 
                dir="rtl"
                placeholder="{{ __('admin.writebioar') }}"
                oninput="this.value = this.value.trimStart()"
              >{{ $client->bio->bio_ar ?? '' }}</textarea>
            </div>
          </div> --}}
          <div class="form-group mb-6">
            <label class="form-label fs-6 fw-bold">
              {{ __('admin.enbio') }}
            </label>
            <div class="input-group input-group-merge">
              <textarea 
                class="form-control" 
                id="bio_en" 
                name='bio_en' 
                rows="3" 
                dir="ltr"
                placeholder="{{ __('admin.writebioen') }}"
                oninput="this.value = this.value.trimStart()"
              >{{ $client->bio->bio_en ?? '' }}</textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="form-group mb-6 d-grid">
            <label for="selectExpertises" class="form-label fs-6 fw-bold">
              {{ __('admin.SelectExpertises') }}
            </label>
            <small class="mb-3">{{ __('admin.SelectMultiExpertises') }}.</small>
            <select 
              name="expertises[]" 
              class="form-select select2" 
              multiple="multiple" 
              id="selectExpertises"
            >
              @foreach ($expertises as $expertise_group)
              <optgroup label="{{ $expertise_group->title }}">
                @foreach ($expertise_group->children as $expertise)
                <option 
                  value="{{ $expertise->id }}" 
                  @if (in_array($expertise->id, $client->mentorExpertises()->pluck('expertises.id')->toArray())) 
                    selected 
                  @endif
                >
                  {{ $expertise->title }}
                </option>
                @endforeach
              </optgroup>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-12">
          <div class="form-group mb-6 d-grid">
            <label for="SelectIndustries" class="form-label fs-6 fw-bold">
              {{ __('admin.SelectIndustries') }}
            </label>
            <small class="mb-3">{{ __('admin.SelectMultiIndustries') }}.</small>
            <select 
              name="industries[]" 
              class="form-select select2" 
              multiple="multiple" 
              id="SelectIndustries"
            >
              @foreach ($industries as $industry)
                <option  value="{{ $industry->id }}" @if (!empty($client->bio->industries) && in_array($industry->id, $client->bio->industries)) selected @endif > {{ $industry->{'name_'. $l} }} </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>


      <div class="form-group mb-6">
        <label for="entrepreneurial_experience" class="form-label fs-6 fw-bold">
            {{ __('admin.Entrepreneurial Experience') }}
        </label>
        <select 
            name="entrepreneurial_experience" 
            id="entrepreneurial_experience" 
            class="form-select select2"
            onchange="toggleExperienceBrief()"
        >
            <option 
                value="1" 
                {{ optional($client->bio)->entrepreneurial_experience ? 'selected' : '' }}
            >
                {{ __('Yes') }}
            </option>
            <option 
                value="0" 
                {{ optional($client->bio)->entrepreneurial_experience ? '' : 'selected' }}
            >
                {{ __('No') }}
            </option>
        </select>
    </div>
    
    <div class="form-group mb-6" id="experience_brief_container" style="display: none;">
        <label class="form-label fs-6 fw-bold">
            {{ __('admin.Entrepreneurial Experience desc') }}.
        </label>
        <textarea 
            class="form-control" 
            id="basicTextarea" 
            rows="8" 
            name="entrepreneurial_experience_brief"
            oninput="this.value = this.value.trimStart()"
            required
        >
            {{ $client->bio ? $client->bio->entrepreneurial_experience_brief : old('entrepreneurial_experience_brief') }}
        </textarea>
        <label for="tcontent" class="mt-2">
            {{ __('admin.Entrepreneurial Experience Brief') }}
        </label>
    </div>
     
      <div class="form-group mb-6">
        <label for="profile_privacy" class="form-label fs-6 fw-bold">
          {{ __('admin.Profile Privacy') }}
        </label>
        <select 
          name="profile_privacy" 
          id="profile_privacy" 
          class="form-select select2"
        >
          <option 
            value="1" 
            {{ optional($client->bio)->profile_privacy ? 'selected' : '' }}
          >
            {{ __('Yes') }}
          </option>
          <option 
            value="0" 
            {{ optional($client->bio)->profile_privacy ? '' : 'selected' }}
          >
            {{ __('No') }}
          </option>
        </select>
      </div>
      <div class="form-group mb-6">
        <label for="approach" class="form-label fs-6 fw-bold">
          {{ __('admin.mentorship approach') }}:
        </label>
        <div class="form-group" id="approach">
          <div class="form-check">
            <input 
              type='checkbox' 
              id="checkbox1" 
              class="form-check-input" 
              name='mentorship_approach[]' 
              {{ $client->bio && in_array('E-mail', $client->bio->mentorship_approaches) ? 'checked' : '' }}
              value="E-mail"
            />
            <label for="checkbox1" class="form-check-label">
              {{ __('admin.E-mail') }}
            </label>
          </div>
          <div class="form-check">
            <input 
              type='checkbox' 
              id="checkbox2" 
              class="form-check-input" 
              name='mentorship_approach[]' 
              {{ $client->bio && in_array('phone calls', $client->bio->mentorship_approaches) ? 'checked' : '' }}
              value="phone calls"
            />
            <label for="checkbox2" class="form-check-label">
              {{ __('admin.phone calls') }}
            </label>
          </div>
          <div class="form-check">
            <input 
              type='checkbox' 
              id="checkbox3" 
              class="form-check-input" 
              name='mentorship_approach[]' 
              {{ $client->bio && in_array('video calls', $client->bio->mentorship_approaches) ? 'checked' : '' }}
              value="video calls"
            />
            <label for="checkbox3" class="form-check-label">
              {{ __('admin.video calls') }}
            </label>
          </div>
          <div class="form-check">
            <input 
              type='checkbox' 
              id="checkbox4" 
              class="form-check-input" 
              name='mentorship_approach[]' 
              {{ $client->bio && in_array('in-person meetings', $client->bio->mentorship_approaches) ? 'checked' : '' }}
              value="in-person meetings"
            />
            <label for="checkbox4" class="form-check-label">
              {{ __('admin.in-person meetings') }}
            </label>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-4">
      <button type="submit" class="btn btn-primary me-3">
        {{ __('admin.Save_Changes') }}
      </button>
      <button type="reset" class="btn btn-label-secondary" onclick="location.reload()">
        {{ __('admin.Reset') }}
      </button>
    </div>
  </form>
</div>

<script>
  // Validation text
  const translations = {
    en: {
      linkedin: {
        uri: 'Please enter valid linkedin URL'
      },
      bio_ar: {
        arOnly: 'Please enter Arabic Bio'
      },
      bio_en: {
        enOnly: 'Please enter English Bio'
      },
      entrepreneurial_experience_brief: {
        notEmpty: 'The entrepreneurial experience brief is required'
      }
    },
    ar: {
      linkedin: {
        uri: 'يرجى ادخال رابط  لينكد ان صحيح'
      },
      bio_ar: {
        arOnly: 'يرجى إدخال السيرة الذاتية باللغة العربية'
      },
      bio_en: {
        enOnly: 'يرجى إدخال السيرة الذاتية باللغة الإنجليزية'
      },
      entrepreneurial_experience_brief: {
        notEmpty: 'الخبرة الريادية مطلوبة'
      }
    }
  };

  
  document.addEventListener('DOMContentLoaded', function() {
    (function() {
      // Get Form Config
      const jobInfoFormConfig = {
        fields: {
          linkedin: {
            validators: {
              regexp: {
                regexp: /^https?:\/\/(www\.)?linkedin\.com\/in\/[a-zA-Z0-9-_\u00C0-\u017F]+\/?$/,
                message: translations['{{ $l }}'].linkedin.uri
              }
            },
          },
          bio_ar: {
            validators: {
            //   regexp: {
            //     regexp: /^[\u0600-\u06FF\s,|-]*$/i,
            //     message: translations['{{ $l }}'].bio_ar.arOnly
            // }
          },
        },
          bio_en: {
            validators: {
              // regexp: {
              //   regexp: /^[a-zA-Z\s,|-]*$/i,
              //   message: translations['{{ $l }}'].bio_en.enOnly
              // }
            }
          },
          entrepreneurial_experience_brief: {
            validators: {
              notEmpty: {
                message: translations['{{ $l }}'].entrepreneurial_experience_brief.notEmpty
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            // rowSelector: '.job-info-row',
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      }
      
      // Init job info form validation
      const fv = FormValidation.formValidation(document.getElementById('jobInfoForm'), jobInfoFormConfig);
    })();
  });

  function toggleExperienceBrief() {
        let select = document.getElementById('entrepreneurial_experience');
        let briefContainer = document.getElementById('experience_brief_container');
        
        if (select.value == "1") {
            briefContainer.style.display = "block";
        } else {
            briefContainer.style.display = "none";
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        toggleExperienceBrief();
    });

</script>
