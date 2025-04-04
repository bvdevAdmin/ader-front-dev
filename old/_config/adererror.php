<?php
/*******************************************************************************
 * 사이트 운영 환경
 ******************************************************************************/
define('THIS_IS_HELIX',	true);
define('BASE_DOMAIN',	'http://dev.adererror.com');	// 운영 주소
define('BASE_URL',		'/');							// 기본 경로
define('UPLOAD_DOMAIN',	'http://data.adererror.com');	// 데이터 업로드
define('BASE_PATH',		'/var/www/adererror.com/');		// 기본환경
define('STATIC_PATH',	'/var/www/_static/');		// 기본환경
define('UPLOAD_PATH',	'/var/www/data/');			// 기본환경
define('SESSION_PATH',	'/var/www/session/');		// 기본환경
define('LANGUAGE',		'kr');			// 사이트 기본 언어
define('MULTI_LANGUAGE',true);			// 글로벌 사이트 여부
define('MULTI_LANGUAGE_LIST','en,cn');		// 추가 언어 설정
define('SS_HEAD',		'SS_');			// 세션 헤더
define('DEVELOPE',		false);			// 개발 모드
define('EDITOR',		'SMARTEDITOR2');	// 에디터 종류 (SMARTEDITOR, CHEDITOR4, CHEDITOR5, CKEDITOR)
define('MEMBER_ID',		'ID');			// 회원 아이디 종류 (고유 아이디, 이메일)
define('MEMBER_JOIN_AUTH','NONE');		// 회원 가입 인증 방법 
										// (NONE = 인증없음, EMAIL = 이메일 링크를 통해 가입완료, SMS = 문자인증, ADMIN = 관리자가 직접 인증)

/*******************************************************************************
 * DB 환경
 ******************************************************************************/
define('DBMS',		'MYSQLi');			// DB 종류 (MYSQL,MYSQLi,MSSQL,ORACLE)
define('DB_HEAD',	'');				// DB 테이블 헤더
define('DB_SERVER',	'localhost');		// DB서버
define('DB_NAME',	'adererror');		// DB이름
define('DB_ID',		'adererror');			// DB아이디
define('DB_PW',		'dkejdpfj19rma!');		// DB비밀번호


/*******************************************************************************
 * SNS API키
 ******************************************************************************/
define('GOOGLE_API_KEY','');
define('INSTAGRAM_TOKEN','');
define('FACEBOOK_APP_ID', '');
define('FACEBOOK_ADMIN', '');
define('EBIZWAY_ID','JISIT');
define('EBIZWAY_PW','1234');


define('ALIMTALK', array(
	'CP'=>'MTSC',
	'KEY'=>'a5b4fd4b6636801586d55259eb774c62ca6b7ff1',
	'REPLACE_MSG'=>'L'
));


/*******************************************************************************
 * 운송장 조회 (스마트트래커)
 ******************************************************************************/
define('SMARTTRACKER',array(
	'KEY'=>'HZjkdHagCrQsVzIrWNuYKA',
	'API'=>'http://info.sweettracker.co.kr'
));



/*******************************************************************************
 * PG // 이니시스
 ******************************************************************************/
define('_INICIS_OPENWEB_SIGNKEY','');	// 이니시스 웹표준결제 signkey


/*******************************************************************************
 * PG // 아임포트
 ******************************************************************************/
define('IAMPORT_KEY', '9846813521156228');
define('IAMPORT_SECRET', 'tGk30jNX8cBXl0vzbEaOWnDMKQOiIe6bVrfWK2TTfvRBvmaG0kvXI8JtYyLHNSTIk8E5byYr3P2zbdgc');


/*******************************************************************************
 * PG // 모빌리언스
 ******************************************************************************/


/*******************************************************************************
 * PG // 페이레터
 ******************************************************************************/
//define('PAYLETTER_STOREID','Addererror');
//define('PAYLETTER_HASHKEY','adererror_190321'); // 테스트
define('PAYLETTER_STOREID','adererror');
define('PAYLETTER_HASHKEY','adererror_190514'); // 실적용
define('PAYLETTER_CURRENCY','USD');


