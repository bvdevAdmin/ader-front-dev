<?php
/** 01. 변수 검증 및 정리 **/
if(isset($no) && is_numeric($no)) {
	$where = ' IDX = ? AND STATUS != "DELETE" ';
	$where_values = array($no);
	if(!isset($bbscode)) {
		$bbscode = $db->get($_TABLE['BOARD'],$where,$where_values)[0]['BBSCODE'];
	}
}
else {
	$bbscode = trim($bbscode);
	$where = ' BBSCODE = ? AND STATUS != "DELETE" ';
	$where_values = array($bbscode);
}

/** 02. 오류 사항 체크 **/
$board_config = $db->get($_TABLE['BOARD_CONFIG'],'BBSCODE=?',array($bbscode))[0];
if(!is_array($board_config)) {
	$code = 999;
	$msg = '존재하지 않는 게시판입니다.';
}
else {
	$tables = $_TABLE['BOARD'];

	/** 03. preset **/
	$json_result = array(
		'total' => $db->count($tables,$where,$where_values),
		'page' => intval($page),
		'pagenum' => intval($pagenum)
	);


	/** DB 쿼리 **/
	$db->query('
		SELECT 
				*
			FROM '.$tables.'
		WHERE
			'.$where.' 
		ORDER BY
			IDX DESC 
		LIMIT 
			?,?
	',array_merge($where_values,array(($page-1)*$pagenum,$pagenum)));
	foreach($db->fetch() as $data) {
		$file = null;
		$image = null;
		if($data['FILE'] != '') {
			foreach(json_decode($data['FILE'],true) as $row) {
				$file[] = array($row['original_name'],intval($row['download_hit']));
			}
		}
		if($data['IMG'] != '') {
			foreach(json_decode($data['IMG'],true) as $row) {
				$image[] = $row['url'];
			}
		}
		/** JSON 데이터 정리 **/
		$json_result['data'][] = array(
			'no'=>intval($data['IDX']),
			'title'=>$data['TITLE'],
			'file'=>$file,
			'image'=>$image,
			'contents'=>$data['CONTENTS'],
			'reg_date'=>$data['FINPUT_DATE'],
			'modify_date'=>$data['LINPUT_DATE']
		);
	}
}

