<?php
/*
 +=============================================================================
 | 
 | 주문 취소 등록
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
	if ($order_code != null && ($param_idx != null && is_array($param_idx) && count($param_idx) > 0)) {
		$db->begin_transaction();
		
		try {
			/* 1. 취소 하려는 주문 정보의 예외처리 */
			$cnt_order = $db->count("ORDER_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND ORDER_CODE = ?",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$order_code));
			if ($cnt_order > 0) {
				/* 2. PARAM 설정::주문 취소 상품 */
				$param		 = setOP_param($param_idx);

				$op_idx		 = $param['op_idx'];
				$product_qty = $param['product_qty'];

				/* 3. 주문 처리번호 (주문 취소번호) 설정 - /api/_legacy/common.php */
				$update_code	= setOrder_update($db,"OCC",$order_code);

				/* 4. 주문 취소 타이틀 설정 */
				$order_title	= setOrder_title($db,$op_idx);

				/* 5. 취소하려는 주문의 결제 현황 조회처리 */
				$remain			= getOrder_remain($db,$order_code);
				
				/* 6. 취소하려는 주문상품 조회 처리 */
				$cancel_product = getCancel_product($db,$op_idx,$product_qty,$remain,$mileage_price);
				
				/* 7. 주문 취소금액 계산 처리 */
				$cancel_price	= calcCancel_total($cancel_product);
				
				/* 8. 추가 배송비 발생 체크 처리 */
				$extra_delivery = checkExtra_delivery($db,$order_code,$remain,$cancel_price);
				if ($extra_delivery > 0) {
					if (($cancel_price['cancel'] - $extra_delivery) > 0)  {
						$cancel_price['delivery']	= $extra_delivery;
						$cancel_price['cancel']		= $cancel_price['cancel'] - $extra_delivery;
					} else {
						$cancel_price['delivery']	= $cancel_price['cancel'];
						$cancel_price['cancel']		= 0;
					}
				}

				/* 9. 전체 취소 시 배송비 반환 처리 */
				if (($remain['pg'] - $cancel_price['cancel'] - $remain['delivery']) == 0) {
					$cancel_price['cancel'] = ($cancel_price['cancel'] + $remain['delivery']);
					$cancel_price['return'] = $remain['delivery'];
				}

				/* 9. 전체 취소 시 배송비 반환 처리 */
				$pg_info		= getPG_info($db,$order_code);

				$pg_cancel = null;

				/* 10. PARAM 설정::주문 취소 정보 */
				if ($cancel_price['cancel'] > 0) {
					/* 10-1. PG사 결제 취소 처리 */
					$pg_cancel		= cancelPG($pg_info['pg_payment_key'],$cancel_price['cancel']);
				} else {
					if ($pg_info['pg_mid'] == $_SESSION['MEMBER_ID'] && $pg_info['pg_currency'] == "MLG") {
						/* 10-2. 전액 적립금 결제 주문의 경우 */
						$pg_cancel = array(
							'pg_cancel_date'		=>NOW(),
							'pg_cancel_price'		=>0,
							'pg_cancel_key'			=>"적립금 결제 취소",
							'pg_remain_price'		=>$pg_info['pg_remain_price']
						);
					} else {
						/* 10-3. 적립금 결제 주문의 경우 */
						$pg_cancel = array(
							'pg_cancel_date'		=>NOW(),
							'pg_cancel_price'		=>$mileage_price,
							'pg_cancel_key'			=>"적립금 결제 취소",
							'pg_remain_price'		=>$pg_info['pg_remain_price'] - $mileage_price
						);
					}
				}
				
				$kakao_code = null;
				/* 11. 알림톡 발송 코드 설정 */
				if ($pg_info['pg_currency'] != "MLG") {
					if ($pg_cancel['pg_remain_price'] == 0) {
						/* 알림톡 코드 설정 - 주문 전체 취소 */
						$kakao_code = "KAKAO_CODE_0007";
					} else {
						/* 알림톡 코드 설정 - 주문 부분 취소 */
						$kakao_code = "KAKAO_CODE_0006";
					}
				}

				if ($pg_cancel != null) {
					/* 12-1. PARAM 설정::주문 취소 정보 테이블  */
					$param_info = array(
						$update_code,
						$order_title,
						
						$cancel_price['product'],
						$cancel_price['discount'],
						$mileage_price,
						$cancel_price['delivery'],
						$cancel_price['return'],
						$cancel_price['cancel'],
						$cancel_price['refund'],
						
						$pg_cancel['pg_cancel_date'],
						$pg_cancel['pg_cancel_price'],
						$pg_cancel['pg_cancel_key'],
						
						$depth1_idx,
						$depth2_idx,
						$reason_memo,
						
						$_SESSION['MEMBER_ID'],
						$_SESSION['MEMBER_ID'],
						
						$order_code
					);
					
					/* 12-2. [주문 취소 상품] 테이블 등록처리 */
					$ci_idx = addOrder_cancel($db,$param_info);
					if (isset($ci_idx) && $ci_idx > 0) {
						$column_update = " PG_REMAIN_PRICE	= ? ";
						$param_price = array($pg_cancel['pg_remain_price']);
						
						if ($cancel_price['discount'] > 0) {
							$column_update .= " ,REMAIN_DISCOUNT = REMAIN_DISCOUNT - ? ";
							array_push($param_price,$cancel_price['discount']);
						}
						
						if ($cancel_price['mileage'] > 0) {
							$column_update .= " ,REMAIN_MILEAGE	= REMAIN_MILEAGE - ? ";
							array_push($param_price,$cancel_price['mileage']);
						}
						
						$update_order_info_sql = "
							UPDATE
								ORDER_INFO
							SET
								".$column_update."
							WHERE
								ORDER_CODE = ?
						";
						
						$db->query($update_order_info_sql,array_merge($param_price,array($order_code)));
						
						foreach($cancel_product as $key => $product) {
							/* 13-1. PARAM 설정::주문 취소 정보 테이블  */
							$param_product = array(
								$ci_idx,
								$update_code,
								
								$product['product_qty'],
								$product['product_price'],
								$product['discount_price'],
								$product['mileage_price'],
								0,
								$product['cancel_price'],
								$product['product_price'],
								
								$_SESSION['MEMBER_ID'],
								$_SESSION['MEMBER_ID'],
								
								$product['op_idx']
							);
							
							$param_update_B = array(
								'op_idx'		=>$product['op_idx'],
								'product_qty'	=>$product['product_qty']
							);
							
							/* 13-2. [주문 취소 정보] 테이블 등록처리 */
							$cp_idx = addCancel_product($db,$param_product);
							if (isset($cp_idx) && $cp_idx > 0) {
								/* 13-3. [주문 상품] 테이블 갱신처리 (일반상품) */
								putOrder_product_B($db,$param_update_B);
								
								/* 13-3. 주문 취소 상품 등록 - 세트 구성상품 체크 처리 */
								$cnt_set = $db->count("ORDER_PRODUCT","ORDER_CODE = ? AND PARENT_IDX = ?",array($order_code,$product['op_idx']));
								if ($cnt_set > 0) {
									/* 13-4. PARAM 설정::주문 취소 정보 테이블 (세트 구성 상품) */
									$param_set = array(
										$ci_idx,
										$update_code,
										$cp_idx,
										
										$product['product_qty'],
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
									
									/* 13-5. [주문 취소 상품] 테이블 등록처리 (세트 구성 상품) */
									$cs_idx = addCancel_set($db,$param_set);
									if (isset($cs_idx) && $cs_idx > 0) {
										/* 13-6. [주문 취소 상품] 테이블 등록처리 (세트 구성 상품) */
										putOrder_product_S($db,$param_update_S);
									}
								}
							}
						}
					}
			
					/* 적립금 차감/차감 적립금 등록 처리 */
					setCnacel_mileage($db,$update_code,$order_code);
					
					/* 주문 취소 시 입력 한 적립금 반환처리 */
					if ($mileage_price > 0) {
						$insert_mileage_info_sql = "
							INSERT INTO
								MILEAGE_INFO
							(
								COUNTRY,
								MEMBER_IDX,
								ID,
								MEMBER_LEVEL,

								MILEAGE_CODE,
								MILEAGE_USABLE_INC,
								MILEAGE_BALANCE,
								
								ORDER_TYPE,
								ORDER_CODE,
								ORDER_UPDATE_CODE,

								DATE_CODE,
								MILEAGE_USABLE_DATE,

								CREATER,
								UPDATER
							)
							SELECT
								MI.COUNTRY					AS COUNTRY,
								MI.MEMBER_IDX				AS MEMBER_IDX,
								MI.ID						AS ID,
								MB.LEVEL_IDX				AS LEVEL_IDX,

								'CIN'						AS MILEAGE_CODE,
								?							AS MILEAGE_USABLE_INC,
								(MI.MILEAGE_BALANCE + ?)	AS MILEAGE_BALANCE,

								'C'							AS ORDER_TYPE,
								?							AS ORDER_CODE,
								?							AS ORDER_UPDATE_CODE,

								'TODAY'						AS DATE_CODE,
								NOW()						AS MILEAGE_USABLE_DATE,

								?							AS CREATER,
								?							AS UPDATER
							FROM
								MILEAGE_INFO MI

								LEFT JOIN MEMBER_LEVEL LV ON
								MI.MEMBER_IDX = MB.LEVEL_IDX
							WHERE
								MI.COUNTRY		= ? AND
								MI.MEMBER_IDX	= ?
							ORDER BY
								MI.IDX DESC
							LIMIT
								0,1
						";
			
						$db->query(
							$insert_mileage_info_sql,
							array(
								$mileage_price,
								$mileage_price,
								$order_code,
								$update_code,
								
								$_SESSION['MEMBER_ID'],
								$_SESSION['MEMBER_ID'],
								
								$_SERVER['HTTP_COUNTRY'],
								$_SESSION['MEMBER_IDX']
							)
						);
					}
			
					/* 취소 가능한 주문 상품이 존재하지 않는 경우 바우처 환급 */
					$cnt_remain = $db->count("ORDER_PRODUCT","ORDER_CODE = ? AND REMAIN_QTY > 0 AND PRODUCT_TYPE NOT REGEXP 'V|D'",array($order_code));
					if ($cnt_remain == 0) {
						/* 결제 시 사용 한 바우처 환급처리 */
						$cnt_discount = $db->count("ORDER_INFO","ORDER_CODE = ? AND PRICE_DISCOUNT > 0",array($order_code));
						if ($cnt_discount > 0) {
							$update_voucher_issue_sql = "
								UPDATE
									VOUCHER_ISSUE
								SET
									USED_FLG		= FALSE,
									UPDATE_DATE		= NOW(),
									UPDATER			= ?
								WHERE
									IDX = (
										SELECT
											S_OP.PRODUCT_IDX
										FROM
											ORDER_PRODUCT S_OP
										WHERE
											S_OP.ORDER_CODE		= ? AND
											S_OP.PRODUCT_TYPE	= 'V'
									)
							";
			
							$db->query($update_voucher_issue_sql,array($_SESSION['MEMBER_ID'],$order_code));
						}
					}
			
					$send_param = getSEND_param($db,$update_code);	
			
					/* 알림톡 발송설정 체크처리 */
					if ($_SERVER['HTTP_COUNTRY'] == "KR") {
						$kakao_setting = checkKAKAO_setting($db,$kakao_code);
						if ($kakao_setting['kakao_flg'] == true && $kakao_setting['template_id'] != null) {
							
							/* KAKAO::PARAM */
							$param_kakao = array(
								'user_email'		=>$_SESSION['MEMBER_ID'],
								'user_name'			=>$_SESSION['MEMBER_NAME'],
								'tel_mobile'		=>str_replace("-","",$_SESSION['TEL_MOBILE']),
								'template_id'		=>$kakao_setting['template_id']
							);
							
							/* KAKAO::DATA */
							/*
							$kakao_data = array(
								'member_id'			=>$_SESSION['MEMBER_ID'],
								'member_name'		=>$_SESSION['MEMBER_NAME'],
								
								'order_code'		=>$send_param['order_code'],
								'order_title'		=>$send_param['order_title'],
								'price_refund'		=>$send_param['price_refund'],
								'create_date'		=>$send_param['create_date']
							);
							*/
							
							/* (공통) NCP - 메일 발송 */
							callSEND_kakao($db,$param_kakao,array());
						}
					}
					
					/* 자동메일 발송설정 체크처리 */
					$mail_setting = checkMAIL_setting($db,$_SERVER['HTTP_COUNTRY'],"MAIL_CODE_0006");
					if ($mail_setting['mail_flg'] == true && ($mail_setting['template_id'] != null && $mail_setting['template_id'] != "00000")) {
						/* MAIL::PARAM */
						$param_mail = array(
							'user_email'		=>$_SESSION['MEMBER_ID'],
							'user_name'			=>$_SESSION['MEMBER_NAME'],
							'tel_mobile'		=>$_SESSION['TEL_MOBILE'],
							'template_id'		=>$mail_setting['template_id']
						);
						
						/* MAIL::DATA */
						/*
						$mail_data = array(
							'member_id'			=>$_SESSION['MEMBER_ID'],
							'member_name'		=>$_SESSION['MEMBER_NAME'],
							
							'order_code'		=>$send_param['order_code'],
							'order_title'		=>$send_param['order_title'],
							'price_refund'		=>$send_param['price_refund'],
							'create_date'		=>$send_param['create_date']
						);
						*/
						
						/* (공통) NCP - 메일 발송 */
						callSEND_mail($db,$param_mail,array());
					}

					addLog_info($db,"ORDER_CANCEL",$update_code);
					addLog_product($db,$_SERVER['HTTP_COUNTRY'],"ORDER_PRODUCT_CANCEL",$update_code);
			
					$db->commit();
					
					$json_result['data'] = $update_code;
				}
			} else {
				$json_result['code'] = 300;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0007',array());
				
				echo json_encode($json_result);
				exit;
			}
		} catch (mysqli_sql_exception $e) {
			$db->rollback();
			
			print_r($e);
			
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0081',array());
			
			echo json_encode($json_result);
			exit;
		}
	} else {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_WRN_0005',array());

		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());

    echo json_encode($json_result);
    exit;
}

