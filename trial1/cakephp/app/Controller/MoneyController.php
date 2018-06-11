<?php

class MoneyController extends AppController {

	public $helpers = array('Html', 'Form');

	function __construct($request, $response) {
		parent::__construct($request, $response);
		session_start();

		$this->oauth->setToken($_SESSION['oauth_token']);
		$this->oauth->setTokenSecret($_SESSION['oauth_token_secret']);
		$this->base_url = 'https://api.zaim.net/v2/';
	}

	/**
	 * メインページ
	 */
    public function index() {
		// $ym = $this->get_ym();
		$this->set('user_data', $this->get_user_data());
		$this->set('money_data', $this->get_money_data());
	}

	/**
	 * パラメーターから年月を取得
	 */
	private function get_ym() {
		$year = $this->request->year ? $this->request->year : date('Y');
		$month = $this->request->month ? $this->request->month : date('m');
		$this->set('year', $year);
		$this->set('month', $month);
	}

	/**
	 * ユーザーデータを取得
	 */
	private function get_user_data() {
		$url = $this->base_url.'home/user/verify';
		$result = $this->oauth->sendRequest($url, array(), 'GET');
		return json_decode($result->getBody(), true);
	}

	/**
	 * 家計簿データを取得
	 */
	private function get_money_data() {
		$url = $this->base_url.'home/money';
		$result = $this->oauth->sendRequest($url, array(
			'mapping' => 1,
		), 'GET');
		return json_decode($result->getBody(), true);
	}
}