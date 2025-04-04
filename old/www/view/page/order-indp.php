<?php
	
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	
	$member_idx = 0;
	if (isset($_SESSION['MEMBER_IDX'])) {
		$member_idx = $_SESSION['MEMBER_IDX'];
	}
	
	$member_id = null;
	if (isset($_SESSION['MEMBER_ID'])) {
		$member_id = $_SESSION['MEMBER_ID'];
	}
	
	$member_name = null;
	if (isset($_SESSION['MEMBER_NAME'])) {
		$member_name = $_SESSION['MEMBER_NAME'];
	}
	
	if ($member_idx == 0 || $member_id == null) {
		echo "
			<script>
				location.href='/login';
			</script>
		";
	}
	
	$order_code = null;
	if (isset($_GET['order_code'])) {
		$order_code = $_GET['order_code'];
	}
	
	$order_cnt = $db->count("TMP_ORDER_INFO","ORDER_CODE = '".$order_code."'");
	
	if ($order_cnt > 0) {
		$select_tmp_order_info_sql = "
			SELECT
				TI.ORDER_CODE		AS ORDER_CODE,
				TI.ORDER_TITLE		AS ORDER_TITLE,
				TI.MEMBER_NAME		AS MEMBER_NAME,
				TI.PRICE_TOTAL		AS PRICE_TOTAL
			FROM
				TMP_ORDER_INFO TI
			WHERE
				ORDER_CODE = '".$order_code."' AND
				ORDER_STATUS = 'PWT'
		";
		
		$db->query($select_tmp_order_info_sql);
		
		$order_info = array();
		foreach($db->fetch() as $order_data) {
			$order_info = array(
				'order_code'		=>$order_data['ORDER_CODE'],
				'order_title'		=>$order_data['ORDER_TITLE'],
				'member_name'		=>$order_data['MEMBER_NAME'],
				'price_total'		=>$order_data['PRICE_TOTAL']
			);
		}
		
		if (count($order_info) > 0) {
?>
<script src="https://js.tosspayments.com/v1/payment-widget"></script>

<script>
const clientKey = "test_ck_YZ1aOwX7K8meL9vyEe98yQxzvNPG";
let tossPayments = TossPayments(clientKey);

$(document).ready(function() {	
	tossPayments.requestPayment('카드', {
		amount: "<?=$order_info['price_total']?>",
		orderId: "<?=$order_info['order_code']?>",
		orderName: "<?=$order_info['order_title']?>",
		customerName: "<?=$order_info['member_name']?>",
		successUrl: '/order/check',
		failUrl: '/order/check',
	});
});

</script>
<?php
		}
	} else {
		$json_result['code'] = 403;
		$json_result['msg'] = "결제하려는 주문정보가 존재하지 않습니다.";
		
		return $json_result;
	}
?>