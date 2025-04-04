<?php
/*
 +=============================================================================
 | 
 | 공통함수 - 상품 색상/사이즈 정보 조회
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

$cdn_img_ftp_host 				= "cdn-ader-orig.fastedge.net";
$cdn_img_user 					= "imageader";
$cdn_img_password 				= "hhg3dnjf16dlf!@#$5";

function checkProductStockQty($db,$product_idx,$option_idx) {
	$stock_result = false;
	
	$select_stock_sql = "
		SELECT
			(
				SELECT
					IFNULL(SUM(STOCK_QTY),0)
				FROM
					PRODUCT_STOCK S_PS
				WHERE
					S_PS.PRODUCT_IDX = PR.IDX AND
					S_PS.OPTION_IDX = ".$option_idx." AND
					S_PS.STOCK_DATE <= NOW()
			)	AS STOCK_QTY,
			(
				SELECT
					IFNULL(
						SUM(S_OP.PRODUCT_QTY),0
					)
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					S_OP.ORDER_STATUS NOT REGEXP 'OC|OE|OR' AND
					S_OP.PRODUCT_IDX = PR.IDX AND
					S_OP.OPTION_IDX = ".$option_idx." AND
					S_OP.PRODUCT_QTY > 0
			)	AS ORDER_QTY,
			(
				SELECT
					IFNULL(
						SUM(S_OE.PRODUCT_QTY),0
					)
				FROM
					ORDER_PRODUCT_EXCHANGE S_OE
				WHERE
					S_OE.ORDER_STATUS = 'OEP' AND
					S_OE.PRODUCT_IDX = PR.IDX AND
					S_OE.PREV_OPTION_IDX = ".$option_idx." AND
					S_OE.STOCK_FLG = TRUE
			)	AS EXCHANGE_QTY,
			(
				SELECT
					IFNULL(
						SUM(S_OR.PRODUCT_QTY),0
					)
				FROM
					ORDER_PRODUCT_REFUND S_OR
				WHERE
					S_OR.ORDER_STATUS = 'ORP' AND
					S_OR.PRODUCT_IDX = PR.IDX AND
					S_OR.OPTION_IDX = ".$option_idx." AND
					S_OR.STOCK_FLG = TRUE
			)	AS REFUND_QTY
		FROM
			SHOP_PRODUCT PR
		WHERE
			PR.IDX = ".$product_idx."
	";
	
	$db->query($select_stock_sql);
	
	$product_qty = 0;
	foreach($db->fetch() as $stock_data) {
		$stock_qty = intval($stock_data['STOCK_QTY'] + $stock_data['EXCHANGE_QTY'] + $stock_data['REFUND_QTY']);
		
		$order_qty = intval($stock_data['ORDER_QTY']);
		
		$product_qty = (intval($stock_qty - $order_qty));
	}
	
	if ($product_qty > 0) {
		$stock_result = true;
	}
	
	return $stock_result;
}

function getProductColor($db,$product_idx) {
	if ($product_idx != null) {
		$select_product_sql = "
			SELECT
				PR.IDX			AS PRODUCT_IDX,
				PR.COLOR		AS COLOR,
				PR.COLOR_RGB	AS COLOR_RGB,
				(
					SELECT
						IFNULL(SUM(STOCK_QTY),0)
					FROM
						PRODUCT_STOCK S_PS
					WHERE
						S_PS.PRODUCT_IDX = PR.IDX AND
						S_PS.STOCK_DATE <= NOW()
				)				AS STOCK_QTY,
				(
					SELECT
						IFNULL(
							SUM(S_OP.PRODUCT_QTY),0
						)
					FROM
						ORDER_PRODUCT S_OP
					WHERE
						S_OP.ORDER_STATUS NOT REGEXP 'OC|OE|OR' AND
						S_OP.PRODUCT_IDX = PR.IDX AND
						S_OP.PRODUCT_QTY > 0
				)				AS ORDER_QTY,
				(
					SELECT
						IFNULL(
							SUM(S_OE.PRODUCT_QTY),0
						)
					FROM
						ORDER_PRODUCT_EXCHANGE S_OE
					WHERE
						S_OE.ORDER_STATUS = 'OEP' AND
						S_OE.PRODUCT_IDX = PR.IDX AND
						S_OE.STOCK_FLG = TRUE
				)				AS EXCHANGE_QTY,
				(
					SELECT
						IFNULL(
							SUM(S_OR.PRODUCT_QTY),0
						)
					FROM
						ORDER_PRODUCT_REFUND S_OR
					WHERE
						S_OR.ORDER_STATUS = 'ORP' AND
						S_OR.PRODUCT_IDX = PR.IDX AND
						S_OR.STOCK_FLG = TRUE
				)				AS REFUND_QTY
			FROM
				SHOP_PRODUCT PR
			WHERE
				PR.SALE_FLG = TRUE AND
				PR.STYLE_CODE = (
					SELECT
						S_PR.STYLE_CODE
					FROM
						SHOP_PRODUCT S_PR
					WHERE
						S_PR.IDX = ".$product_idx."
				)
		";
	
		$db->query($select_product_sql);
		
		$product_color = array();
		foreach($db->fetch() as $data) {
			$stock_status = "";
			$stock_qty = intval($data['STOCK_QTY'] + $data['EXCHANGE_QTY'] + $data['REFUND_QTY']);
			$product_qty = intval($stock_qty - $data['ORDER_QTY']);
			
			$reorder_flg = false;
			if ($product_qty > 0) {
				$stock_status = "STIN";	//재고 있음 (Stock in)
			} else {
				$stock_status = "STSO";	//재고 없음(사선)		→ 증가 예정 재고 없음 (Stock sold out)
				
				$member_idx = 0;
				if (isset($_SESSION['MEMBER_IDX'])) {
					$member_idx = $_SESSION['MEMBER_IDX'];
				}
				
				if ($member_idx > 0) {
					$reorder_cnt = $db->count("PRODUCT_REORDER","MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx);
					
					if ($reorder_cnt > 0) {
						$reorder_flg = true;
					}
				}
			}
			
			$product_color[] = array(
				'product_idx'		=>$data['PRODUCT_IDX'],
				'color'				=>$data['COLOR'],
				'color_rgb'			=>$data['COLOR_RGB'],
				'stock_status'		=>$stock_status,
				'reorder_flg'		=>$reorder_flg
			);
		}
		
		return $product_color;
	}
}

function getProductSize($db,$product_type,$set_type,$product_idx) {
	if ($product_idx > 0) {
		//일반 상품 주문 상태		: 결제완료(PCP)	| 상품준비(PPR) 							| 배송준비(DPR) | 배송중(DPG) | 배송완료(DCP)
		//프리오더 상품 주문 상태	: 결제완료(PCP)	| 프리오더 준비(POP) | 프리오더 상품 생산(POD)	| 배송준비(DPR) | 배송중(DPG) | 배송완료(DCP)
		//현재 주문 상품 중 배송중/배송완료 상태의 주문 상품 수량 취득
		
		$product_size = array();
		
		if ($product_type == "B") {
			$select_product_sql = "
				SELECT
					PR.IDX				AS PRODUCT_IDX,
					PR.SOLD_OUT_QTY		AS SOLD_OUT_QTY,
					PR.COLOR			AS COLOR,
					OO.IDX				AS OPTION_IDX,
					OO.OPTION_NAME		AS OPTION_NAME,
					(
						SELECT
							COUNT(IDX)
						FROM
							PRODUCT_STOCK S_PS_1
						WHERE
							S_PS_1.OPTION_IDX = OO.IDX AND
							S_PS_1.STOCK_DATE > NOW()
					)					AS STOCK_STANDBY,
					(
						SELECT
							IFNULL(SUM(STOCK_QTY),0)
						FROM
							PRODUCT_STOCK S_PS_2
						WHERE
							S_PS_2.OPTION_IDX = OO.IDX AND
							S_PS_2.STOCK_DATE <= NOW()
					)					AS STOCK_QTY,
					(
						SELECT
							IFNULL(
								SUM(S_OP.PRODUCT_QTY),0
							)
						FROM
							ORDER_PRODUCT S_OP
						WHERE
							S_OP.ORDER_STATUS NOT REGEXP 'OC|OE|OR' AND
							S_OP.OPTION_IDX = OO.IDX AND
							S_OP.PRODUCT_QTY > 0
					)					AS ORDER_QTY,
					(
						SELECT
							IFNULL(
								SUM(S_OE.PRODUCT_QTY),0
							)
						FROM
							ORDER_PRODUCT_EXCHANGE S_OE
						WHERE
							S_OE.ORDER_STATUS = 'OEP' AND
							S_OE.PREV_OPTION_IDX = OO.IDX AND
							S_OE.STOCK_FLG = TRUE
					)					AS EXCHANGE_QTY,
					(
						SELECT
							IFNULL(
								SUM(S_OR.PRODUCT_QTY),0
							)
						FROM
							ORDER_PRODUCT_REFUND S_OR
						WHERE
							S_OR.ORDER_STATUS = 'ORP' AND
							S_OR.OPTION_IDX = OO.IDX AND
							S_OR.STOCK_FLG = TRUE
					)					AS REFUND_QTY
				FROM
					SHOP_PRODUCT PR
					LEFT JOIN ORDERSHEET_OPTION OO ON
					PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
				WHERE
					PR.IDX = ".$product_idx." AND
					PR.SALE_FLG = TRUE
			";
			
			$db->query($select_product_sql);
			
			$product_size = array();
			
			foreach($db->fetch() as $data) {
				$size_type = setSizeType($data['OPTION_NAME']);
				$stock_qty = intval($data['STOCK_QTY'] + $data['EXCHANGE_QTY'] + $data['REFUND_QTY']);
				$stock_status = calcStockQty($data['SOLD_OUT_QTY'],$data['STOCK_STANDBY'],$stock_qty,$data['ORDER_QTY']);
				
				$product_size[] = array(
					'product_idx'		=>$data['PRODUCT_IDX'],
					'color'				=>$data['COLOR'],
					'option_idx'		=>$data['OPTION_IDX'],
					'option_name'		=>$data['OPTION_NAME'],
					'size_type'			=>$size_type,
					'stock_status'		=>$stock_status
				);
			}
		} else if ($product_type == "S") {
			$select_set_name_sql = "
				SELECT
					DISTINCT PR.PRODUCT_NAME		AS PRODUCT_NAME
				FROM
					SET_PRODUCT SP
					LEFT JOIN SHOP_PRODUCT PR ON
					SP.PRODUCT_IDX = PR.IDX
				WHERE
					SP.SET_PRODUCT_IDX = ".$product_idx."
			";
			
			$db->query($select_set_name_sql);
			
			foreach($db->fetch() as $name_data) {
				$product_name = $name_data['PRODUCT_NAME'];
				$set_option_info = array();
				
				if ($set_type == "SZ") {
					$select_set_product_sql = "
						SELECT
							SP.PRODUCT_IDX					AS PRODUCT_IDX,
							GROUP_CONCAT(SP.OPTION_IDX)		AS OPTION_IDX
						FROM
							SET_PRODUCT SP
							LEFT JOIN SHOP_PRODUCT PR ON
							SP.PRODUCT_IDX = PR.IDX
						WHERE
							SP.SET_PRODUCT_IDX = ".$product_idx." AND
							PR.PRODUCT_NAME = '".$product_name."'
						GROUP BY
							SP.PRODUCT_IDX
					";
					
					$db->query($select_set_product_sql);
					
					foreach($db->fetch() as $product_data) {
						$set_option_idx = $product_data['OPTION_IDX'];
						
						$select_set_option_sql = "
							SELECT
								OO.IDX				AS OPTION_IDX,
								OO.OPTION_NAME		AS OPTION_NAME,
								(
									SELECT
										COUNT(IDX)
									FROM
										PRODUCT_STOCK S_PS_1
									WHERE
										S_PS_1.OPTION_IDX = OO.IDX AND
										S_PS_1.STOCK_DATE > NOW()
								)					AS STOCK_STANDBY,
								(
									SELECT
										IFNULL(SUM(STOCK_QTY),0)
									FROM
										PRODUCT_STOCK S_PS_2
									WHERE
										S_PS_2.OPTION_IDX = OO.IDX AND
										S_PS_2.STOCK_DATE <= NOW()
								)					AS STOCK_QTY,
								(
									SELECT
										IFNULL(
											SUM(S_OP.PRODUCT_QTY),0
										)
									FROM
										ORDER_PRODUCT S_OP
									WHERE
										S_OP.ORDER_STATUS NOT REGEXP 'OC|OE|OR' AND
										S_OP.OPTION_IDX = OO.IDX AND
										S_OP.PRODUCT_QTY > 0
								)					AS ORDER_QTY,
								(
									SELECT
										IFNULL(
											SUM(S_OE.PRODUCT_QTY),0
										)
									FROM
										ORDER_PRODUCT_EXCHANGE S_OE
									WHERE
										S_OE.ORDER_STATUS = 'OEP' AND
										S_OE.PREV_OPTION_IDX = OO.IDX AND
										S_OE.STOCK_FLG = TRUE
								)					AS EXCHANGE_QTY,
								(
									SELECT
										IFNULL(
											SUM(S_OR.PRODUCT_QTY),0
										)
									FROM
										ORDER_PRODUCT_REFUND S_OR
									WHERE
										S_OR.ORDER_STATUS = 'ORP' AND
										S_OR.OPTION_IDX = OO.IDX AND
										S_OR.STOCK_FLG = TRUE
								)					AS REFUND_QTY
							FROM
								ORDERSHEET_OPTION OO
							WHERE
								OO.IDX IN (".$set_option_idx.")
						";
						
						$db->query($select_set_option_sql);
						
						foreach($db->fetch() as $option_data) {
							$size_type = setSizeType($option_data['OPTION_NAME']);
							$stock_qty = intval($option_data['STOCK_QTY'] + $option_data['EXCHANGE_QTY'] + $option_data['REFUND_QTY']);
							$stock_status = calcStockQty(0,$option_data['STOCK_STANDBY'],$stock_qty,$option_data['ORDER_QTY']);
							
							$set_option_info[] = array(
								'product_idx'		=>$product_data['PRODUCT_IDX'],
								'option_idx'		=>$option_data['OPTION_IDX'],
								'option_name'		=>$option_data['OPTION_NAME'],
								'size_type'			=>$size_type,
								'stock_status'		=>$stock_status
							);
						}
						
					}
				} else if ($set_type == "CL") {
					$select_set_product_sql = "
						SELECT
							PR.IDX				AS PRODUCT_IDX,
							PR.COLOR			AS COLOR,
							PR.COLOR_RGB		AS COLOR_RGB,
							OO.IDX				AS OPTION_IDX,
							(
								SELECT
									COUNT(IDX)
								FROM
									PRODUCT_STOCK S_PS_1
								WHERE
									S_PS_1.OPTION_IDX = OO.IDX AND
									S_PS_1.STOCK_DATE > NOW()
							)					AS STOCK_STANDBY,
							(
								SELECT
									IFNULL(SUM(STOCK_QTY),0)
								FROM
									PRODUCT_STOCK S_PS_2
								WHERE
									S_PS_2.OPTION_IDX = OO.IDX AND
									S_PS_2.STOCK_DATE <= NOW()
							)					AS STOCK_QTY,
							(
								SELECT
									IFNULL(
										SUM(S_OP.PRODUCT_QTY),0
									)
								FROM
									ORDER_PRODUCT S_OP
								WHERE
									S_OP.ORDER_STATUS NOT REGEXP 'OC|OE|OR' AND
									S_OP.OPTION_IDX = OO.IDX AND
									S_OP.PRODUCT_QTY > 0
							)					AS ORDER_QTY,
							(
								SELECT
									IFNULL(
										SUM(S_OE.PRODUCT_QTY),0
									)
								FROM
									ORDER_PRODUCT_EXCHANGE S_OE
								WHERE
									S_OE.ORDER_STATUS = 'OEP' AND
									S_OE.PREV_OPTION_IDX = OO.IDX AND
									S_OE.STOCK_FLG = TRUE
							)					AS EXCHANGE_QTY,
							(
								SELECT
									IFNULL(
										SUM(S_OR.PRODUCT_QTY),0
									)
								FROM
									ORDER_PRODUCT_REFUND S_OR
								WHERE
									S_OR.ORDER_STATUS = 'ORP' AND
									S_OR.OPTION_IDX = OO.IDX AND
									S_OR.STOCK_FLG = TRUE
							)					AS REFUND_QTY
						FROM
							SHOP_PRODUCT PR
							LEFT JOIN ORDERSHEET_OPTION OO ON
							PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
						WHERE
							PR.IDX IN (
								SELECT
									PRODUCT_IDX
								FROM
									SET_PRODUCT S_SP
								WHERE
									S_SP.SET_PRODUCT_IDX = ".$product_idx."
							) AND
							PR.PRODUCT_NAME = '".$product_name."'
					";
					
					$db->query($select_set_product_sql);
					
					foreach($db->fetch() as $option_data) {
						$stock_qty = intval($option_data['STOCK_QTY'] + $option_data['EXCHANGE_QTY'] + $option_data['REFUND_QTY']);
						$stock_status = calcStockQty(0,$option_data['STOCK_STANDBY'],$stock_qty,$option_data['ORDER_QTY']);
						
						$set_option_info[] = array(
							'product_idx'		=>$option_data['PRODUCT_IDX'],
							'color'				=>$option_data['COLOR'],
							'color_rgb'			=>$option_data['COLOR_RGB'],
							'option_idx'		=>$option_data['OPTION_IDX'],
							'stock_status'		=>$stock_status
						);
					}
				}
				
				$product_size[] = array(
					'product_name'		=>$product_name,
					'set_option_info'	=>$set_option_info
				);
			}
		}
		
		return $product_size;
	}
}

function setSizeType($option_name) {
	$size_type = null;
	
	$tmp_len = strlen($option_name);
	if ($tmp_len == 2) {
		$size_type = "N";
	} else if ($tmp_len == 3) {
		$size_type = "O";
	} else {
		$size_type = "S";
	}
	
	return $size_type;
}

function calcStockQty($sold_out_qty,$stock_standby,$stock_qty,$order_qty) {
	$product_qty = intval($stock_qty) - intval($order_qty);
	
	$stock_status = null;
	
	if ($product_qty > 0) {
		if ($product_qty >= $sold_out_qty) {
			$stock_status = "STIN";	//재고 있음 (Stock in)
		} else {
			$stock_status = "STCL";	//품절 임박 (Stock sold out close)
		}
	} else {
		if ($stock_standby > 0) {
			$stock_status = "STSC";	//재고 없음(그레이아웃)	→ 재고 증가 예정 (Stock in schedule)
		} else {
			$stock_status = "STSO";	//재고 없음(사선)		→ 증가 예정 재고 없음 (Stock sold out)
		}
	}
	
	return $stock_status;
}

function getMenuInfo($db,$country,$menu_type,$menu_idx) {
	$menu_info = array();
	
	$parent_info = array();
	if ($menu_type != null && $menu_idx > 0) {
		$parent_info = getMenuParentInfo($db,$menu_type,$menu_idx);
	}
	
	if ($menu_type == "HL1") {
		$select_menu_sql = "
			(
				SELECT
					HL1.IDX					AS MENU_IDX,
					'HL1'					AS MENU_TYPE,
					HL1.MENU_TITLE			AS MENU_TITLE,
					HL1.IMG_LOCATION		AS IMG_LOCATION,
					HL1.EXT_LINK_FLG		AS EXT_LINK_FLG,
					IFNULL(
						HL1.MENU_LINK,''
					)						AS MENU_LINK
				FROM
					MENU_HL_1 HL1
				WHERE
					HL1.IDX = ".$menu_idx." AND
					HL1.COUNTRY = '".$country."' AND
					HL1.A1_EXP_FLG = TRUE
			) UNION (
				SELECT
					HL2.IDX					AS MENU_IDX,
					'HL2'					AS MENU_TYPE,
					HL2.MENU_TITLE			AS MENU_TITLE,	
					HL2.IMG_LOCATION		AS IMG_LOCATION,
					HL2.EXT_LINK_FLG		AS EXT_LINK_FLG,
					IFNULL(
						HL2.MENU_LINK,''
					)						AS MENU_LINK
				FROM
					MENU_HL_2 HL2
				WHERE
					HL2.COUNTRY = '".$country."' AND
					HL2.PARENT_IDX = ".$menu_idx." AND
					HL2.A1_EXP_FLG = TRUE
			)
		";
		
		$db->query($select_menu_sql);
		
		foreach($db->fetch() as $menu_data) {
			$menu_param = "&menu_type=".$menu_data['MENU_TYPE']."&menu_idx=".$menu_data['MENU_IDX'];
			
			$menu_link = null;
			if (strlen($menu_data['MENU_LINK']) > 0) {
				if ($menu_data['EXT_LINK_FLG'] == true) {
					$menu_link = "http://".$menu_data['MENU_LINK'];
				} else if ($menu_data['EXT_LINK_FLG'] == false) {
					$menu_link = $menu_data['MENU_LINK'].$menu_param;
				}
			} else {
				$menu_link = $menu_data['MENU_LINK'];
			}
			
			$tmp_menu_type = $menu_data['MENU_TYPE'];
			$tmp_menu_idx = $menu_data['MENU_IDX'];
			
			$selected = false;
			if ($tmp_menu_type == "HL1" && ($tmp_menu_idx == $menu_idx)) {
				$selected = true;
			}
			
			$menu_info[] = array(
				'menu_title'		=>$menu_data['MENU_TITLE'],
				'img_location'		=>$menu_data['IMG_LOCATION'],
				'menu_link'			=>$menu_link,
				
				'selected'			=>$selected
			);
		}
	} else if ($menu_type == "HL2") {
		$select_menu_sql = "
			(
				SELECT
					HL1.IDX					AS MENU_IDX,
					'HL1'					AS MENU_TYPE,
					HL1.MENU_TITLE			AS MENU_TITLE,
					HL1.IMG_LOCATION		AS IMG_LOCATION,
					HL1.EXT_LINK_FLG		AS EXT_LINK_FLG,
					IFNULL(
						HL1.MENU_LINK,''
					)						AS MENU_LINK
				FROM
					MENU_HL_1 HL1
				WHERE
					HL1.IDX = (
						SELECT
							S_HL2.PARENT_IDX
						FROM
							MENU_HL_2 S_HL2
						WHERE
							S_HL2.IDX = ".$menu_idx."
					) AND
					HL1.A1_EXP_FLG = TRUE
			) UNION (
				SELECT
					HL2.IDX					AS MENU_IDX,
					'HL2'					AS MENU_TYPE,
					HL2.MENU_TITLE			AS MENU_TITLE,
					HL2.IMG_LOCATION		AS IMG_LOCATION,
					HL2.EXT_LINK_FLG		AS EXT_LINK_FLG,
					IFNULL(
						HL2.MENU_LINK,''
					)						AS MENU_LINK
				FROM
					MENU_HL_2 HL2
				WHERE
					(
						HL2.PARENT_IDX = (
							SELECT
								S_HL2.PARENT_IDX
							FROM
								MENU_HL_2 S_HL2
							WHERE
								S_HL2.IDX = ".$menu_idx."
						) AND
						A1_EXP_FLG = TRUE
					) OR
					(
						HL2.IDX = ".$menu_idx." AND
						HL2.A0_EXP_FLG = FALSE AND
						HL2.A1_EXP_FLG = FALSE
					)
			)
		";
		
		$db->query($select_menu_sql);
		
		foreach($db->fetch() as $menu_data) {
			$menu_param = "&menu_type=".$menu_data['MENU_TYPE']."&menu_idx=".$menu_data['MENU_IDX'];
			
			$menu_link = null;
			if (strlen($menu_data['MENU_LINK']) > 0) {
				if ($menu_data['EXT_LINK_FLG'] == true) {
					$menu_link = "http://".$menu_data['MENU_LINK'];
				} else if ($menu_data['EXT_LINK_FLG'] == false) {
					$menu_link = $menu_data['MENU_LINK'].$menu_param;
				}
			} else {
				$menu_link = $menu_data['MENU_LINK'];
			}
			
			$tmp_menu_type = $menu_data['MENU_TYPE'];
			$tmp_menu_idx = $menu_data['MENU_IDX'];
			
			$selected = false;
			if ($tmp_menu_type == "HL2" && ($tmp_menu_idx == $menu_idx)) {
				$selected = true;
			}
			
			$menu_info[] = array(
				'menu_title'		=>$menu_data['MENU_TITLE'],
				'menu_location'		=>$parent_info['parent_title']." ".$menu_data['MENU_TITLE'],
				'img_location'		=>$menu_data['IMG_LOCATION'],
				'menu_link'			=>$menu_link,
				
				'selected'			=>$selected
			);
		}
	}
	
	return $menu_info;
}

function getMenuParentInfo($db,$menu_type,$menu_idx) {
	$parent_info = array();
	
	$select_parent_segment_sql = "";
	if ($menu_type == "HL1") {
		$select_parent_segment_sql = "
			SELECT
				MS.IDX				AS PARENT_IDX,
				MS.MENU_TITLE		AS PARENT_TITLE
			FROM
				MENU_SEGMENT MS
			WHERE
				IDX = (
					SELECT
						S_HL1.PARENT_IDX
					FROM
						MENU_HL_1 S_HL1
					WHERE
						IDX = ".$menu_idx."
				)
		";
	} else if ($menu_type == "HL2") {
		$select_parent_segment_sql = "
			SELECT
				MS.IDX				AS PARENT_IDX,
				MS.MENU_TITLE		AS PARENT_TITLE
			FROM
				MENU_SEGMENT MS
			WHERE
				MS.IDX = (
					SELECT
						S_HL1.PARENT_IDX
					FROM
						MENU_HL_1 S_HL1
					WHERE
						S_HL1.IDX = (
							SELECT
								S_HL2.PARENT_IDX
							FROM
								MENU_HL_2 S_HL2
							WHERE
								S_HL2.IDX = ".$menu_idx."
						)
				)
		";
	}
	
	$db->query($select_parent_segment_sql);
	
	foreach($db->fetch() as $parent_data) {
		$parent_info = array(
			'parent_idx'		=>$parent_data['PARENT_IDX'],
			'parent_title'		=>$parent_data['PARENT_TITLE']
		);
	}
	
	return $parent_info;
}

function cdn_img_upload($db, $country, $img_type,$upload_img,$cdn_img_dir) {
	$upload_result = array();
	
	$cdn_img_ftp_host 				= "cdn-ader-orig.fastedge.net";
	$cdn_img_user 					= "imageader";
	$cdn_img_password 				= "hhg3dnjf16dlf!@#$5";
	
	$conn = ftp_connect($cdn_img_ftp_host);
	if (!$conn) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0069', array());
	}
	
	$result = ftp_login($conn, $cdn_img_user, $cdn_img_password);
	if(!$result){
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0068', array());
	}
	
	$img_num = 1;
	for ($i=0; $i<count($upload_img['name']); $i++) {
		$img_name = $upload_img['name'][$i];
		
		if (!empty($img_name)) {
			$name_arr = explode('.',$img_name);
			$img_ext = $name_arr[count($name_arr) - 1];
			$tmp_file = $upload_img['tmp_name'][$i];
			
			if ($img_type != null) {
				$ftp_path = $cdn_img_dir."/img_".$img_type."_".$img_num."_".time().".".$img_ext;
			} else {
				$ftp_path = $cdn_img_dir."/img_".$img_num."_".time().".".$img_ext;
			}
			
			$local_file = $tmp_file; // 접속한 서버로 업로드 할 파일
			
			if (ftp_put($conn,$ftp_path,$local_file,FTP_BINARY)) {
				array_push($upload_result,$ftp_path);
				$img_num++;
			}
		}
	}
	
	ftp_close($conn);
	
	return $upload_result;
}

function getBasketCnt($db,$country,$member_idx) {
	$basket_cnt = $db->count("BASKET_INFO","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND PARENT_IDX = 0 AND DEL_FLG = FALSE");
	
	return $basket_cnt;
}

function xssEncode($param){
	$param = str_replace("&","&amp;",$param);
	$param = str_replace("\"","&quot;",$param);
	$param = str_replace("'","&apos;",$param);
	$param = str_replace("<","&lt;",$param);
	$param = str_replace(">","&gt;",$param);
	$param = str_replace("\r","<br>",$param);
	$param = str_replace("\n","<p>",$param);

	return "'".$param."'";
}

function getPrevMemberInfo($db,$country,$member_idx) {
	$prev_member_info = array();
	
	$select_member_info_sql = "
		SELECT
			MI.MEMBER_PW		AS MEMBER_PW,
			MI.TEL_MOBILE		AS TEL_MOBILE
		FROM
			MEMBER_".$country." MI
		WHERE
			MI.IDX = ".$member_idx."
	";
	
	$db->query($select_member_info_sql);
	
	foreach($db->fetch() as $member_data) {
		$prev_member_info = array(
			'member_pw'					=>$member_data['MEMBER_PW'],
			'tel_mobile'				=>$member_data['TEL_MOBILE']
		);
	}
	
	return $prev_member_info;
}

function addMemberUpdateLog($db,$country,$member_idx,$prev_member_info) {
	$insert_member_update_log_sql = "
		INSERT INTO
			MEMBER_UPDATE_LOG
		(
			COUNTRY,
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			
			PREV_MEMBER_PW,
			MEMBER_PW,
			PREV_TEL_MOBILE,
			TEL_MOBILE,
			
			RECEIVE_TEL_FLG,
			RECEIVE_SMS_FLG,
			RECEIVE_EMAIL_FLG,
			
			UPDATE_DATE,
			UPDATER
		)
		SELECT
			MI.COUNTRY					AS COUNTRY,
			MI.IDX						AS MEMBER_IDX,
			MI.MEMBER_ID				AS MEMBER_ID,
			MI.MEMBER_NAME				AS MEMBER_NAME,
			
			'".$prev_member_info['member_pw']."'
										AS PREV_MEMBER_PW,
			MI.MEMBER_PW				AS MEMBER_PW,
			'".$prev_member_info['tel_mobile']."'
										AS PREV_TEL_MOBILE,
			MI.TEL_MOBILE				AS TEL_MOBILE,
			
			MI.RECEIVE_TEL_FLG			AS RECEIVE_TEL_FLG,
			MI.RECEIVE_SMS_FLG			AS RECEIVE_SMS_FLG,
			MI.RECEIVE_EMAIL_FLG		AS RECEIVE_EMAIL_FLG,
			
			NOW()						AS UPDATE_DATE,
			MI.MEMBER_ID				AS UPDATER
		FROM
			MEMBER_".$country." MI
		WHERE
			MI.IDX = ".$member_idx."
	";
	
	$db->query($insert_member_update_log_sql);
}

function getMsgToMsgCode($db, $country, $msg_code, $mapping_arr){
	$msg_info = $db->get("MSG_MST", "MSG_CODE = ?", array($msg_code))[0];
	$msg_text = '';
	if(isset($msg_info['MSG_TEXT_'.$country])){
		$msg_text = $msg_info['MSG_TEXT_'.$country];
	}
	foreach($mapping_arr as $mapping_info){
		$msg_text = str_replace($mapping_info['key'], $mapping_info['value'], $msg_text);
	}
	return $msg_text;
}

?>