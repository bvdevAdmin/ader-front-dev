<?php
/*
 +=============================================================================
 | 
 | 공통함수 - 상품 색상/사이즈 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

// require "/usr/local/src/composer/vendor/autoload.php";

use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

$cdn_img_ftp_host 				= "cdn-ader-orig.fastedge.net";
$cdn_img_user 					= "imageader";
$cdn_img_password 				= "hhg3dnjf16dlf!@#$5";

/* 메뉴 정보 조회 */
function getMenuInfo($db,$country,$param_type,$param_idx) {
	$menu_info = array();
	
	$parent_info = array();
	if ($param_type != null && $param_idx > 0) {
		$parent_info = getMenuParentInfo($db,$param_type,$param_idx);
	}
	
	if ($param_type == "HL1") {
		$select_menu_sql = "
			(
				SELECT
					HL1.IDX					AS MENU_IDX,
					'HL1'					AS MENU_TYPE,
					HL1.MENU_TITLE			AS MENU_TITLE,
					HL1.IMG_LOCATION		AS IMG_LOCATION,
					HL1.EXT_LINK_FLG		AS EXT_LINK_FLG,
					IFNULL(
						HL1.MENU_LINK,''
					)						AS MENU_LINK
				FROM
					MENU_HL_1 HL1
				WHERE
					HL1.IDX = ? AND
					HL1.COUNTRY = ? AND
					HL1.A1_EXP_FLG = TRUE
			) UNION (
				SELECT
					HL2.IDX					AS MENU_IDX,
					'HL2'					AS MENU_TYPE,
					HL2.MENU_TITLE			AS MENU_TITLE,	
					HL2.IMG_LOCATION		AS IMG_LOCATION,
					HL2.EXT_LINK_FLG		AS EXT_LINK_FLG,
					IFNULL(
						HL2.MENU_LINK,''
					)						AS MENU_LINK
				FROM
					MENU_HL_2 HL2
				WHERE
					HL2.COUNTRY = ? AND
					HL2.PARENT_IDX = ? AND
					HL2.A1_EXP_FLG = TRUE
			)
		";
		
		$db->query($select_menu_sql,array($param_idx,$country,$country,$param_idx));
		
		foreach($db->fetch() as $data) {
			$menu_type	= $data['MENU_TYPE'];
			$menu_idx	= $data['MENU_IDX'];
			
			$menu_param = "&menu_type=".$menu_type."&menu_idx=".$menu_idx;
			
			$menu_link = $data['MENU_LINK'];
			if (strlen($menu_link) > 0) {
				if ($data['EXT_LINK_FLG'] == true) {
					$menu_link = "http://".$data['MENU_LINK'];
				} else if ($data['EXT_LINK_FLG'] == false) {
					$menu_link = $data['MENU_LINK'].$menu_param;
				}
			}
			
			$selected = false;
			if ($menu_type == "HL1" && ($menu_idx == $param_idx)) {
				$selected = true;
			}
			
			$menu_info[] = array(
				'menu_title'		=>$data['MENU_TITLE'],
				'img_location'		=>$data['IMG_LOCATION'],
				'menu_link'			=>$menu_link,
				
				'selected'			=>$selected
			);
		}
	} else if ($param_type == "HL2") {
		$select_menu_sql = "
			(
				SELECT
					HL1.IDX					AS MENU_IDX,
					'HL1'					AS MENU_TYPE,
					HL1.MENU_TITLE			AS MENU_TITLE,
					HL1.IMG_LOCATION		AS IMG_LOCATION,
					HL1.EXT_LINK_FLG		AS EXT_LINK_FLG,
					IFNULL(
						HL1.MENU_LINK,''
					)						AS MENU_LINK
				FROM
					MENU_HL_1 HL1
				WHERE
					HL1.IDX = (
						SELECT
							S_HL2.PARENT_IDX
						FROM
							MENU_HL_2 S_HL2
						WHERE
							S_HL2.IDX = ?
					) AND
					HL1.A1_EXP_FLG = TRUE
			) UNION (
				SELECT
					HL2.IDX					AS MENU_IDX,
					'HL2'					AS MENU_TYPE,
					HL2.MENU_TITLE			AS MENU_TITLE,
					HL2.IMG_LOCATION		AS IMG_LOCATION,
					HL2.EXT_LINK_FLG		AS EXT_LINK_FLG,
					IFNULL(
						HL2.MENU_LINK,''
					)						AS MENU_LINK
				FROM
					MENU_HL_2 HL2
				WHERE
					(
						HL2.PARENT_IDX = (
							SELECT
								S_HL2.PARENT_IDX
							FROM
								MENU_HL_2 S_HL2
							WHERE
								S_HL2.IDX = ?
						) AND
						A1_EXP_FLG = TRUE
					) OR
					(
						HL2.IDX = ? AND
						HL2.A0_EXP_FLG = FALSE AND
						HL2.A1_EXP_FLG = FALSE
					)
			)
		";
		
		$db->query($select_menu_sql,array($param_idx,$param_idx,$param_idx));
		
		foreach($db->fetch() as $data) {
			$menu_type	= $data['MENU_TYPE'];
			$menu_idx	= $data['MENU_IDX'];
			
			$menu_param = "&menu_type=".$menu_type."&menu_idx=".$menu_idx;
			
			$menu_link = $data['MENU_LINK'];
			if (strlen($menu_link) > 0) {
				if ($data['EXT_LINK_FLG'] == true) {
					$menu_link = "http://".$data['MENU_LINK'];
				} else if ($data['EXT_LINK_FLG'] == false) {
					$menu_link = $data['MENU_LINK'].$menu_param;
				}
			}
			
			$selected = false;
			if ($menu_type == "HL2" && ($menu_idx == $param_idx)) {
				$selected = true;
			}
			
			$menu_info[] = array(
				'menu_title'		=>$data['MENU_TITLE'],
				'menu_location'		=>$parent_info['parent_title']." ".$data['MENU_TITLE'],
				'img_location'		=>$data['IMG_LOCATION'],
				'menu_link'			=>$menu_link,
				
				'selected'			=>$selected
			);
		}
	}
	
	return $menu_info;
}

