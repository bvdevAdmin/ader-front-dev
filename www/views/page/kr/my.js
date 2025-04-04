let t_column = {
	KR : {
		't_01' : "최근 본 제품 내역이 없습니다.",
		't_02' : "위시리스트 내역이 없습니다.",
	},
	EN : {
		't_01' : "No products to recently viewed.",
		't_02' : "Please regist your wish list.",
	}
};

$(document).ready(function() {
	[
		["goods/recently","#swiper-recently",t_column[config.language]['t_01']],
		["wishlist/get","#swiper-wishlist",t_column[config.language]['t_02']]
	].forEach(cont => {	
		drawList(cont)
	});
});

let recently_button = false
let wishlist_button = false

function drawList(cont) {
	console.log(`${cont[0]}`);
	let swiper = [];
	$.ajax({
		url : config.api + cont[0],
		headers : {
			country : config.language
		},
		beforeSend: function(xhr) {
			xhr.setRequestHeader("country",config.language);
		},
		success : function(d) {
			if(d.code == 200) {
				if(d.data.length == 0) {
					$(`${cont[1]} > .swiper-wrapper`).append(`
						<div class="empty" style="margin: auto;">${cont[2]}</div>
					`)
				}
				if(d.data) {
					d.data.forEach(row => {
						// 사이즈
						let size = '';
						if (row.product_type == "B") {
							if(row.product_size) {
								row.product_size.forEach(row2 => {
									//if(row2.stock_status=='STSO' || 'option_name' in row2 == false) return;
									if('option_name' in row2 == false) return;
									size += `
										<li 
											data-no="${row2.product_idx}" 
											data-option_no="${row2.option_idx}" 
											data-type="${row2.size_type}" 
											class="${(row2.stock_status=='STSO')?'soldout':''}">
											${row2.option_name}
										</li>
									`;
								});
							}
						} else {
							size += `Set`;
						}

						// 색상
						let color = '';
						if(row.product_color) {
							row.product_color.forEach(row2 => {
								//if(row2.stock_status=='STSO') return;
								if(row2.color == null) return;
								color += `
									<li data-no="${row2.product_idx}" class="${(row2.stock_status=='STSO')?'soldout':''}">
										<span class="name">${row2.color}</span>
										<span class="colorchip ${(row2.color_rgb=='#ffffff')?'white':''}" style="background-color:${row2.color_rgb}"></span>
									</li>
								`;
							});
						}

						let wishlist_on= cont[0] === 'wishlist/get' ? 'on' : (row.whish_flg ? 'on' : '')
						let recently_display = cont[0] === 'goods/recently' ? 'recently' : 'wishlist'

						$(`${cont[1]} > .swiper-wrapper`).append(`
							<div class="swiper-slide ${cont[0] === 'wishlist/get' ? 'wishlist-container-' + row.product_idx : ''}">
								<a href="/${config.language.toLowerCase()}/shop/${row.product_idx}">
									<span 
										class="image" 
										style="background-image:url('${config.cdn + row.img_location}')"
									></span>
								</a>
								<div class="info">
									<big>${row.product_name}</big>
									<div class="price">${row.price}</div>
									<span class="color"><ul>${color}</ul></span>
									<ul class="size">${size}</ul>
								</div>
								<button type="button" class="favorite ${wishlist_on} ${recently_display} custom" data-goods_no="${row.product_idx}"></button>
							</div>
						`);
					});

						//최근본 제품에만 버튼 이벤트 추가
					$("button.favorite.recently").off("click").click(function () {
						let cont = ["wishlist/get","#swiper-wishlist",t_column[config.language]['t_02']]
						let obj = $(this);
						clickFavoriteButton(obj, cont)
					})

					$("button.favorite.wishlist").off("click").click(function () {
						let cont = ["goods/recently","#swiper-recently"]
						let obj = $(this);
						clickFavoriteButton(obj, cont, () => {
							let wishlist_cont= ["wishlist/get","#swiper-wishlist",t_column[config.language]['t_02']]
							$(`${wishlist_cont[1]} > .swiper-wrapper`).empty()
							drawList(wishlist_cont)
						})
					})

					swiper.push(new Swiper(cont[1], {
						slidesPerView: 'auto',
						spaceBetween: 0,
						loop: false,
						navigation: {
							nextEl: cont[1] + " .swiper-button-next",
							prevEl: cont[1] + " .swiper-button-prev",
						},
					}));
				}
			}
			else {
				if(d.code == 401) {
					makeMsgNoti(null, 'MSG_B_ERR_0018', null);
				} else {
					alert(d.msg);
				}

			}
		}
	});
}

function clickFavoriteButton ( obj, cont, callback ) {
	$(`${cont[1]} > .swiper-wrapper`).empty()
	let uri = ((obj.hasClass("on"))?"delete":"put")
	let product_idx = obj.data("goods_no")

	$.ajax({
		url: config.api + 'wishlist/' + uri,
		headers : {
			country : config.language
		},
		data: {
			product_idx : product_idx
		},
		success: function(d) {
			if(d.code == 200) {
				obj.toggleClass("on");
				if(uri === "delete") {
					$.each($('button.shop.favorite'), function(index, b) {
						let button = $(b);
						if(button.data("goods_no") == product_idx) {
							button.toggleClass("on");
						}
					})
				}
				drawList(cont)

				callback && callback()
			}
			else {
				alert(d.msg);
			}
		}
	});
}
