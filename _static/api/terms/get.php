<?php

if(!isset($terms)) {
	$code = 999;
	$msg = '약관 종류를 지정해주세요.';
}
else {
	$terms = strip_tags(trim($terms));
	$where = 'STATUS="Y" AND CATEGORY=? AND (REG_DATE <= NOW() OR REG_DATE IS NULL)';
	$where_values = array($terms);
	if(isset($reg_date)) {
		$where .= ' AND REG_DATE = ?';
		$where_values[] = $reg_date;
	}
	$data = $db->get($_TABLE['TERMS'],$where.' ORDER BY REG_DATE DESC LIMIT 1',$where_values);
	if(sizeof($data) > 0) {
		$data = $data[0];

		// 이전 약관이 있는지
		$prev = $db->get($_TABLE['TERMS'],'STATUS="Y" AND CATEGORY=? AND REG_DATE < ? ORDER BY REG_DATE DESC LIMIT 1',array($terms,$data['REG_DATE']));
		if(sizeof($prev) > 0) {
			$prev = array(
				'no'=>intval($prev[0]['IDX']),
				'reg_date'=>intval($prev[0]['REG_DATE'])
			);
		}
		else $prev = NULL;

		// 다음 약관이 있는지
		$next = $db->get($_TABLE['TERMS'],'STATUS="Y" AND CATEGORY=? AND REG_DATE > ? ORDER BY REG_DATE ASC LIMIT 1',array($terms,$data['REG_DATE']));
		if(sizeof($next) > 0) {
			$next = array(
				'no'=>intval($next[0]['IDX']),
				'reg_date'=>intval($next[0]['REG_DATE'])
			);
		}
		else $next = NULL;


		$json_result = array(
			'terms'=>$data['CONTENTS'],
			'reg_date'=>date('Y년 m월 d일',strtotime($data['REG_DATE'])),
			'prev'=>$prev,
			'next'=>$next
		);

		$datas = $db->get($_TABLE['TERMS'],'STATUS="Y" AND CATEGORY=? ORDER BY REG_DATE DESC',array($terms));
		foreach($datas as $data) {
			$json_result['data'][] = array(
				'reg_date'=>substr($data['REG_DATE'],0,10)
			);
		}
	}
}