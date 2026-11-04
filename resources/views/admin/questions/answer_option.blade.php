@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      <div class="content-header mb-4">
        <h4 class="card-title">{{ __('admin.all answerOptions') }}</h4>
      </div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div 
              class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" 
              role="alert"
            >
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        </section>

        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header">
                <button 
                  class="btn btn-primary" 
                  data-bs-toggle="modal" data-bs-target="#CreateCategory">
                  {{ __('admin.Add new answerOption') }}
                  <i class="ti ti-plus mx-2"></i>
                </button>
              </div>
              <div class="card-body">
                <div class="card-text">
                  <div class="table-responsive">
                    <table class="table mb-0" id="userstable">
                      <thead class="thead-dark">
                        <tr>
                          <th>#</th>
                          <th>{{ __('admin.answerOption') }}</th>
                          <th>{{ __('admin.Question') }}</th>
                          <th>{{ __('admin.Score') }}</th>
                          <th>{{ __('cycles.Created at') }}</th>
                          <th>{{ __('cycles.Edit/Delete') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php
                        $c=1;
                        @endphp
                        @foreach ($answerOptions as $answerOption)
                        <tr>
                          <td>{{$c++}}</td>
                          <td>{{$answerOption->{'option_text_' . $l} }}</td>
                          <td>{{$answerOption->question->{'text_' . $l} }}</td>
                          <td>{{$answerOption->score }}</td>
                          <td>{{$answerOption->created_at}}</td>
                          <td>
                            <div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ShowanswerOption{{ $answerOption->id }}" >{{__('admin.Show')}}</div>
                            <div class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#EditanswerOption{{ $answerOption->id }}">{{__('admin.Edit')}}</div>
                            <form action="{{ route('answerOption.destroy', $answerOption->id) }}" method="POST" style="display: inline-block;">
                              @csrf
                              @method('DELETE')
                              <button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this ?')">{{__('admin.Delete')}}</button>
                          </form>

                          </td>
                        </tr>
                        <!-- Edit Customer Modal -->
                        <div class="modal fade" id="EditanswerOption{{ $answerOption->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                              <div class="modal-content">
                                  <div class="modal-body">
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      <div class="text-center mb-6">
                                          <h4 class="mb-2">{{__('admin.Edit')}} ({{$answerOption->{'option_text_'.$l }   }})</h4>
                                      </div>

                                      <form class="add-new-user pt-0" action="{{ route('answerOption.update', $answerOption->id) }}" method="POST">
                                          @csrf
                                          @method('PUT')
                                        <input type="hidden" value="{{ $answerOption->id }}" name="answerOption_id" >


                                        <div class="ecommerce-customer-add-basic mb-4" >

                                            <div class="col-md-12">
                      
                                              <div class="mb-6 width49">
                                                <div>
                                                    <label for="option_text_ar" class="form-label" >{{ __('admin.answerOption') }} ( {{ __('admin.Arabic') }} )</label>
                                                </div>
                                                <input
                                                    id="option_text_ar"
                                                    class="form-control @error('option_text_ar') is-invalid @enderror"
                                                    type="text"
                                                    name="option_text_ar"
                                                    autocomplete="off"
                                                    value="{{$answerOption->option_text_ar}}"
                                                />
                                            </div>
                      
                                            <div class="mb-6 width49">
                                              <div>
                                                  <label for="option_text_en" class="form-label" >{{ __('admin.answerOption') }} ( {{ __('admin.English') }} )</label>
                                              </div>
                                              <input
                                                  id="option_text_en"
                                                  class="form-control @error('option_text_en') is-invalid @enderror"
                                                  type="text"
                                                  name="option_text_en"
                                                  autocomplete="off"
                                                    value="{{$answerOption->option_text_en}}"
                                              />
                                          </div>
                      
                      
                                          <div class="mb-6 width49">
                                              <div>
                                                  <label for="option_info_ar" class="form-label" >{{__('admin.answerOption_Info')}} ( {{ __('admin.Arabic') }} )</label>
                                              </div>
                                              <textarea id="option_info_ar"
                                              class="form-control @error('option_info_ar') is-invalid @enderror"
                                              type="text"
                                              name="option_info_ar"
                                              autocomplete="off">{{$answerOption->option_info_ar}}</textarea>
                                              
                                          </div>
                        
                                          <div class="mb-6 width49">
                                            <div>
                                                <label for="option_info_en" class="form-label" >{{__('admin.answerOption_Info')}} ( {{ __('admin.English') }} )</label>
                                            </div>
                                            <textarea id="option_info_en"
                                            class="form-control @error('option_info_en') is-invalid @enderror"
                                            type="text"
                                            name="option_info_en"
                                            autocomplete="off">{{$answerOption->option_info_en}}</textarea>
                                            
                                          </div>
                      
                                          <div class="mb-6 width49">
                                              <div>
                                                  <label for="score" class="form-label" >{{ __('admin.Score') }} </label>
                                              </div>
                                              <input
                                                  id="score"
                                                  class="form-control @error('score') is-invalid @enderror"
                                                  type="number"
                                                  name="score"
                                                  autocomplete="off"
                                                  value="{{$answerOption->score}}"
                                              />
                                          </div>
                      
                                          <div class="mb-6 width49">
                                              <label class="form-label fs-6">
                                                {{ __('admin.Question') }}
                                                
                                              </label>
                                              <select class="category_id form-select" name="question_id">
                                                <option value="" disabled selected>
                                                  {{ $l == 'en' ? 'Choose Question' : 'اختر السؤال' }}
                                                </option>
                                                @foreach ($questions as $question)
                                                  <option value="{{ $question->id }}"  {{ $question->id == $answerOption->question_id ? 'selected' : '' }}  >{{ $question->{'text_'. $l} }}</option>
                                                @endforeach
                                              </select>
                                            </div>
                      
                                         
                      
                                            </div>
                                        </div>
                                        

                                          <div class="text-center">
                                              <button type="submit" class="btn btn-primary me-3 data-submit">{{__('admin.submit')}}</button>
                                              <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{__('admin.Cancel')}}</button>
                                          </div>
                                      </form>



                                  </div>
                              </div>
                          </div>
                      </div>
                      <!--/ Edit JO Modal -->
                      <!-- Show Customer Modal -->
                    <div class="modal fade" id="ShowanswerOption{{ $answerOption->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                      <div class="modal-content" dir="ltr">
                          <div class="modal-body">
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              <div class="text-center mb-6">
                                  <h4 class="mb-2">{{__('admin.Show')}} ( {{ $answerOption->{'option_text_' . $l} }} )</h4>
                              </div>


                              <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                  <div class="d-flex gap-4 align-items-center">
                                      <h6 class="m-0">{{ $answerOption->{'option_text_ar'} }} <br> {{ $answerOption->{'option_text_en'} }}</h6>
                                  </div>
                                  <p class="m-0 d-none d-sm-block">{{ __('admin.answerOption') }}</p>
                              </div>


                              <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                  <div class="d-flex gap-4 align-items-center">
                                      <h6 class="m-0">{{$answerOption->{'option_info_ar'} }}<br>{{ $answerOption->{'option_info_en'} }}</h6>
                                  </div>
                                  <p class="m-0 d-none d-sm-block">{{__('admin.answerOption_Info')}}</p>
                              </div>


                              <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                <div class="d-flex gap-4 align-items-center">
                                    <h6 class="m-0">{{$answerOption->question->{'text_'. $l} }}</h6>
                                </div>
                                <p class="m-0 d-none d-sm-block">{{ __('admin.Question') }}</p>
                            </div>

                              <div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
                                  <div class="d-flex gap-4 align-items-center">
                                      <h6 class="m-0">{{ $answerOption->created_at}}</h6>
                                  </div>
                                  <p class="m-0 d-none d-sm-block">{{__('admin.Created At')}}</p>
                              </div>
                              

                          </div>
                      </div>
                      </div>
                    </div>
                      <!--/ Show Customer Modal -->
                        @endforeach

                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </section>

 <!-- Create JobOrder Modal -->
 <div class="modal fade" id="CreateCategory" tabindex="-1" aria-hidden="true" dir="rtl">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
      <div class="modal-content">
          <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-6">
                  <h4 class="mb-2">{{__('admin.answerOption')}}</h4>
              </div>

              <form method="POST" action="{{route('answerOption.store')}}" class="pt-0" >
                  @csrf

                  <div class="ecommerce-customer-add-basic mb-4" >

                      <div class="col-md-12">

                        <div class="mb-6 width49">
                          <div>
                              <label for="option_text_ar" class="form-label" >{{ __('admin.answerOption') }} ( {{ __('admin.Arabic') }} )</label>
                          </div>
                          <input
                              id="option_text_ar"
                              class="form-control @error('option_text_ar') is-invalid @enderror"
                              type="text"
                              name="option_text_ar"
                              autocomplete="off"
                          />
                      </div>

                      <div class="mb-6 width49">
                        <div>
                            <label for="option_text_en" class="form-label" >{{ __('admin.answerOption') }} ( {{ __('admin.English') }} )</label>
                        </div>
                        <input
                            id="option_text_en"
                            class="form-control @error('option_text_en') is-invalid @enderror"
                            type="text"
                            name="option_text_en"
                            autocomplete="off"
                        />
                    </div>


                    <div class="mb-6 width49">
                        <div>
                            <label for="option_info_ar" class="form-label" >{{__('admin.answerOption_Info')}} ( {{ __('admin.Arabic') }} )</label>
                        </div>
                        <textarea id="option_info_ar"
                        class="form-control @error('option_info_ar') is-invalid @enderror"
                        type="text"
                        name="option_info_ar"
                        autocomplete="off"></textarea>
                        
                    </div>
  
                    <div class="mb-6 width49">
                      <div>
                          <label for="option_info_en" class="form-label" >{{__('admin.answerOption_Info')}} ( {{ __('admin.English') }} )</label>
                      </div>
                      <textarea id="option_info_en"
                      class="form-control @error('option_info_en') is-invalid @enderror"
                      type="text"
                      name="option_info_en"
                      autocomplete="off"></textarea>
                      
                    </div>

                    <div class="mb-6 width49">
                        <div>
                            <label for="score" class="form-label" >{{ __('admin.Score') }} </label>
                        </div>
                        <input
                            id="score"
                            class="form-control @error('score') is-invalid @enderror"
                            type="number"
                            name="score"
                            autocomplete="off"
                        />
                    </div>

                    <div class="mb-6 width49">
                        <label class="form-label fs-6">
                          {{ __('admin.Question') }}
                          
                        </label>
                        <select class="category_id form-select" name="question_id">
                          <option value="" disabled selected>
                            {{ $l == 'en' ? 'Choose Question' : 'اختر السؤال' }}
                          </option>
                          @foreach ($questions as $question)
                            <option value="{{ $question->id }}" >{{ $question->{'text_'. $l} }}</option>
                          @endforeach
                        </select>
                      </div>

                   

                      </div>
                  </div>



                  <div>
                      <button type="submit" class="btn btn-primary me-sm-4 data-submit">{{__('admin.submit')}}</button>
                      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{__('admin.Cancel')}}</button>
                  </div>
              </form>

          </div>
      </div>
  </div>
</div>
<!--/ Create JobOrder Modal -->


      </div>
    </div>
  </div>
  <!-- END: Content-->


@stop
@section('scripts')
  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

<!-- Page JS -->
<script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
@stop
@section('pagescripts')
    <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
    <script src="/js/jquery.datetimepicker.full.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#userstable').DataTable({
                "responsive": true, "searching": true, "lengthChange": false, "paging": true, "bInfo": false,
                "columnDefs": [
                    {"orderable": false, "targets":  [3]},
                ],
                @if ($l == 'ar')
                "language": {
                    "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
                    "sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
                    "sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
                    "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix":  "", "sSearch":       "ابحث:", "sUrl":          "",
                    "oPaginate": {"sFirst":    "الأول", "sPrevious": "السابق", "sNext":     "التالي", "sLast":     "الأخير"},
                    "oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}} @endif
                });

        });


        </script>
            
@stop
