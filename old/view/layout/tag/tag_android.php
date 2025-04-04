<?php
	$select_seo_tag_sql = "
		SELECT
			ST.TAG_MOBILE_URL	AS TAG_MOBILE_URL,
			ST.TAG_APP_NAME		AS TAG_APP_NAME,
			ST.TAG_PACKAGE		AS TAG_PACKAGE,
			ST.TAG_WEB_URL		AS TAG_WEB_URL
		FROM
			SEO_TAG ST
		WHERE
			ST.SEO_TYPE = 'ANDROID'
	";
	
	$db->query($select_seo_tag_sql);
	
	foreach($db->fetch() as $tag_data) {
?>
	<meta property="al:android:url"			content="<?=$tag_data['TAG_MOBILE_URL']?>">
	<meta property="al:android:app_name"	content="<?=$tag_data['TAG_APP_NAME']?>">
	<meta property="al:android:package"		content="<?=$tag_data['TAG_PACKAGE']?>">
	<meta property="al:web:url"				content="<?=$tag_data['TAG_WEB_URL']?>">
<?php
	}
?>