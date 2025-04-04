<?php
/*
 +=============================================================================
 | 
 | 랜딩페이지 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.02.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY'])) {
	$banner_layout = array();

	$banner_mst		= getBanner_mst($db);
	if ($banner_mst != null) {
		foreach($banner_mst as $mst) {
			if (isset($mst['mst_idx'])) {
				$json_result['mst'][$mst['mst_type']] = array(
					'banner_style'		=>$mst['banner_style'],
					'banner_script'		=>$mst['banner_script']
			
				);
				
				if (isset($mst['mst_idx'])) {
					$select_banner_layout_sql = "
						SELECT
							LB.IDX				AS LAYOUT_IDX,
							LB.LAYOUT_GRID		AS LAYOUT_GRID,
		
							LB.TITLE_FLG		AS TITLE_FLG,
							LB.TITLE_VRT		AS TITLE_VRT,
							LB.TITLE_HRZ		AS TITLE_HRZ,
							LB.ALIGN_TITLE		AS ALIGN_TITLE,
							IFNULL(
								LB.BANNER_TITLE,''
							)					AS BANNER_TITLE,
							LB.TITLE_COLOR		AS TITLE_COLOR,
		
							LB.SUB_TITLE_FLG	AS SUB_TITLE_FLG,
							LB.ALIGN_SUB_TITLE	AS ALIGN_SUB_TITLE,
							IFNULL(
								LB.BANNER_SUB_TITLE,''
							)					AS BANNER_SUB_TITLE,
							LB.SUB_TITLE_COLOR	AS SUB_TITLE_COLOR,
		
							LB.LINK_FLG			AS LINK_FLG,
							LB.ALIGN_LINK		AS ALIGN_LINK,
							IFNULL(
								LB.LINK_TXT,''
							)					AS LINK_TXT,
							LB.LINK_COLOR		AS LINK_COLOR,
							LB.EXT_FLG			AS EXT_FLG,
							IFNULL(
								LB.LINK_URL,''
							)					AS LINK_URL
						FROM
							LANDING_BANNER LB
		
							LEFT JOIN (
								SELECT
									S_BC.LAYOUT_IDX			AS LAYOUT_IDX,
									COUNT(S_BC.LAYOUT_IDX)	AS CNT_CONTENTS
								FROM
									BANNER_CONTENTS S_BC
								WHERE
									S_BC.DEL_FLG = FALSE
								GROUP BY
									S_BC.LAYOUT_IDX
							) AS J_BC ON
							LB.IDX = J_BC.LAYOUT_IDX
						WHERE
							LB.MST_IDX = ? AND
							LB.DEL_FLG = FALSE AND
		
							J_BC.CNT_CONTENTS IS NOT NULL
						ORDER BY
							LB.DISPLAY_NUM ASC
					";
		
					$db->query($select_banner_layout_sql,array($mst['mst_idx']));
		
					$layout_idx = array();

					$tmp_layout = array();
		
					foreach($db->fetch() as $data) {
						array_push($layout_idx,$data['LAYOUT_IDX']);
		
						$title_flg = $data['TITLE_FLG'];
						if (strlen($data['BANNER_TITLE']) == 0) {
							$title_flg = false;
						}
		
						$sub_title_flg = $data['SUB_TITLE_FLG'];
						if (strlen($data['BANNER_SUB_TITLE']) == 0) {
							$sub_title_flg = false;
						}
		
						$link_flg = $data['LINK_FLG'];
						if (strlen($data['LINK_TXT']) == 0) {
							$link_flg = false;
						}
		
						$tmp_layout[] = array(
							'layout_idx'			=>$data['LAYOUT_IDX'],
							'layout_grid'			=>$data['LAYOUT_GRID'],
		
							'title_flg'				=>$title_flg,
							'title_vrt'				=>$data['TITLE_VRT'],
							'title_hrz'				=>$data['TITLE_HRZ'],
							'align_title'			=>$data['ALIGN_TITLE'],
							'banner_title'			=>$data['BANNER_TITLE'],
							'title_color'			=>$data['TITLE_COLOR'],
		
							'sub_title_flg'			=>$sub_title_flg,
							'align_sub_title'		=>$data['ALIGN_SUB_TITLE'],
							'banner_sub_title'		=>$data['BANNER_SUB_TITLE'],
							'sub_title_color'		=>$data['SUB_TITLE_COLOR'],
		
							'link_flg'				=>$link_flg,
							'align_link'			=>$data['ALIGN_LINK'],
							'link_txt'				=>$data['LINK_TXT'],
							'link_color'			=>$data['LINK_COLOR'],
							'ext_flg'				=>$data['EXT_FLG'],
							'link_url'				=>$data['LINK_URL']
						);
					}
		
					if (sizeof($layout_idx) > 0) {
						$banner_contents = getBanner_contents($db,$layout_idx);
						if (sizeof($banner_contents) > 0) {
							foreach($tmp_layout as $key => $layout) {
								$param_idx = $layout['layout_idx'];
								if (isset($banner_contents[$param_idx])) {
									$tmp_layout[$key]['banner_contents'] = $banner_contents[$param_idx];
								}
							}
						}
					}

					$banner_layout[$mst['mst_type']] = $tmp_layout;
				}
			}
		}
	}

	if (sizeof($banner_layout) > 0) {
		if (isset($banner_layout['W']) && !isset($banner_layout['M'])) {
			$banner_layout['M'] = $banner_layout['W'];
		}
	}
	
	
	$json_result['data'] = $banner_layout;
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 접근이 감지되었습니다. 사용 언어를 선택 후 다시 시도해주세요.";
	
	echo json_encode($json_result);
	exit;
}

function getBanner_mst($db) {
	$banner_mst = null;

	$select_banner_mst_sql = "
		SELECT 
			LM.IDX				AS MST_IDX,
			LM.MST_TYPE			AS MST_TYPE,
			IFNULL(
				LM.BANNER_STYLE,''
			)					AS BANNER_STYLE,
			IFNULL(
				LM.BANNER_SCRIPT,''
			)					AS BANNER_SCRIPT
		FROM
			L_BANNER_MST LM
			JOIN (
				SELECT
					MST_TYPE,
					MIN(DISPLAY_NUM) AS DISPLAY_NUM
				FROM
					L_BANNER_MST S_LM
				WHERE
					COUNTRY = 'KR' AND
					DISPLAY_FLG = TRUE AND
					MST_TYPE IN ('M', 'W') AND
					(
						ALWAYS_FLG = TRUE OR
						NOW() BETWEEN DISPLAY_START_DATE AND DISPLAY_END_DATE
					) AND
					DEL_FLG = FALSE
				GROUP BY
					MST_TYPE
			) J_LM ON
			LM.MST_TYPE = J_LM .MST_TYPE AND
			LM.DISPLAY_NUM = J_LM.DISPLAY_NUM
		WHERE
			LM.COUNTRY = ? AND
			LM.DISPLAY_FLG = TRUE AND
			LM.MST_TYPE IN ('M', 'W') AND
			(
				LM.ALWAYS_FLG = TRUE OR
				NOW() BETWEEN LM.DISPLAY_START_DATE AND LM.DISPLAY_END_DATE
			) AND
			LM.DEL_FLG = FALSE;
	";

	$db->query($select_banner_mst_sql,array($_SERVER['HTTP_COUNTRY']));

	foreach($db->fetch() as $data) {
		$banner_mst[] = array(
			'mst_idx'			=>$data['MST_IDX'],
			'mst_type'			=>$data['MST_TYPE'],
			'banner_style'		=>$data['BANNER_STYLE'],
			'banner_script'		=>$data['BANNER_SCRIPT']
		);
	}

	return $banner_mst;
}

function getBanner_contents($db,$layout_idx) {
	$banner_contents = array();

	$select_banner_contents_sql = "
		SELECT
			BC.IDX					AS CONTENTS_IDX,
			BC.LAYOUT_IDX			AS LAYOUT_IDX,
			
			BC.CONTENTS_TYPE		AS CONTENTS_TYPE,
			BC.W_LOCATION			AS W_LOCATION,

			BC.TITLE_FLG			AS TITLE_FLG,
			BC.TITLE_VRT			AS TITLE_VRT,
			BC.TITLE_HRZ			AS TITLE_HRZ,
			BC.ALIGN_TITLE			AS ALIGN_TITLE,
			IFNULL(
				BC.CONTENTS_TITLE,''
			)						AS CONTENTS_TITLE,
			BC.TITLE_COLOR			AS TITLE_COLOR,

			BC.SUB_TITLE_FLG		AS SUB_TITLE_FLG,
			BC.ALIGN_SUB_TITLE		AS ALIGN_SUB_TITLE,
			IFNULL(
				BC.CONTENTS_SUB_TITLE,''
			)						AS CONTENTS_SUB_TITLE,
			BC.SUB_TITLE_COLOR		AS SUB_TITLE_COLOR,

			BC.LINK_FLG				AS LINK_FLG,
			BC.ALIGN_LINK			AS ALIGN_LINK,
			IFNULL(
				BC.LINK_TXT,''
			)						AS LINK_TXT,
			BC.LINK_COLOR			AS LINK_COLOR,
			BC.EXT_FLG				AS EXT_FLG,
			IFNULL(
				BC.LINK_URL,''
			)						AS LINK_URL
		FROM
			BANNER_CONTENTS BC
		WHERE
			BC.LAYOUT_IDX IN (".implode(',',array_fill(0,count($layout_idx),'?')).") AND
			BC.DEL_FLG = FALSE
		ORDER BY
			BC.DISPLAY_NUM ASC
	";

	$db->query($select_banner_contents_sql,$layout_idx);

	foreach($db->fetch() as $data) {
		$title_flg = $data['TITLE_FLG'];
		if  (strlen($data['CONTENTS_TITLE']) == 0) {
			$title_flg = false;
		}

		$sub_title_flg = $data['SUB_TITLE_FLG'];
		if (strlen($data['CONTENTS_SUB_TITLE']) == 0) {
			$sub_title_flg = false;
		}

		$link_flg = $data['LINK_FLG'];
		if (strlen($data['LINK_TXT']) == 0) {
			$link_flg = false;
		}

		$banner_contents[$data['LAYOUT_IDX']][] = array(
			'contents_idx'			=>$data['CONTENTS_IDX'],
			'layout_idx'			=>$data['LAYOUT_IDX'],
			'contents_type'			=>$data['CONTENTS_TYPE'],
			'w_location'			=>$data['W_LOCATION'],
			
			'title_flg'				=>$title_flg,
			'title_vrt'				=>$data['TITLE_VRT'],
			'title_hrz'				=>$data['TITLE_HRZ'],
			'align_title'			=>$data['ALIGN_TITLE'],
			'contents_title'		=>$data['CONTENTS_TITLE'],
			'title_color'			=>$data['TITLE_COLOR'],

			'sub_title_flg'			=>$sub_title_flg,
			'align_sub_title'		=>$data['ALIGN_SUB_TITLE'],
			'contents_sub_title'	=>$data['CONTENTS_SUB_TITLE'],
			'sub_title_color'		=>$data['SUB_TITLE_COLOR'],

			'link_flg'				=>$link_flg,
			'align_link'			=>$data['ALIGN_LINK'],
			'link_txt'				=>$data['LINK_TXT'],
			'link_color'			=>$data['LINK_COLOR'],
			'ext_flg'				=>$data['EXT_FLG'],
			'link_url'				=>$data['LINK_URL']
		);
	}

	return $banner_contents;
}

?>