<?php
/*==============================================================
	cloud9 Framework Router 
	------------------------
	
	개발 및 저작권 : 양한빈 azusayhb@naver.com cloud9.today	
	버전 : 3.0.0
	최종 일자 : 2024-06-10
  ==============================================================*/
require_once '../../_static/autoload.php';

if(defined('DEBUG') && DEBUG) {
	@error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
	@ini_set('display_errors', 1);
}

/*==============================================================
	view 이외의 요청 분기
  ==============================================================*/
if(PAGE_TYPE != '') {
	switch($_CONFIG['M'][0]) {
		case '_xls': // 엑셀 Export
			$inc_path = $_CONFIG['PATH']['STATIC'].'controller/xls.php';
		break;

		case '_api': // API 요청일 경우
			$inc_path = array(
				$_CONFIG['PATH']['APP'].$_CONFIG['M'][1].'/api/'.implode('/',array_slice($_CONFIG['M'],2)).'.php',
				$_CONFIG['PATH']['API'].$_CONFIG['REAL_URL'].'.php',
				$_CONFIG['PATH']['API'].$_CONFIG['REAL_URL'].'/get.php',
				$_CONFIG['PATH']['API'].$_CONFIG['M'][1].'.php',
				$_CONFIG['PATH']['STATIC'].'api/'.$_CONFIG['REAL_URL'].'.php',
				$_CONFIG['PATH']['STATIC'].'api/'.$_CONFIG['M'][1].'/get.php',
				$_CONFIG['PATH']['STATIC'].'api/'.$_CONFIG['M'][1].'.php'
			);
		break;
		case '_mailform': // 메일 템플릿 요청일 경우
			$inc_path = array(
				'../views/mailform/'.$_CONFIG['M'][1].'.php',
				'../views/mailform/'.$_CONFIG['M'][1].'.html',
				'../views/mailform/'.$_CONFIG['REAL_URL'].'.php',
				'../views/mailform/'.$_CONFIG['REAL_URL'].'.html'
			);
		break;
		case '_modal': // 모달 창
			$inc_path = array(
				$_CONFIG['PATH']['APP'].$_CONFIG['M'][1].$_CONFIG['SEPARATOR'].'views'.$_CONFIG['SEPARATOR'].implode('/',array_slice($_CONFIG['M'],2)).'.php',
				$_CONFIG['PATH']['PAGE'].$_CONFIG['REAL_URL'].'.php',
				$_CONFIG['PATH']['VIEW'].'modal/'.$_CONFIG['M'][1].'.php'
			);
		break;
		case '_script': // 스크립트
			$inc_path = array(
				$_CONFIG['PATH']['APP'].$_CONFIG['M'][1].'/views/'.implode('/',array_slice($_CONFIG['M'],2)).'.js',
				$_CONFIG['PATH']['PAGE'].implode('/',array_slice($_CONFIG['M'],1)).'.js'
			);
		break;
		case '_pagebody':
			$pagebody = true;
			unset($_CONFIG['M'][0]);
			$_CONFIG['M'] = array_values($_CONFIG['M']);
			$_CONFIG['PATH']['APP_VIEW']	= $_CONFIG['PATH']['APP'].$_CONFIG['M'][0].$_CONFIG['SEPARATOR'].'views'.$_CONFIG['SEPARATOR'];
		break;
	}
	if(isset($inc_path) && is_array($inc_path)) {
		for($i=0;$i<sizeof($inc_path);$i++) {
			if(file_exists($inc_path[$i])) {
				$_inc_path = $inc_path[$i];
				break;
			}
		}
	}
	if(isset($_inc_path)) {
		$result = true;
		$code = 200;
		if(sizeof($_CONFIG['M']) > 2) $m = trim(strtolower($_CONFIG['M'][2]));
		if(!isset($pagenum)) $pagenum = 20;
		if(!isset($page) || is_numeric($page) == FALSE) $page = 1;
		$page = intval($page);
		$pagenum = intval($pagenum);
		include $_CONFIG['PATH']['LIBRARY'].'image.php';
		include $_CONFIG['PATH']['LIBRARY'].'serialgenerator.php';
		try {
			include $_inc_path;
		}
		catch(Exception $e) {
			$code = 999;
			$msg = $e->getMessage();
		}
		
		/*
		$msg = (!isset($msg) || is_string($msg) === FALSE)?$_CODE[$code]:$msg;
        if($_CONFIG['M'][0] == '_api') {
			header('P3P: CP="CAO PSA OUR"');
			header('Access-Control-Allow-Headers: *');
			header('Content-Type: application/json');

			if(isset($callback) && is_string($callback)) echo $callback.'(';
			echo json_encode(array_merge(array(
				'code'=>$code,
				'msg'=>$msg
			),(isset($json_result) && is_array($json_result))?$json_result:array()),JSON_UNESCAPED_UNICODE);
			if(isset($callback) && is_string($callback)) echo ')';
		}
		activity_log($_inc_path);
		*/
		
		if($_CONFIG['M'][0] == '_api' && $_CONFIG['M'][1] != "mok") {
			header('P3P: CP="CAO PSA OUR"');
			header('Access-Control-Allow-Headers: *');
			header('Content-Type: application/json');

			if(isset($callback) && is_string($callback)) echo $callback.'(';
			echo json_encode(array_merge(array(
				'code'=>$code,
				'msg'=>(!isset($msg) || is_string($msg) === FALSE)?$_CODE[$code]:$msg
			),(isset($json_result) && is_array($json_result))?$json_result:array()),JSON_UNESCAPED_UNICODE);
			if(isset($callback) && is_string($callback)) echo ')';
		}
		activity_log($_inc_path);
		
		$db->close();
		exit(0);
	}
    if(defined('DEBUG') && DEBUG == true && PAGE_TYPE != 'pagebody') {
		$code = 404;
		$msg = $_CODE[$code];
        if(PAGE_TYPE == 'script') {
			foreach($inc_path as $script) {
	            echo 'console.log("'.str_replace('\\','\\\\',$script).'");'.chr(10);
			}
        }
        else {
            print_r($inc_path);
            //echo implode('/',array_slice($_CONFIG['M'],1));
        }
		activity_log($inc_path[0]);
		$db->close();
        exit(0);
    }
}

