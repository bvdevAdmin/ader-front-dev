<?php
/*
 +=============================================================================
 | 
 | 주문 취소 화면
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.12.05
 | 최종 수정	: 
 | 최종 수정일	: 
 | 버전		: 
 | 설명		: 
 | 
 +=============================================================================
*/	

include_once $_CONFIG['PATH']['API'].'_legacy/common.php';
include_once $_CONFIG['PATH']['API'].'_legacy/send.php';

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
    if ($order_code != null && (isset($param_OEX) || isset($param_ORF))) {
		$db->begin_transaction();
		
		try {
			$cnt_order = $db->count("ORDER_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND ORDER_CODE = ?",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$order_code));
			if ($cnt_order > 0) {
				if (!isset($param_OEX)) {
					$param_OEX = null;
				}
	
				if (!isset($param_ORF)) {
					$param_ORF = null;
				}
	
				/* 주문교환 배송비 미결제 접수상품 삭제 */
				initOrder_update($db,"OEX",$order_code);
				
				/* 주문반품 배송비 미결제 접수상품 삭제 */
				initOrder_update($db,"ORF",$order_code);
				
				$remain = getOrder_remain($db,$order_code);
				
				$cnt_pg_OEX = 0;
				$update_code_E = null;
				
				$cnt_pg_ORF = 0;
				$update_code_R = null;
				
				/* 주문 교환 접수 */
				if (isset($param_OEX) && is_array($param_OEX) && count($param_OEX) > 0) {
					$param_status = "OEX";
					
					$update_code_E		= setOrder_update($db,$param_status,$order_code);
					
					$order_title		= setOrder_title($db,$param_OEX);
					
					$exchange_product	= getUpdate_product($db,$param_status,$param_OEX,$remain);
					$exchange_price		= calcUpdate_total($exchange_product);
					
					$order_status = $param_status;
					if ($housing_type == "APL") {
						if ($param_delivery > 0) {
							$order_status = "OET";
						} else {
							$order_status = "OEX";
						}
					}
					
					$param_info = array(
						$update_code_E,
						$order_title,
						$order_status,
						
						$exchange_price['product'],
						
						$housing_type,
						$housing_idx,
						$housing_num,
						
						$_SESSION['MEMBER_ID'],
						$_SESSION['MEMBER_ID'],
						
						$order_code
					);
					
					/* 주문 교환 접수 - 교환 접수 등록 */
					$ei_idx = addOrder_update($db,$param_status,$param_info);
					if (isset($ei_idx) && $ei_idx > 0) {
						/* 주문 교환 상품 등록 - 일반상품 등록 */
						foreach($exchange_product as $key => $product) {
							$option_idx		= $product['option_idx'];
							$barcode		= $product['barcode'];
							$option_name	= $product['option_name'];
							
							if ($product['product_type'] == "S") {
								$option_idx		= 0;
								$barcode		= $product['product_code'];
								$option_name	= "Set";
							}
							
							$param_product = array(
								$ei_idx,
								$update_code_E,
								$order_status,
								
								$option_idx,
								$barcode,
								$option_name,
								
								$product['product_qty'],
								$product['product_qty'],
								
								$product['d1_idx'],
								$product['d2_idx'],
								$product['reason_memo'],
								
								$_SESSION['MEMBER_ID'],
								$_SESSION['MEMBER_ID'],
								
								$product['op_idx']
							);
							
							$param_update_B = array(
								'op_idx'		=>$product['op_idx'],
								'product_qty'	=>$product['product_qty']
							);
							
							$ep_idx = addUpdate_product($db,$param_status,$param_product);
							if (isset($ep_idx) && $ep_idx > 0) {
								putOrder_product_B($db,$param_update_B);
								
								$cnt_set = $db->count("ORDER_PRODUCT","ORDER_CODE = ? AND PARENT_IDX = ?",array($order_code,$product['op_idx']));
								if ($cnt_set > 0) {
									$param_set = array(
										$ei_idx,
										$update_code_E,
										$order_status,
										$ep_idx,
										
										$product['product_qty'],
										$product['product_qty'],
										
										$_SESSION['MEMBER_ID'],
										$_SESSION['MEMBER_ID'],
										
										$product['op_idx']
									);
									
									$param_update_S = array(
										'op_idx'		=>$product['op_idx'],
										'product_qty'	=>$product['product_qty']
									);
									
									/* 주문 교환 - 세트 상품 등록 */
									$us_idx = addUpdate_set($db,$param_status,$param_set);
									if (isset($us_idx) && $us_idx > 0) {
										putOrder_product_S($db,$param_update_S);
									}
								}
							}
						}
	
						addLog_info($db,"ORDER_EXCHANGE",$update_code_E);
						addLog_product($db,"ORDER_PRODUCT_EXCHANGE",$update_code_E);

						callSEND($db,"OEX",$update_code_E);
					}
				}
				
				/* 주문 반품 접수 */
				if (isset($param_ORF) && is_array($param_ORF) && count($param_ORF) > 0) {
					$param_status = "ORF";
					
					$update_code_R		= setOrder_update($db,"ORF",$order_code);
					
					$order_title		= setOrder_title($db,$param_ORF);
					
					$refund_product 	= getUpdate_product($db,$param_status,$param_ORF,$remain);
					$refund_price		= calcUpdate_total($refund_product);
					
					$order_status = $param_status;
					if ($housing_type == "APL") {
						if ($param_delivery > 0) {
							$order_status = "ORT";
						} else {
							$order_status = "ORF";
						}
					}
					
					$param_info = array(
						$update_code_R,
						$order_title,
						$order_status,
						
						$refund_price['product'],
						$refund_price['discount'],
						$refund_price['mileage'],
						0,
						0,
						$refund_price['cancel'],
						$refund_price['refund'],
						
						$housing_type,
						$housing_idx,
						$housing_num,
						
						$_SESSION['MEMBER_ID'],
						$_SESSION['MEMBER_ID'],
						
						$order_code
					);
					
					/* 주문 반품 접수 - 반품 접수 등록 */
					$ri_idx = addOrder_update($db,$param_status,$param_info);
					if (isset($ri_idx) && $ri_idx > 0) {
						/* 주문 반품 상품 등록 - 일반상품 등록 */
						foreach($refund_product as $key => $product) {
							$param_product = array(
								$ri_idx,
								$update_code_R,
								$order_status,
								
								$product['product_qty'],
								$product['product_qty'],
								
								$product['d1_idx'],
								$product['d2_idx'],
								$product['reason_memo'],
								
								$_SESSION['MEMBER_ID'],
								$_SESSION['MEMBER_ID'],
								
								$product['op_idx']
							);
							
							$param_update_B = array(
								'op_idx'		=>$product['op_idx'],
								'product_qty'	=>$product['product_qty']
							);
							
							$rp_idx = addUpdate_product($db,$param_status,$param_product);
							if (isset($rp_idx) && $rp_idx > 0) {
								putOrder_product_B($db,$param_update_B);
								
								$cnt_set = $db->count("ORDER_PRODUCT","ORDER_CODE = ? AND PARENT_IDX = ?",array($order_code,$product['op_idx']));
								if ($cnt_set > 0) {
									$param_set = array(
										$ri_idx,
										$update_code_R,
										$order_status,
										$rp_idx,
										
										$product['product_qty'],
										$product['product_qty'],
										
										$_SESSION['MEMBER_ID'],
										$_SESSION['MEMBER_ID'],
										
										$product['op_idx']
									);
									
									$param_update_S = array(
										'op_idx'		=>$product['op_idx'],
										'product_qty'	=>$product['product_qty']
									);
									
									/* 주문 반품 - 세트 상품 등록 */
									$us_idx = addUpdate_set($db,$param_status,$param_set);
									if (isset($us_idx) && $us_idx > 0) {
										putOrder_product_S($db,$param_update_S);
									}
								}
							}
						}
	
						addLog_info($db,"ORDER_REFUND",$update_code_R);
						addLog_product($db,"ORDER_PRODUCT_REFUND",$update_code_R);

						callSEND($db,"ORF",$update_code_R);
					}
				}
				
				$delivery_type	= null;
				$delivery_price	= 0;
				$d_update_code	= null;
				
				/* 주문 교환/반품 배송비 계산 */
				$cnt_pg			= checkPG($param_OEX,$param_ORF);
	
				if ($housing_type == "APL") {
					if ($cnt_pg['OEX_F'] == 0 && $cnt_pg['ORF_F'] == 0) {
						if ($_SERVER['HTTP_COUNTRY'] != "KR") {
							if ($cnt_pg['OEX_T'] > 0) {
								$delivery_type = "OEX";
								
								$d_update_code = $update_code_E;
							} else {
								$delivery_type = "ORF";
								
								$d_update_code = $update_code_R;
							}
							
							$delivery_price = $remain['p_delivery'];
						} else {
							if ($cnt_pg['OEX_T'] > 0) {
								$delivery_type	= "OEX";
								$delivery_price = 5000;
								
								$d_update_code = $update_code_E;
							} else {
								if ($cnt_pg['ORF_T'] > 0) {
									$delivery_type	= "ORF";
									$delivery_price = 2500;
									
									$d_update_code = $update_code_R;
								}
							}
						}
					}
				}
				
				if ($delivery_price > 0) {
					$param_delivery = array(
						'order_code'		=>$order_code,
						'update_code'		=>$d_update_code,
						'delivery_price'	=>$delivery_price
					);
					
					addUpdate_delivery($db,$delivery_type,$param_delivery);
				}
				
				$db->commit();
				
				

				$json_result['data'] = array(
					'member_id'			=>$_SESSION['MEMBER_ID'],
					'delivery_type'		=>$delivery_type,
					'update_code'		=>$d_update_code,
					'delivery_price'	=>$delivery_price	
				);
			} else {
				$json_result['code'] = 300;
				$json_result['msg'] = "";
				
				echo json_encode($json_result);
				exit;
			}
		} catch (mysqli_sql_exception $e) {
			$db->rollback();
			
			print_r($e);
			
			$json_result['code'] = 301;
			$json_result['msg'] = '주문반품 접수처리중 오류가 발생했습니다.';
			
			echo json_encode($json_result);
			exit;
		}
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());

    echo json_encode($json_result);
    exit;
}