/* 부모 메뉴 정보 조회 */
function getMenuParentInfo($db,$param_type,$param_idx) {
	$parent_info = array();
	
	$select_parent_segment_sql = "";
	if ($param_type == "HL1") {
		$select_parent_segment_sql = "
			SELECT
				MS.IDX				AS PARENT_IDX,
				MS.MENU_TITLE		AS PARENT_TITLE
			FROM
				MENU_SEGMENT MS
			WHERE
				IDX = (
					SELECT
						S_HL1.PARENT_IDX
					FROM
						MENU_HL_1 S_HL1
					WHERE
						IDX = ?
				)
		";
	} else if ($param_type == "HL2") {
		$select_parent_segment_sql = "
			SELECT
				MS.IDX				AS PARENT_IDX,
				MS.MENU_TITLE		AS PARENT_TITLE
			FROM
				MENU_SEGMENT MS
			WHERE
				MS.IDX = (
					SELECT
						S_HL1.PARENT_IDX
					FROM
						MENU_HL_1 S_HL1
					WHERE
						S_HL1.IDX = (
							SELECT
								S_HL2.PARENT_IDX
							FROM
								MENU_HL_2 S_HL2
							WHERE
								S_HL2.IDX = ?
						)
				)
		";
	}
	
	$db->query($select_parent_segment_sql,array($param_idx));
	
	foreach($db->fetch() as $parent_data) {
		$parent_info = array(
			'parent_idx'		=>$parent_data['PARENT_IDX'],
			'parent_title'		=>$parent_data['PARENT_TITLE']
		);
	}
	
	return $parent_info;
}

