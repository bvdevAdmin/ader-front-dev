<?php
/*
 +=============================================================================
 | 
 |  L_BANNER_MST
 |     LANDING_BANNER
 |         BANNER_CONTENTS
 | 
 +=============================================================================
*/
// 1
$landing_banner_mst_sql = "
  SELECT * FROM L_BANNER_MST WHERE IDX = 1
";

$db->query($landing_banner_mst_sql);

foreach($db->fetch() as $mst_data) {
  $landing_banner_mst [] = array( // 단건인경우에 [] 안써도 된당
    'idx'           =>$mst_data['IDX'],
    'country'       =>$mst_data['COUNTRY'],
    'mst_type'      =>$mst_data['MST_TYPE'],
    'mst_title'     =>$mst_data['MST_TITLE'],
    'display_num'   =>$mst_data['DISPLAY_NUM'],
    'always_flg'    =>$mst_data['ALWAYS_FLG'],
    'display_flg'   =>$mst_data['DISPLAY_FLG']
  );
}

// 2
$landing_banner_sql = "
  SELECT * FROM LANDING_BANNER WHERE MST_IDX = 1
";

$db->query($landing_banner_sql);

foreach($db->fetch() as $landing_data){
  $landing_banber [] = array(
    'idx'           =>$landing_data['IDX'],
    'mst_idx'       =>$landing_data['MST_IDX'],
    'display_num'   =>$landing_data['DISPLAY_NUM'],
    'layout_grid'   =>$landing_data['LAYOUT_GRID']
  );
}

// 3
$banner_contents_sql = "
  SELECT * FROM BANNER_CONTENTS
";

$db->query($banner_contents_sql);

foreach($db->fetch() as $banner_data){
  $banner_contents [] = array(
    'idx'             =>$banner_data['IDX'],
    'layout_idx'      =>$banner_data['LAYOUT_IDX'],
    'display_num'     =>$banner_data['DISPLAY_NUM'],
    'contents_type'   =>$banner_data['CONTENTS_TYPE']
  );
}

// 결과 ?
foreach($landing_banner_mst as &$mst){
  foreach($landing_banber as $layout) {
    if(isset($layout['mst_idx']) && $mst['idx'] == $layout['mst_idx']) {
      $mst['landing_banner'][] = $layout;
    }
  }
}

// $json_result['data1'] = $landing_banner_mst;
// $json_result['data2'] = $landing_banber;
// $json_result['data3'] = $banner_contents;
$json_result['data'] = $landing_banner_mst;
?>