function setOrder_title($db,$data) {
	$op_idx = array();
	
	foreach($data as $value) {
		$tmp_idx = $value['op_idx'];
		if (!in_array($tmp_idx,$op_idx)) {
			array_push($op_idx,$tmp_idx);
		}
	}
	
	$order_title = "";
	
	$select_order_product_sql = "
		SELECT
			OP.PRODUCT_NAME
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.IDX IN (".implode(',',array_fill(0,count($op_idx),'?')).")
		ORDER BY
			OP.IDX ASC
	";
	
	$db->query($select_order_product_sql,$op_idx);
	
	foreach($db->fetch() as $data) {
		$order_product[] = array(
			'product_name'		=>$data['PRODUCT_NAME']
		);
	}
	
	if (count($order_product) > 0) {
		$suffix	= array(
			'KR'		=>" 외 ",
			'EN'		=>" and ",
		);
		
		$unit	= array(
			'KR'		=>" 건",
			'EN'		=>" items"
		);
		
		if (count($order_product) > 1) {
			$order_title = $order_product[0]['product_name'].$suffix[$_SERVER['HTTP_COUNTRY']].(count($order_product) - 1).$unit[$_SERVER['HTTP_COUNTRY']];
		} else {
			$order_title = $order_product[0]['product_name'];
		}
	}
	
	return $order_title;
}

