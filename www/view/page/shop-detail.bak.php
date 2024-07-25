<main class="goods detail">
	<section class="cont">
		<section class="gallery">
			<nav>
				<ul id="thumbnails"></ul>
			</nav>
			<article>
				<ul id="images"></ul>
				<div class="swiper-container">
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
						<h1></h1>
						<p class="price"></p>
						<div class="option" id="option-color">
							<ul class="color"></ul>
						</div>
						<div class="option" id="option-size">
							<dl class="size">
								<dt>Size</dt>
							</dl>
						</div>
						<div class="buttons" id="buy-buttons">
							<button type="submit">쇼핑백에 담기</button>
							<button type="button" class="favorite"></button>
						</div>
					</form>
				</article>
				<article class="detail">
					<dl id="details">
						<dt class="sizeguide"><button type="button">사이즈 가이드</button></dt>
						<dd>
							<button type="button" class="close"></button>
							<div class="cont">
								<h3>사이즈 가이드</h3>
								<div class="body"></div>
							</div>
						</dd>
						<dt class="material"><button type="button">소재</button></dt>
						<dd>
							<button type="button" class="close"></button>
							<div class="cont">
								<h3>소재</h3>
								<div class="body"></div>
							</div>
						</dd>
						<dt class="info"><button type="button">상세 정보</button></dt>
						<dd>
							<button type="button" class="close"></button>
							<div class="cont">
								<h3>상세 정보</h3>
								<div class="body"></div>
							</div>
						</dd>
						<dt class="warning"><button type="button">취급 유의사항</button></dt>
						<dd>
							<button type="button" class="close"></button>
							<div class="cont">
								<h3>취급 유의사항</h3>
								<div class="body"></div>
							</div>
						</dd>
					</dl>
				</article>
			</section>
		</section>
	</section>
	<section class="swiper-row hidden" id="view-relation">
		<h2>Styling with</h2>
		<article>
			<div class="swiper-container">
				<div class="swiper-wrapper"></div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
		</article>
	</section>
	<section class="swiper-row hidden" id="view-recommend">
		<h2>For you</h2>
		<article>
			<div class="swiper-container">
				<div class="swiper-wrapper"></div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
		</article>
	</section>
</main>
