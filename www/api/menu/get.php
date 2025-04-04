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

error_reporting(E_ALL^ E_WARNING); 

if (isset($_SERVER['HTTP_COUNTRY'])) {
	$login_flg = false;
	if (isset($_SESSION['MEMBER_IDX'])) {
		$json_result['member_info'] = getHeader_member($db,$_SERVER['HTTP_COUNTRY']);
		$login_flg = true;
	}

	$header_mst = getHeader_mst($db,$_SERVER['HTTP_COUNTRY']);

	$build_header	= array();

	$landing_header		= array();
	$landing_story		= array();
	$landing_archive	= array();

	if (isset($header_mst['mst_idx'])) {
		$h_mst_idx = $header_mst['mst_idx'];
		if (isset($h_mst_idx)) {
			$landing_header = getLanding_header($db,$h_mst_idx);
		}
	}
	
	if (sizeof($landing_header) > 0) {
		$build_header = buildHeader($landing_header,1);
	}

	$story_mst = getStory_mst($db,$_SERVER['HTTP_COUNTRY']);
	if (sizeof($story_mst) > 0) {
		$s_mst_idx = $story_mst['mst_idx'];

		if (isset($s_mst_idx)) {
			$landing_story		= getLanding_story($db,$s_mst_idx);
			$landing_archive	= getLanding_archive($db,$s_mst_idx);
		}
	}	
	
	$json_result['data'] = array(
		'landing_header'	=>$build_header,
		
		'story_title'		=>$story_mst['mst_title'],
		'landing_story'		=>$landing_story,
		'landing_archive'	=>$landing_archive
	);
}

function getHeader_mst($db,$country) {
	$header_mst = array();

	$select_header_mst_sql = "
		SELECT
			HM.IDX		AS MST_IDX
		FROM
			L_HEADER_MST HM
		WHERE
			HM.COUNTRY		= ? AND
			HM.DISPLAY_FLG	= TRUE AND
			(
				HM.ALWAYS_FLG = TRUE OR
				NOW() BETWEEN HM.DISPLAY_START_DATE AND HM.DISPLAY_END_DATE
			) AND
			HM.DEL_FLG = FALSE
		ORDER BY
			HM.DISPLAY_NUM ASC
		LIMIT
			0,1
	";

	$db->query($select_header_mst_sql,array($country));

	foreach($db->fetch() as $data) {
		$header_mst = array(
			'mst_idx'		=>$data['MST_IDX']
		);
	}

	return $header_mst;
}

function getLanding_header($db,$mst_idx) {
	$landing_header = array();

	$select_landing_header_sql = "
		SELECT
			LH.IDX					AS HEADER_IDX,
			LH.DISPLAY_NUM			AS DISPLAY_NUM,
			LH.DEPTH				AS DEPTH,
			LH.PARENT_IDX			AS PARENT_IDX,

			LH.A0_FLG				AS A0_FLG,
			LH.A1_FLG				AS A1_FLG,
			LH.EXT_FLG				AS EXT_FLG,
	
			LH.HEADER_TITLE			AS HEADER_TITLE,
			LH.HEADER_LINK			AS HEADER_LINK
		FROM
			LANDING_HEADER LH
		WHERE
			LH.MST_IDX	= ? AND
			LH.A0_FLG	= TRUE AND
			LH.DEPTH BETWEEN 1 AND 3 AND
			LH.DEL_FLG	= FALSE
		ORDER BY
			LH.DEPTH,LH.DISPLAY_NUM
	";

	$db->query($select_landing_header_sql,array($mst_idx));

	foreach($db->fetch() as $data) {
		$header_link = $data['HEADER_LINK'];
		if ($data['EXT_FLG'] == true) {
			$header_link = "http://".$header_link;
		} else {
			$header_link = $header_link."&depth=".$data['DEPTH']."&header_idx=".$data['HEADER_IDX'];
		}

		$parent_idx = 1;
		if ($data['DEPTH'] > 1) {
			$parent_idx = $data['PARENT_IDX'];
		}

		$landing_header[] = array(
			'header_idx'		=>$data['HEADER_IDX'],
			'display_num'		=>$data['DISPLAY_NUM'],
			'depth'				=>$data['DEPTH'],
			'parent_idx'		=>$parent_idx,

			'a0_flg'			=>$data['A0_FLG'],
			'a1_flg'			=>$data['A1_FLG'],
			'ext_flg'			=>$data['EXT_FLG'],

			'header_title'		=>$data['HEADER_TITLE'],
			'header_link'		=>$header_link
		);
	}

	return $landing_header;
}

