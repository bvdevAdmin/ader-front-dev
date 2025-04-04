<?php
/*******************************************************************************
 * 사이트 운영 환경
 ******************************************************************************/
define('THIS_IS_HELIX',		true);	// 변조여부 확인 변수
define('STATIC_PATH',		'/var/www/dev-tmp/_static/');	// 운영에 필요한 필수 파일 위치 
define('UPLOAD_PATH',		'/var/www/dev-tmp/_upload/www/');		// 기본환경
define('LANGUAGE',			'kr');	// 사이트 언어
define('SESSION_PREFIX',	'SS_'); // 기본 세션 헤더
define('DEBUG',				false); // 디버깅 모드
define('PAYTEST',			false); // 테스트 결제 여부
define('MEMBER',array(
	'ID' => 'EMAIL',	// 회원 아이디 종류 (고유 아이디, 이메일)
	'JOIN_AUTH' => false	// 회원 가입 인증 방법 
));

define('DBMS','MYSQL'); // MYSQL,MSSQL,ORACLE,MONGODB
// 개발
//if($_SERVER['SERVER_NAME'] == 'dev-tools.fivespace.zone' || $_SERVER['SERVER_ADDR'] == '116.124.128.246') {
if(false) {
	define('DB',array(
		'SERVER'=>'dev-ecommerce-web-rds.cluster-cpqnzu6us5oj.ap-northeast-2.rds.amazonaws.com', // 테스트 서버 위치
		'NAME'=>'dev', // 사용 db
		'USER'=>'aderdev', // 아이디
		'PASSWORD'=>'dkejdpfj19rma!#', // 비밀번호
		'HEAD'=>'' // db table 접두어
	));
}

// 실서버
else {
	define('DB',array(
		'SERVER'=>'prod-ecommerce-web-rds-instance-1.cpqnzu6us5oj.ap-northeast-2.rds.amazonaws.com', // 테스트 서버 위치
		'NAME'=>'dev', // 사용 db
		'USER'=>'aderprod', // 아이디
		'PASSWORD'=>'dkejdpfj19rma!#', // 비밀번호
		'HEAD'=>'' // db table 접두어
	));
}


define('BIZTALK',array(
	'ID'=>'',
	'APIKEY'=>'',
	'APIURL'=>''
));
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

/*******************************************************************************
 * 모듈 선택
 * =======
 * - FORMMAIL : 폼메일 (환경설정 > 메일 디자인)
 * - POPUP : 팝업 창 관리 (환경설정 > 팝업)
 * - VISUAL : 배너 비쥬얼 관리 (환경설정 > 메인비쥬얼)
 * - MEMBER : 회원제 사이트 (회원)
 * - ESTIMATE : 견적 및 의견 접수 폼 (방문자 의견접수)
 * - GALLERY : (갤러리)
 * - BOARD : (게시판)
 * - FAQ : 자주묻는질문 (게시판 > 자주묻는질문)
 * - PROMOTION : 프로모션, 이벤트 게시판
 * - PG : 결제 모듈
 * - SHOP : 쇼핑몰
 * - SNS : SNS
 ******************************************************************************/
define('MODULES',array('POPUP','BOARD','MEMBER'));


define('PAYMETHOD',array(
	'CASH'=>'현금',
	'CARD'=>'카드',
	'ADMIN'=>'기타',
	'NFC'=>'NFC',
	'COUPON'=>'쿠폰'
));

?>