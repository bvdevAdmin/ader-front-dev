<?php
/*
 +=============================================================================
 | 
 | 1:1 문의 수정/삭제
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.02.27
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once('/usr/local/src/composer/vendor/autoload.php');

use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

$member_ip	= $_SERVER['REMOTE_ADDR'];

$qna_img = null;
if (isset($_FILES['qna_img'])) {
	$qna_img = $_FILES['qna_img'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$cnt_qa = $db->count("BOARD_QUESTION","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($board_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	if ($cnt_qa > 0) {
		if ($action_type != null) {
			switch ($action_type) {
				case "UPDATE" :
					$db->update(
						"BOARD_QUESTION",
						array(
							'CATEGORY_IDX'		=>$category_idx,
							'MEMBER_IP'			=>$member_ip,
							'BOARD_TITLE'		=>$qna_title,
							'BOARD_CONTENTS'	=>$qna_contents,
							'UPDATE_DATE'		=>NOW(),
							'UPDATER'			=>$_SESSION['MEMBER_ID']
						),
						"IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",
						array($board_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])
					);

					if (isset($img_idx) && count($img_idx) > 0) {
						$db->update(
							"BOARD_IMAGE",
							array(
								'DEL_FLG'		=>1,
								
								'UPDATE_DATE'	=>NOW(),
								'UPDATER'		=>$_SESSION['MEMBER_ID']
							),
							"IDX NOT IN (".implode(',',array_fill(0,count($img_idx),'?')).")",
							$img_idx
						);
					} else {
						$db->update(
							"BOARD_IMAGE",
							array(
								'DEL_FLG'		=>1,

								'UPDATE_DATE'	=>NOW(),
								'UPDATER'		=>$_SESSION['MEMBER_ID']
							),
							"BOARD_IDX = ?",
							array($board_idx)
						);
					}

					if ($qna_img != null) {
						uploadQnA_img($db,$board_idx,$qna_img);
					}

					break;
				
				case "DELETE" :
					$db->delete(
						"BOARD_QUESTION",
						"IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",
						array($board_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])
					);

					break;
			}
		}
	}
}

function uploadQnA_img($db,$qna_idx,$files) {
	$param_files = setParam_files($files);
	if (count($param_files) > 0) {
		/* 개발서버 업로드 경로 */
		$server_location = front_upload."/qna";
		
		$qna_files = uploadQnA_server($server_location,$param_files);
		
		/* AWS 서버 - 에디토리얼 파일 업로드 */
		$upload_result = uploadQnA_AWS($qna_files);
		
		$cnt_result = $upload_result['cnt_result'];
		if (isset($cnt_result) && $cnt_result > 0) {
			$qna_img = $upload_result['upload'];
			
			addQnA_img($db,$qna_idx,$qna_img);
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

function uploadQnA_server($server_location,$img_O) {
	$qna_files = array();
	
	$tmp_num = 1;
	
	foreach($img_O as $file) {
		$file_name		= $file['name'];
		$file_tmp_name	= $file['tmp_name'];
		$file_size		= $file['size'];
		$file_ext		= strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
		
		$img_num		= sprintf('%02d',$tmp_num);
		
		$upload_name = "img_qna_".$img_num."-".uniqid().".".$file_ext;
		$upload_path = $server_location."/".$upload_name;
		
		if (move_uploaded_file($file_tmp_name,$upload_path)) {
			array_push($qna_files,$upload_path);
		}
		
		$tmp_num++;
	}
	
	return $qna_files;
}

function uploadQnA_AWS($qna_files) {
	/* AWS 서버 - 접속정보 */
	$aws_ftp_host	= "s-a8518134c23d4dd59.server.transfer.ap-northeast-2.amazonaws.com";
	$aws_user		= "s3-cloud-bucket-ader-user";
	$aws_password	= "dkejdpfj1!";
	$aws_key_file	= dir_f_api."/_legacy/s3-cloud-bucket-ader-key.ppk";
	
	$cnt_result	= 0;
	
	$upload = array();
	
	$dir_qna = "/qna/";
	
	/* AWS 서버 루트 디렉토리 */
	$ftp_url = CDN."/member$dir_qna";
	$ftp_dir = "/s3-cloud-bucket-ader/s3-cloud-bucket-ader-user/member$dir_qna";
	
	/* AWS 서버 - 접속 */
	$sftp = new SFTP($aws_ftp_host);
	if ($sftp) {
		/* AWS 서버 - PRIVATE KEY */
		$private_key = PublicKeyLoader::load(file_get_contents($aws_key_file),$aws_password);
		
		/* AWS 서버 - 로그인 */
		$result = $sftp->login($aws_user,$private_key);
		if ($result) {
			if (count($qna_files) > 0) {
				foreach($qna_files as $tmp) {
					$tmp_img	= explode("/",$tmp);
					$file_name	= $tmp_img[count($tmp_img) - 1];
					
					$file_url		= $ftp_url.$file_name;
					$file_location	= $ftp_dir.$file_name;
					
					if ($sftp->put($file_location,$tmp,SFTP::SOURCE_LOCAL_FILE)) {
						array_push($upload,[
							'file_url'		=>$file_url,
							'file_location'	=>"/member$dir_qna".$file_name
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

function addQnA_img($db,$qna_idx,$qna_img) {
	
	
	foreach($qna_img as $img) {
		if ($img != null) {
			$db->insert(
				"BOARD_IMAGE",
				array(
					'BOARD_IDX'		=>$qna_idx,
					'IMG_LOCATION'	=>$img['file_location'],
					'CREATER'		=>$_SESSION['MEMBER_ID'],
					'UPDATER'		=>$_SESSION['MEMBER_ID']
				)
			);
		}
	}
}

?>