<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문조회화면 - 공통함수
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.30
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

//주문 정보/상품 취득 대상 테이블 설정
function getOrderTable($order_status,$tmp_flg) {
	$order_table = array();
	
	$info = "";
	$product = "";
	$txt_tmp = "";
	
	if ($tmp_flg == true) {
		$txt_tmp = "TMP_";
	}
	
	switch ($order_status) {
		case "PRD" :
			$info = $txt_tmp."ORDER_INFO";
			$product = $txt_tmp."ORDER_PRODUCT";
			
			break;
		
		case "OCC" :
			$info = $txt_tmp."ORDER_CANCEL";
			$product = $txt_tmp."ORDER_PRODUCT_CANCEL";
			
			break;
		
		case "OEX" :
			$info = $txt_tmp."ORDER_EXCHANGE";
			$product = $txt_tmp."ORDER_PRODUCT_EXCHANGE";
			
			break;
		
		case "ORF" :
			$info = $txt_tmp."ORDER_REFUND";
			$product = $txt_tmp."ORDER_PRODUCT_REFUND";
			
			break;
	}
	
	$order_table['info'] = $info;
	$order_table['product'] = $product;
	
	return $order_table;
}

//[임시 주문 상품 테이블],[임시 주문 교환 상품 테이블],[임시 주문 반품 상품 테이블] 삭제
function delTmpOrderProduct($db,$order_code) {
	//[임시 주문 상품 테이블] 삭제
	$db->query("DELETE FROM TMP_ORDER_PRODUCT WHERE ORDER_CODE = '".$order_code."'");
	
	//[임시 주문 반품 상품 테이블] 삭제
	$db->query("DELETE FROM TMP_ORDER_PRODUCT_EXCHANGE WHERE ORDER_CODE = '".$order_code."'");
	
	//[임시 주문 교환 상품 테이블] 삭제
	$db->query("DELETE FROM TMP_ORDER_PRODUCT_REFUND WHERE ORDER_CODE = '".$order_code."'");
}

