<?php
/*
 +=============================================================================
 | 
 | 해외 시/도정보 API
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.6.9
 | 최종 수정일	: 2023.6.9
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

if ($country_code != null) {
    $cnt_country = $db->get('COUNTRY_INFO','COUNTRY_CODE = ? ',array($country_code));
    if ($cnt_country > 0) {
        $get_province_query = "
            SELECT 
                PI.IDX             AS PROVINCE_IDX,
                PI.PROVINCE_NAME   AS PROVINCE_NAME
            FROM 
                PROVINCE_INFO PI
            WHERE
                PI.COUNTRY_CODE = ?
        ";

        $db->query($get_province_query,array($country_code));
        
        foreach($db->fetch() as $data){
            $json_result['province_flg'] = true;
            $json_result['data'][] = array(
                'label' => $data['PROVINCE_NAME'],
                'value' => $data['PROVINCE_IDX'],
            );
        }
    } else {
        $json_result['province_flg'] = false;
        
        echo json_encode($json_result);
		exit;
    }
} else {
    $json_result['code'] = 301;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0012', array());
    
    echo json_encode($json_result);
	exit;
}

?>