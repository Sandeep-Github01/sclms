@extends('frontend.partials.layout')

@section('content')
  <h2>Reset Password</h2>

  <form method="POST" action="{{ route('frontend.password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <label>Email</label>
    <input type="email" name="email" value="{{ old('email') }}" required>
    <label>New Password</label>
    <input type="password" name="password" required>
    <label>Confirm Password</label>
    <input type="password" name="password_confirmation" required>
    <button type="submit">Reset Password</button>
  </form>
@endsection
