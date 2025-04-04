<header>
	<section class="notice">
		<button type="button" class="prev"></button>
		<button type="button" class="next"></button>
		<button type="button" class="close KR"></button>
		<article class="noti">
			<p>CS 및 배송시스템 개편 안내</p>
			<a href="/kr/notice/">자세히보기</a>
		</article>
	</section>
	<nav>
		<a href="/kr"><h1>ADERERROR</h1></a>
		<dl class="gnb" id="gnb"></dl>
		<dl class="tnb" id="tnb">
			<dt><a class="side" data-side="story">스토리</a></dt>
			<dd id="tnb-story">
				<dl id="tnb-story-new">
					<dt>새로운 소식</dt>
				</dl>
				<dl id="tnb-story-archive">
					<dt>아카이브</dt>
				</dl>
			</dd>
			<dt><a href="/kr/stockist">매장찾기</a></dt>
			<dt><a class="side" data-side="my">로그인</a><a href="/kr/my">마이페이지</a></dt>
			<dt><a class="side" data-side="bluemark"><span class="text">Bluemark</span><span class="over"><span class="text">Bluemark</span></span></a></dt>
			<dt><a class="side" data-side="search">검색</a></dt>
			<dt><a class="side" data-side="language">KR</a></dt>
			<dt><a class="side" href="/kr/my/wishlist">Wishlist</a></dt>
			<dt><a class="side" data-side="shoppingbag">Shopping bag</a></dt>
		</dl>
		<ul class="tnb-mobile mobile" id="tnb-mobile">
			<li>
                <?php if (isset($_SESSION['MEMBER_IDX'])): ?>
                    <a href="/kr/my" class="my">마이페이지</a>
                <?php else: ?>
                    <a class="my" data-side="my">로그인</a>
                <?php endif; ?>
            </li>
			<li><a class="bluemark" data-side="bluemark">Bluemark</a></li>
			<li><a class="customer" data-side="customer">고객서비스</a></li>
			<li><a class="language" data-side="language">Language</a></li>
		</ul>
		<button type="button" id="btn-gnb">
			<div class="icon"><span></span><span></span><span></span><span></span></div>
			<span class="text"></span>
		</button>
	</nav>
	<aside>
		<section class="story">
			<button type="button" class="close KR"></button>
			
			<article>
				<dl class="fold" id="tnb-story-new-m">
					<dt>새로운 소식</dt>
				</dl>
				<dl class="fold" id="tnb-story-archive-m">
					<dt>아카이브</dt>
				</dl>
			</article>
		</section>
		<section class="bluemark">
			<button type="button" class="close KR"></button>
			
			<article>
				<h2>Bluemark</h2>
				<p>
					BLUE MARK는 본 브랜드의 모조품으로부터 소비자의 혼란을 최소화하기
					위해 제공되는 정품 인증 서비스입니다.<br>
					<br>
					ADER는 모조품 판매를 인지하고 소비자와 브랜드의 이미지를 보호하기
					위하여 적극적으로 대응중입니다.
				</p>
				<div class="buttons">
					<a href="/kr/my/bluemark?type=regist" class="btn certify no-over">블루마크 인증하기</a>
					<a href="/kr/my/bluemark?type=list" class="btn no-over">블루마크 인증 내역</a>
				</div>
			</article>
		</section>
		<section class="language">
			<button type="button" class="close KR"></button>
			
			<article>
				<h2>언어 선택</h2>
				<p>
					아래 옵션에서 선택해 주세요.<br>
					선택한 언어에 해당되는 홈페이지로 리디렉션됩니다.
				</p>
				<div class="buttons">
					<a class="btn" data-country="KR">한국어</a>
					<a class="btn off" data-country="EN">English</a>
				</div>
			</article>
		</section>
		<section class="search">
			<button type="button" class="close KR"></button>
			
			<article id="tnb-search">
				<form id="frm-side-search" class="search">
					<input type="text" name="keyword" placeholder="검색어를 입력하세요">
					<button type="button">clear</button>
				</form>
				<section class="intro">
					<h2>추천 검색어</h2>
					<ul id="search-recommend-keyword" class="recommend"></ul>
					<h2>실시간 인기 제품</h2>
					<ul id="search-recommend-goods" class="hot"></ul>
				</section>
				<section class="result hidden">
					<h2>검색 결과</h2>
					<ul id="search-result" class="hot"></ul>
					<a href="/search" class="btn">검색결과 전체보기</a>
				</section>
			</article>
		</section>
		<section class="shoppingbag">
			<article>
				<form id="frm-side-cart">
					<h2>장바구니</h2>
                    <button type="button" class="close">                        
                    </button>
					<div class="info-box no-login">
						<div class="login">
							<p>로그인 후 이용 가능한 서비스 입니다.</p>
							<button type="button" class="btn black ready-login">로그인</button>
						</div>
						<div class="join">
							<p>회원가입을 하시면 다양한 혜택을 경험하실 수 있습니다.</p>
							<a href="/kr/join" class="btn">회원가입</a>
						</div>
					</div>
					<div class="info-box empty on">
						<p>장바구니가 비어있습니다.</p>
						<button type="button" id="btn-tnb-cart-continue" class="btn black">계속 쇼핑하기</button>
					</div>
					<dl class="cart">
						<dt>
							<div class="msg"><span id="side-cart-num"></span>개의 제품이 선택되었습니다.</div>
							<div class="right">
								<button type="button" id="btn-cart-select-delete" class="select-delete">선택 삭제</button>
								<label><input type="checkbox" name="all_check"><i></i></label>
							</div>
						</dt>
						<dd>
							<ul id="cart-list"></ul>
						</dd>
					</dl>
					<footer>
						<dl>
							<dt>제품 합계</dt>
							<dd id="frm-side-cart-total-goods">0</dd>
							<dt>회원 할인 합계</dt>
							<dd id="frm-side-cart-total-discount">0</dd>
							<dt>배송비</dt>
							<dd id="frm-side-cart-delivery">0</dd>
							<dt>총 결제 금액</dt>
							<dd id="frm-side-cart-total">0</dd>
						</dl>
						<div class="buttons">
							<button type="submit" class="no-over pay">결제하기</button>
						</div>
					</footer>
				</form>
			</article>
		</section> 
		<section class="my">
			<article class="login">
				<section>
					<h2>로그인</h2>
					<button type="button" class="close KR"></button>
					<form id="frm-side-login">
						<input type="hidden" name="r_url" value="">
						<div class="form-inline inline-label">
							<input type="email" name="member_id" placeholder=" " data-msg1="이메일 입력해주세요." data-msg2="올바른 이메일을 형식을 입력해주세요." required tabindex="1">
							<span class="control-label">E-mail</span>
						</div>
						<div class="form-inline inline-label">
							<button type="button" class="pw-view-toggle" tabindex="-1"></button>
							<input type="password" name="member_pw" placeholder=" " data-msg1="비밀번호를 입력해주세요." required tabindex="2">
							<span class="control-label">비밀번호</span>
						</div>
						<span class="result-msg" id="side-login-result"></span>
						<button type="submit" class="btn login black" tabindex="3">로그인</button>
						<div class="rows">
							<div class="left">
								<label>
									<input type="checkbox" name="save_id" value="y"  tabindex="4">
									<i></i>
									아이디저장
								</label>
							</div>
							<div class="right">
								<a href="/kr/find-account?type=id">아이디</a>
								|
								<a href="/kr/find-account?type=pw">비밀번호 찾기</a>
							</div>
						</div>
					</form>
					<div class="sns-login">
						<p>SNS 계정으로 로그인하기</p>
						<ul>
							<li><button type="button" class="login-kakao" id="btn-login-kakao">카카오 로그인</button></li>
							<li><button type="button" class="login-naver" id="btn-login-naver">네이버 로그인</button></li>
							<li><button type="button" class="login-google" id="btn-login-google">구글 로그인</button></li>
						</ul>
					</div>
					<hr />
					<div class="join">
						<p>회원가입을 하시면 다양한 혜택을 경험하실 수 있습니다.</p>
						<a href="/kr/join" class="btn">회원가입</a>
					</div>
				</section>
				<section class="customer">
					<h2>고객서비스</h2>
					<a href="/kr/notice" class="btn">공지사항</a>
					<a href="/kr/faq" class="btn">자주 묻는 질문</a>
					<a href="/kr/my/customer/qna" class="btn">문의하기</a>
				</section>
			</article>
		</section>
	</aside>
