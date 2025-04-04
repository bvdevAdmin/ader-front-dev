<?php
/*
 +=============================================================================
 | 
 | CJ 대한통운 - (일반) 예약 접수
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.10.27
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/delivery/common.php");

$token_num = checkToken($db);

$rcpt_ymd = date('Ymd', time());

$order_code = null;
$order_update_code = null;
$order_title = null;

$to_name = null;
$to_mobile = null;
$to_zipcode = null;

$sender_addr = null;
$sender_detail_addr = null;

$order_table = null;
$order_type = "ORF";
if ($order_type == "OEX") {
	$order_table = "ORDER_EXCHANGE";
} else if ($order_type == "ORF") {
	$order_table = "ORDER_REFUND";
}

$select_order_info_sql = "
	SELECT
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
		OT.ORDER_UPDATE_CODE = '20231219-1706524-R-3'
";

$db->query($select_order_info_sql);

foreach($db->fetch() as $data) {
	$order_code = $data['ORDER_CODE'];
	$order_update_code = $data['ORDER_UPDATE_CODE'];
	$order_title = $data['ORDER_TITLE'];
	$delivery_num = $data['DELIVERY_NUM'];
	
	$to_name = $data['TO_NAME'];
	$to_mobile = explode("-",$data['TO_MOBILE']);
	$to_zipcode = $data['TO_ZIPCODE'];
	
	$tmp_addr = $data['TO_ROAD_ADDR']." ".$data['TO_DETAIL_ADDR'];
	$road_addr = explode(" ",$tmp_addr);
	
	$sender_addr = $road_addr[0]." ".$road_addr[1]." ".$road_addr[2];
	$sender_detail_addr = str_replace($sender_addr." ","",$tmp_addr);
}

print_r('
	{
		"DATA":{
			"TOKEN_NUM"			:"'.$token_num.'",
			"CUST_ID"			:"30426467",
			"RCPT_YMD"			:"'.$rcpt_ymd.'",
			"CUST_USE_NO"		:"'.$order_update_code.'",
			"RCPT_DV"			:"02",
			"WORK_DV_CD"		:"01",
			"REQ_DV_CD"			:"01",
			
			"MPCK_KEY"			:"'.$order_update_code.'_30426467",
			
			"CAL_DV_CD"			:"01",
			"FRT_DV_CD"			:"03",
			"CNTR_ITEM_CD"		:"01",
			"BOX_TYPE_CD"		:"02",
			"BOX_QTY"			:"1",
			"CUST_MGMT_DLCM_CD"	:"30426467",
			
			"SENDR_NM"			:"'.$to_name.'",
			"SENDR_TEL_NO1"		:"'.$to_mobile[0].'",
			"SENDR_TEL_NO2"		:"'.$to_mobile[1].'",
			"SENDR_TEL_NO3"		:"'.$to_mobile[2].'",
			"SENDR_ZIP_NO"		:"'.$to_zipcode.'",
			"SENDR_ADDR"		:"'.$sender_addr.'",
			"SENDR_DETAIL_ADDR"	:"'.$sender_detail_addr.'",
			
			"RCVR_NM"			:"CJ대한통운",
			"RCVR_TEL_NO1"		:"02",
			"RCVR_TEL_NO2"		:"1588",
			"RCVR_TEL_NO3"		:"1111",
			"RCVR_ZIP_NO"		:"000000",
			"RCVR_ADDR"			:"서울특별시 서초구 서초대로 50길 84-10",
			"RCVR_DETAIL_ADDR"	:"408호",
			
			"ORDRR_NM"			:"'.$order_title.'",
			"ORDRR_TEL_NO1"		:"'.$to_mobile[0].'",
			"ORDRR_TEL_NO2"		:"'.$to_mobile[1].'",
			"ORDRR_TEL_NO3"		:"'.$to_mobile[2].'",
			"ORDRR_ZIP_NO"		:"'.$to_zipcode.'",
			"ORDRR_ADDR"		:"'.$sender_addr.'",
			"ORDRR_DETAIL_ADDR"	:"'.$sender_detail_addr.'",
			
			"INVC_NO"			:"",
			"ORI_INVC_NO"		:"'.$delivery_num.'",
			"ORI_ORD_NO"		:"'.$order_code.'",
			
			"PRT_ST"			:"01",
			
			"MPCK_SEQ"			:1
		}
	}
');

/*
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
				"RCPT_YMD"			:"'.$rcpt_ymd.'",
				"CUST_USE_NO"		:"'.$order_update_code.'",
				"RCPT_DV"			:"02",
				"WORK_DV_CD"		:"01",
				"REQ_DV_CD"			:"01",
				
				"MPCK_KEY"			:"'.$order_update_code.'_30426467",
				
				"CAL_DV_CD"			:"01",
				"FRT_DV_CD"			:"03",
				"CNTR_ITEM_CD"		:"01",
				"BOX_TYPE_CD"		:"02",
				"BOX_QTY"			:"1",
				"CUST_MGMT_DLCM_CD"	:"30426467",
				
				"SENDR_NM"			:"'.$to_name.'",
				"SENDR_TEL_NO1"		:"'.$to_mobile[0].'",
				"SENDR_TEL_NO2"		:"'.$to_mobile[1].'",
				"SENDR_TEL_NO3"		:"'.$to_mobile[2].'",
				"SENDR_ZIP_NO"		:"'.$to_zipcode.'",
				"SENDR_ADDR"		:"'.$sender_addr.'",
				"SENDR_DETAIL_ADDR"	:"'.$sender_detail_addr.'",
				
				"RCVR_NM"			:"CJ대한통운",
				"RCVR_TEL_NO1"		:"02",
				"RCVR_TEL_NO2"		:"1588",
				"RCVR_TEL_NO3"		:"1111",
				
				"RCVR_ZIP_NO"		:"000000",
				"RCVR_ADDR"			:"서울특별시 서초구 서초대로 50길 84-10",
				"RCVR_DETAIL_ADDR"	:"408호",
				
				"ORDRR_NM"			:"'.$order_title.'",
				"ORDRR_TEL_NO1"		:"'.$to_mobile[0].'",
				"ORDRR_TEL_NO2"		:"'.$to_mobile[1].'",
				"ORDRR_TEL_NO3"		:"'.$to_mobile[2].'",
				"ORDRR_ZIP_NO"		:"'.$to_zipcode.'",
				"ORDRR_ADDR"		:"'.$sender_addr.'",
				"ORDRR_DETAIL_ADDR"	:"'.$sender_detail_addr.'",
				
				"INVC_NO"			:"",
				"ORI_INVC_NO"		:"'.$delivery_num.'",
				"ORI_ORD_NO"		:"'.$order_code.'",
				
				"PRT_ST"			:"01",
				
				"MPCK_SEQ"			:1
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
	
	$json_result['data'] = $result;
	
	echo json_decode($json_result);
	exit;
}
*/
?>