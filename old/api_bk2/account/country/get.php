<?php
/*
 +=============================================================================
 | 
 | 해외 국가정보 API
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
include_once("/var/www/www/api/common.php");
$country = null;
if (isset($_POST['country'])) {
	$country	= $_POST['country'];
}
if($country != null){
    $where = '';
    if($country == 'EN'){
        $where .= 'WHERE COUNTRY_NAME NOT IN ("China")';
    }
    else if($country == 'CN'){
        $where .= 'WHERE COUNTRY_NAME ="China"';
    }
    else{
        $json_result['code'] = 301;
        $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0011', array());
        
        echo json_encode($json_result);
        exit;
    }

    $get_country_query = "
        SELECT 
            COUNTRY_NAME,
            COUNTRY_CODE
        FROM COUNTRY_INFO
        ".$where."
    ";
    $db->query($get_country_query);
    foreach($db->fetch() as $data){
        $json_result['data'][] = array(
            'label' => $data['COUNTRY_NAME'],
            'value' => $data['COUNTRY_CODE']
        );
    }
}
else{
    $json_result['code'] = 301;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0012', array());
    
    echo json_encode($json_result);
    exit;
}
?>