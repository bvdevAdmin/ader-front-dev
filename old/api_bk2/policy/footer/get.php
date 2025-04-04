<?php
/*
 +=============================================================================
 | 
 | Footer 정보
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.02.28
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/
// ABT / INF
// 컨트리
// 폴리시 타입
$country = null;
if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

if ($country != null) {
  $select_policy_sql = "
    SELECT 
      DEVICE_TYPE,
      POLICY_TYPE,
      POLICY_TXT
    FROM 
      POLICY_INFO 
    WHERE 
      COUNTRY = '".$country."' AND 
      (
        POLICY_TYPE = 'ABT' OR 
        POLICY_TYPE = 'INF'
      );
  ";
  $db->query($select_policy_sql);

  foreach($db->fetch() as $policy_data) {
    $json_result['data'][] = array(
      'device_type' =>$policy_data['DEVICE_TYPE'],
      'policy_type' =>$policy_data['POLICY_TYPE'],
      'policy_txt' =>$policy_data['POLICY_TXT']
    );
  }
}
?>