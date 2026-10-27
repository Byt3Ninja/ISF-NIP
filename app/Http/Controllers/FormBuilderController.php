<?php

namespace App\Http\Controllers;

use App\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator, DB, Mail};
use App\BuilderForm;
use App\BuilderField;
use App\Field;

class FormBuilderController extends Controller
{
    use \App\Traits\ValidatesUrlDomains;

    /**
     * show the builder
     */
    /**
     * Abort unless the current user is a platform admin or the organization is
     * within their tenant scope. A permission says which action is allowed, not
     * on whose records it may be performed.
     */
    private function authorizeOrganization($organizationId): void
    {
        // managesOrganization, not ownsOrganization: a founder "owns" their own program's organization.
        if (!auth()->user()->managesOrganization($organizationId)) {
            abort(403);
        }
    }

    public function showBuilder(Request $request, $id = null)
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        
        // Get organization_id from request or user's organization
        $organization_id = $request->org_id ?? ($user->organization_id ?? null);

        // org_id comes from the query string: it may only name an organization in the caller's scope.
        if ($request->filled('org_id')) {
            $this->authorizeOrganization($request->org_id);
        }

        // Check if there are no organizations
        if (empty($orgIds) && Organization::count() == 0) {
            return redirect(route('organizations.index'))->with('message2', 'add organizations first');
        }

        // When there's NO id in the URL → treat it as a new form creation
        // if (is_null($id)) {
        //     $title = null; // <-- new form, so title is null
        // } else {
        //     // Otherwise, load the existing form by id
        //     $title = BuilderForm::where('id', $id)
        //         ->when(!is_null($organization_id), function ($q) use ($organization_id) {
        //             return $q->where('organization_id', $organization_id);
        //         })
        //         ->first();

        //     // If not found, fall back to organization-based defaults
        //     if (!$title) {
        //         $title = BuilderForm::when(!is_null($organization_id), function ($q) use ($organization_id) {
        //             return $q->where('organization_id', $organization_id);
        //         })->first();

        //         if (!$title) {
        //             $title = BuilderForm::firstOrCreate([
        //                 'form_name' => 'New Empty Form',
        //                 'organization_id' => $organization_id ?? Organization::first()->id
        //             ]);
        //         }

        //         $id = $title->id;
        //     }
        // }

        // $fields = Field::all()->mapWithKeys(fn($field) => [$field->field_type => $field->id]);

        // $builder_fields = BuilderField::where('builder_form_id', $id)->orderBy('id', 'asc');

        // if ($builder_fields->exists()) {
        //     $field_data = $builder_fields->get()->map(function ($field) use ($fields) {
        //         switch ($fields->search($field->field_id)) {
        //             case 'select':
        //                 $type = ['type' => 'select', 'additionalConfig' => ['listOptions' => $field->options->values]];
        //                 break;
        //             case 'textarea':
        //                 $type = ['type' => 'textarea', 'additionalConfig' => ['textAreaRows' => $field->options->rows]];
        //                 break;
        //             case 'file':
        //                 $type = ['type' => 'file', 'additionalConfig' => ['inputType' => $field->options->type]];
        //                 break;
        //             default:
        //                 $type = ($field->options->type != 'date')
        //                     ? ['type' => 'input', 'additionalConfig' => ['inputType' => $field->options->type]]
        //                     : ['type' => 'date', 'additionalConfig' => []];
        //         }

        //         return [[
        //             'id' => $field->id,
        //             'label' => $field->options->label,
        //             'isRequired' => intval($field?->options?->validation?->required) === 1,
        //             ...$type
        //         ]];
        //     });
        // }

        // // Load builder forms list
        // if (!is_null(auth()->user()->organization_id)) {
        //     $builderForms = BuilderForm::where('organization_id', auth()->user()->organization_id)->get();
        // } else {
        //     $builderForms = BuilderForm::all();
        // }

        // if ($request->org_id) {
        //     $builderForms = BuilderForm::where('organization_id', $request->org_id)->get();
        // }

        // $data = [
        //     'builderForms' => $builderForms,
        //     'title' => 'form builder',
        //     'builder_data' => $builder_fields->exists() ? $field_data->flatten(1)->toArray() : []
        // ];

