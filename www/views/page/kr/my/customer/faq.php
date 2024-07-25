<main class="my">
    <?php include '../_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/customer">고객센터</a></li>
			<li><a href="/kr/my/customer/faq">자주 묻는 질문</a></li>
		</ul>
	</nav>
	<section class="faq">
		<article>
			<h1>자주 묻는 질문</h1>
			<div class="searchform">
				<form id="frm" class="search">
					<input type="text" name="keyword" placeholder="무엇을 도와드릴까요?">
					<button type="button">clear</button>
				</form>
			</div>
			<div class="category">
				<ul id="faq-category"></ul>
			</div>
			<div class="contents" id="faq-contents"></div>
		</article>
	</section>
</main>