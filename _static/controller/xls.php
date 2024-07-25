<?php
@set_time_limit(600);
@ini_set('memory_limit',-1);

use PhpOffice\PhpSpreadsheet\IOFactory;  
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$_alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','X','Y','Z');

include $_CONFIG['PATH']['ROOT'].'export/xls/'.implode('/',array_slice($_CONFIG['M'],1)).'.php';

/*
$_xls['CREATOR'] = 'Helix Web Application System';

// 데이터 프리셋
//$incfile = $output_category;
//if($output_list) $incfile .= '-'.$output_list.'.php';
$output_module = str_replace('/','-',$output_module);
include 'templete/'.$output_module.'.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties

// 목록 헤더
for($i=0;$i<sizeof($_xls['HEADER']);$i++) {
	//$objPHPExcel->getActiveSheet()->getColumnDimension($_alphabet[$i])->setAutoSize(true); // 너비 자동 조정
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($_alphabet[$i].'1', $_xls['HEADER'][$i]); // 값 삽입
	$objPHPExcel->getActiveSheet()->getStyle($_alphabet[$i].'1')->getFont()->setBold(true); // 두껍게
	$objPHPExcel->getActiveSheet()->getStyle($_alphabet[$i].'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY); // 정렬
	$objPHPExcel->getActiveSheet()->getStyle($_alphabet[$i].'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	if(is_numeric($_xls['WIDTH'][$i])) {
		$objPHPExcel->getActiveSheet()->getColumnDimension($_alphabet[$i])->setWidth($_xls['WIDTH'][$i]); // 너비 자동 조정
	}
}

// 목록 내용
$row = 2;
while($data = db_array($sql)) {
	for($i=0;$i<sizeof($data);$i++) {
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValueExplicit($_alphabet[$i].$row, $data[$i], PHPExcel_Cell_DataType::TYPE_STRING);
				//->setCellValue($_alphabet[$i].$row, $data[$i]);
	}
	$row++;
}

*/

$_alphabet_cnt = 0;
$_row_cnt = [];
foreach ($cells as $key => $val) {
	$cellName = $_alphabet[$_alphabet_cnt].'1';
	$_row_cnt[$val[1]] = $_alphabet[$_alphabet_cnt];
	$sheet->getColumnDimension($_alphabet[$_alphabet_cnt++])->setWidth($val[0]);
	$sheet->getRowDimension('1')->setRowHeight(25);
	$sheet->setCellValue($cellName, $val[2]);
	$sheet->getStyle($cellName)->getFont()->setBold(true);
	$sheet->getStyle($cellName)->getFont()->getColor()->setRGB('FFFFFF');
	$sheet->getStyle($cellName)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle($cellName)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
	$sheet->getStyle($cellName)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
	$sheet->getStyle($cellName)->getFill()->getStartColor()->setARGB('FF000000');
}
if(isset($datas) && is_array($datas)) {
	for ($i = 2; $row = array_shift($datas); $i++) {
		foreach ($cells as $val) {
			$cellName = $_row_cnt[$val[1]].$i;
			if(array_key_exists($val[1],$row)) {
				$sheet->setCellValue($cellName, $row[$val[1]]);
			}
			if(array_key_exists('_option',$row)) {
				if(array_key_exists('color',$row['_option'])) {
					$sheet->getStyle($cellName)->getFont()->getColor()->setRGB($row['_option']['color']);
				}
				if(array_key_exists('cell_color',$row['_option'])) {
					$sheet->getStyle($cellName)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
					$sheet->getStyle($cellName)->getFill()->getStartColor()->setARGB('FF'.$row['_option']['cell_color']);
				}
			}
		}
	}
}
/*
$sheet->getProperties()->setCreator($_xls['CREATOR'])
							 ->setLastModifiedBy($_xls['CREATOR'])
							 ->setTitle($_xls['TITLE'])
							 ->setSubject($_xls['TITLE'])
							 ->setDescription($_xls['DESCRIBE'])
							 ->setKeywords($_xls['KEYWORDS'])
							 ->setCategory($_xls['CATEGORY']);
*/
$sheet->setTitle(date('Y년m월d일_His'),time()+(60*60*9));
$spreadsheet->setActiveSheetIndex(0);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$_xls['FILENAME'].'.xlsx"');
header('Cache-Control: max-age=1');
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s',time()+(60*60*9)).' GMT'); // 파일 마지막 수정 일자는 항상 최신으로
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0
$objWriter = new Xlsx($spreadsheet);
$objWriter->save('php://output');