//[주문 상태별 상품 테이블] 상품 단위 개별/합계 취득
function getOrderProductByStatus($db,$order_status,$order_code,$select_type) {
	$order_product = array();
	
	$order_table = getOrderTable($order_status,false);
	
	$column_order_update_code = "";
	$column_prev_option_name = "";
	$column_product_qty = "";
	$column_delivery = "";
	
	if ($order_status != "PRD") {
		$column_order_update_code = "
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
		";
		
		if ($order_status == "OEX") {
			$column_prev_option_name = "
				(
					SELECT
						S_OO.OPTION_NAME
					FROM
						ORDERSHEET_OPTION S_OO
					WHERE
						S_OO.IDX = OT.PREV_OPTION_IDX
				)					AS PREV_OPTION_NAME,
			";
		} else {
			$column_prev_option_name = "
				NULL				AS PREV_OPTION_NAME,
			";
		}
		
		$column_product_qty = "
			0						AS QTY_OEX,
			0						AS QTY_TEX,
			0						AS QTY_ORF,
			0						AS QTY_TRF,
		";
	} else {
		$column_order_update_code = "
			NULL					AS ORDER_UPDATE_CODE,
		";
		
		$column_prev_option_name = "
			NULL					AS PREV_OPTION_NAME,
		";
		
		if ($select_type == "IND") {
			$column_product_qty = "
				(
					SELECT
						IFNULL(
							SUM(S_PE.PRODUCT_QTY),0
						)
					FROM
						ORDER_PRODUCT_EXCHANGE S_PE
					WHERE
						S_PE.ORDER_PRODUCT_CODE = OT.ORDER_PRODUCT_CODE
				)					AS QTY_OEX,
				(
					SELECT
						IFNULL(
							SUM(S_TE.PRODUCT_QTY),0
						)
					FROM
						TMP_ORDER_PRODUCT_EXCHANGE S_TE
					WHERE
						S_TE.ORDER_PRODUCT_CODE = OT.ORDER_PRODUCT_CODE
				)					AS QTY_TEX,
				(
					SELECT
						IFNULL(
							SUM(S_PF.PRODUCT_QTY),0
						)
					FROM
						ORDER_PRODUCT_REFUND S_PF
					WHERE
						S_PF.ORDER_PRODUCT_CODE = OT.ORDER_PRODUCT_CODE
				)					AS QTY_ORF,
				(
					SELECT
						IFNULL(
							SUM(S_TF.PRODUCT_QTY),0
						)
					FROM
						TMP_ORDER_PRODUCT_REFUND S_TF
					WHERE
						S_TF.ORDER_PRODUCT_CODE = OT.ORDER_PRODUCT_CODE
				)					AS QTY_TRF,
			";
		} else if ($select_type == "SUM") {
			$column_product_qty = "
				(
					SELECT
						IFNULL(
							SUM(S_PE.PRODUCT_QTY),0
						)
					FROM
						ORDER_PRODUCT_EXCHANGE S_PE
					WHERE
						S_PE.ORDER_PRODUCT_CODE = OT.ORDER_PRODUCT_CODE
				)					AS QTY_OEX,
				0					AS QTY_TEX,
				(
					SELECT
						IFNULL(
							SUM(S_PF.PRODUCT_QTY),0
						)
					FROM
						ORDER_PRODUCT_REFUND S_PF
					WHERE
						S_PF.ORDER_PRODUCT_CODE = OT.ORDER_PRODUCT_CODE
				)					AS QTY_ORF,
				0					AS QTY_TRF,
			";
		}
	}
	
	$select_order_product_sql = "
		SELECT
			OT.IDX					AS ORDER_PRODUCT_IDX,
			OI.COUNTRY				AS COUNTRY,
			OT.ORDER_CODE			AS ORDER_CODE,
			OT.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			".$column_order_update_code."
			OT.ORDER_STATUS			AS ORDER_STATUS,
			
			OT.PRODUCT_TYPE			AS PRODUCT_TYPE,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)						AS IMG_LOCATION,
			OT.PRODUCT_NAME			AS PRODUCT_NAME,
			PR.COLOR				AS COLOR,
			PR.COLOR_RGB			AS COLOR_RGB,
			
			OT.OPTION_NAME			AS OPTION_NAME,
			
			".$column_prev_option_name."
			
			".$column_product_qty."
			
			OT.PRODUCT_QTY			AS PRODUCT_QTY,
			OT.PRODUCT_PRICE		AS PRODUCT_PRICE,
			
			(
				SELECT
					S_DC.COMPANY_NAME
				FROM
					DELIVERY_COMPANY S_DC
				WHERE
					S_DC.IDX = OT.DELIVERY_IDX
			)						AS COMPANY_NAME,
			OT.DELIVERY_NUM			AS DELIVERY_NUM,
			
			PR.REFUND_FLG			AS REFUND_FLG
		FROM
			".$order_table['product']." OT
			LEFT JOIN SHOP_PRODUCT PR ON
			OT.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDER_INFO OI ON
			OT.ORDER_CODE = OI.ORDER_CODE
		WHERE
			OT.ORDER_CODE = '".$order_code."' AND
			OT.PRODUCT_TYPE NOT IN ('V','D','M') AND
			OT.PRODUCT_QTY > 0 AND
			OT.PARENT_IDX = 0
	";
	
	$db->query($select_order_product_sql);
	
	foreach($db->fetch() as $product_data) {
		$qty_OEX = $product_data['QTY_OEX'];
		$qty_TEX = $product_data['QTY_TEX'];
		$qty_ORF = $product_data['QTY_ORF'];
		$qty_TRF = $product_data['QTY_TRF'];
		
		$product_qty = $product_data['PRODUCT_QTY'] - $qty_OEX - $qty_TEX - $qty_ORF - $qty_TRF;
		
		if ($select_type == "IND") {
			if ($product_data['REFUND_FLG'] == false) {
				for ($i=0; $i<$product_qty; $i++) {
						$order_product[] = array(
							'order_product_idx'		=>$product_data['ORDER_PRODUCT_IDX'],
							'order_code'			=>$product_data['ORDER_CODE'],
							'order_product_code'	=>$product_data['ORDER_PRODUCT_CODE'],
							'order_update_code'		=>$product_data['ORDER_UPDATE_CODE'],
							'order_status'			=>$product_data['ORDER_STATUS'],
							'txt_order_status'		=>setTxtOrderStatus($product_data['ORDER_STATUS']),
							
							'product_type'			=>$product_data['PRODUCT_TYPE'],
							'img_location'			=>$product_data['IMG_LOCATION'],
							'product_name'			=>$product_data['PRODUCT_NAME'],
							'color'					=>$product_data['COLOR'],
							'color_rgb'				=>$product_data['COLOR_RGB'],
							
							'option_name'			=>$product_data['OPTION_NAME'],
							'prev_option_name'		=>$product_data['PREV_OPTION_NAME'],
							
							'product_qty'			=>1,
							'product_price'			=>$product_data['PRODUCT_PRICE'] / $product_qty,
							'txt_product_price'		=>number_format($product_data['PRODUCT_PRICE'] / $product_qty)
						);
				}
			}	
		} else if ($select_type == "SUM" && $product_qty > 0) {
			$order_product_code = $product_data['ORDER_PRODUCT_CODE'];
			$product_type = $product_data['PRODUCT_TYPE'];
			
			$set_product = null;
			if ($product_type == "S") {
				if (!empty($order_product_code) && $product_type == "S") {
					$set_product = getSetProduct($db,$order_product_code);
				}
			}
			
			$company_name = $product_data['COMPANY_NAME'];
			$delivery_num = "";
			
			$order_status = $product_data['ORDER_STATUS'];
			if ($order_status == "OEH" || $order_status == "ORH") {
				$company_name = "";
				if ($product_data['DELIVERY_NUM'] == null) {
					switch ($product_data['COUNTRY']) {
						case "KR" :
							$delivery_num = "체번대기중";
							break;
						
						case "EN" :
							$delivery_num = "Waing the post number.";
							break;
						
						case "CN" :
							$delivery_num = "等待邮政编码";
							break;
					}
				}
			}
			
			$order_product[] = array(
				'order_product_idx'		=>$product_data['ORDER_PRODUCT_IDX'],
				'order_code'			=>$product_data['ORDER_CODE'],
				'order_product_code'	=>$product_data['ORDER_PRODUCT_CODE'],
				'order_update_code'		=>$product_data['ORDER_UPDATE_CODE'],
				'order_status'			=>$product_data['ORDER_STATUS'],
				'txt_order_status'		=>setTxtOrderStatus($product_data['ORDER_STATUS']),
				
				'product_type'			=>$product_data['PRODUCT_TYPE'],
				'img_location'			=>$product_data['IMG_LOCATION'],
				'product_name'			=>$product_data['PRODUCT_NAME'],
				'color'					=>$product_data['COLOR'],
				'color_rgb'				=>$product_data['COLOR_RGB'],
				
				'option_name'			=>$product_data['OPTION_NAME'],
				'prev_option_name'		=>$product_data['PREV_OPTION_NAME'],
				
				'product_qty'			=>$product_qty,
				'product_price'			=>$product_data['PRODUCT_PRICE'],
				'txt_product_price'		=>number_format($product_data['PRODUCT_PRICE']),
				
				'company_name'			=>$company_name,
				'delivery_num'			=>$delivery_num,
				
				'set_product'			=>$set_product
			);
		}
	}
	
	return $order_product;
}

