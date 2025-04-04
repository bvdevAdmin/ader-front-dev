<?php
/*******************************************************************************
 * 사이트 운영 환경
 ******************************************************************************/
define('BASE_DOMAIN',	'//dev.adererror.com');		// 기본환경
define('BASE_URL',		'/');			// 기본환경
define('BASE_PATH',		__DIR__.BASE_URL);		// 기본환경
define('DATA_URL',		'https://data.artbyus.co.kr/');
define('DATA_PATH',		'/data/web/');
define('SESSION',array(
	'HEAD'=>''
));

//define('DBMS','MYSQL'); // MYSQL,MSSQL,ORACLE,MONGODB
define('dir_f_api','/var/www/dev-tmp/www/api');

/*******************************************************************************
 * 페이지 정의
 ******************************************************************************/
define('PAGE_OPTION',array(
	'login-false'=>array(
		'page'=>array(
			'main','landing','login','logout','join','search','introduce','pay','kakao','naver','google',
			'guide','privacy-policy','terms-of-use','refund-policy','faq','notice',
			'studio','performance','audition','store','account-search','components',
			'product', 'order','story','mypage','mypage_tmp','standby',
			'member','posting','purchase','as','mok'
		),
		'header'=>'header',
		'footer'=>'footer',
		'login'=>true
	),
	'base'=>'base'
));
define('PAGE',array(
	/* 22-10-02 bvdev.jhsim */
	'components'=>array('nav','footer','basket'),
	'main'=>array(''),
	//'login'=>array('check','join'),
	'login'=>array(''),
	'logout'=>array(''),
	'member'=>array('register','reminder','mypage','update','mileage','coupon','voucher','qna','deposit'),
	'posting'=>array('list','collaboration','collection','exhibition','editorial','lookbook','event','draw','runway_tmp'),
	'search'=>array('shop','product'),
	'product'=>array('bluemark','list','detail'),
	'order'=>array('wish','basket','confirm','complete','basket-list'),
	'purchase'=>array('refund','exchange','complete'),
	'notice'=>array('notify','faq','qna','guide','service','privacy'),
	'collection'=>array('detail'),
	/* 22-10-02 bvdev.jhsim */

	'join'=>array('','google','naver','kakao'),
	'faq'=>array(''),
	'calendar'=>array(''),
	'account-search'=>array(''),
	'introduce'=>array('company','service'),
	'guide'=>array(''),
	'privacy-policy'=>array(''),
	'refund-policy'=>array(''),
	'terms-of-use'=>array(''),
	'booking'=>array('','ok'),
	'pay'=>array('ok','close'),
	// 'notice'=>array(''),
	// 'product'=>array('classify','register','list'),
	// 'order'=>array('list'),
	'standby'=>array('entry','complete'),
	'story'=>array('main'),
	'mypage'=>array('main'),
	'mypage_tmp'=>array('main_tmp'),
	'kakao'=>array('login'),
	'naver'=>array('login'),
	'google'=>array('login'),
	'as'=>array('payment'),
	'mok'=>array('page','request','result')
));
?>