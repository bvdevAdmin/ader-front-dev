<?php	

if (isset($_GET['code']) && $_GET['code'] == "PAY_PROCESS_CANCELED") {
	echo "
		<script>
			location.href = config.base_url + '/pay';
		</script>
	";

	exit();
}

$db->begin_transaction();

if (isset($_GET['orderId'])) {
	/* 1. 수거신청 추가 배송비 결제 처리 */
	
	$update_code = $_GET['orderId'];
	
	$payment_key = null;
	if (isset($_GET['paymentKey'])) {
		$payment_key = $_GET['paymentKey'];
	}
	
	$amount = null;
	if (isset($_GET['amount'])) {
		$amount = $_GET['amount'];
	}
	
	$pg_info = setPG_payment($update_code,$payment_key,$amount);
	
	try {
		if ($pg_info != null) {
			$cnt_op = $db->count("ORDER_PRODUCT","ORDER_UPDATE_CODE = ? AND ORDER_STATUS = 'PWT'",array($update_code));
			if ($cnt_op > 0) {
				$db->update(
					"ORDER_PRODUCT",
					array(
						'ORDER_STATUS'		=>'PCP',
						'PG_MID'			=>$pg_info['pg_mid'],
						'PG_PAYMENT'		=>$pg_info['pg_payment'],
						'PG_PAYMENT_KEY'	=>$pg_info['pg_payment_key'],
						'PG_ISSUE_CODE'		=>$pg_info['pg_issue_code'],
						'PG_CARD_NUMBER'	=>$pg_info['pg_card_number'],
						'PG_STATUS'			=>$pg_info['pg_status'],
						'PG_DATE'			=>$pg_info['pg_date'],
						'PG_PRICE'			=>$pg_info['pg_price'],
						'PG_CURRENCY'		=>$pg_info['pg_currency'],
						'PG_RECEIPT_URL'	=>$pg_info['pg_receipt_url']
					),
					"ORDER_UPDATE_CODE = ? AND ORDER_STATUS = 'PWT'",
					array($update_code)
				);
				
				$table_I = "";
				$cnt_OE = $db->count("ORDER_EXCHANGE","ORDER_UPDATE_CODE = ?",array($update_code));
				if ($cnt_OE > 0) {
					putOrder_status($db,"OEX",$update_code);
					
					$table_I = "ORDER_EXCHANGE";
				}
				
				$cnt_OR = $db->count("ORDER_REFUND","ORDER_UPDATE_CODE = ?",array($update_code));
				if ($cnt_OR > 0) {
					putOrder_status($db,"ORF",$update_code);
					
					$table_I = "ORDER_REFUND";
				}
				
				$token = checkToken($db);
				
				/* 교환/반품 상품 수거 신청 처리 */
				
				echo "
					<script>
						console.log('".$table_I."');
					</script>
				";
				
				$select_delivery_trace_sql = "
					SELECT
						OI.ORDER_CODE		AS ORDER_CODE,
						OT.ORDER_TITLE		AS ORDER_TITLE,
						OI.DELIVERY_NUM		AS DELIVERY_NUM,
						
						OI.TO_NAME			AS TO_NAME,
						OI.TO_MOBILE		AS TO_MOBILE,
						OI.TO_ZIPCODE		AS TO_ZIPCODE,
						OI.TO_ROAD_ADDR		AS TO_ROAD_ADDR,
						OI.TO_DETAIL_ADDR	AS TO_DETAIL_ADDR
					FROM
						ORDER_INFO OI
						
						LEFT JOIN ".$table_I." OT ON
						OI.ORDER_CODE = OT.ORDER_CODE
					WHERE
						OT.ORDER_UPDATE_CODE = ?
				";
				
				$db->query($select_delivery_trace_sql,array($update_code));
				
				foreach($db->fetch() as $data) {
					$param = array(
						'update_code'		=>$update_code,
						
						'order_code'		=>$data['ORDER_CODE'],
						'order_title'		=>$data['ORDER_TITLE'],
						'delivery_num'		=>$data['DELIVERY_NUM'],
						
						'to_name'			=>$data['TO_NAME'],
						'to_mobile'			=>explode("-",$data['TO_MOBILE']),
						'to_zipcode'		=>$data['TO_ZIPCODE'],
						'to_addr'			=>$data['TO_ROAD_ADDR'],
						'to_detail_addr'	=>$data['TO_DETAIL_ADDR'],
						
						'rcpt_ymd'			=>date('Ymd', time())
					);
					
					$delivery_product = setDelivery_product($db,$update_code);
					
					$trace_result = addDelivery_trace($token,$param,$delivery_product);
					if ($trace_result == true) {
						$db->commit();
						
						echo "
							<script>
								alert(
									'교환/반품 접수가 완료되었습니다.',
									function() {
										location.href = config.base_url + '/my/order';
									}
								);
							</script>
						";
					}
				}
			}
		}
	} catch(mysqli_sql_exception $e){
		$db->rollback();
		
		echo "
			<script>
				console.log('".json_encode($e)."');
			</script>
		";
		
		print_r($e);
		
		$json_result['code'] = 301;
		$json_result['msg'] = "주문정보 등록처리중 오류가 발생했습니다.";
	}
} else {
	/* 2. 추가 배송비 미결제 처리 */
	echo "
		<script>
			alert(
				'교환/반품 접수가 완료되었습니다.',
				function() {
					location.href = config.base_url + '/my/order';
				}
			);
		</script>
	";
}

