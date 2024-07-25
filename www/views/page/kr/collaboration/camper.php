<main class="collaboration">
    <?php include $_CONFIG['PATH']['PAGE'].$_CONFIG['M'][0].'/collaboration/_nav.php'; ?>
	<header>
		<h1>
			<small>2021</small>
			ADER ERROR X CAMPER
		</h1>
	</header>
	<section class="media">
		<video loop muted autoplay playsinline src="https://d340a4zb19l6y1.cloudfront.net/collaboration/camper/ADERxCAMPER_PC.mp4"></video>
		<article>
			<p>
				'The LostOrigin'<br>
				<br>
				새로운 지역에서 고고학자들이 발견한 오래된 유물 조각들에서 영감을 얻어 탄생한 ADER X CAMPER의 협업 컬렉션을 선보입니다.<br>
				세 가지 스타일의 스니커즈 스타일과 로고 플레이가 돋보이는 후드, 스웻셔츠, 백팩을 포함한 액세서리까지 다양한 스타일을 지금 만나보세요.
			</p>
			<div class="buttons">
				<button type="button" class="play">캠페인 메인 영상 보기</button>
			</div>
		</article>
	</section>
	<section class="gallery">
		<article>
			<div class="swiper-container carousel">
				<div class="swiper-wrapper">
					<div class="swiper-slide"><img src="/upload/collaboration/camper/1.jpg"></div>
					<div class="swiper-slide"><img src="/upload/collaboration/camper/2.jpg"></div>
					<div class="swiper-slide"><img src="/upload/collaboration/camper/3.jpg"></div>
					<div class="swiper-slide"><img src="/upload/collaboration/camper/4.jpg"></div>
					<div class="swiper-slide"><img src="/upload/collaboration/camper/5.jpg"></div>
				</div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
			<p>
				세계적으로 활동하고 있는 포토그래퍼이자 아티스트인 코코 카피탄 (Coco Capitan) 과 함께 작업한 ADER X CAMPER<br>
				협업 컬렉션의 광고 캠페인을 감상해보세요. 그녀의 예술적인 재치와 해학으로 재해석한 ‘THE LOST ORIGIN’<br>
				광고 캠페인은 새로운 AC 지역의 탐사반에서 영감을 받아 진행되었습니다.
			</p>
			<div class="buttons">
				<button type="button">캠페인 이미지 전체 보기</button>
			</div>
		</article>
	</section>
	<section>
		<article>
			<div class="product">
				<div class="gallery">
					<img src="/upload/collaboration/camper/01.png" id="first"><div class="cont cont-2">
							<div class="box">
								<p>PHOTO-111-S<br><br>
									아더의 신더 컷팅 디테일이 돋보이는 스니커즈로, <br>
									텍스트 프린트와 라벨 디테일이 메인으로 강조됩니다. <br>
									사이드 아웃솔에는 캠퍼 로고와 아더의 아이덴티티가 만나 <br>
									새롭게 변형된 제트블루 라벨이 새로운 협업을 나타냅니다.<br>
								</p>
							</div> 
					</div>
				</div>
				<div class="gallery">
					<img src="/upload/collaboration/camper/02.png" id="second"><div class="cont cont">
							<div class="box"></div> 
					</div>
				</div>
				<div class="gallery">
					<img src="/upload/collaboration/camper/03.png" id="third"><div class="cont">
							<div class="box">
								<p>PHOTO-240-S<br><br>
									디스트로이드와 스테이플러 디테일의 저먼 스니커즈는<br>
									제거 가능한 타이벡 라벨과 뜯겨 올려진 백 스테이가 돋보입니다. <br>
									캠퍼로고와 지그재그 자수 디테일이 함께 어우러진 스웨이드 텍스처를 만나보세요.
								</p>
							</div> 
					</div>
				</div>
				<div class="gallery">
					<img src="/upload/collaboration/camper/04.png" id="fourth"><div class="cont">
							<div class="box">
								<p>PHOTO-743-S<br><br>
									코인 로퍼에서 영감을 받은 블랙 스니커즈는<br>
									메달포켓의 신더 컷팅 디테일이 적용되어 있습니다.<br>
									사이드에 부착된 서로 다른 라벨 디테일과 지그재그 백 포인트를 만나보세요.
								</p>
							</div> 
					</div>
				</div>
				<div class="gallery">
					<img src="/upload/collaboration/camper/05.png" id="fifth"><div class="cont">
							<div class="box">
								<p>PHOTO-T33-W<br><br>
									ADER 와 CAMPER 각 로고의 알파벳이 볼드하게 적용된<br>
									스니커즈 액세서리는 자유로운 실루엣 변형을 제공합니다.<br>
									테트리스 심볼 디테일이 스파이크로 구성된 탈부착 형태의 아웃솔 액세서리를 만나보세요.
								</p>
							</div> 
					</div>
				</div>
			</div>
		</article>

		<article class="shop">
			<div class="buttons">
				<a href="" class="btn">콜라보레이션 제품 전체 보기</a>
			</div>
		</article>
	</section>

	<script>
	if($("section.collaboration").length > 0){
		$(window).scroll(function() {
			$("article.list > .product > .gallery").each(function() {
				let calc = $("#second").offset().top-$(window).scrollTop()-300;
				var Top = $("header.marginbottom0").height() + 180;
				//gallery 걸렸을때
				if($(window).scrollTop() >= Top) {
					$("article.list .gallery > .cont-2").addClass("on");
					if(calc <= 0) {
						$("article.list .gallery > .cont-2").css({marginTop:calc + "px"});					
					}
				}
				//gallery 벗어날때
				else {
					$("article.list .gallery > .cont-2").removeClass('on');
				}
			}).scroll();
			$(window).resize(function() {
				$(window).scroll();
			});
		});	
	}
	</script>
</main>