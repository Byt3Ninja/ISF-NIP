@extends('layouts.admin')

@php
// Determine active tab
$currentRoute = \Route::currentRouteName();
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-6">
      <li class="nav-item">
        <a 
          class="nav-link {{ $currentRoute === 'mentors.list' ? 'active' : '' }}" 
          href="{{ route('mentors.list') }}"
        >
          <i class="ti ti-users me-2"></i>
          {{ __('admin.allMentors') }}
        </a>
      </li>
      <li class="nav-item">
        <a 
          class="nav-link {{ $currentRoute === 'invitations.index' ? 'active' : '' }}" 
          href="{{ route('invitations.index') }}"
        >
          <i class="ti ti-inbox me-2"></i>
          {{ __('admin.invitations') }}
        </a>
      </li>
    </ul>
    
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Invitations</h4>
          </div>
          <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="scopeFilter" class="form-label">Scope</label>
                <select id="scopeFilter" class="form-select">
                  <option value="">All</option>
                  <option value="Public">Public</option>
                  <option value="Organization">List of organizations</option>
                </select>
              </div>
              <div class="col-md-4">
                <label for="statusFilter" class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                  <option value="">All</option>
                  <option value="Pending">Sent</option>
                  <option value="Accepted">Accepted</option>
                </select>
              </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
              <table id="invitationsTable" class="table table-striped">
                <thead>
                  <tr>
                    <th>Invitee Name</th>
                    <th>Title</th>
                    <th>Scope</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($invitations as $invitation)
                  <tr data-scope="{{ $invitation['scope'] }}" data-status="{{ $invitation['status'] }}">
                    <td>{{ $invitation['invitee_name'] }}</td>
                    <td>{{ $invitation['title'] }}</td>
                    <td>{{ $invitation['scope'] }}</td>
                    <td>
                      <span class="badge 
                        @if($invitation['status'] == 'Accepted') bg-success
                        @elseif($invitation['status'] == 'Pending') bg-warning
                        @else bg-danger
                        @endif">
                        {{ $invitation['status'] }}
                      </span>
                    </td>
                    <td>{{ $invitation['date'] }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Function to filter table rows
  function filterTable() {
    var scopeFilter = $('#scopeFilter').val();
    var statusFilter = $('#statusFilter').val();

    $('#invitationsTable tbody tr').each(function() {
      var row = $(this);
      var rowScope = row.data('scope');
      var rowStatus = row.data('status');

      var scopeMatch = !scopeFilter || rowScope === scopeFilter;
      var statusMatch = !statusFilter || rowStatus === statusFilter;

      if (scopeMatch && statusMatch) {
        row.show();
      } else {
        row.hide();
      }
    });
  }

  // Bind filter change events
  $('#scopeFilter, #statusFilter').on('change', function() {
    filterTable();
  });
});
</script>
@endsection
