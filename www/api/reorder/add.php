<?php
/*
 +=============================================================================
 | 
 | 리오더 신청
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.11.21
 | 최종 수정	: 
 | 최종 수정일	: 
 | 버전		: 
 | 설명		: 
 | 
 +=============================================================================
*/

if(isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if ($product_type != null && $product_idx != null) {
		$cnt_reorder = $db->count(
			"REORDER_INFO",
			"
				COUNTRY			= ? AND
				MEMBER_IDX		= ? AND
				PRODUCT_IDX		= ? AND
				OPTION_IDX		= ? AND
				DEL_FLG			= FALSE AND
				NOTICE_FLG		= FALSE
			",
			array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$product_idx,$option_idx)
		);
		
		if ($cnt_reorder == 0) {
			$db->insert(
				"REORDER_INFO",
				array(
					'COUNTRY'		=>$_SERVER['HTTP_COUNTRY'],
					'MEMBER_IDX'	=>$_SESSION['MEMBER_IDX'],
					'MEMBER_LEVEL'	=>$_SESSION['LEVEL_IDX'],
					'PRODUCT_IDX'	=>$product_idx,
					'OPTION_IDX'	=>$option_idx,
					'CREATER'		=>$_SESSION['MEMBER_ID'],
					'UPDATER'		=>$_SESSION['MEMBER_ID']
				)
			);
			
			if ($product_type == "S" && isset($option_info) && count($option_info) > 0) {
				$parent_idx = $db->last_id();
				
				foreach($option_info as $set) {
					$db->insert(
						"REORDER_INFO",
						array(
							'COUNTRY'		=>$_SERVER['HTTP_COUNTRY'],
							'MEMBER_IDX'	=>$_SESSION['MEMBER_IDX'],
							'MEMBER_LEVEL'	=>$_SESSION['LEVEL_IDX'],
							'PRODUCT_IDX'	=>$set['product_idx'],
							'OPTION_IDX'	=>$set['option_idx'],
							'PARENT_IDX'	=>$parent_idx,
							'CREATER'		=>$_SESSION['MEMBER_ID'],
							'UPDATER'		=>$_SESSION['MEMBER_ID']
						)
					);
				}
				
				$parent_idx = $db->last_id();
			}
		} else {
			$json_result['code'] = 300;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0066',array());
			
			echo json_encode($json_result);
			exit;
		}
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>