/* 2. PARAM 설정::주문 취소 상품 */
function setOP_param($param_idx) {
	$op_idx = array();
	$product_qty = array();
	
	foreach($param_idx as $param) {
		if (!in_array($param,$op_idx)) {
			array_push($op_idx,$param);
			$product_qty[$param] = 1;
		} else {
			$tmp_qty = $product_qty[$param];
			$tmp_qty++;
			
			$product_qty[$param] = $tmp_qty;
		}
	}
	
	$param = array(
		'op_idx'		=>$op_idx,
		'product_qty'	=>$product_qty
	);
	
	return $param;
}

/* 4. 주문 취소 타이틀 설정 */
function setOrder_title($db,$op_idx) {
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

/* 5. 취소하려는 주문의 결제 현황 조회처리 */
function getOrder_remain($db,$order_code) {
	$remain_price = array();
	
	$select_remain_price_sql = "
		SELECT
			(
				SELECT
					IFNULL(
						SUM(
							(S_OP.PRODUCT_PRICE / S_OP.PRODUCT_QTY) * S_OP.REMAIN_QTY
						),0
					)
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					S_OP.ORDER_CODE = OI.ORDER_CODE AND
					S_OP.PRODUCT_TYPE NOT REGEXP 'V|D' AND
					S_OP.REMAIN_QTY > 0
			)					AS REMAIN_PRODUCT,
			(
				SELECT
					IFNULL(
						SUM(
							(S_OP.MEMBER_PRICE / S_OP.PRODUCT_QTY) * S_OP.REMAIN_QTY
						),0
					)
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					S_OP.ORDER_CODE = OI.ORDER_CODE AND
					S_OP.PRODUCT_TYPE NOT REGEXP 'V|D' AND
					S_OP.REMAIN_QTY > 0
			)									AS REMAIN_MEMBER,
			OI.REMAIN_DISCOUNT					AS REMAIN_DISCOUNT,
			OI.REMAIN_MILEAGE					AS REMAIN_MILEAGE,
			OI.PG_REMAIN_PRICE					AS REMAIN_PG,
			OI.REMAIN_DELIVERY					AS REMAIN_DELIVERY,
			IFNULL(
				J_OC.PRICE_DELIVERY,0
			)									AS EXTRA_DELIVERY
		FROM
			ORDER_INFO OI

			LEFT JOIN (
				SELECT
					S_OC.ORDER_CODE				AS ORDER_CODE,
					SUM(S_OC.PRICE_DELIVERY)	AS PRICE_DELIVERY
				FROM
					ORDER_CANCEL S_OC
				GROUP BY
					S_OC.ORDER_CODE
			) AS J_OC ON
			OI.ORDER_CODE = J_OC.ORDER_CODE
		WHERE
			OI.ORDER_CODE = ?
	";

	$db->query($select_remain_price_sql,array($order_code));

	foreach($db->fetch() as $data) {
		$remain_price = array(
			'product'		=>$data['REMAIN_PRODUCT'],
			'member'		=>$data['REMAIN_MEMBER'],
			'discount'		=>$data['REMAIN_DISCOUNT'],
			'mileage'		=>$data['REMAIN_MILEAGE'],
			'delivery'		=>$data['REMAIN_DELIVERY'] + $data['EXTRA_DELIVERY'],
			'pg'			=>$data['REMAIN_PG'],
		);
	}
	
	return $remain_price;
}

/* 6. 취소하려는 주문상품 조회 처리 */
function getCancel_product($db,$op_idx,$product_qty,$remain,$mileage) {
	$cancel_product = array();
	
	$price_product	= 0;
	$price_member	= 0;
	
	foreach($op_idx as $param_idx) {
		$select_cancel_product_sql = "
			SELECT
				OP.IDX					AS OP_IDX,
				OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
				
				OP.PRODUCT_CODE			AS PRODUCT_CODE,
				OP.PRODUCT_NAME			AS PRODUCT_NAME,
				OP.BARCODE				AS BARCODE,
				OP.OPTION_NAME			AS OPTION_NAME,
				?						AS PRODUCT_QTY,
				(
					(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ?
				)						AS PRODUCT_PRICE,
				(
					(OP.MEMBER_PRICE / OP.PRODUCT_QTY) * ?
				)						AS MEMBER_PRICE
			FROM
				ORDER_PRODUCT OP
			WHERE
				OP.IDX = ?
		";
		
		$db->query($select_cancel_product_sql,array($product_qty[$param_idx],$product_qty[$param_idx],$product_qty[$param_idx],$param_idx));
		
		foreach($db->fetch() as $data) {
			$calc_price		= $data['PRODUCT_PRICE'] - $data['MEMBER_PRICE'];
			$calc_remain	= $remain['product'] - $remain['member'];
			
			$discount_price = 0;
			if ($remain['discount'] > 0 && $calc_remain > 0) {
				$discount_price = $calc_price / $calc_remain * $remain['discount'];
			}
			
			$price_product	+= $data['PRODUCT_PRICE'];
			$price_member	+= $data['MEMBER_PRICE'];
			
			$cancel_price = $calc_price - $discount_price;
			
			$cancel_product[] = array(
				'op_idx'				=>$data['OP_IDX'],
				
				'product_qty'			=>$data['PRODUCT_QTY'],
				
				'product_price'			=>$data['PRODUCT_PRICE'],
				'member_price'			=>$data['MEMBER_PRICE'],
				'discount_price'		=>$discount_price,
				'mileage_price'			=>0,
				'cancel_price'			=>$cancel_price
			);
		}
	}
	
	if (count($cancel_product) > 0 && $mileage > 0) {
		foreach($cancel_product as $key =>$cancel) {
			$mileage_price = ($cancel['product_price'] - $cancel['member_price']) / ($price_product - $price_member) * $mileage;
			
			$cancel_product[$key]['mileage_price']	= $mileage_price;
			$cancel_product[$key]['cancel_price']	-= $mileage_price;
		}
	}
	
	return $cancel_product;
}

/* 7. 주문 취소금액 계산 처리 */
function calcCancel_total($cancel_product) {
	$price_product	= 0;
	$price_member	= 0;
	$price_discount	= 0;
	$price_mileage	= 0;
	$price_cancel	= 0;
	$price_refund	= 0;
	$price_delivery	= 0;
	$price_return	= 0;
	
	if (count($cancel_product) > 0) {
		foreach($cancel_product as $product) {
			$price_product	+= $product['product_price'];
			$price_member	+= $product['member_price'];
			$price_discount	+= $product['discount_price'];
			$price_mileage	+= $product['mileage_price'];
			$price_cancel	+= $product['cancel_price'];
			$price_refund	+= $product['product_price'];
		}
	}
	
	$total_price = array(
		'product'		=>$price_product,
		'member'		=>$price_member,
		'discount'		=>$price_discount,
		'mileage'		=>$price_mileage,
		'cancel'		=>$price_cancel,
		'refund'		=>$price_refund,
		'delivery'		=>$price_delivery,
		'return'		=>$price_return
	);
	
	return $total_price;
}

/* 8. 추가 배송비 발생 체크 처리 */
function checkExtra_delivery($db,$order_code,$remain,$cancel_price) {
	$extra_delivery = 0;

	/* 기존 배송비 지불 여부 체크 (배송비 지불 이력이 있을 경우 추가 배송비 발생 X) */
	if ($remain['delivery'] == 0) {
		$calc_price = $remain['pg'] - $cancel_price['cancel'];
		if ($calc_price > 0) {
			if ($_SERVER['HTTP_COUNTRY'] == "KR" && $calc_price < 80000) {
				$extra_delivery = 2500;
			} else if ($_SERVER['HTTP_COUNTRY'] == "EN" && $calc_price < 300) {
				$select_extra_delivery_sql = "
					SELECT
						DZ.COST
					FROM
						ORDER_TO OT
	
						LEFT JOIN COUNTRY_INFO CI ON
						OT.TO_COUNTRY_CODE = CI.COUNTRY_CODE
	
						LEFT JOIN DHL_ZONES DZ ON
						CI.ZONE_NUM = DZ.ZONE_NUM
					WHERE
						OT.IDX = (
							SELECT
								TO_IDX
							FROM
								ORDER_INFO
							WHERE
								ORDER_CODE = ?
						)
				";
	
				$db->query($select_extra_delivery_sql,array($order_code));
	
				foreach($db-fetch() as $data) {
					$extra_delivery = $data['COST'];
				}
			}
		}
	}

	return $extra_delivery;
}

/* 9. 전체 취소 시 배송비 반환 처리 */
function getPG_info($db,$order_code) {
	$pg_info = null;
	
	$select_order_pg_sql = "
		SELECT
			OI.PG_MID				AS PG_MID,
			OI.PG_PAYMENT			AS PG_PAYMENT,
			OI.PG_PAYMENT_KEY		AS PG_PAYMENT_KEY,
			OI.PG_ISSUE_CODE		AS PG_ISSUE_CODE,
			OI.PG_CARD_NUMBER		AS PG_CARD_NUMBER,
			OI.PG_PROVIDER			AS PG_PROVIDER,
			OI.PG_STATUS			AS PG_STATUS,
			OI.PG_DATE				AS PG_DATE,
			OI.PG_PRICE				AS PG_PRICE,
			OI.PG_CURRENCY			AS PG_CURRENCY,
			OI.PG_RECEIPT_URL		AS PG_RECEIPT_URL,
			OI.PG_REMAIN_PRICE		AS PG_REMAIN_PRICE,

			OI.TO_IDX				AS TO_IDX
		FROM
			ORDER_INFO OI
		WHERE
			OI.ORDER_CODE = ?
	";
	
	$db->query($select_order_pg_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$pg_info = array(
			'pg_mid'			=>$data['PG_MID'],
			'pg_payment'		=>$data['PG_PAYMENT'],
			'pg_payment_key'	=>$data['PG_PAYMENT_KEY'],
			'pg_issue_code'		=>$data['PG_ISSUE_CODE'],
			'pg_card_number'	=>$data['PG_CARD_NUMBER'],
			'pg_provider'		=>$data['PG_PROVIDER'],
			'pg_status'			=>$data['PG_STATUS'],
			'pg_date'			=>$data['PG_DATE'],
			'pg_price'			=>$data['PG_PRICE'],
			'pg_currency'		=>$data['PG_CURRENCY'],
			'pg_receipt_url'	=>$data['PG_RECEIPT_URL'],
			'pg_remain_price'	=>$data['PG_REMAIN_PRICE'],

			'to_idx'			=>$data['TO_IDX']
		);
	}
	
	return $pg_info;
}

/* 10. PG사 결제 주문에 대한 결제 취소 처리 */
function cancelPG($key,$price) {
	$pg_cancel = null;

	$currency = "KRW";
	if ($_SERVER['HTTP_COUNTRY'] == "EN") {
		$currency = "USD";
	}
	
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.tosspayments.com/v1/payments/".$key."/cancel",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => '{"cancelReason":"주문취소","cancelAmount":"'.$price.'","currency":"'.$currency.'"}',
		CURLOPT_HTTPHEADER => [
			"Authorization: Basic dGVzdF9za19ONU9XUmFwZEE4ZFkyMTc1N2piM28xekVxWktMOg==",
			"Content-Type: application/json"
		],
	]);
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	curl_close($curl);
	
	if (!$err) {
		$pg_result = json_decode($response,true);
		
		$pg_cancel_date		= null;
		$pg_cancel_price	= null;
		$pg_cancel_key		= null;
		
		$pg_remain_price	= $pg_result['balanceAmount'];
		
		$cancels = null;
		if (isset($pg_result['cancels']) && sizeof($pg_result['cancels']) > 0) {
			$cancels = $pg_result['cancels'];
			
			$cancel = $cancels[count($cancels) - 1];
			
			if ($cancel['cancelAmount'] == $price) {
				$cancel_date	= $cancel['canceledAt'];
				$cancel_price	= $cancel['cancelAmount'];
				$cancel_key		= $cancel['transactionKey'];
			}
		}
		
		$pg_cancel = array(
			'pg_cancel_date'	=>$cancel_date,
			'pg_cancel_price'	=>$cancel_price,
			'pg_cancel_key'		=>$cancel_key,
			
			'pg_remain_price'	=>$pg_remain_price
		);
	}
	
	return $pg_cancel;
}

