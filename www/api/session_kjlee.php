<?php
/*
 +=============================================================================
 | 
 | phpunit 세션 설정용 API
 | -----------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.07.06
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$_SESSION['MEMBER_IDX']		= 121;
$_SESSION['COUNTRY']		= "KR";
$_SESSION['MEMBER_ID']		= "kjlee@bvdev.co.kr";
$_SESSION['LEVEL_IDX']		= 14;
$_SESSION['MEMBER_NAME']	= "이강진";
$_SESSION['TEL_MOBILE']		= "010-8865-0200";
$_SESSION['MEMBER_EMAIL']	= "kjlee@bvdev.co.kr";
$_SESSION['MEMBER_BIRTH']	= 1989-01-21;
$_SESSION['AUTH_FLG']		= true;

print_r($_SESSION);

?>