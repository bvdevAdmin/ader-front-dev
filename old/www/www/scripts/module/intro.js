/*
if('user' in sessionStorage && sessionStorage.user != null) {
	let user = JSON.parse(sessionStorage.user);
	if(user.type == "파트너") {
		$("section.intro > article.partner ul.partner").addClass("on");
	}
}

$.ajax({
	url: config.api + "intro/get",
	success: function(d) {
		if(d.code == 200) {
			if(d.banner.header) {
				d.banner.header.forEach(function(row) {
					$("section.intro > header .swiper-wrapper").append(`
						<div class="swiper-slide" style="background-image:url('${row.image}')"></div>
					`);
				});
			}

			var items = { studio : 0,special : 0 };
			d.special.forEach(function(row) {
				if(row.page == 'studio') {
					if($.browser.mobile || (items.studio == 3 || items.studio == 0)) {
						items.studio = 0;
						$("#contents-studio > .swiper-wrapper").append('<div class="swiper-slide"></div>');
					}
					items.studio++;
					$("#contents-studio > .swiper-wrapper > .swiper-slide").last().append(`
						<a href="/${row.page}/${row.no}">
							<span class="image">
								<span class="cont" style="background-image:url('${row.image}')"></span>
								<span class="category ${row.page}">${row.category[0]}</span>
								<span class="label ${row.reservation_type}"></span>
							</span>
							<span class="info">
								<h2>${row.title}</h2>
								<span class="time">${row.time}</span>
								<span class="summary">${row.summary}</span>
								<span class="price"><big>${number_format(row.price)}</big> 원/시간</span>
								<ul class="count">
									<li><i class="xi-user"></i>최대 1인</li>
									<li><i class="xi-comment"></i>${number_format(row.review_count)}</li>
									<li><i class="xi-heart"></i>${number_format(row.favorite_count)}</li>
								</ul>
							</span>
						</a>
					`);
				}
				else {
					if($.browser.mobile || (items.special == 3 || items.special == 0)) {
						items.special = 0;
						$("#contents-special > .swiper-wrapper").append(`<div class="swiper-slide ${row.page}"></div>`);
					}
					items.special++;
					$("#contents-special > .swiper-wrapper > .swiper-slide").last().append(`
						<a href="/${row.page}/${row.no}">
							<span class="image">
								<span class="cont" style="background-image:url('${row.image}')"></span>
								<span class="category ${row.page}">${row.category[0]}</span>
							</span>
							<span class="info">
								<h2>${trim(row.title)}</h2>
								<span class="date">2020.10.05 ~ 2021.12.31</span>
								<span class="summary">${row.summary}</span>
							</span>
						</a>
					`);
				}
			});
			items.total = $(`section.intro > article.special .swiper-slide`).length;
			$("section.intro > article.special ul.contents > li").each(function() {
				items[$(this).data("category")] = $(`section.intro > article.special .swiper-slide.${$(this).data("category")}`).index()+1;
				$(this).data("slide",items[$(this).data("category")]);
			});

			$(".swiper-container:not(.custom)").each(function() {
				swiper.push(new Swiper($(this).get(0),{
					slidesPerView : $(this).data("perView") || 1,
					slidesPerGroup : $(this).data("perGroup") || 1,
					spaceBetween : $(this).data("space") || 0,
					loop: true,
					loopFillGroupWithBlank: true,
					effect: $(this).data("effect") || "slide",
					autoplay: {
						delay: $(this).data("delay") || 3000,
						disableOnInteraction: false,
					},
					pagination: {
						el: $(this).find(".swiper-pagination").get(0),
						clickable: true,
					},
					navigation: {
						nextEl: $(this).find(".swiper-button-next").get(0),
						prevEl: $(this).find(".swiper-button-prev").get(0)
					},
				}));
			});
			swiper[2].on('slideChange', function() {
				let obj = $("section.intro > article.special ul.contents");
				$(obj).removeClass("tab-1").removeClass("tab-2").removeClass("tab-3");
				if(this.activeIndex == 1 || this.activeIndex <= items.audition || this.activeIndex > items.total) {
					$(obj).addClass("tab-1");
				}
				else if(this.activeIndex >= items.audition && this.activeIndex < items.store) {
					$(obj).addClass("tab-2");
				}
				else {
					$(obj).addClass("tab-3");
				}
			});
			$("section.intro > article.special ul.contents > li").click(function() {
				//console.log(($(this).index()+1));
				swiper[2].slideTo($(this).data("slide"));
			}).eq(0).click();
		}
	}
});

draw_calendar($("body > section.intro > article.partner .calendar"),function(date,type) {
	if(type == 'date') {
		localStorage.setItem("calendar",date);
		location.href = "/calendar";
	}
});
*/
