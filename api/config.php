<?php
/*******************************************************************************
 * 사이트 운영 환경
 ******************************************************************************/
define('DATA_PATH',		'/data/web/');
define('SESSION_PATH',	'/var/www/api/session/');
define('SESSION',array(
	'HEAD'=>''
));
define('DBMS',			'MYSQL'); // MYSQL,MSSQL,ORACLE,MONGODB
define('ACCESS_ALLOW',	true);
// 개발
if($_SERVER['SERVER_ADDR'] == '116.124.128.246') {
    define('BASE_DOMAIN',	'//dev-api.adererror.fivespace.com');		// 기본환경
    define('BASE_URL',		'/');			// 기본환경
    define('BASE_PATH',		__DIR__.BASE_URL);		// 기본환경
    define('DATA_DOMAIN',	'//dev-data.fivespace.zone');
    define('DATA_URL',		'https://dev-data.fivespace.zone/');
    define('DEBUG',         true);
	define('DB',array(
		'SERVER'=>'dev-ecommerce-web-rds.cluster-cpqnzu6us5oj.ap-northeast-2.rds.amazonaws.com', // 테스트 서버 위치
		'NAME'=>'adererror', // 사용 db
		'USER'=>'aderdev', // 아이디
		'PASSWORD'=>'dkejdpfj19rma!#', // 비밀번호
		'HEAD'=>'' // db table 접두어
	));
}

// 실서버
else {
    define('BASE_DOMAIN',	'//api.adererror.com');		// 기본환경
    define('BASE_URL',		'/');			// 기본환경
    define('BASE_PATH',		__DIR__.BASE_URL);		// 기본환경
    define('DATA_DOMAIN',	'//data.adererror.com');
    define('DATA_URL',		'https://data.adererror.com/');
    define('DEBUG',         false);
	define('DB',array(
		'SERVER'=>'prod-ecommerce-web-rds-instance-1.cpqnzu6us5oj.ap-northeast-2.rds.amazonaws.com', // 테스트 서버 위치
		'NAME'=>'adererror', // 사용 db
		'USER'=>'aderprod', // 아이디
		'PASSWORD'=>'dkejdpfj19rma!#', // 비밀번호
		'HEAD'=>'' // db table 접두어
	));
}

/*******************************************************************************
 * 암호화 키
 ******************************************************************************/
define('AES_PASSWORD_KEY','Dkejdpfj1!');  // 암호화 키

define('ACCESS_TOKEN_EXPIRE', 120); // 액세스 토큰 유효시간 (분)
define('REFRESH_TOKEN_EXPIRE', 60*24); // 갱신 토큰 유효시간 (분)
