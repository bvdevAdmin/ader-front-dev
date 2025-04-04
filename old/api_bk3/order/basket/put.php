<?php
/*
 +=============================================================================
 | 
 | 쇼핑백 화면 - 쇼핑백 상품 수량 변경 / 품절 상품 옵션 변경
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

include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/check.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_level = 0;
if (isset($_SESSION['LEVEL_IDX'])) {
	$member_level = $_SESSION['LEVEL_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$option_idx = 0;
if (isset($_POST['option_idx'])) {
	$option_idx = $_POST['option_idx'];
}

if (!isset($country) && $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

/* 쇼핑백 상품 수량 변경 */
if (isset($basket_idx) && isset($stock_status)) {
	/* 1. 수량을 변경하려는 상품의 쇼핑백 존재 여부 체크 */
	$basket_cnt = $db->count("BASKET_INFO","COUNTRY = '".$country."' AND IDX = ".$basket_idx." AND MEMBER_IDX = ".$member_idx." AND DEL_FLG = FALSE ");
	if ($basket_cnt == 0) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0049', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	if ($stock_status == "STIN" && $basket_idx > 0 && $basket_qty > 0) {
		/* 3. [재고있음] 상태의 쇼핑백 상품 수량 변경 */
		
		/* 3-1. 선택한 상품/옵션의 구매 가능 회원등급 체크 */
		$check_result_level = checkProductLevel($db,$member_level,"BSK",$basket_idx);
		if ($check_result_level['result'] == false) {
			$json_result['code'] = 401;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0034', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		$option_idx = 0;
		$limit_qty = 0;
		
		/* 3-2. 수량을 변경하려는 쇼핑백 상품의 재고정보 조회 */
		$check_result = checkLimitQty($db,$basket_idx,0,0);
		if ($check_result != null) {
			$option_idx = $check_result['option_idx'];
			$limit_qty = $check_result['limit_qty'];
		}
		
		/* WCC 잔여재고 0 이상 && WCC 잔여재고가 변경하려는 수량보다 많은 경우 */
		if ($limit_qty > 0 && $limit_qty >= $basket_qty) {
			
			/* 3-3. 수량을 변경하려는 쇼핑백 상품의 구매 제한 수량 체크 */
			$check_result_qty = checkQtyLimit($db,$country,$member_idx,"BSK",$basket_idx,$option_idx,$basket_qty);
			if ($check_result_qty['result'] == false) {
				$json_result['code'] = 401;
				$json_result['msg'] = $check_result_qty['msg'];
				
				echo json_encode($json_result);
				exit;
			}
			
			/* 3-4. 쇼핑백 상품 수량 변경처리 */
			$db->update(
				"BASKET_INFO",
				array(
					'PRODUCT_QTY'	=>$basket_qty,
					'UPDATER'		=>$member_id,
					'UPDATE_DATE'	=>NOW()
				),
				"
					IDX = ".$basket_idx." AND
					COUNTRY = '".$country."' AND
					MEMBER_IDX = ".$member_idx."
				"
			);
			
			/* 3-5. 현재 쇼핑백에 담겨있는 상품 수량 반환처리 */
			$json_result['data'] = array(
				'basket_cnt'		=>getBasketCnt($db,$country,$member_idx)
			);
		} else {
			$json_result['code'] = 303;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0073', array());
			
			echo json_encode($json_result);
			exit;
		}
	} else if ($stock_status == "STSO" && $product_idx > 0 && $option_idx > 0) {
		/* 4. [재고없음] 상태의 쇼핑백 상품 수량 변경 */
		
		/* 4-1. 선택한 상품/옵션의 구매 가능 회원등급 체크 */
		$check_result_level = checkProductLevel($db,$member_level,"PRD",$product_idx);
		if ($check_result_level['result'] == false) {
			$json_result['code'] = 401;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0034', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		/* 4-2. 현재 쇼핑백 내 동일상품 여부 체크 */
		$cnt_dup = $db->count(
			"BASKET_INFO",
			"
				COUNTRY = '".$country."' AND
				MEMBER_IDX = ".$member_idx." AND
				PRODUCT_IDX = ".$product_idx." AND
				OPTION_IDX = ".$option_idx."
			"
		);
		
		$product_qty = 0;
		if ($cnt_dup > 0) {
			$product_qty = getDuplicateBasketInfo($db,$country,$member_idx,$product_idx,$option_idx);
		}
		
		/* 4-2. 수량을 변경하려는 쇼핑백 상품의 재고정보 조회 */
		$check_result = checkLimitQty($db,0,$product_idx,$option_idx);
		if ($check_result != null) {
			$option_idx = $check_result['option_idx'];
			$limit_qty = $check_result['limit_qty'];
			
			if ($cnt_dup > 0 && $product_qty > 0) {
				$limit_qty -= $product_qty;
			}
		}
		
		if ($limit_qty > 0) {
			/* 4-3. 수량을 변경하려는 쇼핑백 상품의 구매 제한 수량 체크 */
			$check_result_qty = checkQtyLimit($db,$country,$member_idx,"PRD",$product_idx,$option_idx,1);
			if ($check_result_qty['result'] == false) {
				$json_result['code'] = 401;
				$json_result['msg'] = $check_result_qty['msg'];
				
				echo json_encode($json_result);
				exit;
			}
			
			$db->begin_transaction();
			
			try {
				/* 4-3. 기존 쇼핑몰 상품 논리 삭제처리 */
				$db->update(
					"BASKET_INFO",
					array(
						'DEL_FLG'		=>1,
						'UPDATE_DATE'	=>NOW(),
						'UPDATER'		=>$member_id
					),
					"
						IDX = ".$basket_idx." AND
						COUNTRY = '".$country."' AND
						MEMBER_IDX = ".$member_idx."
					"
				);
				
				$db_result = $db->affectedRows();
				
				if ($db_result > 0) {
					if ($cnt_dup > 0) {
						$update_basket_info_sql = "
							UPDATE
								BASKET_INFO
							SET
								PRODUCT_QTY = PRODUCT_QTY + 1
							WHERE
								COUNTRY = '".$country."' AND
								MEMBER_IDX = ".$member_idx." AND
								PRODUCT_IDX = ".$product_idx." AND
								OPTION_IDX = ".$option_idx."
						";
						
						$db->query($update_basket_info_sql);
					} else {
						/* 4-4. 변경된 조건의 신규 쇼핑몰 상품 등록처리 */
						$insert_basket_info_sql = "
							INSERT INTO
								BASKET_INFO
							(
								COUNTRY,
								MEMBER_IDX,
								MEMBER_ID,
								PRODUCT_IDX,
								PRODUCT_CODE,
								PRODUCT_NAME,
								OPTION_IDX,
								BARCODE,
								OPTION_NAME,
								PRODUCT_QTY,
								CREATER,
								UPDATER
							)
							SELECT
								'".$country."'		AS COUNTRY,
								".$member_idx."		AS MEMBER_IDX,
								'".$member_id."'	AS MEMBER_ID,
								PR.IDX				AS PRODUCT_IDX,
								PR.PRODUCT_CODE		AS PRODUCT_CODE,
								PR.PRODUCT_NAME		AS PRODUCT_NAME,
								OO.IDX				AS OPTION_IDX,	
								OO.BARCODE			AS BARCODE,
								OO.OPTION_NAME		AS OPTION_NAME,
								1					AS PRODUCT_QTY,
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
						
						$db->query($insert_basket_info_sql);
					}
				}
				
				$db->commit();
				
				$json_result['code'] = 200;
				
				echo json_encode($json_result);
				exit;
			} catch(mysqli_sql_exception $exception){
				$db->rollback();
				
				$json_result['code'] = 304;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0048', array());
				
				echo json_encode($json_result);
				exit;
			}
		} else {
			$json_result['code'] = 303;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0073', array());
			
			echo json_encode($json_result);
			exit;
		}
	} else {
		$json_result['code'] = 306;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0033', array());
		
		echo json_encode($json_result);
		exit;
	}
}

function getDuplicateBasketInfo($db,$country,$member_idx,$product_idx,$option_idx) {
	$product_qty = 0;
	
	$select_duplicate_basket_info_sql = "
		SELECT
			SUM(BI.PRODUCT_QTY)	AS PRODUCT_QTY
		FROM
			BASKET_INFO BI
		WHERE
			BI.COUNTRY = '".$country."' AND
			BI.MEMBER_IDX = ".$member_idx." AND
			BI.PRODUCT_IDX = ".$product_idx." AND
			BI.OPTION_IDX = ".$option_idx." AND
			BI.DEL_FLG = FALSE
	";
	
	$db->query($select_duplicate_basket_info_sql);
	
	foreach($db->fetch() as $data) {
		$product_qty = $data['PRODUCT_QTY'];
	}
	
	return $product_qty;
}

function checkLimitQty($db,$basket_idx,$product_idx,$option_idx) {
	$check_result = null;
	
	$where = "";
	if ($basket_idx > 0) {
		/* 재고있음 상태의 쇼핑백 상품 구매가능수량 체크 */
		$where = "
			V_ST.OPTION_IDX = (
				SELECT
					DISTINCT S_BI.OPTION_IDX
				FROM
					BASKET_INFO S_BI
				WHERE
					S_BI.IDX = ".$basket_idx." AND
					S_BI.DEL_FLG = FALSE
			)
		";
	} else if ($product_idx > 0 && $option_idx > 0) {
		/* 재고없음 상태의 쇼핑백 상품 구매가능수량 체크 */
		$where = "
			V_ST.BARCODE = (
				SELECT
					DISTINCT S_BI.BARCODE
				FROM
					BASKET_INFO S_BI
				WHERE
					S_BI.PRODUCT_IDX = ".$product_idx." AND
					S_BI.OPTION_IDX = ".$option_idx." AND
					S_BI.DEL_FLG = FALSE
			)
		";
	}
	
	$option_idx = 0;
	$limit_qty = 0;
	
	$select_basket_stock_sql = "
		SELECT
			V_ST.OPTION_IDX		AS OPTION_IDX,
			IFNULL(
				V_ST.PURCHASEABLE_QTY,0
			)					AS PURCHASEABLE_QTY
		FROM
			V_STOCK V_ST
		WHERE
			".$where."
	";
	
	$db->query($select_basket_stock_sql);
	
	foreach($db->fetch() as $data) {
		$option_idx = $data['OPTION_IDX'];
		$limit_qty = $data['PURCHASEABLE_QTY'];
		
		$check_result = array(
			'option_idx'	=>$option_idx,
			'limit_qty'		=>$limit_qty
		);
	}
	
	return $check_result;
}

?>