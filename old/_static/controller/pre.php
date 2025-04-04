<?php
/*
 +=============================================================================
 | 
 | 기본 초기화 모듈
 | ----------
 |
 | 최초 작성	: 양한빈
 | 최초 작성일	: 2014.06
 | 최종 수정일	: 2017.08.03
 | 설명		:
 |     (2015.03   ) 로그기록 기능 추가(관리자 로그인 시에는 로그 기록 안함)
 |     (2015.09.14) SQL injection 대비 수정
 |     (2016.02.25) _static/library/ -> _static/ 위치 및 
 |                  header.process.php -> _head.php 파일명 변경
 |     (2016.06.13) 불필요한 코드 분리/정리
 |     (2016.10.28) 코드 정리 #2, 쇼핑몰 함수 인클루드
 |     (2017.08.03) 모바일 카운터 추가
 |     (2017.09.21) _head.php -> head.php 파일명 변경
 |     (2021.01.13) head.php -> pre.php 파일명 변경
 |                  class, namespace 도입, htaccess 구조 변경
 | error_reporting => 모든 에러 : E_ALL ^ E_NOTICE // 에러 표시 후 중단 없음 : E_ERROR | E_WARNING | E_PARSE
 | set_time_limit => 30초
 +=============================================================================
*/
@error_reporting(E_ERROR | E_WARNING | E_PARSE);
//@error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
//@ini_set("display_errors", 1);
@set_time_limit(30);


/** 짧은 환경변수를 지원하지 않는다면 */
if (isset($HTTP_POST_VARS) && !isset($_POST)) {
	$_POST   = &$HTTP_POST_VARS;
	$_GET    = &$HTTP_GET_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_ENV    = &$HTTP_ENV_VARS;
	$_FILES  = &$HTTP_POST_FILES;
    if (!isset($_SESSION)) $_SESSION = &$HTTP_SESSION_VARS;
}

/** XSS **/
$xss = new xss();
$_GET = $xss->clean($_GET);
foreach($_GET AS $key=>$val) $$key = @str_replace('>','&gt;',str_replace('<','&lt;',$val));
if(function_exists('apache_request_headers')) {
	$_HEADER = apache_request_headers();
	$_HEADER = $xss->clean($_HEADER);
	foreach($_HEADER AS $key=>$val) $$key = @str_replace('>','&gt;',str_replace('<','&lt;',$val));
}

/** SQL Injection **/
if(function_exists('get_magic_quotes_gpc')) {
	if( !get_magic_quotes_gpc() ) {
		if(is_array($_GET)) {
			$_GET = sql_injection_addslashes($_GET);
			@reset($_GET);
		}
		if(is_array($_POST) ) {
			$_POST = sql_injection_addslashes($_POST);
			@reset($_POST);
		}
		if(is_array($_COOKIE) ) {
			$_COOKIE = sql_injection_addslashes($_COOKIE);
			@reset($_COOKIE);
		}
		if(is_array($_HEADER) ) {
			$_HEADER = sql_injection_addslashes($_HEADER);
			@reset($_HEADER);
		}
	}
}
extract($_GET);
extract($_POST);

/** 환경 설정 정리 **/
$_CONFIG = array(
	'M' => (isset($_GET['_url']))?explode('/',$_GET['_url']):array(''),
	'REAL_URL' => (isset($_GET['_url']))?$_GET['_url']:'/',
	'OS' => 'Linux',
	'SEPARATOR' => '/',
	'PATH' => array(
		'ROOT' => BASE_PATH,
		'STATIC' => (defined('STATIC_PATH'))?STATIC_PATH:str_replace('controller/'.basename(__FILE__),'',realpath(__FILE__)),
		'UPLOAD' => (defined('UPLOAD_PATH'))?UPLOAD_PATH:str_replace('_static/controller/'.basename(__FILE__),'_upload/',realpath(__FILE__)),
		'SESSION' => (defined('SESSION_PATH'))?SESSION_PATH:str_replace('_static/controller/'.basename(__FILE__),'_session/',realpath(__FILE__))
	)
);

/*******************************************************************************
 * 경로 설정
 ******************************************************************************/
