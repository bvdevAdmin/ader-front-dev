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
include_once("/var/www/www/api/common.php");
function getProductInfo($db,$idx_type,$param_idx) {
	if ($idx_type == "PRD") {
		$where = " IDX = ".$param_idx." ";
	} else if ($idx_type == "WSH") {
		$where = "
			IDX = (
				SELECT
					WL.PRODUCT_IDX
				FROM
					WHISH_LIST WL
				WHERE
					WL.IDX = ".$param_idx."
			)
		";
	} else if ($idx_type == "BSK") {
		$where = "
			IDX = (
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
			PR.LIMIT_PURCHASE_QTY_FLG		AS LIMIT_PURCHASE_QTY_FLG,
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
	
	$product_info = array();
	foreach($db->fetch() as $product_data) {
		$product_info = array(
			'product_idx'				=>$product_data['PRODUCT_IDX'],
			'mileage_flg'				=>$product_data['MILEAGE_FLG'],
			'exclusive_flg'				=>$product_data['EXCLUSIVE_FLG'],
			'limit_member'				=>$product_data['LIMIT_MEMBER'],
			'limit_purchase_qty_flg'	=>$product_data['LIMIT_PURCHASE_QTY_FLG'],
			'limit_product_qty'			=>$product_data['LIMIT_PRODUCT_QTY'],
			'limit_id_flg'				=>$product_data['LIMIT_ID_FLG'],
			'reorder_cnt'				=>$product_data['REORDER_CNT'],
			'sale_flg'					=>$product_data['SALE_FLG']
		);
	}
	
	return $product_info;
}

//상품별 구매 제한
function checkProductSaleFlg($db,$idx_type,$param_idx) {
	$check_result = array();
	$check_result['result'] = false;
	
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$sale_flg = $product_info['sale_flg'];
	
	if ($sale_flg == true) {
		$check_result['result'] = true;
	}
	
	return $check_result;
}

//상품별 구매수량 제한
function checkQtyLimit($db,$country,$member_idx,$idx_type,$param_idx,$option_idx,$product_qty) {
	$check_result = array();
	$check_result['result'] = false;
	
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$product_idx = $product_info['product_idx'];
	$limit_flg = $product_info['limit_purchase_qty_flg'];
	$limit_qty = $product_info['limit_product_qty'];
	
	if ($limit_flg == false) {
		$check_result['result'] = true;
	} else if ($limit_flg == true) {
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
		
		$option_qty = 0;
		foreach($db->fetch() as $option_data) {
			$option_qty = $option_data['OPTION_QTY'];
		}
		
		$basket_qty = 0;
		$select_basket_qty_sql = "
			SELECT
				SUM(BI.PRODUCT_QTY)		AS BASKET_QTY
			FROM
				BASKET_INFO BI
			WHERE
				BI.MEMBER_IDX = ".$member_idx." AND
				BI.PRODUCT_IDX = ".$product_idx." AND
				BI.DEL_FLG = FALSE
		";
		
		$db->query($select_basket_qty_sql);
		
		foreach($db->fetch() as $basket_data) {
			$basket_qty = $basket_data['BASKET_QTY'];
		}
		
		$total_qty = intval($basket_qty + $product_qty);
		
		$check_cnt = 0;
		
		if ($product_qty > $option_qty) {
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
		
		if ($check_cnt == 4) {
			$check_result['result'] = true;
		}
	}
	
	return $check_result;
}

//상품 적립금 구매 제한
function checkProductMileageFlg($db,$idx_type,$param_idx) {
	$mileage_flg = false;
	
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$mileage_flg = $product_info['mileage_flg'];
	
	return $mileage_flg;
}

//상품 단독 구매 제한
function checkProductExclusiveFlg($db,$idx_type,$param_idx) {
	$exclusive_flg = false;
	
	$product_info = getProductInfo($db,$idx_type,$param_idx);
	
	$exclusive_flg = $product_info['exclusive_flg'];
	
	return $exclusive_flg;
}

//상품 구매 멤버 제한
function checkProductLevel($db,$member_level,$idx_type,$param_idx) {
	$check_result = array();
	$check_result['result'] = false;
	
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

//상품 진열 페이지 조회 멤버 제한
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
	foreach($db->fetch() as $limit_data) {
		if ($limit_data['LIMIT_LEVEL'] != null) {
			$limit_level = explode(",",$limit_data['LIMIT_LEVEL']);
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

//ID당 구매 수량 제한
function checkIdReorder($db,$country,$member_idx,$idx_type,$param_idx) {
	$check_result = array();
	$check_result['result'] = false;
	
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
				
				OP.PRODUCT_IDX = ".$product_idx." AND
				OP.ORDER_STATUS NOT REGEXP 'OC|OE|OR'
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

function checkAccessTime($db, $country, $idx, $type){
    $result = array(
        'value' => false,
        'msg' => '',
    );
    $promotion_prev_cnt = $db->count("PAGE_".$type," IDX = ".$idx." AND ENTRY_START_DATE > NOW()");
	$promotion_exit_cnt = $db->count("PAGE_".$type," IDX = ".$idx." AND ENTRY_END_DATE < NOW()");
	$promotion_possible_cnt = $db->count("PAGE_".$type," IDX = ".$idx." AND ENTRY_START_DATE <= NOW() AND ENTRY_END_DATE >= NOW()");
	if($promotion_prev_cnt > 0 || $promotion_exit_cnt){
		$result['value'] = false;
		$mapping_arr[] = array(
			'key' 		=> '<type>',
			'value' 	=> $type
		);
		$result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0001', $mapping_arr);
	}
	else if($promotion_possible_cnt > 0){
		$result['value'] = true;
	}
    return $result;
}

function checkPromotionLogin($db, $country, $member_idx, $member_name){
    if($country == null || $member_idx == 0 || $member_name == null){
        $result['value'] = false;
        $result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
    }
    else{
        $result['value'] = true;
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
    }
    else{
        $result['value'] = false;
        $result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0005', array());
    }
    return $result;
}

function checkDuplicateEntry($db, $idx, $type, $member_idx, $country){
    $tel_mobile = $db->get('MEMBER_'.$country, 'IDX = ? ', array($member_idx))[0]['TEL_MOBILE'];
    if($tel_mobile != null){
        $check_mobile_member_sql = "
            SELECT
                COUNT(0) AS ENTRY_MEMBER_CNT
            FROM
                ENTRY_".$type." ES LEFT JOIN
                MEMBER_".$country." MEMBER
            ON
                ES.MEMBER_IDX = MEMBER.IDX
            WHERE
                ES.".$type."_IDX = ".$idx."
			AND
				DEL_FLG = FALSE
            AND
            (   
                MEMBER.IDX = ".$member_idx."
            OR
                TEL_MOBILE = '".$tel_mobile."'
            )
        ";
        $db->query($check_mobile_member_sql);
		$duplicate_cnt = -1;
        foreach($db->fetch() as $entry_data){
            $duplicate_cnt = $entry_data['ENTRY_MEMBER_CNT'];
        }
		if($duplicate_cnt == 0){
			$result['value'] = true;
		}
		else{
			$result['value'] = false;
			$result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0084', array());
		}
    }
    else{
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

    $level_info = $db->get('PAGE_'.$type, 'IDX = ?', array($idx))[0]['MEMBER_LEVEL'];
    
    if($level_info == 'ALL' || $level_info == null){
        $result['value'] = true;
    }
    else{
        $get_level_sql = "
            SELECT
                LEVEL
            FROM
                MEMBER_LEVEL
            WHERE
                IDX = ".$level_info."
        ";

        $db->query($get_level_sql);
        foreach($db->fetch() as $level_data){
            $level = $level_data['LEVEL'];

            $get_member_level_sql = "
                SELECT
                    (SELECT LEVEL FROM MEMBER_LEVEL WHERE IDX = LEVEL_IDX) AS MEMBER_LEVEL
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