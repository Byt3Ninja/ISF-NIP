@extends('layouts.admin')

@section('title', __('cycles.Cycle') )
@section('subTitle', __('cycles.join Cycle'))

@section('content')
    <!-- BEGIN: Content-->
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="content-body">
                <section id="basic-input">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom pb-3 mb-6">
                                    <h4 class="card-title">{{$step->title}}</h4>
                                    <p class="card-text">{{$step->description}}</p>
                                </div>
                                <div class="card-body">
                                    @if(session()->has('msg'))
                                        <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show" role="alert">
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-{{Session::get('class')=='success'?'check-circle':'error'}} me-2"></i>
                                                <span>{{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if(count($fields) > 0)
                                        <form method="post" action="/dashboard/cycles/save" enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                            <input type="hidden" name="cycle" value="{{$c}}" />
                                            <input type="hidden" name="id" value="{{$step->id}}" />
                                            <input type="hidden" name="field_ids" value="{{ $field_ids }}" />
                                            
                                            @foreach($fields as $field)
                                                @php
                                                    $options = $field->pivot->options? json_decode($field->pivot->options) : null;
                                                    $field_name = 'field_' . $field->pivot->id;
                                                    $id_for = 'input-fld-'. $loop->iteration;
                                                    $get_answer = $answers->where('field_id', $field->pivot->id)->first();
                                                    $answer = $get_answer ? $get_answer->answer : null;
                                                @endphp
                                                
                                                <div class="mb-3">
                                                    @switch($field->field_type)
                                                        @case("select")
                                                        <div class="form-group mb-6">
                                                            @if($options->label)
                                                                <label for="{{ $id_for }}" class="form-label fw-bold">{{ $options->label }}</label>
                                                            @endif
                                                            <select class="form-select @error($field_name) is-invalid @enderror" 
                                                                    id="{{ $id_for }}" 
                                                                    name='{{ $field_name }}' 
                                                                    required>
                                                                <option value="">{{$l=='ar'?'اختر':'Choose'}}...</option>
                                                                @foreach(explode(",", $options->values) as $value)
                                                                    <option value="{{ trim($value) }}" 
                                                                            {{ old($field_name) == trim($value)? "selected" : "" }} 
                                                                            {{trim($value)==$answer?'selected':''}}>
                                                                        {{ trim($value) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error($field_name)
                                                                <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                                                            @enderror
                                                        </div>
                                                        @break
                                                        
                                                        @case("textarea")
                                                        <div class="form-group mb-6">
                                                            @if($options->label)
                                                                <label for="{{ $id_for }}" class="form-label fw-bold">{{ $options->label }}</label>
                                                            @endif
                                                            <textarea class="form-control @error($field_name) is-invalid @enderror" 
                                                                      id="{{ $id_for }}" 
                                                                      name="{{ $field_name }}"
                                                                      rows="{{ $options->rows ?? 4 }}">{{ old($field_name) }}{{$answer}}</textarea>
                                                            @error($field_name)
                                                                <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                                                            @enderror
                                                        </div>
                                                        @break
                                                        
                                                        @case("file")
                                                        <div class="form-group mb-6">
                                                            @if($options->label)
                                                                <label class="form-label fw-bold">{{ $options->label }}</label>
                                                            @endif
                                                            <div class="d-flex flex-column">
                                                                <input type="file" 
                                                                       class="form-control @error($field_name) is-invalid @enderror" 
                                                                       id="{{ $id_for }}" 
                                                                       name="{{ $field_name }}"
                                                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                                                @if($answer)
                                                                    <div class="mt-4">
                                                                        <a href="/{{ $answer }} " target="_blank" class="btn btn-sm btn-outline-primary">
                                                                            <i class="ti ti-file me-2"></i> {{ __('admin.View File') }}
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            @error($field_name)
                                                                <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                                                            @enderror
                                                        </div>
                                                        @break
                                                        
                                                        @default
                                                            <div class="form-group mb-6">
                                                                @if($options->label)
                                                                    <label for="{{$id_for}}" class="form-label fw-bold">{{ $options->label }}</label>
                                                                @endif
                                                                <input type="{{$options->type=="date"?"text":$options->type}}" 
                                                                       class="form-control {{ $options->type == "date"? "datepicker" : "" }} @error($field_name) is-invalid @enderror" 
                                                                       id="{{$id_for}}" 
                                                                       name="{{ $field_name }}" 
                                                                       value="{{ !empty( $answer ) ? $answer : ( !empty( old($field_name) ) ? old($field_name) : '' ) }}" />
                                                                @error($field_name)
                                                                    <div class="invalid-feedback">{{ $errors->first($field_name) }}</div>
                                                                @enderror
                                                            </div>
                                                    @endswitch
                                                </div>
                                            @endforeach
                                            
                                            <div class="d-flex justify-content-end mt-4">
                                                <button type="submit" class="btn btn-primary">
                                                    {{ __('admin.submit') }}
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="text-center">
                                            <h5 class="text-muted">{{ __('cycles.No Fields') }}</h5>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@stop

@section('pagescripts')
    {{-- <script src="/js/jquery.datetimepicker.full.min.js"></script> --}}
    <script>
        // console.log('step', {!! json_encode($step) !!});
        
        // Initialize date pickers
        $('.datepicker').flatpickr({
            dateFormat: 'Y-m-d'
        });
    </script>
@stop
