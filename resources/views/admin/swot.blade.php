@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')

<div class="container">

    @if (session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div id="error-message" class="alert alert-danger d-none mt-3"></div>

    <!-- Generate SWOT Analysis --> 
    <button id="generate-swot" class="btn btn-warning mt-4">{{__('admin.generate')}}</button>

     <!-- Export to PDF -->
     <a href="{{ route('swot.export') }}" class="btn btn-danger mt-3 ms-4">{{__('admin.export')}}</a>

    <form action="{{ route('swot.save') }}" method="POST">
        @csrf

        <input type="hidden" name="company_id" value="{{ auth()->user()?->company?->id }}">

         <div class="row mt-3">
            <div class="col-md-6">
                <label>{{__('admin.strengths')}}</label>
                <textarea name="strength" rows="8" class="form-control mt-2">{{ $swotAnalysis->strength ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label>{{__('admin.weaknesses')}}</label>
                <textarea name="weakness" rows="8" class="form-control mt-2">{{ $swotAnalysis->weakness ?? '' }}</textarea>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label>{{__('admin.strengths_recommendations')}}</label>
                <textarea name="srecommendation" rows="8" class="form-control mt-2">{{ $swotAnalysis->srecommendation ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label>{{__('admin.weaknesses_recommendations')}}</label>
                <textarea name="wrecommendation" rows="8" class="form-control mt-2">{{ $swotAnalysis->wrecommendation ?? '' }}</textarea>
            </div>
        </div>

         <div class="row mt-3">
            <div class="col-md-6">
                <label>{{__('admin.opportunities')}}</label>
                <textarea name="opportunity" rows="8" class="form-control mt-2">{{ $swotAnalysis->opportunity ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label>{{__('admin.threats')}}</label>
                <textarea name="threat" rows="8" class="form-control mt-2">{{ $swotAnalysis->threat ?? '' }}</textarea>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label>{{__('admin.opportunities_recommendations')}}</label>
                <textarea name="orecommendation" rows="8" class="form-control mt-2">{{ $swotAnalysis->orecommendation ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label>{{__('admin.threats_recommendations')}}</label>
                <textarea name="trecommendation" rows="8" class="form-control mt-2">{{ $swotAnalysis->trecommendation ?? '' }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">{{__('admin.Save')}}</button>
    </form>
</div>

@endsection

@section('pagescripts')
<script>
document.getElementById('generate-swot').addEventListener('click', function () {
    fetch("{{ route('swot.generate') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            Swal.fire({
                title: "{{ __('admin.Error!') }}",
                text: data.error + (data.missing_fields ? "\n" + data.missing_fields.join(", ") : ""),
                icon: "error",
                confirmButtonText: "OK"
            });
        } else {
           
            Swal.fire({
                title: "{{ __('admin.Success!') }}",
                html: "{{ __('admin.swot_generated') }}",
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: "{{ __('admin.OK') }}",
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                buttonsStyling: false,
            }).then(() => {
                location.reload();
            });
        }
    })
    .catch(error => {
        
        let errorMessageDiv = document.createElement('div');
        errorMessageDiv.className = 'alert alert-danger mt-3';
        errorMessageDiv.innerHTML = "Something went wrong while generating the SWOT Analysis.";

        document.querySelector('.container').prepend(errorMessageDiv);
    });
});

</script>

 
@endsection


