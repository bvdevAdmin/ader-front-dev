<link rel=stylesheet href='/css/product/detail.css' type='text/css'>
<link rel=stylesheet href='/css/module/styling.css' type='text/css'>
<link rel=stylesheet href='/css/module/foryou.css' type='text/css'>
<style>
main {overflow-x: initial;}
.quickview__box {display: none;}
</style>
<?php
function getUrlParamter($url, $sch_tag)
{
	$parts = parse_url($url);
	parse_str($parts['query'], $query);
	return $query[$sch_tag];
}

$page_url = $_SERVER['REQUEST_URI'];
$product_idx = getUrlParamter($page_url, 'product_idx');
?>
<style>
	.detail__wrapper .basket__wrap--btn{
			display: block;
	}
	.detail__wrapper .detail__btn__wrap{
		display: block;
	}
	.rM-detail-containner{
		display: none;
	}
	
	@media(max-width:1025px){
		.detail__wrapper .basket__wrap--btn{
			display: none;
		}
		.detail__wrapper .detail__btn__wrap{
			display: none;
		}
		.rM-detail-containner{
			display: block;
		}	
		.rM-detail-containner .detail__btn__wrap{
			-webkit-tap-highlight-color: transparent !important;
			transition: 0.3s all;
			grid-column: 1/8;
			margin-bottom: 200px;
			transform: translateX(10%);
			transition-duration: 1s;
			margin-bottom: 75px;
		}

		.rM-detail-containner .detail__btn__row .img-box {
			justify-content: center;
			width: 45px;
			height: 45px;
			flex-shrink: 0;
		}
		.rM-detail-containner .detail__btn__row .img-box.select {
			background-color: #000000;
		}
		.rM-detail-containner .detail__btn__row .img-box.select img{
			filter: var(--filter-wh);
		}
		.rM-detail-containner .detail__btn__wrap .detail__btn__row{
			gap: 10px;
		}
		.rM-detail-containner .detail__btn__control{
			position: absolute;
			bottom: 0;
			right: 0;
			padding: 16px;
			display: flex;
			flex-direction: column;
			gap: 30px;
			display: none;
		}
		.rM-detail-containner .detail__btn__control img{
			width: 12px;
			height: 18px;
		}
		.rM-detail-containner .detail-btn-prev{
			width: 45px;
			display: flex;
			align-items: center;
			justify-content: center;
			transform: rotate(90deg);
		}
		.rM-detail-containner .detail-btn-next{
			width: 45px;
			display: flex;
			align-items: center;
			justify-content: center;
			transform: rotate(270deg);
		}

		.rM-detail-containner .detail__btn__wrap .detail__btn__row.select{
			align-items: flex-start;
		}
		.rM-detail-containner .detail__btn__wrap.open{
			transition: 0.7s all;
			transform: translateX(0%);
			transition-duration: 1s;
			display: flex;
			border-top: 1px solid #dcdcdc;
			border-bottom: 1px solid #dcdcdc;

		}
		.rM-detail-containner .detail__btn__wrap.open .btn-title{
			display: none;
		}
		.rM-detail-containner .detail__btn__wrap.open .detail__content__box{
			max-height: 100%;
			width: 100%;
			position: relative;
			padding: 10px;
			border-left: 1px solid #dcdcdc;
			transition: none;
			padding-bottom: 65px;
		}
		.rM-detail-containner .detail__btn__wrap.open .detail__btn__control{
			display: flex;
		}
	}
	
