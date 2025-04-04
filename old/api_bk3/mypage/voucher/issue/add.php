<?php
/*
 +=============================================================================
 | 
 | 바우처 등록
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

include_once("/var/www/www/api/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

if(!isset($country) && $member_idx == 0){
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($country) && $member_idx > 0 && isset($voucher_issue_code)) {
	$voucher_cnt = $db->count(
		"
			VOUCHER_ISSUE VI
			LEFT JOIN VOUCHER_MST VM ON
			VI.VOUCHER_IDX = VM.IDX
			LEFT JOIN MEMBER_".$country." MB ON
			VI.MEMBER_IDX = MB.IDX
			LEFT JOIN MEMBER_LEVEL ML ON
			MB.LEVEL_IDX = ML.IDX
		","
			VI.VOUCHER_ISSUE_CODE = '".$voucher_issue_code."'
		"
	);
	
	if($voucher_cnt == 0){
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0078', array());
		
		echo json_encode($json_result);
		exit;
	} else {
		$select_verify_sql = "
			SELECT 
				VI.IDX					AS ISSUE_IDX,
				VI.VOUCHER_ADD_DATE		AS VOUCHER_ADD_DATE,
				VM.VOUCHER_TYPE			AS VOUCHER_TYPE,
				VM.MEMBER_LEVEL			AS MEMBER_LEVEL,
				CASE
					WHEN 
						VM.VOUCHER_END_DATE < NOW()
						THEN 
							FALSE
					ELSE
						TRUE
				END						AS ISSUE_DATE_FLG,
				MB.MEMBER_ID			AS MEMBER_ID,
				MB.LEVEL_IDX			AS LEVEL_IDX,
				ML.TITLE				AS TITLE
			FROM
				VOUCHER_ISSUE VI
				LEFT JOIN VOUCHER_MST VM ON
				VI.VOUCHER_IDX = VM.IDX
				LEFT JOIN MEMBER_".$country." MB ON
				VI.MEMBER_IDX = MB.IDX
				LEFT JOIN MEMBER_LEVEL ML ON
				MB.LEVEL_IDX = ML.IDX
			WHERE
				VI.VOUCHER_ISSUE_CODE = '".$voucher_issue_code."'
		";
		
		$db->query($select_verify_sql);
		
		$error_msg = null;
		
		foreach($db->fetch() as $verify_data){
			if($verify_data['VOUCHER_ADD_DATE'] != NULL){
				$json_result['code'] = 301;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0060', array());
				
				echo json_encode($json_result);
				exit;
			}
			
			if($verify_data['ISSUE_DATE_FLG'] == FALSE){
				$json_result['code'] = 301;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0021', array());
				
				echo json_encode($json_result);
				exit;
			}
			
			if($verify_data['MEMBER_LEVEL'] != 'ALL'){
				if(!strpos($verify_data['MEMBER_LEVEL'], strval($verify_data['LEVEL_IDX']))){
					$json_result['code'] = 301;
					$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0070', array());
				
					echo json_encode($json_result);
					exit;
				}
			}
			
			if($verify_data['VOUCHER_TYPE'] != 'OFF'){
				if($verify_data['MEMBER_ID'] != $member_id){
					$json_result['code'] = 301;
					$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0099', array());
					
					echo json_encode($json_result);
					exit;
				}
			}
			
			$update_voucher_issue_sql = "
				UPDATE 
					VOUCHER_ISSUE VI, 
					VOUCHER_MST VM
				SET       
					VI.MEMBER_IDX = ".$member_idx.",
					VI.MEMBER_ID = '".$member_id."',
					VI.COUNTRY = '".$country."',
					VI.VOUCHER_ADD_DATE = NOW(),
					VI.USABLE_START_DATE = 
					CASE
						WHEN 
							VM.VOUCHER_DATE_TYPE = 'FXD' 
							THEN 
								VM.VOUCHER_START_DATE 
						WHEN 
							VM.VOUCHER_DATE_TYPE = 'PRD' 
							THEN 
								NOW() 
					END,
					VI.USABLE_END_DATE = 
					CASE
						WHEN 
							VM.VOUCHER_DATE_TYPE = 'FXD' 
							THEN 
								VM.VOUCHER_END_DATE 
						WHEN 
							VM.VOUCHER_DATE_TYPE = 'PRD' 
							THEN 
								NOW() + INTERVAL VM.VOUCHER_DATE_PARAM DAY  
					END,
					VI.UPDATE_DATE = NOW(),
					VI.UPDATER = '".$member_id."'
				WHERE  
					VI.VOUCHER_IDX = VM.IDX
				AND
					VI.VOUCHER_ISSUE_CODE = '".$voucher_issue_code."';
			";
			
			$db->query($update_voucher_issue_sql);
			
			$json_result['data'] = array(
				'update_cnt'		=>$db->affectedRows()
			);
		}
	}
}

?>