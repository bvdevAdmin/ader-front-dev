<?php
/*
 +=============================================================================
 | 
 | 공통 - 메뉴 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.11.03
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

error_reporting(E_ALL^ E_WARNING); 

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_POST['country'])){
	$country = $_POST['country'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$member_name = null;
if (isset($_SESSION['MEMBER_NAME'])) {
	$member_name = $_SESSION['MEMBER_NAME'];
}

$login_flg = false;

if (isset($country)) {
	/* 메뉴 카테고리 - SEG,HL1,HL2 */
	$menu_category = array();
	
	/* 메뉴 스토리 */
	$posting_story = array();
	
	if ($member_idx > 0) {
		$login_flg = true;
		
		$member_info = getMENU_member($db,$country,$member_idx);
		
		$json_result['member_info'] = $member_info;
	}
	
	/* 메뉴 카테고리 조회처리 */
	$menu_category	= getMENU_category($db,$country,$menu_type,$menu_idx);
	
	/* 메뉴 스토리 조회처리 */
	$posting_story	= getMENU_story($db,$country);
	
	$json_result['data'] = array(
		'menu_info'			=>$menu_category,
		'posting_story'		=>$posting_story
	);
}

/* 메뉴 로그인 회원정보 조회처리 */
function getMENU_member($db,$country,$member_idx) {
	$member_info = array();
	
	$cnt_wish	= $db->count("WHISH_LIST","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND DEL_FLG = FALSE");
	$cnt_basket	= $db->count(
		"
			BASKET_INFO BI
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
		",
		"
			BI.COUNTRY = '".$country."' AND
			BI.MEMBER_IDX = ".$member_idx." AND
			BI.PARENT_IDX = 0 AND
			BI.DEL_FLG = FALSE AND
			
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
		"
	);
	$cnt_order	= $db->count("ORDER_INFO","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND ORDER_STATUS NOT REGEXP 'OC|OE|OR|DCP'");
	
	$select_member_sql = "
		SELECT
			MB.MEMBER_ID			AS MEMBER_ID,
			MB.MEMBER_NAME			AS MEMBER_NAME,
			IFNULL(
				J_MI.MILEAGE_BALANCE,0
			)						AS MEMBER_MILEAGE,
			IFNULL(
				J_MV.CNT_ISSUE,0
			)						AS MEMBER_VOUCHER
		FROM
			MEMBER_".$country." MB
			
			LEFT JOIN (
				SELECT
					S_MI.MEMBER_IDX		AS MEMBER_IDX,
					MILEAGE_BALANCE		AS MILEAGE_BALANCE
				FROM
					MILEAGE_INFO S_MI
				WHERE
					S_MI.COUNTRY = ?
				GROUP BY
					S_MI.MEMBER_IDX
				ORDER BY
					S_MI.IDX DESC

			) J_MI ON
			MB.IDX = J_MI.MEMBER_IDX
			
			LEFT JOIN (
				SELECT
					S_VI.MEMBER_IDX		AS MEMBER_IDX,
					COUNT(S_VI.IDX)		AS CNT_ISSUE
				FROM 
					VOUCHER_ISSUE S_VI
				WHERE
					S_VI.COUNTRY = ? AND
					S_VI.DEL_FLG = FALSE AND
					S_VI.VOUCHER_ADD_DATE IS NOT NULL AND
					S_VI.USED_FLG = FALSE AND
					S_VI.USABLE_END_DATE > NOW()
				GROUP BY
					S_VI.MEMBER_IDX
			) J_MV ON
			MB.IDX = J_MV.MEMBER_IDX
		WHERE
			MB.IDX = ?
	";
	
	$db->query($select_member_sql,array($country,$country,$member_idx));
	
	foreach($db->fetch() as $data) {
		$member_info = array(
			'member_id'			=>$data['MEMBER_ID'],
			'member_name'		=>$data['MEMBER_NAME'],
			'member_mileage'	=>number_format($data['MEMBER_MILEAGE']),
			'member_voucher'	=>$data['MEMBER_VOUCHER'],
			
			'whish_cnt'			=>$cnt_wish,
			'basket_cnt'		=>$cnt_basket,
			'order_cnt'			=>$cnt_order
		);
	}
	
	return $member_info;
}