</style>
<main data-productidx="<?= $product_idx ?>" data-country="KR">
	<div class="detail__sidebar__wrap">
		<div class="sidebar__background" data-modal="detail">
			<div class="sidebar__wrap" data-modal="detail">
				<div class="detail--box--btn">
					<div class="top" id="detail-top"></div>
					<div class="middle">
						<div class="detail__btn__wrap">
							<div class="detail__btn__row">
								<div class="img-box">
									<img src="/images/svg/sizeguide.svg" alt="">
								</div>
							</div>
							<div class="detail__btn__row">
								<div class="img-box select">
									<img src="/images/svg/material.svg" alt="">
								</div>
							</div>
							<div class="detail__btn__row">
								<div class="img-box">
									<img src="/images/svg/information.svg" alt="">
								</div>
							</div>
							<div class="detail__btn__row">
								<div class="img-box">
									<img src="/images/svg/precaution.svg" alt="">
								</div>
							</div>
						</div>
					</div>
					<div class="bottom"></div>
				</div>
				<div class="sidebar__box" data-modal="detail">
					<div class="sidebar__header">
						<img class="sidebar__close__btn" src="/images/svg/close.svg" alt="">
					</div>
					<div class="sidebar__body"></div>
				</div>
			</div>
		</div>
	</div>
	<section class="detail__wrapper">
		<div class="detail__box">
			<div class="navigation__wrap"></div>
			<div class="detail__img__wrap">
				<div id="main__swiper-detail" class="main__swiper swiper">
					<div class="swiper-wrapper main_img_wrapper"></div>
					<div class="swiper-pagination-detail-fraction"></div>
					<div class="swiper-pagination-detail-bullets"></div>
				</div>
			</div>
		</div>
		<div class="info__wrap product"></div>
		
	</section>
	<section class="rM-detail-containner">
		<div class="detail__btn__wrap">
			<div class="detail__btn__box">
				<div class="detail__btn__row mobile">
					<div class="img-box">
						<img src="/images/svg/sizeguide.svg" alt="">
					</div>
					<div class="btn-title" data-i18n="pd_size_guide">사이즈가이드</div>
				</div>
				<div class="detail__btn__row mobile">
					<div class="img-box">
						<img src="/images/svg/material.svg" alt="">
					</div>
					<div class="btn-title" data-i18n="pd_material">소재</div>
				</div>
				<div class="detail__btn__row mobile">
					<div class="img-box">
						<img src="/images/svg/information.svg" alt="">
					</div>
					<div class="btn-title" data-i18n="pd_details">상세정보</div>
				</div>
				<div class="detail__btn__row mobile">
					<div class="img-box">
						<img src="/images/svg/precaution.svg" alt="">
					</div>
					<div class="btn-title" data-i18n="pd_care">취급 유의사항</div>
				</div>
			</div>
			<div class="detail__content__box">
				<div class="detail-content precaution">
					<div class="content-header"><span>제품 취급 유의사항</span></div>
					<div class="content-body">
						<div class="content-list">
							<ul>
								<li>이 제품은 반드시 손세탁 하십시오.</li>
								<li>드라이클리닝을 하지 마십시오.</li>
								<li>이 제품은 회전식 건조기를 사용하지 마십시오.</li>
								<li>중온의 아이론을 권장합니다.</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="detail__btn__control">
					<div class="detail-btn-prev"><img src="/images/svg/arrow-left.svg" alt=""></div>
					<div class="detail-btn-next"><img src="/images/svg/arrow-left.svg" alt=""></div>
				</div>
			</div>
		</div>
	</section>
	<!-- <aside class="style__wrapper">
		<div class="left__title"><span>Styling with ></span></div>
		<div class="style-wrap">
			<div class="style-swiper swiper">
			</div>
		</div>
	</aside> -->
	<section class="styling-with-wrap"></section>
	<section class="recommend-wrap"></section>
