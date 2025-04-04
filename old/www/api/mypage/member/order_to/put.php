<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 배송지 수정
 | -------
 |
 | 최초 작성    : 윤재은
 | 최초 작성일  : 2023.01.12
 | 최종 수정일  : 
 | 버전      : 1.0
 | 설명      : 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$country = null;
if (isset($_SESSION['COUNTRY'])) {
    $country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
    $member_idx = $_SESSION['MEMBER_IDX'];
}

$bool_default_flg = 0;
if (isset($default_flg)) {
    if ($default_flg == "true") {
        $bool_default_flg = 1;
    }
}

if (!isset($country) || $member_idx == 0) {
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
    
    echo json_encode($json_result);
    exit;
}

if ((isset($country) && $member_idx > 0 && isset($order_to_idx))) {
    if (isset($to_place) && isset($to_name)) {
        $db->begin_transaction();
        
        try {
            if ($country == "KR") {
                $db->update(
                    "ORDER_TO",
                    array(
                        'TO_PLACE'       => $to_place,
                        'TO_NAME'        => $to_name,
                        'TO_MOBILE'      => $to_mobile,
                        'TO_ZIPCODE'     => $to_zipcode,
                        'TO_LOT_ADDR'    => $to_lot_addr,
                        'TO_ROAD_ADDR'   => $to_road_addr,
                        'TO_DETAIL_ADDR' => $to_detail_addr,
                        'DEFAULT_FLG'    => $bool_default_flg
                    ),
                    "IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",
                    array($order_to_idx, $country, $member_idx)
                );
            } else {
                $db->update(
                    "ORDER_TO",
                    array(
                        'TO_PLACE'         => $to_place,
                        'TO_NAME'          => $to_name,
                        'TO_MOBILE'        => $to_mobile,
                        'TO_ZIPCODE'       => $to_zipcode,
                        'TO_COUNTRY_CODE'  => $country_code,
                        'TO_PROVINCE_IDX'  => $province_idx,
                        'TO_CITY'          => $city,
                        'TO_ADDRESS'       => $address,
                        'DEFAULT_FLG'      => $bool_default_flg
                    ),
                    "IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",
                    array($order_to_idx, $country, $member_idx)
                );
            }
              
            if ($bool_default_flg > 0) {
                $db_result = $db->affectedRows();
                
                if ($db_result > 0) {
                    $db->update(
                        "ORDER_TO",
                        array(
                            'DEFAULT_FLG' => 0
                        ),
                        "IDX != ? AND COUNTRY = ? AND MEMBER_IDX = ?",
                        array($order_to_idx, $country, $member_idx)
                    );
                }
            }
            
            $db->commit();
        } catch (mysqli_sql_exception $exception) {
            print_r($exception);
            
            $db->rollback();
            
            $json_result['code'] = 302;
            $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0022', array());
            
            echo json_encode($json_result);
            exit;
        }
    } else {
        /* 기존 배송주소 기본배송지 해제처리 */
        $db->update(
            "ORDER_TO",
            array(
                'DEFAULT_FLG' => 0
            ),
            "COUNTRY = ? AND MEMBER_IDX = ?",
            array($country, $member_idx)
        );
        
        /* 기본배송지 설정 */
        $db->update(
            "ORDER_TO",
            array(
                'DEFAULT_FLG' => 1
            ),
            "IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",
            array($order_to_idx, $country, $member_idx)
        );
    }
} else {
    $json_result['code'] = 301;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0023', array());
    
    echo json_encode($json_result);
    exit;
}
?>
