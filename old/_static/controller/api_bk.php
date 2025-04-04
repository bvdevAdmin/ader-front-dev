<?php
/*
+=============================================================================
| 
| API 공통
| -------
|
| 최초 작성		: 양한빈
| 최초 작성일	: 2016.12.12
| 최종 수정일	: 
| 버전		: 1.0
| 설명		: 
| 
+=============================================================================
*/
/** 01. 공통 함수 및 보안 설정 **/
$time_start = getmicrotime(); // 실행 시작시각
header('P3P: CP="CAO PSA OUR"');
header('Content-Type: application/json');
if(defined('ACCESS_ALLOW') && ACCESS_ALLOW==true) {
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
}

/** 02. 변수 정리 **/
$_plain_printout = false;
$result = true;
$code = 200;
if(sizeof($_CONFIG['M']) > 2) $m = trim(strtolower($_CONFIG['M'][2]));
if(!isset($m) && isset($api_module)) $m = $api_module[0];
if(!isset($pagenum) || is_numeric($pagenum) == FALSE) $pagenum = 20;
if(!isset($page) || is_numeric($page) == FALSE) $page = 1;
$page = intval($page);
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
include $_CONFIG['PATH']['LIBRARY'].'image.php';
include $_CONFIG['PATH']['LIBRARY'].'serialgenerator.php';
include $inc_path;
if(isset($callback) && is_string($callback)) echo $callback.'(';
echo json_encode(array_merge(array(
	'code'=>$code,
	'msg'=>(!isset($msg) || is_string($msg) === FALSE)?$_CODE[$code]:$msg
),(isset($json_result) && is_array($json_result))?$json_result:array()));
if(isset($callback) && is_string($callback)) echo ')';
