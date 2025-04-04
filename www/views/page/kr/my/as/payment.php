<?php	

$payment_code = null;
if (isset($_GET['orderId'])) {
	$payment_code = $_GET['orderId'];
}

$payment_key = null;
if (isset($_GET['paymentKey'])) {
	$payment_key = $_GET['paymentKey'];
}

$amount = null;
if (isset($_GET['amount'])) {
	$amount = $_GET['amount'];
}

if (isset($_SESSION['MEMBER_IDX'])) {
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.tosspayments.com/v1/payments/confirm",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "
			{
				\"orderId\":\"".$payment_code."\",
				\"paymentKey\":\"".$payment_key."\",
				\"amount\":".$amount."
			}
		",
		CURLOPT_HTTPHEADER => [
			"Authorization: Basic dGVzdF9za19ONU9XUmFwZEE4ZFkyMTc1N2piM28xekVxWktMOg==",
			"Content-Type: application/json"
		],
	]);
	
	$response = curl_exec($curl);
	$err = curl_error($curl);

	if (!$err) {
		$result = json_decode($response);

		$pg_mid = null;
		if (isset($result->mId)) {
			$pg_mid = $result->mId;
		}
		 
		if ($pg_mid != null) {
			$pg_payment = null;
			if (isset($result->method)) {
				$pg_payment = $result->method;
			}

			$pg_payment_key = null;
			if (isset($result->paymentKey)) {
				$pg_payment_key = $result->paymentKey;
			}

			$pg_status = null;
			if (isset($result->status)) {
				$pg_status = $result->status;
			}
			
			$pg_price = null;
			if (isset($result->totalAmount)) {
				$pg_price = $result->totalAmount;
			}

			$pg_currency = null;
			if (isset($result->currency)) {
				$pg_currency = $result->currency;
			}

			$pg_receipt_url = null;
			if (isset($result->receipt)) {
				$receipt = $result->receipt;
				if ($receipt != null) {
					$pg_receipt_url = $receipt->url;
				}
			}
			
			$pg_date = null;
			if (isset($result->approvedAt)) {
				$pg_date = $result->approvedAt;
			}
						
			try {
				if ($payment_code != null && $pg_mid != null) {
					$member_as = $db->get("MEMBER_AS","IDX = (SELECT AS_IDX FROM AS_PAYMENT WHERE PAYMENT_CODE = ?)",array($payment_code));
					if (sizeof($member_as) > 0) {
						$member_as = $member_as[0];
						
						$payment_type = "";
						if (strpos($payment_code,"D")) {
							$payment_type = "D";
						} else if (strpos($payment_code,"P")) {
							$payment_type = "P";
						}
						
						$db->update(
							"AS_PAYMENT",
							array(
								'PAYMENT_TYPE'		=>$payment_type,
								'PAYMENT_CODE'		=>$payment_code,
								'PAYMENT_STATUS'	=>"PCP",
								
								'PG_MID'			=>$pg_mid,
								'PG_PAYMENT'		=>$pg_payment,
								'PG_PAYMENT_KEY'	=>$pg_payment_key,
								'PG_STATUS'			=>$pg_status,
								'PG_DATE'			=>$pg_date,
								'PG_PRICE'			=>$pg_price,
								'PG_CURRENCY'		=>$pg_currency,
								'PG_RECEIPT_URL'	=>$pg_receipt_url,
								
								'UPDATE_DATE'		=>NOW(),
								'UPDATER'			=>$_SESSION['MEMBER_ID']
							),
							"PAYMENT_CODE = ?",
							array($payment_code)
						);
						
						$db->update(
							"MEMBER_AS",
							array(
								'AS_PRICE_FLG'		=>1,
								
								'UPDATE_DATE'		=>NOW(),
								'UPDATER'			=>$_SESSION['MEMBER_ID']
							),
							"AS_CODE = ?",
							array($member_as['AS_CODE'])
						);
						
						/* A/S 수거신청 제품 배송 추적 등록 */
						if ($payment_type == "D") {
							$token = checkToken($db);
				
							$select_delivery_trace_sql = "
								SELECT
									MA.AS_CODE			AS ORDER_CODE,
									'A/S 수거신청 상품'	 AS ORDER_TITLE,
									'0000000000'		AS DELIVERY_NUM,
									
									MA.TO_NAME			AS TO_NAME,
									MA.TO_MOBILE		AS TO_MOBILE,
									MB.TEL_MOBILE		AS MEMBER_MOBILE,
									MA.TO_ZIPCODE		AS TO_ZIPCODE,
									MA.TO_ROAD_ADDR		AS TO_ROAD_ADDR,
									MA.TO_DETAIL_ADDR	AS TO_DETAIL_ADDR
								FROM
									MEMBER_AS MA

									LEFT JOIN MEMBER MB ON
									MA.MEMBER_IDX = MB.IDX
								WHERE
									MA.AS_CODE = ?
							";
							
							$db->query($select_delivery_trace_sql,array($update_code));
							
							foreach($db->fetch() as $data) {
								$to_mobile = array();
								if (count(explode("-",$data['TO_MOBILE'])) == 3) {
									$to_mobile = explode("-",$data['TO_MOBILE']);
								} else {
									$to_mobile = explode("-",$data['MEMBER_MOBILE']);
								}
								
								$param = array(
									'update_code'		=>$update_code,
									
									'order_code'		=>$data['ORDER_CODE'],
									'order_title'		=>$data['ORDER_TITLE'],
									'delivery_num'		=>$data['DELIVERY_NUM'],
									
									'to_name'			=>$data['TO_NAME'],
									'to_mobile'			=>$to_mobile,
									'to_zipcode'		=>$data['TO_ZIPCODE'],
									'to_addr'			=>$data['TO_ROAD_ADDR'],
									'to_detail_addr'	=>$data['TO_DETAIL_ADDR'],
									
									'rcpt_ymd'			=>date('Ymd', time())
								);
								
								$delivery_product = setDelivery_product($db,$payment_code);
								
								$trace_result = addDelivery_trace($token,$param,$delivery_product);
								if ($trace_result == true) {
									$db->commit();
									
									echo "
										<script>
											alert(
												'A/S 제품 수거신청이 완료되었습니다.',
												function() {
													location.href = config.base_url + '/my/as/status/' + ".$member_as['AS_IDX'].";
												}
											);
										</script>
									";
								}
							}
						}
					}
				}
				
				$db->commit();
				
				echo "
					<script>
						alert(
							'A/S 금액 결제가 완료되었습니다.',
							function() {
								location.href = config.base_url + '/my/as/status/' + ".$member_as['AS_IDX'].";
							}
						)
					</script>
				";
			} catch(mysqli_sql_exception $exception){
				$db->rollback();
				
				print_r($e);
				
				$json_result['code'] = 301;
				$json_result['msg'] = "주문정보 등록처리중 오류가 발생했습니다.";
				
				echo json_encode($json_result);
				exit;
			}
		}
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,"KR",'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

function setDelivery_product($db,$payment_code) {
	$product = array();
	
	$select_delivery_product_sql = "
		SELECT
			AP.AS_CODE				AS PRODUCT_CODE,
			'A/S 수거신청 상품'			AS PRODUCT_NAME,
			1						AS PRODUCT_QTY,
			AP.PAYMENT_CODE			AS BARCODE,
			'수거신청'					AS OPTION_NAME,
			AP.PG_PRICE				AS PRODUCT_PRICE
		FROM
			AS_PAYMENT AP
		WHERE
			AP.PAYMENT_CODE = ?
	";
	
	$db->query($select_delivery_product_sql,array($update_code,$update_code));
	
	$seq = 1;
	
	foreach($db->fetch() as $data) {
		$tmp_product = '
			{
				 "MPCK_SEQ":"'.$seq.'",
				 "GDS_CD":"'.$data['PRODUCT_CODE'].'",
				 "GDS_NM":"'.$data['PRODUCT_NAME'].'",
				 "GDS_QTY":"'.$data['PRODUCT_QTY'].'",
				 "UNIT_CD":"'.$data['BARCODE'].'",
				 "UNIT_NM":"'.$data['OPTION_NAME'].'",
				 "GDS_AMT":"'.$data['PRODUCT_PRICE'].'"
			}
		';
		
		array_push($product,$tmp_product);
		
		$seq++;
	}
	
	$delivery_product = '
		"ARRAY":[
			 '.implode(",",$product).'
		]
	';
	
	return $delivery_product;
}

function addDelivery_trace($token,$param,$product) {
	$trace_result = false;
	
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
					"TOKEN_NUM"			:"'.$token.'",
					"CUST_ID"			:"30426467",
					"RCPT_YMD"			:"'.$param['rcpt_ymd'].'",
					"CUST_USE_NO"		:"'.$param['update_code'].'",
					"RCPT_DV"			:"02",
					"WORK_DV_CD"		:"01",
					"REQ_DV_CD"			:"01",
					
					"MPCK_KEY"			:"'.$param['update_code'].'",
					
					"CAL_DV_CD"			:"01",
					"FRT_DV_CD"			:"03",
					"CNTR_ITEM_CD"		:"01",
					"BOX_TYPE_CD"		:"02",
					"BOX_QTY"			:"1",
					"CUST_MGMT_DLCM_CD"	:"30426467",
					
					"SENDR_NM"			:"'.$param['to_name'].'",
					"SENDR_TEL_NO1"		:"'.$param['to_mobile'][0].'",
					"SENDR_TEL_NO2"		:"'.$param['to_mobile'][1].'",
					"SENDR_TEL_NO3"		:"'.$param['to_mobile'][2].'",
					
					"SENDR_ZIP_NO"		:"'.$param['to_zipcode'].'",
					"SENDR_ADDR"		:"'.$param['to_addr'].'",
					"SENDR_DETAIL_ADDR"	:"'.$param['to_detail_addr'].'",
					
					"RCVR_NM"			:"CJ대한통운",
					"RCVR_TEL_NO1"		:"02",
					"RCVR_TEL_NO2"		:"1588",
					"RCVR_TEL_NO3"		:"1111",
					
					"RCVR_ZIP_NO"		:"000000",
					"RCVR_ADDR"			:"서울특별시 서초구 서초대로 50길 84-10",
					"RCVR_DETAIL_ADDR"	:"408호",
					
					"ORDRR_NM"			:"'.$param['order_title'].'",
					"ORDRR_TEL_NO1"		:"'.$param['to_mobile'][0].'",
					"ORDRR_TEL_NO2"		:"'.$param['to_mobile'][1].'",
					"ORDRR_TEL_NO3"		:"'.$param['to_mobile'][2].'",
					"ORDRR_ZIP_NO"		:"'.$param['to_zipcode'].'",
					"ORDRR_ADDR"		:"'.$param['to_addr'].'",
					"ORDRR_DETAIL_ADDR"	:"'.$param['to_detail_addr'].'",
					
					"INVC_NO"			:"",
					"ORI_INVC_NO"		:"'.$param['delivery_num'].'",
					"ORI_ORD_NO"		:"'.$param['order_code'].'",
					
					"PRT_ST"			:"01",
					
					"DLV_DV"			:"01",
					"MPCK_SEQ"			:1,
					'.$product.'
				}
			}
		',
		CURLOPT_HTTPHEADER => [
			"CJ-Gateway-APIKey:".$token,
			"Content-type:application/json",
			"Accept:application/json"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		$result = json_decode($response,true);
		
		echo "
			<script>
				console.log('".json_encode($response)."');
			</script>
		";
		
		if (isset($result['RESULT_CD']) && $result['RESULT_CD'] == "S") {
			$trace_result = true;
		}
	}
	
	return $trace_result;
}

?>
