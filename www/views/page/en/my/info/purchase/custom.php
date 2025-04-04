<main class="my">
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/info">Account</a></li>
			<li><a href="/en/my/info/purchase">Purchase customize</a></li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="info purchase">
		<h1>Purchase customize</h1>
		<article>
			<ul class="dot">
			<li>Please provide the size information to choose the size before purchasing.</li>
			</ul>
			<form id="frm" type="post">
				<div class="gender-field grid col-2 gap-20">
					<div>
						<label class="check">
							<input id="gender_F" type="radio" name="gender" value="F" checked><i></i>Female
						</label>
					</div>
					<div>
						<label class="check">
							<input id="gender_M" type="radio" name="gender" value="M"><i></i>Male
						</label>
					</div>
				</div>
				<div class="grid col-2 gap-20">
					<div>
						<div class="form-inline inline-label">
							<span class="unit">cm</span>
							<input id="height" type="number" name="height" placeholder=" ">
							<span class="control-label">Height</span>
						</div>
					</div>
					<div>
						<div class="form-inline inline-label">
							<span class="unit">kg</span>
							<input id="weight" type="number" name="weight" placeholder=" ">
							<span class="control-label">Weight</span>
						</div>
					</div>
				</div>
				<div class="form-inline topsize">
					<ul class="checkbox div_upper">
						
					</ul>
					<span class="control-label">Upper size <small>(can duplicate)</small></span>
				</div>
				<div class="form-inline bottomsize">
					<ul class="checkbox div_lower">
						
					</ul>
					<span class="control-label">Lower size <small>(can duplicate)</small></span>
				</div>
				<div class="form-inline shoesize-field">
					<div class="grid gap-15 div_shoes">
						
					</div>
					<span class="control-label">Shoes size<small>(can duplicate)</small></span>
				</div>
				<div class="buttons">
					<button type="button" class="black btn_put">Save</button>
				</div>
			</form>
		</article>
	</section>
</main>