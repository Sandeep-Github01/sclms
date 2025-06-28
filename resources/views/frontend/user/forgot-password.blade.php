@extends('frontend.partials.layout')

@section('content')
  <h2>Forgot Your Password?</h2>

  @if(session('status'))
    <p class="success">{{ session('status') }}</p>
  @endif

  <form method="POST" action="{{ route('frontend.password.email') }}">
    @csrf
    <label>Email</label>
    <input type="email" name="email" required>
    <button type="submit">Send Reset Link</button>
  </form>
@endsection
`