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

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$ip = '0.0.0.0';
if (isset($_SERVER['REMOTE_ADDR'])) {
	$ip = $_SERVER['REMOTE_ADDR'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($store_no) && isset($bluemark) && strlen($bluemark) > 0) {
		$db->begin_transaction();
		
		try {
			$cnt_bluemark = $db->count(
				"BLUEMARK_INFO BI",
				"
					BI.SERIAL_CODE = ? AND
					BI.COUNTRY IS NULL AND
					BI.MEMBER_IDX = 0 AND
					BI.STATUS = FALSE AND
					BI.DEL_FLG = FALSE
				",
				array($bluemark)
			);
			
			if ($cnt_bluemark > 0) {
				$update_bluemark_sql = "
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
								NOW()				AS TMP_UPDATE_DATE,
								MB.MEMBER_ID		AS TMP_UPDATER
							 FROM
								MEMBER MB
							 WHERE
									MB.IDX = ?
						) AS TMP
					SET
						COUNTRY			= ?,
						MEMBER_IDX		= TMP.TMP_MEMBER_IDX,
						MEMBER_ID		= TMP.TMP_MEMBER_ID,
						MEMBER_NAME		= TMP.TMP_MEMBER_NAME,
						TEL_MOBILE		= TMP.TMP_TEL_MOBILE,
						EMAIL			= TMP.TMP_EMAIL,
						STATUS			= TMP.TMP_STATUS,
						UPDATE_DATE		= NOW(),
						UPDATER			= TMP_UPDATER
					WHERE
						SERIAL_CODE = ?
				";
				
				$db->query($update_bluemark_sql,array($_SESSION['MEMBER_IDX'],$_SERVER['HTTP_COUNTRY'],$bluemark));

				$db_result = $db->affectedRows();
				if ($db_result > 0) {
					$insert_bluemark_log_sql = "
						INSERT
							BLUEMARK_LOG
						(
							COUNTRY,
							BLUEMARK_IDX,
							MEMBER_IDX,
							MEMBER_ID,
							MEMBER_NAME,
							IP,
							STATUS,
							STORE_NO,
							PURCHASE_MALL,
							REG_DATE
						)
						SELECT
							?				  		AS COUNTRY,
							IDX						AS BLUEMARK_IDX,
							MEMBER_IDX				AS MEMBER_IDX,
							MEMBER_ID				AS MEMBER_ID,
							MEMBER_NAME				AS MEMBER_NAME,
							?						AS IP,
							'신규인증'					AS STATUS,
							?						AS STORE_NO,
							(
								SELECT
									S_PM.MALL_NAME
								FROM
									PURCHASE_MALL S_PM
								WHERE
									S_PM.IDX = ? AND
									S_PM.COUNTRY = ?
							)						AS PURCHASE_MALL,
							NOW()					AS REG_DATE
						FROM
							BLUEMARK_INFO BI
						WHERE 
							BI.SERIAL_CODE = ?
					";
					
					$db->query($insert_bluemark_log_sql,array($_SERVER['HTTP_COUNTRY'],$ip,$store_no,$store_no,$_SERVER['HTTP_COUNTRY'],$bluemark));
					
					$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_F_INF_0021', array());
				}
			} else {
				$db->rollback();
				
				$json_result['code'] = 303;
				$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0071', array());
				
				echo json_encode($json_result);
				exit;
			}
			
			$db->commit();
		} catch (mysqli_sql_exception $e) {
			$db->rollback();
			
			print_r($e);

			$json_result['code'] = 302;
			$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0031', array());
			
			echo json_encode($json_result);
			exit;
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0039', array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

?>