</header>
<aside id="quick">
	<ul>
		<li class="recently-viewed">
			<button type="button" class="quick" id="btn-quick-recently-viewed"></button>
			<section>
				<button type="button" class="close KR"></button>
				<header>최근 본 제품</header>
				<article>
					<div class="tab">
						<div class="tab-container">
							<ul id="quick-tabs">
								<li class="recently on"></li>
								<li class="popular"></li>
								<li class="wishlist"></li>
							</ul>
						</div>
						<section class="recently on">
							<ul class="list" id="quick-recently-list">
								<?php if (!isset($_SESSION['MEMBER_IDX'])): ?>
									<li class="empty">로그인 해 주세요.</li>
								<?php endif; ?>
							</ul>
							<footer>
								<?php if (!isset($_SESSION['MEMBER_IDX'])): ?>
                                    <a href="/kr/login" class="btn">로그인 하기</a>
                                <?php else: ?>
                                    <a href="/kr/recently" class="btn">최근 본 제품으로 이동하기</a>
                                <?php endif; ?>
							</footer>
						</section>
						<section class="popular">
							<ul class="list" id="quick-popular-list">
							</ul>
							<footer>
                                <a href="/kr/best" class="btn">실시간 인기 제품으로 이동하기</a>
							</footer>
						</section>
						<section class="wishlist">
							<ul class="list" id="quick-wishlist-list">
								<?php if (!isset($_SESSION['MEMBER_IDX'])): ?>
									<li class="empty">로그인 해 주세요.</li>
								<?php endif;?>
							</ul>
							<footer>
								<?php if (!isset($_SESSION['MEMBER_IDX'])): ?>
                                    <a href="/kr/login" class="btn">로그인 하기</a>
                                <?php else: ?>
                                    <a href="/kr/my/wishlist" class="btn">위시리스트로 이동하기</a>
                                <?php endif;?>
							</footer>
						</section>
					</div>
				</article>
			</section>
		</li>
		<li class="qna">
			<button type="button" class="quick"></button>
			<section id="quick-qna">			
				<button type="button" class="close KR"></button>
				<header><i></i>QnA</header>
				<article id="quick-qna-category" class="category">
					<p>무엇을 도와드릴까요?</p>
					<ul></ul>
				</article>
				<article id="quick-qna-chat" class="chat">
					<ul></ul>
				</article>
				<footer>
					<button type="button" class="btn">1:1 문의 작성하기</button>
				</footer>
			</section>
		</dd>
	</dl>			