function getTmpOrder($db,$order_status,$order_code) {
	$tmp_order = null;
	
	$order_table = getOrderTable($order_status,true);
		
	$select_tmp_order_sql = "
		SELECT
			IDX					AS TMP_IDX,
			ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE
		FROM
			".$order_table['info']."
		WHERE
			OT.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_tmp_order_sql);
	
	foreach($db->fetch() as $tmp_data) {
		$tmp_order = array(
			'tmp_idx'				=>$tmp_data['TMP_IDX'],
			'order_update_code'		=>$tmp_data['ORDER_UPDATE_CODE']
		);
	}
	
	return $tmp_order;
}

//[임시 주문 상태별 상품 테이블] 상품 단위 합계 취득
function getTmpProductByStatus($db,$order_status,$order_code) {
	$tmp_product = array();
	
	$order_table = getOrderTable($order_status,true);
	
	$column_order_update_code = "";
	$column_prev_option_name = "";
	
	if ($order_status == "OEX") {
		$column_prev_option_name = "
			(
				SELECT
					S_OO.OPTION_NAME
				FROM
					ORDERSHEET_OPTION S_OO
				WHERE
					S_OO.IDX = OT.PREV_OPTION_IDX
			)							AS PREV_OPTION_NAME,
		";
	} else {
		$column_prev_option_name = "
			NULL						AS PREV_OPTION_NAME,
		";
	}
	
	$select_tmp_product_sql = "
		SELECT
			OT.ORDER_CODE			AS ORDER_CODE,
			OT.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			OT.ORDER_STATUS			AS ORDER_STATUS,
			
			OT.PRODUCT_TYPE			AS PRODUCT_TYPE,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)						AS IMG_LOCATION,
			OT.PRODUCT_NAME			AS PRODUCT_NAME,
			PR.COLOR				AS COLOR,
			PR.COLOR_RGB			AS COLOR_RGB,
			
			OT.OPTION_NAME			AS OPTION_NAME,
			".$column_prev_option_name."
			
			SUM(OT.PRODUCT_QTY)		AS PRODUCT_QTY,
			OT.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			".$order_table['product']." OT
			LEFT JOIN SHOP_PRODUCT PR ON
			OT.PRODUCT_IDX = PR.IDX
		WHERE
			OT.ORDER_CODE = '".$order_code."' AND
			OT.PRODUCT_TYPE NOT IN ('D','M')
		GROUP BY
			OT.ORDER_PRODUCT_CODE
	";
	
	$db->query($select_tmp_product_sql);
	
	foreach($db->fetch() as $product_data) {
		$tmp_product[] = array(
			'order_code'			=>$product_data['ORDER_CODE'],
			'order_product_code'	=>$product_data['ORDER_PRODUCT_CODE'],
			'order_update_code'		=>$product_data['ORDER_UPDATE_CODE'],
			'order_status'			=>$product_data['ORDER_STATUS'],
			'txt_order_status'		=>setTxtOrderStatus($product_data['ORDER_STATUS']),
			
			'product_type'			=>$product_data['PRODUCT_TYPE'],
			'img_location'			=>$product_data['IMG_LOCATION'],
			'product_name'			=>$product_data['PRODUCT_NAME'],
			'color'					=>$product_data['COLOR'],
			'color_rgb'				=>$product_data['COLOR_RGB'],
			
			'option_name'			=>$product_data['OPTION_NAME'],
			'prev_option_name'		=>$product_data['PREV_OPTION_NAME'],
			
			'product_qty'			=>$product_data['PRODUCT_QTY'],
			'product_price'			=>$product_data['PRODUCT_PRICE'],
			'txt_product_price'			=>number_format($product_data['PRODUCT_PRICE'])
		);
	}
	
	return $tmp_product;
}

