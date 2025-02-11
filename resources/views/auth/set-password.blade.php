@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{__('admin.set_password')}}</h2>
    <form action="{{ route('accept-invitation', $invitation->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="password">{{__('admin.Password')}}</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">{{__('admin.Confirm Password')}}</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">{{__('admin.set_password')}}</button>
    </form>
</div>
@endsection
