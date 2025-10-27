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
    public function showBuilder(Request $request, $id=1)
    {
        $organization_id=auth()->user()->organization_id;

        if (is_null($organization_id) && Organization::count() == 0){
            return redirect(route('organizations.index'))->with('message2','add organizations first');
        }
        $title = BuilderForm::where('id',$id)->when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->where('organization_id',$organization_id);
        })->first();
        if (!$title){
            $title = BuilderForm::when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->where('organization_id',$organization_id);
            })->first();
            if (!$title){
                if (!is_null($organization_id)){
                    $title = BuilderForm::firstOrCreate([
                        'form_name'=>'New Empty Form',
                        'organization_id'=>$organization_id
                    ]);
                }else{
                    $title = BuilderForm::firstOrCreate([
                        'form_name'=>'New Empty Form',
                        'organization_id'=>Organization::first()->id
                    ]);
                }
            }
            $id = $title->id;
        }
        $fields = Field::all()->mapWithKeys(function ($field) {
            return [$field->field_type => $field->id];
        });



        $builder_fields = BuilderField::where([
            'builder_form_id'=>$id,
        ])->orderBy('id', 'asc');

        if($builder_fields->exists()) {

            $field_data = $builder_fields->get()->map(function($field) use ($fields) {

                switch ($fields->search($field->field_id)) {
                    case 'select':
                        $field_config_data = [
                            'type'=> 'select',
                            'additionalConfig'=> [
                                'listOptions' => $field->options->values
                            ]
                        ];

                        break;

                    case 'textarea':
                        $field_config_data = [
                            'type'=> 'textarea',
                            'additionalConfig'=> [
                                'textAreaRows' => $field->options->rows
                            ]
                        ];

                        break;
                    case 'file':
                        $field_config_data = [
                            'type'=> 'file',
                            'additionalConfig'=> [
                                'inputType' => $field->options->type
                            ]
                        ];

                        break;

                    default:
                        if($field->options->type != "date") {
                            $field_config_data = [
                                'type'=> 'input',
                                'additionalConfig'=> [
                                    'inputType' => $field->options->type
                                ]
                            ];
                        }
                        else {
                            $field_config_data = [
                                'type'=> 'date',
                                'additionalConfig'=> []
                            ];
                        }

                }

                $common_data = [
                    'id' => $field->id,
                    'label'=> $field->options->label,
                    'isRequired'=> intval( $field?->options?->validation?->required ) == 1 ? true : false,
                ];

                return [array_merge($common_data, $field_config_data)];
            });
        }

        if (!is_null(auth()->user()->organization_id)){
            $builderForms = BuilderForm::where('organization_id',auth()->user()->organization_id)->get();
        }else{
            $builderForms = BuilderForm::all();
        }

        if($request->org_id){
            $builderForms = BuilderForm::where('organization_id',$request->org_id)->get();
        }

        $data = [
            'builderForms'=>$builderForms,
            'title' => 'form builder',
            'builder_data' => $builder_fields->exists()? $field_data->flatten(1)->toArray() : []
        ];

        return view('admin.settings.formbuilder.index', $data)->with('title',$title);
    }

    /**
     * show the form
     */
    public function showForm($id)
    {
        // $form_id_toshow = $id;
        // $the_form = BuilderForm::findOrFail($form_id_toshow);

        // $fields = $the_form->fields()
        //     ->withPivot('options')
        //     ->orderBy('builder_fields.id', 'asc')
        //     ->get();

        // $grouped_fields = $fields->map(function ($field) {
        //     // تأكد أن options دايمًا array
        //     $options = json_decode($field->pivot->options ?? '{}', true) ?? [];

        //     // ضمان وجود المفاتيح حتى لو ناقصة
        //     return [
        //         'field_id'   => $field->id,
        //         'label'      => $options['label'] ?? '',
        //         'section'    => $options['section'] ?? 'بدون قسم',
        //         'type'       => $options['type'] ?? null,
        //         'values'     => $options['values'] ?? null,
        //         'required'   => $options['validation']['required'] ?? 0,
        //         'logic'      => $options['logic'] ?? [],
        //         'raw_field'  => $field,
        //     ];
        // })
        // // هنا مفيش خوف من null
        // ->groupBy(fn($item) => $item['section']);

        // $data = [
        //     'title'          => 'the form',
        //     'sections'       => $grouped_fields,
        //     'builderform_id' => $form_id_toshow,
        //     'field_ids'      => $fields->pluck('id')->implode(','),
        // ];
        $form_id_toshow = $id;
        $the_form = BuilderForm::findOrFail($form_id_toshow);
        $field_map_ids = BuilderField::where('builder_form_id', $form_id_toshow)->select('id')->pluck('id')->toArray();

        $data = [
            'title' => 'the form',
            'fields' => $the_form->fields()->orderBy('builder_fields.id', 'asc')->get(),
            'buolderform_id' => $form_id_toshow,
            'field_ids' => implode(",", $field_map_ids)
        ];
        

        return view('admin.settings.formbuilder.form', $data)->with('id',$id);
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
         // dd($request->all());

        $request->validate([
            'form_fields_data' => 'required|array',
            'form_fields_data.items' => 'required|array',
        ]);

        $form_data = $request->form_fields_data;

       
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
                $questions = $item['questions'] ?? [];
            }
            else {
                return []; 
            }

            return collect($questions)->map(function ($question) use ($fields, $section_title) {
                $field_type = $question['question_type'] ?? 'short_answer';

                switch ($field_type) {
                    case 'short_answer':
                        $field_id = $fields['input'] ?? 0;
                        $additional_config = ['type' => 'text'];
                        break;

                    case 'paragraph':
                        $field_id = $fields['textarea'] ?? 0;
                        $additional_config = ['rows' => 3];
                        break;

                    case 'dropdown':
                        $field_id = $fields['select'] ?? 0;
                        $values = collect($question['options'] ?? [])->map(fn($o) => trim($o))->implode(',');
                        $additional_config = ['values' => $values];
                        break;

                    case 'date':
                        $field_id = $fields['input'] ?? 0;
                        $additional_config = ['type' => 'date'];
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
                    'section' => $section_title, // استخدم عنوان القسم هنا
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
}
