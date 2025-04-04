<?php
/*
@error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
@ini_set("display_errors", 1);
*/
require_once '../../_static/autoload.php';

/*==============================================================
	.htaccess 생성
  ==============================================================*/
if(!file_exists('.htaccess')) {
$htaccess = 'RewriteEngine On  
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php?_url=$1 [QSA,L]';
	$fp = fopen('.htaccess','w');
	fputs($fp,$htaccess);
	fclose($fp);
}
function console_log($data){
	echo "<script>console.log('PHP_Console:".$data."');</script>";
}

/*==============================================================
	
  ==============================================================*/
if(isset($_SESSION[SESSION['HEAD'].'NO'])) {
	/** 로그인 되어 있을 경우 **/
	$_CONFIG['MEMBER'] = $db->get($_TABLE['MEMBER'],'IDX=?',array($_SESSION[SESSION['HEAD'].'NO']))[0];
	$_CONFIG['MEMBER']['CONFIG'] = json_decode($_CONFIG['MEMBER']['CONFIG'],true);
}

/*==============================================================
	
  ==============================================================*/
if(is_array($_CONFIG['M']) && sizeof($_CONFIG['M']) > 1) {
	switch($_CONFIG['M'][0]) {
		case '_api': // API 요청일 경우
			@error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
			@ini_set("display_errors", 1);
			header('Access-Control-Allow-Origin: *');
			header('P3P: CP="CAO PSA OUR"');
			header('Access-Control-Allow-Headers: *');
			header('Content-Type: application/json');
			$_CONFIG['M'][0] = '';
			$inc_path = '../api'.implode('/',$_CONFIG['M']).'.php';
			if(!file_exists($inc_path)) {
				$inc_path = $_CONFIG['PATH']['STATIC'].'api/'.$_CONFIG['M'][1].'.php';
			}
			$_CONFIG['M'][0] = '_api';
		break;
		case '_mailform': // 메일 템플릿 요청일 경우
			$inc_path = '../view/mailform/'.$_CONFIG['M'][1].'.html';
		break;
		case '_modal': // 모달 창
			$inc_path = '../view/modal/'.$_CONFIG['M'][1].'.php';
			if(!file_exists($inc_path)) {
				$_m = explode('-',$_CONFIG['M'][1]);
				$inc_path = '../view/modal/'.$_m[0].'-'.$_m[sizeof($_m)-1].'.php';
			}
		break;
		case '_file': // 파일 다운로드
			ob_start();
			switch($_CONFIG['M'][1]) {
				case 'board':
					$bbscode = $_CONFIG['M'][2];
					$no = intval($_CONFIG['M'][3]);
					$file_cnt = intval($_CONFIG['M'][4])-1;

					$data = $db->get($_TABLE['BOARD'],'IDX=? AND BBSCODE=?',array($no,$bbscode))[0]; // 게시판 정보
					$data['FILE'] = json_decode($data['FILE'],true);
					$original_name = $data['FILE'][$file_cnt]['original_name'];
					$file_path = iconv('utf-8','euc-kr',$data['FILE'][$file_cnt]['path']); // 파일 경로
					$data['FILE'][$file_cnt]['download_hit']++; // 파일 다운로드 횟수 증가
					$db->update($_TABLE['BOARD'],array('FILE'=>json_encode($data['FILE'])),'IDX=? AND BBSCODE=?',array($no,$bbscode));
				break;
			}

			// 다운로드 시작
			if(file_exists($file_path))	{
				Header('Content-Type: file/unknown');
				Header('Content-Disposition: attachment; filename='.$original_name);
				Header('Content-Length: '.filesize($file_path));
				header('Content-Transfer-Encoding: binary ');
				Header('Pragma: no-cache');
				Header('Expires: 0');
				flush(); 
				if ($fp = fopen($file_path, 'rb')){   
					print fread($fp, filesize($file_path));
				}
				fclose($fp);
			}
			exit(0);
		break;
		case 'pagebody':
			$pagebody = 'y';
			unset($_CONFIG['M'][0]);
			$_CONFIG['M'] = array_values($_CONFIG['M']);
		break;
	}
	if(isset($inc_path) && file_exists($inc_path)) {
		if($_CONFIG['M'][0] == '_api') include $_CONFIG['FILE']['API'];
		else include $inc_path;
		$db->close();
		exit(0);
	}
}

