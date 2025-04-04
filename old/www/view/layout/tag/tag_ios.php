<?php
	$select_seo_tag_sql = "
		SELECT
			ST.TAG_MOBILE_URL		AS TAG_MOBILE_URL,
			ST.TAG_APP_STORE_ID		AS TAG_APP_STORE_ID,
			ST.TAG_APP_NAME			AS TAG_APP_NAME
		FROM
			SEO_TAG ST
		WHERE
			ST.SEO_TYPE = 'IOS'
	";
	
	$db->query($select_seo_tag_sql);
	
	foreach($db->fetch() as $tag_data) {
?>
	<meta property="al:ios:url"				content="<?=$tag_data['TAG_MOBILE_URL']?>">
	<meta property="al:ios:app_store_id"	content="<?=$tag_data['TAG_APP_STORE_ID']?>">
	<meta property="al:ios:app_name"		content="<?=$tag_data['TAG_APP_NAME']?>">
<?php
	}
?>