function setPG_payment($order_code,$key,$amount) {
	$pg_info = null;

	$pg_mid				= null;
	$pg_payment			= null;
	$pg_payment_key		= null;
	$pg_issue_code		= null;
	$pg_card_number		= null;
	$pg_status			= null;
	$pg_date			= null;
	$pg_price			= null;
	$pg_currency		= null;
	$pg_receipt_url		= null;
	
	$suppliedAmount		= null;
	$vat				= null;

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
				\"orderId\":\"".$order_code."\",
				\"paymentKey\":\"".$key."\",
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
		$result = json_decode($response,true);

		if (isset($result['status'])) {
			$pg_status = $result['status'];
		}

		if ($pg_status == "DONE") {
			if (isset($result['mId'])) {
				$pg_mid = $result['mId'];
			}

			if (isset($result['method'])) {
				$pg_payment = $result['method'];
			}

			if (isset($result['paymentKey'])) {
				$pg_payment_key = $result['paymentKey'];
			}

			if (isset($result['card']['issuerCode'])) {
				$pg_issue_code = $result['card']['issuerCode'];
			}
			
			if (isset($result['card']['number'])) {
				$pg_card_number = $result['card']['number'];
			}

			if (isset($result['approvedAt'])) {
				$pg_date = $result['approvedAt'];
			}

			if (isset($result['totalAmount'])) {
				$pg_price = $result['totalAmount'];
			}

			if (isset($result['currency'])) {
				$pg_currency = $result['currency'];
			}

			if (isset($result['receipt']['url'])) {
				$pg_receipt_url = $result['receipt']['url'];
			}

			if (isset($result['suppliedAmount'])) {
				$suppliedAmount = $result['suppliedAmount'];
			}
			
			if (isset($result['vat'])) {
				$vat = $result['vat'];
			}

			$pg_info = array(
				'pg_mid'			=>$pg_mid,
				'pg_payment'		=>$pg_payment,
				'pg_payment_key'	=>$pg_payment_key,
				'pg_issue_code'		=>$pg_issue_code,
				'pg_card_number'	=>$pg_card_number,
				'pg_status'			=>$pg_status,
				'pg_date'			=>$pg_date,
				'pg_price'			=>$pg_price,
				'pg_currency'		=>$pg_currency,
				'pg_receipt_url'	=>$pg_receipt_url,
				
				'suppliedAmount'	=>$suppliedAmount,
				'vat'				=>$vat
			);
		}
		
		curl_close($curl);
	}
	
	return $pg_info;
}

function putOrder_status($db,$param_status,$update_code) {
	$table_I = array(
		'OEX'		=>"ORDER_EXCHANGE",
		'ORF'		=>"ORDER_REFUND"
	);
	
	$table_P = array(
		'OEX'		=>"ORDER_PRODUCT_EXCHANGE",
		'ORF'		=>"ORDER_PRODUCT_REFUND"
	);
	
	$status_W = array(
		'OEX'		=>"OET",
		'ORF'		=>"ORT"
	);
	
	$status_O = array(
		'OEX'		=>"OEX",
		'ORF'		=>"ORF"
	);
	
	$db->update(
		$table_I[$param_status],
		array(
			'ORDER_STATUS'		=>$status_O[$param_status]
		),
		"ORDER_STATUS = ? AND ORDER_UPDATE_CODE = ?",
		array($status_W[$param_status],$update_code)
	);
	
	$db->update(
		$table_P[$param_status],
		array(
			'ORDER_STATUS'		=>$status_O[$param_status]
		),
		"ORDER_STATUS = ? AND ORDER_UPDATE_CODE = ?",
		array($status_W[$param_status],$update_code)
	);
}

function setDelivery_product($db,$update_code) {
	$product = array();
	
	$select_delivery_product_sql = "
		SELECT
			PE.PRODUCT_CODE			AS PRODUCT_CODE,
			PE.PRODUCT_NAME			AS PRODUCT_NAME,
			PE.PRODUCT_QTY			AS PRODUCT_QTY,
			PE.BARCODE				AS BARCODE,
			PE.OPTION_NAME			AS OPTION_NAME,
			PE.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT_EXCHANGE PE
		WHERE
			PE.ORDER_UPDATE_CODE = ?
		
		UNION
		
		SELECT
			PF.PRODUCT_CODE			AS PRODUCT_CODE,
			PF.PRODUCT_NAME			AS PRODUCT_NAME,
			PF.PRODUCT_QTY			AS PRODUCT_QTY,
			PF.BARCODE				AS BARCODE,
			PF.OPTION_NAME			AS OPTION_NAME,
			PF.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT_REFUND PF
		WHERE
			PF.ORDER_UPDATE_CODE = ?
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
					"CUST_USE_NO"		:"'.$param['update_code'].'QZASDAD",
					"RCPT_DV"			:"02",
					"WORK_DV_CD"		:"01",
					"REQ_DV_CD"			:"01",
					
					"MPCK_KEY"			:"'.$param['update_code'].'_30426467",
					
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
<div style="height:60vh;"></div>