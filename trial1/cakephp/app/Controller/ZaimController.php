<?php

class ZaimController extends AppController {
	public $helpers = array('Html', 'Form');

	// public $CONSUMER_KEY = 'b69a26665aacd6731e6a4051cb397e548fd1bce8';
	// public $CONSUMER_SECRET = '88e1af266f5d6eb4b8e5ee41aa58d3c94587a12a';
	// public $GET_REQUEST_TOKEN_URL = 'https://api.zaim.net/v2/auth/request';
	// public $OATH_URL = 'https://auth.zaim.net/users/auth';
	// public $GET_ACCESS_TOKEN_URL = 'https://api.zaim.net/v2/auth/access';
    
    public function index() {

		App::import('Vendor', 'Consumer', array('file' => 'HTTP/OAuth/Consumer.php'));

		session_start();

		// Provider info
		$provider_base = 'https://api.zaim.net/v2/auth/';
		$request_url = $provider_base.'request'; //リクエストトークン取得URL
		$authorize_url = 'https://auth.zaim.net/users/auth'; //認証URL
		$access_url = $provider_base.'access'; //アクセストークン取得URL
		$resource_url = 'https://api.zaim.net/v2/home/user/verify'; //ユーザー情報のURL

		// Consumer info
		$consumer_key = 'b69a26665aacd6731e6a4051cb397e548fd1bce8';
		$consumer_secret = '88e1af266f5d6eb4b8e5ee41aa58d3c94587a12a';
		$callback_url = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['SCRIPT_NAME']);

		// Session clear
		if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'clear') {
			session_destroy();
			$_SESSION = array();
			session_start();
		}

		$content = '';
		try {
			// Initialize HTTP_OAuth_Consumer
			$oauth = new HTTP_OAuth_Consumer($consumer_key, $consumer_secret);

			// Enable SSL
			$http_request = new HTTP_Request2();
			$http_request->setAdapter('curl');
			$http_request->setConfig('ssl_verify_peer', false);
			$consumer_request = new HTTP_OAuth_Consumer_Request;
			$consumer_request->accept($http_request);
			$oauth->accept($consumer_request);
			
			if (!isset($_SESSION['type'])) $_SESSION['type'] = null;
			
			// 2 Authorize
			if ($_SESSION['type']=='authorize' &&
				isset($_GET['oauth_token'], $_GET['oauth_verifier'])) {
				echo '2 Authorize';
				// Exchange the Request Token for an Access Token
				$oauth->setToken($_SESSION['oauth_token']);
				$oauth->setTokenSecret($_SESSION['oauth_token_secret']);
				$oauth->getAccessToken($access_url, $_GET['oauth_verifier']);
			
				// Save an Access Token
				$_SESSION['type'] = 'access';
				$_SESSION['oauth_token'] = $oauth->getToken();
				$_SESSION['oauth_token_secret'] = $oauth->getTokenSecret();
			}
			
			// 3 Access
			if ($_SESSION['type']=='access') {
				echo '3 Access';
				// Accessing Protected Resources
				$oauth->setToken($_SESSION['oauth_token']);
				$oauth->setTokenSecret($_SESSION['oauth_token_secret']);
				$result = $oauth->sendRequest($resource_url, array(), 'GET');
			
				$content = $result->getBody();
			
			// 1 Request
			} else {
				// Get a Request Token
				$oauth->getRequestToken($request_url, $callback_url);
			
				// Save a Request Token
				$_SESSION['type'] = 'authorize';
				$_SESSION['oauth_token'] = $oauth->getToken();
				$_SESSION['oauth_token_secret'] = $oauth->getTokenSecret();
			
				// Get an Authorize URL
				$authorize_url = $oauth->getAuthorizeURL($authorize_url);
			
				$content = "Click the link.<br />\n";
				$content .= sprintf('<a href="%s">%s</a>', $authorize_url, $authorize_url);
			}
		
		} catch (Exception $e) {
			$content .= $e->getMessage();
		}

        $this->set('content', $content);
        $this->set('title_for_layout', 'Zaim API 認証');
	}
	


}