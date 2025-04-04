<?php 
/*
 +=============================================================================
 | 
 | 쇼핑백 화면 - 쇼핑백 상품 등록 //  '/var/www/www/api/order/basket/add.php';
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

include $_CONFIG['PATH']['API'].'_legacy/common/check.php';

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($product_type) && isset($product_idx)) {
		$db->begin_transaction();
		
		try {
			$cnt_basket = 0;
			/* 1. 쇼핑백 동일상품 중복 체크 */
			if ($product_type == "B") {
				$cnt_basket = $db->count("BASKET_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND PRODUCT_IDX = ? AND OPTION_IDX = ? AND PARENT_IDX = 0 AND DEL_FLG = FALSE",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$product_idx,$option_idx));
			} else if ($product_type == "S") {
				$cnt_basket = $db->count("BASKET_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND PRODUCT_IDX = ? AND OPTION_IDX = 0 AND DEL_FLG = FALSE",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$product_idx));
			}
			
			if ($cnt_basket > 0) {
				$json_result['code'] = 301;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0063',array());
				
				echo json_encode($json_result);
				exit;
			}
			
			$limit = array();
			if ($product_type == "B") {
				$limit = getProduct_limit($db,$product_idx,$option_idx);
			} else if ($product_type == "S" && count($option_info) > 0) {
				$limit_P = getProduct_limit($db,$product_idx,$option_idx);
				array_push($limit,$limit_P);
				
				foreach($option_info as $set) {
					$limit_C = getProduct_limit($db,$set['product_idx'],$set['option_idx']);
					array_push($limit,$limit_C);
				}
				
				$limit = checkLimit_set($limit);
			}
			
			if (sizeof($limit) > 0) {
				/* 3. 쇼핑백 등록 전 상품 체크 */
				
				/* 3-1. 회원 등급 체크 */
				if (!in_array("0",$limit['limit_member']) && !in_array($_SESSION['LEVEL_IDX'],$limit['limit_member'])) {
					$json_result['code'] = 302;
					$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0098',array());
					
					echo json_encode($json_result);
					exit;
				}
				
				/* 3-2. ID당 구매 제한 수량 체크 */
				if ($limit['limit_id_flg'] == true && $limit['order_qty_P'] > 0) {
					$json_result['code'] = 302;
					$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0004',array());
					
					echo json_encode($json_result);
					exit;
				}
				
				/* 3-3. 상품별 구매 제한 수량 체크 */
				if ($limit['limit_flg_P'] == true) {
					if ($limit['product_qty'] <= $limit['order_qty_P']) {
						$json_result['code'] = 303;
						$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0092',array($limit['product_qty']));;
						
						echo json_encode($json_result);
						exit;
					}
				}
				
				/* 3-4. 옵션별 구매 제한 수량 체크 */
				if ($limit['limit_flg_O'] == true) {
					if ($limit['option_qty'] <= $limit['order_qty_O']) {
						$json_result['code'] = 304;
						$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0092',array($limit['option_qty']));;
						
						echo json_encode($json_result);
						exit;
					}
				}
				
				/* 3-5. WCC 잔여 재고 체크 */
				if ($limit['remain_qty'] == 0) {
					$json_result['code'] = 305;
					$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0045',array());;
					
					echo json_encode($json_result);
					exit;
				}
				
				/* 3-6. 구매 가능 수량 체크 */
				if ($limit['limit_qty'] == 0) {
					$json_result['code'] = 306;
					$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0045',array());;
					
					echo json_encode($json_result);
					exit;
				}
				
				/* 4. 쇼핑백 등록 처리 */
				$basket_idx = addBasket($db,$product_idx,$option_idx);
				if (isset($basket_idx) && $product_type != "B") {
					addBasket_set($db,$basket_idx,$option_info);
				}
				
				$db->commit();
				
				$json_result['data'] = array(	
					'basket_cnt'	=>$db->count("BASKET_INFO","COUNTRY = ? AND MEMBER_IDX = ?",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']))
				);
			}
		} catch (mysqli_sql_exception $e) {
			print_r($e);
			
			$db->rollback();
			
			$json_result['code'] = 407;
			
			echo json_encode($json_result);
			exit;
		}
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

