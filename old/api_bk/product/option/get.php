<?php
/*
 +=============================================================================
 | 
 | 상품 옵션 상세
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.03.19
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common/common.php");

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

$option_idx_arr = null;
if (isset($_POST['option_idx_arr'])) {
	$option_idx_arr = $_POST['option_idx_arr'];
}
if ($option_idx_arr != null && $country != null) {
	$select_option_sql = "
		SELECT
			PR.PRODUCT_CODE				AS PRODUCT_CODE,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
            OO.OPTION_NAME              AS OPTION_NAME,
			OM.BRAND					AS BRAND,
			PR.SALES_PRICE_".$country."	AS SALES_PRICE
		FROM
			SHOP_PRODUCT PR
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX 
            LEFT JOIN ORDERSHEET_OPTION OO ON
            OM.IDX = OO.ORDERSHEET_IDX
		WHERE
            OO.IDX IN (".implode(',',$option_idx_arr).")";
	
	$db->query($select_option_sql);
	
	foreach($db->fetch() as $product_data) {
		$json_result['data'][] = array(
            'product_code'       =>$product_data['PRODUCT_CODE'],
            'product_name'      =>$product_data['PRODUCT_NAME'],
            'option_name'       =>$product_data['OPTION_NAME'],
            'brand'             =>$product_data['BRAND'],
            'sales_price'       =>$product_data['SALES_PRICE'],
		);
	}
}
?>