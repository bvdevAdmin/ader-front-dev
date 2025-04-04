$(document).ready(function() {
	// 살짝 스크롤을 올릴 경우
	var prevScrollTop = 0;
	$(window).scroll(function() {
		if(prevScrollTop > $(this).scrollTop()) {
			if($(this).scrollTop() > 0 && prevScrollTop > $(this).scrollTop() + 10) {
				$("body").removeClass("unview-header");
			}
		} else {
			$("body").addClass("unview-header");
		}

		prevScrollTop = $(this).scrollTop();
	});

    let mobileWeb = false;
    if(window.innerWidth <= 780) {
        mobileWeb = true;
    }

	setTimeout(() => {
		$("body > section.collaboration > article.bang-n-olufsen > section.intro-video .place-center").addClass("fadeout");
        $("body > section.collaboration > article.bang-n-olufsen > section.intro-video #intro-video").get(0).play();
		video_chk = false;
	}, 3000);

	$('video').click(function() {
		this[this.paused ? 'play' : 'play']();
	});

	let video_chk = true;

	if(mobileWeb == false) {
		setInterval(() => {
			if(video_chk == false && $("#intro-video").get(0).paused == true) {
				video_chk = true;
				if($("section.intro-video").next().offset().top > $(window).scrollTop()) {
					$(window).scrollTop($("section.intro-video").next().offset().top, "fast");
				}
			}
		}, 10);
	}

	$(window).scroll(function() {
		$("video[inview]").each(function() {
			if($(this).offset().top < $(window).height() + $(window).scrollTop() && $(this).offset().top + $(this).height() - $(window).height() * 0.1 > $(window).scrollTop()) {
				$(this).get(0).play();
			} else {
				$(this).get(0).pause();
			}
		});

		$(".photo").each(function() {
			let img_eq = 0
				,img_chg_top =  $(this).offset().top - $(this).parent().offset().top;

			if(!$(this).hasClass("with-scroll")) img_chg_top -= $(window).height();

			if(img_chg_top > 0) {
				let div = (mobileWeb) ? 1 : 4;
				img_eq = parseInt(img_chg_top / ( $(window).height() / div ));

				if(img_eq < $(this).find("ul > li").length && $(this).find("ul > li").eq(img_eq).hasClass("on") == false) {
					$(this).find("ul > li.on").removeClass("on");
					$(this).find("ul > li").eq(img_eq).addClass("on");
				}

				if($(this).hasClass("image-change")) {
					let moveX = 0
						, height = (mobileWeb) ? $(this).find("li").height() : $(window).height();
					if(img_eq > 3) {
						moveX = img_chg_top - height - ($("#image-change").offset().top - $(window).scrollTop());
					} else {
						$("#image-change ul").css({marginTop: -(img_eq * height) });
					}
					moveX *= (mobileWeb) ? 1.25 : 2;
					if(mobileWeb && moveX > 0) {
						moveX -= (height * 3) * 1.25;
					}
					if(mobileWeb) {
						$("#image-change-cont").css({
							transform :`translateX(-${moveX}px)`,
							width : $(window).width() + moveX
						});
					} else {
						$("#image-change-cont").css("transform", `translateX(-${moveX}px)`);
					}

					let lookbook_img_width = 400
						, lookbook_img_width_b = 460
						, lookbook_li_padding = 20
						, lookbook_li_img_width = lookbook_img_width + lookbook_li_padding;
					if(mobileWeb) {
						lookbook_img_width = 230
						, lookbook_img_width_b = 260
						, lookbook_li_padding = 10
						, lookbook_li_img_width = lookbook_img_width + lookbook_li_padding;
					}

					moveX -= $(window).width() * 1.1; // 배수로 커지는 이미지 위치 조정
					img_eq = parseInt(moveX / lookbook_li_img_width);
					let img_width = lookbook_img_width
						, img_size = (lookbook_img_width_b - lookbook_img_width) * ((moveX % lookbook_li_img_width) / lookbook_li_img_width);

					for(let i = 0; i < $("#image-horiz > li").length; i++) {
						if(i == img_eq) img_width = lookbook_img_width + img_size;
						else if(i == img_eq - 1) img_width = lookbook_img_width_b - img_size;
						else img_width = lookbook_img_width;
						$("#image-horiz > li").eq(i).find("img").width(img_width);
					}
				}
			}
		});

		if($("#popup-gallery").parent().parent().offset().top - $(window).height() < $(window).scrollTop()) {
			$("#popup-gallery").css("transform",`translateX(${$("#popup-gallery").parent().parent().offset().top - $(window).height() - $(window).scrollTop()}px)`);
		}

		$("section.collaboration p").each(function() {
			if($(this).offset().top < $(window).scrollTop() + $(window).height() && $(this).hasClass("show-up") == false) {
				$(this).addClass("show-up");
			} else if($(this).offset().top > $(window).scrollTop() + $(window).height()) {
				$(this).removeClass("show-up");
			}
		});
	}).scroll();
});