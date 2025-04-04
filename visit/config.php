<?php
/*******************************************************************************
 * 사이트 운영 환경
 ******************************************************************************/
define('BASE_DOMAIN',	'//visit.adererror.com');	// 연결 도메인
define('BASE_URL',		'/');						// 기본환경
define('BASE_PATH',		__DIR__.BASE_URL);			// 기본환경
define('SESSION',array(
	'HEAD'=>BASE_DOMAIN
));

define('UPLOAD_PATH',		'/var/www/dev-247/_upload/www/');		// 기본환경
#define('LANGUAGE',			'kr');	// 사이트 언어
define('DEBUG',				false); // 디버깅 모드
define('PAYTEST',			false); // 테스트 결제 여부
define('MEMBER',array(
	'ID' => 'EMAIL',	// 회원 아이디 종류 (고유 아이디, 이메일)
	'JOIN_AUTH' => false	// 회원 가입 인증 방법 
));

define('DBMS','MYSQL'); // MYSQL,MSSQL,ORACLE,MONGODB
// 개발
if($_SERVER['SERVER_ADDR'] == '116.124.128.246') {
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

/*******************************************************************************
 * 페이지 정의
 ******************************************************************************/
define('PAGE_OPTION',array(
	/*
	'login-false'=>array(
		'page' => array('blue-message'),
		'header'=>'header',
		'footer'=>'footer'
	),
	*/
	'base'=>'base'
));

define('PAGE', array(
	'blue-message' => array('')
));
