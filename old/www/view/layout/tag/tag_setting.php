<?php
$redirect_uri = $_SERVER['REDIRECT_URL'];

/*
$request_uri = $_SERVER['REQUEST_URI'];
$request_uri_arr = explode("?",$request_uri);
$page_url = $request_uri_arr[0];
$param_url = $request_uri_arr[1];

$product_seo_title = null;
if($page_url == '/product/detail'){
	$param_url_arr = explode('=',$param_url);
	$param_key = $param_url_arr[0];
	$param_value = $param_url_arr[1];
	
	if($param_key == 'product_idx'){
		$product_seo_title = xssDecode($db->get('SHOP_PRODUCT', 'IDX = ?', array($param_value))[0]['SEO_TITLE']);
	}
}
*/

$select_seo_setting_sql = "
	SELECT
		SS.TAG_TITLE			AS TAG_TITLE,
		SS.TAG_DESC				AS TAG_DESC,
		
		SS.FAVICON_LOCATION		AS FAVICON_LOCATION,
		
		SS.SEARCH_GOOGLE		AS SEARCH_GOOGLE,
		SS.SEARCH_NAVER			AS SEARCH_NAVER,
		
		SS.SNS_IMG_LOCATION		AS SNS_IMG_LOCATION,
		SS.CARD_FLG				AS CARD_FLG,
		
		SS.ROBOT				AS ROBOT,
		
		SS.REDIRECT_TYPE		AS REDIRECT_TYPE,
		SS.REDIRECT_LOCATION	AS REDIRECT_LOCATION,
		
		SS.SITE_MAP_FLG			AS SITE_MAP_FLG,
		SS.SITE_MAP_LOCATION	AS SITE_MAP_LOCATION,
		
		SS.RSS_FEED_FLG			AS RSS_FEED_FLG,
		SS.RSS_FEED_LOCATION	AS RSS_FEED_LOCATION,
		
		SS.CODE_HEADER			AS CODE_HEADER,
		SS.CODE_BODY			AS CODE_BODY
	FROM
		SEO_SETTING SS
";

$db->query($select_seo_setting_sql);

$seo_setting = array();
foreach($db->fetch() as $setting_data) {
	/*
	if($product_seo_title == null || strlen($product_seo_title) == 0){
		$product_seo_title = xssDecode($setting_data['TAG_TITLE']);
	}
	*/
	
	$seo_setting = array(
		'tag_title'			=>xssDecode($setting_data['TAG_TITLE']),
		'tag_desc'			=>xssDecode($setting_data['TAG_DESC']),
		
		'favicon_location'	=>"https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user".$setting_data['FAVICON_LOCATION'],
		
		'search_google'		=>xssDecode($setting_data['SEARCH_GOOGLE']),
		'search_naver'		=>xssDecode($setting_data['SEARCH_NAVER']),
		
		'sns_img_location'	=>"https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user".$setting_data['SNS_IMG_LOCATION'],
		'card_flg'			=>$setting_data['CARD_FLG'],
		
		'code_header'		=>xssDecode($setting_data['CODE_HEADER']),
		'code_body'			=>xssDecode($setting_data['CODE_BODY'])
	);
}

$naver_channel_info = array();

$channel_cnt = $db->count("NAVER_CHANNEL");
if ($channel_cnt > 0) {
	$naver_channel_info = getNaverChannelInfo($db);
}

$seo_tag_info = array();

if ($redirect_uri != null && strlen($redirect_uri) > 0) {
	$uri_cnt = $db->count("SEO_TAG_INFO","SEO_TAG_URL LIKE '%".$redirect_uri."%'");
	if ($uri_cnt > 0) {
		$seo_tag_info = getSeoTagInfo($db,$redirect_uri);
	}
}

function getNaverChannelInfo($db) {
	$naver_channel_info = array();
	
	$select_naver_channel_sql = "
		SELECT
			NC.CHANNEL_NAME			AS CHANNEL_NAME,
			NC.CHANNEL_URL			AS CHANNEL_URL
		FROM
			NAVER_CHANNEL NC
	";
	
	$db->query($select_naver_channel_sql);
	
	foreach($db->fetch() as $channel_data) {
		$naver_channel_info[] = array(
			'channel_name'		=>$channel_data['CHANNEL_NAME'],
			'channel_url'		=>xssDecode($channel_data['CHANNEL_URL'])
		);
	}
	
	return $naver_channel_info;
}

function getSeoTagInfo($db,$redirect_uri) {
	$seo_tag_info = array();
	
	$select_seo_tag_info_sql = "
		SELECT
			ST.SEO_TAG_TITLE		AS SEO_TAG_TITLE,
			ST.SEO_TAG_DESC			AS SEO_TAG_DESC,
			ST.SEO_TAG_AUTHOR		AS SEO_TAG_AUTHOR,
			ST.SEO_TAG_KEYWORD		AS SEO_TAG_KEYWORD
		FROM
			SEO_TAG_INFO ST
		WHERE
			SEO_TAG_URL LIKE '%".$redirect_uri."%'
	";
	
	$db->query($select_seo_tag_info_sql);
	
	foreach($db->fetch() as $tag_data) {
		$seo_tag_info[] = array(
			'seo_tag_title'		=>$tag_data['SEO_TAG_TITLE'],
			'seo_tag_desc'		=>$tag_data['SEO_TAG_DESC'],
			'seo_tag_author'	=>$tag_data['SEO_TAG_AUTHOR'],
			'seo_tag_keyword'	=>$tag_data['SEO_TAG_KEYWORD']
		);
	}
	
	return $seo_tag_info;
}

function xssDecode($param){
	$decode_result = "";
	
	if($param != null){
		$decode_result = str_replace("&amp;",	"&",	$param);
		$decode_result = str_replace("&quot;",	'"',	$decode_result);
		$decode_result = str_replace("&#039;",	"'",	$decode_result);
		$decode_result = str_replace("&lt;",	"<",	$decode_result);
		$decode_result = str_replace("&gt;",	">",	$decode_result);
	}
	
	return $decode_result;
}

?>