/* 12-2. [주문 취소 정보] 테이블 등록처리 */
function addOrder_cancel($db,$param) {
	$ci_idx = 0;
	
	$insert_order_cancel_sql = "
		INSERT INTO
			ORDER_CANCEL
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
			
			PRICE_PRODUCT,
			PRICE_DISCOUNT,
			PRICE_MILEAGE_POINT,
			PRICE_DELIVERY,
			DELIVERY_RETURN,
			PRICE_CANCEL,
			PRICE_REFUND,
			
			PG_CANCEL_DATE,
			PG_CANCEL_PRICE,
			PG_CANCEL_KEY,
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		)
		SELECT
			OI.COUNTRY				AS COUNTRY,
			OI.ORDER_CODE			AS ORDER_CODE,
			?						AS ORDER_UPDATE_CODE,
			?						AS ORDER_TITLE,
			'OCC'					AS ORDER_STATUS,
			
			OI.MEMBER_IDX			AS MEMBER_IDX,
			OI.MEMBER_ID			AS MEMBER_ID,
			OI.MEMBER_NAME			AS MEMBER_NAME,
			OI.MEMBER_MOBILE		AS MEMBER_MOBILE,
			OI.MEMBER_LEVEL			AS MEMBER_LEVEL,
			
			?						AS PRICE_PRODUCT,
			?						AS PRICE_DISCOUNT,
			?						AS PRICE_MILEAGE_POINT,
			?						AS PRICE_DELIVERY,
			?						AS DELIVERY_RETURN,
			?						AS PRICE_CANCEL,
			?						AS PRICE_REFUND,
			
			?						AS PG_CANCEL_DATE,
			?						AS PG_CANCEL_PRICE,
			?						AS PG_CANCEL_KEY,
			
			?						AS DEPTH1_IDX,
			?						AS DEPTH2_IDX,
			?						AS REASON_MEMO,
			
			?						AS CREATER,
			?						AS UPDATER
		FROM
			ORDER_INFO OI
		WHERE
			OI.ORDER_CODE = ?
	";
	
	$db->query($insert_order_cancel_sql,$param);
	
	$ci_idx = $db->last_id();
	
	return $ci_idx;
}

