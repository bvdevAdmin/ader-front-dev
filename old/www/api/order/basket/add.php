<?php
/*
 +=============================================================================
 | 
 | 쇼핑백 화면 - 쇼핑백 상품 등록
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.14
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include_once(dir_f_api."/common.php");
include_once(dir_f_api."/common/check.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
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

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = "MSG_B_ERR_0018";
	
	echo json_encode($json_result);
	exit;
}

/* 1. 상품 상세 페이지 쇼핑백 상품 등록처리 */
if ($add_type == "product" && isset($product_idx) && isset($option_info)) {
	
	$db->begin_transaction();
	
	try {
		/* 1-1. 일반 상품 쇼핑백 내 동일 상품 체크 */
		if ($product_type == "B") {
			$check_basket_result = checkProductDuplicate($db,"BSK",$country,$member_idx,$product_type,$product_idx,$option_info);
			if ($check_basket_result != true) {
				$json_result['code'] = 402;
				//$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0063', array());
				$json_result['msg'] = "MSG_B_ERR_0063";
				
				echo json_encode($json_result);
				exit;
			}
		}
		
		/* 1-2. 쇼핑백에 추가하려는 상품의 구매 회원 등급 제한 체크 */
		$check_result_level = checkProductLevel($db,$member_level,"PRD",$product_idx);
		if ($check_result_level['result'] != true) {
			$json_result['code'] = 403;
			//$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0098', array());
			$json_result['msg'] = "MSG_B_ERR_0098";
			
			echo json_encode($json_result);
			exit;
		}
		
		/* 1-3. 쇼핑백에 추가하려는 상품의 옵션별 구매 제한 수량 체크 */
		if ($product_type == "B") {
			for ($i=0; $i<count($option_info); $i++) {
				$check_result_qty = checkQtyLimit($db,$country,$member_idx,"PRD",$product_idx,$option_info[$i],1);
				if ($check_result_qty['result'] != true) {
					$json_result['code'] = 404;
					$json_result['msg'] = $check_result_qty['msg'];
					
					echo json_encode($json_result);
					exit;
				}
			}
		}
		
		/* 1-4. 쇼핑백에 추가하려는 상품의 ID당 구매제한 체크 */
		$check_result_id = checkIdReorder($db,$country,$member_idx,"PRD",$product_idx);
		if ($check_result_id['result'] != true) {
			$json_result['code'] = 405;
			//$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0004', array());
			$json_result['msg'] = "MSG_B_ERR_0004";
			
			echo json_encode($json_result);
			exit;
		}
		
		/* 1-5. 쇼핑백에 추가하려는 상품의 잔여재고 체크 */
		$stock_result = checkProductBasketStockQty($db,$product_idx,$product_type,$option_info);
		if ($stock_result != false) {
			/* 1-6. 쇼핑백 상품 추가 처리 */
			addProductBasketInfo($db,$country,$member_idx,$member_id,$product_type,$product_idx,$option_info);
		} else {
			$json_result['code'] = 406;
			//$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0044', array());
			$json_result['msg'] = "MSG_B_ERR_0044";
		}
		
		$db->commit();
		
		/* 1-7. 현재 쇼핑백 상품 수량 반환 */
		$json_result['data'] = array(
			'basket_cnt'		=>getBasketCnt($db,$country,$member_idx)
		);
	} catch(mysqli_sql_exception $exception){
		print_r($exception);
		
		$db->rollback();
		$json_result['code'] = 407;
		
		echo json_encode($json_result);
		exit;
	}
}

