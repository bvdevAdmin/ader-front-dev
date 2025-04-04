<?php
/*
 +=============================================================================
 | 
 | 공통함수 - 조회 및 구매 제한 체크
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

/* (공통) 구매가능수량 체크처리 */
function checkPurchaseableQty($db,$product_idx,$option_idx,$param_qty) {
	$check_result = false;
	
	$where = " 1=1 ";
	
	if ($product_idx > 0) {
		$where .= " AND (V_ST.PRODUCT_IDX = ".$product_idx.") ";
	}
	
	if ($option_idx > 0) {
		$where .= " AND (V_ST.OPTION_IDX = ".$option_idx.") ";
	}
	
	$select_product_stock_sql = "
		SELECT
			SUM(
				V_ST.PURCHASEABLE_QTY
			)					AS PURCHASEABLE_QTY,
			SUM(
				V_ST.TMP_QTY
			)					AS TMP_QTY
		FROM
			V_STOCK V_ST
		WHERE
			".$where."
	";
	
	$db->query($select_product_stock_sql);
	
	foreach($db->fetch() as $data) {
		$limit_qty = $data['PURCHASEABLE_QTY'] - $data['TMP_QTY'];
		//$limit_qty = $data['PURCHASEABLE_QTY'];
		
		if (($limit_qty) > 0) {
			if ($param_qty > 0) {
				if ($param_qty <= $limit_qty) {
					$check_result = true;
				}
			} else {
				$check_result = true;
			}
		}
	}
	
	return $check_result;
}

/* (공통) 상품 체크 전 항목 정보 조회처리 */
function getProductInfo($db,$idx_type,$param_idx) {
	$product_info = array();
	
	if ($idx_type == "PRD") {
		/* 조회 기준 : [독립몰 상품] 테이블 */
		$where = "
			PR.IDX = ".$param_idx."
		";
	} else if ($idx_type == "WSH") {
		/* 조회 기준 : [위시 리스트] 테이블 */
		$where = "
			PR.IDX = (
				SELECT
					WL.PRODUCT_IDX
				FROM
					WHISH_LIST WL
				WHERE
					WL.IDX = ".$param_idx."
			)
		";
	} else if ($idx_type == "BSK") {
		/* 조회 기준 : [쇼핑백] 테이블 */
		$where = "
			PR.IDX = (
				SELECT
					BI.PRODUCT_IDX
				FROM
					BASKET_INFO BI
				WHERE
					BI.IDX = ".$param_idx."
			)
		";
	}
	
	$select_product_limit_sql = "
		SELECT
			PR.IDX							AS PRODUCT_IDX,
			PR.MILEAGE_FLG					AS MILEAGE_FLG,
			PR.EXCLUSIVE_FLG				AS EXCLUSIVE_FLG,
			PR.LIMIT_MEMBER					AS LIMIT_MEMBER,
			PR.LIMIT_QTY_FLG				AS LIMIT_QTY_FLG,
			PR.LIMIT_PRODUCT_QTY			AS LIMIT_PRODUCT_QTY,
			PR.LIMIT_ID_FLG					AS LIMIT_ID_FLG,
			PR.REORDER_CNT					AS REORDER_CNT,
			PR.SALE_FLG						AS SALE_FLG
		FROM
			SHOP_PRODUCT PR
		WHERE
			".$where."
	";
	
	$db->query($select_product_limit_sql);
	
	foreach($db->fetch() as $product_data) {
		$product_info = array(
			'product_idx'				=>$product_data['PRODUCT_IDX'],
			'mileage_flg'				=>$product_data['MILEAGE_FLG'],
			'exclusive_flg'				=>$product_data['EXCLUSIVE_FLG'],
			'limit_member'				=>$product_data['LIMIT_MEMBER'],
			'limit_purchase_qty_flg'	=>$product_data['LIMIT_QTY_FLG'],
			'limit_product_qty'			=>$product_data['LIMIT_PRODUCT_QTY'],
			'limit_id_flg'				=>$product_data['LIMIT_ID_FLG'],
			'reorder_cnt'				=>$product_data['REORDER_CNT'],
			'sale_flg'					=>$product_data['SALE_FLG']
		);
	}
	
	return $product_info;
}

/* (공통) 상품별 판매여부 체크처리 */
function checkProductSaleFlg($db,$idx_type,$param_idx) {
	$check_result = array();
	
	/* (공통) 상품 체크 전 항목 정보 조회처리 */
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$sale_flg = false;
	if (isset($product_info['sale_flg']) && $product_info['sale_flg'] == true) {
		$sale_flg = true;
	}
	
	$check_result['result'] = $sale_flg;
	
	return $check_result;
}

