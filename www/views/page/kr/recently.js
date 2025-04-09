let product_total = [];

let param_filter = [];
let param_sort = "";

let filter_name = {
	'KR': {
		'UP': "상의",
		'LW': "하의",
		'HT': "모자",
		'SH': "신발",
		'JW': "장신구",
		'AC': "악세서리",
		'TA': "테크 악세서리"
	},
	'EN': {
		'UP': "Top",
		'LW': "Bottom",
		'HT': "Hat",
		'SH': "Shoes",
		'JW': "Jewerly",
		'AC': "Accessory",
		'TA': "Tech Accessory"
	}
};

let cnt_filter = 0;

let is_phased	= true;
let is_fetching	= false;

// 윤재은
// let swiper = [];

localStorage.setItem("page",0);

let last_idx = 0
,page = localStorage.getItem("page")
,data = {
	page_idx	: get_query_string("page_idx"),
	depth		: get_query_string("depth"),
	header_idx	: get_query_string("header_idx")
};

const session_order		= 'order_recently';
const session_filter	= 'filter_recently';
const session_img		= 'img_recently';
const session_grid		= 'grid_recently';

let order_recently = sessionStorage.getItem(session_order);
if (order_recently != null) {
	order_recently = JSON.parse(order_recently);
	data.param_sort = order_recently;
}

let filter_recently = sessionStorage.getItem(session_filter);
if (filter_recently != null) {
	$('.tools > ul > li > button.filter .icon').addClass('on');
	filter_recently = JSON.parse(filter_recently);
	data.param_filter = filter_recently;
}
else{
	$('.tools > ul > li > button.filter .icon').removeClass('on');
}

let img_recently = sessionStorage.getItem(session_img);
if (img_recently != null) {
	if (img_recently == "P") {
		$("ul#list").parent().removeClass("outfit");
		$('main.goods.list > header section.tools button.showing').removeClass('on');
	} else if (img_recently == "O") {
		$("ul#list").parent().addClass("outfit");
		$('main.goods.list > header section.tools button.showing').addClass('on');	
	}
}

let grid_recently = sessionStorage.getItem(session_grid);
if (grid_recently != null) {
	if (grid_recently == "2") {
		$("ul#list").parent().addClass("col-2");
		$('main.goods.list > header section.tools button.column').addClass('on');
	} else if (grid_recently == "4") {
		$("ul#list").parent().removeClass("col-2");
		$('main.goods.list > header section.tools button.column').removeClass('on');
	}
}

$(document).ready(function() {
	getFilter();
	getTotal();
	setSort_value();
	setFilter_value();
});

