<html>
	<head>
		<title>Kaway</title>
		<link rel="stylesheet" href="{{url('css/bootstrap.min.css')}}" />
		<link rel="stylesheet" href="{{url('css/alertify.core.css')}}" />
		<link rel="stylesheet" href="{{url('css/alertify.default.css')}}" />
		<script type="text/javascript" src="{{url('js/jquery.js')}}"></script>
		<script type="text/javascript" src="{{url('js/bootstrap.min.js')}}"></script>
		<script type="text/javascript" src="{{url('js/alertify.min.js')}}"></script>
		<style>
			.row > div {
				margin-top: 10px;
			}
			
			.landing-top {
				background-color: rgb(0, 25, 100);
				height: 50%;
			}
			
			.landing-left {
				background-color: rgb(0, 25, 100);
				height: 100%;
			}
		</style>
		@yield('head')
	</head>
	<body>
		@yield('body')
	</body>
</html>