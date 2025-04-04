<?php
/*
+=============================================================================
| 
| API 공통
| -------
|
| 최초 작성	: 양한빈
| 최초 작성일	: 2016.12.12
| 최종 수정일	: 2024.04.27
| 버전		: 2.0
| 설명		: 
| 
+=============================================================================
*/
$time_start = microtime(); // 실행 시작시각
require_once '../../_static/autoload.php';
include $_CONFIG['PATH']['LIBRARY'].'image.php';
include $_CONFIG['PATH']['LIBRARY'].'serialgenerator.php';

/** 01. 공통 함수 및 보안 설정 **/
header('P3P: CP="CAO PSA OUR"');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');
if(defined('ACCESS_ALLOW') && ACCESS_ALLOW==true) {
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
}

if(defined('DEBUG') && DEBUG) {
	@error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
	@ini_set('display_errors', 1);
}

$page = (isset($page) && is_numeric($page)) ? intval($page) : 1;
$pagenum = (isset($pagenum) && is_numeric($pagenum)) ? intval($pagenum) : 20;
$json_request = json_decode(file_get_contents('php://input'),true); // json 방식으로 들어왔을 경우
if(is_array($json_request)) {
	$json_request = $xss->clean($json_request);
	$json_request = sql_injection_addslashes($json_request);
	foreach($json_request as $key => $val) {
		$$key = str_replace('>','&gt;',str_replace('<','&lt;',$val ));
	}
	$json_request = null;
	unset($json_request);
}
$result = true;
$code = 200;
$inc_path = array(
	$_CONFIG['PATH']['APP'].$_CONFIG['M'][0].'/api/'.implode('/',array_slice($_CONFIG['M'],1)).'.php',
	$_CONFIG['PATH']['API'].$_CONFIG['REAL_URL'].'.php',
	$_CONFIG['PATH']['API'].$_CONFIG['REAL_URL'].'/get.php',
	$_CONFIG['PATH']['API'].$_CONFIG['M'][0].'.php',
	$_CONFIG['PATH']['STATIC'].'api/'.$_CONFIG['REAL_URL'].'.php',
	$_CONFIG['PATH']['STATIC'].'api/'.$_CONFIG['M'][0].'/get.php',
	$_CONFIG['PATH']['STATIC'].'api/'.$_CONFIG['M'][0].'.php'
);

/** 02. OAuth 적용시 **/
if(defined('OAUTH') && OAUTH) {
	// 인증 모듈은 패스
	if(!in_array(strtolower($_CONFIG['M'][0]),['oauth'])) {
		if(!array_key_exists('Authorization',$_HEADER)) {
			$code = 752;			
		}
		else {
			$oauth = explode(' ',$_HEADER['Authorization']);
			
			// 인장 방식 Bearer만 인정
			if($oauth[0] != 'Bearer') {
				$code = 752;
			}
			else {
				$data = $db->get($_TABLE['OAUTH'],'ACCESS_TOKEN = ?',array($oauth[1]));
				if(sizeof($data) == 0) {
					$code = 765;
				}
				else {
					$data = $data[0];
					
					if(strtotime($data['EXPIRE_DATE']) < time()) {
						$code = 754; // 토큰 만료됨
					}
				}
			}
		}
	}
}

if($code == 200) {
	for($i=0;$i<sizeof($inc_path);$i++) {
		if(file_exists($inc_path[$i])) {
			include $inc_path[$i];
			break;
		}
	}
}

if(isset($callback) && is_string($callback)) echo $callback.'(';
echo json_encode(array_merge(array(
	'code'=>$code,
	'msg'=>(!isset($msg) || is_string($msg) === FALSE)?$_CODE[$code]:$msg
),(isset($json_result) && is_array($json_result))?$json_result:array()),JSON_UNESCAPED_UNICODE);
if(isset($callback) && is_string($callback)) echo ')';
$db->close();
