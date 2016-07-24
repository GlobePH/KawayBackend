@extends('template')

@section('body')
	<div class="col-sm-6 landing-top visible-sm visible-xs"></div>
	<div class="col-sm-6 landing-left hidden-sm hidden-xs"></div>
	<div class="col-sm-6">
		@if (Auth::id())
			<div class="row">
				<div class="col-md-12">
					Welcome back, <b>{{Auth::user()->name}}</b>!<br />
					<a href="{{url('/logout')}}">Log Out</a>
				</div>
			</div>
		@else
			<form method="POST" action="{{url('/login')}}">
				{!! csrf_field() !!}
				@if (count($errors) > 0)
					The following errors occured:
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				@endif
				<div class="row">
					<div class="col-md-12">
						<label for="email">Email</label>
						<input type="email" name="email" class="form-control" placeholder="my@kaway.ph" value="{{ old('email') }}" />
					</div>
					<div class="col-md-12">
						<label for="password">Password</label>
						<input type="password" name="password" class="form-control" id="password" />
					</div>
					<div class="col-md-6">
						<input type="checkbox" name="remember" /> Remember Me
					</div>
					<div class="col-md-6 clearfix">
						<button type="submit" class="btn btn-primary pull-right">Log In</button>
					</div>
					<div class="col-md-12">
						Don't have an account? <a href="{{url('/register')}}">Register now</a>!
					</div>
				</div>
			</form>
		@endif
	</div>
@endsection