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

    /**
     * show the builder
     */
    public function showBuilder(Request $request, $id = null)
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        
        // Get organization_id from request or user's organization
        $organization_id = $request->org_id ?? ($user->organization_id ?? null);

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

            if (!$title) {
                $title = BuilderForm::when(!is_null($organization_id), function ($q) use ($organization_id) {
                    return $q->where('organization_id', $organization_id);
                })->first();

                if (!$title) {
                    $title = BuilderForm::firstOrCreate([
                        'form_name' => 'New Empty Form',
                        'organization_id' => $organization_id ?? Organization::first()->id
                    ]);
                }

                $id = $title->id;
            }
        }

        $fields = Field::all()->mapWithKeys(fn($field) => [$field->field_type => $field->id]);

        $builder_fields = BuilderField::where('builder_form_id', $id)->orderBy('id', 'asc');

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
        if (!$user->hasRole('admin') && !empty($orgIds)) {
            $builderForms = BuilderForm::whereIn('organization_id', $orgIds)->get();
        } else {
            $builderForms = BuilderForm::all();
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

        $fields = $the_form->fields()
            ->withPivot('options')
            ->orderBy('builder_fields.id', 'asc')
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
                'options'       => isset($options['values'])
                    ? (is_array($options['values']) ? $options['values'] : explode(',', $options['values']))
                    : [],
                'logic'         => $options['logic'] ?? [],
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
        $field_map_ids = explode(",", $request->input('field_ids'));
        $field_required_rules = collect($field_map_ids)->mapWithKeys(function($id){
        $field_options = BuilderField::findOrFail($id)->options;
            if($field_options->validation->required == 1) {
                $rules = ['required'];
                if(isset($field_options->type) && $field_options->type == "email") $rules[] = 'email';

                return [
                    'field_'. $id => implode("|", $rules)
                ];
            }
            else {
                return ['field_'. $id => 'nullable'];
            }
        });

        // validation based on dynamic data
        $dynamic_validator = Validator::make($request->all(), $field_required_rules->toArray(), [
            'required' => "field can't be left blank",
            'email' => 'field must be a valid email address'
        ]);

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
        //  dd($request->all());

        $request->validate([
            'form_fields_data' => 'required|array',
            'form_fields_data.items' => 'required|array',
        ]);

        $form_data = $request->form_fields_data;

        
        $form = BuilderForm::where('form_name', $form_data['title'])->where('organization_id', $form_data['organization_id'])->first();
       
        if ($form) {
            $id = $form->id;
        }else{
        $form = new BuilderForm;
            $form->form_name = $form_data['title'];
            $form->organization_id = $form_data['organization_id'];
            $form->save();
            $id = $form->id;
        }
        
        $fields = Field::all()->mapWithKeys(function ($field) {
            return [$field->field_type => $field->id];
        });

        
        $config = collect($form_data['items'])->flatMap(function ($item) use ($fields) {
            
            if (($item['type'] ?? null) === 'question') {
               
                $section_title = null; 
                $questions = [$item]; 
            }
            elseif (($item['type'] ?? null) === 'section') {
                $section_title = $item['title'] ?? null;
                $description = $item['description'] ?? null;
                $questions = $item['questions'] ?? [];
            }
            else {
                return []; 
            }

            return collect($questions)->map(function ($question) use ($fields, $section_title, $description) {
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
                        $values = collect($question['options'] ?? [])->map(fn($o) => trim($o))->implode(',');
                        $additional_config = ['type' => 'multiple_choice', 'values' => $values];
                        break;

                    case 'checkboxes':
                        $field_id = $fields['radio'] ?? 0;
                        $values = collect($question['options'] ?? [])->map(fn($o) => trim($o))->implode(',');
                        $additional_config = ['type' => 'checkboxes','values' => $values];
                        break;

                    case 'dropdown':
                        $field_id = $fields['select'] ?? 0;
                        $values = collect($question['options'] ?? [])->map(fn($o) => trim($o))->implode(',');
                        $additional_config = ['type' => 'dropdown','values' => $values];
                        break;

                    case 'linear_scale':
                        $field_id = $fields['input'] ?? 0;
                        $min = $question['min'] ?? 1;
                        $max = $question['max'] ?? 5;
                        $additional_config = [
                            'type' => 'linear_scale',
                            'min' => $min,
                            'max' => $max,
                            'step' => 1
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
                        'required' => !empty($question['required']) ? 1 : 0
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

        try {
            DB::transaction(function () use ($config, $id) {
                $builderForm = BuilderForm::findOrFail($id);
                $builderForm->fields()->detach();

                $config->each(function ($item) use ($builderForm) {
                    $builderForm->fields()->attach($item['field'], [
                        'options' => json_encode($item['options'])
                    ]);
                });
            });
        } catch (Exception $e) {
            $save_success = false;
        }
        
        return response()->json([
            'success' => $save_success,
            'id' => $id,
            'organization_id' => $form_data['organization_id'],
        ]);

    }

    public function modifyForm($set, Request $request)
    {

        if ($set==1) {
            // make a copy
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
            $new = new BuilderForm;
            $new->form_name = $request->title;
            $new->organization_id = $request->organization_id;
            $new->save();
            return response()->json($new->id);
        } elseif ($set==3) {
            // delete
            BuilderForm::findOrFail($request->id)->fields()->detach();
            BuilderForm::destroy($request->id);
            return response()->json();
        } else {
            //rename
            BuilderForm::where('id',$request->id)->update(['form_name'=>$request->title,'organization_id'=>$request->organization_id]);
            return response()->json($request->id);
        }

    }

    public function builderByOrganization($organization_id=0){
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

    public function reorderSections(Request $request){
        return response()->json(['status' => 'success']);
    }

    public function reorderQuestions(Request $request){
        return response()->json(['status' => 'success']);
    }
}