function getProduct_limit($db,$product_idx,$option_idx) {
	$product_B = array();
	
	$param_bind = array($option_idx,$option_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$option_idx,$product_idx);
	
	$select_shop_product_sql = "	
		SELECT
			PR.LIMIT_MEMBER				AS LIMIT_MEMBER,
			
			PR.LIMIT_ID_FLG				AS LIMIT_ID_FLG,
			
			PR.LIMIT_QTY_FLG			AS LIMIT_FLG_P,
			IFNULL(
				PR.LIMIT_PRODUCT_QTY,0
			)							AS PRODUCT_QTY,
			
			IFNULL(
				PO.LIMIT_QTY_FLG,FALSE
			)							AS LIMIT_FLG_O,
			IFNULL(
				PO.LIMIT_OPTION_QTY,0
			)							AS OPTION_QTY,
			
			IFNULL(
				V_ST.REMAIN_WCC_QTY,0
			)							AS REMAIN_QTY,
			IFNULL(
				V_ST.PURCHASEABLE_QTY,0
			)							AS LIMIT_QTY,
			IFNULL(
				J_PP.ORDER_QTY_P,0
			)							AS ORDER_QTY_P,
			IFNULL(
				J_PO.ORDER_QTY_O,0
			)							AS ORDER_QTY_O
		FROM
			SHOP_PRODUCT PR
			
			LEFT JOIN PRODUCT_OPTION PO ON
			PR.IDX = PO.PRODUCT_IDX AND
			PO.OPTION_IDX = ?
			
			LEFT JOIN V_STOCK V_ST ON
			PR.IDX = V_ST.PRODUCT_IDX AND
			V_ST.OPTION_IDX = ?
			
			LEFT JOIN (
				SELECT
					S_OP.PRODUCT_IDX	AS PRODUCT_IDX,
					S_OP.OPTION_IDX		AS OPTION_IDX,
					SUM(
						S_OP.REMAIN_QTY
					)					AS ORDER_QTY_P
				FROM
					ORDER_INFO S_OI
					
					LEFT JOIN ORDER_PRODUCT S_OP ON
					S_OI.IDX = S_OP.ORDER_IDX
				WHERE
					S_OI.COUNTRY = ? AND
					S_OI.MEMBER_IDX = ? AND
					S_OP.PRODUCT_TYPE NOT REGEXP 'V|D'
			) AS J_PP ON
			PR.IDX = J_PP.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					S_OP.PRODUCT_IDX	AS PRODUCT_IDX,
					S_OP.OPTION_IDX		AS OPTION_IDX,
					SUM(
						S_OP.REMAIN_QTY
					)					AS ORDER_QTY_O
				FROM
					ORDER_INFO S_OI
					
					LEFT JOIN ORDER_PRODUCT S_OP ON
					S_OI.IDX = S_OP.ORDER_IDX
				WHERE
					S_OI.COUNTRY = ? AND
					S_OI.MEMBER_IDX = ? AND
					S_OP.PRODUCT_TYPE NOT REGEXP 'V|D'
			) AS J_PO ON
			PR.IDX = J_PO.PRODUCT_IDX AND
			J_PO.OPTION_IDX = ?
		WHERE
			PR.IDX = ? AND
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
	";
	
	$db->query($select_shop_product_sql,$param_bind);
	
	foreach($db->fetch() as $data) {
		$limit_member = array("0");
		if ($data['LIMIT_MEMBER'] != null && strlen($data['LIMIT_MEMBER']) > 0) {
			$limit_member = explode(",",$data['LIMIT_MEMBER']);
		}
		
		$product_B = array(
			'limit_member'		=>$limit_member,			//구매 제한 회원 등급
			
			'limit_id_flg'		=>$data['LIMIT_ID_FLG'],	//ID당 구매 제한
			
			'limit_flg_P'		=>$data['LIMIT_FLG_P'],		//상품별 구매 수량 제한 여부
			'product_qty'		=>$data['PRODUCT_QTY'],		//상품별 구매 제한 수량
			'order_qty_P'		=>$data['ORDER_QTY_P'],		//상품 기준 구매 수량
			
			'limit_flg_O'		=>$data['LIMIT_FLG_O'],		//옵션별 구매 수량 제한 여부
			'option_qty'		=>$data['OPTION_QTY'],		//옵션별 구매 제한 수량
			'order_qty_O'		=>$data['ORDER_QTY_O'],		//옵션 기준 구매 수량
			
			'remain_qty'		=>$data['REMAIN_QTY'],		//WCC 잔여재고
			'limit_qty'			=>$data['LIMIT_QTY']		//구매 가능 수량
		);
	}
	
	return $product_B;
}

