<link rel="stylesheet" href="/css/user/main/login-faq.css">

<div class="inquiry__wrap" style="margin-bottom:500px;">
    <div class="inquiry_title" data-i18n="lm_faq">자주 묻는 질문</div>
    <div class="inquiry__tab inquiry__faq__wrap">
        <div class="search">
            <input class="search__keyword" type="text" placeholder="무엇을 도와드릴까요?" data-i18n-placeholder="inquiry_search">
            <img class="search__icon__img btn_search_faq" src="/images/mypage/mypage_search_icon.svg">
        </div>
        <div class="category"></div>
    </div>
	
    <div class="inquiry__tab inquiry__faq__detail__wrap">
		<div class="inquiry__faq__detail__container">
			<div class="inquiry__faq__detail__area">
				<div class="search__small">
					<input class="search__keyword" type="text" data-i18n-placeholder="inquiry_search">

					<img class="search__icon__img" src="/images/mypage/mypage_search_icon.svg">
					
					<div class="close init_keyword hidden">
						<img src="/images/mypage/tmp_img/X-12.svg" />
					</div>
				</div>

				<div class="pc__view">
					<div class="category__small"></div>
				</div>

				<div class="mobile__view">
					<div class="category__small__mobile">
						<div class="inquiry__category" style="width:100%;position:relative">
							<select id="inq_cate"></select>
						</div>
					</div>
				</div>
			</div>
			<div class="inquiry__faq__detail__area">
				<div class="toggle__list">
					<div class="toggle__list__tab 02"></div>
				</div>
			</div>
		</div>
		<div class="footer"></div>
	</div>
</div>

<script src="/scripts/member/login-faq.js"></script>