/* 2. 위시리스트 페이지 페이지 쇼핑백 상품 등록처리 */
if ($add_type == "wish" && count($wish_info) > 0) {
	$db->begin_transaction();
	
	try {
		/* 2-1. 해당 회원의 위시리스트중 선택 한 상품이 존재하는지 확인 */
		$check_wish_result = checkWishList($db,$country,$member_idx,$wish_info);
		if ($check_wish_result != true) {
			$json_result['code'] = 408;
			//$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0065', array());
			$json_result['msg'] = "MSG_B_ERR_0065";
			
			echo json_encode($json_result);
			exit;
		}
		
		$err_cnt = 0;
		for ($i=0; $i<count($wish_info); $i++) {
			$product_type = $wish_info[$i]['product_type'];
			$wish_idx = $wish_info[$i]['wish_idx'];
			
			$json_option_info = json_decode($wish_info[$i]['option_info'],true);
			$option_info = json_decode($json_option_info,true);
			
			/* 2-2. 일반 상품 쇼핑백 내 동일 상품 체크 */
			if ($product_type == "B") {
				$check_basket_result = checkProductDuplicate($db,"WSH",$country,$member_idx,$product_type,$wish_idx,$option_info);
				if ($check_basket_result != true) {
					$json_result['code'] = 409;
					$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0063', array());
					//$json_result['msg'] = "MSG_B_ERR_0063";
					
					return $check_basket_result;
				}
			}
			
			/* 2-3. 쇼핑백에 추가하려는 상품의 구매 회원 제한 레벨 체크 */
			$check_result_level = checkProductLevel($db,$member_level,"WSH",$wish_idx);
			if ($check_result_level['result'] != true) {
				$json_result['code'] = 410;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0034', array());
				//$json_result['msg'] = "MSG_B_ERR_0034";
				
				echo json_encode($json_result);
				exit;
			}
			
			/* 2-4. 쇼핑백에 추가하려는 상품의 옵션별 구매수량  제한 체크 */
			if ($product_type == "B") {
				for ($j=0; $j<count($option_info); $j++) {
					$check_result_qty = checkQtyLimit($db,$country,$member_idx,"WSH",$wish_idx,$option_info[$j],1);
					if ($check_result_qty['result'] != true) {
						$json_result['code'] = 411;
						$json_result['msg'] = $check_result_qty['msg'];
						
						echo json_encode($json_result);
						exit;
					}
				}
			}
			
			/* 2-5. 쇼핑백에 추가하려는 상품의 ID당 구매제한 체크 */
			$check_result_id = checkIdReorder($db,$country,$member_idx,"WSH",$wish_idx);
			if ($check_result_id['result'] != true) {
				$json_result['code'] = 412;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0004', array());
				//$json_result['msg'] = "MSG_B_ERR_0004";
				
				echo json_encode($json_result);
				exit;
			}
		}
		
		/* 쇼핑백에 담으려는 상품의 잔여재고 체크 */
		$stock_result = checkWishListStockQty($db,$wish_info);
		if ($stock_result != false) {
			addWishListBasketInfo($db,$country,$member_idx,$member_id,$wish_info);
		} else {
			$json_result['code'] = 413;
			//$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0044', array());
			$json_result['msg'] = "MSG_B_ERR_0044";
			
			echo json_encode($json_result);
			exit;
		}
		
		$db->commit();
		
		$json_result['data'] = array(
			'basket_cnt'		=>getBasketCnt($db,$country,$member_idx)
		);
	} catch(mysqli_sql_exception $exception){
		print_r($exception);
		
		$db->rollback();
		$json_result['code'] = 414;
		
		echo json_encode($json_result);
		exit;
	}
}

