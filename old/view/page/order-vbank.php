<?php
	
	$webhook_json = file_get_contents("php://input");
	$result = json_decode($webhook_json);

	$pg_date = null;
	if (isset($result->createdAt)) {
		$pg_date = $result->createdAt;
	}

	$vbank_secret_key = null;
	if (isset($result->secret)) {
		$vbank_secret_key = $result->secret;
	}

	$pg_status = null;
	if (isset($result->status)) {
		$pg_status = $result->status;
	}

	$order_code = null;
	if (isset($result->orderId)) {
		$order_code = $result->orderId;
	}

	if ($order_code != null && $vbank_secret_key != null) {
		
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.tosspayments.com/v1/payments/orders/".$order_code,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"Authorization: Basic dGVzdF9za19ONU9XUmFwZEE4ZFkyMTc1N2piM28xekVxWktMOg==",
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);
		
		if (!$err) {
			$pg_result = json_decode($response);
			
			$cash_receipt_flg = "FALSE";
			
			$cash_receipt_type = "NULL";
			$caseh_receipt_key = "NULL";
			$caseh_receipt_issue_number = "NULL";
			$caseh_receipt_url = "NULL";
			$caseh_receipt_price = 0;
			
			if (isset($pg_result->cashReceipt)) {
				$cash_receipt_flg = "TRUE";
				
				$cash_receipt = $pg_result->cashReceipt;
				if (isset($cash_receipt->type)) {
					$cash_receipt_type = "'".$cash_receipt->type."'";
				}
				
				if (isset($cash_receipt->receiptKey)) {
					$cash_receipt_key = "'".$cash_receipt->receiptKey."'";
				}
				
				if (isset($cash_receipt->issueNumber)) {
					$cash_receipt_issue_number = "'".$cash_receipt->issueNumber."'";
				}
				
				if (isset($cash_receipt->receiptUrl)) {
					$cash_receipt_url = "'".$cash_receipt->receiptUrl."'";
				}
				
				if (isset($cash_receipt->amount)) {
					$cash_receipt_price = $cash_receipt->amount;
				}
			}
			
			$update_order_info_sql = "
				UPDATE
					ORDER_INFO
				SET
					ORDER_STATUS = 'PCP',
					PG_DATE = '".$pg_date."',
					PG_STATUS = '".$pg_status."',
					
					VBANK_SECRET_KEY = '".$vbank_secret_key."',
					
					CASH_RECEIPT_FLG = ".$cash_receipt_flg.",
					CASH_RECEIPT_TYPE = ".$cash_receipt_type.",
					CASH_RECEIPT_KEY = ".$cash_receipt_key.",
					CASH_RECEIPT_ISSUE_NUMBER = ".$cash_receipt_issue_number.",
					CASH_RECEIPT_URL = ".$cash_receipt_url.",
					CASH_RECEIPT_PRICE = ".$cash_receipt_price."
				WHERE
					ORDER_CODE = '".$order_code."' AND
					ORDER_STATUS = 'PWT'
			";
			
			$db->query($update_order_info_sql);
		}
	}
	
?>