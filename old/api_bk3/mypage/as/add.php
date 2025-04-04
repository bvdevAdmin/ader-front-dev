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

$product_img = null;
if (isset($_FILES['product_img'])) {
	$product_img = $_FILES['product_img'];
}

$receipt_img = null;
if (isset($_FILES['receipt_img'])) {
	$receipt_img = $_FILES['receipt_img'];
}

if (isset($country) && $member_idx > 0) {
	/* A/S 코드 생성 */
	$as_code = $country."-".date("Ymd-").time();
	
	if ($serial_code != null) {
		/* 블루마크 인증제품 A/S 신청처리 */
		
		/* A/S 진행여부 체크 */
		$cnt_as = $db->count("MEMBER_AS","SERIAL_CODE = '".$serial_code."' AND DEL_FLG = FALSE AND AS_STATUS != 'ACP' ");
		if ($cnt_as > 0) {
			$json_result['code'] = 303;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0057', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		$insert_member_as_bluemark_sql = "
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
				
				0					AS OPTION_IDX,
				''					AS BARCODE,
				''					AS OPTION_NAME,
				
				'APL'				AS AS_STATUS,
				'".$as_contents."'	AS AS_CONTENTS,
				
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
				LEFT JOIN ORDERSHEET_MST OM ON
				PR.ORDERSHEET_IDX = OM.IDX
			WHERE
				BL.COUNTRY = '".$country."' AND
				BL.MEMBER_IDX = ".$member_idx." AND
				BI.SERIAL_CODE = '".$serial_code."'
		";
		
		$db->query($insert_member_as_bluemark_sql);
	} else {
		/* 블루마크 미인증제품 A/S 신청처리 */
		
		/* 제품코드 체크처리 */
		$cnt_option = $db->count("ORDERSHEET_OPTION","BARCODE = '".$barcode."'");
		
		if ($cnt_option > 0) {
			/* 제품코드와 일치하는 바코드가 존재하는 경우 */
			$insert_member_as_barcode_sql = "
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
					?					AS COUNTRY,
					?					AS MEMBER_IDX,
					?					AS AS_CODE,
					?					AS AS_CATEGORY_IDX,
					
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
					?					AS AS_CONTENTS,
					
					NOW()				AS CREATE_DATE,
					?					AS CREATER,
					NOW()				AS UPDATE_DATE,
					?					AS UPDATER
				FROM
					SHOP_PRODUCT PR
					LEFT JOIN ORDERSHEET_MST OM ON
					PR.ORDERSHEET_IDX = OM.IDX
					LEFT JOIN ORDERSHEET_OPTION OO ON
					PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
				WHERE
					OO.BARCODE = ?
			";
			
			$db->query($insert_member_as_barcode_sql,array($country,$member_idx,$as_code,$as_category_idx,$as_contents,$member_id,$member_id,$barcode));
		} else {
			/* 제품코드와 일치하는 바코드가 존재하지 않는 경우 */
			$db->insert(
				"MEMBER_AS",
				array(
					'COUNTRY'			=>$country,
					'MEMBER_IDX'		=>$member_idx,
					'AS_CODE'			=>$as_code,
					'AS_CATEGORY_IDX'	=>$as_category_idx,
					
					'BARCODE'			=>$barcode,
					
					'AS_STATUS'			=>"APL",
					'AS_CONTENTS'		=>$as_contents,
					
					'CREATE_DATE'		=>NOW(),
					'CREATER'			=>$member_id,
					'UPDATE_DATE'		=>NOW(),
					'UPDATER'			=>$member_id
				)
			);
		}
	}
	
	$as_idx = $db->last_id();
	if (!empty($as_idx)) {
		$param_as_img = array(
			'country'			=>$country,
			'cdn_img_ftp_host'	=>$cdn_img_ftp_host,
			'cdn_img_user'		=>$cdn_img_user,
			'cdn_img_password'	=>$cdn_img_password,
			'as_idx'			=>$as_idx,
			'member_id'			=>$member_id,
		);
		
		if ($product_img != null) {
			$param_as_img['img_type']	= "P";
			$param_as_img['as_img']		= $product_img;
			
			uploadAsImg($db,$param_as_img);
		}
		
		if ($receipt_img != null) {
			$param_as_img['img_type']	= "R";
			$param_as_img['as_img']		= $receipt_img;
			
			uploadAsImg($db,$param_as_img);
		}
	}
}

function uploadAsImg($db,$param) {
	$upload_result = array();
	
	$conn = ftp_connect($param['cdn_img_ftp_host']);
	if (!$conn) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $param['country'], 'MSG_B_ERR_0069', array());
	}
	
	$result = ftp_login($conn,$param['cdn_img_user'],$param['cdn_img_password']);
	if(!$result){
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $param['country'], 'MSG_B_ERR_0068', array());
	}
	
	$img_num = 1;
	$as_img = $param['as_img'];
	for ($i=0; $i<count($as_img); $i++) {
		$img_name = $as_img['name'][$i];
		
		if (!empty($img_name)) {
			$name_arr = explode('.',$img_name);
			$img_ext = $name_arr[count($name_arr) - 1];
			$tmp_file = $as_img['tmp_name'][$i];
			
			$ftp_path = "/as_images/img_AS_".$param['img_type']."_".$img_num."_".time().".".$img_ext;
			
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
			$db->insert(
				"AS_IMG",
				array(
					'AS_IDX'		=>$param['as_idx'],
					'IMG_TYPE'		=>$param['img_type'],
					'IMG_LOCATION'	=>$upload_result[$i],
					
					'CREATER'		=>$param['member_id'],
					'UPDATER'		=>$param['member_id']
				)
			);
		}
	}
}

?>