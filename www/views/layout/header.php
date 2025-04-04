<!doctype html>
<html lang="ko">
<head>
    <title>아더에러</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
	<meta name="HandheldFriendly" content="true">
    <meta name="format-detection" content="telephone=no">
    <meta name="Author" content="">
    <meta name="Keywords" content="">
    <meta name="Description" content="">
	<meta property="og:image" content="/images/og-image.png" />
	<meta property="og:title" content="아더에러">
	<meta property="og:description" content="">
	<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">

	<link rel="shortcut icon" href="/favicon.ico">
	
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="stylesheet" href="https://d13fzx7h5ezopb.cloudfront.net/fonts/font.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
	<link rel="stylesheet" href="/css/notosans/font.css">
	<link rel="stylesheet" href="/css/futura/font.css">
	<link rel="stylesheet" href="/css/layout.css?v=<?=time()?>" />
	<link rel="stylesheet" href="/css/contents.css?v=<?=time()?>" />
	<link rel="stylesheet" href="/css/responsive.css?v=<?=time()?>" />
	<link rel="stylesheet" href="/css/collaboration.css?v=<?=time()?>" />

	<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
	<script src="//code.jquery.com/jquery-latest.min.js"></script>
	<script src="/scripts/jquery.lazy-master/jquery.lazy.min.js"></script>
	<script src="/scripts/jquery.lazy-master/jquery.lazy.plugins.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
	<script src="/scripts/function.js"></script>
	<script src="/scripts/helix.js"></script>
	<script src="https://apis.google.com/js/platform.js" async defer></script>
	<script src="/scripts/turn.min.js"></script>
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.js"></script>
	<script src="https://developers.kakao.com/sdk/js/kakao.js"></script>
	<script src="https://static.nid.naver.com/js/naveridlogin_js_sdk_2.0.2.js" charset="utf-8"></script>

	<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
	<script src="//d1p7wdleee1q2z.cloudfront.net/post/search.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/scripts/static/postcodify-master/api/search.css" media="all" />

	<script src="https://js.tosspayments.com/v1/payment-widget"></script>
	
	<!-- 드림 시큐리티 개발 URL -->
	<script src="https://scert.mobile-ok.com/resources/js/index.js"></script>
	<!--
	드림 시큐리티 운영 URL
	<script src="https://cert.mobile-ok.com/resources/js/index.js"></script>
	-->

	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCz2CF9odYuHKbrnPY2uFawVbvYOeqn65Y&region=kr"></script>

	<link rel="stylesheet" href="/scripts/static/toast-selectbox/toastui-select-box.min.css"/>
	<script src="/scripts/static/toast-selectbox/toastui-select-box.min.js"></script>
</head>

<body class="<?=(isset($_SESSION['MEMBER_ID']))?'loged':''?> --loading">