if(strstr($_SERVER['SERVER_SOFTWARE'],'IIS') || strstr($_SERVER['SERVER_SOFTWARE'],'Win')) {
	$_CONFIG['OS'] = 'Windows';
	$_CONFIG['SEPARATOR'] = '\\';
}
$_CONFIG['PATH']['LIBRARY']		= $_CONFIG['PATH']['STATIC'].'lib'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['SCHEMA']		= $_CONFIG['PATH']['STATIC'].'schema'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['SHOP']		= $_CONFIG['PATH']['STATIC'].'shop'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['PG']			= $_CONFIG['PATH']['STATIC'].'pg'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['CONTROL']		= $_CONFIG['PATH']['ROOT'].'controller'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['VIEW']		= $_CONFIG['PATH']['ROOT'].'view'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['LAYOUT']		= $_CONFIG['PATH']['VIEW'].'layout'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['PAGE']		= $_CONFIG['PATH']['VIEW'].'page'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['MODAL']		= $_CONFIG['PATH']['VIEW'].'modal'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['PAGE_ERROR']	= $_CONFIG['PATH']['VIEW'].'error'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['API']			= $_CONFIG['PATH']['ROOT'].'api'.$_CONFIG['SEPARATOR'];
$_CONFIG['FILE']['API']			= $_CONFIG['PATH']['STATIC'].'controller/api.php';
$_CONFIG['FILENAME']			= basename($_SERVER['PHP_SELF'], strrchr($_SERVER['PHP_SELF'],'.'));
$_CONFIG['PATH']['APPL']		= str_replace($_CONFIG['FILENAME'].'.php','',$_SERVER['SCRIPT_FILENAME']);
if(defined('DATA_PATH')) $_CONFIG['PATH']['UPLOAD'] = DATA_PATH;
else $_CONFIG['PATH']['UPLOAD'] = '/var/www/data/';
$_CONFIG['PATH']['UPLOAD_ADMIN'] = $_CONFIG['PATH']['UPLOAD'].'admin'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_VISUAL'] = $_CONFIG['PATH']['UPLOAD'].'visual'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_POPUP'] = $_CONFIG['PATH']['UPLOAD'].'popup'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_POPUP_THUMB'] = $_CONFIG['PATH']['UPLOAD'].'popup/thumbnail'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_BOARD'] = $_CONFIG['PATH']['UPLOAD'].'board'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_PROMOTION'] = $_CONFIG['PATH']['UPLOAD'].'promotion'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_PROFILE'] = $_CONFIG['PATH']['UPLOAD'].'profile'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_PROFILE_THUMB'] = $_CONFIG['PATH']['UPLOAD_PROFILE'].'thumbnail'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_PRODUCT'] = $_CONFIG['PATH']['UPLOAD'].'product'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_SOCIAL'] = $_CONFIG['PATH']['UPLOAD'].'social'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_CONTENTS'] = $_CONFIG['PATH']['UPLOAD'].'contents'.$_CONFIG['SEPARATOR'];
$_CONFIG['PATH']['UPLOAD_VIDEO'] = $_CONFIG['PATH']['UPLOAD'].'video'.$_CONFIG['SEPARATOR'];
if(defined('DATA_URL')) $_CONFIG['URL']['UPLOAD'] = DATA_URL;
else $_CONFIG['URL']['UPLOAD'] = BASE_DOMAIN.BASE_URL.'upload/';
$_CONFIG['URL']['UPLOAD_ADMIN'] = $_CONFIG['URL']['UPLOAD'].'admin/';
$_CONFIG['URL']['UPLOAD_VISUAL'] = $_CONFIG['URL']['UPLOAD'].'visual/';
$_CONFIG['URL']['UPLOAD_POPUP'] = $_CONFIG['URL']['UPLOAD'].'popup/';
$_CONFIG['URL']['UPLOAD_POPUP_THUMB'] = $_CONFIG['URL']['UPLOAD_POPUP'].'popup/thumbnail/';
$_CONFIG['URL']['UPLOAD_BOARD'] = $_CONFIG['URL']['UPLOAD'].'board/';
$_CONFIG['URL']['UPLOAD_PROMOTION'] = $_CONFIG['URL']['UPLOAD'].'promotion/';
$_CONFIG['URL']['UPLOAD_PROFILE'] = $_CONFIG['URL']['UPLOAD'].'profile/';
$_CONFIG['URL']['UPLOAD_PROFILE_THUMB'] = $_CONFIG['URL']['UPLOAD_PROFILE'].'thumbnail/';
$_CONFIG['URL']['UPLOAD_PRODUCT'] = $_CONFIG['URL']['UPLOAD'].'product/';
$_CONFIG['URL']['UPLOAD_SOCIAL'] = $_CONFIG['URL']['UPLOAD'].'social/';
$_CONFIG['URL']['UPLOAD_CONTENTS'] = $_CONFIG['URL']['UPLOAD'].'contents/';
$_CONFIG['URL']['UPLOAD_VIDEO'] = $_CONFIG['URL']['UPLOAD'].'video/';
@chmod($_CONFIG['PATH']['ROOT'],0777);

if(!defined('DB_INFO')) {
	define('DB_INFO', DB);
}
$db = new db(); // db 연결

//new session(); // 세션 
//@session_save_path('/var/www/_session/');

if(!function_exists('session_start_samesite')) {
	function session_start_samesite($options = array()) {
		$res = @session_start($options);
		$headers = headers_list();
		foreach ($headers as $header) {
			if (!preg_match('~^Set-Cookie: PHPSESSID=~', $header)) continue;
			$header = preg_replace('~; secure(; HttpOnly)?$~', '', $header) . '; secure; SameSite=None';
			header($header, false);
			break;
		}
		return $res;
	}
}
session_start_samesite();
//@session_start();
