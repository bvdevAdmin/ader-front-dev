<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 맞춤 구매 SELECT 카테고리 정보
 | ----------------------------------------------------------------------------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.06.02
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}
else{
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}
if ($member_idx == 0 || $country == null) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());

	echo json_encode($json_result);
	exit;
}

$select_customize_category_sql = "
	SELECT
		CC.IDX							AS CATEGORY_IDX,
		CC.CATEGORY_TYPE				AS CATEGORY_TYPE,
		CC.CATEGORY_TXT_".$country."	AS CATEGORY_TXT
	FROM
		CUSTOM_CATEGORY CC
";

$db->query($select_customize_category_sql);

foreach($db->fetch() as $data) {
	$json_result['data'][] = array(
		'category_idx'		=>$data['CATEGORY_IDX'],
		'category_type'		=>$data['CATEGORY_TYPE'],
		'category_txt'		=>$data['CATEGORY_TXT']
	);
}

?>