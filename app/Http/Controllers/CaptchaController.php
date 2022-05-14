<?php
namespace App\Http\Controllers;

use TimeHunter\LaravelGoogleReCaptchaV3\Facades\GoogleReCaptchaV3;
use Illuminate\Http\Request;

class CaptchaController extends Controller {

	/**
	 * @param  array{action: string, token: string}  $params
	 * @param  \Illuminate\Http\Request  $request
	 * @return array{success: boolean}
	 */
	public function verify($params, Request $request) {
		$response = GoogleReCaptchaV3::setAction($params['action'])->verifyResponse($params['token'], null);
		return ['success' => $response->isSuccess()];
	}

}
