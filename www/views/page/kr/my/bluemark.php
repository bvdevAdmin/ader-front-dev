<main class="my">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/bluemark">Bluemark</a></li>
		</ul>
	</nav>
	<section class="bluemark wrap-480">
		<h1>Bluemark</h1>
		<article>
			<p>Bluemark는 본 브랜드의 모조품으로부터 소비자의 혼란을 최소화하기 위해 제공되는 정품 인증 서비스입니다.</p>
			<p>ADER는 모조품 판매를 인지하고 소비자와 브랜드의 이미지를 보호하기 위하여 적극적으로 대응중입니다.</p>
			
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>인증</li>
						<li>내역</li>
					</ul>
				</div>
				<section>
					<form id="frm-bluemark-regist">
						<div class="form-inline height45">
							<select name="store_no">
								<option>공식 온라인 스토어</option>
								<option>공식 오프라인 스토어</option>
								<option>W컨셉</option>
								<option>카카오</option>
							</select>
							<div class="control-label">구매처</div>
						</div>
						<div class="form-inline height45">
							<input type="text" name="bluemark" placeholder="택에 동봉된 블루 커버 내 시리얼 코드 입력" required>
							<div class="control-label">Bluemark 시리얼 코드</div>
						</div>
						<div class="buttons">
							<button type="submit" class="blue no-over">VERIFY</button>
						</div>
					</form>
				</section>
				<section>
					<ul class="list" id="list"></ul>
				</section>
			</div>			
		</article>
	</section>
</main>