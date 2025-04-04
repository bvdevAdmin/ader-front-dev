<?php
/*
 +=============================================================================
 | 
 | CJ 대한통운 - 1Day 토큰 공통함수
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.27
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

/* 1Day 토큰 체크 */
function checkToken($db) {
	$cnt_token = $db->count("DELIVERY_TOKEN","DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s') <= DATE_FORMAT(TOKEN_DATE,'%Y-%m-%d %H:%i:%s')");
	
	$token_num = null;
	if ($cnt_token > 0) {
		$select_delivery_token_sql = "
			SELECT
				DT.TOKEN_NUM	AS TOKEN_NUM
			FROM
				DELIVERY_TOKEN DT
			WHERE
				DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s') <= DATE_FORMAT(DT.TOKEN_DATE,'%Y-%m-%d %H:%i:%s')
		";
		
		$db->query($select_delivery_token_sql);
		
		foreach($db->fetch() as $data) {
			$token_num = $data['TOKEN_NUM'];
		}
	} else {
		$token = generateToken();
		setToken($db,$token);
		
		$token_num = $token['token_num'];
	}
	
	return $token_num;
}

/* 1Day 토큰 발행 */
function generateToken() {
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://dxapi-dev.cjlogistics.com:5054/ReqOneDayToken",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"DATA" : {
					"CUST_ID"		:30426467,
					"BIZ_REG_NUM"	:7608701757
				}
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-type:application/json",
			"Accept:application/json"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	if (!$err) {
		$result = json_decode($response,true);
		
		$result_cd = $result['RESULT_CD'];
		
		if ($result_cd == "S") {
			$token_num = $result['DATA']['TOKEN_NUM'];
			$token_date = date("Y-m-d H:i:s", strtotime($result['DATA']['TOKEN_EXPRTN_DTM']));
			
			$token = array(
				"token_num"		=>$token_num,
				"token_date"	=>$token_date,
			);
			
			return $token;
		}
	}
}

/* 1Day 토큰 저장 */
function setToken($db,$token) {
	$cnt_token = $db->count("DELIVERY_TOKEN");
	
	$result = null;
	if ($cnt_token > 0) {
		$result = $db->update(
			"DELIVERY_TOKEN",
			array(
				'TOKEN_NUM'		=>$token['token_num'],
				'TOKEN_DATE'	=>$token['token_date']
			)
		);
	} else {
		$db->insert(
			"DELIVERY_TOKEN",
			array(
				'TOKEN_NUM'		=>$token['token_num'],
				'TOKEN_DATE'	=>$token['token_date']
			)
		);
		
		$result = $db->last_id();
	}
	
	if ($result > 0) {
		$json_result['code'] = 200;
	} else {
		$json_result['code'] = 400;
	}
}

function generateDeliveryNum($token_num) {
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://dxapi-dev.cjlogistics.com:5054/ReqInvcNo",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"DATA" : {
					"CLNTNUM"	:30426467,
					"TOKEN_NUM"	:"'.$token_num.'"
				}
			}
		',
		CURLOPT_HTTPHEADER => [
			"CJ-Gateway-APIKey:".$token_num,
			"Content-type:application/json",
			"Accept:application/json"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	if (!$err) {
		$result = json_decode($response,true);
		$result_cd = $result['RESULT_CD'];
		
		if ($result_cd == "S") {
			$delivery_num = $result['DATA']['INVC_NO'];
			
			return $delivery_num;
		}
	}
}

