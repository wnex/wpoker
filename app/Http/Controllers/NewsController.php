<?php

namespace App\Http\Controllers;

use App\Models\News;

class NewsController extends Controller {

	/**
	 * @param  array  $params
	 * @return array<News>
	 */
	public function get($params) {
		$news = News::orderBy('created_at', 'desc')->get();

		return $news->toArray();
	}

}
