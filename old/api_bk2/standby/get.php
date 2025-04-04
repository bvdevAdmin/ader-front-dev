<?php
/*
 +=============================================================================
 | 
 | 마이페이지_스탠바이 - 스탠바이 정보 취득 API
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.05.10
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/check.php");

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

$member_name = null;
if (isset($_SESSION['MEMBER_NAME'])) {
	$member_name = $_SESSION['MEMBER_NAME'];	
}

$standby_idx = 0;
if (isset($_POST['standby_idx'])) {
	$standby_idx = $_POST['standby_idx'];
}

$login_chk = checkPromotionLogin($db, $country, $member_idx, $member_name);
if($login_chk['value'] == false){
    $json_result['code'] = 301;
	$json_result['msg'] = $login_chk['msg'];
	
	echo json_encode($json_result);
	exit;
}

if ($standby_idx > 0) {
    $time_result =  checkAccessTime($db, $country, $standby_idx, 'STANDBY');
    if($time_result['value'] == false){
        $json_result['code'] = 301;
        $json_result['msg'] = $time_result['msg'];
        
        echo json_encode($json_result);
		exit;
    }
    $get_standby_sql = "
        SELECT
            PS.IDX                  AS STANDBY_IDX,
            PS.COUNTRY              AS COUNTRY,
            PS.MEMBER_LEVEL         AS MEMBER_LEVEL,
            PS.TITLE                AS TITLE,
            IFNULL(PS.DESCRIPTION,'')          
                                    AS DESCRIPTION,
            PS.ENTRY_START_DATE     AS ENTRY_START_DATE,
            PS.ENTRY_END_DATE       AS ENTRY_END_DATE,
            PS.PURCHASE_START_DATE  AS PURCHASE_START_DATE,
            PS.PURCHASE_END_DATE    AS PURCHASE_END_DATE,
            PS.ORDER_LINK_DATE      AS ORDER_LINK_DATE,
            PS.THUMBNAIL_LOCATION   AS THUMBNAIL_LOCATION,
            PS.BANNER_LOCATION      AS BANNER_LOCATION
        FROM
            PAGE_STANDBY PS
        WHERE
            IDX = ".$standby_idx."
    ";
    $db->query($get_standby_sql);
    foreach($db->fetch() as $data){
        $standby_product_info = array();
        $product_table = "
            STANDBY_PRODUCT SBP
            LEFT JOIN SHOP_PRODUCT PR ON
            SBP.PRODUCT_IDX = PR.IDX
            LEFT JOIN ORDERSHEET_MST OM ON
            PR.ORDERSHEET_IDX = OM.IDX
        ";

        $product_where = "
            SBP.STANDBY_IDX = ".$data['STANDBY_IDX']." AND
            PR.SALE_FLG = TRUE AND
            PR.DEL_FLG = FALSE
        ";

        $standby_product_sql = "
            SELECT
                PR.IDX                      AS PRODUCT_IDX,
                PR.PRODUCT_NAME				AS PRODUCT_NAME,
                PR.PRICE_".$country."		AS PRICE,
                PR.DISCOUNT_".$country."	AS DISCOUNT,
                PR.SALES_PRICE_".$country."	AS SALES_PRICE,
                OM.COLOR					AS COLOR
            FROM
                ".$product_table."
            WHERE
                ".$product_where."
        ";
        
        $db->query($standby_product_sql);
        
        foreach($db->fetch() as $product_data) {
            $product_idx = $product_data['PRODUCT_IDX'];
            $product_img = array();
            $thumb_cnt = $db->count("dev.PRODUCT_IMG","PRODUCT_IDX = ".$product_idx." AND IMG_TYPE LIKE 'T%'");
            
            $img_type = "";
            if ($thumb_cnt > 0) {
                $p_img_type = 'TP';
                $o_img_type = 'TO';
            } else {
                $p_img_type = 'P';
                $o_img_type = 'O';
            }
            
            $select_img_p_sql = "
                SELECT
                    PI.IMG_TYPE			AS IMG_TYPE,
                    PI.IMG_LOCATION		AS IMG_LOCATION
                FROM
                    PRODUCT_IMG PI
                WHERE
                    PI.PRODUCT_IDX = ".$product_idx." AND
                    PI.IMG_TYPE = '".$p_img_type."' AND
                    PI.IMG_SIZE = 'M'
                ORDER BY
                    PI.IDX ASC
            ";
            
            $db->query($select_img_p_sql);
            
            $product_p_img = array();
            foreach($db->fetch() as $img_data) {
                $product_p_img[] = array(
                    'img_type'		=>"P",
                    'img_location'	=>$img_data['IMG_LOCATION']
                );
            }
            
            $select_img_o_sql = "
                SELECT
                    PI.IMG_TYPE			AS IMG_TYPE,
                    PI.IMG_LOCATION		AS IMG_LOCATION
                FROM
                    PRODUCT_IMG PI
                WHERE
                    PI.PRODUCT_IDX = ".$product_idx." AND
                    PI.IMG_TYPE = '".$o_img_type."' AND
                    PI.IMG_SIZE = 'M'
                ORDER BY
                    PI.IDX ASC
            ";
            
            $db->query($select_img_o_sql);
            
            $product_o_img = array();
            foreach($db->fetch() as $img_data) {
                $product_o_img[] = array(
                    'img_type'		=>"O",
                    'img_location'	=>$img_data['IMG_LOCATION']
                );
            }
            
            $product_img = array(
                'product_p_img'		=>$product_p_img,
                'product_o_img'		=>$product_o_img
            );
            
            $whish_flg = false;
            if ($member_idx > 0) {
                $whish_count = $db->count("WHISH_LIST","MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND DEL_FLG = FALSE");
                if ($whish_count > 0) {
                    $whish_flg = true;
                }
            }
            
            $product_color = getProductColor($db,$product_idx);
            
            $product_size = getProductSize($db,'B',null,$product_idx);
            
            $stock_status = null;
            $soldout_cnt = 0;
            $stock_close_cnt = 0;
            for ($i=0; $i<count($product_size); $i++) {
                $tmp_stock_status = $product_size[$i]['stock_status'];
                if ($tmp_stock_status == "STSO") {
                    $soldout_cnt++;
                } else if ($tmp_stock_status == "STCL") {
                    $stock_close_cnt++;
                }
            }
            
            if (count($product_size) == $soldout_cnt) {
                $stock_status = "STSO";
            } else if ($stock_close_cnt > 0) {
                $stock_status = "STCL";
            }
            
            $product_info[] = array(
                'product_idx'       =>$product_idx,
                'product_name'		=>$product_data['PRODUCT_NAME'],
                'price'				=>$product_data['PRICE'],
                'discount'			=>$product_data['DISCOUNT'],
                'sales_price'		=>$product_data['SALES_PRICE'],
                'color'				=>$product_data['COLOR'],
                'product_img'		=>$product_img,
                'product_color'		=>$product_color,
                'product_size'		=>$product_size,
                'stock_status'		=>$stock_status,
                'whish_flg'			=>$whish_flg
            );
        }
        $policy = $db->get('STANDBY_POLICY', 'COUNTRY = ?', array($country))[0]['POLICY'];
        $json_result['data'][] = array(
            'standby_idx'           =>$data['STANDBY_IDX'],
            'country'               =>$data['COUNTRY'],
            'member_level'          =>$data['MEMBER_LEVEL'],
            'title'                 =>$data['TITLE'],
            'description'           =>$data['DESCRIPTION'],
            'entry_start_date'      =>$data['ENTRY_START_DATE'],
            'entry_end_date'        =>$data['ENTRY_END_DATE'],
            'purchase_start_date'   =>$data['PURCHASE_START_DATE'],
            'purchase_end_date'     =>$data['PURCHASE_END_DATE'],
            'order_link_date'       =>$data['ORDER_LINK_DATE'],
            'thumbnail_location'    =>$data['THUMBNAIL_LOCATION'],
            'banner_location'       =>$data['BANNER_LOCATION'],
            'policy'                =>$policy,
            'product_info'          =>$product_info
        );
    }
}
else{
    $json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', array());
	
	echo json_encode($json_result);
	exit;
}
?>