/*==============================================================
	페이지 정의에 따른 설정
  ==============================================================*/
$_inc_page = null;
if(isset($_SESSION[SESSION['HEAD'].'NO']) && file_exists($_CONFIG['PATH']['APP_VIEW'].implode('/',array_slice($_CONFIG['M'],1)).'.php')) {
	$_CONFIG['PATH']['PAGE'] = $_CONFIG['PATH']['APP_VIEW'];
	$_inc_page = implode('/',array_slice($_CONFIG['M'],1));
}

elseif(!defined('PAGE_TYPE') || PAGE_TYPE == '') {
    if(!isset($_SESSION[SESSION['HEAD'].'NO']) && isset(PAGE_OPTION['login-false']) && isset(PAGE_OPTION['login-false']['page']) && in_array($_CONFIG['M'][0],PAGE_OPTION['login-false']['page']) === FALSE) {
        $_CONFIG['M'] = [];
        $_CONFIG['M'][0] = PAGE_OPTION['login-false']['page'][0];
    }

    elseif(defined('PAGE') && is_array(PAGE) && in_array($_CONFIG['M'][0],array_keys(PAGE)) === FALSE) {
        foreach(PAGE as $k => $v) {
            $_CONFIG['M'][0] = $k;
            break;
        }
    }
}
if($_inc_page == null) {
	// 마지막에 숫자만 들어온 경우 상세보기 페이지 지정 
	if(is_numeric($_CONFIG['M'][sizeof($_CONFIG['M'])-1]) && file_exists($_CONFIG['PATH']['PAGE'].implode('/',array_slice($_CONFIG['M'],0,sizeof($_CONFIG['M'])-1)).'.no.php')) {
		$_inc_page = implode('/',array_slice($_CONFIG['M'],0,sizeof($_CONFIG['M'])-1)).'.no';
	}
	else {
		$_inc_page = implode('/',$_CONFIG['M']);
	}

	// 다중 언어 사용시 스크립트 위치를 확인하기 위함
	if(defined('PAGE')) {
		foreach(PAGE as $key => $val) {
			$_inc_page_f = $key;
			if(sizeof($_CONFIG['M']) > 1) {
				$_inc_page_f .= '/'.implode('/',array_slice(explode('/',$_inc_page),1));
			}
			break;
		}
	}
}

