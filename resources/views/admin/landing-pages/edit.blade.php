@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
@endsection

@section('content')
  @php
    $sections = [
      [
        'key'      => 'general',
        'icon'     => 'ti-settings',
        'label_en' => 'General',
        'label_ar' => 'عام',
        'route'    => route('admin.landing-page.sections.update', 'general'),
        'fields'   => [
          [
            'name'        => 'logo',
            'type'        => 'image',
            'label_en'    => 'Logo',
            'label_ar'    => 'الشعار',
            'value'       => null,
            'required'    => true,
            'accept'      => '.png,.jpg,.jpeg,.webp',
            'max_size_mb' => 5,
            'col'         => 12,
          ],
          [
            'name'      => 'description',
            'type'      => 'textarea',
            'label_en'  => 'Description',
            'label_ar'  => 'الوصف',
            'value'     => null,
            'required'  => true,
            'maxlength' => 500,
            'col'       => 6,
          ],
          [
            'name'      => 'description_ar',
            'type'      => 'textarea',
            'label_en'  => 'Description (Arabic)',
            'label_ar'  => 'الوصف بالعربية',
            'value'     => null,
            'required'  => true,
            'maxlength' => 500,
            'col'       => 6,
          ],
          [
            'name'      => 'linkedin',
            'type'      => 'url',
            'label_en'  => 'LinkedIn',
            'label_ar'  => 'لينكدإن',
            'value'     => null,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'facebook',
            'type'      => 'url',
            'label_en'  => 'Facebook',
            'label_ar'  => 'فيسبوك',
            'value'     => null,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'instagram',
            'type'      => 'url',
            'label_en'  => 'Instagram',
            'label_ar'  => 'إنستغرام',
            'value'     => null,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'x',
            'type'      => 'url',
            'label_en'  => 'X',
            'label_ar'  => 'إكس',
            'value'     => null,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'tiktok',
            'type'      => 'url',
            'label_en'  => 'TikTok',
            'label_ar'  => 'تيك توك',
            'value'     => null,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
          [
            'name'      => 'youtube',
            'type'      => 'url',
            'label_en'  => 'YouTube',
            'label_ar'  => 'يوتيوب',
            'value'     => null,
            'required'  => false,
            'maxlength' => 255,
            'col'       => 6,
          ],
        ],
      ],
      [
        'key'      => 'hero',
        'icon'     => 'ti-align-box-top-center',
        'label_en' => 'Hero',
        'label_ar' => 'البطل',
        'route'    => route('admin.landing-page.sections.update', 'hero'),
        'fields'   => [
          [
            'name'      => 'title',
            'type'      => 'text',
            'label_en'  => 'Title',
            'label_ar'  => 'العنوان',
            'value'     => null,
            'required'  => true,
            'maxlength' => 100,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag',
            'type'      => 'text',
            'label_en'  => 'Title tag',
            'label_ar'  => 'العنوان الفرعي',
            'value'     => null,
            'required'  => true,
            'maxlength' => 150,
            'col'       => 6,
          ],
          [
            'name'      => 'description',
            'type'      => 'textarea',
            'label_en'  => 'Description',
            'label_ar'  => 'الوصف',
            'value'     => null,
            'required'  => true,
            'maxlength' => 500,
            'col'       => 6,
          ],
          [
            'name'      => 'title_ar',
            'type'      => 'text',
            'label_en'  => 'Title (Arabic)',
            'label_ar'  => 'العنوان بالعربية',
            'value'     => null,
            'required'  => true,
            'maxlength' => 100,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag_ar',
            'type'      => 'text',
            'label_en'  => 'Title tag (Arabic)',
            'label_ar'  => 'العنوان الفرعي بالعربية',
            'value'     => null,
            'required'  => true,
            'maxlength' => 150,
            'col'       => 6,
          ],
          [
            'name'      => 'description_ar',
            'type'      => 'textarea',
            'label_en'  => 'Description (Arabic)',
            'label_ar'  => 'الوصف بالعربية',
            'value'     => null,
            'required'  => true,
            'maxlength' => 500,
            'col'       => 6,
          ],
          [
            'name'        => 'hero_image',
            'type'        => 'image',
            'label_en'    => 'Hero image',
            'label_ar'    => 'صورة القسم الرئيسي',
            'value'       => null,
            'required'    => true,
            'accept'      => '.png,.jpg,.jpeg,.webp',
            'max_size_mb' => 5,
            'col'         => 12,
          ],
        ],
      ],
      [
        'key'      => 'about',
        'icon'     => 'ti-users',
        'label_en' => 'About Us',
        'label_ar' => 'من نحن',
        'route'    => route('admin.landing-page.sections.update', 'about'),
        'fields'   => [
          [
            'name'      => 'is_enabled',
            'type'      => 'checkbox',
            'label_en'  => 'Enable Section',
            'label_ar'  => 'تفعيل القسم',
            'value'     => 1,
            'col'       => 12,
          ],
          [
            'name'      => 'title',
            'type'      => 'text',
            'label_en'  => 'Title',
            'label_ar'  => 'العنوان',
            'value'     => null,
            'required'  => true,
            'maxlength' => 100,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag',
            'type'      => 'text',
            'label_en'  => 'Title tag',
            'label_ar'  => 'العنوان الفرعي',
            'value'     => null,
            'required'  => true,
            'maxlength' => 150,
            'col'       => 6,
          ],
          [
            'name'      => 'description',
            'type'      => 'textarea',
            'label_en'  => 'Description',
            'label_ar'  => 'الوصف',
            'value'     => null,
            'required'  => true,
            'maxlength' => 500,
            'col'       => 6,
          ],
          [
            'name'      => 'title_ar',
            'type'      => 'text',
            'label_en'  => 'Title (Arabic)',
            'label_ar'  => 'العنوان بالعربية',
            'value'     => null,
            'required'  => true,
            'maxlength' => 100,
            'col'       => 6,
          ],
          [
            'name'      => 'title_tag_ar',
            'type'      => 'text',
            'label_en'  => 'Title tag (Arabic)',
            'label_ar'  => 'العنوان الفرعي بالعربية',
            'value'     => null,
            'required'  => true,
            'maxlength' => 150,
            'col'       => 6,
          ],
          [
            'name'      => 'description_ar',
            'type'      => 'textarea',
            'label_en'  => 'Description (Arabic)',
            'label_ar'  => 'الوصف بالعربية',
            'value'     => null,
            'required'  => true,
            'maxlength' => 500,
            'col'       => 6,
          ],
        ],
      ],
      [
        'key'      => 'programs',
        'icon'     => 'ti-rocket',
        'label_en' => 'Programs',
        'label_ar' => 'البرامج',
        'route'    => route('admin.landing-page.sections.update', 'programs'),
        'fields'   => [],
      ],
      [
        'key'      => 'how',
        'icon'     => 'ti-settings-bolt',
        'label_en' => 'How It Works',
        'label_ar' => 'كيف يعمل',
        'route'    => route('admin.landing-page.sections.update', 'how'),
        'fields'   => [],
      ],
      [
        'key'      => 'custom',
        'icon'     => 'ti-puzzle',
        'label_en' => 'Custom sections',
        'label_ar' => 'أقسام مخصصة',
        'route'    => route('admin.landing-page.sections.update', 'custom'),
        'fields'   => [],
      ],
    ];
  @endphp

  {{-- Header --}}
  <div class="mb-4">
    <h4 class="mb-1">
      {{ $l == 'ar' ? 'تعديل صفحة هبوط' : 'Edit Landing Page' }}
    </h4>
    <p class="text-secondary mb-0">
      {{ $l == 'ar' ? 'أدِر صفحة هبوط مخصصة لبرامجك.' : 'Manage tailored landing page for your programs.' }}
    </p>
  </div>

  <div class="card mb-4">
    <div class="card-header border-bottom">
      <h6 class="card-title mb-0 d-flex align-items-center gap-2">
        <i class="ti ti-layout-dashboard"></i>{{ __('admin.Landing Page Sections') }}
      </h6>
    </div>
    <div class="card-body p-0">
      <div class="nav-align-left">
        <ul class="nav nav-tabs py-4" role="tablist">
          @foreach ($sections as $index => $section)
            <li class="nav-item py-1" role="presentation">
              <button
                type="button"
                class="nav-link waves-effect @if($index === 0) active @endif"
                role="tab"
                data-bs-toggle="tab"
                data-bs-target="#navs-{{ $section['key'] }}"
                aria-controls="navs-{{ $section['key'] }}"
                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                @if($index !== 0) tabindex="-1" @endif
              >
                <i class="ti {{ $section['icon'] }} me-2"></i>
                {{ $l == 'ar' ? $section['label_ar'] : $section['label_en'] }}
              </button>
            </li>
          @endforeach
        </ul>

        <div class="tab-content">
          @foreach ($sections as $index => $section)
            <div
              class="tab-pane fade @if($index === 0) show active @endif"
              id="navs-{{ $section['key'] }}"
              role="tabpanel"
            >
              <form
                action="{{ $section['route'] }}"
                method="POST"
                enctype="multipart/form-data"
                class="section-form"
                data-section="{{ $section['key'] }}"
                novalidate
              >
                @csrf
                @method('PUT')

                {{-- Save button, top-right of each section --}}
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <h6 class="mb-0">
                    {{ $l == 'ar' ? $section['label_ar'] : $section['label_en'] }}
                  </h6>
                  <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                    <i class="ti ti-device-floppy"></i>
                    {{ $l == 'ar' ? 'حفظ' : 'Save' }}
                  </button>
                </div>

                <div class="row g-4">
                  @foreach ($section['fields'] as $field)
                    @php
                      $fieldName  = "{$section['key']}[{$field['name']}]";
                      $fieldId    = "{$section['key']}_{$field['name']}";
                      $fieldLabel = $l == 'ar' ? $field['label_ar'] : $field['label_en'];
                      $colWidth   = $field['col'] ?? 12;
                    @endphp
                    <div class="col-md-{{ $colWidth }}">
                      <label for="{{ $fieldId }}" class="form-label">
                        {{ $fieldLabel }}
                        @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                      </label>

                      @switch($field['type'])
                        @case('textarea')
                          <textarea
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            rows="4"
                            @if(!empty($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                            @if(!empty($field['required'])) required @endif
                            class="form-control @error($fieldName) is-invalid @enderror"
                          >{{ old($fieldName, $field['value']) }}</textarea>
                          @if(!empty($field['maxlength']))
                            <div class="form-text">
                              {{ $l == 'ar' ? 'الحد الأقصى' : 'Max' }} {{ $field['maxlength'] }}
                              {{ $l == 'ar' ? 'حرف' : 'characters' }}
                            </div>
                          @endif
                          @break

                        @case('url')
                          <input
                            type="url"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ old($fieldName, $field['value']) }}"
                            @if(!empty($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                            @if(!empty($field['required'])) required @endif
                            placeholder="https://"
                            class="form-control @error($fieldName) is-invalid @enderror"
                          >
                          @break

                        @case('select')
                          <select
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            @if(!empty($field['required'])) required @endif
                            class="form-select @error($fieldName) is-invalid @enderror"
                          >
                            @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                              <option
                                value="{{ $optionValue }}"
                                @selected(old($fieldName, $field['value']) == $optionValue)
                              >
                                {{ $optionLabel }}
                              </option>
                            @endforeach
                          </select>
                          @break

                        @case('checkbox')
                          <div class="form-check form-switch">
                            <input
                              type="hidden"
                              name="{{ $fieldName }}"
                              value="0"
                            >
                            <input
                              type="checkbox"
                              id="{{ $fieldId }}"
                              name="{{ $fieldName }}"
                              value="1"
                              class="form-check-input"
                              @checked(old($fieldName, $field['value']))
                            >
                          </div>
                          @break

                        @case('image')
                          <div class="d-flex align-items-center gap-3">
                            @if(!empty($field['value']))
                              <img
                                src="{{ $field['value'] }}"
                                alt="{{ $fieldLabel }}"
                                class="rounded"
                                style="width: 64px; height: 64px; object-fit: cover;"
                              >
                            @endif
                            <div class="flex-grow-1">
                              <input
                                type="file"
                                id="{{ $fieldId }}"
                                name="{{ $fieldName }}"
                                accept="{{ $field['accept'] ?? 'image/*' }}"
                                @if(!empty($field['required']) && empty($field['value'])) required @endif
                                class="form-control @error($fieldName) is-invalid @enderror"
                              >
                              @if(!empty($field['accept']) || !empty($field['max_size_mb']))
                                <div class="form-text">
                                  @if(!empty($field['accept']))
                                    {{ strtoupper(str_replace(['.', ','], ['', ', '], trim($field['accept']))) }}
                                  @endif
                                  @if(!empty($field['max_size_mb']))
                                    &mdash; {{ $l == 'ar' ? 'الحد الأقصى' : 'Max' }} {{ $field['max_size_mb'] }}MB
                                  @endif
                                </div>
                              @endif
                            </div>
                          </div>
                          @break

                        @default
                          <input
                            type="text"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ old($fieldName, $field['value']) }}"
                            @if(!empty($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                            @if(!empty($field['required'])) required @endif
                            class="form-control @error($fieldName) is-invalid @enderror"
                          >
                      @endswitch

                      @error($fieldName)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                      @enderror
                    </div>
                  @endforeach
                </div>

                @if($section['key'] === 'about')
                  <hr class="my-4">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">
                      {{ $l == 'ar' ? 'البطاقات' : 'Cards' }}
                    </h6>
                    <button
                      type="button"
                      class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                      id="aboutAddCard"
                    >
                      <i class="ti ti-plus"></i>
                      {{ $l == 'ar' ? 'إضافة بطاقة' : 'Add Card' }}
                    </button>
                  </div>
                  <div class="row g-4" id="aboutCardsContainer"></div>
                @endif
              </form>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@endsection

@section('pagescripts')
  <script>
    $(function () {
      const urlTab = new URLSearchParams(window.location.search).get('tab');
      if (urlTab) {
        const $targetBtn = $('button[data-bs-target="#' + urlTab + '"]');
        if ($targetBtn.length) {
          new bootstrap.Tab($targetBtn[0]).show();
        }
      }

      $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        const tabId = $(this).attr('data-bs-target').replace('#', '');
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
      });

      // ─────────────────────────────────────────────────────────────
      // Section forms — FormValidation (client-side)
      // Validation errors only block the save; they never reset the form.
      // ─────────────────────────────────────────────────────────────
      const isArabic = {{ $l == 'ar' ? 'true' : 'false' }};
      const maxImageSize = 5 * 1024 * 1024; // 5 MB

      const validationMessages = {
        logoRequired:       isArabic ? 'يرجى تحميل الشعار.' : 'Please upload a logo.',
        logoUnsupported:    isArabic ? 'صيغة الملف غير مدعومة. يرجى تحميل ملف بصيغة PNG أو JPG أو JPEG أو WEBP.' : 'Unsupported file format. Please upload a PNG, JPG, JPEG, or WEBP file.',
        logoSize:           isArabic ? 'يجب ألا يتجاوز حجم الشعار 5 ميجابايت.' : 'Logo size must not exceed 5 MB.',
        description:        isArabic ? 'يرجى إدخال الوصف.' : 'Please enter a description.',
        descriptionArabic:  isArabic ? 'يرجى إدخال الوصف باللغة العربية.' : 'Please enter a description in Arabic.',
        invalidUrl:         isArabic ? 'يرجى إدخال رابط صالح.' : 'Please enter a valid URL.',
        heroTitle:          isArabic ? 'يرجى إدخال العنوان.' : 'Please enter the title.',
        heroSubtitle:       isArabic ? 'يرجى إدخال العنوان الفرعي.' : 'Please enter the subtitle.',
        heroTitleArabic:    isArabic ? 'يرجى إدخال العنوان باللغة العربية.' : 'Please enter the title in Arabic.',
        heroSubtitleArabic: isArabic ? 'يرجى إدخال العنوان الفرعي باللغة العربية.' : 'Please enter the subtitle in Arabic.',
        heroImageRequired:  isArabic ? 'يرجى تحميل صورة القسم الرئيسي.' : 'Please upload a hero image.',
        heroImageFormat:    isArabic ? 'صيغة الملف غير مدعومة. يرجى تحميل ملف بصيغة PNG أو JPG أو JPEG أو WEBP.' : 'Unsupported file format. Please upload a PNG, JPG, JPEG, or WEBP file.',
        heroImageSize:      isArabic ? 'يجب ألا يتجاوز حجم صورة القسم الرئيسي 5 ميجابايت.' : 'Hero image size must not exceed 5 MB.',
        aboutTitle:         isArabic ? 'يرجى إدخال عنوان القسم.' : 'Please enter the section title.',
        aboutSubtitle:      isArabic ? 'يرجى إدخال العنوان الفرعي للقسم.' : 'Please enter the section subtitle.',
        aboutDescription:   isArabic ? 'يرجى إدخال وصف القسم.' : 'Please enter the section description.',
        aboutTitleArabic:   isArabic ? 'يرجى إدخال عنوان القسم باللغة العربية.' : 'Please enter the section title in Arabic.',
        aboutSubtitleArabic: isArabic ? 'يرجى إدخال العنوان الفرعي للقسم باللغة العربية.' : 'Please enter the section subtitle in Arabic.',
        aboutDescriptionArabic: isArabic ? 'يرجى إدخال وصف القسم باللغة العربية.' : 'Please enter the section description in Arabic.',
        cardIcon:           isArabic ? 'يرجى اختيار أيقونة.' : 'Please select an icon.',
        cardTitle:          isArabic ? 'يرجى إدخال عنوان البطاقة.' : 'Please enter a card title.',
        cardDescription:    isArabic ? 'يرجى إدخال وصف البطاقة.' : 'Please enter a card description.',
        cardTitleArabic:    isArabic ? 'يرجى إدخال عنوان البطاقة باللغة العربية.' : 'Please enter a card title in Arabic.',
        cardDescriptionArabic: isArabic ? 'يرجى إدخال وصف البطاقة باللغة العربية.' : 'Please enter a card description in Arabic.',
      };

      const successMessages = {
        general: isArabic ? 'تم حفظ المعلومات العامة لصفحة الهبوط بنجاح.' : 'General landing page information saved successfully.',
        hero:    isArabic ? 'تم حفظ معلومات القسم الرئيسي بنجاح.' : 'Hero section information saved successfully.',
        about:   isArabic ? 'تم حفظ معلومات قسم من نحن بنجاح.' : 'About section information saved successfully.',
      };

      const buildImageField = function (requiredMsg, formatMsg, sizeMsg) {
        return {
          validators: {
            notEmpty: {
              message: requiredMsg,
            },
            file: {
              extension: 'png,jpg,jpeg,webp',
              message: formatMsg,
            },
            callback: {
              message: sizeMsg,
              callback: function (input) {
                const files = input.element.files;
                if (!files || !files.length) {
                  return { valid: true };
                }
                return { valid: files[0].size <= maxImageSize };
              },
            },
          },
        };
      };

      const sectionFields = {
        general: {
          'general[logo]': buildImageField(
            validationMessages.logoRequired,
            validationMessages.logoUnsupported,
            validationMessages.logoSize
          ),
          'general[description]': {
            validators: {
              notEmpty: { message: validationMessages.description },
            },
          },
          'general[description_ar]': {
            validators: {
              notEmpty: { message: validationMessages.descriptionArabic },
            },
          },
        },
        hero: {
          'hero[title]': {
            validators: {
              notEmpty: { message: validationMessages.heroTitle },
            },
          },
          'hero[title_tag]': {
            validators: {
              notEmpty: { message: validationMessages.heroSubtitle },
            },
          },
          'hero[description]': {
            validators: {
              notEmpty: { message: validationMessages.description },
            },
          },
          'hero[title_ar]': {
            validators: {
              notEmpty: { message: validationMessages.heroTitleArabic },
            },
          },
          'hero[title_tag_ar]': {
            validators: {
              notEmpty: { message: validationMessages.heroSubtitleArabic },
            },
          },
          'hero[description_ar]': {
            validators: {
              notEmpty: { message: validationMessages.descriptionArabic },
            },
          },
          'hero[hero_image]': buildImageField(
            validationMessages.heroImageRequired,
            validationMessages.heroImageFormat,
            validationMessages.heroImageSize
          ),
        },
        about: {
          'about[title]': {
            validators: {
              notEmpty: { message: validationMessages.aboutTitle },
            },
          },
          'about[title_tag]': {
            validators: {
              notEmpty: { message: validationMessages.aboutSubtitle },
            },
          },
          'about[description]': {
            validators: {
              notEmpty: { message: validationMessages.aboutDescription },
            },
          },
          'about[title_ar]': {
            validators: {
              notEmpty: { message: validationMessages.aboutTitleArabic },
            },
          },
          'about[title_tag_ar]': {
            validators: {
              notEmpty: { message: validationMessages.aboutSubtitleArabic },
            },
          },
          'about[description_ar]': {
            validators: {
              notEmpty: { message: validationMessages.aboutDescriptionArabic },
            },
          },
        },
      };

      // Optional social links: only validate when a value is filled in.
      ['linkedin', 'facebook', 'instagram', 'x', 'tiktok', 'youtube'].forEach(function (name) {
        sectionFields.general['general[' + name + ']'] = {
          validators: {
            uri: {
              protocol: 'http, https',
              message: validationMessages.invalidUrl,
            },
          },
        };
      });

      const sectionValidations = {};
      $('.section-form').each(function () {
        const sectionKey = $(this).data('section');
        const fields = sectionFields[sectionKey];
        if (fields && typeof FormValidation !== 'undefined') {
          sectionValidations[sectionKey] = FormValidation.formValidation(this, {
            fields: fields,
            plugins: {
              trigger: new FormValidation.plugins.Trigger(),
              bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleValidClass: '',
              }),
              autoFocus: new FormValidation.plugins.AutoFocus(),
            },
          });
        }
      });

      // ─────────────────────────────────────────────────────────────
      // About section — cards repeater (max 3 cards)
      // Card fields are all-or-nothing: an untouched card is valid, but
      // once any field of a card is filled every card field becomes
      // mandatory.
      // ─────────────────────────────────────────────────────────────
      const aboutMaxCards = 3;
      const aboutCardFields = ['icon', 'title', 'description', 'title_ar', 'description_ar'];
      const aboutCardMessages = {
        icon:           validationMessages.cardIcon,
        title:          validationMessages.cardTitle,
        description:    validationMessages.cardDescription,
        title_ar:       validationMessages.cardTitleArabic,
        description_ar: validationMessages.cardDescriptionArabic,
      };
      const aboutIcons = [
        { value: 'ti-rocket', label: 'Rocket' },
        { value: 'ti-bulb', label: 'Idea' },
        { value: 'ti-target', label: 'Target' },
        { value: 'ti-chart-line', label: 'Growth' },
        { value: 'ti-users', label: 'Community' },
        { value: 'ti-trophy', label: 'Achievement' },
        { value: 'ti-shield', label: 'Protection' },
        { value: 'ti-heart', label: 'Care' },
        { value: 'ti-star', label: 'Quality' },
        { value: 'ti-book', label: 'Education' },
        { value: 'ti-code', label: 'Code' },
        { value: 'ti-cpu', label: 'Technology' },
        { value: 'ti-world', label: 'Global' },
        { value: 'ti-flag', label: 'Goal' },
        { value: 'ti-crown', label: 'Leadership' },
        { value: 'ti-gift', label: 'Reward' },
        { value: 'ti-briefcase', label: 'Career' },
        { value: 'ti-cloud', label: 'Cloud' },
      ];
      let aboutCardCounter = 0;

      const validateAboutCardField = function (element) {
        const $card = $(element).closest('.about-card');
        const hasAny = $card.find('[data-card-field]').toArray().some(function (el) {
          const $el = $(el);
          return $el.is('select') ? !!$el.val() : $el.val().trim() !== '';
        });
        if (!hasAny) return { valid: true };
        if (element.tagName === 'SELECT') {
          return { valid: element.value !== '' };
        }
        return { valid: element.value.trim() !== '' };
      };

      const aboutCardHtml = function (id) {
        const iconOptions = aboutIcons.map(function (icon) {
          return '<option value="' + icon.value + '">' + icon.label + '</option>';
        }).join('');
        return `
          <div class="col-12 about-card" data-card-id="${id}">
            <div class="border rounded p-3">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 about-card-label">${isArabic ? 'البطاقة' : 'Card'}</h6>
                <button type="button" class="btn btn-label-danger btn-sm d-flex align-items-center gap-1 remove-about-card">
                  <i class="ti ti-trash"></i>
                  ${isArabic ? 'حذف' : 'Remove'}
                </button>
              </div>
              <div class="row g-4">
                <div class="col-md-4">
                  <label class="form-label">${isArabic ? 'الأيقونة' : 'Icon'}</label>
                  <select name="about[cards][${id}][icon]" class="form-select" data-card-field>
                    <option value="">${isArabic ? '— اختر أيقونة —' : '— Select an icon —'}</option>
                    ${iconOptions}
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label">${isArabic ? 'العنوان' : 'Title'}</label>
                  <input type="text" name="about[cards][${id}][title]" maxlength="100" class="form-control" data-card-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'العنوان بالعربية' : 'Title (Arabic)'}</label>
                  <input type="text" name="about[cards][${id}][title_ar]" maxlength="100" class="form-control" data-card-field>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'الوصف' : 'Description'}</label>
                  <textarea name="about[cards][${id}][description]" maxlength="500" rows="4" class="form-control" data-card-field></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">${isArabic ? 'الوصف بالعربية' : 'Description (Arabic)'}</label>
                  <textarea name="about[cards][${id}][description_ar]" maxlength="500" rows="4" class="form-control" data-card-field></textarea>
                </div>
              </div>
            </div>
          </div>
        `;
      };

      const updateAboutCardButtons = function () {
        const count = $('#aboutCardsContainer .about-card').length;
        $('#aboutAddCard').prop('disabled', count >= aboutMaxCards);
        $('#aboutCardsContainer .about-card').each(function (i) {
          $(this).find('.about-card-label').text((isArabic ? 'البطاقة ' : 'Card ') + (i + 1));
        });
      };

      const addAboutCard = function () {
        const fv = sectionValidations.about;
        const id = ++aboutCardCounter;
        $('#aboutCardsContainer').append(aboutCardHtml(id));
        if (fv) {
          aboutCardFields.forEach(function (field) {
            fv.addField('about[cards][' + id + '][' + field + ']', {
              validators: {
                callback: {
                  message: aboutCardMessages[field],
                  callback: function (input) {
                    return validateAboutCardField(input.element);
                  },
                },
              },
            });
          });
        }
        updateAboutCardButtons();
      };

      $('#aboutAddCard').on('click', addAboutCard);

      $('#aboutCardsContainer')
        .on('input change', '[data-card-field]', function () {
          const fv = sectionValidations.about;
          if (!fv) return;
          const id = $(this).closest('.about-card').data('card-id');
          aboutCardFields.forEach(function (field) {
            fv.revalidateField('about[cards][' + id + '][' + field + ']');
          });
        })
        .on('click', '.remove-about-card', function () {
          const fv = sectionValidations.about;
          const $card = $(this).closest('.about-card');
          const id = $card.data('card-id');
          if (fv) {
            aboutCardFields.forEach(function (field) {
              fv.removeField('about[cards][' + id + '][' + field + ']');
            });
          }
          $card.remove();
          updateAboutCardButtons();
        });

      // About main fields are only mandatory while the section is enabled.
      $('#about_is_enabled').on('change', function () {
        const fv = sectionValidations.about;
        if (!fv) return;
        const enabled = this.checked;
        ['title', 'title_tag', 'description', 'title_ar', 'title_tag_ar', 'description_ar'].forEach(function (name) {
          const fieldName = 'about[' + name + ']';
          if (enabled) {
            fv.enableValidator(fieldName, 'notEmpty');
          } else {
            fv.disableValidator(fieldName, 'notEmpty');
          }
          fv.revalidateField(fieldName);
        });
      });

      // Each section saves independently via AJAX so switching tabs never
      // loses unsaved input in other sections.
      $('.section-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const sectionKey = $form.data('section');
        const fv = sectionValidations[sectionKey];

        const submitViaAjax = function () {
          const $btn = $form.find('button[type="submit"]');
          const originalHtml = $btn.html();

          $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

          $.ajax({
            url: $form.attr('action'),
            method: 'POST', // method-spoofed to PUT via @method('PUT') hidden field
            data: new FormData(this),
            processData: false,
            contentType: false,
          })
            .done(function () {
              $btn.removeClass('btn-primary').addClass('btn-success').html('<i class="ti ti-check"></i>');
              if (successMessages[sectionKey]) {
                releaseSuccessAlert({
                  title: "{{ __('admin.success') }}",
                  message: successMessages[sectionKey],
                });
              }
              setTimeout(() => {
                $btn.removeClass('btn-success').addClass('btn-primary').html(originalHtml);
              }, 1500);
            })
            .fail(function (xhr) {
              $btn.html(originalHtml);
              let errorMessage = "{{ __('admin.unexpected_error') }}";
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              releaseFailureAlert({
                title: "{{ __('admin.Error') }}",
                message: errorMessage,
              });
            })
            .always(function () {
              $btn.prop('disabled', false);
            });
        }.bind(this);

        // Block the save until client-side validation passes.
        if (!fv) {
          submitViaAjax();
          return;
        }

        fv.validate().then(function (status) {
          if (status !== 'Valid') return;
          submitViaAjax();
        });
      });
    });
  </script>
@endsection
