<?php
/*
 +=============================================================================
 | 
 | 재입고 알림 신청 취소
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.01.10
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
    if ($reorder_idx > 0) {
		$cnt_reorder = $db->count("REORDER_INFO","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($reorder_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
		if ($cnt_reorder > 0) {
			$db->update(
				"REORDER_INFO",
				array(
					'DEL_FLG'			=>1,

					'UPDATER'			=>$_SESSION['MEMBER_ID'],
					'UPDATE_DATE'		=>NOW(),
				),
				"IDX = ?",
				array($reorder_idx)
			);
			
			$cnt_child = $db->count("REORDER_INFO","PARENT_IDX = ?",array($reorder_idx));
			if ($cnt_child > 0) {
				$db->update(
					"REORDER_INFO",
					array(
						'DEL_FLG'			=>1,

						'UPDATER'			=>$_SESSION['MEMBER_ID'],
						'UPDATE_DATE'		=>NOW(),
					),
					"PARENT_IDX = ?",
					array($reorder_idx)
				);
			}
		} else {
			$json_result['code'] = 304;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0024',array());
			
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