<?php

// A modified https://github.com/ATomIK/Automatic-SteamAuth

session_start();
ob_start();

include __DIR__.'/openid.php';
class steam extends LightOpenID {
	public static function autologin() {
		if(!isset($_SESSION['steam']) && strpos($_SERVER['HTTP_USER_AGENT'], "Awesom") == false){
			try {
				$openid = new LightOpenID($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				if(!$openid->mode) {
					$openid->identity = 'http://steamcommunity.com/openid';
					header('Location: ' . $openid->authUrl());
				} elseif ($openid->mode == 'cancel') {
					echo 'Canceled auth.';
				} else {
					if($openid->validate()) {
						$id = $openid->identity;
						$url = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
						preg_match($url, $id, $match);
						$_SESSION['steam'] = $match[1];
						header('Location: '.str_replace("?login","",$_GET['openid_return_to']));
					}
				}
			} catch(ErrorException $e) {
				echo $e->getMessage();
			}
		} else {
			if(isset($_GET['openid_identity']) && !empty($_GET['openid_identity'])){
				header('Location: '.$_GET['openid_return_to']);
			}
		}
	}
}

class steamInfo {

	public static $steamid;
	public static $username;
	public static $avaFll;
	public static $avaMed;
	public static $avaSml;

	public function __construct(){
		if(isset($_SESSION['steam'])){
			self::$steamid = $_SESSION['steam'];
			$xml = simplexml_load_string(file_get_contents("http://steamcommunity.com/profiles/".self::$steamid."/?xml=1"));
			self::$username = $xml->steamID;
			self::$avaFll = $xml->avatarFull;
			self::$avaMed = $xml->avatarMedium;
			self::$avaSml = $xml->avatarIcon;
		}

	}

}

if(isset($_GET['login'])&&!isset($_SESSION['steam'])){
	steam::autologin();
}
