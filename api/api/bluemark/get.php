<?php
/*
if(isset($_SESSION[SS_HEAD.'ID']) && $_SESSION[SS_HEAD.'ID'] != '') {
	$id = $_SESSION[SS_HEAD.'ID'];
}
*/
$id = trim($id);
$id = strip_tags($id);
$where = 'A.STATUS = "CONFIRM" AND A.ID = ? ';
$where_values = array($id);
if(isset($no) && is_numeric($no)) {
	$where .= ' AND A.IDX = ? ';
	$where_values[] = $no;
}


$db->query('
	SELECT 
			A.IDX,A.SERIAL_CODE,A.BARCODE,A.ID,B.IDX AS GOODS_NO,B.COLOR,B.NAME,A.CONFIRM_DATE
		FROM '.$_TABLE['SERIAL'].' AS A 
		LEFT JOIN '.$_TABLE['SHOP_WARE'].' AS B ON A.BARCODE = B.BARCODE OR B.BARCODE = LEFT(A.BARCODE,11)
	WHERE 
		'.$where.'
	ORDER BY 
		A.CONFIRM_DATE DESC 
',$where_values);
$json_result = array(
	'total' => $db->rows()
);
foreach($db->fetch() as $data) {
	$confirm_date = str_replace('-','.',substr($data['CONFIRM_DATE'],0,10));
	$name = ($data['NAME']!=null)?$data['NAME']:$data['NAME2'];
	$goods_no = intval($data['GOODS_NO']);
	$color = $data['COLOR'];
	$id = substr($data['ID'],0,4);
	for($i=4;$i<=strlen($data['ID']);$i++) $id .= '*';

	/** 기록 가져옴 **/
	$log = null;
	$date_e = time();
	if(!isset($db2)) $db2 = new db();
	$db2->query('
		SELECT 
				ID,CATEGORY,REG_DATE
			FROM '.$_TABLE['SERIAL_LOG'].' 
		WHERE 
			SERIAL_NO = '.$data['IDX'].' 
		ORDER BY 
			REG_DATE DESC 
	');
	foreach($db2->fetch() as $data2) {
		$log_id = substr($data2['ID'],0,4);
		for($i=4;$i<=strlen($data2['ID']);$i++) $log_id .= '*';
		$date_s = strtotime($data2['REG_DATE']);
		$log[] = array(
			'id'=>$log_id,
			'date'=>array(
				's'=>date('Y.m.d',$date_s),
				'e'=>date('Y.m.d',$date_e)
			)
		);
		$date_e = $date_s;
	}

	$json_result['data'][] = array(
		'no' => intval($data['IDX']),
		'id' => $id,
		'bluemark' => $data['BARCODE'].'-'.$data['SERIAL_CODE'],
		'name' => $name,
		'image' => $image,
		'confirm_date' => $confirm_date,
		'log' => $log,
		'goods' => array(
			'no' => $goods_no,
			'name' => $name,
			'color' => $color
		)
	);
}