function setParamDelivery($db,$order_type,$order_update_code) {
	$param_delivery = array();
	
	$order_table = null;
	$product_table = null;
	
	if ($order_type == "OEX") {
		$order_table = "ORDER_EXCHANGE";
		$product_table = "ORDER_PRODUCT_EXCHANGE";
	} else if ($order_type == "ORF") {
		$order_table = "ORDER_REFUND";
		$product_table = "ORDER_PRODUCT_REFUND";
	}
	
	$select_order_sql = "
		SELECT
			DATE_FORMAT(
				OT.CREATE_DATE,'%Y%m%d'
			)						AS CREATE_DATE,
			OI.ORDER_CODE			AS ORDER_CODE,
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			OT.ORDER_TITLE			AS ORDER_TITLE,
			OI.DELIVERY_NUM			AS DELIVERY_NUM,
			OI.TO_NAME				AS TO_NAME,
			OI.TO_MOBILE			AS TO_MOBILE,
			OI.TO_ZIPCODE			AS TO_ZIPCODE,
			OI.TO_ROAD_ADDR			AS TO_ROAD_ADDR,
			OI.TO_DETAIL_ADDR		AS TO_DETAIL_ADDR,
			OI.DELIVERY_NUM			AS DELIVERY_NUM
		FROM
			ORDER_INFO OI
			LEFT JOIN ".$order_table." OT ON
			OI.ORDER_CODE = OT.ORDER_CODE
		WHERE
			OT.ORDER_UPDATE_CODE = '".$order_update_code."'
	";

	$db->query($select_order_sql);
	
	foreach($db->fetch() as $order_data) {
		$param_delivery['create_date'] = $order_data['CREATE_DATE'];
		$param_delivery['order_code'] = $order_data['ORDER_CODE'];
		$param_delivery['order_update_code'] = $order_data['ORDER_UPDATE_CODE'];
		$param_delivery['order_title'] = $order_data['ORDER_TITLE'];
		$param_delivery['delivery_num'] = $order_data['DELIVERY_NUM'];
		
		$param_delivery['to_name'] = $order_data['TO_NAME'];
		$param_delivery['to_mobile'] = explode("-",$order_data['TO_MOBILE']);
		$param_delivery['to_zipcode'] = $order_data['TO_ZIPCODE'];
		
		$tmp_addr = $order_data['TO_ROAD_ADDR']." ".$order_data['TO_DETAIL_ADDR'];
		$road_addr = explode(" ",$tmp_addr);
		
		$param_delivery['sender_addr'] = $road_addr[0]." ".$road_addr[1]." ".$road_addr[2];
		$param_delivery['sender_detail_addr'] = str_replace($param_delivery['sender_addr']." ","",$tmp_addr);
	}
	
	$param_mpck = array();
	
	$column_option = "";
	if ($order_type == "ORF") {
		$column_option = "
			PT.BARCODE				AS BARCODE,
			PT.OPTION_NAME			AS OPTION_NAME,
		";
	} else if ($order_type == "OEX") {
		$column_option = "
			(
				SELECT
					S_OO.BARCODE
				FROM
					ORDERSHEET_OPTION S_OO
				WHERE
					S_OO.IDX = PT.PREV_OPTION_IDX
			) AS BARCODE,
			(
				SELECT
					S_OO.OPTION_NAME
				FROM
					ORDERSHEET_OPTION S_OO
				WHERE
					S_OO.IDX = PT.PREV_OPTION_IDX
			) AS OPTION_NAME,
		";
	}
	
	$select_product_sql = "
		SELECT
			PT.PRODUCT_CODE			AS PRODUCT_CODE,
			PT.PRODUCT_NAME			AS PRODUCT_NAME,
			PT.PRODUCT_QTY			AS PRODUCT_QTY,
			".$column_option."
			PT.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			".$product_table." PT
		WHERE
			PT.ORDER_UPDATE_CODE = '".$order_update_code."' AND
			PT.PRODUCT_TYPE NOT IN ('D','V','M') AND
			PT.PRODUCT_QTY > 0
	";
	
	$db->query($select_product_sql);
	
	$seq = 1;
	foreach($db->fetch() as $product_data) {
		array_push($param_mpck,array(
			'MPCK_SEQ'		=>$seq,
			'GDS_CD'		=>$product_data['PRODUCT_CODE'],
			'GDS_NM'		=>$product_data['PRODUCT_NAME'],
			'GDS_QTY'		=>$product_data['PRODUCT_QTY'],
			'UNIT_CD'		=>$product_data['BARCODE'],
			'UNIT_NM'		=>$product_data['OPTION_NAME'],
			'GDS_AMT'		=>$product_data['PRODUCT_PRICE']
		));
		
		$seq++;
	}
	
	$param_delivery['param_mpck'] = $param_mpck;
	
	return $param_delivery;
}