/* (공통) 상품별 구매 제한 수량 체크처리 */
function checkQtyLimit($db,$country,$member_idx,$idx_type,$param_idx,$option_idx,$product_qty) {
	$check_result = array();
	
	$check_result['result'] = false;
	
	/* (공통) 상품 체크 전 항목 정보 조회처리 */
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$product_idx = $product_info['product_idx'];
	$limit_flg = $product_info['limit_purchase_qty_flg'];	//[독립몰 상품] 테이블 구매 제한 수량 설정 여부 플래그
	$limit_qty = $product_info['limit_product_qty'];		//[독립몰 상품] 테이블 구매 제한 수량
	
	if ($limit_flg == false) {
		$check_result['result'] = true;
	} else if ($limit_flg == true) {
		$check_cnt = 0;
		
		$basket_qty = 0;
		$order_qty = 0;
		$total_qty = 0;
		
		$option_qty = 0;
		
		/* 쇼핑백 동일상품 수량 조회 */
		$cnt_basket = $db->count("BASKET_INFO","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND DEL_FLG = FALSE ");
		if ($cnt_basket > 0) {
			$basket_qty = getParamBasketQty($db,$country,$member_idx,$product_idx);
		}
		
		/* 주문 상품수량 조회 */
		$cnt_order = $db->count("ORDER_PRODUCT OP LEFT JOIN ORDER_INFO OI ON OP.ORDER_IDX = OI.IDX","OI.COUNTRY = '".$country."' AND OI.MEMBER_IDX = ".$member_idx." AND OP.PRODUCT_IDX = ".$product_idx);
		if ($cnt_order > 0) {
			$order_qty = getParamOrderQty($db,$country,$member_idx,$product_idx);
		}
		
		$total_qty = intval($basket_qty + $product_qty + $order_qty);
		
		/* 현재 체크중인 상품의 상품별 구매제한수량 체크처리 */
		if ($product_qty > $limit_qty) {
			$mapping_arr[] = array(
				'key' 		=> '<limit_qty>',
				'value' 	=> $limit_qty
			);
			$check_result['result'] = false;
			$check_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0092', $mapping_arr);
			
			return $check_result;
		} else {
			$check_cnt++;
		}
		
		/* 동일한 쇼핑백 상품의 상품별 구매제한수량 체크처리 */
		if ($basket_qty > $limit_qty) {
			$mapping_arr[] = array(
				'key' 		=> '<limit_qty>',
				'value' 	=> $limit_qty
			);
			$check_result['result'] = false;
			$check_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0092', $mapping_arr);
			
			return $check_result;
		} else {
			$check_cnt++;
		}
		
		/* 총 합계수량의 상품별 구매제한수량 체크처리  */
		if ($total_qty > $limit_qty) {
			$mapping_arr[] = array(
				'key' 		=> '<limit_qty>',
				'value' 	=> $limit_qty
			);
			
			$check_result['result'] = false;
			$check_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0092', $mapping_arr);
			
			return $check_result;
		} else {
			$check_cnt++;
		}
		
		/* 옵션별 구매 제한 수량 설정 여부 체크 */
		$cnt_option = $db->count("PRODUCT_OPTION","PRODUCT_IDX = ".$product_idx." AND OPTION_IDX = ".$option_idx);
		if ($cnt_option > 0) {
			$option_qty = getParamOptionQty($db,$product_idx,$option_idx);
			
			/* 옵션별 구매제한수량 체크처리 */
			if ($product_qty > $option_qty || $basket_qty > $option_qty || $total_qty > $option_qty) {
				$mapping_arr[] = array(
					'key' 		=> '<option_qty>',
					'value' 	=> $option_qty
				);
				
				$check_result['result'] = false;
				$check_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0091', $mapping_arr);
				
				return $check_result;
			} else {
				$check_cnt++;
			}
		}
		
		if ($check_cnt == 4) {
			$check_result['result'] = true;
		}
	}
	
	return $check_result;
}

/* 동일한 쇼핑백 상품의 수량 조회 */
function getParamBasketQty($db,$country,$member_idx,$product_idx) {
	$basket_qty = 0;
	
	$select_basket_qty_sql = "
		SELECT
			SUM(BI.PRODUCT_QTY)		AS BASKET_QTY
		FROM
			BASKET_INFO BI
		WHERE
			BI.COUNTRY = '".$country."' AND
			BI.MEMBER_IDX = ".$member_idx." AND
			BI.PRODUCT_IDX = ".$product_idx." AND
			BI.DEL_FLG = FALSE
	";
	
	$db->query($select_basket_qty_sql);
	
	foreach($db->fetch() as $data) {
		$basket_qty = $data['BASKET_QTY'];
	}
	
	return $basket_qty;
}