</main>
<script>
	window.addEventListener("DOMContentLoaded",function(){
		mobileDetailBtnHanddler();
		
		
	});
	function mobileDetailBtnHanddler() {
		let $$btn = document.querySelectorAll(".rM-detail-containner .detail__btn__row");
		let controllBtn = document.querySelector(".rM-detail-containner .detail__btn__control");
		let prevBtn = document.querySelector(".rM-detail-containner .detail-btn-prev");
		let nextBtn = document.querySelector(".rM-detail-containner .detail-btn-next");
		const productDetailInfoArr = getProductDetailInfo(4);
		let currentIdx = 0;
		$$btn.forEach((btn , idx) => {
			btn.addEventListener("click", function(e){
				if(e.currentTarget.classList.contains("select")){
					e.currentTarget.offsetParent.classList.remove("open");
					e.currentTarget.classList.remove("select");
				}else {
					$$btn.forEach(el => el.classList.remove("select"));
					btn.classList.add("select");
					e.currentTarget.offsetParent.classList.add("open");
				}
				currentIdx = clickControllBtnEvent();
				updateControllBtnCss(idx);
				mobileSizeGuideContentBody(idx);
			})
		});

		prevBtn.addEventListener("click", function(e){
			if(currentIdx == 0) {return false;}
			console.log(currentIdx--);
			updateSelectElem(currentIdx);
			updateControllBtnCss(currentIdx);
			mobileSizeGuideContentBody(currentIdx);
		});
		nextBtn.addEventListener("click", function(e){
			if(currentIdx == 4){return false;}
			console.log(currentIdx++);
			updateSelectElem(currentIdx);
			updateControllBtnCss(currentIdx);
			mobileSizeGuideContentBody(currentIdx);
		});

		function updateSelectElem(current){
			$$btn.forEach(el => el.classList.remove("select"));
			document.querySelectorAll(".rM-detail-containner .detail__btn__row")[current].classList.add("select");
		}
		//컨트롤러 버튼 css 갱신
		function updateControllBtnCss(idx) {
			let prevBtn = document.querySelector(".rM-detail-containner .detail-btn-prev");
			let nextBtn = document.querySelector(".rM-detail-containner .detail-btn-next");
			if(idx == 0){
				prevBtn.style.opacity = "0";
			}else if(idx == 3){
				nextBtn.style.opacity = "0";
			} else{
				nextBtn.style.opacity = "inherit";
				prevBtn.style.opacity = "inherit";
			}
		}
		//선택되어있는 idx불러오기
		function clickControllBtnEvent(){
			let currentIdx;
			[...$$btn].find((el , idx)=> {
				if(el.classList.contains("select")){currentIdx = idx;}
			});
			return currentIdx;
		}
		function mobileSizeGuideContentBody(idx){
			let contentHeader = document.querySelector(".rM-detail-containner .content-header span");
			let contentBody = document.querySelector(".rM-detail-containner .content-body");
			contentBody.innerHTML = productDetailInfoArr[idx];
			if(idx == 0 ){
				contentHeader.innerHTML = "사이즈가이드";
			} else if(idx == 1){
				contentHeader.innerHTML = "소재";
			} else if(idx == 1){
				contentHeader.innerHTML = "제품 상세 정보";
			} else if(idx == 1){
				contentHeader.innerHTML = "취급 유의 사항";
			}
		}
	}
	const getProductDetailInfo = (product_idx) => {
		const main = document.querySelector("main");
		let country = main.dataset.country;
		let sizeGuideArr = new Array();
		$.ajax({
			type: "post",
			data: {
				"product_idx": product_idx,
				"country": getLanguage()
			},
			async:false,
			dataType: "json",
			url: "/_api/product/get",
			error: function () {
				alert("상품 진열 페이지 불러오기 처리에 실패했습니다.");
			},
			success: function (d) {
				let {sizeGuide,care,detail,material} = d.data[0];
				sizeGuide = `
				<div class="sizeguide-box">
                        <div class="sizeguide-btn ">A1</div>
                        <div class="sizeguide-btn">A2</div>
                        <div class="sizeguide-btn select">A3</div>
                        <div class="sizeguide-btn">A4</div>
                        <div class="sizeguide-btn">A5</div>
                    </div>
                    <div class="sizeguide-noti" data-i18n="pd_model_msg_01">모델신장 179cm,착용사이즈는 A3입니다.</div>
                    <div class="sizeguide-img" style="background-image: url('/images/svg/guide-top.svg');"></div>
                    <div class="sizeguide-dct">
                        <div class="dct-row">
                            <span>A.총장</span>
                            <span>옆목점에서 끝단까지의 수직길이</span>
                            <span class="dct-value">103.5</span>
                        </div>
                        <div class="dct-row">
                            <span>B. 목너비</span>
                            <span>옆목점 양끝의 수평길이</span>
                            <span class="dct-value">103.5</span>
                        </div>
                        <div class="dct-row">
                            <span>C. 어깨너비</span>
                            <span>옆어깨점 양끝의 수평길이</span>
                            <span class="dct-value">103.5</span>
                        </div>
                        <div class="dct-row">
                            <span>D. 가슴단면</span>
                            <span>암홀점에서 1cm아래 양끝의 수평길이</span>
                            <span class="dct-value">103.5</span>
                        </div>
                        <div class="dct-row">
                            <span>E. 소매통</span>
                            <span>암홀점에서 반대 소매면까지의 수직길이옆목점에서 끝단까지의 수직길이</span>
                            <span class="dct-value">103.5</span>
                        </div>
                        <div class="dct-row">
                            <span>F. 소매장</span>
                            <span>어깨점부터 소매끝단까지의 길이</span>
                            <span class="dct-value">103.5</span>
                        </div>
                    </div>
				</div>`

				sizeGuideArr.push(sizeGuide);
				sizeGuideArr.push(material);
				sizeGuideArr.push(detail);
				sizeGuideArr.push(care);
			}
		});
		return sizeGuideArr;
	}
	



</script>
<script src="/scripts/product/detail.js"></script>
<script>
	const foryou = new ForyouRender();
	let main = document.querySelector("main");
    let country = main.dataset.country;
	let urlParams = new URL(location.href).searchParams;
	let productIdx = urlParams.get('product_idx');
    $.ajax({
        type: "post",
        data: {
            "product_idx": productIdx,
            "country": getLanguage()
        },
        dataType: "json",
        url: "/_api/product/get",
        error: function () {
            alert("상품 진열 페이지 불러오기 처리에 실패했습니다.");
        },
        success: function (d) {
			let relevant_idx = d.data[0].relevant_idx;
			const styling = new StylingRender(relevant_idx);
		}
	})
</script> 
	