function checkPG($oex,$orf) {
	$oex_T = 0;
	$oex_F = 0;

	$orf_T = 0;
	$orf_F = 0;
	
	if ($oex != null) {
		foreach($oex as $value) {
			$d1_pg_flg = $value['d1_pg_flg'];
			if ($d1_pg_flg == true) {
				$oex_T++;
			} else {
				$oex_F++;
			}
			
			$d2_pg_flg = $value['d2_pg_flg'];
			if ($d2_pg_flg == true) {
				$oex_T++;
			} else {
				$oex_F++;
			}
		}
	}
	
	if ($orf != null) {
		foreach($orf as $value) {
			$d1_pg_flg = $value['d1_pg_flg'];
			if ($d1_pg_flg == true) {
				$orf_T++;
			} else {
				$orf_F++;
			}
			
			$d2_pg_flg = $value['d2_pg_flg'];
			if ($d2_pg_flg == true) {
				$orf_T++;
			} else {
				$orf_F++;
			}
		}
	}

	$cnt_pg = array(
		'OEX_T'		=>$oex_T,
		'OEX_F'		=>$oex_F,
		'ORF_T'		=>$orf_T,
		'ORF_F'		=>$orf_F
	);

	return $cnt_pg;
}

function getOrder_remain($db,$order_code) {
	$remain_price = array();
	
	$select_remain_price_sql = "
		SELECT
			(
				SELECT
					IFNULL(
						SUM(S_OP.REMAIN_PRICE),0
					)
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					S_OP.ORDER_CODE = ? AND
					S_OP.REMAIN_QTY > 0
			)					AS REMAIN_PRODUCT,
			OI.REMAIN_DISCOUNT	AS REMAIN_DISCOUNT,
			OI.REMAIN_MILEAGE	AS REMAIN_MILEAGE,
			OI.PG_REMAIN_PRICE	AS REMAIN_PG,
			OI.REMAIN_DELIVERY	AS REMAIN_DELIVERY,
			
			OI.PRICE_DELIVERY	AS PRICE_DELIVERY
		FROM
			ORDER_INFO OI
		WHERE
			OI.ORDER_CODE = ?
	";

	$db->query($select_remain_price_sql,array($order_code,$order_code));

	foreach($db->fetch() as $data) {
		$remain_price = array(
			'product'		=>$data['REMAIN_PRODUCT'],
			'discount'		=>$data['REMAIN_DISCOUNT'],
			'mileage'		=>$data['REMAIN_MILEAGE'],
			'delivery'		=>$data['REMAIN_DELIVERY'],
			'pg'			=>$data['REMAIN_PG'],
			
			'p_delivery'	=>$data['PRICE_DELIVERY']
		);
	}
	
	return $remain_price;
}

function getUpdate_product($db,$param_status,$data_product,$remain) {
	$update_product = array();
	
	$column_select = "";
	if ($param_status == "OEX") {
		$column_select = "
			(
				SELECT
					S_OO.BARCODE
				FROM
					SHOP_OPTION S_OO
				WHERE
					S_OO.IDX = ?
			)						AS BARCODE,
			(
				SELECT
					S_OO.OPTION_NAME
				FROM
					SHOP_OPTION S_OO
				WHERE
					S_OO.IDX = ?
			)						AS OPTION_NAME,
		";
	}
	
	foreach($data_product as $product) {
		$param_bind = array($product['op_idx']);
		
		if ($param_status == "OEX") {
			$param_bind = array_merge(array($product['option_idx'],$product['option_idx']),$param_bind);
		}
		
		$select_order_product_sql = "
			SELECT
				OP.IDX					AS OP_IDX,
				OP.PRODUCT_TYPE			AS PRODUCT_TYPE,
				OP.PRODUCT_CODE			AS PRODUCT_CODE,
				
				".$column_select."
				
				1						AS PRODUCT_QTY,
				(
					OP.REMAIN_PRICE / OP.REMAIN_QTY
				)						AS PRODUCT_PRICE
			FROM
				ORDER_PRODUCT OP
			WHERE
				OP.IDX = ?
		";
		
		$db->query($select_order_product_sql,$param_bind);
		
		foreach($db->fetch() as $data) {
			$discount_price	= 0;
			$mileage_price	= 0;
			$cancel_price	= 0;
			
			if ($param_status == "ORF") {
				if ($remain['discount'] > 0) {
					$discount_price = $data['PRODUCT_PRICE'] / ($remain['product'] - $remain['delivery']) * $remain['discount'];
				}
				
				if ($remain['mileage'] > 0) {
					$mileage_price = $data['PRODUCT_PRICE'] / ($remain['product'] - $remain['delivery']) * $remain['mileage'];
				}
				
				$cancel_price = $data['PRODUCT_PRICE'] - $discount_price - $mileage_price;
			}
			
			$tmp_product = array(
				'op_idx'				=>$data['OP_IDX'],
				'product_type'			=>$data['PRODUCT_TYPE'],
				'product_code'			=>$data['PRODUCT_CODE'],
				
				'product_qty'			=>$data['PRODUCT_QTY'],
				'product_price'			=>$data['PRODUCT_PRICE'],
				
				'discount_price'		=>$discount_price,
				'mileage_price'			=>$mileage_price,
				'cancel_price'			=>$cancel_price,
				
				'd1_idx'				=>$product['d1_idx'],
				'd1_pg_flg'				=>$product['d1_pg_flg'],
				'd2_idx'				=>$product['d2_idx'],
				'd2_pg_flg'				=>$product['d2_pg_flg'],
				'reason_memo'			=>$product['reason_memo']
			);
			
			if ($param_status == "OEX") {
				$tmp_product['option_idx']	= $product['option_idx'];
				$tmp_product['barcode']		= $data['BARCODE'];
				$tmp_product['option_name']	= $data['OPTION_NAME'];
			}
			
			array_push($update_product,$tmp_product);
		}
	}
	
	return $update_product;
}