/* 동일한 주문 상품의 수량 조회 */
function getParamOrderQty($db,$country,$member_idx,$product_idx) {
	$order_qty = 0;
	
	$select_order_qty_sql = "
		SELECT
			SUM(OP.PRODUCT_QTY)		AS ORDER_QTY
		FROM
			ORDER_PRODUCT OP
			LEFT JOIN ORDER_INFO OI ON
			OP.ORDER_IDX = OI.IDX
		WHERE
			OI.COUNTRY = '".$country."' AND
			OI.MEMBER_IDX = ".$member_idx." AND
			OP.PRODUCT_IDX = ".$product_idx."
	";
	
	$db->query($select_order_qty_sql);
	
	foreach($db->fetch() as $data) {
		$order_qty = $data['ORDER_QTY'];
	}
	
	return $order_qty;
}

/* 옵션별 구매 제한 수량 조회 */
function getParamOptionQty($db,$product_idx,$option_idx) {
	$option_qty = 0;
	
	$select_limit_option_qty_sql = "
		SELECT
			PO.QTY					AS OPTION_QTY
		FROM
			PRODUCT_OPTION PO
		WHERE
			PO.PRODUCT_IDX = ".$product_idx." AND
			PO.OPTION_IDX = ".$option_idx."
	";
	
	$db->query($select_limit_option_qty_sql);
	
	foreach($db->fetch() as $data_option) {
		$option_qty = $data_option['OPTION_QTY'];
	}
	
	return $option_qty;
}

/* (공통) 상품별 적립금 사용 가능 여부 체크 */
function checkProductMileageFlg($db,$idx_type,$param_idx) {
	$mileage_flg = false;
	
	/* (공통) 상품 체크 전 항목 정보 조회처리 */
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$mileage_flg = $product_info['mileage_flg'];
	
	return $mileage_flg;
}

/* (공통) 상품별 단독구매 제한 여부 체크 */
function checkProductExclusiveFlg($db,$idx_type,$param_idx) {
	$exclusive_flg = false;
	
	/* (공통) 상품 체크 전 항목 정보 조회처리 */
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$exclusive_flg = $product_info['exclusive_flg'];
	
	return $exclusive_flg;
}

/* (공통) 상품별 구매 회원 제한 체크 */
function checkProductLevel($db,$member_level,$idx_type,$param_idx) {
	$check_result = array();
	$check_result['result'] = false;
	
	/* (공통) 상품 체크 전 항목 정보 조회처리 */
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$limit_level = $product_info['limit_member'];
	
	if ($limit_level != "" || $limit_level != null) {
		$limit_level = explode(",",$limit_level);
		
		if (count($limit_level) > 0) {
			if (in_array("0",$limit_level)) {
				$check_result['result'] = true;
			} else {
				if (in_array($member_level,$limit_level)) {
					$check_result['result'] = true;
				}
			}
		}
	}
	
	return $check_result;
}

/* (공통) 상품 진열 페이지별 조회 가능 한 회원등급 체크 */
function checkListLevel($db,$member_level,$page_idx) {
	$check_result = array();
	
	$check_result['result'] = false;
	
	$select_limit_level_sql = "
		SELECT
			PP.DISPLAY_MEMBER_LEVEL		AS LIMIT_LEVEL
		FROM
			PAGE_PRODUCT PP
		WHERE
			PP.IDX = ".$page_idx."
	";
	
	$db->query($select_limit_level_sql);
	
	$limit_level = array();
	foreach($db->fetch() as $data) {
		if (isset($data['LIMIT_LEVEL'])) {
			$limit_level = explode(",",$data['LIMIT_LEVEL']);
		}
	}
	
	if (count($limit_level) > 0) {
		if (in_array("0",$limit_level)) {
			$check_result['result'] = true;
		} else {
			if (in_array($member_level,$limit_level)) {
				$check_result['result'] = true;
			}
		}
	}
	
	return $check_result;
}

/* (공통) 상품별 ID당 구매 제한수량 체크 */
function checkIdReorder($db,$country,$member_idx,$idx_type,$param_idx) {
	$check_result = array();
	
	$check_result['result'] = false;
	
	/* (공통) 상품 체크 전 항목 정보 조회처리 */
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$product_idx = $product_info['product_idx'];
	$limit_id_flg = $product_info['limit_id_flg'];
	$reorder_cnt = $product_info['reorder_cnt'];
	
	if ($limit_id_flg == true) {
		$order_cnt = $db->count(
			"
				ORDER_INFO OI
				LEFT JOIN ORDER_PRODUCT OP ON
				OI.IDX = OP.ORDER_IDX
			","
				OI.COUNTRY = '".$country."' AND
				OI.MEMBER_IDX = ".$member_idx." AND
				OP.REORDER_CNT = ".$reorder_cnt." AND
				
				OP.ORDER_STATUS NOT REGEXP 'OC|OE|OR' AND
				OP.PRODUCT_IDX = ".$product_idx." AND
				OP.PRODUCT_TYPE NOT IN ('V','D','M')
			"
		);
		
		if ($order_cnt == 0) {
			$check_result['result'] = true;
		}
	} else {
		$check_result['result'] = true;
	}
	
	return $check_result;
}

