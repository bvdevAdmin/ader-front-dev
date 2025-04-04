<?php
	$select_seo_tag_sql = "
		SELECT
			ST.TAG_TITLE		AS TAG_TITLE,
			ST.TAG_WEB_URL		AS TAG_WEB_URL,
			ST.TAG_TYPE			AS TAG_TYPE,
			ST.TAG_IMG			AS TAG_IMG,
			ST.TAG_SUB_TITLE	AS TAG_SUB_TITLE,
			ST.TAG_DESC			AS TAG_DESC
		FROM
			SEO_TAG ST
		WHERE
			ST.SEO_TYPE = 'KAKAO'
	";
	
	$db->query($select_seo_tag_sql);
	
	foreach($db->fetch() as $tag_data) {
?>
	<meta property="og:title"				content="<?=$tag_data['TAG_TITLE']?>">
	<meta property="og:url"					content="<?=$tag_data['TAG_WEB_URL']?>">
	<meta property="og:type"				content="<?=$tag_data['TAG_TYPE']?>">
	<meta property="og:image"				content="<?=$tag_data['TAG_IMG']?>">
	<meta property="og:title"				content="<?=$tag_data['TAG_SUB_TITLE']?>">
	<meta property="og:description"			content="<?=$tag_data['TAG_DESC']?>">
<?php
	}
?>
