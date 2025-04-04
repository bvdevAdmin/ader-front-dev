<?php
/*
 +=============================================================================
 | 
 | 마이페이지 블루마크 - 블루마크 인증정보 수정
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

if (isset($_SERVER['HTTP_COUNTRY']) && $_SESSION['MEMBER_IDX'] > 0 && $action_type != null) {
	$db->begin_transaction();
	
	try {
		switch ($action_type) {
			case "CANCEL" :
				if ($bluemark_idx != null && $log_idx != null) {
					/* 블루마크 인증정보 체크 */
					 $check_bluemark = checkBluemark($db,"LOG",$log_idx);
					if ($check_bluemark != false) {
						$db->update(
							"BLUEMARK_LOG",
							array(
								'ACTIVE_FLG'	=>0
							),
							"IDX = ?",
							array($log_idx)
						);
						
						$db->update(
							"BLUEMARK_INFO",
							array(
								'COUNTRY'		=>null,
								'MEMBER_IDX'	=>0,
								'MEMBER_ID'		=>null,
								'MEMBER_NAME'	=>null,
								'TEL_MOBILE'	=>null,
								'EMAIL'			=>null,
								'STATUS'		=>0,
								
								'UPDATE_DATE'	=>NOW(),
								'UPDATER'		=>$_SESSION['MEMBER_ID']
							),
							"IDX = ?",
							array($bluemark_idx)
						);
						
						$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_INF_0022',array());
					}
				}
				
				break;
			
			case "TRANSFER" :
				if ($transfer_type != null) {
					/* 블루마크 인증정보 체크 */
					$check_bluemark = checkBluemark($db,"INFO",$bluemark_idx);
					if ($check_bluemark != false) {
						/* 블루마크 양도회원 체크 */
						$check_transfer = false;
						
						if ($transfer_type == "MAIL") {
							$check_transfer = checkTransfer($db,$transfer_type,$param_country,$transfer_id);
						} else if ($transfer_type == "TEL") {
							$check_transfer = checkTransfer($db,$transfer_type,$param_country,$tel_mobile);
						}

						$code	= "";
						$msg	= "";

						if ($check_bluemark != false && $check_transfer != false) {
							$param_info = array(
								'bluemark_idx'		=>$bluemark_idx,
								'transfer_type'		=>$transfer_type,
								'member_id'			=>$_SESSION['MEMBER_ID'],
								'param_country'		=>$param_country,
								'transfer_id'		=>$transfer_id,
								'tel_mobile'		=>$tel_mobile
							);
							
							putBluemark_info($db,$param_info);
							
							$db_result = $db->affectedRows();
							if ($db_result > 0) {
								addBluemark_log($db,$param_country,$ip,$bluemark_idx);
								
								$log_idx = $db->last_id();
								if (!empty($log_idx)) {
									$db->update(
										"BLUEMARK_LOG",
										array(
											'ACTIVE_FLG'		=>0
										),
										"IDX != ? AND BLUEMARK_IDX = ?",
										array($log_idx,$bluemark_idx)
									);
								}
								
								$code	= 200;
								$msg	= getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_INF_0023',array());
							}
						} else {
							if ($check_bluemark != true) {
								$code	= 301;
								$msg	= getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0052',array());
								
								echo json_encode($json_result);
								exit;
							} else if ($check_transfer != true) {
								$code	= 302;
								$msg	= getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0053',array());
							}
						}

						$db->commit();

						$json_result = array(
							'code'		=>$code,
							'msg'		=>$msg
						);

						echo json_encode($json_result);
						exit;
					}
				}
				
				break;
		}

		$db->commit();
	} catch(mysqli_sql_exception $exception) {
		$db->rollback();
		
		print_r($exception);
		
		$json_result['code'] = 401;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0038',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

function checkBluemark($db,$check_type,$param_idx) {
	$check_result = false;
	
	$table = "";
	$where = "";
	
	if ($check_type == "LOG") {
		$table = "BLUEMARK_LOG";
		
		$where = "
			IDX = ? AND
			COUNTRY = ? AND
			MEMBER_IDX = ?
		";
	} else if ($check_type == "INFO") {
		$table = "BLUEMARK_INFO";
		
		$where = "
			IDX = ? AND
			COUNTRY = ? AND
			MEMBER_IDX = ? AND

			STATUS = TRUE AND
			DEL_FLG = FALSE
		";
	}
	
	/* 블루마크 인증정보 체크 */
	$cnt_bluemark = $db->count(
		$table,
		$where,
		array($param_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])
	);
	
	if ($cnt_bluemark > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

function checkTransfer($db,$check_type,$param_country,$param) {
	$check_result = false;
	
	$where = " MEMBER_ID != ? ";
	$param_bind = array($_SESSION['MEMBER_ID']);
	
	if ($check_type == "MAIL") {
		$where .= " AND (MEMBER_ID = ?) ";
	} else if ($check_type == "TEL") {
		$param = str_replace("-","",$param);
		
		$where .= " AND (REPLACE(TEL_MOBILE,'-','') = ?) ";
	}
	
	array_push($param_bind,$param);
	
	$cnt_member = $db->count("MEMBER",$where,$param_bind);
	if ($cnt_member > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

function putBluemark_info($db,$param) {
	$where = "";
	$param_bind = array();
	
	if ($param['transfer_type'] == "MAIL") {
		$where .= " MB.MEMBER_ID = ? ";
		
		array_push($param_bind,$param['transfer_id']);
	} else if ($param['transfer_type'] == "TEL") {
		$where .= " REPLACE(MB.TEL_MOBILE,'-','') = ?";
		
		array_push($param_bind,str_replace("-","",$param['tel_mobile']));
	}
	
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
					MEMBER MB
				WHERE
					".$where."
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
			UPDATER			= ?
		WHERE
			IDX = ?
	";
	
	$db->query(
		$update_bluemark_info_sql,
		array_merge($param_bind,array($_SERVER['HTTP_COUNTRY'],$param['member_id'],$param['bluemark_idx']))
	);
}

function addBluemark_log($db,$param_country,$ip,$bluemark_idx) {
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
			STORE_NO,
			PURCHASE_MALL,
			PURCHASE_DATE,
			REG_DATE
		)
		SELECT
			?					AS COUNTRY,
			BI.IDX				AS BLUEMARK_IDX,
			BI.MEMBER_IDX		AS MEMBER_IDX,
			BI.MEMBER_ID		AS MEMBER_ID,
			BI.MEMBER_NAME		AS MEMBER_NAME,
			?					AS IP,	
			'양도'				AS STATUS,
			BL.STORE_NO			AS STORE_NO,
			BL.PURCHASE_MALL	AS PURCHASE_MALL,
			BL.PURCHASE_DATE	AS PURCHASE_DATE,
			NOW()				AS REG_DATE
		FROM
			BLUEMARK_INFO BI

			LEFT JOIN BLUEMARK_LOG BL ON
			BI.IDX = BL.BLUEMARK_IDX
		WHERE 
			BI.IDX = ? AND
			BL.ACTIVE_FLG = TRUE
	";
	
	$db->query($insert_bluemark_log_sql,array($param_country,$ip,$bluemark_idx));
}

?>