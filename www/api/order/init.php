<?php
/*
 +=============================================================================
 |
 | Batch - 임시 주문정보/상품 삭제 처리
 | -------
 |
 | 최초 작성	: 이재민
 | 최초 작성일	: 2024.11.16
 | 최종 수정일	:
 | 버전		: 1.0
 | 설명		:
 |
 +=============================================================================
*/

$select_tmp_order_sql = "
    SELECT
        OI.IDX      AS ORDER_IDX
    FROM
        TMP_ORDER_INFO OI
    WHERE
        DATE_ADD(OI.CREATE_DATE,INTERVAL 5 MINUTE) < NOW()
";

$db->query($select_tmp_order_sql);

$param_idx = array();

foreach($db->fetch() as $data) {
    array_push($param_idx,$data['ORDER_IDX']);
}

if (count($param_idx) > 0) {
    $db->delete(
        "TMP_ORDER_INFO",
        "IDX IN (".implode(',',array_fill(0,count($param_idx),'?')).")",
        $param_idx
    );

    $db->delete(
        "TMP_ORDER_PRODUCT",
        "ORDER_IDX IN (".implode(',',array_fill(0,count($param_idx),'?')).")",
        $param_idx
    );
}

?>