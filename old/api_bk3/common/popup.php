<?php
/*
 +=============================================================================
 | 
 | 팝업조회
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.10.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$non_param_url = explode('?',$url)[0];

$select_popup_info_sql = "
    SELECT
        DP.IDX              AS IDX,
        DP.TITLE            AS TITLE,
        DP.CONTENTS         AS CONTENTS,
        DP.WIDTH            AS WIDTH,
        DP.HEIGHT            AS HEIGHT,
        DP.CLOSE_FLG        AS CLOSE_FLG,
        PU.URL              AS URL,
        PU.POPUP_URL_TYPE   AS POPUP_URL_TYPE
    FROM 
        DISPLAY_POPUP DP LEFT JOIN
        POPUP_URL PU ON
        DP.IDX = PU.POPUP_IDX
    WHERE
        NOW() BETWEEN DP.DISPLAY_START_DATE AND DP.DISPLAY_END_DATE
    AND
        DP.DISPLAY_FLG = TRUE
    ORDER BY 
        DP.UPDATE_DATE ASC
";

$db->query($select_popup_info_sql);

foreach($db->fetch() as $popup_info){
    if($popup_info['POPUP_URL_TYPE'] == 'PRODUCT'){
        if($popup_info['URL'] == $url){
            $json_result['data'] = array(
                'idx'               =>$popup_info['IDX'],
                'title'             =>$popup_info['TITLE'],
                'contents'          =>$popup_info['CONTENTS'],
                'width'             =>$popup_info['WIDTH'],
                'height'            =>$popup_info['HEIGHT'],
                'close_flg'         =>$popup_info['CLOSE_FLG'],
                'popup_url_type'    =>$popup_info['POPUP_URL_TYPE'],
                'url'               =>$popup_info['URL']
            );
        }
    } else {
        if($popup_info['URL'] == $non_param_url){
            $json_result['data'] = array(
                'idx'               =>$popup_info['IDX'],
                'title'             =>$popup_info['TITLE'],
                'contents'          =>$popup_info['CONTENTS'],
                'width'             =>$popup_info['WIDTH'],
                'height'            =>$popup_info['HEIGHT'],
                'close_flg'         =>$popup_info['CLOSE_FLG'],
                'popup_url_type'    =>$popup_info['POPUP_URL_TYPE'],
                'url'               =>$popup_info['URL']
            );
        }
    }
}

?>