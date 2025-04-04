<?php

// 각 버전 별 맞는 mobileOKManager-php를 사용
$mok_path = "../mok/mobileOK_manager_phpseclib_v3.0_v1.0.2.php";

if (file_exists($mok_path)) {
	require_once $mok_path;
} else {
	/* mok 파일 누락 */
}

date_default_timezone_set('Asia/Seoul');

session_start();

/* 1. 본인확인 인증결과 MOKGetToken API 요청 URL */
$MOK_GET_TOKEN_URL = "https://scert-dir.mobile-ok.com/agent/v1/token/get";  // 개발
// $MOK_GET_TOKEN_URL = "https://cert-dir.mobile-ok.com/agent/v1/token/get";  // 운영

/* 요청하기 버튼 클릭시 이동 PHP (mobileOK-Request PHP) */
$MOK_API_REQUEST_PHP = "./mok_api_request.php";

/* 2. 본인확인 키파일을 통한 비밀키 설정 */
$mobileOK = new mobileOK_Key_Manager();
/* 키파일은 반드시 서버의 안전한 로컬경로에 별도 저장. 웹URL 경로에 파일이 있을경우 키파일이 외부에 노출될 수 있음 주의 */
$key_path = "/본인확인-API 키정보파일 Path/mok_keyInfo.dat";
$password = "키파일 패스워드";
$mobileOK->key_init($key_path, $password);
$mobileOK->set_site_url("본인확인-API 등록 사이트 URL");

// 이용기관 거래ID생성시 이용기관별 유일성 보장을 위해 설정, 이용기관식별자는 이용기관코드 영문자로 반드시 수정
$PREFIX_ID = '본인확인 이용기관식별자 PREFIX';  // 8자이내 영대소문자,숫자  (예) MOK, TESTCOKR

$auth_request_string = mobileOK_api_gettoken($mobileOK, $PREFIX_ID, $MOK_GET_TOKEN_URL);

?>