<?php
/*
 +=============================================================================
 | 
 | 마이페이지 정보 취득
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.09
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}
else{
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if($member_idx > 0 && $country != null){
    $select_member_sql = "
        SELECT
            MB.IDX				AS MEMBER_IDX,
            MB.MEMBER_NAME		AS MEMBER_NAME,
            MB.MEMBER_ID		AS MEMBER_ID,
            (
				SELECT 
					S_MI.MILEAGE_BALANCE
				FROM 
					MILEAGE_INFO S_MI
				WHERE 
					S_MI.COUNTRY = '".$country."' AND
					S_MI.MEMBER_IDX = MB.IDX
				ORDER BY 
					S_MI.IDX DESC
				LIMIT
					0,1
			)					AS MILEAGE_BALANCE_TOTAL,
			(
				SELECT
					COUNT(S_VI.IDX)
				FROM 
					VOUCHER_ISSUE S_VI
				WHERE
					S_VI.MEMBER_IDX = MB.IDX AND
					S_VI.VOUCHER_ADD_DATE IS NOT NULL AND
					S_VI.USED_FLG = FALSE AND
					NOW() BETWEEN S_VI.USABLE_START_DATE AND S_VI.USABLE_END_DATE AND
					S_VI.DEL_FLG = FALSE AND
					S_VI.COUNTRY = '".$country."'
			)					AS VOUCHER_CNT,
			(
				SELECT 
					COUNT(S_OI.IDX) 
				FROM 
					ORDER_INFO S_OI
				WHERE 
					COUNTRY = '".$country."'  AND 
					MEMBER_IDX = ".$member_idx." AND 
					ORDER_STATUS NOT REGEXP 'OC|OE|OR|DCP'
			)					AS ORDER_ING_CNT
        FROM
            MEMBER_".$country." MB
        WHERE
            MB.IDX = ".$member_idx."
    ";

    $db->query($select_member_sql);

    foreach($db->fetch() as $data){
        $json_result['data'][] = array(
            'member_idx'        		=>$data['MEMBER_IDX'],
            'member_id'         		=>$data['MEMBER_ID'],
            'member_name'       		=>$data['MEMBER_NAME'],
            'mileage_balance_total'		=>number_format($data['MILEAGE_BALANCE_TOTAL']),
            'voucher_cnt'       		=>$data['VOUCHER_CNT'],
						'order_ing_cnt'				=>$data['ORDER_ING_CNT']
        );
    }
}

?>