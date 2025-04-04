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

/*
  L_BANNER_MST / IDX = LANDING_BANNER / MST_IDX
  LANDING_BANNER / IDX = BANNER_CONTENTS / LAYOUT_IDX
*/

// $landing_banner_mst_sql = "
//   SELECT
//     *
//   FROM
//     L_BANNER_MST
//   ORDER BY 
//     DISPLAY_NUM
// ";

// $db->query($landing_banner_mst_sql);

// foreach($db->fetch() as $data) {
//   $landing_banner_mst[] = array(
//     'idx'   =>$data['IDX'],
//     'country'   =>$data['COUNTRY'],
//     'mst_type'   =>$data['MST_TYPE'],
//     'mst_title'   =>$data['MST_TITLE'],
//     'display_num'   =>$data['DISPLAY_NUM'],
//     'always_flg'   =>$data['ALWAYS_FLG'],
//     'display_flg'   =>$data['DISPLAY_FLG']
//   );
// }

// $landing_banner_sql = "
//   SELECT
//     *
//   FROM
//     LANDING_BANNER
// ";

// $db->query($landing_banner_sql);

// foreach($db->fetch() as $data) {
//   $landing_banner[] = array(
//     'idx'   =>$data['IDX'],
//     'mst_idx'   =>$data['MST_IDX'],
//     'display_num'   =>$data['DISPLAY_NUM'],
//     'layout_grid'   =>$data['LAYOUT_GRID']
//   );
// }

// $banner_contents_sql = "
//   SELECT
//     *
//   FROM
//     BANNER_CONTENTS
// ";

// $db->query($banner_contents_sql);

// foreach($db->fetch() as $data) {
//   $banner_contents[] = array(
//     'idx'   =>$data['IDX'],
//     'layout_idx'   =>$data['LAYOUT_IDX'],
//     'display_num'   =>$data['DISPLAY_NUM'],
//     'contents_type'   =>$data['CONTENTS_TYPE']
//   );
// }

// // landing_banner_mst, landing_banner, banner_contents
// foreach($landing_banner_mst as &$mst) {
//   foreach($landing_banner as $layout) {
//     // isset $layout['mst_idx'] 체크
//     if($mst['idx'] == $layout['mst_idx']) {
//       $mst['landing_banner'][] = $layout;
//     }
//   }
// }

// foreach($landing_banner_mst as &$mst) {
//   if(isset($mst['landing_banner'])) {
//     foreach($mst['landing_banner'] as &$layout) {
//       foreach($banner_contents as $content) {
//         if($layout['idx'] == $content['layout_idx']) {
//           $layout['banner_contents'][] = $content;
//         }
//       }
//     }
//   }
// }

// $json_result['data'] = $landing_banner_mst;

// =============================================

$landing_banner_mst_sql = "
  SELECT
    *
  FROM
    L_BANNER_MST
  WHERE
    IDX IN (1, 3)
  ORDER BY 
    DISPLAY_NUM
";

$db->query($landing_banner_mst_sql);

foreach($db->fetch() as $mst_data) {
  $landing_banner_mst[] = array(
    'idx'   =>$mst_data['IDX'],
    'country'   =>$mst_data['COUNTRY'],
    'mst_type'   =>$mst_data['MST_TYPE'],
    'mst_title'   =>$mst_data['MST_TITLE'],
    'display_num'   =>$mst_data['DISPLAY_NUM'],
    'always_flg'   =>$mst_data['ALWAYS_FLG'],
    'display_flg'   =>$mst_data['DISPLAY_FLG']
  );
}

$landing_banner_sql = "
  SELECT
    *
  FROM
    LANDING_BANNER
  WHERE 
    MST_IDX IN (1, 3)
";

$db->query($landing_banner_sql);

foreach($db->fetch() as $layout_data) {
  $landing_banner[] = array(
    'idx'   =>$layout_data['IDX'],
    'mst_idx'   =>$layout_data['MST_IDX'],
    'display_num'   =>$layout_data['DISPLAY_NUM'],
    'layout_grid'   =>$layout_data['LAYOUT_GRID']
  );
}

$banner_contents_sql = "
  SELECT
    *
  FROM
    BANNER_CONTENTS
";

$db->query($banner_contents_sql);

foreach($db->fetch() as $contents_data) {
  $banner_contents[] = array(
    'idx'   =>$contents_data['IDX'],
    'layout_idx'   =>$contents_data['LAYOUT_IDX'],
    'display_num'   =>$contents_data['DISPLAY_NUM'],
    'contents_type'   =>$contents_data['CONTENTS_TYPE']
  );
}

// landing_banner_mst, landing_banner, banner_contents
foreach($landing_banner_mst as &$mst) {
  foreach($landing_banner as $layout) {
    if(isset($layout['mst_idx']) && $mst['idx'] == $layout['mst_idx']) {
      $mst['landing_banner'][] = $layout;
    }
  }
}

// foreach($landing_banner_mst as &$mst) {
//   if(isset($mst['landing_banner'])) {
//     foreach($mst['landing_banner'] as &$layout) {
//       foreach($banner_contents as $content) {
//         if($layout['idx'] == $content['layout_idx']) {
//           $layout['banner_contents'][] = $content;
//         }
//       }
//     }
//   }
// }

$json_result['data'] = $landing_banner_mst;

?>