/* 구매 가능 수량 취득 MIN (WMS 실재고 - 출고 예정 재고(결제완료/상품준비중) , WCC 잔여재고) */
function getPurchaseableQtyByIdx($db,$product_idx,$option_idx) {
	$limit_qty = 0;
	
	$where = " 1=1 ";
	
	if ($product_idx > 0) {
		$where .= "
			AND (V_ST.PRODUCT_IDX = ".$product_idx.")
		";
	}
	
	if ($option_idx > 0) {
		$where .= "
			AND (V_ST.OPTION_IDX = ".$option_idx.")
		";
	}
	
	$select_stock_sql = "
		SELECT
			SUM(V_ST.PURCHASEABLE_QTY)		AS PURCHASEABLE_QTY
		FROM
			V_STOCK V_ST
		WHERE
			".$where."
	";
	
	$db->query($select_stock_sql);
	
	foreach($db->fetch() as $data) {
		$limit_qty = $data['PURCHASEABLE_QTY'];
	}
	
	return $limit_qty;
}

/* 상품 컬러정보 조회 */
function getProductColor($db,$product_idx) {
	$product_color = array();
	
	if ($product_idx != null) {
		$select_product_sql = "
			SELECT
				PR.IDX			AS PRODUCT_IDX,
				PR.COLOR		AS COLOR,
				PR.COLOR_RGB	AS COLOR_RGB,
				J_ST.LIMIT_QTY	AS LIMIT_QTY
			FROM
				SHOP_PRODUCT PR
				
				LEFT JOIN (
					SELECT
						V_ST.PRODUCT_IDX			AS PRODUCT_IDX,
						SUM(V_ST.PURCHASEABLE_QTY)	AS LIMIT_QTY
					FROM
						V_STOCK V_ST
					GROUP BY
						V_ST.PRODUCT_IDX
				) J_ST ON
				PR.IDX = J_ST.PRODUCT_IDX
			WHERE
				PR.SALE_FLG = TRUE AND
				PR.STYLE_CODE = (
					SELECT
						S_PR.STYLE_CODE
					FROM
						SHOP_PRODUCT S_PR
					WHERE
						S_PR.IDX = ?
				)
		";
	
		$db->query($select_product_sql,array($product_idx));
		
		foreach($db->fetch() as $data) {
			$stock_status = "";
			
			/* 구매 가능 수량 취득 MIN (WMS 실재고 - 출고 예정 재고(결제완료/상품준비중) , WCC 잔여재고) */
			//$limit_qty = getPurchaseableQtyByIdx($db,$product_idx,0);
			$limit_qty = $data['LIMIT_QTY'];
			
			$reorder_flg = false;
			if ($limit_qty > 0) {
				$stock_status = "STIN";	//재고 있음 (Stock in)
			} else {
				$stock_status = "STSO";	//재고 없음(사선)		→ 증가 예정 재고 없음 (Stock sold out)
				
				$member_idx = 0;
				if (isset($_SESSION['MEMBER_IDX'])) {
					$member_idx = $_SESSION['MEMBER_IDX'];
				}
				
				if ($member_idx > 0) {
					$reorder_cnt = $db->count("PRODUCT_REORDER","MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx);
					
					if ($reorder_cnt > 0) {
						$reorder_flg = true;
					}
				}
			}
			
			$product_color[] = array(
				'product_idx'		=>$data['PRODUCT_IDX'],
				'color'				=>$data['COLOR'],
				'color_rgb'			=>$data['COLOR_RGB'],
				'stock_status'		=>$stock_status,
				'reorder_flg'		=>$reorder_flg
			);
		}
	}
	
	return $product_color;
}

/* 일반/세트 상품 사이즈별 재고정보 조회 */
function getProductSize($db,$product_type,$set_type,$product_idx) {
	$product_size = array();
	
	if ($product_type == "B") {
		/* 일반상품 사이즈별 재고정보 조회 */
		$product_size = getSIZE_B($db,$product_idx);
	} else if ($product_type == "S" && $set_type != null) {
		/* 세트상품 사이즈별 재고정보 조회 */
		$product_size = getSIZE_S($db,$product_idx);
	}
	
	return $product_size;
}

/* 일반상품 사이즈별 재고정보 조회 */
function getSIZE_B($db,$product_idx) {
	$product_size = array();
	
	$select_product_sql = "
		SELECT
			PR.IDX						AS PRODUCT_IDX,
			PR.SOLD_OUT_QTY				AS SOLD_OUT_QTY,
			OO.IDX						AS OPTION_IDX,
			OO.OPTION_NAME				AS OPTION_NAME,
			
			J_PS.CNT					AS STOCK_STANDBY,
			J_ST.LIMIT_QTY				AS LIMIT_QTY
		FROM
			SHOP_PRODUCT PR
			LEFT JOIN ORDERSHEET_OPTION OO ON
			PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
			
			LEFT JOIN (
				SELECT
					S_PS.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PS.OPTION_IDX		AS OPTION_IDX,
					COUNT(S_PS.IDX)		AS CNT
				FROM
					PRODUCT_STOCK S_PS
				WHERE
					S_PS.STOCK_DATE > NOW()
				GROUP BY
					S_PS.PRODUCT_IDX,
					S_PS.OPTION_IDX
			) J_PS ON
			PR.IDX = J_PS.PRODUCT_IDX AND
			OO.IDX = J_PS.OPTION_IDX
			
			LEFT JOIN (
				SELECT
					V_ST.PRODUCT_IDX		AS PRODUCT_IDX,
					V_ST.OPTION_IDX			AS OPTION_IDX,
					V_ST.PURCHASEABLE_QTY	AS LIMIT_QTY
				FROM
					V_STOCK V_ST
				GROUP BY
					V_ST.PRODUCT_IDX,
					V_ST.OPTION_IDX
			) J_ST ON
			PR.IDX = J_ST.PRODUCT_IDX AND
			OO.IDX = J_ST.OPTION_IDX
		WHERE
			PR.IDX = ? AND
			PR.SALE_FLG = TRUE
	";
	
	$db->query($select_product_sql,array($product_idx));
	
	foreach($db->fetch() as $data) {
		$option_idx = $data['OPTION_IDX'];
		
		$size_type = setSizeType($data['OPTION_NAME']);
		
		$sold_out_qty	= $data['SOLD_OUT_QTY'];
		$stock_standby	= $data['STOCK_STANDBY'];
		
		//$limit_qty = getPurchaseableQtyByIdx($db,$product_idx,$option_idx);
		$limit_qty = $data['LIMIT_QTY'];
		
		$stock_status = calcStockQty($sold_out_qty,$stock_standby,$limit_qty);
		
		$product_size[] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			
			'size_type'			=>$size_type,
			'stock_status'		=>$stock_status
		);
	}
	
	return $product_size;
}