/* 각 프로모션 별 접근제한시간 체크 */
function checkAccessTime($db, $country, $idx, $type){
    $result = array(
        'value' => false,
        'msg' => '',
    );
    
	/* 응모 가능 프로모션 */
	$cnt_possible = $db->count(
		"PAGE_".$type,
		"
			IDX = ".$idx." AND
			ENTRY_START_DATE <= NOW() AND
			ENTRY_END_DATE >= NOW()
		"
	);
	
	if($cnt_possible > 0) {
		$result['value'] = true;
	} else {
		$result['value'] = false;
		$mapping_arr[] = array(
			'key' 		=> '<type>',
			'value' 	=> $type
		);
		
		$result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0001', $mapping_arr);
	}
	
    return $result;
}

function checkPromotionLogin($db, $country, $member_idx, $member_name){
    if ($country != null && $member_idx > 0 && $member_name != null) {
		$result['value'] = true;
    } else {
        $result['value'] = false;
        $result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
    }
    return $result;
}

function checkVerifyMember($db, $member_idx, $country){
	/*
	본인인증 미연동.
	연동이후 사용예정

    $verify_flg = $db->get('MEMBER_'.$country, 'IDX = ? ', array($member_idx))[0]['VERIFY_FLG'];

    if($verify_flg){
        $result['value'] = true;
    }
    else{
        $result['value'] = false;
        $result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0028', array());
    }
	*/
	$result['value'] = true;
    return $result;
}

function checkIpBanMember($db, $member_idx, $country){
    $ip_ban_flg = $db->get('MEMBER_'.$country, 'IDX = ? ', array($member_idx))[0]['IP_BAN_FLG'];

    if(!$ip_ban_flg){
        $result['value'] = true;
    } else {
        $result['value'] = false;
        $result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0005', array());
    }
	
    return $result;
}

function checkDuplicateEntry($db, $idx, $type, $member_idx, $country){
    $tel_mobile = $db->get('MEMBER_'.$country, 'IDX = ? ', array($member_idx))[0]['TEL_MOBILE'];
	if ($tel_mobile != null) {
		$cnt_member = $db->count(
			"
				ENTRY_".$type." ET
				LEFT JOIN MEMBER_".$country." MB ON
                ET.MEMBER_IDX = MB.IDX
			",
			"
				ET.".$type."_IDX = ".$idx." AND
				(
					MB.IDX = ".$member_idx." OR
					MB.TEL_MOBILE = '".$tel_mobile."'
				) AND
				ET.DEL_FLG = FALSE
			"
		);
		
		if ($cnt_member > 0) {
			$result['value'] = false;
			$result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0084', array());
		} else {
			$result['value'] = true;
		}
    } else {
        $result['value'] = false;
        $result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0054', array());
    }
    return $result;
    //휴대전화 중복체크
}

function checkPromotionMemberLevel($db, $idx, $type, $member_idx, $country){
    $result = array(
        'value' => false,
        'msg' => '',
    );
	
    $member_level = $db->get('PAGE_'.$type, 'IDX = ?', array($idx))[0]['MEMBER_LEVEL'];
    
    if ($member_level == 'ALL' || $member_level == null) {
        $result['value'] = true;
    } else {
        $select_member_level_sql = "
            SELECT
                LEVEL
            FROM
                MEMBER_LEVEL
            WHERE
                IDX = ".$member_level."
        ";
		
        $db->query($select_member_level_sql);
		
        foreach($db->fetch() as $level_data){
            $level = $level_data['LEVEL'];

            $get_member_level_sql = "
                SELECT
                    (
						SELECT
							LEVEL
						FROM
							MEMBER_LEVEL
						WHERE
							IDX = LEVEL_IDX
					) AS MEMBER_LEVEL
                FROM
                    MEMBER_".$country."
                WHERE
                    IDX = ".$member_idx."
            ";
			
            $db->query($get_member_level_sql);
            $member_level = 0;
            foreach($db->fetch() as $member_info){
                $member_level = $member_info['MEMBER_LEVEL'];
            }

            if($level <= $member_level){
                $result['value'] = true;
            }
            else{
                $result['value'] = false;
                $result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0070', array());
            }
        }
    }
    return $result;
}

?>