/* 쇼핑백 상품 중복 체크 */
function checkProductDuplicate($db,$check_type,$country,$member_idx,$product_type,$product_idx,$option_info) {
	$check_result = false;
	
	$basket_cnt = 0;
	for ($i=0; $i<count($option_info); $i++) {
		$product_where = "";
		if ($check_type == "WSH") {
			/* 위시리스트 페이지 쇼핑백 추가 시 체크조건 */
			$product_where = "
				AND BI.PRODUCT_IDX = (
					SELECT
						WL.PRODUCT_IDX
					FROM
						WHISH_LIST WL
					WHERE
						WL.IDX = ".$product_idx."
				)
			";
		} else {
			/* 상품 상세 페이지 쇼핑백 추가 시 체크조건 */
			$product_where = " AND BI.PRODUCT_IDX = ".$product_idx." ";
		}
		
		$option_where = " AND BI.OPTION_IDX = ".$option_info[$i]." ";
		
		$tmp_cnt = $db->count("BASKET_INFO BI","BI.COUNTRY = '".$country."' AND BI.MEMBER_IDX = ".$member_idx." AND BI.PARENT_IDX = 0 AND BI.DEL_FLG = FALSE ".$product_where.$option_where);
		$basket_cnt += $tmp_cnt;
	}
	
	if (!$basket_cnt > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

function checkWishList($db,$country,$member_idx,$wish_info) {
	$check_result = false;
	
	$err_cnt = 0;
	for ($i=0; $i<count($wish_info); $i++) {
		$wish_idx = $wish_info[$i]['wish_idx'];
		
		$wish_cnt = $db->count("WHISH_LIST","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND IDX = ".$wish_idx);
		
		if ($wish_cnt == 0) {
			$err_cnt++;
		}
	}
	
	if (!$err_cnt > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 상품 상세 페이지 쇼핑백 상품 추가 전 재고 체크 */
function checkProductBasketStockQty($db,$product_idx,$product_type,$option_info) {
	$check_result = false;
	
	$err_cnt = 0;
	for ($i=0; $i<count($option_info); $i++) {
		$stock_result = false;
		
		if ($product_type == "B") {
			$option_idx = $option_info[$i];
			
			$stock_result = checkPurchaseableQty($db,$product_idx,$option_idx,1);
		} else if ($product_type == "S") {
			$tmp_product_idx = $option_info[$i]['product_idx'];
			$tmp_option_idx = $option_info[$i]['option_idx'];
			
			$stock_result = checkPurchaseableQty($db,$tmp_product_idx,$tmp_option_idx,1);
		}
		
		if ($stock_result != true) {
			$err_cnt++;
		}
	}
	
	if (!$err_cnt > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 위시 리스트 페이지 쇼핑백 상품 추가 전 재고 체크 */
function checkWishListStockQty($db,$wish_info) {
	$check_result = false;
	
	$err_cnt = 0;
	for ($i=0; $i<count($wish_info); $i++) {
		$product_type = $wish_info[$i]['product_type'];
		$wish_idx = $wish_info[$i]['wish_idx'];
		
		$json_option_info = json_decode($wish_info[$i]['option_info'],true);
		$option_info = json_decode($json_option_info,true);
		
		if (!empty($wish_idx) && !empty($option_info) && count($option_info) > 0) {
			for ($j=0; $j<count($option_info); $j++) {
				
				$product_idx = 0;
				$option_idx = 0;
				
				if ($product_type == "B") {
					$select_wish_list_sql = "
						SELECT
							WL.PRODUCT_IDX	AS PRODUCT_IDX,
							WL.OPTION_IDX	AS OPTION_IDX
						FROM
							WHISH_LIST WL
						WHERE
							WL.IDX = ".$wish_idx."
					";
				} else if ($product_type == "S") {
					$tmp_product_idx = $option_info[$j]['product_idx'];
					$tmp_option_idx = $option_info[$j]['option_idx'];
					
					$select_wish_list_sql = "
						SELECT
							PR.IDX			AS PRODUCT_IDX,
							OO.IDX			AS OPTION_IDX
						FROM
							SHOP_PRODUCT PR
							LEFT JOIN ORDERSHEET_OPTION OO ON
							PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
						WHERE
							PR.IDX = ".$tmp_product_idx." AND
							OO.IDX = ".$tmp_option_idx."
					";
				}
				
				$db->query($select_wish_list_sql);
				
				$product_qty = 0;
				foreach($db->fetch() as $data_stock) {
					$product_idx = $data_stock['PRODUCT_IDX'];
					$option_idx = $data_stock['OPTION_IDX'];
				}
				
				$check_result = checkPurchaseableQty($db,$product_idx,$option_idx,1);
				
				if ($check_result != true) {
					$err_cnt++;
				}
			}
		}
	}
	
	if (!$err_cnt > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 상품 상세 페이지 쇼핑백 삼품 추가처리 */
function addProductBasketInfo($db,$country,$member_idx,$member_id,$product_type,$product_idx,$option_info) {
	if ($product_type == "B") {
		/* 일반 상품 쇼핑백 추가 처리 */
		for ($i=0; $i<count($option_info); $i++) {
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
					OO.IDX = ".$option_info[$i]."
			";
			
			$db->query($insert_basket_info_sql);
		}
	} else if ($product_type == "S") {
		/* 세트 상품 쇼핑백 추가 처리 */
		
		/* 부모 세트 상품 추가 처리 */
		$insert_basket_info_sql = "
			INSERT INTO
				BASKET_INFO
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
				PRODUCT_QTY,
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
				0					AS OPTION_IDX,
				PR.PRODUCT_CODE		AS BARCODE,
				'Set'				AS OPTION_NAME,
				1					AS PRODUCT_QTY,
				'".$member_id."'	AS CREATER,
				'".$member_id."'	AS UPDATER
			FROM
				SHOP_PRODUCT PR
			WHERE
				PR.IDX = ".$product_idx."
		";
		
		$db->query($insert_basket_info_sql);
		
		$parent_idx = $db->last_id();
		if (!empty($parent_idx)) {
			/* 자식 세트 상품 추가 처리 */
			for ($i=0; $i<count($option_info); $i++) {
				$set_product_idx = $option_info[$i]['product_idx'];
				$set_option_idx = $option_info[$i]['option_idx'];
				
				$insert_basket_set_product_sql = "
					INSERT INTO
						BASKET_INFO
					(
						COUNTRY,
						MEMBER_IDX,
						MEMBER_ID,
						PRODUCT_IDX,
						PARENT_IDX,
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
						".$parent_idx."		AS PARENT_IDX,
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
						PR.IDX = ".$set_product_idx." AND
						OO.IDX = ".$set_option_idx."
				";
				
				$db->query($insert_basket_set_product_sql);
			}
		}
	}
}

/* 위시 리스트 페이지 쇼핑백 추가 처리 */
function addWishListBasketInfo($db,$country,$member_idx,$member_id,$wish_info) {
	for ($i=0; $i<count($wish_info); $i++) {
		$product_type = $wish_info[$i]['product_type'];
		$wish_idx = $wish_info[$i]['wish_idx'];
		
		$json_option_info = json_decode($wish_info[$i]['option_info'],true);
		$option_info = json_decode($json_option_info,true);
		
		if ($product_type == "B") {
			for ($j=0; $j<count($option_info); $j++) {
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
						WHISH_LIST WL
						LEFT JOIN SHOP_PRODUCT PR ON
						WL.PRODUCT_IDX = PR.IDX
						LEFT JOIN ORDERSHEET_OPTION OO ON
						PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
					WHERE
						WL.IDX = ".$wish_idx." AND
						OO.IDX = ".$option_info[$j]."
				";
				
				$db->query($insert_basket_info_sql);
			}
		} else if ($product_type == "S") {
			$insert_basket_info_sql = "
				INSERT INTO
					BASKET_INFO
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
					PRODUCT_QTY,
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
					0					AS OPTION_IDX,
					PR.PRODUCT_CODE		AS BARCODE,
					'Set'				AS OPTION_NAME,
					1					AS PRODUCT_QTY,
					'".$member_id."'	AS CREATER,
					'".$member_id."'	AS UPDATER
				FROM
					WHISH_LIST WL
					LEFT JOIN SHOP_PRODUCT PR ON
					WL.PRODUCT_IDX = PR.IDX
				WHERE
					WL.IDX = ".$wish_idx."
			";
			
			$db->query($insert_basket_info_sql);
			
			$parent_idx = $db->last_id();
			
			if (!empty($parent_idx)) {
				for ($k=0; $k<count($option_info); $k++) {
					$set_product_idx = $option_info[$k]['product_idx'];
					$set_option_idx = $option_info[$k]['option_idx'];
					
					$insert_basket_set_product_sql = "
						INSERT INTO
							BASKET_INFO
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
							PRODUCT_QTY,
							CREATER,
							UPDATER
						)
						SELECT
							'".$country."'		AS COUNTRY,
							".$member_idx."		AS MEMBER_IDX,
							'".$member_id."'	AS MEMBER_ID,
							PR.IDX				AS PRODUCT_IDX,
							PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
							".$parent_idx."		AS PARENT_IDX,
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
							PR.IDX = ".$set_product_idx." AND
							OO.IDX = ".$set_option_idx."
					";
					
					$db->query($insert_basket_set_product_sql);
				}
			}
		}
	}
}

?>	