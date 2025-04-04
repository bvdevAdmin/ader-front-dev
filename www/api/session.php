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

$_SESSION['MEMBER_IDX']		= 134;
$_SESSION['COUNTRY']		= "KR";
$_SESSION['MEMBER_ID']		= "shson@bvdev.co.kr";
$_SESSION['LEVEL_IDX']		= 14;
$_SESSION['MEMBER_NAME']	= "손성환";
$_SESSION['TEL_MOBILE']		= "010-6736-4537";
$_SESSION['MEMBER_EMAIL']	= "shson@bvdev.co.kr";
$_SESSION['MEMBER_BIRTH']	= 19931020;
$_SESSION['AUTH_FLG']		= true;

print_r($_SESSION);

?>