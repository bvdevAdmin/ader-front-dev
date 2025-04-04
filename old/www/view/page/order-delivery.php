<?php	
	include_once("/var/www/www/api/mypage/order/order-common.php");
	include_once("/var/www/www/api/mypage/order/order-pg.php");
	
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	
	$member_id = null;
	if (isset($_SESSION['MEMBER_ID'])) {
		$member_id = $_SESSION['MEMBER_ID'];
	}
	
	$order_idx = null;
	if (isset($_GET['order_idx'])) {
		$order_idx = $_GET['order_idx'];
	}
	
	$order_code = null;
	if (isset($_GET['order_code'])) {
		$order_code = $_GET['order_code'];
	}
	
	$order_update_code = null;
	if (isset($_GET['orderId'])) {
		$order_update_code = $_GET['orderId'];
	}
	
	$payment_key = null;
	if (isset($_GET['paymentKey'])) {
		$payment_key = $_GET['paymentKey'];
	}
	
	$amount = null;
	if (isset($_GET['amount'])) {
		$amount = $_GET['amount'];
	}
	
	$pg_info = null;
	
	if ($order_update_code != null) {
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
					\"orderId\":\"".$order_update_code."\",
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
			$result = json_decode($response,true);

			$pg_mid = null;
			if (isset($result->mId)) {
				$pg_mid = $result->mId;
			}
			
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
			
			$card = null;
			
			$pg_issue_code = null;
			$pg_card_number = null;
			
			if (isset($result['card'])) {
				$card = $result['card'];
				
				if (isset($card['issuerCode'])) {
					$pg_issue_code = $card['issuerCode'];
				}
				
				if (isset($card['number'])) {
					$pg_card_number = $card['number'];
				}
			}
			
			$pg_date = null;
			if (isset($result->approvedAt)) {
				$pg_date = $result->approvedAt;
			}
			
			$pg_info = array(
				'pg_mid'			=>$pg_mid,
				'pg_payment'		=>$pg_payment,
				'pg_payment_key'	=>$pg_payment_key,
				
				'pg_issue_code'		=>$pg_issue_code,
				'pg_card_number'	=>$pg_card_number,
				
				'pg_status'			=>$pg_status,
				'pg_price'			=>$pg_price,
				'pg_currency'		=>$pg_currency,
				'pg_receipt_url'	=>$pg_receipt_url,
				'pg_date'           =>$pg_date,
			);
		}
	}
			
	try {
		if ($pg_mid != null && is_array($pg_info)) {
			$cnt_TE = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_CODE = '".$order_code."'");
			$cnt_TR = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_CODE = '".$order_code."'");
			
			if ($cnt_TE > 0 && $cnt_TR > 0) (
				addOrderDelivery($db,"OEX",$order_code,$pg_info);
				addOrderDelivery($db,"ORF",$order_code,$pg_info);
			} else {
				if ($cnt_TE > 0) {
					addOrderDelivery($db,"OEX",$order_code,$pg_info);
				}
				
				if ($cnt_TR > 0) {
					addOrderDelivery($db,"ORF",$order_code,$pg_info);
				}
			}
		}
		
		$db->commit();
	} catch(mysqli_sql_exception $exception){
		$db->rollback();
		print_r($exception);
		
		$json_result['code'] = 301;
		$json_result['msg'] = "주문정보 등록처리중 오류가 발생했습니다.";
	}
	
	echo "
		<script>
			$.ajax({
				type: 'post',
				url: api_location + 'mypage/order/put',
				data: {
					'order_code': ".$order_code."
				},
				dataType: 'json',
				async:false,
				error: function (d) {
					notiModal('주문상품 교환/반품처리중 오류가 발생했습니다.');
					location.href='/mypage/main/orderlist/refund?order_idx=".$order_idx."';
				},
				success: function (d) {
					if (d.code == 200) {
						location.href='/mypage?mypage_type=orderlist';
					} else {
						notiModal(d.msg);
						location.href='/mypage/main/orderlist/refund?order_idx=".$order_idx."';
					}
				}
			});
		</script>
	";
	
	function addOrderDelivery($db,$order_status,$order_code,$data) {
		$order_table = getOrderTable($order_status,false);
		$tmp_order_table = getOrderTable($order_status,true);
		
		$insert_order_delivery_sql = "
			INSERT INTO
				".$order_table['product']."
			(
				IDX,
				ORDER_IDX
				ORDER_CODE
				ORDER_PRODUCT_CODE
				ORDER_UPDATE_CODE
				ORDER_STATUS
				
				PRODUCT_IDX
				PRODUCT_TYPE
				PRODUCT_CODE
				PRODUCT_NAME
				
				OPTION_IDX
				BARCODE
				OPTION_NAME
				
				PRODUCT_QTY
				PRODUCT_PRICE
				
				PG_MID
				PG_PAYMENT
				PG_PAYMENT_KEY
				PG_ISSUE_CODE
				PG_CARD_NUMBER
				PG_STATUS
				PG_DATE
				PG_PRICE
				PG_CURRENCY
				PG_RECEIPT_URL
				
				CREATE_DATE
				CREATER
				UPDATE_DATE
				UPDATER
			)
			SELECT
				TP.IDX							AS IDX,
				TP.ORDER_IDX					AS ORDER_IDX,
				TP.ORDER_CODE					AS ORDER_CODE,
				TP.ORDER_PRODUCT_CODE			AS ORDER_PRODUCT_CODE,
				TP.ORDER_UPDATE_CODE			AS ORDER_UPDATE_CODE,
				TP.ORDER_STATUS					AS ORDER_STATUS,
				
				TP.PRODUCT_IDX					AS PRODUCT_IDX,
				TP.PRODUCT_TYPE					AS PRODUCT_TYPE,
				TP.PRODUCT_CODE					AS PRODUCT_CODE,
				TP.PRODUCT_NAME					AS PRODUCT_NAME,
				
				TP.OPTION_IDX					AS OPTION_IDX,
				TP.BARCODE						AS BARCODE,
				TP.OPTION_NAME					AS OPTION_NAME,
				
				TP.PRODUCT_QTY					AS PRODUCT_QTY,
				TP.PRODUCT_PRICE				AS PRODUCT_PRICE,
				
				'".$data['pg_mid']."'			AS PG_MID,
				'".$data['pg_payment']."'		AS PG_PAYMENT,
				'".$data['pg_payment_key']."'	AS PG_PAYMENT_KEY,
				
				'".$data['pg_issue_code']."'	AS PG_ISSUE_CODE,
				'".$data['pg_card_number']."'	AS PG_CARD_NUMBER,
				
				'".$data['pg_status']."'		AS PG_STATUS,
				'".$data['pg_date']."'			AS PG_DATE,
				'".$data['pg_price']."'			AS PG_PRICE,
				'".$data['pg_currency']."'		AS PG_CURRENCY,
				'".$data['pg_receipt_url']."'	AS PG_RECEIPT_URL,
				
				NOW()							AS CREATE_DATE,
				TP.CREATER						AS CREATER,
				NOW()							AS UPDATE_DATE,
				TP.UPDATER						AS UPDATER	
			FROM
				".$tmp_order_table['product']."
			WHERE
				TP.ORDER_CODE = '".$order_code."'
		";
		
		$db->query($insert_order_delivery_sql);
	}
?>