/*******************************************************************************
 * PG // 네이버페이
 ******************************************************************************/
define('NAVERPAY_ID','');
define('NAVERPAY_KEY','');
define('NAVERPAY_BUTTON_KEY','');
define('NAVER_KEY','');



/*******************************************************************************
 * 웹컨텐츠
 ******************************************************************************/
define('CONTENTS','collection,editorial,stockist');
define('CONTENTS_DESCRIPTION','COLLECTION,EDITORIAL,STOCKIST');


/*******************************************************************************
 * 페이지 정의
 * =======
 * $_page_m = 레이아웃 정의
 * $_page_fullscreen = 전체 화면 여부, array(레이아웃명 => 전체화면 여부)
 * $_page_module = 내부 상세 레이아웃 정의, array(레이아웃명 => 상세 레이아웃명)
 ******************************************************************************/
$_login_required_page = array(
	/*'bluemark'=>'login',*/
	/*'community'=>'login',*/
	'order'=>'login',
	'mypage'=>'login'
);
$_page = array(
	'intro'=>array(false,''),
	'intro2'=>array(false,''),
	'shop'=>array(false,'','category','goods'),
	'collection'=>array(false,'','detail'),
	'collaboration'=>array(true,
		'eastpak', 'eastpak-campaign', 'eastpak-19fw-campaign',
		'puma', 'puma-campaign', 'puma-lookbook', 'puma-19ss-editorial','puma-19ss-lookbook', 'puma-19fw-campaign', 'puma-19fw-lookbook',
		'g-shock', 'kitsune-19s', 'kitsune-19s-editorial', 'kitsune-19s-lookbook', 'kitsune-18ss-campaign', 'kitsune-18ss-lookbook',
		'10corsocomo', '10cc-19s-lookbook',
		'kitsune'
	),
	'editorial'=>array(true,'image','video','detail'),
	'stockist'=>array(false,'','detail','space-card'),
	'stockist-temp'=>array(false,''),
	'stockist-new'=>array(false,'','detail','space-card'),
	'community'=>array(false,'notice','notice-detail','qna','qna-detail','faq'),
	'cart'=>array(false,''),
	'order'=>array(false,''),
	'login'=>array(false,''),
	'join'=>array(false,''),
	'bluemark'=>array(true,''),
	'checkout'=>array(false,'','ok'),
	'find-account'=>array(false,'id','password'),
	'mypage'=>array(false,
		'','profile','mileage','bluemark','addresses','coupons',
		'board','board-write','board-modify',
		'returns'
	),
	'faq'=>array(false,''),
	'customer'=>array(false,'faq','qna'),
	'terms-of-use'=>array(false,''),
	'privacy-policy'=>array(false,''),
	'error'=>array(false,'')
);


/*******************************************************************************
 * 쇼핑몰관련 설정
 ******************************************************************************/
$_page_checkout = 'checkout';



/*******************************************************************************
 * 관리자 환경
 ******************************************************************************/
define('LOGIN_COUNT',3);		// 로그인 최대 실패 허용 횟수
define('LOGIN_COUNTTIME',30);	// 로그인 실패시 재시도 대기 시간 (분단위)

/*******************************************************************************
 * 모듈 선택
 * =======
 * - formmail : 폼메일 (환경설정 > 메일 디자인)
 * - popup : 팝업 창 관리 (환경설정 > 팝업)
 * - visual : 배너 비쥬얼 관리 (환경설정 > 메인비쥬얼)
 * - member : 회원제 사이트 (회원)
 * - estimate : 견적 및 의견 접수 폼 (방문자 의견접수)
 * - gallery : (갤러리)
 * - board : (게시판)
 * - faq : 자주묻는질문 (게시판 > 자주묻는질문)
 * - promotion : 프로모션, 이벤트 게시판
 * - pg : 결제 모듈
 * - shop : 쇼핑몰
 * - sns : SNS
 ******************************************************************************/
define('MODULES','shop,formmail,board,member,sms,pg,popup,faq');
?>