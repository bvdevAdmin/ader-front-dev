<?php
/*
 +=============================================================================
 | 
 | A/S 신청내용 등록 처리
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

@set_time_limit(3000);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once('/usr/local/src/composer/vendor/autoload.php');

use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

$product_img = null;
if (isset($_FILES['product_img'])) {
	$product_img = $_FILES['product_img'];
}

$receipt_img = null;
if (isset($_FILES['receipt_img'])) {
	$receipt_img = $_FILES['receipt_img'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	/* A/S 코드 생성 */
	$as_code = $_SERVER['HTTP_COUNTRY']."-".date("Ymd-").time();
	
	if (isset($serial_code)) {
		/* 1-1. 블루마크 인증제품 A/S 신청처리 */
		
		/* 동일제품 A/S 진행여부 체크 */
		$cnt_as = $db->count("MEMBER_AS","AS_STATUS != 'ACP' AND SERIAL_CODE = ? AND DEL_FLG = FALSE",array($serial_code));
		if ($cnt_as > 0) {
			$json_result['code'] = 303;
			$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0057', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		$param_as = array(
			$as_code,
			$as_contents,
			
			$serial_code,
			$_SERVER['HTTP_COUNTRY'],
			$_SESSION['MEMBER_IDX']
		);
		
		/* 1-1-1. A/S 신청내용 등록처리 (블루마크) */
		addAS_bluemark($db,$param_as);
	} else {
		/* 1-2. 블루마크 미인증제품 A/S 신청처리 */
		
		$param_as = array(
			$_SERVER['HTTP_COUNTRY'],
			$_SESSION['MEMBER_IDX'],
			$as_code,
			$as_category,
			$as_contents,
			$_SESSION['MEMBER_ID'],
			$_SESSION['MEMBER_ID'],
			$barcode
		);
		
		/* A/S 신청제품 바코드 체크처리 */
		$cnt_option = $db->count("SHOP_OPTION","BARCODE = ?",array($barcode));
		if ($cnt_option > 0) {
			/* 1-2-1. A/S 신청내용 등록처리 (바코드 등록) */
			addAS_barcode($db,$param_as);
		} else {
			/* 1-2-2. A/S 신청내용 등록처리 */
			addAS($db,$param_as);
		}
	}
	
	/* 2.  A/S 이미지 등록처리 */
	$as_idx = $db->last_id();
	if (!empty($as_idx)) {
		/* 2-1 A/S 상품 이미지 등록처리 */
		if ($product_img != null) {
			uploadAS_img($db,$as_idx,"product",$product_img);
		}
		
		/* 2-2 A/S 영수증 이미지 등록처리 */
		if ($receipt_img != null) {
			uploadAS_img($db,$as_idx,"receipt",$receipt_img);
		}
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

/* 1-1-1. A/S 신청내용 등록처리 (블루마크) */
function addAS_bluemark($db,$param_as) {
	$insert_member_as_bluemark_sql = "
		INSERT INTO
			MEMBER_AS
		(
			COUNTRY,
			MEMBER_IDX,
			AS_CODE,
			
			BLUEMARK_FLG,
			SERIAL_CODE,
			
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
			
			CREATER,
			UPDATER
		)
		SELECT
			BL.COUNTRY			AS COUNTRY,
			BL.MEMBER_IDX		AS MEMBER_IDX,
			?					AS AS_CODE,
			
			TRUE				AS BLUEMARK_FLG,
			BI.SERIAL_CODE		AS SERIAL_CODE,
			
			PR.IDX				AS PRODUCT_IDX,
			PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
			PR.PRODUCT_CODE		AS PRODUCT_CODE,
			PR.PRODUCT_NAME		AS PRODUCT_NAME,
			PR.COLOR			AS COLOR,
			
			BI.OPTION_IDX		AS OPTION_IDX,
			BI.BARCODE			AS BARCODE,
			BI.OPTION_NAME		AS OPTION_NAME,
			
			'APL'				AS AS_STATUS,
			?					AS AS_CONTENTS,
			
			BL.MEMBER_ID		AS CREATER,
			BL.MEMBER_ID		AS UPDATER
		FROM
			BLUEMARK_LOG BL
			LEFT JOIN BLUEMARK_INFO BI ON
			BL.BLUEMARK_IDX = BI.IDX
			
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
		WHERE
			BI.SERIAL_CODE = ? AND
			BL.COUNTRY = ? AND
			BL.MEMBER_IDX = ?
			
	";
	
	$db->query($insert_member_as_bluemark_sql,$param_as);
}

/* 1-2-1. A/S 신청내용 등록처리 (바코드) */
function addAS_barcode($db,$param_as) {
	$insert_member_as_barcode_sql = "
		INSERT INTO
			MEMBER_AS
		(
			COUNTRY,
			MEMBER_IDX,
			AS_CODE,
			AS_CATEGORY_IDX,
			
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
			
			CREATER,
			UPDATER
		)
		SELECT
			?					AS COUNTRY,
			?					AS MEMBER_IDX,
			?					AS AS_CODE,
			?					AS AS_CATEGORY_IDX,
			
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
			
			?					AS CREATER,
			?					AS UPDATER
		FROM
			SHOP_PRODUCT PR
			
			LEFT JOIN SHOP_OPTION OO ON
			PR.IDX = OO.PRODUCT_IDX
		WHERE
			OO.BARCODE = ?
	";
	
	$db->query($insert_member_as_barcode_sql,$param_as);
}

/* 1-2-2. A/S 신청내용 등록처리 */
function addAS($db,$param) {
	$db->insert(
		"MEMBER_AS",
		array(
			'COUNTRY'			=>$param[0],
			'MEMBER_IDX'		=>$param[1],
			'AS_CODE'			=>$param[2],
			'AS_CATEGORY_IDX'	=>$param[3],
			
			'AS_STATUS'			=>"APL",
			'AS_CONTENTS'		=>$param[4],
			
			'CREATER'			=>$param[5],
			'UPDATER'			=>$param[6],
			
			'BARCODE'			=>$param[7],
		)
	);
}

function uploadAS_img($db,$as_idx,$img_type,$files) {
	$param_files = setParam_files($files);
	if (count($param_files) > 0) {
		/* 개발서버 업로드 경로 */
		$server_location = front_upload."/as/";
		
		$as_files = uploadAS_server($server_location,$img_type,$param_files);
		
		/* AWS 서버 - 에디토리얼 파일 업로드 */
		$upload_result = uploadAS_AWS($img_type,$as_files);
		
		$cnt_result = $upload_result['cnt_result'];
		if (isset($cnt_result) && $cnt_result > 0) {
			$as_img = $upload_result['upload'];
			
			addAS_img($db,$as_idx,$img_type,$as_img,$_SESSION['MEMBER_ID']);
		}
	}
}

function setParam_files($param) {
	$param_files = array();
	
	$file_keys = array_keys($param);
	
	$cnt_file = count($param['name']);
	if ($cnt_file > 0) {
		for ($i=0; $i<$cnt_file; $i++) {
			foreach($file_keys as $key) {
				$param_files[$i][$key] = $param[$key][$i];
			}
		}
	}
	
	return $param_files;
}

function uploadAS_server($server_location,$img_type,$img_O) {
	$as_files = array();
	
	$tmp_num = 1;
	
	foreach($img_O as $file) {
		$file_name		= $file['name'];
		$file_tmp_name	= $file['tmp_name'];
		$file_size		= $file['size'];
		$file_ext		= strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
		
		$img_num		= sprintf('%02d',$tmp_num);
		
		$upload_name = "img_as_".$img_type."_".$img_num."-".uniqid().".".$file_ext;
		$upload_path = $server_location.$img_type."/".$upload_name;
		
		if (move_uploaded_file($file_tmp_name,$upload_path)) {
			array_push($as_files,$upload_path);
		}
		
		$tmp_num++;
	}
	
	return $as_files;
}

function uploadAS_AWS($img_type,$as_files) {
	/* AWS 서버 - 접속정보 */
	$aws_ftp_host	= "s-a8518134c23d4dd59.server.transfer.ap-northeast-2.amazonaws.com";
	$aws_user		= "s3-cloud-bucket-ader-user";
	$aws_password	= "dkejdpfj1!";
	$aws_key_file	= dir_f_api."/_legacy/s3-cloud-bucket-ader-key.ppk";
	
	$cnt_result	= 0;
	
	$upload = array();
	
	$dir_as = "/$img_type/";
	
	/* AWS 서버 루트 디렉토리 */
	$ftp_url = CDN."/member/as$dir_as";
	$ftp_dir = "/s3-cloud-bucket-ader/s3-cloud-bucket-ader-user/member/as$dir_as";
	
	/* AWS 서버 - 접속 */
	$sftp = new SFTP($aws_ftp_host);
	if ($sftp) {
		/* AWS 서버 - PRIVATE KEY */
		$private_key = PublicKeyLoader::load(file_get_contents($aws_key_file),$aws_password);
		
		/* AWS 서버 - 로그인 */
		$result = $sftp->login($aws_user,$private_key);
		if ($result) {
			if (count($as_files) > 0) {
				foreach($as_files as $tmp) {
					$tmp_img	= explode("/",$tmp);
					$file_name	= $tmp_img[count($tmp_img) - 1];
					
					$file_url		= $ftp_url.$file_name;
					$file_location	= $ftp_dir.$file_name;
					
					if ($sftp->put($file_location,$tmp,SFTP::SOURCE_LOCAL_FILE)) {
						array_push($upload,[
							'file_url'		=>$file_url,
							'file_location'	=>"/member/as$dir_as".$file_name
						]);
					}
					
					/* AWS 서버 - 업로드 용 임시 파일 삭제처리 */
					file_del($tmp);
				}
			}
		}
	}
	
	$cnt_result = count($upload);
	
	$upload_result = array(
		'cnt_result'	=>$cnt_result,
		'upload'		=>$upload,
	);
	
	return $upload_result;
}

function addAS_img($db,$as_idx,$img_type,$as_img,$member_id) {
	$tmp_img_type = "";
	if ($img_type == "product") {
		$tmp_img_type = "P";
	} else if ($img_type == "receipt") {
		$tmp_img_type = "R";
	}
	
	foreach($as_img as $img) {
		if ($img != null) {
			$db->insert(
				"AS_IMG",
				array(
					'AS_IDX'			=>$as_idx,
					'IMG_TYPE'			=>$tmp_img_type,
					'IMG_LOCATION'		=>$img['file_location'],
					'CREATER'			=>$member_id,
					'UPDATER'			=>$member_id
				)
			);
		}
	}
}

?>