$(document).on("click","main.goods.list > header section.tools button",function() {

	if($(this).hasClass("on")) {
		$(this).removeClass("on");
	} else {
		if (!$(this).hasClass('reset')) {
			$("main.goods.list > header section.tools button.on:not(.showing):not(.column)").removeClass("on");
			$(this).addClass("on");
		} else {
			$("#shoplist-tools-filter .grid > ul > li .sort input").attr("checked",false);
			$("#shoplist-tools-filter .grid > ul > li .on").removeClass('on');
			
			param_filter = [];

			setFiltered_count([]);
			data.param_sort		= "";
			data.param_filter	= param_filter;

			data.last_idx = 0;
			
//			get_goods(true);
		}
	}

	if($(this).hasClass("showing")) {
		// 아이템/착용샷 토글
		if($(this).hasClass("on")) {
			$("ul#list").parent().addClass("outfit");

			if (sessionStorage.getItem(session_img)) {
				sessionStorage.removeItem(session_img);
			}

			sessionStorage.setItem(session_img,"O");
			
			// 윤재은
			// swipe 초기화
			// swiper.forEach(row => {
			// 	row.enable();
			// });
		} else {
			$("ul#list").parent().removeClass("outfit");

			if (sessionStorage.getItem(session_img)) {
				sessionStorage.removeItem(session_img);
			}

			sessionStorage.setItem(session_img,"P");
			
			// 윤재은
			// swipe 비활성화
			// if(typeof swiper == 'object' && swiper.length > 0) {
			// 	swiper.forEach(row => {
			// 		row.disable;
			// 	});
			// }
		}
	} else if ($(this).hasClass("column")) {
		// 2/4칸 보기 토글
		if($(this).hasClass("on")) {
			// 2칸 보기
			$("ul#list").parent().addClass("col-2");

			if (sessionStorage.getItem(session_grid)) {
				sessionStorage.removeItem(session_grid);
			}

			sessionStorage.setItem(session_grid,"2");
		} else {
			// 4칸 보기
			$("ul#list").parent().removeClass("col-2");

			if (sessionStorage.getItem(session_grid)) {
				sessionStorage.removeItem(session_grid);
			}

			sessionStorage.setItem(session_grid,"4");
		}
	} else if ($(this).hasClass("btn_filter")) {
		if (param_sort) data.param_sort = param_sort;
		data.param_filter = param_filter
		data.last_idx = 0;

		// 필터 선택에 따라 아이콘 클래스 처리
		const hasActiveFilters = param_filter &&
		(
			(param_filter.filter_cl?.length || 0) > 0 ||
			(param_filter.filter_sz?.length || 0) > 0 ||
			(param_filter.filter_ft?.length || 0) > 0 ||
			(param_filter.filter_gp?.length || 0) > 0 ||
			(param_filter.filter_ln?.length || 0) > 0
		);
		if (param_sort != null) {
			sessionStorage.setItem(session_order,JSON.stringify(param_sort));
		} else {
			sessionStorage.removeItem(session_order);
		}
		if (hasActiveFilters) {
			sessionStorage.setItem(session_filter,JSON.stringify(param_filter));
			$('.tools > ul > li > button.filter .icon').addClass('on');
		} else {
			sessionStorage.removeItem(session_filter);
			$('.tools > ul > li > button.filter .icon').removeClass('on');
		}

		get_goods( true );
	}
});

$("#shoplist-tools-sort input").click(function() {
	$(this).toggleClass("checked");
	if($(this).hasClass("checked") == false) {
		$(this).prop("checked",false);
	}
	$(this).parent().parent().siblings().find("input.checked").removeClass("checked");
});

$(window).scroll(function() {
	if(is_phased == false) return;
	if($(this).scrollTop() + $(this).height() < $("main").height() - $("body > header").height() - ($("body > footer").height() * 2)) return;
	is_phased = false; // 중복 호출 방지

	if(last_idx > 0) {
		data.last_idx = last_idx;
	}

	let product_length = $("ul#list > li").length;
	if (product_length > 0 && product_length == cnt_filter) return;
	get_goods();
}).scroll();

