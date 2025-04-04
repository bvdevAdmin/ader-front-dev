<?php

$goods_data = $db->get($_TABLE['GOODS'],'STATUS != "DELETE" ORDER BY SEQ,IDX DESC');
foreach($goods_data as $data) {
	$json_result['data'][] = array(
		'no' => intval($data['IDX']),
		'title' => $data['TITLE'],
		'is_soldout' => ($data['IS_SOLDOUT'] == 'N') ? false : true
	);
}