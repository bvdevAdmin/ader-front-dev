<?php
@set_time_limit(600);
@ini_set('memory_limit',-1);
@ini_set("display_errors", 1);

use PhpOffice\PhpSpreadsheet\IOFactory;  
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$_alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','X','Y','Z');

include $_CONFIG['PATH']['ROOT'].'export/xls/'.implode('/',array_slice($_CONFIG['M'],1)).'.php';

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
	$sheet->getStyle($cellName)->getFill()->getStartColor()->setARGB('FF343434');
}

if(isset($datas) && is_array($datas)) {
	for ($i = 2; $row = array_shift($datas); $i++) {
		foreach ($cells as $val) {
			$cellName = $_row_cnt[$val[1]].$i;
			$sheet->getStyle($cellName)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			if(array_key_exists($val[1],$row) && is_string($row[$val[1]])) {
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
				if(array_key_exists('string',$row['_option'])) {
					$sheet
						->getCell($cellName)
						->setValueExplicit(
							$row[$val[1]], 
							\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
						);
				}
			}

			/** 옵션 존재 시(행) **/
			if(array_key_exists('_option',$row)) {
				if(array_key_exists('border',$row['_option'])) {
					if(array_key_exists('border',$row['_option']['border'])) {
						// https://stackoverflow.com/questions/46959282/styling-cell-borders-with-phpspreadsheet-php
						switch($row['_option']['border']['border']) { 
							case 'bottom':
								$sheet
									->getStyle($cellName)
									->getBorders()
									->getBottom()
									->setBorderStyle(Border::BORDER_THIN)
									->setColor(new Color($row['_option']['border']['color']));
							break;
						}
					}
					else {
						$sheet
							->getCell($cellName)
							->getBorders()
							->getOutline()
							->setBorderStyle(Border::BORDER_THICK)
							->setColor(new Color($row['_option']['border']['color']));
					}
				}
			}

			/** 옵션 존재 시(열) **/
			if(array_key_exists('_option',$val)) {
				if(array_key_exists('string',$val['_option']) && array_key_exists($val[1],$row)) {
					$sheet
						->getCell($cellName)
						->setValueExplicit(
							$row[$val[1]], 
							\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
						);
				}

				if(array_key_exists('image',$val['_option'])) {
					$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing(); // 이미지 객체				
					if(file_exists($row[$val[1]]['path'])) {
						$img_obj = get_image_resource_from_file($row[$val[1]]['path']); 
						
						// 엑셀에 저장될 이미지 이름 설정
						$drawing->setName($row[$val[1]]['name']);
						// 이미지 설명
						$drawing->setDescription($row[$val[1]]['remark']);
						// 이미지 경로
						$drawing->setPath($row[$val[1]]['path']);
						// 이미지 자동 크기조절 비활성화
						$drawing->setResizeProportional(false);
						// 이미지 가로 세로 길이 설정(pixel 단위)
						$drawing->setWidthAndHeight(128, get_size_by_rule($img_obj[1],$img_obj[2],128));
						// 이미지가 추가될 위치 지정
						$drawing->setCoordinates($cellName);
						// 이미지를 sheet에 추가
						$drawing->setWorksheet($sheet);
						$sheet->getRowDimension($i)->setRowHeight(128);
					}
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

// 아이폰일 경우
if(array_key_exists('USER_AGENT',$_CONFIG) && sizeof($_CONFIG['USER_AGENT']) > 1 && $_CONFIG['USER_AGENT'][1] == 'iPhone') {
	$_xls['FILENAME'] = 'export_'.date('Ymd_His').'.xlsx';
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$_xls['FILENAME'].'.xlsx"');
header('Cache-Control: max-age=1');
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s',time()+(60*60*9)).' GMT'); // 파일 마지막 수정 일자는 항상 최신으로
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0
$objWriter = new Xlsx($spreadsheet);
$objWriter->save('php://output');
