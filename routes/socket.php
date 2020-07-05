<?php

$router = $this->app->make('App\Services\SocketRouter');

$router->add('room.get', '\App\Http\Controllers\RoomsController@get');
$router->add('room.create', '\App\Http\Controllers\RoomsController@create');
$router->add('room.delete', '\App\Http\Controllers\RoomsController@delete');
