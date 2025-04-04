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

$country_code = null;
if (isset($_POST['country_code'])) {
	$country_code	= $_POST['country_code'];
}
if($country_code != null){
    $province_flg = $db->get('COUNTRY_INFO', 'COUNTRY_CODE = ? ', array($country_code));
    
    if($province_flg == true){
        $get_province_query = "
            SELECT 
                IDX             AS PROVINCE_IDX,
                PROVINCE_NAME   AS PROVINCE_NAME
            FROM 
                PROVINCE_INFO
            WHERE
                COUNTRY_CODE = '".$country_code."';
        ";

        $db->query($get_province_query);
        foreach($db->fetch() as $data){
            $json_result['province_flg'] = true;
            $json_result['data'][] = array(
                'label' => $data['PROVINCE_NAME'],
                'value' => $data['PROVINCE_IDX'],
            );
        }
    }
    else{
        $json_result['province_flg'] = false;
        return $json_result;
    }
}
else{
    $json_result['code'] = 301;
    $json_result['msg'] = "국가정보를 찾을 수 없습니다.";
    return false;
}
?>