// 전체 화면 여부
$is_fullscreen = false;
if(!isset($pagebody) || !is_bool($pagebody)) $pagebody = false;
if(defined('PAGE_OPTION') && isset(PAGE_OPTION['fullscreen']) && isset(PAGE_OPTION['fullscreen']['page'])) {
	if(in_array($_inc_page,PAGE_OPTION['fullscreen']['page'])) $is_fullscreen = true;
}

/*==============================================================
	레이아웃
  ==============================================================*/
if(file_exists($_CONFIG['PATH']['PAGE'].$_inc_page.'.php') || (defined('VIEW_PASS_THROUGH') && VIEW_PASS_THROUGH && !$pagebody)) {
	if(!$is_fullscreen && !$pagebody && file_exists($_CONFIG['PATH']['LAYOUT'].'base.php')) {
		if(file_exists($_CONFIG['PATH']['LAYOUT'].$_CONFIG['M'][0].'/base.php')) {
			include $_CONFIG['PATH']['LAYOUT'].$_CONFIG['M'][0].'/base.php';
		}
		else {
			include $_CONFIG['PATH']['LAYOUT'].'base.php';
		}
	}
	if(!$is_fullscreen && !$pagebody && file_exists($_CONFIG['PATH']['LAYOUT'].'header.php')) {
		if(file_exists($_CONFIG['PATH']['LAYOUT'].$_CONFIG['M'][0].'/header.php')) {
			include $_CONFIG['PATH']['LAYOUT'].$_CONFIG['M'][0].'/header.php';
		}
		else {
			include $_CONFIG['PATH']['LAYOUT'].'header.php';
		}
	}
	if((file_exists($_CONFIG['PATH']['LAYOUT'].'header.php') || $pagebody || $is_fullscreen) && file_exists($_CONFIG['PATH']['PAGE'].$_inc_page.'.php')) {
		include $_CONFIG['PATH']['PAGE'].$_inc_page.'.php';

		if((!isset($no_script) || str2bool($no_script) != true) 
			&& (file_exists($_CONFIG['PATH']['PAGE'].$_inc_page.'.js') || file_exists($_CONFIG['PATH']['PAGE'].$_inc_page_f.'.js'))) {
			echo '<script>';
			if(file_exists($_CONFIG['PATH']['PAGE'].$_inc_page_f.'.js')) {
				include $_CONFIG['PATH']['PAGE'].$_inc_page_f.'.js';
			}
			else {
				include $_CONFIG['PATH']['PAGE'].$_inc_page.'.js';
			}
			echo '</script>';
		}
	}
	if(!$is_fullscreen && !$pagebody && file_exists($_CONFIG['PATH']['LAYOUT'].'footer.php')) {
		if(file_exists($_CONFIG['PATH']['LAYOUT'].$_CONFIG['M'][0].'/footer.php')) {
			include $_CONFIG['PATH']['LAYOUT'].$_CONFIG['M'][0].'/footer.php';
		}
		else {
			include $_CONFIG['PATH']['LAYOUT'].'footer.php';
		}
		if((!isset($no_script) || str2bool($no_script) != true) && file_exists($_CONFIG['PATH']['PAGE'].'_base.js')) {
			echo '<script>';
			include $_CONFIG['PATH']['PAGE'].'_base.js';
			echo '</script>';
		}		
	}
}
else {
	if(defined('DEBUG') && DEBUG) {
        if(defined('PAGE_TYPE') && PAGE_TYPE == 'script') {
            echo 'console.warn("'.$_CONFIG['PATH']['PAGE'].$_inc_page.'.js");';
        }
        else {
    		echo $_CONFIG['PATH']['PAGE'].$_inc_page.'.php';
        }
	}
	else {
		header('HTTP/1.0 404 Not Found');
	}
}
$db->close();
