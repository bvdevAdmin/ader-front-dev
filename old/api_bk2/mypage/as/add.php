<?php
/*
 +=============================================================================
 | 
 | A/S신청 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.23
 | 최종 수정일	: 
 | 버전		: 1.1
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$serial_code = null;
if (isset($_POST['serial_code'])) {
	$serial_code = $_POST['serial_code'];
}

$as_category_idx = 0;
if (isset($_POST['as_category_idx'])) {
	$as_category_idx = $_POST['as_category_idx'];
}

$barcode = null;
if (isset($_POST['barcode'])) {
	$barcode = $_POST['barcode'];
}

$as_contents = null;
if (isset($_POST['as_contents'])) {
	$as_contents = xssEncode($_POST['as_contents']);
}

$product_img = null;
if (isset($_FILES['product_img'])) {
	$product_img = $_FILES['product_img'];
}

$receipt_img = null;
if (isset($_FILES['receipt_img'])) {
	$receipt_img = $_FILES['receipt_img'];
}

if ($country != null && $member_idx > 0) {
	$insert_member_as_sql = "";
	
	$as_code = $country."-".date("Ymd-").time();
	
	if ($serial_code != null) {
		$as_cnt = $db->count("MEMBER_AS","SERIAL_CODE = '".$serial_code."' AND DEL_FLG = FALSE AND AS_STATUS != 'ACP' ");
		if ($as_cnt > 0) {
			$json_result['code'] = 303;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0057', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		$insert_member_as_sql = "
			INSERT INTO
				MEMBER_AS
			(
				COUNTRY,
				MEMBER_IDX,
				AS_CODE,
				
				BLUEMARK_FLG,
				SERIAL_CODE,
				
				MANUFACTURER,
				PRODUCT_IDX,
				PRODUCT_TYPE,
				PRODUCT_CODE,
				PRODUCT_NAME,
				COLOR,
				
				OPTION_IDX,
				BARCODE,
				OPTION_NAME,
				
				AS_STATUS,
				AS_CONTENTS,
				
				CREATE_DATE,
				CREATER,
				UPDATE_DATE,
				UPDATER
			)
			SELECT
				'".$country."'		AS COUNTRY,
				".$member_idx."		AS MEMBER_IDX,
				'".$as_code."'		AS AS_CODE,
				
				TRUE				AS BLUEMARK_FLG,
				'".$serial_code."'	AS SERIAL_CODE,
				
				OM.MANUFACTURER		AS MANUFACTURER,
				PR.IDX				AS PRODUCT_IDX,
				PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
				PR.PRODUCT_CODE		AS PRODUCT_CODE,
				PR.PRODUCT_NAME		AS PRODUCT_NAME,
				PR.COLOR			AS COLOR,
				
				OO.IDX				AS OPTION_IDX,
				OO.BARCODE			AS BARCODE,
				OO.OPTION_NAME		AS OPTION_NAME,
				
				'APL'				AS AS_STATUS,
				".$as_contents."	AS AS_CONTENTS,
				
				NOW()				AS CREATE_DATE,
				'".$member_id."'	AS CREATER,
				NOW()				AS UPDATE_DATE,
				'".$member_id."'	AS UPDATER
			FROM
				BLUEMARK_LOG BL
				LEFT JOIN BLUEMARK_INFO BI ON
				BL.BLUEMARK_IDX = BI.IDX
				LEFT JOIN SHOP_PRODUCT PR ON
				BI.PRODUCT_IDX = PR.IDX
				LEFT JOIN ORDERSHEET_OPTION OO ON
				BI.OPTION_IDX = OO.IDX
				LEFT JOIN ORDERSHEET_MST OM ON
				PR.ORDERSHEET_IDX = OM.IDX
			WHERE
				BL.COUNTRY = '".$country."' AND
				BL.MEMBER_IDX = ".$member_idx." AND
				BI.SERIAL_CODE = '".$serial_code."'
		";
	} else {
		$option_cnt = $db->count("ORDERSHEET_OPTION","BARCODE = '".$barcode."'");
		
		if ($option_cnt > 0) {
			$insert_member_as_sql = "
				INSERT INTO
					MEMBER_AS
				(
					COUNTRY,
					MEMBER_IDX,
					AS_CODE,
					AS_CATEGORY_IDX,
					
					MANUFACTURER,
					PRODUCT_IDX,
					PRODUCT_TYPE,
					PRODUCT_CODE,
					PRODUCT_NAME,
					COLOR,
					
					OPTION_IDX,
					BARCODE,
					OPTION_NAME,
					
					AS_STATUS,
					AS_CONTENTS,
					
					CREATE_DATE,
					CREATER,
					UPDATE_DATE,
					UPDATER
				)
				SELECT
					'".$country."'		AS COUNTRY,
					".$member_idx."		AS MEMBER_IDX,
					'".$as_code."'		AS AS_CODE,
					AS_CATEGORY_IDX		AS AS_CATEGORY_IDX,
					
					OM.MANUFACTURER		AS MANUFACTURER,
					PR.IDX				AS PRODUCT_IDX,
					PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
					PR.PRODUCT_CODE		AS PRODUCT_CODE,
					PR.PRODUCT_NAME		AS PRODUCT_NAME,
					PR.COLOR			AS COLOR,
					
					OO.IDX				AS OPTION_IDX,
					OO.BARCODE			AS BARCODE,
					OO.OPTION_NAME		AS OPTION_NAME,
					
					'APL'					AS AS_STATUS,
					".$as_contents."	AS AS_CONTENTS,
					
					NOW()				AS CREATE_DATE,
					'".$member_id."'	AS CREATER,
					NOW()				AS UPDATE_DATE,
					'".$member_id."'	AS UPDATER
				FROM
					BLUEMARK_LOG BL
					LEFT JOIN BLUEMARK_INFO BI ON
					BL.BLUEMARK_IDX = BI.IDX
					LEFT JOIN SHOP_PRODUCT PR ON
					BI.PRODUCT_IDX = PR.IDX
					LEFT JOIN ORDERSHEET_OPTION OO ON
					BI.OPTION_IDX = OO.IDX
					LEFT JOIN ORDERSHEET_MST OM ON
					PR.ORDERSHEET_IDX = OM.IDX
				WHERE
					BL.COUNTRY = '".$country."' AND
					BL.MEMBER_IDX = ".$member_idx." AND
					BI.SERIAL_CODE = '".$serial_code."' AND
					OO.BARCODE = '".$barcode."'
			";
		} else {
			$insert_member_as_sql = "
				INSERT INTO
					MEMBER_AS
				(
					COUNTRY,
					MEMBER_IDX,
					AS_CODE,
					AS_CATEGORY_IDX,
					
					BARCODE,
					
					AS_STATUS,
					AS_CONTENTS,
					
					CREATE_DATE,
					CREATER,
					UPDATE_DATE,
					UPDATER
				) VALUES (
					'".$country."',
					".$member_idx.",
					'".$as_code."',
					".$as_category_idx.",
					
					'".$barcode."',
					
					'APL',
					".$as_contents.",
					
					NOW(),
					'".$member_id."',
					NOW(),
					'".$member_id."'
				)
			";
		}
	}
	
	$db->query($insert_member_as_sql);
	
	$as_idx = $db->last_id();
	
	if (!empty($as_idx)) {
		uploadAsImg($db,$country,"P",$product_img,$cdn_img_ftp_host,$cdn_img_user,$cdn_img_password,$as_idx,$member_id);
		
		if ($receipt_img != null) {
			uploadAsImg($db,$country,"R",$receipt_img,$cdn_img_ftp_host,$cdn_img_user,$cdn_img_password,$as_idx,$member_id);
		}
	}
}

function uploadAsImg($db,$country,$img_type,$as_img,$cdn_img_ftp_host,$cdn_img_user,$cdn_img_password,$as_idx,$member_id) {
	$upload_result = array();
	
	$conn = ftp_connect($cdn_img_ftp_host);
	if (!$conn) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0069', array());
	}
	
	$result = ftp_login($conn, $cdn_img_user, $cdn_img_password);
	if(!$result){
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0068', array());
	}
	
	$img_num = 1;
	for ($i=0; $i<count($as_img); $i++) {
		$img_name = $as_img['name'][$i];
		
		if (!empty($img_name)) {
			$name_arr = explode('.',$img_name);
			$img_ext = $name_arr[count($name_arr) - 1];
			$tmp_file = $as_img['tmp_name'][$i];
			
			$ftp_path = "/as_images/img_AS_".$img_type."_".$img_num."_".time().".".$img_ext;
			
			$local_file = $tmp_file; // 접속한 서버로 업로드 할 파일
			
			if (ftp_put($conn,$ftp_path,$local_file,FTP_BINARY)) {
				array_push($upload_result,$ftp_path);
				$img_num++;
			}
		}
	}
	
	ftp_close($conn);
	
	if ($upload_result != null && count($upload_result) > 0) {
		for ($i=0; $i<count($upload_result); $i++) {
			$insert_as_img_sql = "
				INSERT INTO
					AS_IMG
				(
					AS_IDX,
					IMG_TYPE,
					IMG_LOCATION,
					
					CREATE_DATE,
					CREATER,
					UPDATE_DATE,
					UPDATER
				) VALUES (
					".$as_idx.",
					'".$img_type."',
					'".$upload_result[$i]."',
					
					NOW(),
					'".$member_id."',
					NOW(),
					'".$member_id."'
				)
			";
			
			$db->query($insert_as_img_sql);
		}
	}
}

?>