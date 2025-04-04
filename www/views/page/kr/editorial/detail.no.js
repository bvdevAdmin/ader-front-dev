let thumb_swiper    = null;
let contents_swiper = null;

let mobile_thumb_swiper     = null;
let mobile_contents_swiper  = null;

$(document).ready(function () {
    let page_idx = location.pathname.split("/")[4];

    $.ajax({
        url: config.api + "editorial/get",
        headers: {
            country: config.language
        },
        data: {
            page_idx: page_idx
        },
        error: function () {
            makeMsgNoti("MSG_F_ERR_0101", null);
        },
        success: function (d) {
            //PC
            d.data.e_thumb_W.forEach(row => {
                $("#list").append(`
					<li class="swiper-slide">
						<div class="item" data-no="${row.t_idx}">                        
							<img src="${config.cdn + row.t_location}">
						</div>
					</li>
				`);
            });

            d.data.e_contents_W.forEach(row => {
                let list_img;
                if (row.c_location.indexOf(".mp4") > 0) {
                    list_img = `<video muted autoplay loop src="${config.cdn + row.c_location}"></video>`;
                } else {
                    list_img = `<img src="${config.cdn + row.c_location}">`;
                }

                let item = `
                    <div class="item" data-no="${row.c_idx}">
                        <div class="cont">${list_img}</div>
                    </div>
                `;

                $("#swiper-editorial .swiper-wrapper").append(`
					<div class="swiper-slide">${item}</div>
				`);
            })

            thumb_swiper = new Swiper("#swiper-list", {
                slidesPerView: 'auto',
                direction: 'horizontal',
                speed: 200,
                spaceBetween: 10,
                loop: false,
                allowTouchMove: true,
            });

            contents_swiper = new Swiper("#swiper-editorial", {
                slidesPerView: 'auto',
                loop: false,
                effect: "slide",
                navigation: {
                    nextEl: $("#swiper-editorial .swiper-button-next").get(0),
                    prevEl: $("#swiper-editorial .swiper-button-prev").get(0)
                },
            });

            $('#list .swiper-slide').each(function (index, slide) {
                $(slide).on('click', function () {
                    $("#list .swiper-slide").removeClass("on");
                    $(slide).addClass("on");
                    contents_swiper.slideTo(index);
                    thumb_swiper.slideTo(index);
                });
            });

            $('#list .swiper-slide').eq(0).addClass("on");

            thumb_swiper.update();
            contents_swiper.update();

            contents_swiper.on('slideChange', function () {
                $("#list .swiper-slide").removeClass("on");
                $("#list .swiper-slide").each(function (index, slide) {
                    if (index === contents_swiper.activeIndex) {
                        $(slide).addClass("on");
                        thumb_swiper.slideTo(index);
                    }
                });
            });

            //Mobile
            d.data.e_thumb_M.forEach(row => {
                $("#mobile-list").append(`
					<li class="swiper-slide">
						<div class="item" data-no="${row.t_idx}">                        
							<img src="${config.cdn + row.t_location}">
						</div>
					</li>
				`);
            });

            d.data.e_contents_M.forEach(row => {
                let list_img;
                if (row.c_location.indexOf(".mp4") > 0) {
                    list_img = `<video muted autoplay loop src="${config.cdn + row.c_location}"></video>`;
                } else {
                    list_img = `<img src="${config.cdn + row.c_location}">`;
                }

                let item = `
                    <div class="item" data-no="${row.c_idx}">
                        <div class="cont">${list_img}</div>
                    </div>
                `;

                $("#mobile-swiper-editorial .swiper-wrapper").append(`
					<div class="swiper-slide">${item}</div>
				`);
            });

            mobile_thumb_swiper = new Swiper("#mobile-swiper-list", {
                slidesPerView: 'auto',
                direction: 'horizontal',
                speed: 200,
                loop: false,
                allowTouchMove: true,
            });

            mobile_contents_swiper = new Swiper("#mobile-swiper-editorial", {
                slidesPerView: 'auto',
                loop: false,
                effect: "slide",
                navigation: {
                    nextEl: $("#mobile-swiper-editorial .swiper-button-next").get(0),
                    prevEl: $("#mobile-swiper-editorial .swiper-button-prev").get(0)
                },
            });

            $('#mobile-list .swiper-slide').each(function (index, slide) {
                $(slide).on('click', function () {
                    $("#mobile-list .swiper-slide").removeClass("on");
                    $(slide).addClass("on");
                    mobile_contents_swiper.slideTo(index);
                    mobile_thumb_swiper.slideTo(index);
                });
            });

            $('#mobile-list .swiper-slide').eq(0).addClass("on");

            mobile_thumb_swiper.update();
            mobile_contents_swiper.update();

            mobile_contents_swiper.on('slideChange', function () {
                $("#mobile-list .swiper-slide").removeClass("on");
                $("#mobile-list .swiper-slide").each(function (index, slide) {
                    if (index === mobile_contents_swiper.activeIndex) {
                        $(slide).addClass("on");
                        mobile_thumb_swiper.slideTo(index);
                    }
                })
            });
        }
    });


});
