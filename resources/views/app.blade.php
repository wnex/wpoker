<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link rel="icon" href="/favicon.png">

	<title>{{ $meta['title'] }}</title>
	<meta name="description" content="Free website for scrum planning online.">
	<meta name="author" content="Dmitriy Tyugaev">

	<meta property="og:type" content="website" />
	<meta property="og:title" content="{{ $meta['title'] }}" />
	<meta property="og:description" content="Free website for scrum planning online." />
	<meta property="og:image" content="{{ $meta['image'] }}" />

	<script>var meta = @json($meta);</script>

	<!-- Styles -->
	<link rel="stylesheet" type="text/css" href="{{ mix('css/app.css') }}">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="bg-light" data-socket="{{env('APP_SOCKET', 'ws://localhost:3000')}}">
	<div id="app">
		<nav class="navbar navbar-expand-lg navbar-dark mb-3">
			<div class="container">
				<router-link link :to="{name: 'home'}" class="navbar-brand">
					<img class="logo" src="/images/logo_header.png">
				</router-link>
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

		<div v-if="disconnect" class="disconnect-alert alert alert-danger" role="alert">
			Connecting...
		</div>
	</div>

	<script src="{{ mix('js/manifest.js') }}"></script>
	<script src="{{ mix('js/vendor.js') }}"></script>
	<script src="{{ mix('js/app.js') }}"></script>

	@include('analytics')
</body>
</html>