function calcUpdate_total($update_product) {
	$price_product	= 0;
	$price_discount	= 0;
	$price_mileage	= 0;
	$price_cancel	= 0;
	$price_refund	= 0;
	
	if (count($update_product) > 0) {
		foreach($update_product as $product) {
			$price_product	+= $product['product_price'];
			$price_discount	+= $product['discount_price'];
			$price_mileage	+= $product['mileage_price'];
			$price_cancel	+= $product['cancel_price'];
			$price_refund	+= $product['product_price'];
		}
	}
	
	$total_price = array(
		'product'		=>$price_product,
		'discount'		=>$price_discount,
		'mileage'		=>$price_mileage,
		'cancel'		=>$price_cancel,
		'refund'		=>$price_refund
	);
	
	return $total_price;
}

function addOrder_update($db,$param_status,$param) {
	$table = array(
		'OEX'		=>"ORDER_EXCHANGE",
		'ORF'		=>"ORDER_REFUND"
	);
	
	$column_insert = "
		PRICE_PRODUCT,
	";
	$column_select = "
		?						AS PRICE_PRODUCT,
	";
	
	if ($param_status == "ORF") {
		$column_insert .= "
			PRICE_DISCOUNT,
			PRICE_MILEAGE_POINT,
			PRICE_DELIVERY,
			DELIVERY_RETURN,
			PRICE_CANCEL,
			PRICE_REFUND,
		";
		
		$column_select .= "
			?						AS PRICE_DISCOUNT,
			?						AS PRICE_MILEAGE_POINT,
			?						AS PRICE_DELIVERY,
			?						AS DELIVERY_RETURN,
			?						AS PRICE_CANCEL,
			?						AS PRICE_REFUND,
		";
	}
	
	$ui_idx = 0;
	
	$insert_order_update_sql = "
		INSERT INTO
			".$table[$param_status]."
		(
			COUNTRY,
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_TITLE,
			ORDER_STATUS,
			
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			MEMBER_MOBILE,
			MEMBER_LEVEL,
			
			".$column_insert."
			
			HOUSING_TYPE,
			HOUSING_IDX,
			HOUSING_NUM,
			
			CREATER,
			UPDATER
		)
		SELECT
			OI.COUNTRY				AS COUNTRY,
			OI.ORDER_CODE			AS ORDER_CODE,
			?						AS ORDER_UPDATE_CODE,
			?						AS ORDER_TITLE,
			?						AS ORDER_STATUS,
			
			OI.MEMBER_IDX			AS MEMBER_IDX,
			OI.MEMBER_ID			AS MEMBER_ID,
			OI.MEMBER_NAME			AS MEMBER_NAME,
			OI.MEMBER_MOBILE		AS MEMBER_MOBILE,
			OI.MEMBER_LEVEL			AS MEMBER_LEVEL,
			
			".$column_select."
			
			?						AS HOUSING_TYPE,
			?						AS HOUSING_IDX,
			?						AS HOUSING_NUM,
			
			?						AS CREATER,
			?						AS UPDATER
		FROM
			ORDER_INFO OI
		WHERE
			OI.ORDER_CODE = ?
	";
	
	$db->query($insert_order_update_sql,$param);
	
	$ui_idx = $db->last_id();
	
	return $ui_idx;
}

function addUpdate_product($db,$param_status,$param) {
	$table = array(
		'OEX'		=>"ORDER_PRODUCT_EXCHANGE",
		'ORF'		=>"ORDER_PRODUCT_REFUND"
	);
	
	$up_idx = 0;
	
	$column_insert = "";
	$column_select = "";
	
	if ($param_status == "ORF") {
		$column_insert = "
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
		";
		
		$column_select = "
			OP.OPTION_IDX		AS OPTION_IDX,
			OP.BARCODE			AS BARCODE,
			OP.OPTION_NAME		AS OPTION_NAME,
		";
	} else if ($param_status == "OEX") {
		$column_insert = "
			PREV_OPTION_IDX,
			PREV_OPTION_NAME,
			
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
		";
		
		$column_select = "
			OP.OPTION_IDX		AS PREV_OPTION_IDX,
			OP.OPTION_NAME		AS PREV_OPTION_NAME,
			
			?					AS OPTION_IDX,
			?					AS BARCODE,
			?					AS OPTION_NAME,
		";
	}
	
	$insert_update_product_sql = "
		INSERT INTO
			".$table[$param_status]."
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			
			PRODUCT_IDX,
			PRODUCT_TYPE,
			PARENT_IDX,
			PRODUCT_CODE,
			PRODUCT_NAME,
			
			".$column_insert."
			
			PRODUCT_QTY,
			PRODUCT_PRICE,
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		)
		SELECT
			?					AS ORDER_IDX,
			ORDER_CODE			AS ORDER_CODE,
			ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			?					AS ORDER_UPDATE_CODE,
			?					AS ORDER_STATUS,
			
			OP.PRODUCT_IDX		AS PRODUCT_IDX,
			OP.PRODUCT_TYPE		AS PRODUCT_TYPE,
			PARENT_IDX			AS PARENT_IDX,
			PRODUCT_CODE		AS PRODUCT_CODE,
			PRODUCT_NAME		AS PRODUCT_NAME,
			
			".$column_select."
			
			?					AS PRODUCT_QTY,
			(
				(OP.REMAIN_PRICE / OP.REMAIN_QTY) * ?
			)					AS PRODUCT_PRICE,
			
			?					AS DEPTH1_IDX,
			?					AS DEPTH2_IDX,
			?					AS REASON_MEMO,
			
			?					AS CREATER,
			?					AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.IDX = ?
	";
	
	$db->query($insert_update_product_sql,$param);
	
	$up_idx = $db->last_id();
	
	return $up_idx;
}