/* 메뉴 카테고리 조회처리 */
function getMENU_category($db,$country,$menu_type,$menu_idx) {
	$menu_category = array();
	
	/* 부모 메뉴 정보 */
	$parent_info = array();
	if ($menu_type != null && $menu_idx > 0) {
		$parent_info = getMenuParentInfo($db,$menu_type,$menu_idx);
	}
	
	/* 1. 메뉴 SEG (최상위) 조회처리 */
	$select_menu_segment_sql = "
		SELECT
			MS.IDX				AS SEGMENT_IDX,
			MS.MENU_TITLE		AS MENU_TITLE,
			
			MS.EXT_LINK_FLG		AS EXT_LINK_FLG,
			IFNULL(
				MS.MENU_LINK,''
			)					AS MENU_LINK
		FROM
			MENU_SEGMENT MS
		WHERE
			MS.COUNTRY = ?
	";
		
	$db->query($select_menu_segment_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$seg_idx = $data['SEGMENT_IDX'];
		$param_segment = "&menu_type=SEG&menu_idx=".$seg_idx;
		
		$parent_flg = false;
		if ($menu_type != null && $menu_idx > 0) {
			if ($seg_idx == $parent_info['parent_idx']) {
				$parent_flg = true;
			}
		}
		
		$segment_link = null;
		if (strlen($data['MENU_LINK']) > 0) {
			if ($data['EXT_LINK_FLG'] == true) {
				$segment_link = "http://".$data['MENU_LINK'];
			} else if ($data['EXT_LINK_FLG'] == false) {
				$segment_link = $data['MENU_LINK'].$param_segment;
			}
		} else {
			$segment_link = $data['MENU_LINK'];
		}
		
		$menu_slide	= array();
		$menu_hl1	= array();
		
		if (!empty($seg_idx)) {
			$cnt_slide = $db->count("MENU_SEG_SLIDE","COUNTRY = '".$country."' AND PARENT_IDX = ".$seg_idx);
			if ($cnt_slide > 0) {
				$menu_slide = getMENU_slide($db,$country,$seg_idx);
			}
			
			$cnt_hl1 = $db->count("MENU_HL_1","COUNTRY = '".$country."' AND PARENT_IDX = ".$seg_idx);
			if ($cnt_hl1 > 0) {
				$menu_hl1 = getMENU_HL1($db,$country,$seg_idx);
			}
		}
		
		$menu_category[] = array(
			'menu_idx'			=>$seg_idx,
			'menu_title'		=>$data['MENU_TITLE'],
			'menu_link'			=>$segment_link,
			'parent_flg'		=>$parent_flg,
			
			'menu_slide'		=>$menu_slide,
			'menu_hl1'			=>$menu_hl1
		);
	}
	
	return $menu_category;
}

function getMENU_slide($db,$country,$param_idx) {
	$menu_slide = null;
	
	$select_menu_slide_sql ="
		SELECT
			MS.IDX				AS SLIDE_IDX,
			MS.SLIDE_TITLE		AS SLIDE_TITLE,
			MS.IMG_LOCATION		AS IMG_LOCATION,
			
			MS.EXT_LINK_FLG		AS EXT_LINK_FLG,
			IFNULL(
				MS.SLIDE_LINK,''
			)					AS SLIDE_LINK
		FROM
			MENU_SEG_SLIDE MS
		WHERE
			MS.COUNTRY = ? AND
			MS.PARENT_IDX = ?
		ORDER BY
			MS.DISPLAY_NUM ASC
	";
	
	$db->query($select_menu_slide_sql,array($country,$param_idx));
	
	foreach($db->fetch() as $data) {
		$slide_link = $data['SLIDE_LINK'];
		$ext_link_flg = $data['EXT_LINK_FLG'];
		
		if ($slide_link != null && strlen($slide_link) > 0) {
			if ($ext_link_flg == true) {
				$slide_link = "http://".$slide_link;
			}
		}
		
		$menu_slide[] = array(
			'slide_idx'		=>$data['SLIDE_IDX'],
			'slide_title'	=>$data['SLIDE_TITLE'],
			'img_location'	=>$data['IMG_LOCATION'],
			'slide_link'	=>$slide_link
		);
	}
	
	return $menu_slide;
}