function addOrderTrace($db,$token_num,$order_type,$order_update_code) {
	if (strlen($token_num) > 0 && $order_type != null && $order_update_code != null) {
		$param_delivery = setParamDelivery($db,$order_type,$order_update_code);
		
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL				=>"https://dxapi-dev.cjlogistics.com:5054/RegBook",
			CURLOPT_RETURNTRANSFER	=>true,
			CURLOPT_ENCODING		=>"",
			CURLOPT_MAXREDIRS		=>10,
			CURLOPT_TIMEOUT			=>30,
			CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST	=>"POST",
			CURLOPT_POSTFIELDS		=>'
				{
					"DATA":{
						"TOKEN_NUM"			:"'.$token_num.'",
						"CUST_ID"			:"30426467",
						"RCPT_YMD"			:"'.$param_delivery['create_date'].'",
						"CUST_USE_NO"		:"'.$param_delivery['order_update_code'].'",
						"RCPT_DV"			:"02",
						"WORK_DV_CD"		:"01",
						"REQ_DV_CD"			:"01",
						
						"MPCK_KEY"			:"'.$param_delivery['order_update_code'].'_30426467",
						
						"CAL_DV_CD"			:"01",
						"FRT_DV_CD"			:"03",
						"CNTR_ITEM_CD"		:"01",
						"BOX_TYPE_CD"		:"02",
						"BOX_QTY"			:"1",
						"CUST_MGMT_DLCM_CD"	:"30426467",
						
						"SENDR_NM"			:"'.$param_delivery['to_name'].'",
						"SENDR_TEL_NO1"		:"'.$param_delivery['to_mobile'][0].'",
						"SENDR_TEL_NO2"		:"'.$param_delivery['to_mobile'][1].'",
						"SENDR_TEL_NO3"		:"'.$param_delivery['to_mobile'][2].'",
						"SENDR_ZIP_NO"		:"'.$param_delivery['to_zipcode'].'",
						"SENDR_ADDR"		:"'.$param_delivery['sender_addr'].'",
						"SENDR_DETAIL_ADDR"	:"'.$param_delivery['sender_detail_addr'].'",
						
						"RCVR_NM"			:"CJ대한통운",
						"RCVR_TEL_NO1"		:"02",
						"RCVR_TEL_NO2"		:"1588",
						"RCVR_TEL_NO3"		:"1111",
						
						"RCVR_ZIP_NO"		:"000000",
						"RCVR_ADDR"			:"서울특별시 서초구 서초대로 50길 84-10",
						"RCVR_DETAIL_ADDR"	:"408호",
						
						"ORDRR_NM"			:"'.$param_delivery['order_title'].'",
						"ORDRR_TEL_NO1"		:"'.$param_delivery['to_mobile'][0].'",
						"ORDRR_TEL_NO2"		:"'.$param_delivery['to_mobile'][1].'",
						"ORDRR_TEL_NO3"		:"'.$param_delivery['to_mobile'][2].'",
						"ORDRR_ZIP_NO"		:"'.$param_delivery['to_zipcode'].'",
						"ORDRR_ADDR"		:"'.$param_delivery['sender_addr'].'",
						"ORDRR_DETAIL_ADDR"	:"'.$param_delivery['sender_detail_addr'].'",
						
						"INVC_NO"			:"",
						"ORI_INVC_NO"		:"'.$param_delivery['delivery_num'].'",
						"ORI_ORD_NO"		:"'.$param_delivery['order_code'].'",
						
						"PRT_ST"			:"01",
						"DLV_DV"			:"01",
						"ARRAY"				:'.json_encode($param_delivery['param_mpck']).'
					}
				}
			',
			CURLOPT_HTTPHEADER => [
				"CJ-Gateway-APIKey:".$token_num,
				"Content-type:application/json",
				"Accept:application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		if (!$err) {
			$result = json_encode($response,true);
		}
	}
}

