<?php
/*
 +=============================================================================
 | 
 | 공통함수 - 프론트 페이지 팝업 표시
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

if ($_SERVER['HTTP_COUNTRY'] != null && $popup_type != null && isset($param_popup)) {
	if ($popup_type == "W") {
		$cnt_param = $db->count("FRONT_PAGE_URL","PAGE_URL = ?",array($param_popup));
	} else if ($popup_type == "P") {
		$cnt_param = $db->count("SHOP_PRODUCT","IDX = ? AND SALE_FLG = TRUE AND DEL_FLG = FALSE",array($param_popup));
	}

	if ($cnt_param > 0) {
		$check_result = checkPopup_url($db,$popup_type,$param_popup);
		if ($check_result == true) {
			$where = " S_PU.URL_TYPE = ? ";
			if ($popup_type == "W") {
				$where .= "
					AND (
						S_PU.FRONT_IDX = (
							SELECT
								S_FU.IDX
							FROM
								FRONT_PAGE_URL S_FU
							WHERE
								S_FU.PAGE_URL = ?
						)
					)
				";
			} else if ($popup_type == "P") {
				$where .= " AND (S_PU.PRODUCT_IDX = ?) ";
			}

			$select_front_popup_sql = "
				SELECT
					FP.IDX					AS POPUP_IDX,
					FP.POPUP_TITLE			AS POPUP_TITLE,
					FP.POPUP_CONTENTS		AS POPUP_CONTENTS,
					FP.WIDTH				AS WIDTH,
					FP.HEIGHT				AS HEIGHT,
					FP.POPUP_VRT			AS POPUP_VRT,
					FP.POPUP_HRZ			AS POPUP_HRZ,
					FP.CLOSE_TYPE			AS CLOSE_TYPE
				FROM
					FRONT_POPUP FP
				WHERE
					FP.COUNTRY = ? AND
					FP.IDX IN (
						SELECT
							S_PU.POPUP_IDX
						FROM
							POPUP_URL S_PU
						WHERE
							".$where."
						ORDER BY
							S_PU.IDX DESC
					) AND
					(
						ALWAYS_FLG = TRUE OR
						NOW() BETWEEN DISPLAY_START_DATE AND DISPLAY_END_DATE
					) AND
					FP.DEL_FLG = FALSE
				ORDER BY
					FP.IDX DESC
				LIMIT
					0,1
			";

			$db->query($select_front_popup_sql,array($_SERVER['HTTP_COUNTRY'],$popup_type,$param_popup));

			foreach($db->fetch() as $data) {
				$popup_contents = str_replace('/scripts/smarteditor2/upload/',smart_editor,$data['POPUP_CONTENTS']);
				$json_result['data'] = array(
					'popup_idx'				=>$data['POPUP_IDX'],
					'popup_title'			=>$data['POPUP_TITLE'],
					'popup_contents'		=>$popup_contents,
					'width'					=>$data['WIDTH'],
					'height'				=>$data['HEIGHT'],
					'popup_vrt'				=>$data['POPUP_VRT'],
					'popup_hrz'				=>$data['POPUP_HRZ'],
					'close_type'			=>$data['CLOSE_TYPE']
				);
			}
		}
	}
}

function checkPopup_url($db,$popup_type,$param) {
	$check_result = false;

	$where = " URL_TYPE = ? ";

	if ($popup_type == "W") {
		$where .= "
			AND (
				FRONT_IDX = (
					SELECT
						S_FU.IDX
					FROM
						FRONT_PAGE_URL S_FU
					WHERE
						S_FU.PAGE_URL = ?
				)
			)
		";
	} else if ($popup_type == "P") {
		$where .= " AND (PRODUCT_IDX = ?) ";
	}

	$cnt_url = $db->count("POPUP_URL",$where,array($popup_type,$param));
	if ($cnt_url > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

?>	