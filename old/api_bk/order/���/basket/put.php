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

include_once("/var/www/www/api/common/common.php");
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

$stock_status = null;
if (isset($_POST['stock_status'])) {
	$stock_status	= $_POST['stock_status'];
}

$basket_idx = 0;
if (isset($_POST['basket_idx'])) {
	$basket_idx		= $_POST['basket_idx'];
}

$basket_qty = 0;
if (isset($_POST['basket_qty'])) {
	$basket_qty	= $_POST['basket_qty'];
}

$product_idx = 0;
if (isset($_POST['product_idx'])) {
	$product_idx = $_POST['product_idx'];
}

$option_idx = 0;
if (isset($_POST['option_idx'])) {
	$option_idx	= $_POST['option_idx'];
}

if ($member_idx == 0 || $country == null) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	return $json_result;
}

if ($basket_idx != null && $stock_status != null) {
	$basket_cnt = $db->count("BASKET_INFO","IDX = ".$basket_idx." AND MEMBER_IDX = ".$member_idx." AND DEL_FLG = FALSE ");
		
	if ($basket_cnt == 0) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0049', array());
		
		return $json_result;
	}
	
	//선택 한 상품의 재고가 남아있는지 확인 => 선택한 상품/옵션의 재고 = 전체 상품 재고 - 주문 상품 재고
	$stock_result = checkProductStockQty($db,$product_idx,$option_idx);
	
	//선택한 상품/옵션이 구매 가능한 상태인지 체크
	$check_result_level = checkProductLevel($db,$member_level,"BSK",$basket_idx);
	if ($check_result_level['result'] == false) {
		$json_result['code'] = 401;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0034', array());
		
		return $json_result;
	}
	
	if ($stock_status == "STIN" && $basket_idx > 0 && $basket_qty > 0) {
		$select_stock_sql = "
			SELECT
				BI.OPTION_IDX		AS OPTION_IDX,
				(
					SELECT
						IFNULL(SUM(STOCK_QTY),0)
					FROM
						PRODUCT_STOCK S_PS
					WHERE
						S_PS.PRODUCT_IDX = BI.PRODUCT_IDX AND
						S_PS.OPTION_IDX = BI.OPTION_IDX AND
						S_PS.STOCK_DATE <= NOW()
				)					AS STOCK_QTY,
				(
					SELECT
						IFNULL(SUM(OP.PRODUCT_QTY),0)
					FROM
						ORDER_PRODUCT OP
					WHERE
						OP.PRODUCT_IDX = BI.PRODUCT_IDX AND
						OP.OPTION_IDX = BI.OPTION_IDX AND
						OP.ORDER_STATUS IN ('PCP','PPR','DPR','DPG','DCP')
				)					AS ORDER_QTY
			FROM
				BASKET_INFO BI
			WHERE
				BI.IDX = ".$basket_idx."
		";
		
		$db->query($select_stock_sql);
		
		$option_idx = 0;
		$product_qty = 0;
		foreach($db->fetch() as $stock_data) {
			$option_idx = $stock_data['OPTION_IDX'];
			$product_qty = intval($stock_data['STOCK_QTY']) - intval($stock_data['ORDER_QTY']);
		}
		
		if ($product_qty > 0 && $product_qty >= $basket_qty) {
			$check_result_qty = checkQtyLimit($db,$country,$member_idx,"BSK",$basket_idx,$option_idx,$basket_qty);
			if ($check_result_qty['result'] == false) {
				$json_result['code'] = 401;
				$json_result['msg'] = $check_result_qty['msg'];
				
				return $json_result;
			}
			
			$update_basket_sql="
				UPDATE
					BASKET_INFO BI
				SET
					BI.PRODUCT_QTY = ".$basket_qty.",
					BI.UPDATER = '".$member_id."',
					BI.UPDATE_DATE = NOW()
				WHERE
					BI.IDX = ".$basket_idx." AND
					BI.MEMBER_IDX = ".$member_idx."
			";
			
			$db->query($update_basket_sql);
			
			$json_result['data'] = array(
				'basket_cnt'		=>getBasketCnt($db,$country,$member_idx)
			);
		} else {
			$json_result['code'] = 303;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0073', array());
		}
	} else if ($stock_status == "STSO" && $product_idx > 0 && $option_idx > 0) {
		if ($stock_result == true) {
			$check_result_level = checkProductLevel($db,$member_level,"PRD",$product_idx);
			if ($check_result_level['result'] == false) {
				$json_result['code'] = 401;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0034', array());
				
				return $json_result;
			}
			
			$check_result_qty = checkQtyLimit($db,$country,$member_idx,"PRD",$product_idx,$option_idx,1);
			if ($check_result_qty['result'] == false) {
				$json_result['code'] = 401;
				$json_result['msg'] = $check_result_qty['msg'];
				
				return $json_result;
			}
			
			$db->begin_transaction();
			
			try {
				$delete_basket_sql = "
					UPDATE
						BASKET_INFO
					SET
						DEL_FLG = TRUE,
						UPDATE_DATE = NOW(),
						UPDATER = '".$member_id."'
					WHERE
						IDX = ".$basket_idx." AND
						MEMBER_IDX = ".$member_idx."
				";
				
				$db->query($delete_basket_sql);
				
				$db_result = $db->affectedRows();
				
				if ($db_result > 0) {
					$insert_basket_sql = "
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
					
					$db->query($insert_basket_sql);
				}
				
				$db->commit();
				
				$json_result['code'] = 200;
				
				return $json_result;
			} catch(mysqli_sql_exception $exception){
				$db->rollback();
				
				$json_result['code'] = 304;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0048', array());
				
				return $json_result;
			}
		} else {
			$json_result['code'] = 305;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0045', array());
			
			return $json_result;
		}
	} else {
		$json_result['code'] = 306;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0033', array());
		
		return $json_result;
	}
}
?>