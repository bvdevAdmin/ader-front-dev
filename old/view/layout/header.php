<!DOCTYPE html>
<html lang="ko">
	<?php
		include_once("/var/www/dev-tmp/www/view/layout/tag/tag_setting.php");
	?>
	
	<head>
		<!-- Google Tag Manager -->
		<script>
			(
				function (w,d,s,l,i) {
					w[l] = w[l] || [];
					w[l].push({
						'gtm.start' : new Date().getTime(),
						event : 'gtm.js'
					});
					
					var f = d.getElementsByTagName(s)[0],
						j = d.createElement(s),
						dl = l != 'dataLayer' ? '&l=' + l : '';
						j.async = true;
					
					j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
					f.parentNode.insertBefore(j,f);
				}
			)(window,document,'script','dataLayer','GTM-NP3CSM8');
		</script>
		
		<!-- End Google Tag Manager -->
		
		<!-- Meta TAG -->
		<?php
			if ($seo_setting['tag_title'] != null) {
				echo "<title>".$seo_setting['tag_title']."</title>";
			}
			
			if ($seo_setting['tag_desc'] != null) {
				echo '<meta name="description" content="'.$seo_setting['tag_desc'].'" />';
			}
			
			if ($seo_setting['favicon_location'] != null) {
				echo '<link rel="icon" href="'.$seo_setting['favicon_location'].'">';
			}
			
			if ($seo_setting['search_google'] != null) {
				echo $seo_setting['search_google'];
			}
			
			if ($seo_setting['search_naver'] != null) {
				echo $seo_setting['search_naver'];
			}
			
			if ($seo_setting['code_header'] != null) {
				echo $seo_setting['code_header'];
			}
			
			if ($seo_setting['sns_img_location'] != null) {
				$txt_meta_img = '<meta property="og:image" content="'.$seo_setting['sns_img_location'].'">';
				
				if ($seo_setting['card_flg'] == true) {
					$txt_meta_img .= '
						<meta property="twitter:card"			content="photo">
						<meta property="twitter:title"			content="'.$seo_setting['tag_title'].'">
						<meta property="twitter:description"	content="'.$seo_setting['tag_desc'].'">
						<meta property="twitter:image"			content="'.$seo_setting['sns_img_location'].'">
					';
				}
				
				echo $txt_meta_img;
			}
			
			$txt_seo_tag = "";
			if ($seo_tag_info != null && count($seo_tag_info) > 0) {
				for ($i=0; $i<count($seo_tag_info); $i++) {
					$txt_seo_tag .= '
						<meta http-equiv="Title"	content="'.$seo_tag_info[$i]['seo_tag_title'].'" />
						<meta name="Description"	content="'.$seo_tag_info[$i]['seo_tag_desc'].'" />
						<meta http-equiv="Author"	content="'.$seo_tag_info[$i]['seo_tag_author'].'" />
						<meta name="Keywords"		content="'.$seo_tag_info[$i]['seo_tag_keyword'].'" />
					';
				}
			}
			
			if (strlen($txt_seo_tag) > 0) {
				echo $txt_seo_tag;
			}
		?>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<meta name="Author" content="">
		<meta name="Keywords" content="">
		<meta name="facebook-domain-verification" content="" />
		<meta name ="google-signin-client_id" content="">
		<meta property="og:image" content="/images/og-image.png" />
		<meta property="og:title" content="">
		<meta property="og:description" content="">
		<meta name="googlebot" content="noindex, nofollow">
		<meta name="Yeti" content="noindex, nofollow" />
		
		<!-- Apple id -->
		<meta name="appleid-signin-client-id" content="[CLIENT_ID]">
		<meta name="appleid-signin-scope" content="[SCOPES]">
		<meta name="appleid-signin-redirect-uri" content="[REDIRECT_URI]">
		<meta name="appleid-signin-state" content="[STATE]">
		<meta name="appleid-signin-nonce" content="[NONCE]">
		<meta name="appleid-signin-use-popup" content="true">
		<meta name="google-signin-client_id" content="YOUR_CLIENT_ID.apps.googleusercontent.com">
		
		<meta http-equiv="Content-Type" content="application/json; charset=utf-8"/>
		<meta name="apple-mobile-web-app-capable" content="yes"/>
		<meta name="format-detection" content="telephone=no">
		<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
		
		<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
		<link rel="stylesheet" href="/scripts/static/jquery-ui.min.css" />
		<link rel="stylesheet" href="/scripts/static/jquery.minicolors.css">
		<link rel="stylesheet" href="/scripts/static/farbtastic/farbtastic.css">
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="//cdn.jsdelivr.net/xeicon/2/xeicon.min.css" rel="stylesheet">
		<link href="//cdn.jsdelivr.net/xeicon/1.0.4/xeicon.min.css" rel="stylesheet">
		<link href="/scripts/static/jquery.scrollbar.css" rel="stylesheet">
		<link rel="stylesheet" href="https://d13fzx7h5ezopb.cloudfront.net/fonts/font.css" />
		<link rel="stylesheet" href="/scripts/static/taggingJS/example/tag-basic-style.css" />
		<link rel="stylesheet" href="/scripts/static/toast-selectbox/toastui-select-box.min.css"/>
		<link rel=stylesheet href='/css/user/login.css' type='text/css'>
		<link rel=stylesheet href='/css/common/common.css' type='text/css'>
		<link rel=stylesheet href='/css/common/sidebar.css' type='text/css'>
		<link rel=stylesheet href='/css/common/footer.css' type='text/css'>
		<link rel=stylesheet href='/css/common/nav.css' type='text/css'>
		<link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/>
		<link rel="stylesheet" href="/css/common/basket.css">
		<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
		<link rel="alternate" type="application/rss+xml" title="adererror" href="/images/rss.xml" />
		<!-- 라이브러리 -->
		<script src="//cdn.jsdelivr.net/npm/hls.js@latest"></script>
		<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
		<script src="https://cdn.tailwindcss.com"></script>
		<script src="//code.jquery.com/jquery-latest.min.js"></script>
		<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
		<script src="//cdn.tiny.cloud/1/8hqw5yh8xbtwt4pm8v4989rj0osoy7jyes9s0kwkncucraz4/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
		<script type="text/javascript" src="//code.jquery.com/jquery.min.js"></script>		
		<script src="//code.jquery.com/jquery-latest.min.js"></script>
		<script src="/scripts/static/jquery-ui.min.js"></script>
		<script src="/scripts/static/jquery.minicolors.min.js"></script>
		<script src="/scripts/static/jquery.scrollbar.min.js"></script>
		<script src="/scripts/static/jquery.animateNumber.min.js"></script>
		<script src="/scripts/static/jquery.mask.min.js"></script>
		<script src="/scripts/static/jquery.caret.js"></script>
		<script src="/scripts/static/jquery.detectmobilebrowser.js"></script>
		<script src="/scripts/static/farbtastic/farbtastic.js"></script>
		<script src="/scripts/static/taggingJS/tagging.min.js"></script>
		<script src="/scripts/static/toast-selectbox/toastui-select-box.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
		<script src="https://apis.google.com/js/platform.js" async defer></script>
		<script src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>
		<script src="/scripts/static/postcodify-master/api/search.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>

		<script src="https://developers.kakao.com/sdk/js/kakao.js"></script>
		<script src="https://static.nid.naver.com/js/naveridlogin_js_sdk_2.0.2.js" charset="utf-8"></script>

		<!-- 공통 js -->
		<script src="/scripts/static/functions.js"></script>
		<script src="/scripts/functions.js?v=<?=time()?>"></script>
		<script src="/scripts/helix.js?v=<?=time()?>"></script>
		<script src="/scripts/common.js" ></script>
		
		<!-- SNS 로그인 스크립트 -->
		<?php
			//include_once("/var/www/dev-tmp/www/class/class.kakaoOAuth.php");
			include_once("/var/www/dev-tmp/www/class/class.naverOAuth.php");
			
			include_once("/var/www/dev-tmp/www/class/mail/class.PHPMailer.php");
			include_once("/var/www/dev-tmp/www/class/mail/class.SMTP.php");
		?>
		
		<script>
			function initLoginHandler(){
				$('.naver__btn').unbind('click');
				<?php
					$naver = new Naver();
					echo $naver->login();
				?>
				
				$('.kakao__btn').unbind('click');
				<?php
					$kakao_oauth			= "https://kauth.kakao.com/oauth/";
					$client_kakao			= "b43df682b08d3270e40a79b5c51506b5";
					$redirect_kakao			= urlencode("https://dev.adererror.com/kakao/login");
					
					$tmp_url = $kakao_oauth."authorize?client_id=".$client_kakao."&response_type=code&scope=account_email,name,phone_number,birthyear&redirect_uri=".$redirect_kakao."&response_type=code&','_blank','width=320,height=480";
					
					echo "
						$('.kakao__btn').click(function() {
							location.href = '".$tmp_url."';
						});
					";
				?>
				
				$('.google_btn').unbind('click');
				<?php
					$google_oauth			= "https://accounts.google.com/o/oauth2/v2/auth";
					$client_google			= "824115093434-0mj6bh3el4ndur8u9cglu3u0sojhub9f.apps.googleusercontent.com";
					$redirect_google		= urlencode("https://dev.adererror.com/google/login");
					$scope					= urlencode("https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email https://www.google.com/m8/feeds/");
					
					$tmp_url = $google_oauth."?client_id=".$client_google."&redirect_uri=".$redirect_google."&state=OK&scope=".$scope."&access_type=online&include_granted_scopes=true&response_type=code";
					
					echo "
						$('.google_btn').click(function() {
							location.href = '".$tmp_url."';
						});
					";
				?>
			}
		</script>
		
		<!-- 모듈 -->
		<script src="/scripts/member/login.js"></script>
		<script src="/scripts/module.js" ></script>
		<script src="/scripts/module/foryou.js" ></script>
		<script src="/scripts/module/wishlist.js" ></script>
		<script src="/scripts/module/styling.js" ></script>
		<script src="/scripts/module/pagination.js" ></script>
		<script src="/scripts/module/login-module.js" ></script>
		<script src="/scripts/module/search-popular.js" ></script>
		<script src="/scripts/module/bluemark.js" ></script>
		<script src="/scripts/module/user.js" ></script>
		<script src="/scripts/module/sidebar.js" ></script>
		<script src="/scripts/module/language.js" ></script>
		<script src="/scripts/module/basket-page.js" ></script>
		<script src="/scripts/module/basket-sidebar.js" ></script>
		
		<script src=" https://cdn.jsdelivr.net/npm/i18next@22.4.10/i18next.min.js "></script>
		
		<!-- SEO Setting -->
		<?php
			$txt_channel_info = "";
			
			if ($naver_channel_info != null && count($naver_channel_info) > 0) {
				
				for ($i=0; $i<count($naver_channel_info); $i++) {
					$txt_channel_info .= '"'.$naver_channel_info[$i]['channel_url'].'",';
				}
				
				if (strlen($txt_channel_info) > 0) {
					$txt_channel_info = substr($txt_channel_info,0,-1);
				}
			}
			
			if (strlen($txt_channel_info) > 0) {
				echo '
					<script type="application/ld+json">
						{
							"@context": "http://schema.org",
							"@type": "Organization",					//사이트 이름
							"name": "ADERERROR",						//사이트 URL
							"url": "",			//사이트와 연관된 채널 URL 목록
							"sameAs": [
								'.$txt_channel_info.'
							]
						}
					</script>
				';
			}
		?>
	</head>
	
	<body>
		<?php
			if ($seo_setting['code_body'] != null) {
				echo $seo_setting['code_body'];
			}
		?>
		
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NP3CSM8" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		
		<!-- End Google Tag Manager (noscript) -->
		<header>
			<?php include $_CONFIG['PATH']['PAGE'].'/components/nav.php';?>
		</header>
		
		<div id="dimmer"></div>