<main class="goods detail">
	<section class="cont">
		<section class="gallery">
			<article>
				<div id="images-paging"></div>
				<ul id="images"></ul>
				<div class="swiper-container" id="images-swiper">
					<div class="swiper-wrapper"></div>
					<div class="swiper-paging"></div>
					<div class="swiper-pagination"></div>
				</div>
			</article>
		</section>
		<section class="information">
			<section class="cont">
				<article class="goods">
					<form id="frm-goods">
						<input type="hidden" name="goods_no">
						<button type="button" class="favorite"></button>
						<h1></h1>
						<p class="price"></p>
						<div class="option hidden" id="option-color">
							<ul class="color"></ul>
						</div>
						<div class="option hidden" id="option-size">
							<dl class="size">
								<dt>Size</dt>
							</dl>
							<div class="sizeguide">
								<button type="button" id="btn-sizeguide">사이즈 가이드</button>
							</div>
						</div>
						<div class="option" id="option-set"></div>
						<div class="buttons" id="buy-buttons">
							<button type="submit" class="no-over">쇼핑백에 담기</button>
						</div>
					</form>
				</article>
				<article class="detail">
					<dl id="details">
						<dt class="material"><button type="button">소재</button></dt>
						<dd>
							<div class="cont">
								<div class="body"></div>
							</div>
						</dd>
						<dt class="info"><button type="button">상세 정보</button></dt>
						<dd>
							<div class="cont">
								<div class="body"></div>
							</div>
						</dd>
						<dt class="warning"><button type="button">취급 유의사항</button></dt>
						<dd>
							<div class="cont">
								<div class="body"></div>
							</div>
						</dd>
					</dl>
				</article>
			</section>
			<section class="sizeguide-cont" id="sizeguide-cont">
				<header>
					<h2>사이즈 가이드</h2>
					<button type="button" class="close"></button>
				</header>
				<article></article>
			</section>
		</section>
	</section>
	<section class="swiper-row hidden" id="view-relation">
		<h2>함께 스타일링 된 아이템</h2>
		<article>
			<div class="swiper-container">
				<div class="swiper-wrapper"></div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
		</article>
	</section>
	<section class="swiper-row hidden" id="view-recommend">
		<h2>추천 제품</h2>
		<article>
			<div class="swiper-container">
				<div class="swiper-wrapper"></div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
		</article>
	</section>
</main>