function addUpdate_set($db,$param_status,$param) {
	$us_idx = 0;
	
	$table = array(
		'OEX'		=>"ORDER_PRODUCT_EXCHANGE",
		'ORF'		=>"ORDER_PRODUCT_REFUND"
	);
	
	$insert_set_product_sql = "
		INSERT INTO
			".$table[$param_status]."
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			
			PRODUCT_IDX,
			PRODUCT_TYPE,
			PARENT_IDX,
			PRODUCT_CODE,
			PRODUCT_NAME,
			
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			
			PRODUCT_QTY,
			PRODUCT_PRICE,
			
			CREATER,
			UPDATER
		)
		SELECT
			?					AS ORDER_IDX,
			ORDER_CODE			AS ORDER_CODE,
			ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			?					AS ORDER_UPDATE_CODE,
			?					AS ORDER_STATUS,
			
			OP.PRODUCT_IDX		AS PRODUCT_IDX,
			OP.PRODUCT_TYPE		AS PRODUCT_TYPE,
			?					AS PARENT_IDX,
			PRODUCT_CODE		AS PRODUCT_CODE,
			PRODUCT_NAME		AS PRODUCT_NAME,
			
			OP.OPTION_IDX		AS OPTION_IDX,
			OP.BARCODE			AS BARCODE,
			OP.OPTION_NAME		AS OPTION_NAME,
			
			?					AS PRODUCT_QTY,
			(
				(OP.REMAIN_PRICE / OP.REMAIN_QTY) * ?
			)					AS PRODUCT_PRICE,
			
			?					AS CREATER,
			?					AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.PARENT_IDX = ?
	";
	
	$db->query($insert_set_product_sql,$param);
	
	$us_idx = $db->last_id();
	
	return $us_idx;
}

/* 주문 추가 배송비 등록 */
function addUpdate_delivery($db,$param_status,$param) {
	$connect = array(
		'OEX'		=>"E",
		'ORF'		=>"R"
	);
	
	$name = array(
		'OEX'		=>"교환",
		'ORF'		=>"반품"
	);
	
	$cnt_PRD = $db->count("ORDER_PRODUCT","ORDER_CODE = ?",array($param['order_code']));
	$cnt_PRD++;
	
	$order_product_code = $param['order_code']."-".$cnt_PRD;
	
	$barcode		= "DLV-P-".$connect[$param_status]."-XXXXXXXXX";
	$option_name	= "주문 ".$name[$param_status]." 추가 배송비";
	
	$param_bind = array(
		$order_product_code,
		$param['update_code'],
		
		$barcode,
		$option_name,
		
		$param['delivery_price'],
		$param['delivery_price'],
		
		$_SESSION['MEMBER_ID'],
		$_SESSION['MEMBER_ID'],
		
		$param['order_code']
	);
	
	$insert_extra_delivery_sql = "
		INSERT INTO
			ORDER_PRODUCT
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			
			PRODUCT_IDX,
			PRODUCT_TYPE,
			PRODUCT_CODE,
			PRODUCT_NAME,
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			
			PRODUCT_QTY,
			REMAIN_QTY,
			PRODUCT_PRICE,
			REMAIN_PRICE,
			
			CREATER,
			UPDATER
		)
		SELECT
			OI.IDX					AS ORDER_IDX,
			OI.ORDER_CODE			AS ORDER_CODE,
			?						AS ORDER_PRODUCT_CODE,
			?						AS ORDER_UPDATE_CODE,
			'PWT'					AS ORDER_STATUS,
				
			0						AS PRODUCT_IDX,
			'D'						AS PRODUCT_TYPE,
			'DLV-P-XXXXXXXXX'		AS PRODUCT_CODE,
			'주문 추가 배송비'			AS PRODUCT_NAME,
			0						AS OPTION_IDX,
			?						AS BARCODE,
			?						AS OPTION_NAME,
			
			1						AS PRODUCT_QTY,
			1						AS REMAIN_QTY,
			?						AS PRODUCT_PRICE,
			?						AS REMAIN_PRICE,
				
			?						AS CREATER,
			?						AS UPDATER
		FROM
			ORDER_INFO OI
		WHERE
			OI.ORDER_CODE = ?
	";
	
	$db->query($insert_extra_delivery_sql,$param_bind);
}