        if (is_null($id)) {
            $title = null; // <-- new form, so title is null
        } else {
            $title = BuilderForm::where('id', $id)
                ->when(!is_null($organization_id), function ($q) use ($organization_id) {
                    return $q->where('organization_id', $organization_id);
                })
                ->first();

            if ($title) {
                $this->authorizeOrganization($title->organization_id);
            }

            if (!$title) {
                // Fall back to a form of the caller's organization (never another tenant's).
                $fallbackOrgId = $organization_id
                    ?? ($user->hasRole('admin') ? Organization::first()->id : ($orgIds[0] ?? null));
                abort_if(is_null($fallbackOrgId), 403);

                $title = BuilderForm::where('organization_id', $fallbackOrgId)->first();

                if (!$title) {
                    $title = BuilderForm::firstOrNew([
                        'form_name' => 'New Empty Form',
                        'organization_id' => $fallbackOrgId,
                    ]);
                    if (!$title->exists) {
                        // organization_id is not mass-assignable ($fillable); set it explicitly.
                        $title->organization_id = $fallbackOrgId;
                        $title->save();
                    }
                }

                $id = $title->id;
            }
        }

        $fields = Field::all()->mapWithKeys(fn($field) => [$field->field_type => $field->id]);

        $builder_fields = BuilderField::where('builder_form_id', $id)->orderBy('sort_order', 'asc');

        if ($builder_fields->exists()) {
            $field_data = $builder_fields->get()->map(function ($field) use ($fields) {
                $options = $field->options ?? (object)[];
                $logic = $options->logic ?? 'continue';
                $section = $options->section ?? 'Default Section';
                $description = $options->description ?? '';

                switch ($fields->search($field->field_id)) {
                    case 'select':
                        $type = $options->type ?? 'dropdown';
                        $listOptions = $options->values ?? [];
                        break;
                    case 'textarea':
                        $type = $options->type ?? 'paragraph';
                        $listOptions = [];
                        break;
                    case 'file':
                        $type = $options->type ?? 'file';
                        $listOptions = [];
                        break;
                    default:
                        $type = $options->type ?? 'text';
                        $listOptions = $options->values ?? [];
                }

                return [
                    'id' => $field->id,
                    'section' => $section,
                    'description' => $description,
                    'label' => $options->label ?? '',
                    'is_required' => intval($options->validation->required ?? 0) === 1,
                    'type' => $type,
                    'options' => is_array($listOptions) ? $listOptions : explode(',', $listOptions),
                    'logic' => $logic,
                    // Re-hydrate the URL "allowed domains" config so the builder
                    // repopulates it on edit (the JS reads url_config.allowed_domains).
                    'url_config' => $type === 'url'
                        ? ['allowed_domains' => $options->allowed_domains ?? '']
                        : null,
                    // Re-hydrate the number min/max/step config so the builder
                    // repopulates it on edit (the JS reads number_config.min/max/step).
                    'number_config' => $type === 'number'
                        ? [
                            'min'  => $options->min  ?? '',
                            'max'  => $options->max  ?? '',
                            'step' => $options->step ?? '',
                        ]
                        : null,
                    // Re-hydrate the linear scale endpoints/labels so the
                    // builder repopulates them on edit (the JS reads
                    // scale.from/to/label_low/label_high).
                    'scale' => $type === 'linear_scale'
                        ? [
                            'from'       => $options->min ?? 1,
                            'to'         => $options->max ?? 5,
                            'label_low'  => $options->label_low  ?? '',
                            'label_high' => $options->label_high ?? '',
                        ]
                        : null,
                ];
            });

            // Group by section
            $grouped_data = $field_data
                ->groupBy('section')
                ->map(function ($fields, $section_title) {
                    $first = $fields->first();
                    return [
                        'title' => $section_title,
                        'description' => $first['description'] ?? '',
                        'logic' => is_array($first['logic'])
                            ? ($first['logic']['section'] ?? 'continue')
                            : ($first['logic'] ?? 'continue'),
                        'questions' => $fields->map(function ($f) {
                            return [
                                'id' => $f['id'],
                                'label' => $f['label'],
                                'is_required' => $f['is_required'],
                                'type' => $f['type'],
                                'options' => $f['options'],
                                'logic' => $f['logic'],
                                'url_config' => $f['url_config'] ?? null,
                                'number_config' => $f['number_config'] ?? null,
                                'scale' => $f['scale'] ?? null,
                            ];
                        })->values()->toArray(),
                    ];
                })
                ->values()
                ->toArray();
        } else {
            $grouped_data = [];
        }

