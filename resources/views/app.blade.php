<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link rel="icon" href="/favicon.png">

	<title>Planning Poker</title>

	<!-- Styles -->
	<link rel="stylesheet" type="text/css" href="{{ mix('css/app.css') }}">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="bg-light" data-socket="{{env('APP_SOCKET', 'ws://localhost:3000')}}">

	@include('analytics')

	<div id="app">
		<nav class="navbar navbar-expand-lg navbar-dark mb-3">
			<div class="container">
				<a class="navbar-brand" href="/"><img class="logo" src="/images/logo_header.png"></a>
				<h1 style="display: none;">Planning Poker</h1>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>

				<menu-list></menu-list>
			</div>
		</nav>

		<div class="container">
			<app :socket="socket"></app>

			<footer class="my-5 pt-5 text-muted text-center text-small">
				<statistics :socket="socket" :refresh-every="300"></statistics>
				<p class="mb-1">© {{date('Y') > 2020 ? '2020-'.date('Y') : date('Y')}} wNex</p>
				<ul class="list-inline">
					<li class="list-inline-item"><a href="#"></a></li>
				</ul>
			</footer>
		</div>
	</div>

	<script type="text/javascript" src="{{ mix('js/manifest.js') }}"></script>
	<script type="text/javascript" src="{{ mix('js/vendor.js') }}"></script>
	<script type="text/javascript" src="{{ mix('js/app.js') }}"></script>
</body>
</html>
