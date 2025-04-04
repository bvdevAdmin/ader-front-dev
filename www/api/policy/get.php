<?php
/*
 +=============================================================================
 | 
 | 법적 고지사항
 | -------
 |
 | 최초 작성	: 양한빈
 | 최초 작성일	: 2023.09.14
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |						
 | 
 +=============================================================================
*/

if(!isset($type)) $type = 'PNL';

$data = $db->get('POLICY_INFO','COUNTRY = ? AND POLICY_TYPE = ?',array($country,$type));
if (sizeof($data) > 0) {
	$policy = $data[0];
	
    $json_result = array(
        'type'		=>$policy['POLICY_TYPE'],
        'contents'	=>$policy['POLICY_TXT']
    );
}
