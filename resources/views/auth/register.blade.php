@extends('template')

@section('body')
	<div class="col-sm-6 landing-top visible-sm visible-xs"></div>
	<div class="col-sm-6 landing-left hidden-sm hidden-xs"></div>
	<div class="col-sm-6">
		<form method="POST" action="{{url('/register')}}">
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
					<label for="name">Name</label>
					<input type="name" name="name" class="form-control" placeholder="John Doe" value="{{ old('name') }}" />
				</div>
				<div class="col-md-12">
					<label for="email">Email</label>
					<input type="email" name="email" class="form-control" placeholder="my@kaway.ph" value="{{ old('email') }}" />
				</div>
				<div class="col-md-12">
					<label for="password">Password</label>
					<input type="password" name="password" class="form-control" id="password" />
				</div>
				<div class="col-md-12">
					<label for="password">Confirm Password</label>
					<input type="password" name="password_confirmation" class="form-control" id="password" />
				</div>
				<div class="col-md-12 clearfix">
					<button type="submit" class="btn btn-primary pull-right">Register</button>
				</div>
			</div>
		</form>
	</div>
@endsection