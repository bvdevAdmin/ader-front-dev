<?php
/*
 +=============================================================================
 | 
 | A/S 신청내역 정보 수정
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.23
 | 최종 수정일	: 
 | 버전		: 1.1
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($as_idx)) {
		try {
			$cnt_as = $db->count("MEMBER_AS","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($as_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
			if ($cnt_as > 0) {
				switch ($action_type) {
					case "ADDR" :
						if ($to_idx != null) {
							$order_to = getOrder_address($db,$to_idx);
							if ($order_to != null) {
								$as_memo = setOrder_memo($db,$as_memo,$as_message);
								
								$db->update(
									'MEMBER_AS',
									array(
										'TO_IDX'			=>$order_to['to_idx'],
										'TO_PLACE'			=>$order_to['to_place'],
										'TO_NAME'			=>$order_to['to_name'],
										'TO_MOBILE'			=>$order_to['to_mobile'],
										'TO_ZIPCODE'		=>$order_to['to_zipcode'],
										'TO_COUNTRY_CODE'	=>$order_to['to_country_code'],
										'TO_PROVINCE_IDX'	=>$order_to['to_province_idx'],
										'TO_CITY'			=>$order_to['to_city'],
										'TO_LOT_ADDR'		=>$order_to['to_lot_addr'],
										'TO_ROAD_ADDR'		=>$order_to['to_road_addr'],
										'TO_ADDRESS'		=>$order_to['to_address'],
										'TO_DETAIL_ADDR'	=>$order_to['to_detail_addr'],
										
										'ORDER_MEMO'		=>$as_memo
									),
									"IDX = ?",
									array($as_idx)
								);

								$txt_addr = $order_to['to_road_addr'];
								if ($_SERVER['HTTP_COUNTRY'] != "KR") {
									$txt_addr = $order_to['country_name']." ".$order_to['province_name']." ".$order_to['to_city']." ".$order_to['to_address'];
								}
								
								$json_result['data'] = array(
									'to_idx'			=>$order_to['to_idx'],
									'to_place'			=>$order_to['to_place'],
									'to_name'			=>$order_to['to_name'],
									'to_mobile'			=>$order_to['to_mobile'],
									
									'to_zipcode'		=>$order_to['to_zipcode'],
									'txt_addr'			=>$txt_addr,
									'to_detail_addr'	=>$order_to['to_detail_addr'],
									
									'as_memo'			=>$as_memo
								);
							}
						}
						
						break;
						
					/* A/S 신청제품 - 반송정보 등록 처리 */
					case "HOS" :
						if ($housing_type == "APL") {
							$payment_type = null;
							$payment_code = null;
							
							$cnt_payment = $db->count("AS_PAYMENT","AS_CODE = (SELECT AS_CODE FROM MEMBER_AS WHERE IDX = ?)",array($as_idx));
							$cnt_payment++;
							
							$member_as = $db->get("MEMBER_AS","IDX = ?",array($as_idx))[0];
							$payment_code = $member_as['AS_CODE']."-D-".$cnt_payment;
							
							$db->insert(
								"AS_PAYMENT",
								array(
									'AS_IDX'			=>$as_idx,
									'AS_CODE'			=>$member_as['AS_CODE'],
									'PAYMENT_TYPE'		=>"D",
									'PAYMENT_CODE'		=>$payment_code,
									'PAYMENT_STATUS'	=>"PWT",
									'CREATER'			=>$_SESSION['MEMBER_ID'],
									'UPDATER'			=>$_SESSION['MEMBER_ID']
								)
							);
							
							$json_result['data'] = array(
								'payment_type'		=>"delivery",
								'payment_code'		=>$payment_code,
								'as_price'			=>5000
							);
						} else if ($housing_type == "DRC") {
							$db->update(
								"MEMBER_AS",
								array(
									'HOUSING_TYPE'			=>$housing_type,
									'HOUSING_IDX'			=>$housing_idx,
									'HOUSING_NUM'			=>$housing_num,
									'HOUSING_START_DATE'	=>NOW(),
									
									'UPDATE_DATE'			=>NOW(),
									'UPDATER'				=>$_SESSION['MEMBER_ID']
								),
								"IDX = ?",
								array($as_idx)
							);
						}
						
						break;
					
					/* A/S 신청제품 - 반환정보 등록 처리 */
					case "APG" :
						if ($to_idx != null) {
							$order_to = $db->get("ORDER_TO","IDX = ?",array($to_idx));
							if (sizeof($order_to) > 0) {
								$order_to = $order_to[0];
								
								$payment_type = null;
								$payment_code = null;
								
								$member_as = $db->get("MEMBER_AS","IDX = ?",array($as_idx));
								if (sizeof($member_as) > 0) {
									$member_as = $member_as[0];
									
									$cnt_payment = $db->count("AS_PAYMENT","AS_CODE = (SELECT AS_CODE FROM MEMBER_AS WHERE IDX = ?)",array($as_idx));
									$cnt_payment++;
									
									$payment_code = $member_as['AS_CODE']."-P-".$cnt_payment;
									
									$column_pg = array(
										'PAYMENT_TYPE'		=>"P",
										'PAYMENT_CODE'		=>$payment_code,
										
										'AS_IDX'			=>$member_as['IDX'],
										'AS_CODE'			=>$member_as['AS_CODE'],
										
										'CREATER'			=>$_SESSION['MEMBER_ID'],
										'UPDATER'			=>$_SESSION['MEMBER_ID']
									);
									
									$as_price_flg = 0;
									
									if ($member_as['AS_PRICE'] > 0) {
										$column_pg['PAYMENT_STATUS']	= "PWT";
										
										$json_result['data'] = array(
											'payment_type'		=>"price",
											'payment_code'		=>$payment_code,
											'as_price'			=>$member_as['AS_PRICE']
										);
									} else {
										$as_price_flg = 1;
										
										$column_pg['PAYMENT_STATUS']	="PCP";
										
										$column_pg['PG_PAYMENT']		= "FREE";
										$column_pg['PG_MID']			= $_SESSION['MEMBER_ID'];
										$column_pg['PG_STATUS']			= "DONE";
										$column_pg['PG_DATE']			= NOW();
										$column_pg['PG_PRICE']			= 0;
									}
								}
								
								$db->insert(
									"AS_PAYMENT",
									$column_pg
								);
								
								$db->update(
									"MEMBER_AS",
									array(
										'AS_PRICE_FLG'		=>$as_price_flg
									),
									"IDX = ?",
									array($as_idx)
								);
							} else {
								$json_result['code'] = 302;
								$json_result['msg'] = "";
								
								echo json_result['code'];
								exit;
							}
						}
						
						break;
				}
				
				$db->commit();
			} else {
				$json_result['code'] = 301;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0006', array());
				
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

function setOrder_memo($db,$as_memo,$as_message) {
	$order_memo = null;
	
	$memo = $db->get("ORDER_MEMO","IDX = ?",array($as_memo));
	if (sizeof($memo) > 0) {
		$order_memo = $memo[0]['MEMO_TXT_'.$_SERVER['HTTP_COUNTRY']];
		
		if ($as_message != null) {
			$order_memo = " ".$as_message;
		}
	}
	
	return $order_memo;
}

function getOrder_address($db,$to_idx) {
	$order_to = null;

	$select_order_to_sql = "
		SELECT
			OT.IDX					AS TO_IDX,
			OT.TO_PLACE				AS TO_PLACE,
			OT.TO_NAME				AS TO_NAME,
			OT.TO_MOBILE			AS TO_MOBILE,
			OT.TO_ZIPCODE			AS TO_ZIPCODE,
			OT.TO_COUNTRY_CODE		AS TO_COUNTRY_CODE,
			CI.COUNTRY_NAME			AS COUNTRY_NAME,
			OT.TO_PROVINCE_IDX		AS TO_PROVINCE_IDX,
			PI.PROVINCE_NAME		AS PROVINCE_NAME,
			OT.TO_CITY				AS TO_CITY,
			OT.TO_LOT_ADDR			AS TO_LOT_ADDR,
			OT.TO_ROAD_ADDR			AS TO_ROAD_ADDR,
			OT.TO_ADDRESS			AS TO_ADDRESS,	
			IFNULL(
				OT.TO_DETAIL_ADDR,''
			)						AS TO_DETAIL_ADDR
		FROM
			ORDER_TO OT
			
			LEFT JOIN COUNTRY_INFO CI ON
			OT.TO_COUNTRY_CODE = CI.COUNTRY_CODE

			LEFT JOIN PROVINCE_INFO PI ON
			OT.TO_PROVINCE_IDX = PI.IDX
		WHERE
			OT.IDX = ?
	";

	$db->query($select_order_to_sql,array($to_idx));

	foreach($db->fetch() as $data) {
		$order_to = array(
			'to_idx'			=>$data['TO_IDX'],
			'to_place'			=>$data['TO_PLACE'],
			'to_name'			=>$data['TO_NAME'],
			'to_mobile'			=>$data['TO_MOBILE'],
			'to_zipcode'		=>$data['TO_ZIPCODE'],
			'to_country_code'	=>$data['TO_COUNTRY_CODE'],
			'country_name'		=>$data['TO_COUNTRY_CODE'],
			'to_province_idx'	=>$data['TO_PROVINCE_IDX'],
			'province_name'		=>$data['PROVINCE_NAME'],
			'to_city'			=>$data['TO_CITY'],
			'to_lot_addr'		=>$data['TO_LOT_ADDR'],
			'to_road_addr'		=>$data['TO_ROAD_ADDR'],
			'to_address'		=>$data['TO_ADDRESS'],
			'to_detail_addr'	=>$data['TO_DETAIL_ADDR'],
		);
	}

	return $order_to;
}

?>