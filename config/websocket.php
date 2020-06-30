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
];

if (env('APP_ENV') === 'local') {
	$config['context'] = [];
	$config['transport'] = 'tcp';
}

return $config;