//세트상품 조회
function getSetProduct($db,$order_product_code) {
	$set_product_info = array();
	//$parent_idx = $db->get('SHOP_PRODUCT','PRODUCT_TYPE="S" AND PRODUCT_CODE = ? AND DEL_FLG = FALSE', array($order_product_code))[0]['IDX'];
	$select_set_product_sql = "
		SELECT
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = OP.PRODUCT_IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)								AS IMG_LOCATION,
			OP.PRODUCT_NAME					AS PRODUCT_NAME,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			OP.OPTION_NAME					AS OPTION_NAME
		FROM
			ORDER_PRODUCT OP
			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
		WHERE
			OP.PARENT_IDX = (
				SELECT
					S_OP.IDX
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					ORDER_PRODUCT_CODE = '".$order_product_code."'
			)
	";
	
	$db->query($select_set_product_sql);
	
	foreach($db->fetch() as $set_data) {
		$set_product_info[] = array(
			'parent_code'		=>$order_product_code,
			'img_location'		=>$set_data['IMG_LOCATION'],
			'product_name'		=>$set_data['PRODUCT_NAME'],
			'color'				=>$set_data['COLOR'],
			'color_rgb'			=>$set_data['COLOR_RGB'],
			'option_name'		=>$set_data['OPTION_NAME']
		);
	}
	
	return $set_product_info;
}

//각 상품별 [주문취소],[주문교환],[주문반품] 사유 취득
function getOrderReason($db,$country,$order_status,$order_product_code) {
	$order_reason = array();
	
	$order_table = getOrderTable($order_status,false);
	
	$select_order_reason_sql = "
		SELECT
			OT.DEPTH1_IDX		AS DEPTH1_IDX,
			OT.DEPTH2_IDX		AS DEPTH2_IDX,
			OT.REASON_MEMO		AS REASON_MEMO
		FROM
			".$order_table['product']." OT
		WHERE
			OT.ORDER_PROUCT_CODE = '".$order_product_code."'
	";
	
	$db->query($select_order_reason_sql);
	
	foreach($db->fetch() as $reason_data) {
		$depth1_idx = $reason_data['DEPTH1_IDX'];
		$depth2_idx = $reason_data['DEPTH2_IDX'];
		
		$reason_txt = array();
		
		if (!empty($depth1_idx) && !empty($depth2_idx)) {
			$select_reason_txt_sql = "
				SELECT
					D1.REASON_TXT		AS REASON1_TXT,
					D2.REASON_TXT		AS REASON2_TXT
				FROM
					REASON_DEPTH_1 D1
					LEFT JOIN REASON_DEPTH_2 D2 ON
					D1.IDX = D2.DEPTH_1_IDX
				WHERE
					D2.COUNTRY = '".$country."' AND
					D2.IDX = ".$depth2_idx." AND
					D2.REASON_TYPE = '".$order_status."' AND
					D2.DEL_FLG = FALSE
			";
			
			$db->query($select_reason_txt_sql);
			
			foreach($db->fetch() as $txt_data) {
				$reason_txt = array(
					'reason1_txt'		=>$txt_data['REASON1_TXT'],
					'reason2_txt'		=>$txt_data['REASON2_TXT']
				);
			}
		}
		
		$order_reason = array(
			'reason1_txt'		=>$reason_txt['reason1_txt'],
			'reason2_txt'		=>$reason_txt['reason2_txt'],
			'reason_memo'		=>$reason_data['REASON_MEMO']
		);
	}
	
	return $order_reason;
}