function buildHeader($landing_header,$parent_idx = 1) {
    $build_header = [];

    foreach ($landing_header as $header) {
        if ($header['parent_idx'] == $parent_idx) {
            $children = buildHeader($landing_header,$header['header_idx']);
            if ($children) {
                $header['children'] = $children;
            }

            $build_header[] = $header;
        }
    }
    return $build_header;
}

function getStory_mst($db,$country) {
	$story_mst = array();

	$select_story_mst_sql = "
		SELECT
			SM.IDX			AS MST_IDX,
			SM.MST_TITLE	AS MST_TITLE
		FROM
			L_STORY_MST SM
		WHERE
			SM.COUNTRY		= ? AND
			SM.DISPLAY_FLG	= TRUE AND
			(
				SM.ALWAYS_FLG = TRUE OR
				NOW() BETWEEN SM.DISPLAY_START_DATE AND SM.DISPLAY_END_DATE
			) AND
			SM.DEL_FLG = FALSE
		ORDER BY
			SM.DISPLAY_NUM ASC
		LIMIT
			0,1
	";
	
	$db->query($select_story_mst_sql,array($country));

	foreach($db->fetch() as $data) {
		$story_mst = array(
			'mst_idx'		=>$data['MST_IDX'],
			'mst_title'		=>$data['MST_TITLE']
		);
	}

	return $story_mst;
}

function getLanding_story($db,$mst_idx) {
	$landing_story = array();

	$select_landing_story_sql = "
		SELECT
			LS.IDX					AS STORY_IDX,
			LS.DISPLAY_NUM			AS DISPLAY_NUM,
			LS.STORY_TITLE			AS STORY_TITLE,
			LS.STORY_SUB_TITLE		AS STORY_SUB_TITLE,
			LS.STORY_MEMO			AS STORY_MEMO,
			LS.EXT_FLG				AS EXT_FLG,
			LS.STORY_LINK			AS STORY_LINK,
			LS.IMG_LOCATION			AS IMG_LOCATION
		FROM
			LANDING_STORY LS
		WHERE
			LS.MST_IDX = ? AND
			LS.DEL_FLG = FALSE
		ORDER BY
			LS.DISPLAY_NUM ASC
	";

	$db->query($select_landing_story_sql,array($mst_idx));

	foreach($db->fetch() as $data) {
		$landing_story[] = array(
			'story_idx'			=>$data['STORY_IDX'],
			'display_num'		=>$data['DISPLAY_NUM'],
			'story_title'		=>$data['STORY_TITLE'],
			'story_sub_title'	=>$data['STORY_SUB_TITLE'],
			'story_memo'		=>$data['STORY_MEMO'],
			'ext_flg'			=>$data['EXT_FLG'],
			'story_link'		=>$data['STORY_LINK'],
			'img_location'		=>$data['IMG_LOCATION']
		);
	}

	return $landing_story;
}

function getLanding_archive($db,$mst_idx) {
	$landing_archive = array();
	
	$select_archive_img_sql = "
		SELECT
			AI.ARCHIVE_TYPE		AS ARCHIVE_TYPE,
			AI.IMG_LOCATION		AS IMG_LOCATION
		FROM
			ARCHIVE_IMG AI
		WHERE
			AI.MST_IDX = ? AND
			AI.DEL_FLG = FALSE
		ORDER BY
			AI.ARCHIVE_TYPE
	";
	
	$db->query($select_archive_img_sql,array($mst_idx));
	
	foreach($db->fetch() as $data) {
		$archive_img[] = array(
			'archive_type'		=>$data['ARCHIVE_TYPE'],
			'img_location'		=>$data['IMG_LOCATION']
		);
	}
	
	return $archive_img;
}

function getHeader_member($db,$country) {
	$member_info = array();
	
	$cnt_wish	= $db->count("WHISH_LIST","COUNTRY = ? AND MEMBER_IDX = ? AND DEL_FLG = FALSE",array($country,$_SESSION['MEMBER_IDX']));
	$cnt_basket	= $db->count(
		"
			BASKET_INFO BI
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
		",
		"
			BI.COUNTRY		= ? AND
			BI.MEMBER_IDX	= ? AND
			BI.PARENT_IDX	= 0 AND
			BI.DEL_FLG		= FALSE AND
			
			PR.SALE_FLG		= TRUE AND
			PR.DEL_FLG		= FALSE
		",
		array($country,$_SESSION['MEMBER_IDX'])
	);
	$cnt_order	= $db->count("ORDER_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND ORDER_STATUS NOT REGEXP 'OC|OE|OR|DCP'",array($country,$_SESSION['MEMBER_IDX']));
	
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
			MEMBER MB
			
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
	
	$db->query($select_member_sql,array($country,$country,$_SESSION['MEMBER_IDX']));
	
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

?>