</aside>
<section class="cookie-agree">
	<article class="banner">
		<button type="button" class="close KR"></button>
		<p>당사는 사이트 탐색을 개선하고 이용을 분석하고 마케팅 노력을 지원하기 위해 쿠키 및 이와 유사한 기술을 사용합니다. 아더에러의 온라인 스토어를 계속 이용하는 것으로, 귀하는 이러한 이용 약관에 동의 의사를 표하게 됩니다.</p>
		<div class="buttons">
			<button type="button" class="config">쿠키 설정</button>
			<button type="button" class="black accept-all">모두 수락</button>
		</div>
	</article>
	<article class="accept">
		<section class="cont">
			<header>
				쿠키 설정
				<button type="button" class="close KR"></button>
			</header>
			<article>
				<form id="frm-cookie-accept">
					<ul>
						<li>
							<label class="check"><input type="checkbox" name="necessary" value="y" checked><i></i>필수 쿠키</label>
							<p>
								기술 쿠키는 웹사이트가 제대로 기능하기 위해 또는 요청된 서비스 및 콘텐츠를 사용하는 데 필요한 쿠키입니다. 해당 쿠키에 대하여 차단 또는 알림을 보내도록 브라우저를 설정할 수 있으며 그러한 경우 사이트 일부가 제대로 작동하지 않을 수 있습니다.
							</p>
						</li>
						<li>
							<label class="check"><input type="checkbox" name="general" value="y"><i></i>기본 설정</label>
							<p>
								기능 쿠키는 웹사이트가 귀하의 선택 기준(예를 들어, 언어 또는 구매하기로 선택한 제품)에 따라 더욱 향상된 기능 및 개인 맞춤 서비스를 제공하도록 합니다. 해당 쿠키를 허용하지 않을 때는, 위의 언급한 서비스 일부 또는 전체가 제대로 기능하지 않을 수 있습니다.
							</p>
						</li>
						<li>
							<label class="check"><input type="checkbox" name="stat" value="y"><i></i>통계</label>
							<p>
								기능 쿠키는 웹사이트가 귀하의 선택 기준(예를 들어, 언어 또는 구매하기로 선택한 제품)에 따라 더욱 향상된 기능 및 개인 맞춤 서비스를 제공하도록 합니다. 해당 쿠키를 허용하지 않을 때는, 위의 언급한 서비스 일부 또는 전체가 제대로 기능하지 않을 수 있습니다.
							</p>
						</li>
						<li>
							<label class="check"><input type="checkbox" name="marketing" value="y"><i></i>마케팅</label>
							<p>
								해당 쿠키는 사용자 관련 프로필 생성을 목적으로 하며 네트워크 탐색 중 나타나는 사용자 설정을 기반으로 한 프로모션 메시지를 표시하는 데 사용됩니다. 이 쿠키는 개인 정보를 직접 저장하지 않으며 고유한 식별 브라우저와 인터넷 디바이스를 기반으로 합니다. 해당 쿠키를 허용하지 않으면, 인터넷 검색 중 보시게 되는 대상 광고의 연관 정확도가 저하될 수 있습니다.
							</p>
						</li>
					</ul>
					<div class="buttons">
						<button type="button">선택 사항 저장</button>
						<button type="submit" class="accept-all black">모두 수락</button>
					</div>
				</form>
			</article>
		</section>
	</article>
