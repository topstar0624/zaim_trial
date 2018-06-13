<?php

class OAuthsController extends AppController {

	public $helpers = array('Html', 'Form');

	function __construct($request, $response) {
		parent::__construct($request, $response);
		session_start();

		# URL情報
		$this->base_url = 'https://api.zaim.net/v2/';
		$this->request_url = $this->base_url.'auth/request'; //リクエストトークン取得URL
		$this->authorize_url = 'https://auth.zaim.net/users/auth'; //認証URL
		$this->access_url = $this->base_url.'auth/access'; //アクセストークン取得URL
	}

	/**
	 * ログイン
	 */
    public function login() {
		try {

			if (isset($_GET['oauth_token'], $_GET['oauth_verifier'], $_SESSION['type'])
				&& $_SESSION['type']=='request') {
				$this->step2_receive();				
			}

			if (isset($_SESSION['type']) && $_SESSION['type']=='access') {
				$this->step3_access();	
			} else {
				$this->step1_request();
			}

		} catch (Exception $e) {
			$this->flush_error_message($e);
		}
	}

	/**
	 * [step1]承認リクエスト
	 */
	private function step1_request() {
		$this->set('authorize_url', $this->get_authorize_url());
	}

	/**
	 * [step1-1]認証URLを入手
	 */
	private function get_authorize_url() {
		$this->get_request_token($this->oauth);
		$this->save_request_token($this->oauth);
		return $this->oauth->getAuthorizeURL($this->authorize_url);
	}

	/**
	 * [step1-2]リクエストトークンを入手
	 */
	private function get_request_token() {
		$callback_url = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['SCRIPT_NAME']);
		$this->oauth->getRequestToken($this->request_url, $callback_url);
	}

	/**
	 * [step1-3]リクエストトークンを保存
	 */
	private function save_request_token() {
		$_SESSION['type'] = 'request';
		$_SESSION['oauth_token'] = $this->oauth->getToken();
		$_SESSION['oauth_token_secret'] = $this->oauth->getTokenSecret();
	}

	/**
	 * [step2]承認を受ける
	 */
	private function step2_receive() {
		$this->exchange_Token();
		$this->save_access_token();
	}

	/**
	 * [step2-1]リクエストトークンをアクセストークンに変換
	 */
	private function exchange_Token() {
		$this->oauth->setToken($_SESSION['oauth_token']);
		$this->oauth->setTokenSecret($_SESSION['oauth_token_secret']);
		$this->oauth->getAccessToken($this->access_url, $_GET['oauth_verifier']);
	}

	/**
	 * [step2-2]アクセストークンを保存
	 */
	private function save_access_token() {
		$_SESSION['type'] = 'access';
		$_SESSION['oauth_token'] = $this->oauth->getToken();
		$_SESSION['oauth_token_secret'] = $this->oauth->getTokenSecret();
		$this->Flash->set('ログインしました');
	}

	/**
	 * [step3]データにアクセス
	 */
	private function step3_access() {
		header('Location: /logs');
		exit;
	}

	/**
	 * エラーメッセージを表示
	 */
	private function flush_error_message($e) {
		$this->Flash->set($e->getMessage());
		header('Location: /');
		exit;
	}

	/**
	 * ログアウト
	 */
	public function logout() {
		$_SESSION = array();
		session_destroy();
		session_start();
		$this->Flash->set('ログアウトしました');
		header('Location: /');
		exit;
	}
}