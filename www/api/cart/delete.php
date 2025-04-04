<?php
/*
 +=============================================================================
 | 
 | 쇼핑백 - 쇼핑백 상품 삭제
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.14
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (is_array($basket_idx) && count($basket_idx) > 0) {
		$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
		$param_bind = array_merge($param_bind,$basket_idx,$basket_idx);
		
		$db->update(
			"BASKET_INFO",
			array(
				'DEL_FLG'		=>1,
				'UPDATE_DATE'	=>NOW(),
				'UPDATER'		=>$_SESSION['MEMBER_IDX'],
			),
			"
				COUNTRY = ? AND
				MEMBER_IDX = ? AND
				IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).") OR
				PARENT_IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).")
			",
			$param_bind
		);
		
		$json_result['data'] = array(
			'basket_cnt'	=>$db->count("BASKET_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND PARENT_IDX = 0 AND DEL_FLG = FALSE",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']))
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

?>