$(`main.collaboration > nav a.swiper-slide`).each(function() {
	let href = $(this).attr("href")
	// if(location.pathname == `${config.base_url}${href}`) {
	if(location.pathname == `${href}`) {
		$(this).addClass("on");
	}
});

/** nav swipe **/
const nav_swiper = new Swiper("#collaboration-nav",{
	slidesPerView : 'auto',
	spaceBetween : 0,
	loop: false,
	effect: "slide",
	navigation: {
		nextEl: "#collaboration-nav .swiper-button-next",
		prevEl: "#collaboration-nav .swiper-button-prev"
	},
	// initialSlide:
});

$(`main.collaboration > nav button.swiper-button-next`).click(function() {
	nav_swiper.slideNext();
});
$(`main.collaboration > nav button.swiper-button-prev`).click(function() {
	nav_swiper.slidePrev();
});

$(document).ready(function () {
	$(`main.collaboration > nav a.swiper-slide`).each(function( idx ) {
		if($(this).attr("href") == $("body").data("path")) {
			nav_swiper.slideTo(idx, 0)
		}
	});

})

/** swiper init **/
let swiper_arr = [];
$("main.collaboration .swiper-container:not(#collaboration-nav)").each(function() {
	let option = { // 기본 옵션
		slidesPerView : 'auto',
		spaceBetween : $(this).data("gap") || 0,
		loop: $(this).data("loop") || false,
		direction : $(this).data("direction") || "horizontal",
		effect: "slide",
		// mousewheel : true,
		navigation: {
			nextEl: $(this).find(".swiper-button-next").get(0),
			prevEl: $(this).find(".swiper-button-prev").get(0)
		},
		scrollbar: {
			el: $(this).find(".swiper-scrollbar").get(0),
			hide: false,
		},
		pagination: {
			el: $(this).find(".swiper-pagination").get(0),
			clickable: true,
		},
	};

	/** 자동 슬라이드 **/
	if($(this).hasClass("slide-auto")) {
		option.loop = true;
		option = Object.assign(option,{
			speed: 5000,
			autoplay: {
				delay: 1,
				//pauseOnMouseEnter: true,
				disableOnInteraction: false,
				waitForTransition: true,
				stopOnLastSlide: false,
			}
		});
	}
	
	/** 카드 **/
	else if($(this).hasClass("card")) {
		$(this).find(".swiper-wrapper").append($(this).find(".swiper-wrapper > .swiper-slide").clone());
		option.loop = true;
		option = Object.assign(option,{
			centeredSlides: true,
		});
	}

	/** coursel **/
	else if($(this).hasClass("carousel")) {
		option.loop = true;
		option.effect = "coverflow";
		option = Object.assign(option,{
			grabCursor: true,
			centeredSlides: true,
			coverflowEffect: {
				rotate: 50,
				stretch: 0,
				depth: 100,
				modifier: 1,
				slideShadows: true,
			},
		});
	}

	else if ($(this).hasClass("effect-fade")) {
    option = Object.assign(option, {
			effect: "fade",
			fadeEffect: {
				crossFade: true,
			},
    });
	}

	/** 썸네일이 있는 갤러리 **/
	if($(this).parent().hasClass("with-thumbnails") == true) {
		$(this).find(".swiper-slide").each(function() {
			let src = $(this).find("img").attr("src");
			$(this).css({backgroundImage:`url('${src}')`});
		});
		$(this).after($(this).clone().addClass("thumbnails"));
	}

	swiper_arr.push(new Swiper($(this).get(0),option));
});

/** 상품 갤러리 **/
$(".product-gallery .scroll .thumbnail > img").each(function() {
	let src = $(this).attr("src");
	$(this).parent().css({backgroundImage:`url('${src}')`});
});

$(".product-gallery .thumbnails ul > li").click(function() {
	let src = $(this).find("img").attr("src")
		, obj = get_parent_by_class($(this),"goods");

	// $(obj).find(".image").css("backgroundImage",`url('${src}')`);
	$(obj).find(".image").attr("style", `background-image: url('${src}') !important;`);
	$(obj).find(".image img").attr("src",src);
});
$(".product-gallery .thumbnails button").click(function() {
	let obj = $(this).parent().find(".scroll")
		, height = $(obj).find("li").height() + 1;
	
	if($(this).hasClass("up")) {
		$(obj).animate({scrollTop: obj.scrollTop() - height},"fast");
	}
	else {
		$(obj).animate({scrollTop: obj.scrollTop() + height},"fast");
	}
});

$(".product-gallery > ul.list > li").click(function() {
	let idx = $(this).index()
		,obj = $(this).parent().parent();

	$(this).addClass("on").siblings().removeClass("on");
	$(obj).children(".goods.on").removeClass("on");
	$(obj).children(".goods").eq(idx).addClass("on");
	$(obj).children(".goods").eq(idx).find(".thumbnails ul > li").eq(0).click();
}).eq(0).click();

// 캠페인 영상 보기
$("main.collaboration article button.play").click(function() {
	$("body").addClass("on-modal");
	if($("main.collaboration").hasClass("new")) {
		var video = $(this).parent().parent().find("video").get(0);
		if (window.matchMedia("only screen and (max-width: 1024px)").matches) {
			var mobileVideo = $(this).parent().parent().find("video.mobile").get(0);
      if (mobileVideo) {
        video = mobileVideo;
      }
		}
		video.pause();

		$("body").append(
			'<div class="video-fullscreen">'
			+'	<button type="button" class="close"></button>'
			+'	<video playsinline src="' + video.src + '"></video>'
			+'</div>'
		);
		$("body > .video-fullscreen video").get(0).currentTime = video.currentTime;
		$("body > .video-fullscreen video").get(0).play();
		$("body > .video-fullscreen > *").click(function() {
			$("body").removeClass("on-modal");
			video.currentTime = $("body > .video-fullscreen video").get(0).currentTime;
			video.play();
			$(this).parent().remove();
		});

		$("body > .video-fullscreen button.close").css({"background": "none"});
	}
	else {
		$(this).parent().parent().parent().find("video").addClass("on");
		$(this).parent().parent().parent().find("video").get(0).play();
	}
});