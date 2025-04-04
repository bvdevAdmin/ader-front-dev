<?php
	$select_seo_tag_sql = "
		SELECT
			ST.TAG_CARD			AS TAG_CARD,
			ST.TAG_TITLE		AS TAG_TITLE,
			ST.TAG_DESC			AS TAG_DESC,
			ST.TAG_IMG			AS TAG_IMG
		FROM
			SEO_TAG ST
		WHERE
			ST.SEO_TYPE = 'TWITTER'
	";
	
	$db->query($select_seo_tag_sql);
	
	foreach($db->fetch() as $tag_data) {
?>
	<meta property="twitter:card"			content="<?=$tag_data['TAG_CARD']?>">
	<meta property="twitter:title"			content="<?=$tag_data['TAG_TITLE']?>">
	<meta property="twitter:description"	content="<?=$tag_data['TAG_DESC']?>">
	<meta property="twitter:image"			content="<?=$tag_data['TAG_IMG']?>">
<?php
	}
?>