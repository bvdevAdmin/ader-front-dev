<?php
/*
 +=============================================================================
 | 
 | CJ 대한통운 - (일반)예약취소
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

include_once(dir_f_api."/delivery/token.php");

$token = checkToken($db);

/* API 헤더 설정 */
$headers = array(
	"Content-type"	=>"application/json",
	"Accept"		=>"application/json"
);

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://dxapi.cjlogistics.com:5052/gateway/PA-P-RegBook/1.0",
	CURLOPT_HEADER			=>true,
	CURLOPT_HTTPHEADER		=>$headers,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => "
		{
			\"TOKEN_NUM\":\"23c5c70e-97f8-4a46-9d4a-8b15b098429z\",
			\"CUST_ID\":\"계약된	고객ID를	넣어	주세요\",
			\"RCPT_YMD\":\"20200820\",
			\"CUST_USE_NO\":\"12345678\",
			
			\"RCPT_DV\":\"02\",
			\"WORK_DV_CD\":\"01\",
			\"REQ_DV_CD\":\"01\",
			\"MPCK_KEY\":\"1234567893\",
			\"CAL_DV_CD\":\"1\",
			\"FRT_DV_CD\":\"03\",
			\"CNTR_ITEM_CD\":\"01\",
			\"BOX_TYPE_CD\":\"02\",
			\"BOX_QTY\":\"1\",
			\"FRT\":\"6250\",
			\"CUST_MGMT_DLCM_CD\":\"T00002\",
			
			\"SENDR_NM\":\"회사명\",
			\"SENDR_TEL_NO1\":\"02\",
			\"SENDR_TEL_NO2\":\"1577\",
			\"SENDR_TEL_NO3\":\"1111\",
			\"SENDR_CELL_NO1\":\"02\",
			\"SENDR_CELL_NO2\":\"1577\",
			\"SENDR_CELL_NO3\":\"1111\",
			\"SENDR_SAFE_NO1\":\"02\",
			\"SENDR_SAFE_NO2\":\"1577\",
			\"SENDR_SAFE_NO3\":\"1111\",
			\"SENDR_ZIP_NO\":\"000000\",
			\"SENDR_ADDR\":\"서울	송파구\",
			\"SENDR_DETAIL_ADDR\":\"무슨동\",
			
			\"RCVR_NM\":\"CJ대한통운\",
			\"RCVR_TEL_NO1\":\"02\",
			\"RCVR_TEL_NO2\":\"1588\",
			\"RCVR_TEL_NO3\":\"1111\",
			\"RCVR_CELL_NO1\":\"02\",
			\"RCVR_CELL_NO2\":\"1588\",
			\"RCVR_CELL_NO3\":\"1111\",
			\"RCVR_SAFE_NO1\":\"02\",
			\"RCVR_SAFE_NO2\":\"1588\",
			\"RCVR_SAFE_NO3\":\"1111\",
			\"RCVR_ZIP_NO\":\"000000\",
			\"RCVR_ADDR\":\"서울특별시	중구\",
			\"RCVR_DETAIL_ADDR\":\"아무개동\",
			
			\"ORDRR_NM\":\"테스트\",
			\"ORDRR_TEL_NO1\":\"02\",
			\"ORDRR_TEL_NO2\":\"1599\",
			\"ORDRR_TEL_NO3\":\"9999\",
			\"ORDRR_CELL_NO1\":\"02\",
			\"ORDRR_CELL_NO2\":\"1599\",
			\"ORDRR_CELL_NO3\":\"9999\"
			\"ORDRR_SAFE_NO1\":\"02\",
			\"ORDRR_SAFE_NO2\":\"1599\",
			\"ORDRR_SAFE_NO3\":\"9999\",
			\"ORDRR_ZIP_NO\":\"11111\",
			\"ORDRR_ADDR\":\"서울특별시	마포구\",
			\"ORDRR_DETAIL_ADDR\":\"성산동\",
			
			\"INVC_NO\":\"11111\",
			\"ORI_INVC_NO\":\"1234567890\",
			\"ORI_ORD_NO\":\"11\",
			
			\"COLCT_EXPCT_YMD\":\"20210121\",
			\"COLCT_EXPCT_HOUR\":\"11\",
			
			\"SHIP_EXPCT_YMD\":\"20210121\",
			\"SHIP_EXPCT_HOUR\":\"11\",
			
			\"PRT_ST\":\"1\",
			
			\"ARTICLE_AMT\":\"1\",
			
			\"REMARK_1\":\"샘플입니다\",
			\"REMARK_2\":\"샘플입니다\",
			\"REMARK_3\":\"샘플입니다\",
			
			\"COD_YN\":\"20\",
			
			\"ETC_1\":\"2020-06-04T13:38:00\",
			\"ETC_2\":\"1\",
			\"ETC_3\":\"T00002\",
			\"ETC_4\":\"11\",
			\"ETC_5\":\"11\",
			
			\"DLV_DV\":\"01\",
			
			\"RCPT_SERIAL\":\"2008403063820\",
			
			\"ARRAY\":[
				{
					\"MPCK_SEQ\":\"1\",
					\"GDS_CD\":\"11\",
					\"GDS_NM\":\"[6250.0]	테스트자료	^^\",
					\"GDS_QTY\":\"1\",
					\"UNIT_CD\":\"11\",
					\"UNIT_NM\":\"테스트1\",
					\"GDS_AMT\":\"111\"
				},
				{
					\"MPCK_SEQ\":\"2\",
					\"GDS_CD\":\"22\",
					\"GDS_NM\":\"테스트2\",
					\"GDS_QTY\":\"2\",
					\"UNIT_CD\":\"11\",
					\"UNIT_NM\":\"테스트2\",
					\"GDS_AMT\":\"222\"
				},
				{
					\"MPCK_SEQ\":\"3\",
					\"GDS_CD\":\"33\",
					\"GDS_NM\":\"테스트3\",
					\"GDS_QTY\":\"3\",
					\"UNIT_CD\":\"33\",
					\"UNIT_NM\":\"테스트3\",
					\"GDS_AMT\":\"333\"
				}
			]
		}
	"
]);

$response = curl_exec($curl);
$err = curl_error($curl);

if (!$err) {
	$result = json_decode($response,true);
	
	print_r($result);
}

?>