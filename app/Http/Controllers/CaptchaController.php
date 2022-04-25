<?php
namespace App\Http\Controllers;

use TimeHunter\LaravelGoogleReCaptchaV3\Facades\GoogleReCaptchaV3;

class CaptchaController extends Controller {

	/**
	 * @param  array{action: string, token: string} $params
	 * @return array<News>
	 */
	public function verify($params) {
		$response = GoogleReCaptchaV3::setAction($params['action'])->verifyResponse($params['token'], app('request')->getClientIp());
		return ['success' => $response->isSuccess()];
	}

}