function getSIZE_S($db,$product_idx) {
	$product_size = array();
	
	$select_set_name_sql = "
		SELECT
			DISTINCT PR.PRODUCT_NAME	AS PRODUCT_NAME,
			PR.SET_TYPE					AS SET_TYPE
		FROM
			SET_PRODUCT SP
			LEFT JOIN SHOP_PRODUCT PR ON
			SP.SET_PRODUCT_IDX = PR.IDX
		WHERE
			SP.SET_PRODUCT_IDX = ?
	";
	
	$db->query($select_set_name_sql,array($product_idx));
	
	foreach($db->fetch() as $data) {
		$product_name = $data['PRODUCT_NAME'];
		
		$set_option = array();
		
		$set_type = $data['SET_TYPE'];
		if ($set_type == "SZ") {
			/* 사이즈 세트 옵션정보 조회 */
			$set_option = getSET_SZ($db,$product_idx,$product_name);
		} else if ($set_type == "CL") {
			/* 컬러 세트 옵션정보 조회 */
			$set_option = getSET_CL($db,$product_idx,$product_name);
		}
		
		$product_size[] = array(
			'product_name'		=>$product_name,
			'set_option_info'	=>$set_option
		);
	}
	
	return $product_size;
}

/* 사이즈 세트 옵션정보 조회 */
function getSET_SZ($db,$product_idx,$product_name) {
	$set_option = array();
	
	$select_set_product_SZ_sql = "
		SELECT
			SP.PRODUCT_IDX					AS PRODUCT_IDX,
			GROUP_CONCAT(SP.OPTION_IDX)		AS OPTION_IDX
		FROM
			SET_PRODUCT SP
			LEFT JOIN SHOP_PRODUCT PR ON
			SP.PRODUCT_IDX = PR.IDX
		WHERE
			SP.SET_PRODUCT_IDX = ?
		GROUP BY
			SP.PRODUCT_IDX
	";
	
	$db->query($select_set_product_SZ_sql,array($product_idx));
	
	foreach($db->fetch() as $data_product) {
		$set_option_idx = $data_product['OPTION_IDX'];
		
		$select_set_option_sql = "
			SELECT
				OO.IDX					AS OPTION_IDX,
				OO.OPTION_NAME			AS OPTION_NAME,
				(
					SELECT
						COUNT(IDX)
					FROM
						PRODUCT_STOCK S_PS
					WHERE
						S_PS.OPTION_IDX = OO.IDX AND
						S_PS.STOCK_DATE > NOW()
				)						AS STOCK_STANDBY,
				
				J_ST.LIMIT_QTY			AS LIMIT_QTY
			FROM
				ORDERSHEET_OPTION OO
				
				LEFT JOIN (
					SELECT
						V_ST.OPTION_IDX			AS OPTION_IDX,
						V_ST.PURCHASEABLE_QTY	AS LIMIT_QTY
					FROM
						V_STOCK V_ST
					GROUP BY
						V_ST.OPTION_IDX
				) J_ST ON
				OO.IDX = J_ST.OPTION_IDX
			WHERE
				OO.IDX IN (".$set_option_idx.")
		";
		
		$db->query($select_set_option_sql);
		
		foreach($db->fetch() as $data_option) {
			$option_idx = $data_option['OPTION_IDX'];
			
			//$limit_qty = getPurchaseableQtyByIdx($db,$product_idx,$option_idx);
			$limit_qty = $data_option['LIMIT_QTY'];
			
			$size_type = setSizeType($data_option['OPTION_NAME']);
			
			$stock_status = calcStockQty(0,$data_option['STOCK_STANDBY'],$limit_qty);
			
			$set_option[] = array(
				'product_idx'		=>$data_product['PRODUCT_IDX'],
				'option_idx'		=>$data_option['OPTION_IDX'],
				'option_name'		=>$data_option['OPTION_NAME'],
				
				'size_type'			=>$size_type,
				
				'stock_status'		=>$stock_status
			);
		}
	}
	
	return $set_option;
}