function checkOrderProductCancelType($db,$order_code) {
	$check_result = "IND";
	
	$cnt_PRD = $db->count("ORDER_PRODUCT","ORDER_CODE = '".$order_code."' AND PRODUCT_TYPE NOT IN ('V','D') AND PRODUCT_QTY > 0 AND PARENT_IDX = 0");
	
	$tmp_info = array();
	
	$select_tmp_product_sql = "
		SELECT
			TMP.ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE,
			SUM(TMP.PRODUCT_QTY)		AS PRODUCT_QTY
		FROM
			(
				(
					SELECT
						TE.ORDER_PRODUCT_CODE,
						TE.PRODUCT_QTY
					FROM
						TMP_ORDER_PRODUCT_EXCHANGE TE
					WHERE
						TE.ORDER_CODE = '".$order_code."'
				) UNION (
					SELECT
						TR.ORDER_PRODUCT_CODE,
						TR.PRODUCT_QTY
					FROM
						TMP_ORDER_PRODUCT_REFUND TR
					WHERE
						TR.ORDER_CODE = '".$order_code."'
				)
			) AS TMP
		ORDER BY
			TMP.ORDER_PRODUCT_CODE
	";
	
	$db->query($select_tmp_product_sql);
	
	foreach($db->fetch() as $tmp_data) {
		if ($tmp_data['ORDER_PRODUCT_CODE'] != null) {
			$tmp_info[$tmp_data['ORDER_PRODUCT_CODE']] = $tmp_data['PRODUCT_QTY'];
		}
	}
	
	$select_order_product_sql = "
		SELECT
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.PRODUCT_QTY			AS PRODUCT_QTY
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_order_product_sql);
	
	$check_cnt = 0;
	
	foreach($db->fetch() as $product_data) {
		$order_product_code = $product_data['ORDER_PRODUCT_CODE'];
		$product_qty = $product_data['PRODUCT_QTY'];
		
		if (isset($tmp_info[$order_product_code])) {
			$product_qty -= $tmp_info[$order_product_code];
		}
		
		if ($product_qty == 0) {
			$check_cnt++;
		}
	}
	
	if ($check_cnt == $cnt_PRD) {
		$check_result = "ALL";
	}
	
	return $check_result;
}

//주문상태코드 변환
function setTxtOrderStatus($param) {
	$order_status = "";
	
	$country = null;
	if (isset($_SESSION['COUNTRY'])) {
		$country = $_SESSION['COUNTRY'];
	}
	
	if ($country != null) {
		switch ($param) {
			case "PCP" :
				switch ($country) {
					case "KR" :
						$order_status = "결제완료";
						break;
					
					case "EN" :
						$order_status = "Payment Completed";
						break;
					
					case "CN" :
						$order_status = "支付完成";
						break;
				}
				break;
			
			case "PPR" :
				switch ($country) {
					case "KR" :
						$order_status = "상품준비";
						break;
					
					case "EN" :
						$order_status = "Product Preparation";
						break;
					
					case "CN" :
						$order_status = "商品准备";
						break;
				}
				break;
			
			case "POP" :
				switch ($country) {
					case "KR" :
						$order_status = "프리오더 준비";
						break;
					
					case "EN" :
						$order_status = "Pre-order Preparation";
						break;
					
					case "CN" :
						$order_status = "预购准备";
						break;
				}
				break;
			
			case "POD" :
				switch ($country) {
					case "KR" :
						$order_status = "프리오더 상품 생산";
						break;
					
					case "EN" :
						$order_status = "Pre-order Product Production";
						break;
					
					case "CN" :
						$order_status = "预购商品生产";
						break;
				}
				break;
			
			case "DPR" :
				switch ($country) {
					case "KR" :
						$order_status = "배송준비";
						break;
					
					case "EN" :
						$order_status = "Shipping Preparation";
						break;
					
					case "CN" :
						$order_status = "配送准备";
						break;
				}
				break;
			
			case "DPG" :
				switch ($country) {
					case "KR" :
						$order_status = "배송중";
						break;
					
					case "EN" :
						$order_status = "Shipping in Progress";
						break;
					
					case "CN" :
						$order_status = "运送中";
						break;
				}
				break;
			
			case "DCP" :
				switch ($country) {
					case "KR" :
						$order_status = "배송완료";
						break;
					
					case "EN" :
						$order_status = "Shipping Completed";
						break;
					
					case "CN" :
						$order_status = "运送完成";
						break;
				}
				break;
			
			case "OCC" :
				switch ($country) {
					case "KR" :
						$order_status = "주문취소";
						break;
					
					case "EN" :
						$order_status = "Order Canceled";
						break;
					
					case "CN" :
						$order_status = "订单取消";
						break;
				}
				break;
			
			case "OET" :
				switch ($country) {
					case "KR" :
						$order_status = "반송대기";
						break;
					
					case "EN" :
						$order_status = "Wating for return";
						break;
					
					case "CN" :
						$order_status = "等待退回";
						break;
				}
				break;
			
			case "OEH" :
				switch ($country) {
					case "KR" :
						$order_status = "수거중";
						break;
					
					case "EN" :
						$order_status = "Product collection in progress";
						break;
					
					case "CN" :
						$order_status = "产品收集正在进行中";
						break;
				}
				break;
			
			case "OEX" :
				switch ($country) {
					case "KR" :
						$order_status = "수거완료";
						break;
					
					case "EN" :
						$order_status = "Collection completed";
						break;
					
					case "CN" :
						$order_status = "回收完毕";
						break;
				}
				break;
			
			case "OEP" :
				switch ($country) {
					case "KR" :
						$order_status = "교환완료";
						break;
					
					case "EN" :
						$order_status = "Exchange completed";
						break;
					
					case "CN" :
						$order_status = "订单换货完成";
						break;
				}
				break;
			
			case "OEE" :
				switch ($country) {
					case "KR" :
						$order_status = "교환반려";
						break;
					
					case "EN" :
						$order_status = "Exchange rejected";
						break;
					
					case "CN" :
						$order_status = "订单换货拒绝";
						break;
				}
				break;
			
			case "ORT" :
				switch ($country) {
					case "KR" :
						$order_status = "반송대기";
						break;
					
					case "EN" :
						$order_status = "Wating for return";
						break;
					
					case "CN" :
						$order_status = "等待退回";
						break;
				}
				break;
			
			case "ORH" :
				switch ($country) {
					case "KR" :
						$order_status = "수거중";
						break;
					
					case "EN" :
						$order_status = "Product collection in progress";
						break;
					
					case "CN" :
						$order_status = "产品收集正在进行中";
						break;
				}
				break;
			
			case "ORF" :
				switch ($country) {
					case "KR" :
						$order_status = "수거완료";
						break;
					
					case "EN" :
						$order_status = "Collection completed";
						break;
					
					case "CN" :
						$order_status = "回收完毕";
						break;
				}
				break;
			
			case "ORP" :
				switch ($country) {
					case "KR" :
						$order_status = "반품완료";
						break;
					
					case "EN" :
						$order_status = "Return completed";
						break;
					
					case "CN" :
						$order_status = "订单退货完成";
						break;
				}
				break;
			
			case "ORE" :
				switch ($country) {
					case "KR" :
						$order_status = "반품반려";
						break;
					
					case "EN" :
						$order_status = "Return Rejected";
						break;
					
					case "CN" :
						$order_status = "订单退货拒绝";
						break;
				}
				break;
		}
	}
	
	return $order_status;
}