        // Load builder forms list
        if ($user->hasRole('admin')) {
            $builderForms = BuilderForm::all();
        } else {
            $builderForms = BuilderForm::whereIn('organization_id', $orgIds)->get();
        }

        if ($request->org_id) {
            $builderForms = BuilderForm::where('organization_id', $request->org_id)->get();
        }

        $data = [
            'builderForms' => $builderForms,
            'title' => 'form builder',
            'builder_data' => $grouped_data,
            
        ];


        // The `$title` variable passed to view can be null safely
        return view('admin.settings.formbuilder.index', $data)->with('data', $data)->with('title', $title);
    }


    /**
     * show the form
     */
    public function showForm($id)
    {
      
        $form_id_toshow = $id;
        $the_form = BuilderForm::findOrFail($form_id_toshow);
        $this->authorizeOrganization($the_form->organization_id);

        $fields = $the_form->fields()
            ->withPivot('options')
            ->orderBy('builder_fields.sort_order', 'asc')
            ->get();

        $structured = $fields->map(function ($field) {
            $options = json_decode($field->pivot->options ?? '{}', true) ?? [];

            return [
                'id'            => $field->id,
                'label'         => $options['label'] ?? '',
                'description'   => $options['description'] ?? '',
                'section'       => $options['section'] ?? 'بدون قسم',
                'type'          => $options['type'] ?? null,
                'is_required'   => (bool)($options['validation']['required'] ?? 0),
                'options'       => form_field_options($options['values'] ?? null),
                'logic'         => $options['logic'] ?? [],
                'allowed_domains' => $options['allowed_domains'] ?? '',
                'min'           => $options['min'] ?? null,
                'max'           => $options['max'] ?? null,
                'label_low'     => $options['label_low'] ?? '',
                'label_high'    => $options['label_high'] ?? '',
            ];
        })
        ->groupBy('section')
        ->map(function ($questions, $section_title) {
            // نحاول نجيب description و logic من أول سؤال في القسم إن وجد
            $first = $questions->first();

            return [
                'title'       => $section_title,
                'description' => $first['description'] ?? '',
                'logic'       => is_array($first['logic']) ? ($first['logic']['section'] ?? 'continue') : ($first['logic'] ?? 'continue'),
                'questions'   => $questions->values()->map(function ($q) {
                    return [
                        'id'          => $q['id'],
                        'label'       => $q['label'],
                        'is_required' => $q['is_required'],
                        'type'        => $q['type'],
                        'options'     => $q['options'],
                        'logic'       => $q['logic'],
                        'allowed_domains' => $q['allowed_domains'] ?? '',
                        'min'         => $q['min'] ?? null,
                        'max'         => $q['max'] ?? null,
                        'label_low'   => $q['label_low'] ?? '',
                        'label_high'  => $q['label_high'] ?? '',
                    ];
                })->toArray(),
            ];
        })
        ->values()
        ->toArray();

        $data = [
            'id'              => $the_form->id,
            'title'           => $the_form->form_name,
            'organization_id' => $the_form->organization_id,
            'build_data'      => $structured,
        ];

       


        //     'title' => 'the form',
        //     'fields' => $the_form->fields()->orderBy('builder_fields.id', 'asc')->get(),
        //     'buolderform_id' => $form_id_toshow,
        //     'field_ids' => implode(",", $field_map_ids)
        // ];
        // dd($data);

        return view('admin.settings.formbuilder.form')->with('data', $data)->with('id',$id);
    }

    /**
     * handle form submit request
     */
    public function handleFormRequest(Request $request) {
        // must have data
        $validator = Validator::make($request->all(), [
            'builder_form_id' => 'required|integer|exists:builder_forms,id',
            'field_ids' => 'required|string',
        ]);

        abort_if($validator->fails(), 422, "Data error");

        // generating validation rules based on dynamic field config data
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $messages_map = [
            'en' => [
                'required'        => 'This field is required.',
                'email'           => 'Please enter a valid email address.',
                'url'             => 'Please enter a valid URL starting with http:// or https://.',
                'allowed_domains' => 'The URL must belong to one of these domains: :domains.',
                'numeric'   => 'Please enter numbers only.',
                'time'      => 'Please enter a valid time.',
                'file_size' => 'File size must not exceed 10 MB.',
                'file_type' => 'Supported file types are Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, and WEBP.',
            ],
            'ar' => [
                'required'        => 'هذا الحقل مطلوب.',
                'email'           => 'يرجى إدخال بريد إلكتروني صحيح.',
                'url'             => 'يرجى إدخال رابط صالح يبدأ بـ http:// أو https://.',
                'allowed_domains' => 'يجب أن ينتمي الرابط إلى أحد هذه النطاقات: :domains.',
                'numeric'   => 'يرجى إدخال أرقام فقط.',
                'time'      => 'يرجى إدخال وقت صحيح.',
                'file_size' => 'يجب ألا يتجاوز حجم الملف 10 ميجابايت.',
                'file_type' => 'أنواع الملفات المدعومة هي Word وExcel وPowerPoint وPDF وJPG وJPEG وPNG وWEBP.',
            ],
        ];
        $L = $messages_map[$locale];

        // File upload policy (shared with client-side in form.blade.php).
        $allowed_file_extensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'jpg', 'jpeg', 'png', 'webp'];
        $max_file_size_kb = 10 * 1024; // 10 MB

        $field_map_ids = explode(",", $request->input('field_ids'));
        $rules_array = [];
        $custom_messages = [];

        foreach ($field_map_ids as $id) {
            $field_options = BuilderField::findOrFail($id)->options;
            $field_name = 'field_' . $id;
            $type = $field_options->type ?? 'text';
            $required = intval($field_options->validation->required ?? 0) === 1;

            $rules = [];
            $rules[] = $required ? 'required' : 'nullable';

            switch ($type) {
                case 'email':
                    $rules[] = 'email';
                    $custom_messages[$field_name . '.email'] = $L['email'];
                    break;
                case 'url':
                    $rules[] = 'url';
                    $custom_messages[$field_name . '.url'] = $L['url'];
                    $allowedDomains = $this->parseAllowedDomains($field_options->allowed_domains ?? '');
                    if (!empty($allowedDomains)) {
                        $domainsLabel = implode(', ', $allowedDomains);
                        $rules[] = function ($attribute, $value, $fail) use ($allowedDomains, $domainsLabel, $L) {
                            if ($value !== null && $value !== '' && !$this->urlHostAllowed($value, $allowedDomains)) {
                                $fail(str_replace(':domains', $domainsLabel, $L['allowed_domains']));
                            }
                        };
                    }
                    break;
                case 'number':
                case 'linear_scale':
                    $rules[] = 'numeric';
                    $custom_messages[$field_name . '.numeric'] = $L['numeric'];
                    break;
                case 'time':
                    $rules[] = 'date_format:H:i';
                    $custom_messages[$field_name . '.date_format'] = $L['time'];
                    break;
                case 'date':
                    $rules[] = 'date';
                    $custom_messages[$field_name . '.date'] = $L['time'];
                    break;
                case 'file':
                    $rules[] = 'file';
                    $rules[] = 'max:' . $max_file_size_kb;
                    $rules[] = 'mimes:' . implode(',', $allowed_file_extensions);
                    $custom_messages[$field_name . '.file']  = $L['file_type'];
                    $custom_messages[$field_name . '.max']   = $L['file_size'];
                    $custom_messages[$field_name . '.mimes'] = $L['file_type'];
                    break;
            }

            // Closure rules (e.g. the allowed-domains check) can't be joined into
            // the pipe-delimited string, so pass the array form when any rule is
            // not a plain string; otherwise keep the original string format.
            $hasClosureRule = collect($rules)->contains(fn ($r) => !is_string($r));
            $rules_array[$field_name] = $hasClosureRule ? $rules : implode('|', $rules);
            $custom_messages[$field_name . '.required'] = $L['required'];
        }

        $dynamic_validator = Validator::make($request->all(), $rules_array, $custom_messages);

        if ($dynamic_validator->fails()) {
            return redirect()->back()
                ->withErrors($dynamic_validator)
                ->withInput();
        }

        // gather the form data as field_name => [ label, data ]
        $form_data = collect($field_map_ids)->mapWithKeys(function($id) use ($request){
            $field_options = BuilderField::findOrFail($id)->options;
            $field_name = 'field_'. $id;
            return [
                $field_name => [
                    $field_options->label, $request->input($field_name)
                ]
            ];
        });

        return redirect()->back()->with('BuilderForm work correctly.', 1);
    }

    /**
     * handle form data ajax request save
     */
    public function saveForm($id, Request $request)
    {
        $request->validate([
            'form_fields_data' => 'required|array',
            'form_fields_data.items' => 'required|array',
            'form_fields_data.title' => 'required|string',
            'form_fields_data.organization_id' => 'required|integer|exists:organizations,id',
        ]);

        $form_data = $request->form_fields_data;

        $actor = auth()->user();
        if (!$actor->hasRole('admin') && !$actor->ownsOrganization($form_data['organization_id'])) {
            abort(403);
        }

        // Backend validation: Do not allow saving empty sections
        if (isset($form_data['items']) && is_array($form_data['items'])) {
            foreach ($form_data['items'] as $item) {
                if (($item['type'] ?? null) === 'section') {
                    if (empty($item['questions']) || !is_array($item['questions'])) {
                        $message = app()->getLocale() === 'ar' 
                            ? 'لا يمكن حفظ قسم فارغ. يرجى إضافة أسئلة للقسم أو حذفه.' 
                            : 'An empty section cannot be saved. Please add questions to the section or delete it.';
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                        ], 422);
                    }
                }
            }
        }

        // Route parameter `{id}` is the authoritative form identifier.
        // "0" / missing means "create new"; any other value means "update that
        // specific form" (including renaming it). The old implementation
        // looked the form up by (form_name, organization_id) which broke
        // renames — a rename created a new form and orphaned the original,
        // and a name collision silently rewrote the wrong form.
        $is_edit = false;
        $form = null;

        if ($id && (int) $id > 0) {
            $form = BuilderForm::find($id);
            if ($form) {
                if (!$actor->hasRole('admin') && !$actor->ownsOrganization($form->organization_id)) {
                    abort(403);
                }
                $is_edit = true;
                $form->form_name = $form_data['title'];
                $form->organization_id = $form_data['organization_id'];
                $form->save();
            }
        }

        if (!$is_edit) {
            $form = new BuilderForm;
            $form->form_name = $form_data['title'];
            $form->organization_id = $form_data['organization_id'];
            $form->save();
        }

        $id = $form->id;
        
        $fields = Field::all()->mapWithKeys(function ($field) {
            return [$field->field_type => $field->id];
        });

        
        $section_index = 0;

        // GV-8346: choice options are stored as a comma-joined string. Trim,
        // drop blanks and de-duplicate before persisting — the builder's
        // `Add "Other"` button could be clicked repeatedly, and every extra
        // row was serialized as its own `__OTHER__` entry, so the founder-
        // facing form rendered one "Other" choice per click. De-duplicating
        // here also heals questions already saved with duplicates the next
        // time their form is saved.
        $normalizeOptions = function ($options) {
            return collect($options ?? [])
                ->map(fn($o) => trim((string) $o))
                ->filter(fn($o) => $o !== '')
                ->unique()
                ->implode(',');
        };

        $config = collect($form_data['items'])->flatMap(function ($item) use ($fields, &$section_index, $normalizeOptions) {
            
            if (($item['type'] ?? null) === 'question') {
                $section_title = null; 
                $questions = [$item]; 
                $description = null;
            }
            elseif (($item['type'] ?? null) === 'section') {
                $section_index++;
                $section_title = trim($item['title'] ?? '');
                if ($section_title === '') {
                    $section_title = 'Section ' . $section_index;
                }
                $description = $item['description'] ?? null;
                $questions = $item['questions'] ?? [];
            }
            else {
                return []; 
            }

            return collect($questions)->map(function ($question) use ($fields, $section_title, $description, $normalizeOptions) {
                $field_type = $question['question_type'] ?? 'short_answer';

                switch ($field_type) {
                    case 'short_answer':
                        $field_id = $fields['input'] ?? 0;
                        $additional_config = ['type' => 'short_answer'];
                        break;

                    case 'paragraph':
                        $field_id = $fields['textarea'] ?? 0;
                        $additional_config = ['type' => 'paragraph', 'rows' => 3];
                        break;

                    case 'multiple_choice':
                        $field_id = $fields['select'] ?? 0;
                        $additional_config = ['type' => 'multiple_choice', 'values' => $normalizeOptions($question['options'] ?? [])];
                        break;

                    case 'checkboxes':
                        $field_id = $fields['radio'] ?? 0;
                        $additional_config = ['type' => 'checkboxes','values' => $normalizeOptions($question['options'] ?? [])];
                        break;

                    case 'dropdown':
                        $field_id = $fields['select'] ?? 0;
                        $additional_config = ['type' => 'dropdown','values' => $normalizeOptions($question['options'] ?? [])];
                        break;

                    case 'linear_scale':
                        $field_id = $fields['input'] ?? 0;
                        // GV-8349: the builder JS nests these under `scale`
                        // (see extractQuestionData). Reading $question['min']
                        // never matched, so every scale silently persisted as
                        // the 1-5 default and the authored endpoint labels
                        // were dropped entirely.
                        $scaleConfig = $question['scale'] ?? [];
                        $min = $scaleConfig['from'] ?? 1;
                        $max = $scaleConfig['to'] ?? 5;
                        $min = is_numeric($min) ? (int) $min : 1;
                        $max = is_numeric($max) ? (int) $max : 5;
                        // An inverted or zero-width range would render no
                        // choices at all; fall back to the 1-5 default.
                        if ($max <= $min) {
                            $min = 1;
                            $max = 5;
                        }
                        $additional_config = [
                            'type' => 'linear_scale',
                            'min' => $min,
                            'max' => $max,
                            'step' => 1,
                            'label_low' => $scaleConfig['label_low'] ?? '',
                            'label_high' => $scaleConfig['label_high'] ?? '',
                        ];
                        break;

                    case 'date':
                        $field_id = $fields['input'] ?? 0;
                        $additional_config = ['type' => 'date'];
                        break;

                    case 'time':
                        $field_id = $fields['input'] ?? 0;
                        $additional_config = ['type' => 'time'];
                        break;

                    case 'number':
                        $field_id = $fields['number'] ?? $fields['input'] ?? 0;
                        $numberConfig = $question['number_config'] ?? [];
                        $additional_config = [
                            'type' => 'number',
                            'min'  => $numberConfig['min']  ?? '',
                            'max'  => $numberConfig['max']  ?? '',
                            'step' => $numberConfig['step'] ?? '',
                        ];
                        break;

                    case 'email':
                        $field_id = $fields['input'] ?? 0;
                        $emailConfig = $question['email_config'] ?? [];
                        $additional_config = [
                            'type'    => 'email',
                            'confirm' => !empty($emailConfig['confirm']),
                        ];
                        break;

                    case 'url':
                        $field_id = $fields['input'] ?? 0;
                        $urlConfig = $question['url_config'] ?? [];
                        $additional_config = [
                            'type'            => 'url',
                            'allowed_domains' => $urlConfig['allowed_domains'] ?? '',
                        ];
                        break;

                    case 'file':
                        $field_id = $fields['file'] ?? 0;
                        $additional_config = ['type' => 'file'];
                        break;

                    default:
                        $field_id = 0;
                        $additional_config = [];
                        break;
                }


                $common_data = [
                    'label' => $question['title'] ?? 'Untitled',
                    'section' => $section_title,
                    'description' => $description,
                    'validation' => [
                        'required' => !empty($question['required']) ? 1 : 0,
                        // Optional by default; only enforced when the builder
                        // explicitly marks the "Other" free-text as required.
                        'other_required' => !empty($question['other_required']) ? 1 : 0,
                    ],
                    'logic' => $question['logic'] ?? [],
                ];

                return [
                    'field' => $field_id,
                    'options' => array_merge($common_data, $additional_config)
                ];
            });
        });

        $save_success = true;
        $error_message = null;

        try {
            DB::transaction(function () use ($config, $id, $actor) {
                $builderForm = BuilderForm::findOrFail($id);
                if (!$actor->hasRole('admin') && !$actor->ownsOrganization($builderForm->organization_id)) {
                    abort(403);
                }
                $builderForm->fields()->detach();

                $config->values()->each(function ($item, $index) use ($builderForm) {
                    $builderForm->fields()->attach($item['field'], [
                        'options' => json_encode($item['options']),
                        'sort_order' => $index + 1
                    ]);
                });
            });
        } catch (\Throwable $e) {
            $save_success = false;
            $error_message = $e->getMessage();
            report($e);
        }

        return response()->json([
            'success'         => $save_success,
            'id'              => $id,
            'organization_id' => $form_data['organization_id'],
            'action'          => $is_edit ? 'updated' : 'created',
            'message'         => $error_message,
        ]);

    }

    public function modifyForm($set, Request $request)
    {
        $actor = auth()->user();

        // This route has no name, so the route-permission middleware never sees it.
        abort_unless($actor->ableToAny(['forms.design_save', 'forms.builder_view']), 403);

        if ($set==1) {
            // make a copy
            $source = BuilderForm::findOrFail($request->id);
            if (!$actor->hasRole('admin') && !$actor->ownsOrganization($source->organization_id)) {
                abort(403);
            }
            if (!$actor->hasRole('admin') && !$actor->ownsOrganization($request->organization_id)) {
                abort(403);
            }
            $form = new BuilderForm;
            $form->form_name = $request->title;
            $form->organization_id = $request->organization_id;
            $form->save();
            $new = BuilderField::where('builder_form_id',$request->id)->orderBy('id')->get();
            if (count($new)>0) {
                foreach ($new as $n){
                    $save = BuilderField::findOrFail($n->id)->replicate();
                    $save->builder_form_id = $form->id;
                    $save->save();
                                    }
                                }
            return response()->json($form->id);
        } elseif ($set==2) {
            // create new
            if (!$actor->hasRole('admin') && !$actor->ownsOrganization($request->organization_id)) {
                abort(403);
            }
            $new = new BuilderForm;
            $new->form_name = $request->title;
            $new->organization_id = $request->organization_id;
            $new->save();
            return response()->json($new->id);
        } elseif ($set==3) {
            // delete
            $form = BuilderForm::findOrFail($request->id);
            if (!$actor->hasRole('admin') && !$actor->ownsOrganization($form->organization_id)) {
                abort(403);
            }
            $form->fields()->detach();
            BuilderForm::destroy($request->id);
            return response()->json();
        } else {
            //rename
            $form = BuilderForm::findOrFail($request->id);
            if (!$actor->hasRole('admin') && !$actor->ownsOrganization($form->organization_id)) {
                abort(403);
            }
            // The rename also writes organization_id: the destination must be owned too.
            if (!$actor->hasRole('admin') && !$actor->ownsOrganization($request->organization_id)) {
                abort(403);
            }
            BuilderForm::where('id',$request->id)->update(['form_name'=>$request->title,'organization_id'=>$request->organization_id]);
            return response()->json($request->id);
        }

    }

    public function builderByOrganization($organization_id=0){
        // No organization selected yet -> nothing to list (the page calls this before a choice is made).
        if (!is_numeric($organization_id) || (int) $organization_id <= 0) {
            return response()->json(['data' => [], 'appear' => '']);
        }
        // This route has no name, so the route-permission middleware never sees it.
        abort_unless(auth()->user()->ableToAny(['forms.builder_view', 'forms.design_save']), 403);
        $this->authorizeOrganization($organization_id);
        $builders = BuilderForm::where('organization_id',$organization_id)->get();
        $output = [];
        $appear = '';
        foreach( $builders as $builder )
        {
            $output[] = [
                'id' => $builder->id,
                'form_name' => $builder->form_name
            ];
            $appear .= "<option value=".$builder->id." >".$builder->form_name."</option>";
        }
        // return $appear;
        return response()->json([
            'data' => $output,
            'appear' => $appear
        ]);
    }

    /**
     * Delete a form with validation
     */
    public function deleteForm($id)
    {
        try {
            $form = BuilderForm::findOrFail($id);

            $actor = auth()->user();
            if (!$actor->hasRole('admin') && !$actor->ownsOrganization($form->organization_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'forbidden',
                    'message_en' => 'You are not allowed to delete this form.',
                    'message_ar' => 'غير مسموح لك بحذف هذا النموذج'
                ], 403);
            }

            // Check if form is used in any program steps
            $usedInSteps = $form->steps()->count();
            if ($usedInSteps > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'form_used_in_steps',
                    'message_en' => 'This form cannot be deleted because it is used in program steps.',
                    'message_ar' => 'لا يمكن حذف هذا النموذج لأنه مستخدم في خطوات البرنامج'
                ], 422);
            }
            
            $builderFieldIds = BuilderField::where('builder_form_id', $id)->pluck('id');
            $hasSubmissions = false;
            
            if ($builderFieldIds->isNotEmpty()) {
                $hasSubmissions = DB::table('form_data')
                    ->whereIn('field_id', $builderFieldIds)
                    ->exists();
            }
                
            if ($hasSubmissions) {
                return response()->json([
                    'success' => false,
                    'message' => 'form_has_submissions',
                    'message_en' => 'You cannot delete this form because it has existing submissions.',
                    'message_ar' => 'لا يمكنك حذف هذا النموذج لأنه يحتوي على تقديمات'
                ], 422);
            }
            
            // Delete form fields relationship
            $form->fields()->detach();
            
            // Delete builder fields
            BuilderField::where('builder_form_id', $id)->delete();
            
            // Delete the form
            $form->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'form_deleted_successfully',
                'message_en' => 'Form deleted successfully.',
                'message_ar' => 'تم حذف النموذج بنجاح'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'error_deleting_form',
                'message_en' => 'An error occurred while deleting the form.',
                'message_ar' => 'حدث خطأ أثناء حذف النموذج',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder sections for a form.
     *
     * Payload (POST JSON):
     *   {
     *     "sections": [
     *       { "title": "Section Name", "question_ids": [1, 5, 2] },
     *       ...
     *     ]
     *   }
     *
     * Sections are purely logical groupings stored as the `section` key in
     * each builder_field's options JSON. To persist a new section order, we
     * process each section sequentially, assigning contiguous sort_orders
     * and updating the `options.section` string value for all its questions.
     */
    public function reorderSections($id, Request $request)
    {
        $request->validate([
            'sections'                => 'required|array',
            'sections.*.title'        => 'nullable|string',
            'sections.*.question_ids' => 'required|array',
        ]);

        $this->authorizeOrganization(BuilderForm::findOrFail($id)->organization_id);

        $sectionsPayload = $request->input('sections');

        try {
            DB::transaction(function () use ($id, $sectionsPayload) {
                $sortOrder = 1;
                foreach ($sectionsPayload as $sec) {
                    $title = $sec['title'] ?? '';
                    $qids  = array_values(array_filter($sec['question_ids'], fn($v) => is_numeric($v)));
                    
                    foreach ($qids as $qid) {
                        $field = BuilderField::where('id', $qid)
                            ->where('builder_form_id', $id)
                            ->first();
                            
                        if ($field) {
                            $options = json_decode($field->getRawOriginal('options') ?? '{}', true) ?? [];
                            $options['section'] = $title;
                            
                            $field->fill([
                                'options'    => json_encode($options),
                                'sort_order' => $sortOrder++
                            ])->save();
                        }
                    }
                }
            });

            return response()->json(['status' => 'success']);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reorder questions within a section (and optionally move between sections).
     *
     * Payload (POST JSON):
     *   {
     *     "section_title": "Section Title" | null,
     *     "question_ids":  [3, 1, 7, ...]        ← builder_fields primary keys
     *   }
     *
     * When section_title is provided and differs from a question's current
     * options.section, the question's options JSON is updated so it belongs
     * to the new section (cross-section drag-and-drop support).
     */
    public function reorderQuestions($id, Request $request)
    {
        $request->validate([
            'question_ids'   => 'required|array',
            'question_ids.*' => 'nullable',
            'section_title'  => 'nullable|string',
        ]);

        $this->authorizeOrganization(BuilderForm::findOrFail($id)->organization_id);

        $questionIds  = array_values(array_filter(
            $request->input('question_ids'),
            fn($v) => is_numeric($v)
        ));
        $sectionTitle = $request->input('section_title');

        if (empty($questionIds)) {
            return response()->json(['status' => 'success', 'message' => 'no db ids']);
        }

        try {
            DB::transaction(function () use ($id, $questionIds, $sectionTitle) {
                // Find the minimum sort_order slot occupied by these questions
                // so we don't disturb the sort_order of other sections.
                $minOrder = BuilderField::where('builder_form_id', $id)
                    ->whereIn('id', $questionIds)
                    ->min('sort_order') ?? 1;

                $sortOrder = $minOrder;
                foreach ($questionIds as $qid) {
                    $field = BuilderField::where('id', $qid)
                        ->where('builder_form_id', $id)
                        ->first();

                    if (!$field) {
                        $sortOrder++;
                        continue;
                    }

                    // Decode options to potentially update the section name.
                    $options = json_decode($field->getRawOriginal('options') ?? '{}', true) ?? [];

                    if ($sectionTitle !== null && ($options['section'] ?? null) !== $sectionTitle) {
                        $options['section'] = $sectionTitle;
                        $field->fill(['options' => json_encode($options)]);
                    }

                    $field->fill(['sort_order' => $sortOrder++])->save();
                }
            });

            return response()->json(['status' => 'success']);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
