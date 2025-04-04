<?php

function alert($msg, $href = null) {
	if(is_string($msg) && $msg != '') {
		echo '
			<script>
				alert("'.$msg.'");
		';
		if($href != null && is_string($href)) {
			echo 'location.href = "'.$href.'";';
		}
		echo '</script>';		
	}
}

function order_num() {
	return date('Ymd-His').substr(microtime(),2,1);
}

function get_msg($msg_code) {	
	$country = (!defined('COUNTRY')) ? 'KR' : COUNTRY;	
	$db = new db();
	$data = $db->get('MSG_MST', "MSG_CODE = ? ", array($msg_code));
	
	if(sizeof($data) > 0) {
		return $data[0]['MSG_TEXT_'.$country];
	}
	
	return null;
}

//** DB에 CDN 주소를 포함하지 않고 반환되는 값에 CDN 주소를 붙여서 반환함  **/
function chk_cdn_url($url) {
	return ( strpos($url, CDN) !== 0 ? CDN : '' ) . $url;
}