/*==============================================================
	페이지 정의에 따른 설정
  ==============================================================*/
if (
	!isset($_SESSION[SESSION['HEAD'].'ID']) &&
	(array_key_exists('login-false',PAGE_OPTION) && is_array(PAGE_OPTION['login-false'])) &&
	in_array($_CONFIG['M'][0],PAGE_OPTION['login-false']['page']) === FALSE
) {
	if ($_CONFIG['REAL_URL'] != '/' && PAGE_OPTION['login-false']['login'] == TRUE) {
		$_CONFIG['M'] = array('login');
	} else {
		$_CONFIG['M'][0] = PAGE_OPTION['login-false']['page'][0];
	}

	//$pagebody = 'y';
}

if (in_array($_CONFIG['M'][0],array_keys(PAGE)) === FALSE) {
	foreach(PAGE as $k => $v) {
		//if(array_key_exists('login-false',PAGE_OPTION) && in_array($k,PAGE_OPTION['login-false']['page']) === FALSE && isset($_SESSION[SESSION['HEAD'].'ID'])) {
		if(true) {
			$_CONFIG['M'][0] = $k;
			break;
		}
	}
}

if(sizeof($_CONFIG['M']) > 1 && is_numeric($_CONFIG['M'][sizeof($_CONFIG['M'])-1])) {
	$no = intval($_CONFIG['M'][sizeof($_CONFIG['M'])-1]);
	$_CONFIG['M'][sizeof($_CONFIG['M'])-1] = 'detail';
}

if(sizeof($_CONFIG['M']) == 1 && PAGE[$_CONFIG['M'][0]][0] != '') {
	$_CONFIG['M'][1] = PAGE[$_CONFIG['M'][0]][0];
}

$_inc_page = implode('-',$_CONFIG['M']);

// 전체 화면 여부
//$is_fullscreen = (is_bool($_page[$m][0]))?$_page[$m][0]:false;
/*
$is_fullscreen = false;
if(!isset($pagebody)) $pagebody = 'n';
if(!defined('PAGE_OPTION')) {
}

/*==============================================================
	페이지별 정보 정의
  ==============================================================*/
/*
if(!file_exists($_CONFIG['PATH']['PAGE'].$_inc_page.'.php') && !file_exists($_CONFIG['PATH']['PAGE_ORG'].$_inc_page.'.php')) {
	$_CONFIG['PATH']['PAGE'] = $_CONFIG['PATH']['PAGE_ERROR'];
	$code = 404;
	$_inc_page = $code;
}
*/

/*==============================================================
	레이아웃
  ==============================================================*/
//console_log($_CONFIG['PATH']['PAGE'].$_inc_page);
//print_r($_CONFIG);
if(file_exists($_CONFIG['PATH']['PAGE'].$_inc_page.'.php')) {
	if(!$is_fullscreen && $pagebody != 'y' && file_exists($_CONFIG['PATH']['LAYOUT'].'base.php')) {
		include $_CONFIG['PATH']['LAYOUT'].'base.php';
	}

	if(!$is_fullscreen && $pagebody != 'y' && file_exists($_CONFIG['PATH']['LAYOUT'].'header.php')) {
		include $_CONFIG['PATH']['LAYOUT'].'header.php';
	}
	if(file_exists($_CONFIG['PATH']['CONTROL'].$_inc_page.'.php')) {
		include $_CONFIG['PATH']['CONTROL'].$_inc_page.'.php';
	}
	if(file_exists($_CONFIG['PATH']['PAGE'].$_inc_page.'.php')) {
		include $_CONFIG['PATH']['PAGE'].$_inc_page.'.php';
	}
	if(!$is_fullscreen && $pagebody != 'y' && file_exists($_CONFIG['PATH']['LAYOUT'].'footer.php')) {
		include $_CONFIG['PATH']['LAYOUT'].'footer.php';
	}
}
/*else {
	header('HTTP/1.0 404 Not Found');
}*/
$db->close();
?>
