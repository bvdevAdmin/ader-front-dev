<main class="collaboration">
    <?php include $_CONFIG['PATH']['PAGE'] . $_CONFIG['M'][0] . '/collaboration/_nav.php'; ?>
    <header>
        <h1>
            <small>2021</small>
            ADER ERROR X CAMPER
        </h1>
    </header>
    <section class="collaboration new">
        <img src="https://adererror.com/collaboration/camper/intro.jpg">
        <article class="list">
            <div class="describe">
                <div class="text">
                    <h1>#TheLostOrigin</h1>
                    <p>
                        We present ADER X CAMPER's collaborative collection,<br>
                        inspired by pieces of old artifacts<br>
                        discovered by archaeologists in a new area.<br>
                        <br>
                        Discover three styles of sneakers and accessories today,<br>
                        including hoods, sweatshirts and backpacks with logo play.
                    </p>
                </div>
            </div>
            <div class="product">
                <div class="gallery">
                    <img src="https://adererror.com/collaboration/camper/images/01.png" id="first">
                    <div class="cont cont-2 on" style="margin-top: -3839.86px;">
                        <div class="box">
                            <p>
                                PHOTO-111-S<br><br>
                                These sneakers feature ADER's cinder cutting details,<br>
                                highlighting text prints and label details as the main.<br>
                                <br>
                                On the side outsole, the camper logo and ADER's identity<br>
                                meet to indicate a new collaboration<br>
                                with the newly modified JetBlue label.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="gallery">
                    <img src="https://adererror.com/collaboration/camper/images/02.png" id="second">
                    <div class="cont cont">
                        <div class="box"></div>
                    </div>
                </div>
                <div class="gallery">
                    <img src="https://adererror.com/collaboration/camper/images/03.png" id="third">
                    <div class="cont">
                        <div class="box">
                            <p>
                                PHOTO-240-S<br><br>
                                German sneakers with destroyed<br>
                                and stapler details stand out<br>
                                with removable tiebeck labels and ripped backstays.<br>
                                <br>
                                Meet the suede texture, which combines camperogo<br>
                                and zigzag embroidery details.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="gallery">
                    <img src="https://adererror.com/collaboration/camper/images/04.png" id="fourth">
                    <div class="cont">
                        <div class="box">
                            <p>
                                PHOTO-743-S<br><br>
                                The coin loafers-inspired black sneakers<br>
                                come with cinder cutting details from medal pockets.<br>
                                Meet the different label details<br>
                                and zigzag back points attached to the sides.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="gallery">
                    <img src="https://adererror.com/collaboration/camper/images/05.png" id="fifth">
                    <div class="cont">
                        <div class="box">
                            <p>
                                PHOTO-T33-W<br><br>
                                Boldly applied with the alphabets of each ADER and CAMPER logo,<br>
                                the sneaker accessory provides a free silhouette transformation.<br>
                                Meet the detachable outsole accessory with Tetris symbol details composed of spikes.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </article>
        <article class="campaign"><h3>ADER x Camper</h3>
            <div class="media">
                <video loop="" muted="" autoplay="" playsinline=""
                       src="https://ader-video-s3-bucket.s3.ap-northeast-2.amazonaws.com/collaboration/masion+kitsune/MASION+KITSUNE_campaign_1920x1080.mp4"></video>
            </div>
            <p>
                Watch the ADER X CAMPER collaboration collection's<br>
                advertising campaign with Coco Capitan,<br>
                a world-renowned photographer and artist.<br>
                <br>
                The 'THE LOST ORIGIN' ad campaign,<br>
                reinterpreted with her artistic wit and humor,<br>
                was inspired by the new AC region's exploration team.
            </p>
            <div class="buttons camper">
                <button type="button" class="button play">Show campaign main videos<span class="over"><span
                                class="text">Show campaign main videos</span></span></button>
                <br>
                <button type="button" class="button" onclick="CallIframeModal('/editorial/image-detail.html?product_no=6596&amp;cate_no=73&amp;display_group=1');">
                    Show campaign images
                    <span class="over">
                        <span class="text">Show campaign images</span>
                    </span>
                </button>
            </div>
        </article>        
    </section>
</main>
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