/* 13-2. [주문 취소 정보] 테이블 등록처리 */
function addCancel_product($db,$param) {
	$cp_idx = 0;
	
	$insert_cancel_product_sql = "
		INSERT INTO
			ORDER_PRODUCT_CANCEL
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
			DISCOUNT_PRICE,
			MILEAGE_PRICE,
			DELIVERY_PRICE,
			CANCEL_PRICE,
			REFUND_PRICE,
			
			CREATER,
			UPDATER
		)
		SELECT
			?					AS ORDER_IDX,
			ORDER_CODE			AS ORDER_CODE,
			ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			?					AS ORDER_UPDATE_CODE,
			'OCC'				AS ORDER_STATUS,
			
			OP.PRODUCT_IDX		AS PRODUCT_IDX,
			OP.PRODUCT_TYPE		AS PRODUCT_TYPE,
			PARENT_IDX			AS PARENT_IDX,
			PRODUCT_CODE		AS PRODUCT_CODE,
			PRODUCT_NAME		AS PRODUCT_NAME,
			
			OP.OPTION_IDX		AS OPTION_IDX,
			OP.BARCODE			AS BARCODE,
			OP.OPTION_NAME		AS OPTION_NAME,
			
			?					AS PRODUCT_QTY,
			?					AS PRODUCT_PRICE,
			?					AS DISCOUNT_PRICE,
			?					AS MILEAGE_PRICE,
			?					AS DELIVERY_PRICE,
			?					AS CANCEL_PRICE,
			?					AS REFUND_PRICE,
			
			?					AS CREATER,
			?					AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.IDX = ?
	";
	
	$db->query($insert_cancel_product_sql,$param);
	
	$cp_idx = $db->last_id();
	
	return $cp_idx;
}