function putOrder_product_B($db,$param) {
	$update_order_product_sql = "
		UPDATE
			ORDER_PRODUCT
		SET
			REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY - ?),
			REMAIN_QTY		= REMAIN_QTY - ?
		WHERE
			IDX = ?
	";
	
	$db->query(
		$update_order_product_sql,
		array(
			$param['product_qty'],
			$param['product_qty'],
			
			$param['op_idx']
		)
	);
}

function putOrder_product_S($db,$param) {
	$update_order_product_sql = "
		UPDATE
			ORDER_PRODUCT
		SET
			REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY - ?),
			REMAIN_QTY		= REMAIN_QTY - ?
		WHERE
			IDX = ?
	";
	
	$db->query(
		$update_order_product_sql,
		array(
			$param['product_qty'],
			$param['product_qty'],
			
			$param['op_idx']
		)
	);
}

/* 주문교환/반품 배송비 미결제 접수상품 삭제 */
function initOrder_update($db,$param_status,$order_code) {
	$table_I = array(
		'OEX'		=>"ORDER_EXCHANGE",
		'ORF'		=>"ORDER_REFUND",
	);
	
	$table_P = array(
		'OEX'		=>"ORDER_PRODUCT_EXCHANGE",
		'ORF'		=>"ORDER_PRODUCT_REFUND",
	);
	
	$status_D = array(
		'OEX'		=>"OET",
		'ORF'		=>"ORT"
	);
	
	$select_order_update_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.PRODUCT_QTY			AS PRODUCT_QTY
		FROM
			".$table_P[$param_status]." OP
		WHERE
			OP.ORDER_CODE = ? AND
			OP.ORDER_STATUS LIKE '%T' AND
			OP.PRODUCT_TYPE NOT REGEXP 'V|D'
	";
	
	$db->query($select_order_update_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$op_idx				= $data['OP_IDX'];
		$order_product_code = $data['ORDER_PRODUCT_CODE'];
		$product_qty		= $data['PRODUCT_QTY'];
		
		$init_product_B_sql = "
			UPDATE
				ORDER_PRODUCT
			SET
				REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY + ?),
				REMAIN_QTY		= REMAIN_QTY + ?
			WHERE
				ORDER_PRODUCT_CODE = ?
		";
		
		$db->query($init_product_B_sql,array($product_qty,$product_qty,$order_product_code));
		
		$cnt_set = $db->count($table_P[$param_status],"PARENT_IDX = ?",array($op_idx));
		if ($cnt_set > 0) {
			$init_product_S_sql = "
				UPDATE
					ORDER_PRODUCT
				SET
					REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY + ?),
					REMAIN_QTY		= REMAIN_QTY + ?
				WHERE
					PARENT_IDX = ?
			";
			
			$db->query($init_product_B_sql,array($product_qty,$product_qty,$op_idx));
		}
	}
	
	$db->delete("ORDER_PRODUCT","ORDER_CODE = ? AND PRODUCT_TYPE = 'D' AND ORDER_STATUS = 'PWT'",array($order_code));
	$db->delete($table_I[$param_status],"ORDER_CODE = ? AND ORDER_STATUS = ?",array($order_code,$status_D[$param_status]));
	$db->delete($table_P[$param_status],"ORDER_CODE = ? AND ORDER_STATUS = ?",array($order_code,$status_D[$param_status]));
}

