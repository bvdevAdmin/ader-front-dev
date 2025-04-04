<?php	
include_once(dir_f_api."/mypage/order/order-common.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = null;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$housing_type = null;
if (isset($_GET['housing_type'])) {
	$housing_type = $_GET['housing_type'];
}

$housing_company = "NULL";
if (isset($_GET['housing_company'])) {
	$housing_company = $_GET['housing_company'];
}

$housing_num = "NULL";
if (isset($_GET['housing_num'])) {
	$housing_num = $_GET['housing_num'];
}

$pg_info = null;

$order_code = null;
if (isset($_GET['order_code'])) {
	$order_code = $_GET['order_code'];
}

$order_update_code = null;
if (isset($_GET['orderId'])) {
	$order_update_code = $_GET['orderId'];
	
	$payment_key = null;
	if (isset($_GET['paymentKey'])) {
		$payment_key = $_GET['paymentKey'];
	}
	
	$amount = null;
	if (isset($_GET['amount'])) {
		$amount = $_GET['amount'];
	}
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.tosspayments.com/v1/payments/confirm",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => '
			{
				"orderId":"'.$order_update_code.'",
				"paymentKey":"'.$payment_key.'",
				"amount":'.$amount.'
			}
		',
		CURLOPT_HTTPHEADER => [
			"Authorization: Basic dGVzdF9za19ONU9XUmFwZEE4ZFkyMTc1N2piM28xekVxWktMOg==",
			"Content-Type: application/json"
		],
	]);

	$response = curl_exec($curl);
	
	$err = curl_error($curl);
	
	if (!$err) {
		$result = json_decode($response,true);
		
		$pg_info = setPgInfo($result);
		
		try {
			$cnt_OEX = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_UPDATE_CODE = '".$order_update_code."'");
			if ($cnt_OEX > 0) {
				putTmpOrderTable($db,"OEX",$order_update_code,$pg_info,$member_id);
			}
			
			$cnt_ORF = $db->count("TMP_ORDER_PRODUCT_REFUND","ORDER_UPDATE_CODE = '".$order_update_code."'");
			if ($cnt_ORF > 0) {
				putTmpOrderTable($db,"ORF",$order_update_code,$pg_info,$member_id);
			}
			
			$db->commit();
		} catch(mysqli_sql_exception $exception){
			$db->rollback();
			print_r($exception);
			
			$json_result['code'] = 301;
			$json_result['msg'] = "주문정보 등록처리중 오류가 발생했습니다.";
		}
	} else {
		print_r($err);
	}
}

echo "
	<script>
		$.ajax({
			type: 'post',
			url: api_location + 'mypage/order/put',
			data: {
				'order_code': '".$order_code."',
				'order_update_code' : '".$order_update_code."'
			},
			dataType: 'json',
			async:false,
			error: function (d) {
				notiModal('주문상품 교환/반품처리중 오류가 발생했습니다.');
				//location.href='/mypage/main/orderlist/refund?order_code=".$order_code."';
			},
			success: function (d) {
				if (d.code == 200) {
					location.href='/mypage?mypage_type=orderlist';
				} else {
					notiModal(d.msg);
					//location.href='/mypage/main/orderlist/refund?order_code=".$order_code."';
				}
			}
		});
	</script>
";	

