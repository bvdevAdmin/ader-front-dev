$(document).ready(function () {
	getStandby_entry();
});

function getStandby_entry() {
	$.ajax({
		type: "post",
		url: config.api + "member/standby/detail",
		headers: {
			country: config.language
		},
		data: {
			'standby_idx': location.pathname.split("/")[4]
		},
		dataType: "json",
		async: false,
		error: function () {
			makeMsgNoti(config.language, "MSG_F_ERR_0129", null);
		},
		success: function (d) {
			if (d.code == 200) {
				let standby_page = d.data.standby_page;
				let mediaQuery = window.matchMedia("screen and (max-width:1025px)");
				let banner_location = mediaQuery.matches ? standby_page.banner_location_M : standby_page.banner_location_W
				if (standby_page != null) {
					$('.info-product-name').text(standby_page.title);
					$('.banner-img img').attr('src', `${config.cdn}${banner_location}`);
					$('.info-product-description').html(standby_page.description);

					$('.info-standby-date .period_entry').text(`응모기간 : ${standby_page.period_entry}`);
					$('.info-standby-date .period_order').text(`참여자 구매 링크 문자 발송 : ${standby_page.period_order}`);
					$('.info-standby-date .period_purchase').text(`당첨자 구매기간 : ${standby_page.period_purchase}`);
				}
				clickBTN_join();

				let standby_product = d.data.standby_product
				if (standby_product != null) {
					if (standby_product.length == 1) {
						getSingleProduct(standby_product);
					} else {
						getMultiProduct(standby_product)
					}
				}
			} else {
				alert(d.msg)
			}
		}
	});
}

function clickBTN_join() {
	let btn_join = document.querySelector('.standby-joinus-btn');
	if (btn_join != null) {
		btn_join.addEventListener('click', function (e) {
			let terms = $('input[name="terms"]:checked').val();
			if (terms != "FALSE") {
				$.ajax({
					type: "post",
					url: config.api + "member/standby/entry",
					headers: {
						country: config.language
					},
					data: {
						'standby_idx': location.pathname.split("/")[4]
					},
					dataType: "json",
					async: false,
					error: function () {
						makeMsgNoti(config.language, "MSG_F_ERR_0133", null);
					},
					success: function (d) {
						if (d.code == 200) {
							alert(
								d.msg,
								function() {
									location.href = `${config.base_url}/my/standby`;
								}
							);
						} else {
							alert(
								d.msg,
								function () {
									if (d.code == 401) {
										sessionStorage.setItem('r_url',location.href);
										location.href = `${config.base_url}/login`;
									} else {
										location.href = `${config.base_url}/my/standby`;
									}
								}
							);
						}
					}
				});
			} else {
				let msg = "";
				if (config.language == "KR") {
					msg = "약관 동의 후 다시 시도해주세요.";
				} else if (config.language == "EN") {
					msg = "Please agree to the terms and try again."
				}

				alert(msg);
			}
		});
	}
}

function getSingleProduct(standby_product) {
	let item = standby_product[0]
	let color = [];
	item.product_color.forEach(row2 => {
		color.push(`<span class="colorchip" style="background-color:${row2.color_rgb}"></span>`);
	});

	// 사이즈
	let size = [];
	if (item.product_type == 'S') {
		size.push(`<li>Set</li>`)
	} else {
		item.product_size.forEach(row2 => {
			size.push(`<li>${row2.option_name}</li>`);
		});
	}

	$(".standby_web_product_list,.standby_mobile_product_list").append(`
		<div class="single-container complete">			
			<div class="single-image">
				<span class="image" style="background-image:url('${config.cdn + item.img_location}')"></span>
			</div>
			<div class="single-info">
				<div class="left-info">
					<div class="name">${item.product_name}</div>
					<div>${item.color}</div>
					<div class="color"><ul>${color}</ul></div>
				</div>
				<div class="right-info">
					<div class="price">${item.txt_price}</div>
					<span class="size"><ul>${size.join("")}</ul></span>
				</div>
			</div>
			<button class="favorite ${item.whish_flg ? ' on' : ''}" data-goods_no="${item.product_idx}"/>
		</div>
		
	`)
	setScroll('single', standby_product.length);

}

function getMultiProduct(standby_product) {
	standby_product.forEach(row => {
		let color = [];
		row.product_color.forEach(row2 => {
			color.push(`<span class="colorchip" style="background-color:${row2.color_rgb}} "></span>`);
		});

		// 사이즈
		let size = [];
		if (row.product_type == 'S') {
			size.push(`<li>Set</li>`)
		} else {
			row.product_size.forEach(row2 => {
				size.push(`<li>${row2.option_name}</li>`);
			});
		}

		$(".standby_web_product_list,.standby_mobile_product_list").append(`
			<div class="multi-container complete">
				<div class="multi-image">
					<span class="image" style="background-image:url('${config.cdn + row.img_location}')"></span>
				</div>
				<div class="multi-info">
					<div class="left-info">
						<div class="name">${row.product_name}</div>
						<div>${row.color}</div>
						<div class="color"><ul>${color}</ul></div>
					</div>
					<div class="right-info">
						<div class="price">${row.txt_price}</div>
						<span class="size"><ul>${size.join("")}</ul></span>
					</div>
				</div>
				<button class="favorite ${row.whish_flg ? ' on' : ''}" data-goods_no="${row.product_idx}"/>
			</div>								
		`)
	})

	setScroll('multi', standby_product.length);
}

function setScroll(type, imgLength) {

	const $standbyWrap = $("main section.standby-list-wrap");
	const $infoWrap = $standbyWrap.find(".info-wrap");
    const $imagesPaging = $("#images-paging");
	const viewportWidth = $(window).width();

	$(window).scroll(function () {
		// 갤러리 페이징
		$(`.standby_web_product_list .${type}-container`).each(function () {
			$(this).find(".image").load(function () {
				$(this).parent().parent().addClass("complete");
				//$(window).scroll();
			});
			if ($(this).hasClass("complete") == false) return;
			let el_top = $(this).offset().top;
			if ($(window).scrollTop() + ($(window).height() / 2) > el_top && $(window).scrollTop() + ($(window).height() * 1.5) > el_top + $(this).height()) {
				$("#images-paging").html(`${$(this).index() + 1}/${imgLength}`);

				// 마지막 이미지일 경우 페이징 마지막 이미지 상단에 고정
				if ($(this).index() + 1 == imgLength) {
					$("#images-paging").addClass("fixed");
				} else {
					$("#images-paging").removeClass("fixed");
					$("#images-paging").addClass("floating");
				}
			}
			else{
				$("#images-paging").removeClass("floating");
			}
		});

		// 우측 상품 정보 플로팅
		const wrapHeight = $standbyWrap.height();
		const windowScrollTop = $(window).scrollTop();
		const windowHeight = $(window).height();

		if (wrapHeight > windowHeight) {
			const wrapOffsetTop = $standbyWrap.offset().top;
			const wrapBottom = wrapOffsetTop + wrapHeight;
			const infoWrapHeight = $infoWrap.outerHeight();

			if (windowScrollTop + windowHeight >= wrapBottom) {
				$infoWrap.addClass("bottom");
				$infoWrap.removeClass("floating");
			} else if (windowScrollTop + windowHeight >= wrapOffsetTop + windowHeight) {
				$infoWrap.removeClass("bottom");
				$infoWrap.addClass("floating");
			} else {
				$infoWrap.removeClass("bottom");
				$infoWrap.removeClass("floating");
			}
		}
	}).scroll();
}