/* 컬러 세트 옵션정보 조회 */
function getSET_CL($db,$product_idx,$product_name) {
	$set_option = array();
	
	$select_set_product_CL_sql = "
		SELECT
			PR.IDX							AS PRODUCT_IDX,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			OO.IDX							AS OPTION_IDX,
			
			J_PS.STOCK_STANDBY				AS STOCK_STANDBY,
			
			V_ST.PURCHASEABLE_QTY			AS LIMIT_QTY,
			V_ST.ORDER_QTY					AS ORDER_QTY,
			
			J_OE.EXCHANGE_QTY				AS EXCHANGE_QTY,
			J_OR.REFUND_QTY					AS REFUND_QTY
		FROM
			SHOP_PRODUCT PR
			LEFT JOIN ORDERSHEET_OPTION OO ON
			PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
			
			LEFT JOIN V_STOCK V_ST ON
			PR.IDX = V_ST.PRODUCT_IDX AND
			OO.IDX = V_ST.OPTION_IDX
			
			LEFT JOIN (
				SELECT
					S_PS.OPTION_IDX				AS OPTION_IDX,
					SUM(S_PS.STOCK_QTY)			AS STOCK_STANDBY
				FROM
					PRODUCT_STOCK S_PS
				WHERE
					S_PS.STOCK_DATE > NOW()
				GROUP BY
					S_PS.OPTION_IDX
			) AS J_PS ON
			OO.IDX = J_PS.OPTION_IDX
			
			LEFT JOIN (
				SELECT
					S_OE.PREV_OPTION_IDX		AS PREV_OPTION_IDX,
					SUM(S_OE.PRODUCT_QTY)		AS EXCHANGE_QTY
				FROM
					ORDER_PRODUCT_EXCHANGE S_OE
				WHERE
					S_OE.ORDER_STATUS = 'OEP' AND
					S_OE.STOCK_FLG = TRUE
			) AS J_OE ON
			OO.IDX = J_OE.PREV_OPTION_IDX
			
			LEFT JOIN (
				SELECT
					S_OR.OPTION_IDX				AS OPTION_IDX,
					SUM(S_OR.PRODUCT_QTY)		AS REFUND_QTY
				FROM
					ORDER_PRODUCT_REFUND S_OR
				WHERE
					S_OR.ORDER_STATUS = 'ORP' AND
					S_OR.STOCK_FLG = TRUE
			) AS J_OR ON
			OO.IDX = J_OR.OPTION_IDX
		WHERE
			PR.IDX IN (
				SELECT
					S_SP.PRODUCT_IDX
				FROM
					SET_PRODUCT S_SP
				WHERE
					S_SP.SET_PRODUCT_IDX = ?
			)
	";
	
	$db->query($select_set_product_CL_sql,array($product_idx));
	
	foreach($db->fetch() as $option_data) {
		$stock_status = calcStockQty(0,$option_data['STOCK_STANDBY'],$option_data['LIMIT_QTY']);
		
		$set_option[] = array(
			'product_idx'		=>$option_data['PRODUCT_IDX'],
			'color'				=>$option_data['COLOR'],
			'color_rgb'			=>$option_data['COLOR_RGB'],
			'option_idx'		=>$option_data['OPTION_IDX'],
			
			'stock_status'		=>$stock_status
		);
	}
	
	return $set_option;
}