function setTxtIssueName($param) {
	$txt_issue_name = "";
	
	switch ($param) {
		case "3K":
			$txt_issue_name = "기업 BC";
			break;

		case "46":
			$txt_issue_name = "광주은행";
			break;

		case "71":
			$txt_issue_name = "롯데카드";
			break;

		case "30":
			$txt_issue_name = "KDB산업은행";
			break;

		case "31":
			$txt_issue_name = "BC카드";
			break;

		case "51":
			$txt_issue_name = "삼성카드";
			break;

		case "38":
			$txt_issue_name = "새마을금고";
			break;

		case "41":
			$txt_issue_name = "신한카드";
			break;

		case "62":
			$txt_issue_name = "신협";
			break;

		case "36":
			$txt_issue_name = "씨티카드";
			break;

		case "33":
			$txt_issue_name = "우리BC카드(BC 매입)";
			break;

		case "W1":
			$txt_issue_name = "우리카드(우리 매입)";
			break;

		case "37":
			$txt_issue_name = "우체국예금보험";
			break;

		case "39":
			$txt_issue_name = "저축은행중앙회";
			break;

		case "35":
			$txt_issue_name = "전북은행";
			break;

		case "42":
			$txt_issue_name = "제주은행";
			break;

		case "15":
			$txt_issue_name = "카카오뱅크";
			break;

		case "3A":
			$txt_issue_name = "케이뱅크";
			break;

		case "24":
			$txt_issue_name = "토스뱅크";
			break;

		case "21":
			$txt_issue_name = "하나카드";
			break;

		case "61":
			$txt_issue_name = "현대카드";
			break;

		case "11":
			$txt_issue_name = "KB국민카드";
			break;

		case "91":
			$txt_issue_name = "NH농협카드";
			break;

		case "34":
			$txt_issue_name = "Sh수협은행";
			break;
	}
	
	return $txt_issue_name;
}