/* 13-5. [주문 취소 상품] 테이블 등록처리 (세트 구성 상품) */
function addCancel_set($db,$param) {
	$cs_idx = 0;
	
	$insert_set_product_sql = "
		INSERT INTO
			ORDER_PRODUCT_CANCEL
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
			REFUND_PRICE,
			
			CREATER,
			UPDATER
		)
		SELECT
			?					AS ORDER_IDX,
			ORDER_CODE			AS ORDER_CODE,
			ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			?					AS ORDER_UPDATE_CODE,
			'OCC'				AS ORDER_STATUS,
			
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
			(
				(OP.REMAIN_PRICE / OP.REMAIN_QTY) * ?
			)					AS REFUND_PRICE,
			
			?					AS CREATER,
			?					AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.PARENT_IDX = ?
	";
	
	$db->query($insert_set_product_sql,$param);
	
	$cs_idx = $db->last_id();
	
	return $cs_idx;
}

/* 13-3. [주문 상품] 테이블 갱신처리 (일반상품) */
function putOrder_product_B($db,$param) {
	$update_order_product_sql = "
		UPDATE
			ORDER_PRODUCT
		SET
			REMAIN_PRICE	= (
				(
					(PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY - ?)
				) - (
				 	(MEMBER_PRICE / PRODUCT_QTY) * (REMAIN_QTY - ?)
				)
			),
			REMAIN_QTY		= REMAIN_QTY - ?
		WHERE
			IDX = ?
	";
	
	$db->query(
		$update_order_product_sql,
		array(
			$param['product_qty'],
			$param['product_qty'],
			$param['product_qty'],
			
			$param['op_idx']
		)
	);
}