function addLog_info($db,$table,$code) {
    $data = $db->get($table,"ORDER_UPDATE_CODE = ?",array($code));
    if (sizeof($data) > 0) {
        $data = $data[0];

        $country = null;
        if (isset($data['COUNTRY'])) {
            $country = $data['COUNTRY'];
        }

        $order_code = null;
        if (isset($data['ORDER_CODE'])) {
            $order_code = $data['ORDER_CODE'];
        }

        $update_code = null;
        if (isset($data['ORDER_UPDATE_CODE'])) {
            $update_code = $data['ORDER_UPDATE_CODE'];
        }

		$order_status = null;
        if (isset($data['ORDER_STATUS'])) {
            $order_status = $data['ORDER_STATUS'];
        }

        $order_title = null;
        if (isset($data['ORDER_TITLE'])) {
            $order_title = $data['ORDER_TITLE'];
        }

        $price_product = 0;
        if (isset($data['PRICE_PRODUCT'])) {
            $price_product = $data['PRICE_PRODUCT'];
        }

        $price_mileage = 0;
        if (isset($data['PRICE_MILEAGE_POINT'])) {
            $price_mileage = $data['PRICE_MILEAGE_POINT'];
        }

        $price_charge = 0;
        if (isset($data['PRICE_CHARGE_POINT'])) {
            $price_charge = $data['PRICE_CHARGE_POINT'];
        }

        $price_discount = 0;
        if (isset($data['PRICE_DISCOUNT'])) {
            $price_discount = $data['PRICE_DISCOUNT'];
        }

        $price_delivery = 0;
        if (isset($data['PRICE_DELIVERY'])) {
            $price_delivery = $data['PRICE_DELIVERY'];
        }

        $delivery_return = 0;
        if (isset($data['DELIVERY_RETURN'])) {
            $delivery_return = $data['DELIVERY_RETURN'];
        }

        $price_cancel = 0;
        if (isset($data['PRICE_CANCEL'])) {
            $price_cancel = $data['PRICE_CANCEL'];
        }

        $price_refund = 0;
        if (isset($data['PRICE_REFUND'])) {
            $price_refund = $data['PRICE_REFUND'];
        }

        $pg_mid = null;
        if (isset($data['PG_MID'])) {
            $pg_mid = $data['PG_MID'];
        }

        $pg_payment = null;
        if (isset($data['PG_PAYMENT'])) {
            $pg_payment = $data['PG_PAYMENT'];
        }

        $pg_payment_key = null;
        if (isset($data['PG_PAYMENT_KEY'])) {
            $pg_payment_key = $data['PG_PAYMENT_KEY'];
        }

        $pg_issue_code = null;
        if (isset($data['PG_ISSUE_CODE'])) {
            $pg_issue_code = $data['PG_ISSUE_CODE'];
        }

        $pg_card_number = null;
        if (isset($data['PG_CARD_NUMBER'])) {
            $pg_card_number = $data['PG_CARD_NUMBER'];
        }

        $pg_currency = null;
        if (isset($data['PG_CURRENCY'])) {
            $pg_currency = $data['PG_CURRENCY'];
        }

        $pg_cancel_date = null;
        if (isset($data['PG_CANCE_DATE'])) {
            $pg_cancel_date = $data['PG_CANCE_DATE'];
        }

        $pg_cancel_price = null;
        if (isset($data['PG_CANCE_PRICE'])) {
            $pg_cancel_price = $data['PG_CANCE_PRICE'];
        }

        $pg_cancel_key = null;
        if (isset($data['PG_CANCEL_KEY'])) {
            $pg_cancel_key = $data['PG_CANCEL_KEY'];
        }

        $create_date = null;
        if (isset($data['CREATE_DATE'])) {
            $create_date = $data['CREATE_DATE'];
        }

        $creater = null;
        if (isset($data['CREATER'])) {
            $creater = $data['CREATER'];
        }

        $db->insert(
            "ORDER_INFO_LOG",
            array(
                'COUNTRY'				=>$country,
                'ORDER_CODE'			=>$order_code,
                'ORDER_UPDATE_CODE'		=>$update_code,
                'ORDER_STATUS'			=>$order_status,
                'ORDER_TITLE'			=>$order_title,
                'PRICE_PRODUCT'			=>$price_product,
                'PRICE_MILEAGE'			=>$price_mileage,
                'PRICE_CHARGE'			=>$price_charge,
                'PRICE_DISCOUNT'		=>$price_discount,
                'PRICE_DELIVERY'		=>$price_delivery,
                'DELIVERY_RETURN'		=>$delivery_return,
                'PRICE_CANCEL'			=>$price_cancel,
                'PRICE_REFUND'			=>$price_refund,
                'PG_MID'				=>$pg_mid,
                'PG_PAYMENT'			=>$pg_payment,
                'PG_PAYMENT_KEY'		=>$pg_payment_key,
                'PG_ISSUE_CODE'			=>$pg_issue_code,
                'PG_CARD_NUMBER'		=>$pg_card_number,
                'PG_CURRENCY'			=>$pg_currency,
                'PG_CANCEL_DATE'		=>$pg_cancel_date,
                'PG_CANCEL_PRICE'		=>$pg_cancel_price,
                'PG_CANCEL_KEY'			=>$pg_cancel_key,
                'CREATE_DATE'			=>$create_date,
                'CREATER'				=>$creater
            )
        );
    }
}

/* (공통) 주문 상품 로그 등록 */
function addLog_product($db,$table,$code) {
	$data = $db->get($table,"ORDER_UPDATE_CODE = ?",array($code));
    if (sizeof($data) > 0) {
		$data = $data[0];
		
		$order_code = null;
		if (isset($data['ORDER_CODE'])) {
			$order_code = $data['ORDER_CODE'];
		}
		
		$update_code = null;
		if (isset($data['ORDER_UPDATE_CODE'])) {
			$update_code = $data['ORDER_UPDATE_CODE'];
		}

		$order_status = null;
		if (isset($data['ORDER_STATUS'])) {
			$order_status = $data['ORDER_STATUS'];
		}
		
		$product_idx = null;
		if (isset($data['PRODUCT_IDX'])) {
			$product_idx = $data['PRODUCT_IDX'];
		}
		
		$product_code = null;
		if (isset($data['PRODUCT_CODE'])) {
			$product_code = $data['PRODUCT_CODE'];
		}
		
		$product_name = null;
		if (isset($data['PRODUCT_NAME'])) {
			$product_name = $data['PRODUCT_NAME'];
		}
		
		$option_idx = null;
		if (isset($data['OPTION_IDX'])) {
			$option_idx = $data['OPTION_IDX'];
		}
		
		$barcode = null;
		if (isset($data['BARCODE'])) {
			$barcode = $data['BARCODE'];
		}
		
		$option_name = null;
		if (isset($data['OPTION_NAME'])) {
			$option_name = $data['OPTION_NAME'];
		}
		
		$prev_idx = null;
		if (isset($data['PREV_OPTION_IDX'])) {
			$prev_idx = $data['PREV_OPTION_IDX'];
		}
		
		$prev_name = null;
		if (isset($data['PREV_OPTION_NAME'])) {
			$prev_name = $data['PREV_OPTION_NAME'];
		}
		
		$product_qty = null;
		if (isset($data['PRODUCT_QTY'])) {
			$product_qty = $data['PRODUCT_QTY'];
		}
		
		$product_price = 0;
		if (isset($data['PRODUCT_PRICE'])) {
			$product_price = $data['PRODUCT_PRICE'];
		}
		
		$mileage_price = 0;
		if (isset($data['MILEAGE_PRICE'])) {
			$mileage_price = $data['MILEAGE_PRICE'];
		}
		
		$charge_price = 0;
		if (isset($data['CHARGE_PRICE'])) {
			$charge_price = $data['CHARGE_PRICE'];
		}
		
		$discount_price = 0;
		if (isset($data['DISCOUNT_PRICE'])) {
			$discount_price = $data['DISCOUNT_PRICE'];
		}
		
		$delivery_price = 0;
		if (isset($data['DELIVERY_PRICE'])) {
			$delivery_price = $data['DELIVERY_PRICE'];
		}
		
		$cancel_price = 0;
		if (isset($data['CANCEL_PRICE'])) {
			$cancel_price = $data['CANCEL_PRICE'];
		}
		
		$refund_price = 0;
		if (isset($data['REFUND_PRICE'])) {
			$refund_price = $data['REFUND_PRICE'];
		}
		
		$create_date = null;
		if (isset($data['CREATE_DATE'])) {
			$create_date = $data['CREATE_DATE'];
		}
		
		$creater = null;
		if (isset($data['CREATER'])) {
			$creater = $data['CREATER'];
		}
		
		$db->insert(
			"ORDER_PRODUCT_LOG",
			array(
				'COUNTRY'				=>$_SERVER['HTTP_COUNTRY'],
				'ORDER_CODE'			=>$order_code,
				'ORDER_UPDATE_CODE'		=>$update_code,
				'ORDER_STATUS'			=>$order_status,
				'PRODUCT_IDX'			=>$product_idx,
				'PRODUCT_CODE'			=>$product_code,
				'PRODUCT_NAME'			=>$product_name,
				'OPTION_IDX'			=>$option_idx,
				'BARCODE'				=>$barcode,
				'OPTION_NAME'			=>$option_name,
				'PREV_OPTION_IDX'		=>$prev_idx,
				'PREV_OPTION_NAME'		=>$prev_name,
				'PRODUCT_QTY'			=>$product_qty,
				'PRODUCT_PRICE'			=>$product_price,
				'MILEAGE_PRICE'			=>$mileage_price,
				'DISCOUNT_PRICE'		=>$discount_price,
				'DELIVERY_PRICE'		=>$delivery_price,
				'CANCEL_PRICE'			=>$cancel_price,
				'REFUND_PRICE'			=>$refund_price,
				'CREATE_DATE'			=>$create_date,
				'CREATER'				=>$creater,
			)
		);
	}
}

