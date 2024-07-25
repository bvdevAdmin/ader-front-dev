<main class="my">
	<?php include 'inc/my.summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/customer">고객센터</a></li>
			<li><a href="/my/customer/qna">문의하기</a></li>
			<li><a>문의내역</a></li>
		</ul>
	</nav>
	<section class="qna wrap-720">
		<article>
			<h2>문의내역</h2>
			<div class="cont">
				<dl>
					<dt>작성 일시</dt>
					<dd></dd>
					<dt>문의 유형</dt>
					<dd></dd>
					<dt>첨부파일</dt>
					<dd></dd>
				</dl>
				<div class="buttons">
					<button type="button" class="modify">수정</button>
					<button type="button" class="delete">삭제</button>
				</div>
			</div>
			<div class="cont">
				<dl>
					<dt>답변 일시</dt>
					<dd>
						-<br>
						<br>
						빠른 시간 내에 답변드리겠습니다.<br>잠시만 기다려 주세요.
					</dd>
				</dl>
			</div>
			<div class="buttons">
				<a href="/my/customer/qna" class="btn">문의 내역 보기</a>
			</div>
		</article>
	</section>
</main>