function checkLimit_set($param) {
	$check_limit = array();
	
	$limit_member	= array();
	$limit_id_flg	= false;
	
	$limit_flg_P	= false;
	$product_qty	= null;
	$order_qty_P	= null;
	
	$limit_flg_O	= false;
	$option_qty		= 5;
	$order_qty_O	= 5;
	
	$remain_qty		= null;
	$limit_qty		= null;
	
	$tmp_member = array();
	
	foreach($param as $key => $limit) {
		if ($key == 0) {
			$limit_member	= $limit['limit_member'];
			$limit_id_flg	= $limit['limit_id_flg'];
			
			$limit_flg_P	= $limit['limit_flg_P'];
			$product_qty	= $limit['product_qty'];
			$order_qty_P	= $limit['order_qty_P'];
		} else {
			array_merge($limit_member,$limit['limit_member']);
			
			if ($limit_id_flg == false && $limit['limit_id_flg'] == true) {
				$limit_id_flg == true;
			}
			
			if ($limit_flg_P == false && $limit['limit_flg_P'] == true) {
				$limit_flg_P = true;
			}
			
			if ($product_qty != null && ($product_qty > $limit['product_qty'])) {
				$product_qty = $limit['product_qty'];
			}
			
			if ($order_qty_P != null && ($order_qty_P > $limit['order_qty_P'])) {
				$order_qty_P = $limit['order_qty_P'];
			}
			
			if ($limit_flg_O == false && $limit['limit_flg_O'] == true) {
				$limit_flg_O = true;
			}
			
			if ($option_qty != null && ($option_qty > $limit['option_qty'])) {
				$option_qty = $limit['option_qty'];
			}
			
			if ($order_qty_O != null && ($order_qty_O > $limit['order_qty_O'])) {
				$order_qty_O = $limit['order_qty_O'];
			}
			
			if ($remain_qty == null || ($remain_qty > $limit['remain_qty'])) {
				$remain_qty = $limit['remain_qty'];
			}
			
			if ($limit_qty == null || ($limit_qty > $limit['limit_qty'])) {
				$limit_qty = $limit['limit_qty'];
			}
		}
	}
	
	$check_limit = array(
		'limit_member'		=>array_unique($limit_member),	//구매 제한 회원 등급
		
		'limit_id_flg'		=>$limit_id_flg,	//ID당 구매 제한
		
		'limit_flg_P'		=>$limit_flg_P,		//상품별 구매 수량 제한 여부
		'product_qty'		=>$product_qty,		//상품별 구매 제한 수량
		'order_qty_P'		=>$order_qty_P,		//상품 기준 구매 수량
		
		'limit_flg_O'		=>$limit_flg_O,		//옵션별 구매 수량 제한 여부
		'option_qty'		=>$option_qty,		//옵션별 구매 제한 수량
		'order_qty_O'		=>$order_qty_O,		//옵션 기준 구매 수량
		
		'remain_qty'		=>$remain_qty,		//WCC 잔여재고
		'limit_qty'			=>$limit_qty		//구매 가능 수량
	);
	
	return $check_limit;
}