function get_goods( is_list_clear ) {
	if (is_fetching) return; // 중복 호출 방지
	is_fetching = true;

	$.ajax({
		url: config.api + 'goods/recently',
		headers : {
			country : config.language
		},
		data: data,
		beforeSend: function(xhr) {
			xhr.setRequestHeader("country",config.language);
		},
		success: function(d) {
			if(d.code == 200) {
				cnt_filter = d.cnt_filter;
				$('.btn_filter .cnt_filter').text(number_format(d.cnt_filter));

				if ($("#goods-nav-top > ul > li").length == 0) {
					$("#goods-nav > ul").html('')
					$("#goods-nav-top > ul").append(`
						<li>
							<a href="/kr/my" class="on">
								<span class="title">마이페이지</span>
							</a>
						</li>
						<li>
							<a href="#" class="on">
								<span class="title">최근 본 제품</span>
							</a>
						</li>
					`);
				}

				if(!d.data) d.data = []
				if( is_list_clear ) $('#list').empty()
				
				d.data.forEach(row => {
					// 이미지 슬라이드
                    let swiper_container_o = '';
					let swiper_slides_o = [];
                    let swiper_container_p = '';
					let swiper_slides_p = [];
					let outfit_image = '';
					
                    if (row.product_img) {
						if(row.product_img.product_p_img.length > 0) {
							row.product_img.product_p_img.forEach(img => {
								swiper_slides_p.push(`
									<div class="swiper-slide">
										<div class="image-cont">
											<img src="${config.cdn + img.img_location}" loading="lazy">
										</div>
									</div>
								`);
							});

							swiper_container_p = `
								<div class="swiper swiper-container product">
									<div class="swiper-wrapper">
										${swiper_slides_p.join("")}
									</div>
								</div>
							`;
						}

						if(row.product_img.product_o_img.length > 0) {
							row.product_img.product_o_img.forEach(img => {
								swiper_slides_o.push(`
									<div class="swiper-slide">
										<div class="image-cont">
											<img src="${config.cdn + img.img_location}" loading="lazy">
										</div>
									</div>
								`);
							});

							swiper_container_o = `
								<div class="swiper swiper-container outfit">
									<div class="swiper-wrapper">
										${swiper_slides_o.join("")}
									</div>
								</div>
							`;
						}
						
						// 윤재은
						// if (row.product_img.product_p_img.length > 0 && row.product_img.product_p_img.length > 0) {
						// 	// 아이템 <-> 착용샷
						// 	outfit_image = `style="--outfit-src: url('${config.cdn + row.product_img.product_p_img[0].img_location}'); "`;
						// 	if(row.product_img.product_o_img.length > 0) { // 착용샷이 있을 경우 대표 이미지 가져옴
						// 		outfit_image = `style="--outfit-src: url('${config.cdn + row.product_img.product_o_img[0].img_location}'); "`;
						// 	}
						// }
					}
					
					// 사이즈
					let size = '';
					if (row.product_type == "B") {
						if(row.product_size) {
							row.product_size.forEach(row2 => {
								//if(row2.stock_status=='STSO' || 'option_name' in row2 == false) return;

								let stock_status = "";
								if (row2.stock_status == "STSO") {
									stock_status = "soldout";
								} else if (row2.stock_status == "STSC") {
									stock_status = "reorder";
								}

								if('option_name' in row2 == false) return;
								size += `
									<li 
										data-no="${row2.product_idx}" 
										data-option_no="${row2.option_idx}" 
										data-type="${row2.size_type}" 
										class="${stock_status}"
										>
										<span class="name">${row2.option_name}</span>
									</li>
								`;
							});
						}
					} else {
						size = "<li>Set</li>";
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
					
					last_idx = row.display_num;
					$("#list").append(`
						<li class="${(row.stock_status == 'STSO')?'soldout':''}" style="${(row.background_color!='')?'background-color:' + row.background_color:''}">
							<a href="${config.base_url}/shop/${row.product_idx}">
								<span class="image item-${row.product_idx}">
									${swiper_container_p}
									${swiper_container_o}
								</span>
							</a>
							<div class="info">
								<strong>${row.product_name}</strong>
								<span class="price ${row.discount > 0 ? ' discount' : ''}" data-discount="${row.discount}" data-saleprice="${row.sales_price}">
									<span class="cont">${row.price}</span>
								</span>
								<span class="color">
									<ul>${color}</ul>
								</span>
								<span class="size">
									<ul>${size}</ul>
								</span>
							</div>
							<button type="button" class="shop favorite ${(row.whish_flg)?'on':''}" data-goods_no="${row.product_idx}"></button>
						</li>
					`);

                    // inview swipe
					let swiper_option = {
						speed: 400,
						spaceBetween: 0,
						loop : true,
					};

					// 20250408 윤재은
					// swiper.push(new Swiper($(`.item-${row.product_idx} .swiper.outfit`).get(0), swiper_option));
					// swiper.push(new Swiper($(`.item-${row.product_idx} .swiper.product`).get(0), swiper_option));
					new Swiper($(`.item-${row.product_idx} .swiper.outfit`).get(0), swiper_option);
					new Swiper($(`.item-${row.product_idx} .swiper.product`).get(0), swiper_option);

					if($("ul#list").parent().hasClass("col-2")) {
                        let el_top = $("#list > li").last().offset().top,
                            el_bottom = $("#list > li").last().height+el_top;
                        if($(window).scrollTop > el_top || $(window).scrollTop < el_bottom) {
                            // swiper 초기화
                        }
                    }
				});

				$('.image').lazy({
					effect: "fadeIn",
					effectTime: 400,
					threshold: 0
				});

				$(window).scroll(); // 퀵메뉴 재정렬
			}
			else {
				//alert(d.msg);
			}
			setTimeout(() => { is_phased = true; },100);
		},
		complete: function() {
			is_phased	= true;
			is_fetching = false;
		}
	});
}

function getFilter() {
	$.ajax({
		url: config.api + 'goods/filter',
		headers : {
			country : config.language
		},
		data: {
			'filter_type'	: 'recently',
			'filter_param'	: config.member.id
		},
		async: false,
		beforeSend: function(xhr) {
			xhr.setRequestHeader("country",config.language);
		},
		success: function(d) {
			// 필터
			if(d.data && $("#shoplist-tools-filter .grid > ul > li").length == 1) {
				let filter_grid_eq = {
					filter_cl : 1, // 색상
					filter_ft : 2, // 핏
					filter_gp : 3, // 그래픽
					filter_ln : 4, // 라인
					filter_sz : 5, // 사이즈
				};
				
				let filter_title = {
					KR : {
						'filter_cl'		:"색상",
						'filter_ft'		:"핏",
						'filter_gp'		:"그래픽",
						'filter_ln'		:"라인",
						'filter_sz'		:"사이즈",
						'filter_sz_ac'	:"ACC",
						'filter_sz_ht'	:"모자",
						'filter_sz_jw'	:"주얼리",
						'filter_sz_lw'	:"하의",
						'filter_sz_sh'	:"신발",
						'filter_sz_ta'	:"테크 악세서리",
						'filter_sz_up'	:"상의"
					},
					EN : {
						'filter_cl'		:"Color",
						'filter_ft'		:"Fit",
						'filter_gp'		:"Graphic",
						'filter_ln'		:"Line",
						'filter_sz'		:"Size",
						'filter_sz_ac'	:"Accessory",
						'filter_sz_ht'	:"Hat",
						'filter_sz_jw'	:"Jewerly",
						'filter_sz_lw'	:"Lower",
						'filter_sz_sh'	:"Shoes",
						'filter_sz_ta'	:"Tech accessory",
						'filter_sz_up'	:"Upper"
					}
				};

				let filter = ''
				, filter2 = ''
				, filter_sz_eq = 0;
				for(let key in d.data) {
					if(d.data[key] == null) continue;
					filter = '';
					filter2 = '';

					switch(key) {
						/* 필터 색상 */
						case 'filter_cl': // 색상
							filter = '<ul data-filter="color">';
							d.data[key].forEach(row => {
								filter += `
									<li class="filter_CL_${row.filter_idx}" data-type="CL" data-no="${row.filter_idx}">
										${row.filter_name}
										<span class="colorchip" style="background-color:${row.rgb_color}"></span>
									</li>
								`;
							});

							filter += '</ul>';
						break;

						/* 필터 사이즈 */
						case 'filter_sz':
							let filter_sz = d.data[key];

							/* 필터 사이즈 - 상의 */
							let size_UP = filter_sz.filter_sz_up;
							filter += setFilter_sz("UP",size_UP);

							/* 필터 사이즈 - 하의 */
							let size_LW = filter_sz.filter_sz_lw;
							filter += setFilter_sz("LW",size_LW);

							/* 필터 사이즈 - 모자 */
							let size_HT = filter_sz.filter_sz_ht;
							filter += setFilter_sz("HT",size_HT);

							/* 필터 사이즈 - 슈즈 */
							let size_SH = filter_sz.filter_sz_sh;
							filter += setFilter_sz("SH",size_SH);

							/* 필터 사이즈 - 쥬얼리 */
							let size_JW = filter_sz.filter_sz_jw;
							filter += setFilter_sz("JW",size_JW);

							/* 필터 사이즈 - 악세서리 */
							let size_AC = filter_sz.filter_sz_ac;
							filter += setFilter_sz("AC",size_AC);

							/* 필터 사이즈 - 테크 악세서리 */
							let size_TA = filter_sz.filter_sz_ta;
							filter += setFilter_sz("TA",size_TA);
						break;

						/* 필터 핏 */
						case 'filter_ft': // 핏
							filter = '<ul data-filter="fit">';
							d.data[key].forEach(row => {
								filter += `<li class="filter_FT_${row.fit}" data-type="FT">${row.fit}</li>`;
							});
							filter += '</ul>';
						break;

						/* 필터 그래픽 */
						case 'filter_gp': // 그래픽
							filter = '<ul data-filter="graphic">';
							d.data[key].forEach(row => {
								filter += `<li class="filter_GP_${row.graphic}" data-type="GP">${row.graphic}</li>`;
							});
							filter += '</ul>';
						break;

						/* 필터 라인 */
						case 'filter_ln': // 라인
							filter = '<ul data-filter="line">';
							d.data[key].forEach(row => {
								filter += `<li class="filter_LN_${row.line_idx}" data-type="LN" data-no="${row.line_idx}">${row.line_name}</li>`;
							});
							filter += '</ul>';
						break;
					}

					$("#shoplist-tools-filter .grid > ul").append(`
						<li>
							<h3>${filter_title[config.language][key]}</h3>
							${filter}
						</li>
					`);
				}

				clickSort();

				clickFilter();
			}
		}
	});
}

function getTotal() {
	$.ajax({
		url: config.api + 'goods/total',
		headers : {
			country : config.language
		},
		data: {
			'total_type'	: 'recently',
			'total_param'	: config.member.id
		},
		async: false,
		beforeSend: function(xhr) {
			xhr.setRequestHeader("country",config.language);
		},
		success: function(d) {
			if (d.data != null && d.data.length > 0) {
				product_total = d.data;
			}
		}
	});
}

function setFilter_sz(filter_type,data) {
	let filter = "";
	
	if (data != null && data.length > 0) {
		data.forEach(function(row) {
			filter += `<li class="filter_SZ_${row.filter_idx}" data-type="SZ" data-no="${row.filter_idx}" data-sort="${row.size_sort}">${row.filter_name}</li>`;
		});
		
		filter = `
			<div class="item">
				<h3>${filter_name[config.language][filter_type]}</h3>
				<ul data-filter="${filter_type}">
					${filter}
				</ul>
			</div>
		`;
	}
	
	return filter;
}

function clickSort() {
	$('.param_sort').click(function() {
		param_sort = $('input[name=sort]:checked').val();
	});
}

function clickFilter() {
	$("#shoplist-tools-filter .grid > ul > li ul > li").click(function() {
		$(this).toggleClass("on");
		
		let filter_cl = new Array();
		let filter_sz = new Array();
		let filter_ft = new Array();
		let filter_gp = new Array();
		let filter_ln = new Array();
		
		let filter_selected = document.querySelectorAll("#shoplist-tools-filter .grid > ul > li ul > li.on");
		filter_selected.forEach(selected => {
			let filter_type = selected.dataset.type;
			
			let filter_no = 0;
			if (filter_type != "FT" || filter_type != "GP") {
				filter_no = parseInt(selected.dataset.no);
			}
			
			switch (filter_type) {
				case "CL" :
					filter_cl.push(filter_no);
					break;
				
				case "SZ" :
					filter_sz.push(filter_no);
					break;
				
				case "FT" :
					filter_ft.push($(selected).html());
					break;
				
				case "GP" :
					filter_gp.push($(selected).html());
					break;
				
				case "LN" :
					filter_ln.push(filter_no);
					break;
			}
		});

		param_filter = {
			'filter_cl'		:filter_cl,
			'filter_sz'		:filter_sz,
			'filter_ft'		:filter_ft,
			'filter_gp'		:filter_gp,
			'filter_ln'		:filter_ln
		}

        setFiltered_count(param_filter);
	});
}

function setFiltered_count(param) {
    // 필터가 초기화된 상태인지 확인
    const isParamEmpty = !param || (
        (param.filter_cl?.length || 0) === 0 &&
        (param.filter_sz?.length || 0) === 0 &&
        (param.filter_ft?.length || 0) === 0 &&
        (param.filter_gp?.length || 0) === 0 &&
        (param.filter_ln?.length || 0) === 0
    );

    let filtered_count;

    if (isParamEmpty) {
        // 필터가 초기화된 상태 -> 전체 제품 수 사용
        filtered_count = product_total.length;
    } else {
        // 필터 기준으로 제품 수 계산
        filtered_count = product_total.filter(product => { 
			let match_cl = param?.filter_cl.length === 0 || param?.filter_cl.some(cl => product.filter_CL.includes(cl.toString()));
			let match_sz = param?.filter_sz.length === 0 || param?.filter_sz.some(sz => product.filter_SZ.includes(sz.toString()));
			let match_ft = param?.filter_ft.length === 0 || param?.filter_ft.some(ft => product.filter_FT.trim() === ft.trim());
			let match_gp = param?.filter_gp.length === 0 || param?.filter_gp.some(gp => product.filter_GP.trim() === gp.trim());
			let match_ln = param?.filter_ln.length === 0 || param?.filter_ln.some(ln => product.filter_LN === ln);
			return match_cl && match_sz && match_ft && match_gp && match_ln;
		}).length;
    }

    // 업데이트
    $('.btn_filter .cnt_filter').text(filtered_count);
    return filtered_count;
}

function setSort_value() {
	if (data.param_sort != null) {
		let div_sort = document.querySelectorAll('.param_sort');
		if (div_sort != null && div_sort.length > 0) {
			div_sort.forEach(div => {
				if (div.value == data.param_sort) {
					$(div).prop('checked',true);
				}
			});
		}
	}
}

function setFilter_value() {
	if (data.param_filter != null) {
		if (data.param_filter.filter_cl) {
			let filter_cl = data.param_filter.filter_cl;
			if (filter_cl != null && filter_cl.length > 0) {
				filter_cl.forEach(cl => {
					$(`.filter_CL_${cl}`).addClass('on');
				});
			}
		}
		
		if (data.param_filter.filter_ft) {
			let filter_ft = data.param_filter.filter_ft;
			if (filter_ft != null && filter_ft.length > 0) {
				filter_ft.forEach(ft => {
					$(`.filter_FT_${ft}`).addClass('on');
				});
			}
		}
	
		if (data.param_filter.filter_gp) {
			let filter_gp = data.param_filter.filter_gp;
			if (filter_gp != null && filter_gp.length > 0) {
				filter_gp.forEach(gp => {
					$(`.filter_GP_${gp}`).addClass('on');
				});
			}
		}
		
		if (data.param_filter.filter_ln) {
			let filter_ln = data.param_filter.filter_ln;
			if (filter_ln != null && filter_ln.length > 0) {
				filter_ln.forEach(ln => {
					$(`.filter_LN_${ln}`).addClass('on');
				});
			}
		}
	
		if (data.param_filter.filter_sz) {
			let filter_sz = data.param_filter.filter_sz;
			if (filter_sz != null && filter_sz.length > 0) {
				filter_sz.forEach(sz => {
					$(`.filter_SZ_${sz}`).addClass('on');
				});
			}
		}
	}
}