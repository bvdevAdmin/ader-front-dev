<?php

if(!isset($goods) || !is_array($goods)) {
	$code = 999;
	$msg = '상품 정보가 잘못되었습니다.';
}
/*
elseif(!isset($account_no) || !is_numeric($account_no)) {
	$code = 999;
	$msg = '계정 정보가 잘못되었습니다.';
}
*/
else {
	$account = $db->get($_TABLE['ACCOUNT'],'TEL=?',array($tel))[0];
	$type = implode(',',$type);
	
	// 주문번호 생성
	$order_num = 1;
	$last_order = $db->get($_TABLE['ORDER'],'FINPUT_DATE > ? ORDER BY IDX DESC LIMIT 1',array(date('Y-m-d 00:00:00')));

	if(sizeof($last_order) > 0) {
		$order_num = intval($last_order[0]['ORDER_NUM']) + 1;
	}

	for($i=0;$i<sizeof($goods);$i++) { // 음료마다 입력	
		for($j=0;$j<$goods[$i]['qty'];$j++) { // 수량만큼 주문 입력
			if(!$db->insert($_TABLE['ORDER'],array(
				'ORDER_NUM' => $order_num,
				'ACCOUNT_NO' => $account['IDX'],
				'GOODS_NO' => $goods[$i]['no'],
				'TYPE' => $type,
				'QTY' => 1,
				'IP' => $_SERVER['REMOTE_ADDR']
			))) {
				$code = 500;
				break;
			}
			else {
				// 주문 확인
				$json_result['data'][] = array(
					'no' => $db->last_id(),
					'goods_no' => $goods[$i]['no']
				);
			}
		
		}
	}
}
