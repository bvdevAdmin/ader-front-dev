<?php
/*
 +=============================================================================
 | 
 | 위시 리스트 - 위시리스트 상품 추가
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.13
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.06.26
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($product_idx)) {
		$cnt_wish = $db->count("WHISH_LIST","COUNTRY = ? AND MEMBER_IDX = ? AND PRODUCT_IDX = ? AND DEL_FLG = FALSE",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$product_idx));
		if ($cnt_wish > 0) {
			$json_result['code'] = 402;
			$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0065',array());
			
			echo json_encode($json_result);
			exit;
		} else {
			$insert_wish_list_sql = "
				INSERT INTO
					WHISH_LIST
				(
					COUNTRY,
					MEMBER_IDX,
					MEMBER_ID,
					PRODUCT_IDX,
					PRODUCT_CODE,
					PRODUCT_NAME,
					CREATER,
					UPDATER
				)
				SELECT
					?					AS COUNTRY,
					?					AS MEMBER_IDX,
					?					AS MEMBER_ID,
					PR.IDX				AS PRODUCT_IDX,
					PR.PRODUCT_CODE		AS PRODUCT_CODE,
					PR.PRODUCT_NAME		AS PRODUCT_NAME,
					?					AS CREATER,
					?					AS UPDATER
				FROM
					SHOP_PRODUCT PR
				WHERE
					PR.IDX = ?
			";
		
			$db->query(
				$insert_wish_list_sql,
				array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$_SESSION['MEMBER_ID'],$_SESSION['MEMBER_ID'],$_SESSION['MEMBER_ID'],$product_idx)
			);
		}
		
		$json_result['data'] = $db->count("WHISH_LIST","COUNTRY = ? AND MEMBER_IDX = ? AND DEL_FLG = FALSE",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>