function getMENU_HL1($db,$country,$param_idx) {
	$menu_hl1 = array();
	
	$select_menu_hl1_sql = "
		SELECT
			HL1.IDX				AS HL1_IDX,
			HL1.MENU_TITLE		AS MENU_TITLE,
			HL1.IMG_LOCATION	AS IMG_LOCATION,
			
			HL1.EXT_LINK_FLG	AS EXT_LINK_FLG,
			IFNULL(
				HL1.MENU_LINK,''
			)					AS MENU_LINK
		FROM
			MENU_HL_1 HL1
		WHERE
			HL1.COUNTRY = ? AND
			HL1.PARENT_IDX = ? AND
			HL1.A0_EXP_FLG = TRUE
		ORDER BY
			HL1.DISPLAY_NUM ASC
	";
	
	$db->query($select_menu_hl1_sql,array($country,$param_idx));
	
	foreach($db->fetch() as $data) {
		$hl1_idx	= $data['HL1_IDX'];
		
		$param_hl1	= "&menu_type=HL1&menu_idx=".$hl1_idx;
		
		$hl1_link	= $data['MENU_LINK'];
		if (strlen($hl1_link) > 0) {
			if ($data['EXT_LINK_FLG'] == true) {
				$hl1_link = "http://".$data['MENU_LINK'];
			} else if ($data['EXT_LINK_FLG'] == false) {
				$hl1_link = $hl1_link.$param_hl1;
			}
		}
		
		$menu_hl2 = array();
		if (!empty($hl1_idx)) {
			$hl2_cnt = $db->count("MENU_HL_2","COUNTRY = '".$country."' AND PARENT_IDX = ".$hl1_idx);
			
			if ($hl2_cnt > 0) {
				$menu_hl2 = getMENU_HL2($db,$country,$hl1_idx);
			}
		}
		
		$menu_hl1[] = array(
			'menu_idx'		=>$hl1_idx,
			'menu_title'	=>$data['MENU_TITLE'],
			'img_location'	=>$data['IMG_LOCATION'],
			'menu_link'		=>$hl1_link,
			
			'menu_hl2'		=>$menu_hl2
		);
	}
	
	return $menu_hl1;
}

function getMENU_HL2($db,$country,$param_idx) {
	$menu_hl2 = array();
	
	$select_menu_hl2_sql = "
		SELECT
			HL2.IDX				AS HL2_IDX,
			HL2.MENU_TITLE		AS MENU_TITLE,
			HL2.IMG_LOCATION	AS IMG_LOCATION,
			
			HL2.EXT_LINK_FLG	AS EXT_LINK_FLG,
			IFNULL(
				HL2.MENU_LINK,''
			)					AS MENU_LINK
		FROM
			MENU_HL_2 HL2
		WHERE
			HL2.COUNTRY = ? AND
			HL2.PARENT_IDX = ? AND
			HL2.A0_EXP_FLG = TRUE
		ORDER BY
			HL2.DISPLAY_NUM ASC
	";
	
	$db->query($select_menu_hl2_sql,array($country,$param_idx));
	
	foreach($db->fetch() as $data) {
		$param_hl2	= "&menu_type=HL2&menu_idx=".$data['HL2_IDX'];
		
		$hl2_link	= $data['MENU_LINK'];
		if (strlen($hl2_link) > 0) {
			if ($data['EXT_LINK_FLG'] == true) {
				$hl2_link = "http://".$hl2_link;
			} else if ($data['EXT_LINK_FLG'] == false) {
				$hl2_link = $hl2_link.$param_hl2;
			}
		}
		
		$menu_hl2[] = array(
			'menu_idx'			=>$data['HL2_IDX'],
			'menu_title'		=>$data['MENU_TITLE'],
			'img_location'		=>$data['IMG_LOCATION'],
			'menu_link'			=>$hl2_link
		);
	}
	
	return $menu_hl2;
}

