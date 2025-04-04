<?php
/*
 +=============================================================================
 | 
 | 스탠바이 응모
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.15
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($standby_idx)) {
		/* 1. 응모하려는 스탠바이 정보 조회 */
		$page_standby = getPage_standby($db,$standby_idx);
		if (sizeof($page_standby) > 0) {
			/* 2. 스탠바이 응모 여부 체크 */
			$cnt_entry = $db->count("ENTRY_STANDBY","STANDBY_IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ? AND DEL_FLG = FALSE",array($standby_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
			if ($cnt_entry == 0) {
				$now = strtotime(date('Y-m-d H:i'));
				$e_start	= strtotime($page_standby['e_start']);
				$e_end		= strtotime($page_standby['e_end']);
				
				/* 3. 스탠바이 응모기간 체크 */
				if ($now >= $e_start && $now <= $e_end) {
					/* 4. 스탠바이 응모중단 체크 */
					if ($page_standby['entry_flg'] != true) {
						/* 5. 스탠바이 회원등급 체크 */
						if (in_array("0",$page_standby['member_level']) || in_array($_SESSION['MEMBER_LEVEL'],$page_standby['member_level'])) {
							$db->insert(
								"ENTRY_STANDBY",
								array(
									'country'			=>$_SERVER['HTTP_COUNTRY'],
									'standby_idx'		=>$standby_idx,
									'member_idx'		=>$_SESSION['MEMBER_IDX'],
									'member_id'			=>$_SESSION['MEMBER_ID'],
									'member_name'		=>$_SESSION['MEMBER_NAME'],
									'member_level'		=>$_SESSION['LEVEL_IDX'],
									'creater'			=>$_SESSION['MEMBER_ID'],
									'updater'			=>$_SESSION['MEMBER_ID']
								)
							);

							$json_result['code'] = 200;
							$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_INF_0024',array());
						} else {
							/* 스탠바이 회원등급 예외처리 */
							$json_result['code'] = 305;
							$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0135',array());
							
							echo json_encode($json_result);
							exit;
						}
					} else {
						/* 스탠바이 응모중단 예외처리 */
						$json_result['code'] = 304;
						$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0132',array());
						
						echo json_encode($json_result);
						exit;
					}
				} else {
					/* 스탠바이 응모기간 예외처리 */
					$msg_code = "";
					if ($now < $e_start) {
						$msg_code = "MSG_F_ERR_0130";
					} else if ($now > $e_end) {
						$msg_code = "MSG_F_ERR_0131";
					}
					
					$json_result['code'] = 303;
					$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],$msg_code,array());
					
					echo json_encode($json_result);
					exit;
				}
			} else {
				/* 스탠바이 중복 응모 예외처리 */
				$json_result['code'] = 302;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0134',array());
				
				echo json_encode($json_result);
				exit;
			}
		} else {
			/* 스탠바이 조회 실패 예외처리 */
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0129',array());
			
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

function getPage_standby($db,$standby_idx) {
	$page_standby = array();
	
	$select_page_standby_sql = "
		SELECT
			PS.ENTRY_FLG		AS ENTRY_FLG,
			DATE_FORMAT(
				PS.ENTRY_START_DATE,
				'%Y-%m-%d %H:%i'
			)					AS ENTRY_START_DATE,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,
				'%Y-%m-%d %H:%i'
			)					AS ENTRY_END_DATE,
			PS.MEMBER_LEVEL		AS MEMBER_LEVEL
		FROM
			PAGE_STANDBY PS
		WHERE
			COUNTRY = ? AND
			IDX = ?
	";
	
	$db->query($select_page_standby_sql,array($_SERVER['HTTP_COUNTRY'],$standby_idx));
	
	foreach($db->fetch() as $data) {
		$member_level = array();
		if ($data['MEMBER_LEVEL'] != null && strlen($data['MEMBER_LEVEL']) > 0) {
			$member_level = explode(",",$data['MEMBER_LEVEL']);
		}
		
		$page_standby = array(
			'entry_flg'			=>$data['ENTRY_FLG'],
			'e_start'			=>$data['ENTRY_START_DATE'],
			'e_end'				=>$data['ENTRY_END_DATE'],
			'member_level'		=>$member_level
		);
	}
	
	return $page_standby;
}

?>