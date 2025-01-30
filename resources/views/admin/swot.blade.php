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



    <!-- Generate SWOT Analysis --> 
    <button id="generate-swot" class="btn btn-warning mt-4">{{__('admin.generate')}}</button>
    <form action="{{ route('swot.save') }}" method="POST">
        @csrf

        <input type="hidden" name="company_id" value="{{ auth()->user()->company->id }}">

        <div class="form-group mt-3">
            <label>{{__('admin.strengths')}}</label>
            <textarea name="strength" rows="8" class="form-control">{{ $swotAnalysis->strength ?? '' }}</textarea>
        </div>

        <div class="form-group mt-3">
            <label>{{__('admin.strengths_recommendations')}}</label>
            <textarea name="srecommendation" rows="8" class="form-control">{{ $swotAnalysis->srecommendation ?? '' }}</textarea>
        </div>

        <div class="form-group mt-3">
            <label>{{__('admin.weaknesses')}}</label>
            <textarea name="weakness" rows="8" class="form-control">{{ $swotAnalysis->weakness ?? '' }}</textarea>
        </div>

        <div class="form-group mt-3">
            <label>{{__('admin.weaknesses_recommendations')}}</label>
            <textarea name="wrecommendation" rows="8" class="form-control">{{ $swotAnalysis->wrecommendation ?? '' }}</textarea>
        </div>

        <div class="form-group mt-3">
            <label>{{__('admin.opportunities')}}</label>
            <textarea name="opportunity" rows="8" class="form-control">{{ $swotAnalysis->opportunity ?? '' }}</textarea>
        </div>

        <div class="form-group mt-3">
            <label>{{__('admin.opportunities_recommendations')}}</label>
            <textarea name="orecommendation" rows="8" class="form-control">{{ $swotAnalysis->orecommendation ?? '' }}</textarea>
        </div>

        <div class="form-group mt-3">
            <label>{{__('admin.threats')}}</label>
            <textarea name="threat" rows="8" class="form-control">{{ $swotAnalysis->threat ?? '' }}</textarea>
        </div>

        <div class="form-group mt-3">
            <label>{{__('admin.threats_recommendations')}}</label>
            <textarea name="trecommendation" rows="8" class="form-control">{{ $swotAnalysis->trecommendation ?? '' }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Save</button>
    </form>
</div>

@endsection

@section('pagescripts')
<script>
    document.getElementById('generate-swot').addEventListener('click', function () {
        fetch("{{ route('swot.generate') }}", {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire({
                    title: "Error!",
                    text: data.error + "\n" + data.missing_fields.join(", "),
                    icon: "error",
                    confirmButtonText: "OK"
                });
            } else {
                Swal.fire({
                    title: "Success!",
                    text: "SWOT Analysis Generated Successfully.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    location.reload();
                });
            }
        })
        .catch(error => {
            console.error("Error generating SWOT:", error);
            Swal.fire({
                title: "Error!",
                text: "Something went wrong while generating the SWOT Analysis.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    });
</script>
 
@endsection


