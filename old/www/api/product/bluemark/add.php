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

include_once(dir_f_api."/common.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
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

if(!isset($purchase_date) && intval($mall_type) != 1) {
	$json_result['code'] = 302;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0010', array());
	
	echo json_encode($json_result);
	exit;
}

if(isset($serial_code) && isset($purchase_mall)) {
	$db->begin_transaction();
	
	try {
		$bluemark_cnt = $db->count("BLUEMARK_INFO BI", "BI.SERIAL_CODE = ? AND BI.MEMBER_IDX = 0 AND BI.STATUS = FALSE AND BI.DEL_FLG = FALSE",array($serial_code));
		
		if ($bluemark_cnt > 0) {
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
								MEMBER_".$country." MB
						 WHERE
								MB.IDX = ?
					) AS TMP
				SET
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
			
			$db->query($update_bluemark_sql,array($member_idx,$serial_code));

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
						PURCHASE_MALL,
						PURCHASE_DATE,
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
						?						AS PURCHASE_MALL,
						?						AS PURCHASE_DATE,
						NOW()					AS REG_DATE
					FROM
						BLUEMARK_INFO BI
					WHERE 
						BI.SERIAL_CODE = ?
				";
				$db->query($insert_bluemark_log_sql,array($country,$ip,$purchase_mall,$purchase_date,$serial_code));
			}
		} else {
			$db->rollback();
			
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0071', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		$db->commit();
	} catch (mysqli_sql_exception $e) {
		$db->rollback();
		
		print_r($e);

		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0037', array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 302;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0039', array());
	
	echo json_encode($json_result);
	exit;
}

?>