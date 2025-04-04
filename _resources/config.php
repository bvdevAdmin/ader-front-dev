<?php
/*******************************************************************************
 * 사이트 운영 환경
 ******************************************************************************/
define('THIS_IS_HELIX',		true);	// 변조여부 확인 변수
define('STATIC_PATH',		'/var/www/dev-tmp/_static/');	// 운영에 필요한 필수 파일 위치 
define('SESSION_PREFIX',	'SS_'); // 기본 세션 헤더
define('BIZTALK',array(
	'ID'=>'',
	'APIKEY'=>'',
	'APIURL'=>''
));
define('LANGUAGE', 'KR');

define('NAVERPAY',array(
	'ID' => '',
	'KEY' => '',
	'BUTTON_KEY' => '',
	'NAVER_KEY' => ''
));
define('INICIS',array(
	'MID' => '',
	'MKEY' => '',
	'SIGNKEY' => '',
	'SCRIPT' => 'https://stdpay.inicis.com/stdjs/INIStdPay.js'
));
define('SNS', array(
	'NAVER' => array(
		'CLIENT_ID' => '',
		'SECRET_KEY' => '',
		'LOGIN' => array(
			'REDIRECT_URL' => 'https://dev.adererror.com/join/naver'
		)
	),
	'KAKAO' => array(
		'CLIENT_ID' => '',
		'LOGIN' => array(
			'REDIRECT_URL' => 'https://dev.adererror.com/join/kakao'
		)
	),
	'GOOGLE' => array(
		'CLIENT_ID' => '',
		'SECRET_PW' => '',
		'REDIRECT_URI' => 'https://dev.adererror.com/join/google'
	)
));
define('SMS',array(
	'HOST' => 'munjanara.co.kr', 
	'SSL' => false,
	'ID' => '', // 문자나라 아이디
	'PW' => '', // 문자나라 2차 비밀번호(로그인 후 개인정보 수정에서 설정)
	'TEL' => '', // 보내는분 핸드폰번호(문자전송에서 발신번호 인증 필요!)
	'ADMIN' => '' // 비상시 메시지를 받으실 관리자 핸드폰번호
));
define('SAFENUM',array(
	'IID' => '',
	'CR_ID' => '',
	'IF_ID' => '',
	'SWITCH' => true
));


define('WEEK_NAME',array('일','월','화','수','목','금','토'));

/*******************************************************************************
 * 관리자 환경
 ******************************************************************************/
define('LOGIN_COUNT',3);		// 로그인 최대 실패 허용 횟수
define('LOGIN_COUNTTIME',30);	// 로그인 실패시 재시도 대기 시간 (분단위)
