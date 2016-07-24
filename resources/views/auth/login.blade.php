<!-- resources/views/auth/login.blade.php -->
@extends('template')
<form method="POST" action="/login">
    {!! csrf_field() !!}

    <div>
        Email
        <input type="email" name="email" value="{{ old('email') }}">
    </div>

    <div>
        Password
        <input type="password" name="password" id="password">
    </div>

    <div>
        <input type="checkbox" name="remember"> Remember Me
    </div>

    <div>
        <button type="submit">Login</button>
    </div>
</form>
@section('body')
	<div class="container">
		<form method="POST" action="/login">
			{!! csrf_field() !!}
			<div class="row">
				<div class="col-md-6">
					<label for="email">Email</label>
					<input type="email" name="email" class="form-control" placeholder="my@kaway.ph" value="{{ old('email') }}" />
				</div>
				<div class="col-md-6">
					<label for="password">Password</label>
					<input type="password" name="password" class="form-control" id="password" />
				</div>
				<div class="col-md-6">
					<input type="checkbox" name="remember" /> Remember Me
				</div>
			</div>
		</form>
	</div>
@endsection