<?php
define("naverBaseUrl",			"https://dev.adererror.com/naver/login");
define("NAVER_OAUTH_URL",		"https://nid.naver.com/oauth2.0/");
define("NAVER_SESSION_NAME",	"NVR_SESSION");

@session_start();

/*
네이버 간편 로그인 클래스 작성.
*/
class Naver {
	var $tokenDatas				= array();
	var $access_token			= '';			// oauth 엑세스	토큰
	var $refresh_token			= '';			// oauth 갱신 토큰
	var $access_token_type		= 'Bearer';		// oauth 토큰 타입
	var $access_token_expire	= '';			// oauth 토큰 만료	
	
	// 네이버에서 발급받은 클라이언트 아이디
	var $client_id				= 'k4gK4Eon6TG0GwnX5zhM';
	var $client_secret			= 'AyIIX91Li3';	// 네이버에서 발급받은 클라이언트 시크릿키 
	
	var $returnURL				= '';			// 콜백 받을 URL ( 네이버에 등록된 콜백 URI가 우선됨)
	var $state					= '';			// 네이버 명세에 필요한 검증 키 (현재 버전 라이브러리에서 미검증)
	var $loginMode				= 'request';	// 라이브러리 작동 상태
	var $returnCode				= '';			// 네이버에서 리턴 받은 승인 코드
	var $returnState			= '';			// 네이버에서 리턴 받은 검증 코드
	var $nvrConnectState		= false;
	
	// action options
	var $social_id_prefix		= 'social_K_';	// 회원 아이디 생성시 미리 붙는.
	var $autoClose				= true;
	var $showLogout				= true;
	var $is_mobile				= false;
	var $curl					= NULL;
	var $refreshCount			= 1;			// 토큰 만료시 갱신시도 횟수
	var $drawOptions			= array("type" =>"normal","width" =>"160");

	function __construct($argv = array()) {
		if (!in_array('curl', get_loaded_extensions())) {
			//echo 'curl required';
			return false;
		}
		if ($argv['CLIENT_ID']) {
			$this->client_id = trim($argv['CLIENT_ID']);
		}
		if ($argv['CLIENT_SECRET']) {
			$this->client_secret = trim($argv['CLIENT_SECRET']);
		}
		if ($argv['RETURN_URL']) {
			$this->returnURL = trim(urlencode($argv['RETURN_URL']));
		}
		/*
		if ($argv['AUTO_CLOSE'] == false) {
			$this->autoClose = false;
		}
		*/
		if ($argv['SHOW_LOGOUT'] == false) {
			$this->showLogout = false;
		}
		if ($argv['SOCIAL_ID_PREFIX']) {
			$this->social_id_prefix = trim($argv['SOCIAL_ID_PREFIX']);
		}
		if ($argv['IS_MOBILE'] == true) {
			$this->is_mobile = true;
		}

		$this->loadSession();
		
		if (isset($_GET['nvrMode']) && $_GET['nvrMode'] != '') {
			$this->loginMode = 'logout';
			$this->logout();
		}
		
		if ($_GET['state'] && $_GET['code']) {
			$this->loginMode = 'request_token';
			$this->returnCode = $_GET['code'];
			$this->returnState = $_GET['state'];
			$this->_getAccessToken();
		}
		
		if ($this->getConnectState() == false) {
			$this->generate_state();

			if ($_GET['state'] && $_GET['code']) {
				$this->loginMode = 'request_token';
				$this->returnCode = $_GET['code'];
				$this->returnState = $_GET['state'];
				$this->_getAccessToken();
			}
		}
	}

	function getSocialId()
	{
		return $this->social_id_prefix;
	}

	function getIsMobile()
	{
		return $this->is_mobile;
	}

	function login($options = array())
	{
		$this->generate_state();
		if (isset($options['type'])) {
			$this->drawOptions['type'] = $options['type'];
		}
		if (isset($options['width'])) {
			$this->drawOptions['width'] = $options['width'];
		}
		if ($this->loginMode == 'request' && (!$this->getConnectState()) || !$this->showLogout) {
			// 네이버는 웹,모바일 분리 리턴을 할 수 없음.
			$strUrl = naverBaseUrl;
			$url = urlencode($strUrl);
			$return = '
				$(".naver__btn").on("click",function(){
					let return_url = location.href;
					setCookie("return_url", return_url, 10000);
					
					location.href = \''.NAVER_OAUTH_URL.'authorize?response_type=code&client_id='.$this->client_id.'&redirect_uri='.$url.'&state='.$this->state.'\', \'_blank\', \'width=640, height=480\';
					return false;
				});
			';

			return $return;
		}
		if ($this->loginMode == 'request_token') {
			$this->_getAccessToken();
		}
	}

