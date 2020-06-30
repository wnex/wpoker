<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Planning Poker</title>

	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

	<!-- Styles -->
	<link rel="stylesheet" type="text/css" href="">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body data-socket="{{env('APP_SOCKET', 'ws://localhost:3000')}}">
	<div class="flex-center position-ref full-height">
		<div class="content">
			<div class="container">
				<div class="text-center">
					@include('logo')
					<h1 style="display: none;">Planning Poker</h1>
					<p class="lead"></p>
				</div>

				<div id="app">
					
				</div>

				<footer class="my-5 pt-5 text-muted text-center text-small">
					<p class="mb-1">© {{date('Y') > 2020 ? '2020-'.date('Y') : date('Y')}} wNex</p>
					<ul class="list-inline">
						<li class="list-inline-item"><a href="#"></a></li>
					</ul>
				</footer>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="{{ mix('js/app.js') }}"></script>
</body>
</html>
