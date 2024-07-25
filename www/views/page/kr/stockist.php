<main class="stockist fix-header">
	<section class="search-tab">
		<form id="frm" class="search">
			<input type="text" name="keyword" placeholder="검색어를 입력하세요">
			<button type="button"></button>
		</form>
		<button type="button" class="location">
			<img src="/images/ico-location.svg"><label>현재 위치로 검색하기</label>
		</button>
	</section>
	<section class="tab">
		<div class="tab-container mobile">
			<ul>
				<li>Map</li>
				<li>List</li>
			</ul>
		</div>

		<section class="search">
			<div class="google-map">
				<div class="map" id="map"></div>
				<div class="zoom-button">
					<div class="zoom-in"></div>
					<div class="zoom-out"></div>
				</div>
			</div>
        </section>

		<section class="store-card">
			<h2>브랜드 스토어</h2>
			<article class="space">
				<h3>스페이스</h3>
				<dl id="space-info"></dl>
			</article>
			<article class="plug">
				<h3>플러그샵</h3>
				<dl id="plug-info"></dl>
			</article>
			<h2>스톡키스트</h2>
			<dl class="stockist" id="stockist-info"></dl>
		</section>
    </section>
</main>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCz2CF9odYuHKbrnPY2uFawVbvYOeqn65Y&region=kr"></script>