function putOrderTrace($db,$token_num,$order_type,$order_update_code) {
	if (strlen($token_num) > 0 && $order_type != null && $order_update_code != null) {
		$param_delivery = setParamDelivery($db,$order_type,$order_update_code);
		
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL				=>"https://dxapi-dev.cjlogistics.com:5054/CnclBook",
			CURLOPT_RETURNTRANSFER	=>true,
			CURLOPT_ENCODING		=>"",
			CURLOPT_MAXREDIRS		=>10,
			CURLOPT_TIMEOUT			=>30,
			CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST	=>"POST",
			CURLOPT_POSTFIELDS		=>'
				{
					"DATA":{
						"TOKEN_NUM"			:"'.$token_num.'",
						"CUST_ID"			:"30426467",
						"RCPT_YMD"			:"'.$param_delivery['rcpt_ymd'].'",
						"CUST_USE_NO"		:"'.$param_delivery['order_update_code'].'",
						"RCPT_DV"			:"02",
						"WORK_DV_CD"		:"01",
						"REQ_DV_CD"			:"01",
						
						"MPCK_KEY"			:"'.$param_delivery['order_update_code'].'_30426467",
						
						"CAL_DV_CD"			:"01",
						"FRT_DV_CD"			:"03",
						"CNTR_ITEM_CD"		:"01",
						"BOX_TYPE_CD"		:"02",
						"BOX_QTY"			:"1",
						"CUST_MGMT_DLCM_CD"	:"30426467",
						
						"SENDR_NM"			:"'.$param_delivery['to_name'].'",
						"SENDR_TEL_NO1"		:"'.$param_delivery['to_mobile'][0].'",
						"SENDR_TEL_NO2"		:"'.$param_delivery['to_mobile'][1].'",
						"SENDR_TEL_NO3"		:"'.$param_delivery['to_mobile'][2].'",
						"SENDR_ZIP_NO"		:"'.$param_delivery['to_zipcode'].'",
						"SENDR_ADDR"		:"'.$param_delivery['sender_addr'].'",
						"SENDR_DETAIL_ADDR"	:"'.$param_delivery['sender_detail_addr'].'",
						
						"RCVR_NM"			:"CJ대한통운",
						"RCVR_TEL_NO1"		:"02",
						"RCVR_TEL_NO2"		:"1588",
						"RCVR_TEL_NO3"		:"1111",
						
						"RCVR_ZIP_NO"		:"000000",
						"RCVR_ADDR"			:"서울특별시 서초구 서초대로 50길 84-10",
						"RCVR_DETAIL_ADDR"	:"408호",
						
						"ORDRR_NM"			:"'.$param_delivery['order_title'].'",
						"ORDRR_TEL_NO1"		:"'.$param_delivery['to_mobile'][0].'",
						"ORDRR_TEL_NO2"		:"'.$param_delivery['to_mobile'][1].'",
						"ORDRR_TEL_NO3"		:"'.$param_delivery['to_mobile'][2].'",
						"ORDRR_ZIP_NO"		:"'.$param_delivery['to_zipcode'].'",
						"ORDRR_ADDR"		:"'.$param_delivery['sender_addr'].'",
						"ORDRR_DETAIL_ADDR"	:"'.$param_delivery['sender_detail_addr'].'",
						
						"INVC_NO"			:"",
						"ORI_INVC_NO"		:"'.$param_delivery['delivery_num'].'",
						"ORI_ORD_NO"		:"'.$param_delivery['order_code'].'",
						
						"PRT_ST"			:"01",
						"DLV_DV"			:"01",
						"ARRAY"				:'.json_encode($param_delivery['param_mpck']).'
					}
				}
			',
			CURLOPT_HTTPHEADER => [
				"CJ-Gateway-APIKey:".$token_num,
				"Content-type:application/json",
				"Accept:application/json"
			],
		]);
		
		$response = curl_exec($curl);
		$err = curl_error($curl);

		if (!$err) {
			$result = json_encode($response,true);
		} else {
			$result = json_encode($err,true);
		}
	}
}

?>