function addOrderLog($db,$order_status,$order_update_code,$session_id) {
	$order_table = getOrderTable($order_status,false);
	
	$insert_order_info_log_sql = "
		INSERT INTO
			ORDER_INFO_LOG
		(
			COUNTRY,
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			ORDER_TITLE,
			
			PRICE_PRODUCT,
			PRICE_MILEAGE,
			PRICE_CHARGE,
			PRICE_DISCOUNT,
			PRICE_DELIVERY,
			DELIVERY_RETURN,
			PRICE_CANCEL,
			PRICE_REFUND,
			
			PG_MID,
			PG_PAYMENT,
			PG_PAYMENT_KEY,
			PG_ISSUE_CODE,
			PG_CARD_NUMBER,
			PG_CURRENCY,
			PG_REMAIN_PRICE,
			
			CREATER
		)
		SELECT
			OT.COUNTRY				AS COUNTRY,
			OT.ORDER_CODE			AS ORDER_CODE,
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			OT.ORDER_STATUS			AS ORDER_STATUS,
			OT.ORDER_TITLE			AS ORDER_TITLE,
			
			OT.PRICE_PRODUCT		AS PRICE_PRODUCT,
			OT.PRICE_MILEAGE_POINT	AS PRICE_MILEAGE,
			OT.PRICE_CHARGE_POINT	AS PRICE_CHARGE,
			OT.PRICE_DISCOUNT		AS PRICE_DISCOUNT,
			OT.PRICE_DELIVERY		AS PRICE_DELIVERY,
			OT.DELIVERY_RETURN		AS DELIVERY_RETURN,
			OT.PRICE_CANCEL			AS PRICE_CANCEL,
			OT.PRICE_REFUND			AS PRICE_REFUND,
			
			OI.PG_MID				AS PG_MID,
			OI.PG_PAYMENT			AS PG_PAYMENT,
			OI.PG_PAYMENT_KEY		AS PG_PAYMENT_KEY,
			OI.PG_ISSUE_CODE		AS PG_ISSUE_CODE,
			OI.PG_CARD_NUMBER		AS PG_CARD_NUMBER,
			OI.PG_CURRENCY			AS PG_CURRENCY,
			OI.PG_REMAIN_PRICE		AS PG_REMAIN_PRICE,
			
			'".$session_id."'		AS CREATER
		FROM
			".$order_table['info']." OT
			LEFT JOIN ORDER_INFO OI ON
			OT.ORDER_CODE = OI.ORDER_CODE
		WHERE
			OT.ORDER_UPDATE_CODE = '".$order_update_code."'
	";
	
	$db->query($insert_order_info_log_sql);
	
	$insert_order_product_log_sql = "
		INSERT INTO
			ORDER_PRODUCT_LOG
		(
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			
			PRODUCT_IDX,
			PRODUCT_CODE,
			PRODUCT_NAME,
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			PRODUCT_QTY,
			
			PRODUCT_PRICE,
			MILEAGE_PRICE,
			CHARGE_PRICE,
			DISCOUNT_PRICE,
			DELIVERY_PRICE,
			DELIVERY_RETURN,
			CANCEL_PRICE,
			REFUND_PRICE,
			
			CREATER
		)
		SELECT
			OT.ORDER_CODE			AS ORDER_CODE,
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			OT.ORDER_STATUS			AS ORDER_STATUS,
			
			OT.PRODUCT_IDX			AS PRODUCT_IDX,
			OT.PRODUCT_CODE			AS PRODUCT_CODE,
			OT.PRODUCT_NAME			AS PRODUCT_NAME,
			OT.OPTION_IDX			AS OPTION_IDX,
			OT.BARCODE				AS BARCODE,
			OT.OPTION_NAME			AS OPTION_NAME,
			OT.PRODUCT_QTY			AS PRODUCT_QTY,
			
			OT.PRODUCT_PRICE		AS PRODUCT_PRICE,
			OT.MILEAGE_PRICE		AS MILEAGE_PRICE,
			OT.CHARGE_PRICE			AS CHRAGE_PRICE,
			OT.DISCOUNT_PRICE		AS DISCOUNT_PRICE,
			OT.DELIVERY_PRICE		AS DELIVERY_PRICE,
			OT.DELIVERY_RETURN		AS DELIVERY_RETURN,
			OT.CANCEL_PRICE			AS CANCEL_PRICE,
			OT.REFUND_PRICE			AS REFUND_PRICE,
			
			CREATER					AS CREATER
		FROM
			".$order_table['product']." OT
		WHERE
			OT.ORDER_UPDATE_CODE = '".$order_update_code."'
	";
	
	$db->query($insert_order_product_log_sql);
}

