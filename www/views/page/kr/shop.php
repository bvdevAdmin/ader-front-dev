<main class="goods list fix-header">
	<header>
		<section class="banner"></section>
		<nav>
			<section class="category">
				<div class="swiper-container" id="goods-category">
					<ul class="swiper-wrapper"></ul>
					<button type="button" class="swiper-button-prev"></button>
					<button type="button" class="swiper-button-next"></button>
				</div>
			</section>
			<section class="nav" id="goods-nav"></section>
			<section class="tools">
				<ul>
					<!--
					<li>
						<button type="button" class="sort">
							<span class="icon"></span>
							정렬
						</button>
						<div class="dropdown">
							<ul id="shoplist-tools-sort">
								<li><label><input type="radio" name="sort" value="인기"><i></i>인기순</label></li>
								<li><label><input type="radio" name="sort" value="신상품"><i></i>신상품순</label></li>
								<li><label><input type="radio" name="sort" value="낮은가격"><i></i>낮은 가격순</label></li>
								<li><label><input type="radio" name="sort" value="높은가격"><i></i>높은 가격순</label></li>
							</ul>
						</div>
					</li>
					-->
					<li>
						<button type="button" class="filter">
							<span class="icon">
								<span class="--cont">
									<span class="--line"></span>
									<span class="--line"></span>
									<span class="--line"></span>
								</span>
							</span>
							필터
						</button>
						<div class="dropdown" id="shoplist-tools-filter">
							<header>
								<img src="/images/ico-filter.svg">정렬 / 필터
								<button type="button" class="close"></button>
							</header>
							<div class="buttons">
								<button type="button" class="reset no-over">초기화</button>
								<button type="button" class="black"><span>0</span> 개의 제품보기</button>
							</div>
							<div class="grid">
								<ul>
									<li>
										<h3>정렬</h3>
										<div class="sort">
											<label><input type="checkbox" name="sort" value=""><i></i>인기순</label>
											<label><input type="checkbox" name="sort" value=""><i></i>낮은 가격순</label>
											<label><input type="checkbox" name="sort" value=""><i></i>신상품순</label>
											<label><input type="checkbox" name="sort" value=""><i></i>높은 가격순</label>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</li>
					<li>
						<button type="button" class="showing">
							<span class="icon"></span>
							<span class="off">아이템</span>
							<span class="on">착용컷</span>
						</button>
					</li>
					<li>
						<button type="button" class="column">
							<span class="icon"></span>
							<span class="off">2칸 보기</span>
							<span class="on">4칸 보기</span>
						</button>
					</li>
				</ul>
			</section>
		</nav>
	</header>
	<section class="list">
		<ul id="list"></ul>
	</section>
</main>
