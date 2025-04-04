<?php	
	include_once("/var/www/www/api/mypage/order/common.php");
	
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	
	$member_idx = null;
	if (isset($_SESSION['MEMBER_IDX'])) {
		$member_idx = $_SESSION['MEMBER_IDX'];
	}
	
	$member_id = null;
	if (isset($_SESSION['MEMBER_ID'])) {
		$member_id = $_SESSION['MEMBER_ID'];
	}
	
	$country = null;
	if (isset($_SESSION['COUNTRY'])) {
		$country = $_SESSION['COUNTRY'];
	}
	
	$as_code = null;
	if (isset($_GET['orderId'])) {
		$as_code = $_GET['orderId'];
	}

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
		CURLOPT_POSTFIELDS => "
			{
				\"orderId\":\"".$as_code."\",
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
				if ($as_code != null && $pg_mid != null) {
					$insert_member_as_sql = "
						INSERT INTO
							AS_PAYMENT
						(
							AS_IDX,
							AS_CODE,
							
							PG_MID,
							PG_PAYMENT,
							PG_PAYMENT_KEY,
							PG_STATUS,
							PG_DATE,
							PG_PRICE,
							PG_CURRENCY,
							PG_RECEIPT_URL,
							
							CREATE_DATE,
							CREATER,
							UPDATE_DATE,
							UPDATER
						)
						SELECT
							MA.IDX					AS AS_IDX,
							MA.AS_CODE				AS AS_CODE,
							
							'".$pg_mid."'			AS PG_MID,
							'".$pg_payment."'		AS PG_PAYMENT,
							'".$pg_payment_key."'	AS PG_PAYMENT_KEY,
							'".$pg_status."'		AS PG_STATUS,
							'".$pg_date."'			AS PG_DATE,
							'".$pg_price."'			AS PG_PRICE,
							'".$pg_currency."'		AS PG_CURRENCY,
							'".$pg_receipt_url."'	AS PG_RECEIPT_URL,
							
							NOW()					AS CREATE_DATE,
							'".$member_id."'		AS CREATER,
							NOW()					AS UPDATE_DATE,
							'".$member_id."'		AS UPDATER	
						FROM
							MEMBER_AS MA
						WHERE
							MA.AS_CODE = '".$as_code."'
					";
					
					$db->query($insert_member_as_sql);
					
					$db_result = $db->last_id();
					
					if (!empty($db_result)) {
						$update_member_as_sql = "
							UPDATE
								MEMBER_AS
							SET
								AS_PRICE_FLG = TRUE
							WHERE
								AS_CODE = '".$as_code."'
						";
						
						$db->query($update_member_as_sql);
					}
				}
				
				$db->commit();
			} catch(mysqli_sql_exception $exception){
				$db->rollback();
				print_r($exception);
				
				$json_result['code'] = 301;
				$json_result['msg'] = "주문정보 등록처리중 오류가 발생했습니다.";
			}
		}
	}
	
	echo "
		<script>
			location.href='/mypage?mypage_type=as_first';
		</script>
	";
?>