function addOrderLogByIdx($db,$order_status,$order_idx,$session_id) {
	$order_table = getOrderTable($order_status,false);
	
	$column_info_price = "";
	$column_product_price = "";
	
	if ($order_status == "OEX") {
		$column_info_price = "
			OT.PRICE_PRODUCT		AS PRICE_PRODUCT,
			0						AS PRICE_MILEAGE,
			0						AS PRICE_CHARGE,
			0						AS PRICE_DISCOUNT,
			OT.PRICE_DELIVERY		AS PRICE_DELIVERY,
			0						AS DELIVERY_RETURN,
			0						AS PRICE_CANCEL,
			0						AS PRICE_REFUND,
		";
		
		$column_product_price = "
			OT.PRODUCT_PRICE		AS PRODUCT_PRICE,
			0						AS MILEAGE_PRICE,
			0						AS CHRAGE_PRICE,
			0						AS DISCOUNT_PRICE,
			OT.DELIVERY_PRICE		AS DELIVERY_PRICE,
			0						AS DELIVERY_RETURN,
			0						AS CANCEL_PRICE,
			0						AS REFUND_PRICE,
		";
	} else if ($order_status == "ORF") {
		$column_info_price = "
			OT.PRICE_PRODUCT		AS PRICE_PRODUCT,
			OT.PRICE_MILEAGE_POINT	AS PRICE_MILEAGE,
			OT.PRICE_CHARGE_POINT	AS PRICE_CHARGE,
			OT.PRICE_DISCOUNT		AS PRICE_DISCOUNT,
			OT.PRICE_DELIVERY		AS PRICE_DELIVERY,
			OT.DELIVERY_RETURN		AS DELIVERY_RETURN,
			OT.PRICE_CANCEL			AS PRICE_CANCEL,
			OT.PRICE_REFUND			AS PRICE_REFUND,
		";
		
		$column_product_price = "
			OT.PRODUCT_PRICE		AS PRODUCT_PRICE,
			OT.MILEAGE_PRICE		AS MILEAGE_PRICE,
			OT.CHARGE_PRICE			AS CHRAGE_PRICE,
			OT.DISCOUNT_PRICE		AS DISCOUNT_PRICE,
			OT.DELIVERY_PRICE		AS DELIVERY_PRICE,
			OT.DELIVERY_RETURN		AS DELIVERY_RETURN,
			OT.CANCEL_PRICE			AS CANCEL_PRICE,
			OT.REFUND_PRICE			AS REFUND_PRICE,
		";
	}
	
	$insert_order_info_log_sql = "
		INSERT INTO
			ORDER_INFO_LOG
		(
			COUNTRY,
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			ORDER_TITLE,
			
			PRICE_PRODUCT,
			PRICE_MILEAGE,
			PRICE_CHARGE,
			PRICE_DISCOUNT,
			PRICE_DELIVERY,
			DELIVERY_RETURN,
			PRICE_CANCEL,
			PRICE_REFUND,
			
			PG_MID,
			PG_PAYMENT,
			PG_PAYMENT_KEY,
			PG_ISSUE_CODE,
			PG_CARD_NUMBER,
			PG_CURRENCY,
			PG_REMAIN_PRICE,
			
			CREATER
		)
		SELECT
			OT.COUNTRY				AS COUNTRY,
			OT.ORDER_CODE			AS ORDER_CODE,
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			OT.ORDER_STATUS			AS ORDER_STATUS,
			OT.ORDER_TITLE			AS ORDER_TITLE,
			
			".$column_info_price."
			
			OI.PG_MID				AS PG_MID,
			OI.PG_PAYMENT			AS PG_PAYMENT,
			OI.PG_PAYMENT_KEY		AS PG_PAYMENT_KEY,
			OI.PG_ISSUE_CODE		AS PG_ISSUE_CODE,
			OI.PG_CARD_NUMBER		AS PG_CARD_NUMBER,
			OI.PG_CURRENCY			AS PG_CURRENCY,
			OI.PG_REMAIN_PRICE		AS PG_REMAIN_PRICE,
			
			'".$session_id."'		AS CREATER
		FROM
			".$order_table['info']." OT
			LEFT JOIN ORDER_INFO OI ON
			OT.ORDER_CODE = OI.ORDER_CODE
		WHERE
			OT.IDX = ".$order_idx."
	";
	
	$db->query($insert_order_info_log_sql);
	
	$insert_order_product_log_sql = "
		INSERT INTO
			ORDER_PRODUCT_LOG
		(
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			
			PRODUCT_IDX,
			PRODUCT_CODE,
			PRODUCT_NAME,
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			PRODUCT_QTY,
			
			PRODUCT_PRICE,
			MILEAGE_PRICE,
			CHARGE_PRICE,
			DISCOUNT_PRICE,
			DELIVERY_PRICE,
			DELIVERY_RETURN,
			CANCEL_PRICE,
			REFUND_PRICE,
			
			CREATER
		)
		SELECT
			OT.ORDER_CODE			AS ORDER_CODE,
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			OT.ORDER_STATUS			AS ORDER_STATUS,
			
			OT.PRODUCT_IDX			AS PRODUCT_IDX,
			OT.PRODUCT_CODE			AS PRODUCT_CODE,
			OT.PRODUCT_NAME			AS PRODUCT_NAME,
			OT.OPTION_IDX			AS OPTION_IDX,
			OT.BARCODE				AS BARCODE,
			OT.OPTION_NAME			AS OPTION_NAME,
			OT.PRODUCT_QTY			AS PRODUCT_QTY,
			
			".$column_product_price."
			
			CREATER					AS CREATER
		FROM
			".$order_table['product']." OT
		WHERE
			OT.ORDER_IDX = ".$order_idx."
	";
	
	$db->query($insert_order_product_log_sql);
}

?>