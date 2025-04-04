<main class="my">
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/info">Account</a></li>
			<li>Edit account</li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="info modify">
		<h1>Edit account</h1>
		<article class="input">
			<form id="frm">
				<div class="form-inline">
					<big class="val" id="member-name"></big>
					<span class="control-label">Name</span>
				</div>
				<div class="form-inline no-margin">
					<div class="val" id="member-email"></div>
					<span class="control-label">E-mail</span>
				</div>
				<div class="form-inline" no-margin>
					<big class="val">· · · · · · · · · · </big>
					<span class="control-label">Password</span>
					<button type="button" id="btn-change-pw">Change password</button>
				</div>
				<div class="form-inline" no-margin>
					<div class="val" id="member-tel"></div>
					<span class="control-label">Mobile number</span>
				</div>
				<div class="form-inline" no-margin>
					<div class="val" id="member-birthday"></div>
					<span class="control-label">Birth</span>
				</div>
				<?php
					if ($_SESSION['AUTH_FLG'] != true) {
				?>
				<div class="buttons">
					<button type="button" class="black btn_auth">본인인증 하기</button>
				</div>
				<?php
					}
				?>
			</form>
		</article>
		<a href="/en/my/info/dropout" class="btn gray">Dropout</a>
	</section>
</main>