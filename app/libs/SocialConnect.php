<?php
	namespace KintoUn\app\libs;
	use Facebook\FacebookRedirectLoginHelper;
	use Facebook\FacebookSession;
	use Facebook\FacebookRequest;
	use LinkedIn\LinkedIn;

	require_once realpath(dirname(__FILE__) . '/../vendor/google/apiclient/autoload.php');

class SocialConnect{

/*
	Require composer:

	    "require": {
        "facebook/php-sdk-v4" : "4.0.*", 		// Facebook Lib
        "kertz/twitteroauth": "dev-master", 	// Twitter Lib
        "linkedinapi/linkedin": "dev-master", 	// Linkedin Lib
        "google/apiclient": "1.1.*@dev" 		// Google Lib
    	}
*/

	/**
	 * Connection/inscription à/par Facebook
	 * @param  Int  	$appId        	Key Id App
	 * @param  String 	$appSecret    	Key App Secret
	 * @param  String 	$redirect_url 	Link to redirect
	 * @param  Array  	$request      	Request you want from Fb
	 * @return Array/String             Return String if token doesn't exist
	 */
	public function facebook_connect($appId, $appSecret, $redirect_url, $request = array()){
		FacebookSession::setDefaultApplication($appId,$appSecret);
		$helper = new FacebookRedirectLoginHelper($redirect_url);
		if(isset($fb_token) || isset($_SESSION['fb_id_session'])){
			$fb_Session = new FacebookSession($fb_token);
		}else{
			$fb_Session = $helper->getSessionFromRedirect();
		}

		if($fb_Session){
			try{
				$fb_token = $fb_Session->getToken();
				$request = new FacebookRequest($fb_Session, 'GET', '/me');
				$profil = $request->execute()->getGraphObject("Facebook\GraphUser");
				/*	if($profil->getEmail() === null){
						throw new \Exception("L'email n'est pas disponible");
					}*/
				return $profil;
			}catch(\Exception $e){
				unset($fb_token);
			}
		}else{
			return $helper->getReRequestUrl($request);
		}
	}

	/**
	 * Connection/inscription à/par Google+
	 * @param  String $client_id     		Simple client ID Key
	 * @param  String $client_secret 		Secret client ID Key
	 * @param  String $redirect_url  		Link for redirect
	 * @return String/Array                	Return String if token doesn't exist
	 */
	public function googlePlus_connect($client_id, $client_secret, $redirect_url){
		$client = new \Google_Client();
		$plus = new \Google_Service_Plus($client);
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_url);
		$client->setScopes('email');

		if (isset($_GET['code']) && !isset($_GET['state'])){
			$client->authenticate($_GET['code']);
			$gp_access_token = $client->getAccessToken();
		}

		if (isset($gp_access_token)){
		  	$client->setAccessToken($gp_access_token);
		} else {
			$authUrl = $client->createAuthUrl();
			return $authUrl;
		}

		if ($client->getAccessToken()){
/*		  $gp_access_token = $client->getAccessToken();
		  $token_data = $client->verifyIdToken()->getAttributes();*/
		  $me = $plus->people->get('me');
		  return $me["modelData"];
		}
	}

	/**
	 * Connection/inscription à/par Twitter
	 * @param  String $consumer_key    	Consumer Key
	 * @param  String $consumer_secret 	Consumer Secret Key
	 * @param  String $urlCallback      Url Callback
	 * @return String/Array             Return String if token doesn't exist
	 */
	public function twitter_connect($consumer_key, $consumer_secret, $urlCallback){
		if(!isset($_REQUEST['oauth_token'])){
			$connection = new \TwitterOAuth($consumer_key, $consumer_secret);
			$request_token = $connection->getRequestToken($urlCallback);
			if(isset($_SESSION['oauth_token']) && isset($_SESSION['oauth_token_secret'])){ unset($_SESSION['oauth_token']); unset($_SESSION['oauth_token_secret']); } 
			$_SESSION['oauth_token'] = $request_token['oauth_token'];
			$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
			$url = $connection->getAuthorizeURL($request_token['oauth_token']);
			return $url;
		}elseif(isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] === $_REQUEST['oauth_token']){ 
			$connection = new \TwitterOAuth($consumer_key, $consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
			$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
			unset($connection);
			$connection = new \TwitterOAuth($consumer_key, $consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
			$twitterInfos = $connection->get('account/verify_credentials');
			unset($_SESSION['oauth_token_secret']);
			unset($_SESSION['oauth_token']);
			return $twitterInfos;
		}
	}

	/**
	 * Connection/inscription à/par Linkedin. 
	 * L'enregistrement doit se faire par une autre action.
	 * @param  String $api_key      	API Key
	 * @param  String $api_secret   	Secret Key
	 * @param  String $callback_url 	Callback Url
	 * @return String/Array             Return String if token doesn't exist
	 */
	public function linkedin_connect($api_key, $api_secret, $callback_url){
		$config = array('api_key' => $api_key, 'api_secret' => $api_secret , 'callback_url' => $callback_url);
		$connection = new LinkedIn($config);
		$scope = array('r_basicprofile','r_emailaddress');
		if (isset($_REQUEST['code']) && strlen($_REQUEST['state']) < 30 ){
		    $code = $_REQUEST['code'];
		    $access_token = $connection->getAccessToken($code);
		    $connection->setAccessToken($access_token);
		    $user = $connection->get("people/~:(id,first-name,last-name,email-address,headline,picture-url,)");
		    return $user;
		}else{
		    if (isset($_REQUEST['error'])){
		        \Debug::Show('error');
		    }
		    else{
		        $authUrl = $connection->getLoginUrl($scope);
		        return $authUrl;
		    }
		}
	}
}