/* 메뉴 스토리 조회처리 */
function getMENU_story($db,$country) {
	$posting_story = array();
	
	$column_NEW	= array();
	$column_COLC = array();
	$column_RNWY = array();
	$column_EDTL = array();
	
	$select_posting_story_sql = "
		SELECT
			PS.STORY_TYPE		AS STORY_TYPE,
			PS.PAGE_IDX			AS PAGE_IDX,
			PS.IMG_LOCATION		AS IMG_LOCATION,
			PS.STORY_TITLE		AS STORY_TITLE,
			PS.STORY_SUB_TITLE	AS STORY_SUB_TITLE,
			IFNULL(
				CONCAT(
					PP.PAGE_URL,PP.IDX
				),''
			)					AS PAGE_URL
		FROM
			POSTING_STORY PS
			LEFT JOIN PAGE_POSTING PP ON
			PS.PAGE_IDX = PP.IDX
		WHERE
			PS.COUNTRY = ? AND
			PS.DEL_FLG = FALSE
		ORDER BY
			PS.STORY_TYPE,
			PS.DISPLAY_NUM
			ASC
	";
	
	$db->query($select_posting_story_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$story_type = $data['STORY_TYPE'];
		
		switch ($story_type) {
			case "NEW" :
				$column_NEW[] = array(
					'story_type'		=>$data['STORY_TYPE'],
					'img_location'		=>$data['IMG_LOCATION'],
					'story_title'		=>$data['STORY_TITLE'],
					'story_sub_title'	=>$data['STORY_SUB_TITLE'],
					'page_url'			=>$data['PAGE_URL']
				);
				
				break;
			
			case "COLC" :
				$column_COLC[] = array(
					'story_type'		=>$data['STORY_TYPE'],
					'img_location'		=>$data['IMG_LOCATION'],
					'story_title'		=>$data['STORY_TITLE'],
					'story_sub_title'	=>$data['STORY_SUB_TITLE'],
					'page_url'			=>"/posting/collection?project_idx=".$data['PAGE_IDX']
				);
				
				break;
			
			case "RNWY" :
				$column_RNWY[] = array(
					'story_type'		=>$data['STORY_TYPE'],
					'img_location'		=>$data['IMG_LOCATION'],
					'story_title'		=>$data['STORY_TITLE'],
					'story_sub_title'	=>$data['STORY_SUB_TITLE'],
					'page_url'			=>$data['PAGE_URL']
				);
				
				break;
			
			case "EDTL" :
				$column_EDTL[] = array(
					'story_type'		=>$data['STORY_TYPE'],
					'img_location'		=>$data['IMG_LOCATION'],
					'story_title'		=>$data['STORY_TITLE'],
					'story_sub_title'	=>$data['STORY_SUB_TITLE'],
					'page_url'			=>$data['PAGE_URL']
				);
				
				break;
		}
	}
	
	/* 아카이브 이미지 조회처리 */
	$archive_img = getArchiveImg($db,$country);
	
	$posting_story = array(
		'archive_img'		=>$archive_img,
		
		'column_NEW'		=>$column_NEW,
		'column_COLC'		=>$column_COLC,
		'column_RNWY'		=>$column_RNWY,
		'column_EDTL'		=>$column_EDTL
	);
	
	return $posting_story;
}

/* 아카이브 이미지 조회처리 */
function getArchiveImg($db,$country) {
	$archive_img = array();
	
	$select_archive_img_sql = "
		SELECT
			AI.ARCHIVE_TYPE		AS ARCHIVE_TYPE,
			AI.IMG_LOCATION		AS IMG_LOCATION
		FROM
			ARCHIVE_IMG AI
		WHERE
			AI.COUNTRY = ?
	";
	
	$db->query($select_archive_img_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$archive_img[] = array(
			'archive_type'		=>$data['ARCHIVE_TYPE'],
			'img_location'		=>$data['IMG_LOCATION']
		);
	}
	
	return $archive_img;
}

?>