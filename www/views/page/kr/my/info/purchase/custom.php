<main class="my">
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/info">내 정보 관리</a></li>
			<li><a href="/kr/my/info/purchase">구매 맞춤 정보</a></li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="info purchase">
		<h1>구매 맞춤 정보</h1>
		<article>
			<ul class="dot">
				<li>구매 전 사이즈를 선택할 수 있도록 사이즈 정보를 제공해 주세요.</li>
			</ul>
			<form id="frm" type="post">
				<div class="gender-field grid col-2 gap-20">
					<div>
						<label class="check">
							<input id="gender_F" type="radio" name="gender" value="F" checked><i></i>여성
						</label>
					</div>
					<div>
						<label class="check">
							<input id="gender_M" type="radio" name="gender" value="M"><i></i>남성
						</label>
					</div>
				</div>
				<div class="grid col-2 gap-20">
					<div>
						<div class="form-inline inline-label">
							<span class="unit">cm</span>
							<input id="height" type="number" name="height" placeholder=" ">
							<span class="control-label">키</span>
						</div>
					</div>
					<div>
						<div class="form-inline inline-label">
							<span class="unit">kg</span>
							<input id="weight" type="number" name="weight" placeholder=" ">
							<span class="control-label">몸무게</span>
						</div>
					</div>
				</div>
				<div class="form-inline topsize">
					<ul class="checkbox div_upper">
						
					</ul>
					<span class="control-label">상의사이즈 <small>(중복선택가능)</small></span>
				</div>
				<div class="form-inline bottomsize">
					<ul class="checkbox div_lower">
						
					</ul>
					<span class="control-label">하의사이즈 <small>(중복선택가능)</small></span>
				</div>
				<div class="form-inline shoesize-field">
					<div class="grid gap-15 div_shoes">
						
					</div>
					<span class="control-label">신발사이즈<small>(중복선택가능)</small></span>
				</div>
				<div class="buttons">
					<button type="button" class="black btn_put">저장</button>
				</div>
			</form>
		</article>
	</section>
</main>