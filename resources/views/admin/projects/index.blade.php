@extends('layouts.admin')

@section('title', __('admin.Projects') )

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">

@stop

@section('content')
<div class="container">
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible mt-2" role="alert">
              {{ __('admin.'.session()->get('message')) }} {{ Session::forget('message') }}
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
   @endif

   @if(session()->has('error'))
     <div class="alert alert-danger alert-dismissible mt-2" role="alert">
       {{ __('admin.'.session()->get('error')) }}
       <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
     </div>
    @endif

    <h4>{{ __('admin.Projects') }}</h4>

    <div class="row mb-6">
        <div class="d-flex align-items-start w-100">
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i></i>                 
                 <span class="mx-2">{{ __('admin.Add New')}} </span>
              </a>
        </div>
      </div>

      <div class="row mb-4">
        <form method="GET" action="{{ route('projects.index') }}" class="d-flex gap-2">
    
            <select name="program_count" class="form-control">
                <option value="">{{ __('admin.filter_by_programs') }}</option>
                <option value="1" {{ request('program_count') === '1' ? 'selected' : '' }}>1</option>
                <option value="5" {{ request('program_count') === '5' ? 'selected' : '' }}>5</option>
                <option value="10" {{ request('program_count') === '10' ? 'selected' : '' }}>10</option>
            </select>
    
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
    
            <button type="submit" class="btn btn-primary">{{ __('admin.Filter') }}</button>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">{{ __('admin.Reset') }}</a>
        </form>
    </div>
   
    <table id="projects-datatable" class="table table-striped">
        <thead>
            <tr>
                <th class="text-center">{{ __('admin.Name') }}</th>
                <th class="text-center">{{ __('admin.Programs') }}</th>
                <th class="text-center">{{ __('admin.Team_members') }}</th>
                <th class="text-center">{{ __('admin.Date') }}</th>
                <th class="text-center">{{ __('admin.Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projects as $project)
            <tr>
                <td class="text-center"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                <td class="text-center">{{ $project->programs_count }}</td>
                <td class="text-center">{{ $project->teamMembers()->count() }}</td>
                <td class="text-center">{{ $project->created_at->format('Y-m-d') }}</td>
                <td class="text-center">
                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-primary" title="{{ __('admin.View') }}">
                    <i class="ti ti-eye"></i>
                </a>
                
                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-warning" title="{{ __('admin.Edit') }}">
                    <i class="ti ti-edit"></i>
                </a>
                
                <form action="{{ route('projects.destroy', $project->id) }}" method="POST" style="display:inline-block;" 
                    onsubmit="return confirm('{{ __('admin.delete_project') }}')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin.Delete') }}">
                      <i class="ti ti-trash"></i>
                  </button>
                </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    

</div>


@stop

@section('scripts')
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#projects-datatable').DataTable({
        "paging": true,
        "searching": true, 
        "ordering": true,
        "info": true,
        "lengthMenu": [10, 25, 50, 100]
    });
});
</script>
@stop