function setPgInfo($result) {
	$pg_info = null;
	
	$pg_mid = null;
	if (isset($result['mId'])) {
		$pg_mid = $result['mId'];
	}
	 
	$pg_payment = null;
	if (isset($result['method'])) {
		$pg_payment = $result['method'];
	}

	$pg_payment_key = null;
	if (isset($result['paymentKey'])) {
		$pg_payment_key = $result['paymentKey'];
	}

	$pg_status = null;
	if (isset($result['status'])) {
		$pg_status = $result['status'];
	}
		
	$pg_price = null;
	if (isset($result['totalAmount'])) {
		$pg_price = $result['totalAmount'];
	}

	$pg_currency = null;
	if (isset($result['currency'])) {
		$pg_currency = $result['currency'];
	}

	$pg_receipt_url = null;
	if (isset($result['receipt'])) {
		$receipt = $result['receipt'];
		if ($receipt != null) {
			$pg_receipt_url = $receipt['url'];
		}
	}
		
	$pg_date = null;
	if (isset($result['approvedAt'])) {
		$pg_date = $result['approvedAt'];
	}
	
	$pg_issue_code = "NULL";
	$pg_card_number = "NULL";
	
	$card = null;
	if (isset($result['card'])) {
		$card = $result['card'];
		
		if ($card != null) {
			if (isset($card['issuerCode'])) {
				$pg_issue_code = $card['issuerCode'];
			}
			
			if (isset($card['number'])) {
				$pg_card_number = $card['number'];
			}
		}
	}
	
	$pg_provider = "NULL";
	
	$easy_pay = null;
	if (isset($result['easyPay'])) {
		$easy_pay = $result['easyPay'];
		if ($easy_pay != null) {
			$pg_provider = $easy_pay['provider'];
		}
	}
	
	if ($pg_payment_key != null) {
		$pg_info = array(
			'pg_mid'			=>$pg_mid,
			'pg_payment'		=>$pg_payment,
			'pg_payment_key'	=>$pg_payment_key,
			'pg_status'			=>$pg_status,
			'pg_price'			=>$pg_price,
			'pg_currency'		=>$pg_currency,
			'pg_receipt_url'	=>$pg_receipt_url,
			'pg_date'			=>$pg_date,
			'pg_issue_code'		=>$pg_issue_code,
			'pg_card_number'	=>$pg_card_number,
			'pg_provider'		=>$pg_provider,
		);
	}
	
	return $pg_info;
}

function putTmpOrderTable($db,$order_status,$order_update_code,$pg_info,$member_id) {
	$order_table = getOrderTable($order_status,true);
	
	$pg_mid				= "NULL";
	$pg_payment			= "NULL";
	$pg_payment_key		= "NULL";
	$pg_status			= "NULL";
	$pg_price			= "NULL";
	$pg_currency		= "NULL";
	$pg_receipt_url		= "NULL";
	$pg_date			= "NULL";
	$pg_issue_code		= "NULL";
	$pg_card_number		= "NULL";
	$pg_provider		= "NULL";
	
	if ($pg_info != null) {
		$pg_mid				= "'".$pg_info['pg_mid']."'";
		$pg_payment			= "'".$pg_info['pg_payment']."'";
		$pg_payment_key		= "'".$pg_info['pg_payment_key']."'";
		$pg_status			= "'".$pg_info['pg_status']."'";
		$pg_price			= $pg_info['pg_price'];
		$pg_currency		= "'".$pg_info['pg_currency']."'";
		$pg_receipt_url		= "'".$pg_info['pg_receipt_url']."'";
		$pg_date			= "'".$pg_info['pg_date']."'";
		$pg_issue_code		= "'".$pg_info['pg_issue_code']."'";
		$pg_card_number		= "'".$pg_info['pg_card_number']."'";
		
		if ($pg_info['pg_provider'] != "NULL") {
			$pg_provider		= "'".$pg_info['pg_provider']."'";
		}
	}
	
	$order_table = getOrderTable($order_status,true);
	
	$update_order_table_sql = "
		UPDATE
			".$order_table['info']."
		SET
			PG_MID				= ".$pg_mid.",
			PG_PAYMENT			= ".$pg_payment.",
			PG_PAYMENT_KEY		= ".$pg_payment_key.",
			PG_STATUS			= ".$pg_status.",
			PG_DATE				= ".$pg_date.",
			PG_PRICE			= ".$pg_price.",
			PG_CURRENCY			= ".$pg_currency.",
			PG_RECEIPT_URL		= ".$pg_receipt_url.",
			PG_ISSUE_CODE		= ".$pg_issue_code.",
			PG_CARD_NUMBER		= ".$pg_card_number.",
			PG_PROVIDER			= ".$pg_provider.",
			
			UPDATE_DATE			= NOW(),
			UPDATER				= '".$member_id."'
		WHERE
			ORDER_UPDATE_CODE = '".$order_update_code."'
	";
	
	$db->query($update_order_table_sql);
}
?>
