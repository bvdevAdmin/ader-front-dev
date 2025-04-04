<?php
/*
 +=============================================================================
 | 
 | 비밀번호 변경
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.11.30
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/
$country = null;
if(isset($_POST['country'])){
	$country		= $_POST['country'];
}

$member_idx = null;
if(isset($_POST['member_idx'])){
	$member_idx		= $_POST['member_idx'];
}

$member_pw = null;
if(isset($_POST['member_pw'])){
	$member_pw		= $_POST['member_pw'];
}

/* member_idx는 현재 비밀번호 변경->이메일로 링크 전달->해당 링크로 비밀번호 변경창으로 이동하는 파라미터이다.*/
/* 추후 변경 가능*/

if($member_idx == null || $country == null){
    $result = false;
	$code	= 401;
	$msg = '링크 주소가 올바르지 않습니다.';
}
else{
    $member_count = 0;
	$sql = "
		UPDATE 
			MEMBER_".$country." 
		SET 
			MEMBER_PW = '".md5($member_pw)."',
            PW_DATE = NOW()
		WHERE
			MEMBER_IDX = ".$member_idx."
	";
	$db->query($sql);
}
?>