function addBasket($db,$product_idx,$option_idx) {
	$param_bind = array(
		$_SERVER['HTTP_COUNTRY'],
		$_SESSION['MEMBER_IDX'],
		$_SESSION['MEMBER_ID'],
		
		$_SESSION['MEMBER_ID'],
		$_SESSION['MEMBER_ID'],
	);
	
	$table = " SHOP_PRODUCT PR ";
	$where = " PR.IDX = ? ";
	
	array_push($param_bind,$product_idx);
	
	if (intval($option_idx) > 0) {
		$column_option = "
			OO.IDX				AS OPTION_IDX,
			OO.BARCODE			AS BARCODE,
			OO.OPTION_NAME		AS OPTION_NAME,
		";
		
		$table .= "
			LEFT JOIN SHOP_OPTION OO ON
			PR.IDX = OO.PRODUCT_IDX
		";
		
		$where .= " AND (OO.IDX = ?) ";
		
		array_push($param_bind,$option_idx);
	} else {
		$column_option = "
			0					AS OPTION_IDX,
			PR.PRODUCT_CODE		AS BARCODE,
			'Set'				AS OPTION_NAME,
		";
	}
	
	$insert_basket_info_B_sql = "
		INSERT INTO
			BASKET_INFO
		(
			COUNTRY,
			MEMBER_IDX,
			MEMBER_ID,
			PRODUCT_TYPE,
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
			?					AS COUNTRY,
			?					AS MEMBER_IDX,
			?					AS MEMBER_ID,
			PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
			PR.IDX				AS PRODUCT_IDX,
			PR.PRODUCT_CODE		AS PRODUCT_CODE,
			PR.PRODUCT_NAME		AS PRODUCT_NAME,
			
			".$column_option."
			
			1					AS PRODUCT_QTY,
			?					AS CREATER,
			?					AS UPDATER
		FROM
			".$table."
		WHERE
			".$where."
	";
	
	$db->query($insert_basket_info_B_sql,$param_bind);
	
	$basket_idx = $db->last_id();
	
	return $basket_idx;
}

function addBasket_set($db,$basket_idx,$option_info) {
	foreach($option_info as $set) {
		$param_bind = array(
			$_SERVER['HTTP_COUNTRY'],
			$_SESSION['MEMBER_IDX'],
			$_SESSION['MEMBER_ID'],
			$basket_idx,
			$_SESSION['MEMBER_ID'],
			$_SESSION['MEMBER_ID'],
			$set['product_idx'],
			$set['option_idx']
		);
		
		$insert_basket_info_S_sql = "
			INSERT INTO
				BASKET_INFO
			(
				COUNTRY,
				MEMBER_IDX,
				MEMBER_ID,
				PRODUCT_TYPE,
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
				?					AS COUNTRY,
				?					AS MEMBER_IDX,
				?					AS MEMBER_ID,
				PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
				PR.IDX				AS PRODUCT_IDX,
				?					AS PARENT_IDX,
				PR.PRODUCT_CODE		AS PRODUCT_CODE,
				PR.PRODUCT_NAME		AS PRODUCT_NAME,
				OO.IDX				AS OPTION_IDX,
				OO.BARCODE			AS BARCODE,
				OO.OPTION_NAME		AS OPTION_NAME,
				1					AS PRODUCT_QTY,
				?					AS CREATER,
				?					AS UPDATER
			FROM
				SHOP_PRODUCT PR
				
				LEFT JOIN SHOP_OPTION OO ON
				PR.IDX = OO.PRODUCT_IDX
			WHERE
				PR.IDX = ? AND
				OO.IDX = ?
		";
		
		$db->query($insert_basket_info_S_sql,$param_bind);
	}
}

?>