<main class="my">
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/info">내 정보 관리</a></li>
			<li><a href="/my/info/purchase">구매 맞춤 정보</a></li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="info purchase">
		<h1>구매 맞춤 정보</h1>
		<article>
			<ul class="dot">
				<li>구매 전 사이즈를 선택할 수 있도록 사이즈 정보를 제공해 주세요.</li>
			</ul>
			<form id="frm">
				<div class="gender-field grid col-2 gap-20">
					<div>
						<label class="check">
							<input type="radio" name="gender" value="여성"><i></i>여성
						</label>
					</div>
					<div>
						<label class="check">
							<input type="radio" name="gender" value="남성"><i></i>남성
						</label>
					</div>
				</div>
				<div class="grid col-2 gap-20">
					<div>
						<div class="form-inline inline-label">
							<span class="unit">cm</span>
							<input type="number" name="height" placeholder=" ">
							<span class="control-label">키</span>
						</div>
					</div>
					<div>
						<div class="form-inline inline-label">
							<span class="unit">kg</span>
							<input type="number" name="weight" placeholder=" ">
							<span class="control-label">몸무게</span>
						</div>
					</div>
				</div>
				<div class="form-inline topsize">
					<ul class="checkbox">
						<li>
							<label>
								<input type="checkbox" name="topsize" value="A1"><span>A1</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="topsize" value="A2"><span>A2</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="topsize" value="A3"><span>A3</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="topsize" value="XS"><span>XS</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="topsize" value="S"><span>S</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="topsize" value="M"><span>M</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="topsize" value="L"><span>L</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="topsize" value="XL"><span>XL</span>
							</label>
						</li>
					</ul>
					<span class="control-label">상의사이즈 <small>(중복선택가능)</small></span>
				</div>
				<div class="form-inline bottomsize">
					<ul class="checkbox">
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="A1"><span>A1</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="A2"><span>A2</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="A3"><span>A3</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="A4"><span>A4</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="A5"><span>A5</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="XS"><span>XS</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="S"><span>S</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="M"><span>M</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="L"><span>L</span>
							</label>
						</li>
						<li>
							<label>
								<input type="checkbox" name="bottomsize" value="XL"><span>XL</span>
							</label>
						</li>
					</ul>
					<span class="control-label">하의사이즈 <small>(중복선택가능)</small></span>
				</div>
				<div class="form-inline shoesize-field">
					<div class="grid gap-15">
						<label><input type="checkbox" name="topsize" value="A1"><i></i>230 / 36 / UK 3.5</label>
						<label><input type="checkbox" name="topsize" value="A1"><i></i>240 / 37 / UK 4.5</label>
						<label><input type="checkbox" name="topsize" value="A1"><i></i>250 / 40 / UK 6</label>
						<label><input type="checkbox" name="topsize" value="A1"><i></i>260 / 41 / UK 7</label>
						<label><input type="checkbox" name="topsize" value="A1"><i></i>270 / 42 / UK 8</label>
						<label><input type="checkbox" name="topsize" value="A1"><i></i>280 / 43 / UK 9</label>
						<label><input type="checkbox" name="topsize" value="A1"><i></i>290 / 44 / UK 10</label>
					</div>
					<span class="control-label">신발사이즈<small>(중복선택가능)</small></span>
				</div>
				<div class="buttons">
					<button type="submit" class="black">저장</button>
				</div>
			</form>
		</article>
	</section>
</main>