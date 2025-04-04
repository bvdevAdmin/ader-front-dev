<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 배송지 목록 조회
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.11
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.28
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$table = "
		ORDER_TO OT
		
		LEFT JOIN COUNTRY_INFO CI ON
		OT.TO_COUNTRY_CODE = CI.COUNTRY_CODE
		
		LEFT JOIN PROVINCE_INFO PI ON
		OT.TO_PROVINCE_IDX = PI.IDX
	";
	
	$param_bind = array();
	
	$where = "";
	if (isset($no)) {
		$where .= "
			OT.IDX = ?
		";
		
		array_push($param_bind,$no);
	} else {
		$where .= "
			OT.COUNTRY = ? AND
			OT.MEMBER_IDX = ?
		";
		
		$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	}
	
	$select_order_to_sql = "
		SELECT
			OT.IDX					AS ORDER_TO_IDX,
			OT.TO_PLACE				AS TO_PLACE,
			OT.TO_NAME				AS TO_NAME,
			OT.TO_MOBILE			AS TO_MOBILE,
			OT.TO_ZIPCODE			AS TO_ZIPCODE,
			OT.TO_LOT_ADDR			AS TO_LOT_ADDR,
			OT.TO_ROAD_ADDR			AS TO_ROAD_ADDR,
			IFNULL(
				OT.TO_DETAIL_ADDR,''
			)						AS TO_DETAIL_ADDR,
			OT.TO_COUNTRY_CODE		AS TO_COUNTRY_CODE,
			IFNULL(
				CI.COUNTRY_NAME,''
			)						AS TO_COUNTRY_NAME,
			OT.TO_PROVINCE_IDX		AS TO_PROVINCE_IDX,
			IFNULL(
				PI.PROVINCE_NAME,''
			)						AS TO_PROVINCE_NAME,
			OT.TO_CITY				AS TO_CITY,
			OT.TO_ADDRESS			AS TO_ADDRESS,
			OT.DEFAULT_FLG			AS DEFAULT_FLG,
			IFNULL(
				DZ.COST,0
			)						AS DELIVERY_PRICE
		FROM
			ORDER_TO OT
			
			LEFT JOIN COUNTRY_INFO CI ON
			OT.TO_COUNTRY_CODE = CI.COUNTRY_CODE
			
			LEFT JOIN PROVINCE_INFO PI ON
			OT.TO_PROVINCE_IDX = PI.IDX
			
			LEFT JOIN DHL_ZONES DZ ON
			CI.ZONE_NUM = DZ.ZONE_NUM
		WHERE
			".$where."
		ORDER BY
			OT.DEFAULT_FLG DESC, OT.IDX DESC
	";
	
	if (isset($rows)) {
		$limit_start = (intval($page)-1)*$rows;
		
		$select_order_to_sql .= " LIMIT ?,? ";
		
		array_push($param_bind,$limit_start);
		array_push($param_bind,$rows);
	}

	$db->query($select_order_to_sql,$param_bind);

	foreach($db->fetch() as $data) {
		$delivery_price = 0;
		$txt_addr = "";

		if ($_SERVER['HTTP_COUNTRY'] == "KR") {
			/* 한국몰 배송금액 설정 */

			/* 배송지역별 추가 배송비 설정 여부 체크처리 */
			$cnt_location = $db->count("DELIVERY_LOCATION","? BETWEEN START_ZIPCODE AND END_ZIPCODE",array($data['TO_ZIPCODE']));
			if ($cnt_location > 0) {
				$delivery_price = checkOrder_location($db,$data['TO_ZIPCODE']);
			} else {
				$delivery_price = 2500;
			}

			$txt_addr = $data['TO_ROAD_ADDR']." ".$data['TO_DETAIL_ADDR'];
		} else {
			/* 영문몰 배송금액 설정 */

			$delivery_price = round(currency_EN * $data['DELIVERY_PRICE'],2);

			$txt_addr = $data['TO_ADDRESS']." ".$data['TO_CITY'].", ".$data['TO_PROVINCE_NAME'].", ".$data['TO_COUNTRY_NAME'];
		}
		
		$json_result['data'][] = array(
			'order_to_idx'		=>$data['ORDER_TO_IDX'],
			'to_place'			=>$data['TO_PLACE'],
			'to_name'			=>$data['TO_NAME'],
			'to_mobile'			=>$data['TO_MOBILE'],
			'to_zipcode'		=>$data['TO_ZIPCODE'],
			'to_lot_addr'		=>$data['TO_LOT_ADDR'],
			'to_road_addr'		=>$data['TO_ROAD_ADDR'],
			'to_detail_addr'	=>$data['TO_DETAIL_ADDR'],
			'to_country_name' 	=>$data['TO_COUNTRY_NAME'],
			'to_country_code' 	=>$data['TO_COUNTRY_CODE'],
			'to_province_idx' 	=>$data['TO_PROVINCE_IDX'],
			'to_province_name' 	=>$data['TO_PROVINCE_NAME'],
			'to_city' 			=>$data['TO_CITY'],
			'to_address' 		=>$data['TO_ADDRESS'],
			'txt_addr'			=>$txt_addr,
			'default_flg'		=>$data['DEFAULT_FLG'],
			'delivery_price'	=>$delivery_price
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
}
