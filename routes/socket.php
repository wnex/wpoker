<?php

$router = $this->app->make('App\Services\SocketRouterContract');

$router->add('room.get', '\App\Http\Controllers\RoomsController@get');
$router->add('room.create', '\App\Http\Controllers\RoomsController@create');
$router->add('room.delete', '\App\Http\Controllers\RoomsController@delete');

$router->add('task.get', '\App\Http\Controllers\TasksController@get');
$router->add('task.create', '\App\Http\Controllers\TasksController@create');
$router->add('task.update', '\App\Http\Controllers\TasksController@update');
$router->add('task.delete', '\App\Http\Controllers\TasksController@delete');

$router->add('new.get', '\App\Http\Controllers\NewsController@get');
