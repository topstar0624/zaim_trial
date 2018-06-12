<?php

class MoneyController extends AppController {

	public $helpers = array('Html', 'Form');

	function __construct($request, $response) {
		parent::__construct($request, $response);
		session_start();

		$this->get_date_data();
		$this->validate_date();

		$this->oauth->setToken($_SESSION['oauth_token']);
		$this->oauth->setTokenSecret($_SESSION['oauth_token_secret']);
		$this->base_url = 'https://api.zaim.net/v2/home/';
	}

	/**
	 * メインページ
	 */
    public function index() {
		$this->get_data('user');
		$this->get_log();
		$this->get_select_genre();
		$this->get_category_list();
		$this->get_genre_list();
	}

	/**
	 * パラメーターから日付関連データを取得
	 */
	private function get_date_data() {
		$year = $this->request->year ? $this->request->year : date('Y');
		$month = $this->request->month ? $this->request->month : date('m');
		$target = $year.'/'.$month;
		$this->date_data = array(
			'year' 		=> $year,
			'month' 	=> $month,
			'target' 	=> $target,
			'prev' 		=> date('Y/m', strtotime($target.'/01 -1 month')),
			'next' 		=> date('Y/m', strtotime($target.'/01 +1 month')),
			'start_date'=> date('Ymd', strtotime('first day of '.$target.'/01')),
			'end_date' 	=> date('Ymd', strtotime('last day of '.$target.'/01')),
		);
		$this->set('date_data', $this->date_data);
	}

	/**
	 * 指定された年月が正しくなければログアウト
	 */
	private function validate_date() {
		if(!checkdate($this->date_data['month'], 1, $this->date_data['year'])) {
			$_SESSION = array();
			session_destroy();
			$this->Flash->set('ご指定の年月が間違っていたため、ログアウトしました');
			header('Location: /');
			exit;
		}
	}

	/**
	 * 指定された月のログを取得
	 */
	private function get_log() {
		$url = $this->base_url.'money';
		$result = $this->oauth->sendRequest($url, array(
			'mapping' => 1,
			'start_date' => $this->date_data['start_date'],
			'end_date' => $this->date_data['end_date'],
			'limit' => 100,
		), 'GET');
		$result = json_decode($result->getBody(), true);
		$this->set('log', $result);
		return $result;
	}

	/**
	 * 各種データを取得
	 */
	private function get_data($data_name) {
		$url = $data_name === 'user' ? $data_name.'/verify' : $data_name; //userの場合はverifyを追加
		$url = $this->base_url.$url;
		$result = $this->oauth->sendRequest($url, array('mapping' => 1), 'GET');
		$result = json_decode($result->getBody(), true);
		$this->set($data_name, $result);
		return $result;
	}

	/**
	 * カテゴリーのid=>nameの配列を取得
	 */
	private function get_category_list() {
		$category = $this->get_data('category');
		foreach ($category['categories'] as $c) {
			$category_list[$c['id']] = $c['name'];
		}
		$this->set('category_list', $category_list);
	}

	/**
	 * ジャンルのid=>nameの配列を取得
	 */
	private function get_genre_list() {
		$genre = $this->get_data('genre');
		foreach ($genre['genres'] as $g) {
			$genre_list[$g['id']] = $g['name'];
		}
		$this->set('genre_list', $genre_list);
	}

	/**
	 * カテゴリー別のジャンル
	 */
	private function get_select_genre() {
		$category = $this->get_data('category');
		$genre = $this->get_data('genre');
		$source = '';
		foreach ($category['categories'] as $c) {
			if($c['mode'] !== 'payment' OR !$c['active']) continue;
			$source .= '<optgroup label="'.$c['name'].'">';
			foreach ($genre['genres'] as $g) {
				if($c['id'] == $g['category_id'] && $g['active']) {
					$genre_list[$c['id']][$g['id']] = $g['name'];
					$source .= '<option value="'
						.$c['id'].'__'.$g['id'].'">'
						.$g['name'].'</option>';
				}
			}
		}
		$this->set('select_genre', $source);
	}

	/**
	 * 支出登録
	 */
    public function create() {
		$explode = explode('__', $_POST['category__genre']);
		$url = $this->base_url.'money/payment';
		$result = $this->oauth->sendRequest($url, array(
			'mapping' 		=> 1,
			'category_id'	=> $explode[0],
			'genre_id'		=> $explode[1],
			'amount'		=> $_POST['amount'],
			'date'			=> $_POST['date'],
		), 'POST');
		$this->Flash->set(date('Y/m/d', strtotime($_POST['date'])).' に '.$_POST['amount'].' 円の支出を登録しました');
		header('Location: /money/'.date('Y/m', strtotime($_POST['date'])));
		exit;
	}

	/**
	 * 削除
	 */
    public function delete() {
		foreach ($_POST['mode__id'] as $mode__id) {
			$explode = explode('__', $mode__id);
			$url = $this->base_url.'money/'.$explode[0].'/'.$explode[1];
			$result = $this->oauth->sendRequest($url, array(
				// 'id' => $explode[1],
			), 'DELETE');
		}
		$this->Flash->set($_POST['target'].' の支出データを '.count($_POST['mode__id']).' 件削除しました');
		header('Location: /money/'.$_POST['target']);
		exit;
	}
}

/*
User
GET /v2/home/user/verify

Money Read
GET /v2/home/money

Category
GET /v2/home/category

Genre
GET /v2/home/genre

Account
GET /v2/home/account


Other
GET /v2/account
GET /v2/category
GET /v2/genre
GET /v2/currency




Money Create
POST /v2/home/money/payment
POST /v2/home/money/income
POST /v2/home/money/transfer

Money Update
PUT /v2/home/money/:id

Money Delete
DELETE /v2/home/money/:id

*/