function cdn_img_upload($db,$country,$img_type,$file_upload,$ftp_dir) {
	$upload_result = array();
	
	/* AWS 서버 - 접속정보 */
	$aws_ftp_host	= "s-a8518134c23d4dd59.server.transfer.ap-northeast-2.amazonaws.com";
	$aws_user		= "s3-cloud-bucket-ader-user";
	$aws_password	= "dkejdpfj1!";
	$aws_key_file	= "/var/www/dev-tmp/www/api/common/s3-cloud-bucket-ader-key.ppk";
	
	/* AWS 서버 - 접속 */
	$sftp = new SFTP($aws_ftp_host);
	if ($sftp) {
		/* AWS 서버 - PRIVATE KEY */
		$private_key = PublicKeyLoader::load(file_get_contents($aws_key_file),$aws_password);
		
		/* AWS 서버 - 로그인 */
		$result = $sftp->login($aws_user,$private_key);
		if ($result) {
			$img_num = 1;
			
			for ($i=0; $i<count($file_upload['name']); $i++) {
				$file_img = $file_upload['name'][$i];
				
				if (!empty($file_img)) {
					$tmp_file_name = explode('.',$file_img);
					$file_ext = $tmp_file_name[count($tmp_file_name) - 1];
					
					$tmp_name = $file_upload['tmp_name'][$i];
					
					$ftp_path = $ftp_dir."/img_".$img_type."_".$img_num."_".time().".".$file_ext;
					
					if ($sftp->put("/s3-cloud-bucket-ader/s3-cloud-bucket-ader-user".$ftp_path,$tmp_name,SFTP::SOURCE_LOCAL_FILE)) {
						array_push($upload_result,$ftp_path);
						$img_num++;
					}
				}
			}
			
			unset($sftp);
		}
	}
	
	return $upload_result;
}

function getBasketCnt($db,$country,$member_idx) {
	$basket_cnt = $db->count("BASKET_INFO","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND PARENT_IDX = 0 AND DEL_FLG = FALSE");
	
	return $basket_cnt;
}

