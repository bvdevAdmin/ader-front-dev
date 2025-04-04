document.addEventListener("DOMContentLoaded", function () {
	setProductFilter();
	initFilterParam();
});

function checkFilterParam() {
	let filter_param = [];
	
	let filter_color = document.querySelector('.filter-content.color');
	let filter_cl = [];
	
	let select_color = filter_color.querySelectorAll('.select');
	if (select_color != null && select_color.length > 0) {
		select_color.forEach(color => {
			let filter_value = color.dataset.filter_idx;
			filter_cl.push(filter_value);
		});
	}
	
	let filter_fit = document.querySelector('.filter-content.fit');
	let filter_ft = [];
	
	let select_fit = filter_fit.querySelectorAll('.select');
	if (select_fit != null && select_fit.length > 0) {
		select_fit.forEach(fit => {
			let filter_value = fit.dataset.fit;
			filter_ft.push(filter_value);
		});
	}
	
	let filter_graphic = document.querySelector('.filter-content.graphic');
	let filter_gp = [];
	
	let select_graphic = filter_graphic.querySelectorAll('.select');
	if (select_graphic != null && select_graphic.length > 0) {
		select_graphic.forEach(graphic => {
			let filter_value = graphic.dataset.graphic;
			filter_gp.push(filter_value);
		});
	}
	
	let filter_line = document.querySelector('.filter-content.line');
	let filter_ln = [];
	
	let select_line = filter_line.querySelectorAll('.select');
	if (select_line != null && select_line.length > 0) {
		select_line.forEach(line => {
			let filter_value = line.dataset.line_idx;
			filter_ln.push(filter_value);
		});
	}
	
	let filter_size = document.querySelector('.filter-content.size');
	let filter_sz = [];
	
	let select_size = filter_size.querySelectorAll('.select');
	if (select_size != null && select_size.length > 0) {
		select_size.forEach(size => {
			let filter_value = size.dataset.filter_idx;
			filter_sz.push(filter_value);
		});
	}
	
	filter_param = {
		'filter_cl' : filter_cl,
		'filter_ft' : filter_ft,
		'filter_gp' : filter_gp,
		'filter_ln' : filter_ln,
		'filter_sz' : filter_sz
	};
	
	return filter_param;
}

function checkFilterProductCnt() {
	let filter_param = checkFilterParam();
	let product_list_wrap = document.querySelector('.product__list__wrap');
	let page_idx = product_list_wrap.dataset.page_idx;
	
	$.ajax({
		type: "post",
		url: api_location + "product/filter/check",
		data: {
			"country": getLanguage(),
			"page_idx": page_idx,
			"filter_param": filter_param
		},
		dataType: "json",
		async: false,
		error: function () {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0003", null);
			// notiModal("필터 적용 상품 수량 체크처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let product_cnt = d.data;
				
				let select_result = document.querySelector('.select-btn .select-result');
				select_result.innerText = product_cnt;
			}
		}
	});
}

function setProductFilter() {
	let set_filter_btn = document.querySelector('.select-btn');
	set_filter_btn.addEventListener('click',function() {
		let filter_param = checkFilterParam();
		
		getProductListByFilter(filter_param);
		
		let filter_container = document.querySelector('.filter-container');
		filter_container.classList.remove('open');
		
		let filter_body = filter_container.querySelector('.filter-body');
		filter_body.classList.remove('open');
	});
}

function initFilterParam() {
	let init_filter_btn = document.querySelector('.reset-btn');
	init_filter_btn.addEventListener('click',function() {
		let filter_color = document.querySelectorAll('.filter-color');
		filter_color.forEach(color => {
			color.classList.remove('select');
		});
		
		let filter_fit = document.querySelectorAll('.filter-fit');
		filter_fit.forEach(fit => {
			fit.classList.remove('select');
		});
		
		let filter_graphic = document.querySelectorAll('.filter-graphic');
		filter_graphic.forEach(graphic => {
			graphic.classList.remove('select')
		});
		
		let filter_line = document.querySelectorAll('.filter-line');
		filter_line.forEach(line => {
			line.classList.remove('select');
		});
		
		let filter_size = document.querySelectorAll('.filter-size');
		filter_size.forEach(size => {
			size.classList.remove('select');
		});
	});
}

function getProductListByFilter(filter_param) {
	let product_list_wrap = document.querySelector('.product__list__wrap');
	let { menu_idx, menu_type, page_idx, last_idx } = product_list_wrap.dataset;

	$.ajax({
		type: "post",
		url: api_location + "product/list/get",
		data: {
			"country": getLanguage(),
			"menu_type": menu_type,
			"menu_idx": menu_idx,
			"page_idx": page_idx,
			"last_idx": last_idx,
			"order_param": order_param,
			"filter_param": filter_param
		},
		dataType: "json",
		beforeSend: function () {
			productLoading('/images/product/loading_img.gif');
		},
		async: false,
		error: function () {
			closeProductLoading();
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0087", null);
			// notiModal("상품 진열페이지 조회처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				let grid_info = data.grid_info;

				if (grid_info.length > 0) {
					let product_list_html = writeProductListHtml(grid_info);
					
					$(".product-wrap").html('');
					$(".product-wrap").append(product_list_html);

					setProductListFadeIn();
					
					bannerHeightBySiblingElements();
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}
