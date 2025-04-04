<main class="goods list has-header">
	<header>
		<section class="banner"></section>
		<nav>
<!--            <section class="nav" id="goods-nav"></section>-->
			<section class="category">
				<div class="swiper-container" id="goods-category">
					<ul class="swiper-wrapper"></ul>
					<button type="button" class="swiper-button-prev"></button>
					<button type="button" class="swiper-button-next"></button>
				</div>
			</section>
            <section class="tools">
				<ul>
					<li>
						<button type="button" class="filter">
							<span class="icon">
								<span class="--cont">
									<span class="--line"></span>
									<span class="--line"></span>
									<span class="--line"></span>
								</span>
							</span>
							filter
						</button>
						<div class="dropdown" id="shoplist-tools-filter">
							<header>
								<img src="/images/ico-filter.svg">Order / Filter
								<button type="button" class="close"></button>
							</header>
							<div class="buttons">
								<button type="button" class="reset no-over">Init</button>
								<button type="button" class="black btn_filter">Show <span class="cnt_filter">0</span> items</button>
							</div>
							<div class="grid">
								<ul>
									<li>
										<h3>Order</h3>
										<div class="sort">
										<label><input class="param_sort" type="radio" name="sort" value="POP"><i></i>Popular</label>
											<label><input class="param_sort" type="radio" name="sort" value="MIN"><i></i>Low price</label>
											<label><input class="param_sort" type="radio" name="sort" value="NEW"><i></i>New</label>
											<label><input class="param_sort" type="radio" name="sort" value="MAX"><i></i>High price</label>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</li>
					<li>
						<button type="button" class="showing">
							<span class="icon"></span>
							<span class="off">Item</span>
							<span class="on">Outfit</span>
						</button>
					</li>
					<li>
						<button type="button" class="column">
							<span class="icon"></span>
							<span class="off">2 grid</span>
							<span class="on">4 grid</span>
						</button>
					</li>
				</ul>
			</section>
		</nav>
	</header>
	<section class="list">
		<ul id="list"></ul>
	</section>
	<div class="div_top">
		<button type="button" class="to-top"></button>
	</div>
</main>