function callSEND($db,$order_status,$update_code) {
	$table = array(
		'OEX'		=>"ORDER_EXCHANGE",
		'ORF'		=>"ORDER_REFUND"
	);

	$select_order_cancel_sql = "
		SELECT
			OI.COUNTRY				AS COUNTRY,
			OI.MEMBER_IDX			AS MEMBER_IDX,
			OI.MEMBER_ID			AS MEMBER_ID,
			OI.MEMBER_NAME			AS MEMBER_NAME,
			REPLACE(
				OI.MEMBER_MOBILE,'-',''
			)						AS TEL_MOBILE,

			OI.ORDER_CODE			AS ORDER_CODE,
			OI.ORDER_TITLE			AS ORDER_TITLE,
			OI.CREATE_DATE			AS CREATE_DATE
		FROM
			".$table[$order_status]." OI
		WHERE
			OI.ORDER_UPDATE_CODE = ?
	";

	$db->query($select_order_cancel_sql,array($update_code));

	foreach($db->fetch() as $data) {
		/* 알림톡 발송설정 체크처리 */
		if ($data['COUNTRY'] == "KR") {
			$kakao_setting = checkKAKAO_setting($db,"KAKAO_CODE_0008");
			if ($kakao_setting['kakao_flg'] == true && $kakao_setting['template_id'] != null) {
				
				/* KAKAO::PARAM */
				$param_kakao = array(
					'user_email'		=>$data['MEMBER_ID'],
					'user_name'			=>$data['MEMBER_NAME'],
					'tel_mobile'		=>$data['TEL_MOBILE'],
					'template_id'		=>$kakao_setting['template_id']
				);
				
				/* KAKAO::DATA */
				/*
				$kakao_data = array(
					'member_id'			=>$data['MEMBER_ID'],
					'member_name'		=>$data['MEMBER_NAME'],
					
					'order_code'		=>$data['ORDER_CODE'],
					'order_title'		=>$data['ORDER_TITLE'],
					'create_date'		=>$data['CREATE_DATE']
				);
				*/
				
				/* (공통) NCP - 메일 발송 */
				callSEND_kakao($db,$param_kakao,array());
			}
		}

		/* 자동메일 발송설정 체크처리 */
		$mail_setting = checkMAIL_setting($db,$data['COUNTRY'],"MAIL_CODE_0007");
		if ($mail_setting['mail_flg'] == true && ($mail_setting['template_id'] != null && $mail_setting['template_id'] != "00000")) {
			/* MAIL::PARAM */
			$param_mail = array(
				'user_email'		=>$data['MEMBER_ID'],
				'user_name'			=>$data['MEMBER_NAME'],
				'tel_mobile'		=>$data['TEL_MOBILE'],
				'template_id'		=>$mail_setting['template_id']
			);
			
			/* MAIL::DATA */
			/*
			$mail_data = array(
				'member_id'			=>$data['MEMBER_ID'],
				'member_name'		=>$data['MEMBER_NAME'],
				
				'order_code'		=>$data['ORDER_CODE'],
				'order_title'		=>$data['ORDER_TITLE'],
				'create_date'		=>$data['CREATE_DATE']
			);
			*/
			
			/* (공통) NCP - 메일 발송 */
			callSEND_mail($db,$param_mail,array());
		}
	}
}

?>