/* 회원 갱신 전 정보 조회 */
function getMEMBER_prev($db,$country,$member_idx) {
	$member_prev = array();
	
	$select_member_prev_sql = "
		SELECT
			IDX					AS MEMBER_IDX,
			COUNTRY				AS COUNTRY,
			MEMBER_ID			AS MEMBER_ID,
			MEMBER_NAME			AS MEMBER_NAME,
			MEMBER_PW			AS MEMBER_PW,
			PW_DATE				AS PW_DATE,
			MEMBER_STATUS		AS MEMBER_STATUS,
			
			SLEEP_DATE			AS SLEEP_DATE,
			SLEEP_OFF_DATE		AS SLEEP_OFF_DATE,
			DROP_DATE			AS DROP_DATE,
			
			TEL_MOBILE			AS TEL_MOBILE,
			RECEIVE_TEL_FLG		AS RECEIVE_TEL_FLG,
			RECEIVE_SMS_FLG		AS RECEIVE_SMS_FLG,
			RECEIVE_EMAIL_FLG	AS RECEIVE_EMAIL_FLG,
			RECEIVE_PUSH_FLG	AS RECEIVE_PUSH_FLG,
			SUSPICION_FLG		AS SUSPICION_FLG
		FROM
			MEMBER_".$country." MI
		WHERE
			MI.IDX = ?
	";
	
	$db->query($select_member_prev_sql,array($member_idx));
	
	foreach($db->fetch() as $data) {
		$member_prev = array(
			'member_idx'			=>$data['MEMBER_IDX'],
			'country'				=>$data['COUNTRY'],
			'member_id'				=>$data['MEMBER_ID'],
			'member_name'			=>$data['MEMBER_NAME'],
			'member_pw'				=>$data['MEMBER_PW'],
			'pw_date'				=>$data['PW_DATE'],
			'member_status'			=>$data['MEMBER_STATUS'],
			
			'sleep_date'			=>$data['SLEEP_DATE'],
			'sleep_off_date'		=>$data['SLEEP_OFF_DATE'],
			'drop_date'				=>$data['DROP_DATE'],
			
			'tel_mobile'			=>$data['TEL_MOBILE'],
			'receive_tel_flg'		=>$data['RECEIVE_TEL_FLG'],
			'receive_sms_flg'		=>$data['RECEIVE_SMS_FLG'],
			'receive_email_flg'		=>$data['RECEIVE_EMAIL_FLG'],
			'receive_push_flg'		=>$data['RECEIVE_PUSH_FLG'],
			'suspicion_flg'			=>$data['SUSPICION_FLG']
		);
	}
	
	return $member_prev;
}

/* 회원 갱신 로그 등록처리 */
function addMEMBER_log($db,$member) {
	$insert_member_update_log_sql = "
		INSERT INTO
			MEMBER_UPDATE_LOG
		(
			COUNTRY,
			MEMBER_IDX,
			PREV_MEMBER_STATUS,
			MEMBER_STATUS,
			SLEEP_DATE,
			SLEEP_OFF_DATE,
			DROP_DATE,
			MEMBER_ID,
			MEMBER_NAME,
			PW_DATE,
			PREV_MEMBER_PW,
			MEMBER_PW,
			PREV_TEL_MOBILE,
			TEL_MOBILE,
			RECEIVE_TEL_FLG,
			RECEIVE_SMS_FLG,
			RECEIVE_EMAIL_FLG,
			RECEIVE_PUSH_FLG,
			SUSPICION_FLG,
			UPDATE_DATE,
			UPDATER
		)
		SELECT
			MI.COUNTRY						AS COUNTRY,
			MI.IDX							AS MEMBER_IDX,
			?								AS PREV_MEMBER_STATUS,
			MI.MEMBER_STATUS				AS MEMBER_STATUS,
			MI.SLEEP_DATE					AS SLEEP_DATE,
			MI.SLEEP_OFF_DATE				AS SLEEP_OFF_DATE,
			MI.DROP_DATE					AS DROP_DATE,
			MI.MEMBER_ID					AS MEMBER_ID,
			MI.MEMBER_NAME					AS MEMBER_NAME,
			MI.PW_DATE						AS PW_DATE,
			?								AS PREV_MEMBER_PW,
			MI.MEMBER_PW					AS MEMBER_PW,
			?								AS PREV_TEL_MOBILE,
			MI.TEL_MOBILE					AS TEL_MOBILE,
			MI.RECEIVE_TEL_FLG				AS RECEIVE_TEL_FLG,
			MI.RECEIVE_SMS_FLG				AS RECEIVE_SMS_FLG,
			MI.RECEIVE_EMAIL_FLG			AS RECEIVE_EMAIL_FLG,
			MI.RECEIVE_PUSH_FLG				AS RECEIVE_PUSH_FLG,
			MI.SUSPICION_FLG				AS SUSPICION_FLG,
			NOW()							AS UPDATE_DATE,
			MI.MEMBER_ID					AS UPDATER
		FROM
			MEMBER_".$member['country']." MI
		WHERE
			MI.IDX = ?
	";

	$param = [$member['member_status']];
	$param[] = $member['member_pw'];
	$param[] = $member['tel_mobile'];
	$param[] = $member['member_idx'];
	$db->query($insert_member_update_log_sql,$param);
}

