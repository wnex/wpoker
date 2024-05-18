<?php

$config = [
	'context' => [
		'ssl' => [
			'local_cert'  => 'ssl/cert.pem',
			'local_pk'    => 'ssl/privkey.pem',
			'verify_peer' => false,
		],
	],

	'transport' => 'ssl',
	'host' => env('APP_SOCKET', 'ws://localhost'),
	'port' => env('APP_SOCKET_PORT', '3000'),
];

if (env('APP_ENV') === 'local') {
	$config['context'] = [];
	$config['transport'] = 'tcp';
}

return $config;
