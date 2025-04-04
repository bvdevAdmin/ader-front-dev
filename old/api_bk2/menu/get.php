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

include_once("/var/www/www/api/common.php");

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_POST['country'])){
	$country = $_POST['country'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$member_name = null;
if (isset($_SESSION['MEMBER_NAME'])) {
	$member_name = $_SESSION['MEMBER_NAME'];
}

$menu_type = null;
if (isset($_POST['menu_type'])) {
	$menu_type = $_POST['menu_type'];
}

$menu_idx = 0;
if (isset($_POST['menu_idx'])) {
	$menu_idx = $_POST['menu_idx'];
}

$login_flg = false;
$menu_info = array();
$posting_story = array();

if ($country != null) {
	if ($member_idx > 0) {
		$login_flg = true;
		
		$member_info = array();
		$member_info = getMemberInfo($db,$country,$member_idx);
		
		$json_result['member_info'] = $member_info;
	}

	$menu_info = getMenuCategory($db,$country,$menu_type,$menu_idx);
	$posting_story = getPostingStory($db,$country);
}

$json_result['data'] = array(
	'menu_info'			=>$menu_info,
	'posting_story'		=>$posting_story
);

function getMemberInfo($db,$country,$member_idx) {
	$whish_cnt = $db->count("WHISH_LIST","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND DEL_FLG = FALSE");
	$basket_cnt = $db->count("BASKET_INFO","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND PARENT_IDX = 0 AND DEL_FLG = FALSE ");
	$order_cnt = $db->count("ORDER_INFO","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND ORDER_STATUS NOT REGEXP 'OC|OE|OR|DCP'");
	
	$select_member_sql = "
		SELECT
			MB.MEMBER_ID		AS MEMBER_ID,
			MB.MEMBER_NAME		AS MEMBER_NAME,
			(
				SELECT 
					S_MI.MILEAGE_BALANCE
				FROM 
					MILEAGE_INFO S_MI
				WHERE
					S_MI.COUNTRY = '".$country."' AND
					S_MI.MEMBER_IDX = MB.IDX
				ORDER BY 
					S_MI.IDX DESC 
				LIMIT
					0,1
			)					AS MEMBER_MILEAGE,
			(
				SELECT
					COUNT(S_VI.IDX)
				FROM 
					VOUCHER_ISSUE S_VI
				WHERE
					S_VI.COUNTRY = '".$country."' AND
					S_VI.MEMBER_IDX = MB.IDX AND
					S_VI.DEL_FLG = FALSE AND
					S_VI.VOUCHER_ADD_DATE IS NOT NULL AND
					S_VI.USED_FLG = FALSE AND
					S_VI.USABLE_END_DATE > NOW()
			)					AS MEMBER_VOUCHER
		FROM
			MEMBER_".$country." MB
		WHERE
			MB.IDX = ".$member_idx."
	";
	
	$db->query($select_member_sql);
	
	$member_info = array();
	foreach($db->fetch() as $member_data) {
		$member_info = array(
			'member_id'			=>$member_data['MEMBER_ID'],
			'member_name'		=>$member_data['MEMBER_NAME'],
			'member_mileage'	=>number_format($member_data['MEMBER_MILEAGE']),
			'member_voucher'	=>$member_data['MEMBER_VOUCHER'],
			'whish_cnt'			=>$whish_cnt,
			'basket_cnt'		=>$basket_cnt,
			'order_cnt'			=>$order_cnt
		);
	}
	
	return $member_info;
}

function getMenuCategory($db,$country,$menu_type,$menu_idx) {
	$parent_info = array();
	if ($menu_type != null && $menu_idx > 0) {
		$parent_info = getMenuParentInfo($db,$menu_type,$menu_idx);
	}
	
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
			MS.COUNTRY = '".$country."'
	";
		
	$db->query($select_menu_segment_sql);
	
	$menu_info = array();
	foreach($db->fetch() as $segment_data) {
		$segment_idx = $segment_data['SEGMENT_IDX'];
		$segment_param = "&menu_type=SEG&menu_idx=".$segment_idx;
		
		$parent_flg = false;
		if ($menu_type != null && $menu_idx > 0) {
			if ($segment_idx == $parent_info['parent_idx']) {
				$parent_flg = true;
			}
		}
		
		$segment_link = null;
		if (strlen($segment_data['MENU_LINK']) > 0) {
			if ($segment_data['EXT_LINK_FLG'] == true) {
				$segment_link = "http://".$segment_data['MENU_LINK'];
			} else if ($segment_data['EXT_LINK_FLG'] == false) {
				$segment_link = $segment_data['MENU_LINK'].$segment_param;
			}
		} else {
			$segment_link = $segment_data['MENU_LINK'];
		}
		
		$menu_slide = array();
		if (!empty($segment_idx)) {
			$slide_cnt = $db->count("MENU_SEG_SLIDE","COUNTRY = '".$country."' AND PARENT_IDX = ".$segment_idx);
			if ($slide_cnt > 0) {
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
						MS.COUNTRY = '".$country."' AND
						MS.PARENT_IDX = ".$segment_idx."
					ORDER BY
						MS.DISPLAY_NUM ASC
				";
				
				$db->query($select_menu_slide_sql);
				
				foreach($db->fetch() as $slide_data) {
					$slide_link = null;
					if (strlen($slide_data['SLIDE_LINK']) > 0) {
						if ($slide_data['EXT_LINK_FLG'] == true) {
							$slide_link = "http://".$slide_data['SLIDE_LINK'];
						} else if ($slide_data['EXT_LINK_FLG'] == false) {
							$slide_link = $slide_data['SLIDE_LINK'];
						}
					} else {
						$slide_link = $slide_data['SLIDE_LINK'];
					}
					
					$menu_slide[] = array(
						'slide_idx'			=>$slide_data['SLIDE_IDX'],
						'slide_title'		=>$slide_data['SLIDE_TITLE'],
						'img_location'		=>$slide_data['IMG_LOCATION'],
						'slide_link'		=>$slide_link
					);
				}
			}
			
			$menu_hl1 = array();
			$hl1_cnt = $db->count("MENU_HL_1","COUNTRY = '".$country."' AND PARENT_IDX = ".$segment_idx);
			if ($hl1_cnt > 0) {
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
						HL1.COUNTRY = '".$country."' AND
						HL1.PARENT_IDX = ".$segment_idx." AND
						HL1.A0_EXP_FLG = TRUE
					ORDER BY
						HL1.DISPLAY_NUM ASC
				";
				
				$db->query($select_menu_hl1_sql);
				
				foreach($db->fetch() as $hl1_data) {
					$hl1_idx = $hl1_data['HL1_IDX'];
					$hl1_param = "&menu_type=HL1&menu_idx=".$hl1_idx;
					
					$hl1_link = null;
					if (strlen($hl1_data['MENU_LINK']) > 0) {
						if ($hl1_data['EXT_LINK_FLG'] == true) {
							$hl1_link = "http://".$hl1_data['MENU_LINK'];
						} else if ($hl1_data['EXT_LINK_FLG'] == false) {
							$hl1_link = $hl1_data['MENU_LINK'].$hl1_param;
						}
					} else {
						$hl1_link = $hl1_data['MENU_LINK'];
					}
					
					$menu_hl2 = array();
					if (!empty($hl1_idx)) {
						$hl2_cnt = $db->count("MENU_HL_2","COUNTRY = '".$country."' AND PARENT_IDX = ".$hl1_idx);
						
						if ($hl2_cnt > 0) {
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
									HL2.PARENT_IDX = ".$hl1_idx." AND
									HL2.COUNTRY = '".$country."' AND
									HL2.A0_EXP_FLG = TRUE
								ORDER BY
									HL2.DISPLAY_NUM ASC
							";
							
							$db->query($select_menu_hl2_sql);
							
							foreach($db->fetch() as $hl2_data) {
								$hl2_link = null;
								$hl2_param = "&menu_type=HL2&menu_idx=".$hl2_data['HL2_IDX'];
								
								if (strlen($hl2_data['MENU_LINK']) > 0) {
									if ($hl2_data['EXT_LINK_FLG'] == true) {
										$hl2_link = "http://".$hl2_data['MENU_LINK'];
									} else if ($hl2_data['EXT_LINK_FLG'] == false) {
										$hl2_link = $hl2_data['MENU_LINK'].$hl2_param;
									}
								} else {
									$hl2_link = $hl2_data['MENU_LINK'];
								}
								
								$menu_hl2[] = array(
									'menu_idx'			=>$hl2_data['HL2_IDX'],
									'menu_title'		=>$hl2_data['MENU_TITLE'],
									'img_location'		=>$hl2_data['IMG_LOCATION'],
									'menu_link'			=>$hl2_link
								);
							}
						}
					}
					
					$menu_hl1[] = array(
						'menu_idx'		=>$hl1_idx,
						'menu_title'	=>$hl1_data['MENU_TITLE'],
						'img_location'	=>$hl1_data['IMG_LOCATION'],
						'menu_link'		=>$hl1_link,
						
						'menu_hl2'		=>$menu_hl2
					);
				}
			}
		}
		
		$menu_info[] = array(
			'menu_idx'			=>$segment_idx,
			'menu_title'		=>$segment_data['MENU_TITLE'],
			'menu_link'			=>$segment_link,
			'parent_flg'		=>$parent_flg,
			
			'menu_slide'		=>$menu_slide,
			'menu_hl1'			=>$menu_hl1
		);
	}
	
	return $menu_info;
}

function getPostingStory($db,$country) {
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
				),
				''
			)					AS PAGE_URL
		FROM
			POSTING_STORY PS
			LEFT JOIN PAGE_POSTING PP ON
			PS.PAGE_IDX = PP.IDX
		WHERE
			PS.COUNTRY = '".$country."' AND
			PS.DEL_FLG = FALSE
		ORDER BY
			PS.STORY_TYPE,
			PS.DISPLAY_NUM
			ASC
	";
	
	$db->query($select_posting_story_sql);
	
	$column_NEW = array();
	$column_COLC = array();
	$column_RNWY = array();
	$column_EDTL = array();
	
	$posting_story = array();
	foreach($db->fetch() as $story_data) {
		$story_type = $story_data['STORY_TYPE'];
		
		switch ($story_type) {
			case "NEW" :
				$column_NEW[] = array(
					'story_type'		=>$story_data['STORY_TYPE'],
					'img_location'		=>$story_data['IMG_LOCATION'],
					'story_title'		=>$story_data['STORY_TITLE'],
					'story_sub_title'	=>$story_data['STORY_SUB_TITLE'],
					'page_url'			=>$story_data['PAGE_URL']
				);
				
				break;
			
			case "COLC" :
				$column_COLC[] = array(
					'story_type'		=>$story_data['STORY_TYPE'],
					'img_location'		=>$story_data['IMG_LOCATION'],
					'story_title'		=>$story_data['STORY_TITLE'],
					'story_sub_title'	=>$story_data['STORY_SUB_TITLE'],
					'page_url'			=>"/posting/collection?project_idx=".$story_data['PAGE_IDX']
				);
				
				break;
			
			case "RNWY" :
				$column_RNWY[] = array(
					'story_type'		=>$story_data['STORY_TYPE'],
					'img_location'		=>$story_data['IMG_LOCATION'],
					'story_title'		=>$story_data['STORY_TITLE'],
					'story_sub_title'	=>$story_data['STORY_SUB_TITLE'],
					'page_url'			=>$story_data['PAGE_URL']
				);
				
				break;
			
			case "EDTL" :
				$column_EDTL[] = array(
					'story_type'		=>$story_data['STORY_TYPE'],
					'img_location'		=>$story_data['IMG_LOCATION'],
					'story_title'		=>$story_data['STORY_TITLE'],
					'story_sub_title'	=>$story_data['STORY_SUB_TITLE'],
					'page_url'			=>$story_data['PAGE_URL']
				);
				
				break;
		}
	}
	
	$posting_story = array(
		'column_NEW'		=>$column_NEW,
		'column_COLC'		=>$column_COLC,
		'column_RNWY'		=>$column_RNWY,
		'column_EDTL'		=>$column_EDTL
	);
	
	return $posting_story;
}

?>