function getMsgToMsgCode($db, $country, $msg_code, $mapping_arr){
	$msg_info = $db->get("MSG_MST", "MSG_CODE = ?", array($msg_code))[0];
	$msg_text = "";
	
	if(isset($msg_info['MSG_TEXT_'.$country])){
		$msg_text = $msg_info['MSG_TEXT_'.$country];
	}
	
	foreach($mapping_arr as $mapping_info){
		$msg_text = str_replace($mapping_info['key'], $mapping_info['value'], $msg_text);
	}
	
	return $msg_text;
}

function setSizeType($option_name) {
	$size_type = null;
	
	$tmp_len = strlen($option_name);
	if ($tmp_len == 2) {
		$size_type = "N";
	} else if ($tmp_len == 3) {
		$size_type = "O";
	} else {
		$size_type = "S";
	}
	
	return $size_type;
}

function calcStockQty($sold_out_qty,$stock_standby,$limit_qty) {
	$stock_status = null;
	
	if ($limit_qty > 0) {
		if ($limit_qty >= $sold_out_qty) {
			$stock_status = "STIN";	//재고 있음 (Stock in)
		} else {
			$stock_status = "STCL";	//품절 임박 (Stock sold out close)
		}
	} else {
		if ($stock_standby > 0) {
			$stock_status = "STSC";	//재고 없음(그레이아웃)	→ 재고 증가 예정 (Stock in schedule)
		} else {
			$stock_status = "STSO";	//재고 없음(사선)		→ 증가 예정 재고 없음 (Stock sold out)
		}
	}
	
	return $stock_status;
}

function xssEncode($param){
	$result = "";
	
	if ($param != null && strlen($param) > 0) {
		$param = str_replace("&","&amp;",$param);
		$param = str_replace("\"","&quot;",$param);
		$param = str_replace("'","&apos;",$param);
		$param = str_replace("<","&lt;",$param);
		$param = str_replace(">","&gt;",$param);
		$param = str_replace("\r","<br>",$param);
		$param = str_replace("\n","<p>",$param);
		
		$result = $param;
	}

	return $result;
}

function xssDecode($param) { 
	$result = "";
	
	if ($param != null && strlen($param) > 0) {
		$param = str_replace("&nbsp;"," ",$param);
		$param = str_replace("&amp;","&",$param);
		$param = str_replace("&quot;","\"",$param);
		$param = str_replace("&apos;","'",$param);
		$param = str_replace("&lt;","<",$param);
		$param = str_replace("&gt;",">",$param);
		$param = str_replace("<br>","\r",$param);
		$param = str_replace("<p>","\n",$param);
		
		$result = $param;
	}
	
	return $result;
}

?>