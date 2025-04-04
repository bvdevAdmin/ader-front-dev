<?php
/*******************************************************************************
 * 사이트 운영 환경
 ******************************************************************************/
define('THIS_IS_HELIX',	true);
define('BASE_DOMAIN',	'https://www.adererror.com');		// 기본환경
define('BASE_URL',		'/');						// 기본환경
define('UPLOAD_DOMAIN',	'http://data.adererror.com');	// 데이터 업로드
define('BASE_PATH',		'/var/www/event/');		// 기본환경
define('STATIC_PATH',	'/var/www/_static/');		// 기본환경
define('UPLOAD_PATH',	'/var/www/data/');			// 기본환경
define('SESSION_PATH',	'/var/www/session/');		// 기본환경
define('LANGUAGE',		'kr');			// 사이트 기본 언어
define('MULTI_LANGUAGE',true);			// 글로벌 사이트 여부
define('MULTI_LANGUAGE_LIST','en,fr,sp,jp,cn,it');		// 추가 언어 설정
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
define('MODULES',	'');
?>