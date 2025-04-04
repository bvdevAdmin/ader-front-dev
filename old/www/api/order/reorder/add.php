<?php
/*
 +=============================================================================
 | 
 | 쇼핑백 화면 - 재입고 알림 상품 추가
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.17
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

$member_id = null;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

/* 상품 상세 페이지 / 위시리스트 페이지 재입고 알림 상품 추가 */
if ($add_type == "product" || $add_type == "wish") {
	if ($product_type != null && $product_idx > 0 && is_array($option_info) && count($option_info) > 0) {
		$reorder_cnt = getReorderCnt($db,$country,$member_idx,$add_type,$product_type,$product_idx,$option_info);

		if ($reorder_cnt > 0) {
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0066', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		$insert_column = "";
		$where = "";
		
		if ($product_type == "B") {
			$insert_column = "
				OO.IDX				AS OPTION_IDX,
				OO.BARCODE			AS BARCODE,
				OO.OPTION_NAME		AS OPTION_NAME,
			";
			
			$where = "AND OO.IDX = ".$option_info[0];
		} else if ($product_type == "S") {
			$insert_column = "
				0				AS OPTION_IDX,
				NULL			AS BARCODE,
				NULL			AS OPTION_NAME,
			";
		}
		
		$insert_product_reorder_sql = "
			INSERT INTO
				PRODUCT_REORDER
			(
				COUNTRY,
				MEMBER_IDX,
				MEMBER_ID,
				PRODUCT_IDX,
				PRODUCT_TYPE,
				PRODUCT_CODE,
				PRODUCT_NAME,
				OPTION_IDX,
				BARCODE,
				OPTION_NAME,
				CREATER,
				UPDATER
			)
			SELECT
				'".$country."'		AS COUNTRY,
				".$member_idx."		AS MEMBER_IDX,
				'".$member_id."'	AS MEMBER_ID,
				PR.IDX				AS PRODUCT_IDX,
				PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
				PR.PRODUCT_CODE		AS PRODUCT_CODE,
				PR.PRODUCT_NAME		AS PRODUCT_NAME,
				
				".$insert_column."
				
				'".$member_id."'	AS CREATER,
				'".$member_id."'	AS UPDATER
			FROM
				SHOP_PRODUCT PR
				LEFT JOIN ORDERSHEET_OPTION OO ON
				PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
			WHERE
				PR.IDX = ".$product_idx."
				".$where."
		";
		
		$db->query($insert_product_reorder_sql);
		
		if ($product_type == "S") {
			$reorder_idx = $db->last_id();
			
			if (!empty($reorder_idx)) {
				for($i=0; $i<count($option_info); $i++) {
					$insert_set_product_reorder_sql = "
						INSERT INTO
							PRODUCT_REORDER
						(
							COUNTRY,
							MEMBER_IDX,
							MEMBER_ID,
							PRODUCT_IDX,
							PRODUCT_TYPE,
							PARENT_IDX,
							PRODUCT_CODE,
							PRODUCT_NAME,
							OPTION_IDX,
							BARCODE,
							OPTION_NAME,
							CREATER,
							UPDATER
						)
						SELECT
							'".$country."'		AS COUNTRY,
							".$member_idx."		AS MEMBER_IDX,
							'".$member_id."'	AS MEMBER_ID,
							PR.IDX				AS PRODUCT_IDX,
							PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
							".$reorder_idx."	AS PARENT_IDX,
							PR.PRODUCT_CODE		AS PRODUCT_CODE,
							PR.PRODUCT_NAME		AS PRODUCT_NAME,
							OO.IDX				AS OPTION_IDX,
							OO.BARCODE			AS BARCODE,
							OO.OPTION_NAME		AS OPTION_NAME,
							'".$member_id."'	AS CREATER,
							'".$member_id."'	AS UPDATER
						FROM
							SHOP_PRODUCT PR
							LEFT JOIN ORDERSHEET_OPTION OO ON
							PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
						WHERE
							PR.IDX = ".$option_info[$i]['product_idx']." AND
							OO.IDX = ".$option_info[$i]['option_idx']."
					";
					
					$db->query($insert_set_product_reorder_sql);
				}				
			}
		}
	}
}

/* 쇼핑백 페이지 재입고 알림 상품 추가 */
if ($add_type == "basket") {
	if ($basket_idx > 0 && $product_idx > 0 && $option_idx > 0) {
		
		$db->begin_transaction();
		
		try {
			$insert_product_reorder_sql = "
				INSERT INTO
					PRODUCT_REORDER
				(
					COUNTRY,
					MEMBER_IDX,
					MEMBER_ID,
					PRODUCT_IDX,
					PRODUCT_TYPE,
					PRODUCT_CODE,
					PRODUCT_NAME,
					OPTION_IDX,
					BARCODE,
					OPTION_NAME,
					CREATER,
					UPDATER
				)
				SELECT
					'".$country."'		AS COUNTRY,
					".$member_idx."		AS MEMBER_IDX,
					'".$member_id."'	AS MEMBER_ID,
					PR.IDX				AS PRODUCT_IDX,
					PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
					PR.PRODUCT_CODE		AS PRODUCT_CODE,
					PR.PRODUCT_NAME		AS PRODUCT_NAME,
					OO.IDX				AS OPTION_IDX,
					OO.BARCODE			AS BARCODE,
					OO.OPTION_NAME		AS OPTION_NAME,
					'".$member_id."'	AS CREATER,
					'".$member_id."'	AS UPDATER
				FROM
					SHOP_PRODUCT PR
					LEFT JOIN ORDERSHEET_OPTION OO ON
					PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
				WHERE
					PR.IDX = ".$product_idx." AND
					OO.IDX = ".$option_idx."
			";
			
			$db->query($insert_product_reorder_sql);
			
			$reorder_idx = $db->last_id();
			
			if (!empty($reorder_idx)) {
				$update_basket_sql = "
					UPDATE
						BASKET_INFO
					SET
						REORDER_FLG = TRUE,
						UPDATE_DATE = NOW(),
						UPDATER = '".$member_id."'
					WHERE
						IDX = ".$basket_idx." AND
						MEMBER_IDX = ".$member_idx."
				";
				
				$db->query($update_basket_sql);
				
				$db->commit();
				
				$json_result['code'] = 200;
				
				echo json_encode($json_result);
				exit;
			}
		} catch(mysqli_sql_exception $exception){
			$db->rollback();
			
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0074', array());
			$json_result['exception_msg'] = $exception;			
			
			echo json_encode($json_result);
			exit;
		}
	}
}

function getReorderCnt($db,$country,$member_idx,$add_type,$product_type,$product_idx,$option_info) {
	$reorder_cnt = 0;
	
	if ($add_type == "product" || $add_type == "wish") {
		if ($product_type == "B") {
			$reorder_cnt = $db->count("PRODUCT_REORDER","DEL_FLG = FALSE AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND OPTION_IDX = ".$option_info[0]);
		} else if ($product_type == "S") {
			for ($i=0; $i<count($option_info); $i++) {
				$tmp_reorder_cnt = $db->count("PRODUCT_REORDER","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$option_info[$i]['product_idx']." AND OPTION_IDX = ".$option_info[$i]['option_idx']);
				$reorder_cnt += $tmp_reorder_cnt;
			}
		}
	}	
	
	return $reorder_cnt;
}
?>