	function logout()
	{
		$this->loginMode = 'logout';
		$this->refreshCount = 1;
		$data = array();
		$data['Authorization'] = $this->access_token_type.' '.$this->access_token;
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_URL, 'https://kapi.naver.com/v1/user/logout');
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
			'Authorization: '.$data['Authorization']
		)
		);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		$retVar = curl_exec($this->curl);
		curl_close($this->curl);
		$this->deleteSession();
		//echo "<script>window.location.href = 'http://".$_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF']."';</script>";
	}

	function getUserProfile($retType = "JSON") {
		if ($this->getConnectState()) {
			$data = array();
			$data['Authorization'] = $this->access_token_type.' '.$this->access_token;
			$this->curl = curl_init();
			$data['propertyKeys'] = array();
			//echo $data['Authorization'];
			curl_setopt($this->curl, CURLOPT_URL, 'https://openapi.naver.com/v1/nid/me');
			curl_setopt($this->curl, CURLOPT_POST, 1);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data['propertyKeys']);
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
				'Authorization: '.$data['Authorization']
			)
			);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			$retVar = curl_exec($this->curl);
			curl_close($this->curl);

			return $retVar;
		} else {
			return false;
		}
	}

	/**
	 * Get AccessToken
	 * 발급된 엑세스 토큰을 반환합니다. 엑세스 토큰 발급은 로그인 후 자동으로 이루어집니다.
	 */
	function getAccess_token()
	{
		if ($this->access_token) {
			return $this->access_token;
		}
	}

	/**
	 * 네이버 연결상태를 반환합니다.
	 * 엑세스 토큰 발급/저장이 이루어진 후 connected 상태가 됩니다.
	 */
	function getConnectState()
	{
		return $this->nvrConnectState;
	}

	function updateConnectState($strState = '')
	{
		$this->nvrConnectState = $strState;
	}

	/**
	 * 토근을 세션에 기록합니다.
	 */
	function saveSession()
	{
		if (isset($_SESSION) && is_array($_SESSION)) {
			$_saveSession = array();
			$_saveSession['access_token'] = $this->access_token;
			$_saveSession['access_token_type'] = $this->access_token_type;
			$_saveSession['refresh_token'] = $this->refresh_token;
			$_saveSession['access_token_expire'] = $this->access_token_expire;
			$this->tokenDatas = $_saveSession;
			foreach ($_saveSession as $k => $v) {
				$_SESSION[NAVER_SESSION_NAME][$k] = $v;
			}
		}
	}
	function deleteSession()
	{
		if (isset($_SESSION) && is_array($_SESSION) && $_SESSION[NAVER_SESSION_NAME]) {
			$_loadSession = array();
			$this->tokenDatas = $_loadSession;
			unset($_SESSION[NAVER_SESSION_NAME]);
			$this->access_token = '';
			$this->access_token_type = '';
			$this->refresh_token = '';
			$this->access_token_expire = '';
			$this->updateConnectState(false);
		}
	}

	/**
	 * 저장된 토큰을 복원합니다.
	 */
	function loadSession()
	{
		if (isset($_SESSION) && is_array($_SESSION) && $_SESSION[NAVER_SESSION_NAME]) {
			$_loadSession = array();
			$_loadSession['access_token'] = $_SESSION[NAVER_SESSION_NAME]['access_token'] ? $_SESSION[NAVER_SESSION_NAME]['access_token'] : '';
			$_loadSession['access_token_type'] = $_SESSION[NAVER_SESSION_NAME]['access_token_type'] ? $_SESSION[NAVER_SESSION_NAME]['access_token_type'] : '';
			$_loadSession['refresh_token'] = $_SESSION[NAVER_SESSION_NAME]['refresh_token'] ? $_SESSION[NAVER_SESSION_NAME]['refresh_token'] : '';
			$_loadSession['access_token_expire'] = $_SESSION[NAVER_SESSION_NAME]['access_token_expire'] ? $_SESSION[NAVER_SESSION_NAME]['access_token_expire'] : '';
			$this->tokenDatas = $_loadSession;
			$this->access_token = $this->tokenDatas['access_token'];
			$this->access_token_type = $this->tokenDatas['access_token_type'];
			$this->refresh_token = $this->tokenDatas['refresh_token'];
			$this->access_token_expire = $this->tokenDatas['access_token_expire'];
			$this->updateConnectState(true);
			$this->saveSession();
		}
	}

	function _getAccessToken()
	{
		$data = array();
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_URL, NAVER_OAUTH_URL.'token?client_id='.$this->client_id.'&client_secret='.$this->client_secret.'&grant_type=authorization_code&code='.$this->returnCode.'&state='.$this->returnState);
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		$retVar = curl_exec($this->curl);
		curl_close($this->curl);
		$NVRreturns = json_decode($retVar);
		if (isset($NVRreturns->access_token)) {
			$this->access_token = $NVRreturns->access_token;
			$this->access_token_type = $NVRreturns->token_type;
			$this->refresh_token = $NVRreturns->refresh_token;
			$this->access_token_expire = $NVRreturns->expires_in;
			$this->updateConnectState(true);
			$this->saveSession();
			//if ($this->autoClose) {
			//	echo '<script>window.close();</script>';
			//}
		}
	}

	function _refreshAccessToken()
	{
		$data = array();
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_URL, NAVER_OAUTH_URL.'token?client_id='.$this->client_id.'&client_secret='.$this->client_secret.'&grant_type=refresh_token&refresh_token='.$this->refresh_token);
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		$retVar = curl_exec($this->curl);
		curl_close($this->curl);
		$NVRreturns = json_decode($retVar);
		if (isset($NVRreturns->access_token)) {
			$this->access_token = $NVRreturns->access_token;
			$this->access_token_type = $NVRreturns->token_type;
			$this->access_token_expire = $NVRreturns->expires_in;
			$this->updateConnectState(true);
			$this->saveSession();
		}
	}

	function generate_state()
	{
		$mt = microtime();
		$rand = mt_rand();
		$this->state = md5($mt.$rand);
	}
}
?>