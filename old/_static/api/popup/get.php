<?php
/*
 +=============================================================================
 | 
 | 팝업
 | ---
 |
 | 최초 작성	: 양한빈
 | 최초 작성일	: 2014.12.19
 | 최종 수정일	: 2021.04.01
 | 버전		: 1.5
 | 설명		: 
 | 
 +=============================================================================
*/


$where_value = array();
if(isset($no) && is_numeric($no)) {
	$where[] = 'IDX=?';
	$where_value[] = $no;
}
$where[] = 'START_DATE <= NOW() AND END_DATE >= NOW() AND STATUS = ?';
$where_value[] = 'Y';
$where = implode(' AND ',$where);
$json_result['total'] = $db->count($_TABLE['POPUP'],$where,$where_value);
$db->query('
	SELECT 
		* 
		FROM '.$_TABLE['POPUP'].'
	WHERE 
		'.$where.'
	ORDER BY 
		SEQ ASC,IDX ASC
',$where_value);
foreach($db->fetch() as $data) {
	$idx		= intval($data['IDX']);	// 인덱스 번호
	$title		= $data['TITLE'];		// 제목
	$image		= $data['IMG'];			// 이미지
	$url		= $data['URL'];			// 링크
	$contents	= $data['CONTENTS'];	// 내용
	$start_date	= $data['START_DATE'];	// 시작일
	$end_date	= $data['END_DATE'];	// 종료일

	$imgsize[0] = 0;
	$imgsize[1] = 0;
	$data['IMG'] = explode('/',$data['IMG']);
	$data['IMG'] = $data['IMG'][sizeof($data['IMG'])-1];
	if(file_exists($_CONFIG['PATH']['UPLOAD_POPUP'].$data['IMG'])) {
		$imgsize = getImageSize($_CONFIG['PATH']['UPLOAD_POPUP'].$data['IMG']);	// 이미지 사이즈 구하기
	}

	$json_result['data'][] = array(
		'title'=>$title,
		'no'=>$idx,
		'image'=>$image,
		'url'=>$url,
		'target'=>$target,
		'contents'=>$contents,
		'width'=>$imgsize[0],
		'height'=>$imgsize[1],
		'start'=>$start_date,
		'end'=>$end_date
	);
}
?>