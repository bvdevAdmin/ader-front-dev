<?php
/*******************************************************************************
 * 사이트 운영 환경
 ******************************************************************************/
define('BASE_DOMAIN',	'//dev.adererror.com');		// 기본환경
define('BASE_URL',		'/');			// 기본환경
define('BASE_PATH',		__DIR__.BASE_URL);		// 기본환경
define('DATA_DOMAIN',	'//data.fivespace.zone');						// 기본환경
define('DATA_URL',		'https://data.artbyus.co.kr/');
define('DATA_PATH',		'/data/web/');
define('SESSION_PATH',	'/var/www-tmp/www/session/');
define('SESSION',array(
	'HEAD'=>''
));
define('CDN','https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user');

define('DEBUG',         true);
define('RECENTLY_EXPIRE_DATE',	14); // 최근 본 상품 유효기간 (14일)
define('PG', array(
	'KEY' => 'test_ck_YZ1aOwX7K8meL9vyEe98yQxzvNPG'
));
define('DBMS',			'MYSQL'); // MYSQL,MSSQL,ORACLE,MONGODB

// 개발
if($_SERVER['SERVER_NAME'] == 'dev2.adererror.com') {
	// define('DB',array(
	// 	'SERVER'=>'dev-ecommerce-web-rds.cluster-cpqnzu6us5oj.ap-northeast-2.rds.amazonaws.com', // 테스트 서버 위치
	// 	'NAME'=>'dev', // 사용 db
	// 	'USER'=>'aderdev', // 아이디
	// 	'PASSWORD'=>'dkejdpfj19rma!#', // 비밀번호
	// 	'HEAD'=>'' // db table 접두어
	// ));
	define('DB',array(
		'SERVER'=>'prod-ecommerce-web-rds-instance-1.cpqnzu6us5oj.ap-northeast-2.rds.amazonaws.com', // 테스트 서버 위치
		'NAME'=>'dev', // 사용 db
		'USER'=>'aderprod', // 아이디
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
	'login-false'=>array(
		'page'=>array(
            'kr','en','cn',
			'main',
			'login','logout','join','find-account','search','introduce','pay',
			'guide','online-store-guide','privacy-policy','terms-of-use','cookie-policy','refund-policy','faq','notice',
			'recently',
			'order',
			'member',
			'stockist',
			'purchase',
			'best',
			'shop',
			'my',
            'collaboration',
			'collection',
			'editorial'
		),
		'header'=>'header',
		'footer'=>'footer',
		'login'=>true
	),
    'account_confirm' => array(
        'page' => array(
            'my/info/modify'
        ),
        'confirm' => 'my/_confirm'
    ),
	'base'=>'base'
));
define('PAGE',array(
    'kr' => array('main','login','logout','join','find-account','notice','best','shop','stockist','my','story','collaboration','search','collection','editorial','recently','faq','online-store-guide','privacy-policy','refund-policy','cookie-policy','terms-of-use','pay'),
    'en' => array('main','login','logout','join','find-account','notice','best','shop','stockist','my','story','collaboration','search','collection','editorial','recently','faq','online-store-guide','privacy-policy','refund-policy','cookie-policy','terms-of-use','pay')
));
