<?php
/*
 +=============================================================================
 | 
 | CJ 대한통운 - (일반) 상품추적 (예약정보기준)
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

include_once("/var/www/www/api/delivery/common.php");

$token_num = checkToken($db);

//$req_dt		= $_POST['req_dt'];
$req_dt = "20231220";

if ($req_dt != null) {
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://dxapi-dev.cjlogistics.com:5054/ReqMssGdsTrc",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"DATA" : {
					"CUST_ID"	:"30426467",
					"REQ_DT"	:"'.$req_dt.'",
					"SND_YN"	:"N",
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
			$result_data = $result['DATA'];
			
			$trace_info = array();
			
			foreach($result_data as $result) {
				$order_update_code = $result['CUST_USE_NO'];
				
				$select_order_sql = "
					SELECT
						*
					FROM
						(
							SELECT
								'OEF'					AS ORDER_TYPE,
								OE.ORDER_CODE			AS ORDER_CODE,
								OE.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
								OE.PRODUCT_CODE			AS PRODUCT_CODE,
								OE.PRODUCT_NAME			AS PRODUCT_NAME,
								(
									SELECT
										S_OO.BARCODE
									FROM
										ORDERSHEET_OPTION S_OO
									WHERE
										S_OO.IDX = OE.PREV_OPTION_IDX
								)						AS BARCODE,
								(
									SELECT
										S_OO.OPTION_NAME
									FROM
										ORDERSHEET_OPTION S_OO
									WHERE
										S_OO.IDX = OE.PREV_OPTION_IDX
								)						AS OPTION_NAME,
								OE.PRODUCT_QTY			AS PRODUCT_QTY,
								OE.PRODUCT_PRICE		AS PRODUCT_PRICE,
								OE.DEPTH1_IDX			AS DEPTH1_IDX,
								OE.DEPTH2_IDX			AS DEPTH2_IDX,
								OE.REASON_MEMO			AS REASON_MEMO
							FROM
								ORDER_PRODUCT_EXCHANGE OE
							WHERE
								ORDER_UPDATE_CODE = '".$order_update_code."' AND
								OE.PRODUCT_IDX > 0
							
							UNION
							
							SELECT
								'ORF'					AS ORDER_TYPE,
								OF.ORDER_CODE			AS ORDER_CODE,
								OF.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
								OF.PRODUCT_CODE			AS PRODUCT_CODE,
								OF.PRODUCT_NAME			AS PRODUCT_NAME,
								OF.BARCODE				AS BARCODE,
								OF.OPTION_NAME			AS OPTION_NAME,
								OF.PRODUCT_QTY			AS PRODUCT_QTY,
								OF.PRODUCT_PRICE		AS PRODUCT_PRICE,
								OF.DEPTH1_IDX			AS DEPTH1_IDX,
								OF.DEPTH2_IDX			AS DEPTH2_IDX,
								OF.REASON_MEMO			AS REASON_MEMO
							FROM
								ORDER_PRODUCT_REFUND OF
							WHERE
								OF.ORDER_UPDATE_CODE = '".$order_update_code."' AND
								OF.PRODUCT_IDX > 0
						) AS TMP
					ORDER BY
						ORDER_UPDATE_CODE ASC
				";
				
				$db->query($select_order_sql);
				
				foreach($db->fetch() as $data) {
					$txt_order_type = null;
					
					$order_type = $data['ORDER_TYPE'];
					if ($order_type == "OEX") {
						$txt_order_type = "주문교환";
					} else if ($order_type == "ORF") {
						$txt_order_type = "주문반품";
					}
					
					$depth1_idx = $data['DEPTH1_IDX'];
					$reason_depth_1 = $db->get("REASON_DEPTH_1","IDX = ? AND COUNTRY = 'KR'",array($depth1_idx))[0];
					$depth1_txt = $reason_depth_1['REASON_TXT'];
					
					$depth2_idx = $data['DEPTH2_IDX'];
					$reason_depth_2 = $db->get("REASON_DEPTH_2","IDX = ? AND DEPTH_1_IDX = ? AND COUNTRY = 'KR'",array($depth2_idx,$depth1_idx))[0];
					$depth2_txt = $reason_depth_2['REASON_TXT'];
					
					$trace_info[] = array(
						'order_code'			=>$data['ORDER_CODE'],
						'order_update_code'		=>$data['ORDER_UPDATE_CODE'],
						'product_code'			=>$data['PRODUCT_CODE'],
						'product_name'			=>$data['PRODUCT_NAME'],
						'barcode'				=>$data['BARCODE'],
						'option_name'			=>$data['OPTION_NAME'],
						'product_qty'			=>$data['PRODUCT_QTY'],
						'product_price'			=>$data['PRODUCT_PRICE'],
						
						'crg_st_nm'				=>$result['CRG_ST_NM'],
						'scan_ymd'				=>$result['SCAN_YMD'],
						'scan_hour'				=>$result['SCAN_HOUR'],
						'dealt_bran_nm'			=>$result['DEALT_BRAN_NM'],
						'dealemp_nm'			=>$result['DEALEMP_NM'],
						'acptr_nm'				=>$result['ACPTR_NM'],
						'detail_rsn'			=>$result['DETAIL_RSN'],
						
						'depth1_txt'			=>$depth1_txt,
						'depth2_txt'			=>$depth2_txt,
						'reason_memo'			=>$data['REASON_MEMO']
					);
				}
				
				$trace_info[] = array(
					'crg_st_nm'				=>$result['CRG_ST_NM'],
					'scan_ymd'				=>$result['SCAN_YMD'],
					'scan_hour'				=>$result['SCAN_HOUR'],
					'dealt_bran_nm'			=>$result['DEALT_BRAN_NM'],
					'dealemp_nm'			=>$result['DEALEMP_NM'],
					'acptr_nm'				=>$result['ACPTR_NM'],
					'detail_rsn'			=>$result['DETAIL_RSN']
				);
			}
			
			$json_result['data'] = $trace_info;
			
			print_r($json_result);
			
			//echo json_decode($json_result);
			//exit;
		}
	}
}

?>