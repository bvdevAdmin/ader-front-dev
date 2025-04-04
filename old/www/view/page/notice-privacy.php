<?php
function getUrlParamter($url, $sch_tag)
{
	$parts = parse_url($url);
	parse_str($parts['query'], $query);
	return $query[$sch_tag];
}
$page_url = $_SERVER['REQUEST_URI'];
$notice_type = getUrlParamter($page_url, 'notice_type');
?>

<link rel="stylesheet" href="/css/notice/notice-privacy.css">

<main>
	<input type="hidden" id="notice_type" value="<?= $notice_type ?>">
	<div class="notice__privacy__wrap">
		<div class="title primary">
			<p data-i18n="lm_regulations">법적고지사항</p>
		</div>
		
		<div class="tab__wrap">
			<div class="tab__btn online_store selected" data-tab_type="online_store">
				<span data-i18n="lm_online_store_guide">온라인스토어 이용가이드</span>
			</div>
			
			<div class="tab__btn terms_of_use" data-tab_type="terms_of_use">
				<span data-i18n="lm_terms_and_conditions">이용약관</span>
			</div>
			
			<div class="tab__btn privacy_policy" data-tab_type="privacy_policy">
				<span data-i18n="lm_privacy_policy_02">개인정보처리방침</span>
			</div>
			
			<div class="tab__btn cookies_policy" data-tab_type="cookies_policy">
				<span data-i18n="lm_cookie_policy">쿠키정책</span>
			</div>
		</div>

		<div class="info__wrap online_store_info">
			<div class="title">
				<h2 data-i18n="lm_online_store_guide">온라인스토어 이용 가이드</h2>
			</div>
			<div class="info__scroll__wrap"></div>
		</div>

		<div class="info__wrap terms_of_use_info">
			<div class="title">
				<h2 data-i18n="lm_terms_and_conditions">이용약관</h2>
			</div>
			<div class="info__scroll__wrap"></div>
		</div>

		<div class="info__wrap privacy_policy_info">
			<div class="title">
				<h2 data-i18n="lm_privacy_policy_02">개인정보처리방침</h2>
			</div>
			<div class="info__scroll__wrap"></div>
		</div>

		<div class="info__wrap cookies_policy_info">
			<div class="title">
				<h2 data-i18n="lm_cookie_policy">쿠키정책</h2>
			</div>
			<div class="info__scroll__wrap"></div>
		</div>
	</div>
</main>

<script src="/scripts/notice/notice-privacy.js"></script>