/* 13-3. [주문 상품] 테이블 갱신처리 (세트상품) */
function putOrder_product_S($db,$param) {
	$update_order_product_sql = "
		UPDATE
			ORDER_PRODUCT
		SET
			REMAIN_PRICE	= (
				(
					(PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY - ?)
				) - (
				 	(MEMBER_PRICE / PRODUCT_QTY) * (REMAIN_QTY - ?)
				)
			),
			REMAIN_QTY		= REMAIN_QTY - ?
		WHERE
			IDX = ?
	";
	
	$db->query(
		$update_order_product_sql,
		array(
			$param['product_qty'],
			$param['product_qty'],
			$param['product_qty'],
			
			$param['op_idx']
		)
	);
}

function setCnacel_mileage($db,$update_code,$order_code) {
	$select_order_product_cancel_sql = "
		SELECT
			PC.PRODUCT_QTY				AS PRODUCT_QTY,
			PC.ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE
		FROM
			ORDER_PRODUCT_CANCEL PC
		WHERE
			PC.ORDER_UPDATE_CODE = ?
		ORDER BY
			PC.IDX ASC
	";

	$db->query($select_order_product_cancel_sql,array($update_code));

	foreach($db->fetch() as $data) {
		$select_mileage_info_sql = "
			UPDATE
				MILEAGE_INFO
			SET
				MILEAGE_UNUSABLE = MILEAGE_UNUSABLE - (
					SELECT
						(
							(MILEAGE_UNUSABLE / (OP.REMAIN_QTY + ?)) * ?
						)
					FROM
						ORDER_PRODUCT OP
					WHERE
						OP.ORDER_PRODUCT_CODE = ?
				)
			WHERE
				MEMBER_IDX = ? AND
				ORDER_PRODUCT_CODE = ?
		";

		$db->query(
			$select_mileage_info_sql,
			array(
				$data['PRODUCT_QTY'],
				$data['PRODUCT_QTY'],
				$data['ORDER_PRODUCT_CODE'],

				$_SESSION['MEMBER_IDX'],
				$data['ORDER_PRODUCT_CODE']
			)
		);
	}
}

function getSEND_param($db,$order_code) {
	$send_param = array();
	
	$select_send_param_sql = "
		SELECT
			OI.ORDER_CODE		AS ORDER_CODE,
			OI.ORDER_TITLE		AS ORDER_TITLE,
			OI.PRICE_REFUND		AS PRICE_REFUND,
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y.%m.%d %H:%i'
			)					AS CREATE_DATE
		FROM
			ORDER_CANCEL OI
		WHERE
			OI.ORDER_UPDATE_CODE = ?
	";
	
	$db->query($select_send_param_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$send_param = array(
			'order_code'		=>$data['ORDER_CODE'],
			'order_title'		=>$data['ORDER_TITLE'],
			'price_refund'		=>$data['PRICE_REFUND'],
			'create_date'		=>$data['CREATE_DATE']
		);
	}
	
	return $send_param;
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
function addLog_product($db,$country,$table,$code) {
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
				'COUNTRY'				=>$country,
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

?>