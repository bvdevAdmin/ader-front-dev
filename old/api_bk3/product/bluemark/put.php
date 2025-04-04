<?php
/*
 +=============================================================================
 | 
 | 마이페이지 블루마크 - 블루마크 인증
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.09
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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

$ip = '0.0.0.0';
if (isset($_SERVER['REMOTE_ADDR'])) {
	$ip = $_SERVER['REMOTE_ADDR'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($country) && $member_idx > 0 && isset($handover_id) && isset($bluemark_idx)) {
	$db->begin_transaction();
	
	try {
		$bluemark_cnt = $db->count("BLUEMARK_INFO BI", "BI.IDX = ".$bluemark_idx." AND BI.MEMBER_IDX = '".$member_idx."' AND BI.STATUS = TRUE AND BI.DEL_FLG = FALSE");
		$handover_cnt = $db->count("MEMBER_".$country." MB", "MB.MEMBER_ID = '".$handover_id."'");
		
		if ($bluemark_cnt > 0 && $handover_cnt > 0) {
			$update_bluemark_info_sql = "
				UPDATE
					BLUEMARK_INFO,
					(
						SELECT
							MB.IDX				AS TMP_MEMBER_IDX,
							MB.MEMBER_ID		AS TMP_MEMBER_ID,
							MB.MEMBER_NAME		AS TMP_MEMBER_NAME,
							MB.TEL_MOBILE		AS TMP_TEL_MOBILE,
							MB.MEMBER_ID		AS TMP_EMAIL,
							TRUE				AS TMP_STATUS,
							NOW()				AS TMP_UPDATE_DATE
						FROM
							MEMBER_".$country." MB
						WHERE
							MB.MEMBER_ID = '".$handover_id."'
					) AS TMP
				SET
					MEMBER_IDX		= TMP.TMP_MEMBER_IDX,
					MEMBER_ID		= TMP.TMP_MEMBER_ID,
					MEMBER_NAME		= TMP.TMP_MEMBER_NAME,
					TEL_MOBILE		= TMP.TMP_TEL_MOBILE,
					EMAIL			= TMP.TMP_EMAIL,
					STATUS			= TMP.TMP_STATUS,
					UPDATE_DATE		= NOW(),
					UPDATER			= '".$member_id."'
				WHERE
					IDX = ".$bluemark_idx."
			";
			
			$db->query($update_bluemark_info_sql);

			$db_result = $db->affectedRows();
			
			if ($db_result > 0) {
				$insert_bluemark_log_sql = "
					INSERT INTO
						BLUEMARK_LOG
					(
						COUNTRY,
						BLUEMARK_IDX,
						MEMBER_IDX,
						MEMBER_ID,
						MEMBER_NAME,
						IP,
						STATUS,
						PURCHASE_MALL,
						PURCHASE_DATE,
						REG_DATE
					)
					SELECT
						'".$country."'		AS COUNTRY,
						BI.IDX				AS BLUEMARK_IDX,
						BI.MEMBER_IDX		AS MEMBER_IDX,
						BI.MEMBER_ID		AS MEMBER_ID,
						BI.MEMBER_NAME		AS MEMBER_NAME,
						'".$ip."'			AS IP,	
						'양도'				AS STATUS,
						BL.PURCHASE_MALL	AS PURCHASE_MALL,
						BL.PURCHASE_DATE	AS PURCHASE_DATE,
						NOW()				AS REG_DATE
					FROM
						BLUEMARK_INFO BI
						LEFT JOIN BLUEMARK_LOG BL ON
						BI.IDX = BL.BLUEMARK_IDX
					WHERE 
						BI.IDX = ".$bluemark_idx." AND
						BL.ACTIVE_FLG = TRUE
				";
				
				$db->query($insert_bluemark_log_sql);
				
				$log_idx = $db->last_id();

				if (!empty($log_idx)) {
					$db->update(
						"BLUEMARK_LOG",
						array(
							'ACTIVE_FLG'		=>0
						),
						"
							IDX != ".$log_idx." AND
							BLUEMARK_IDX = ".$bluemark_idx."
						"
					);
				}
			}
		} else {
			if ($bluemark_cnt == 0) {
				$json_result['code'] = 401;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0052', array());
				
				echo json_encode($json_result);
				exit;
			} else if ($handover_cnt == 0) {
				$json_result['code'] = 401;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0053', array());
				
				echo json_encode($json_result);
				exit;
			}
		}

		$db->commit();
	} catch(mysqli_sql_exception $exception) {
		$db->rollback();
		print_r($exception);
		
		$json_result['code'] = 401;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0038', array());
		
		echo json_encode($json_result);
		exit;
	}
}

?>