</section>
<footer>
	<section>
		<article class="about">
			<h2>About ADERERROR</h2>
			<p>
				ADERERROR (아더에러)는 2014년 설립되었으며 패션을 기반으로 한 문화 커뮤니케이션 브랜드입니다.<br>
				ADERERROR는 ‘but near missed things’ 이라는 브랜드 슬로건, 철학을 바탕으로 사람들이 일상에서 쉽게<br>
				놓치고 있는 것들을 익숙하지만 낯설고, 새롭게 느낄 수 있도록 표현하는 활동에 집중하고 있으며, 사진, 영상,<br>
				공간, 디자인, 예술, 가구 등 문화 콘텐츠를 우리의 방식으로 재편집하여 새로운 문화를 제안합니다.<br>
				ADER는 모든 영역 간의 커뮤니케이션 디자인하는 것을 브랜드 핵심 가치로서 추구합니다.
			</p>
		</article>
		<article class="rules">
			<h2>법적 고지사항</h2>
			<ul>
				<li><a href="/kr/online-store-guide">온라인 스토어 이용가이드</a></li>
				<li><a href="/kr/terms-of-use">이용 약관</a></li>
				<li><a href="/kr/privacy-policy">개인정보 처리방침</a></li>
				<li><a href="/kr/cookie-policy">쿠키정책</a></li>
			</ul>
		</article>
		<article class="social">
			<h2>소셜 미디어</h2>
			<ul>
				<li><a href="http://pf.kakao.com/_mQzRxl" target="_blank"><img src="/images/sns/kakao.svg"></a></li>
				<li><a href="https://www.facebook.com/adererror" target="_blank"><img src="/images/sns/facebook.svg"></a></li>
				<li><a href="http://instagram.com/ader_error" target="_blank"><img src="/images/sns/instagram.svg"></a></li>
				<li><a href="https://goo.gl/s1b2mN" target="_blank"><img src="/images/sns/youtube.svg"></a></li>
				<li><a href="http://weibo.com/aderofficial" target="_blank"><img src="/images/sns/weibo.svg"></a></li>
				<li><a href="https://pinterest.com/adererror/" target="_blank"><img src="/images/sns/pinterest.svg"></a></li>
				<li><a href="https://goo.gl/MUkFap" target="_blank"><img src="/images/sns/vimeo.svg"></a></li>
				<li><a href="/"><img src="/images/sns/wechat.svg"></a></li>
			</ul>
		</article>
		<article class="customer-center">
			<h2>고객센터</h2>
			<address>ADER 3F 53, Yeonmujang-gil, <br>Seongdong-gu, Seoul, Korea</address>
			<p class="tel">TEL. 02-792-2232</p>
			<p class="hour">Office hour Mon - Fri AM 10:00 - PM 5:00</p>
		</article>
		<article class="company">
			<h2>회사정보</h2>
			<ul>
				<li>Company | ADER</li>
				<li>Business Name | FIVE SPACE CO.,LTD</li>
				<li>Business License | 760-87-01757</li>
				<li>Mail-order License No. | 제 2021-서울성동-01588호</li>
				<li>CEO | HANN</li>
				<li>OFFICE | ADER 3F 53, Yeonmujang-gil, Seongdong-gu, Seoul, Korea</li>
			</ul>
		</article>
	</section>
	<p class="copyright">© ADERERROR 2024</p>
</footer>

</body>
</html>