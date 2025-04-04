document.addEventListener('DOMContentLoaded', function () {
    let country = getLanguage();
    $('#country').val(lang);

    $('.inquiry__tab').hide();
	$('.inquiry__faq__wrap').show();
	
	faqKeywordHandler();
	getFaqCategoryList();
	
    //getInquiry();
	
	clickBtnSearch();
});

function getFaqCategoryList() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/faq/category/get",
		headers: {
			"country":country
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(country, 'MSG_F_ERR_0076', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				
				let div_category = $('.category');
				div_category.html('');
				
				let div_small_category = $('.category__small');
				div_small_category.html('');
				
				if (data != null && data.length > 0) {
					let inq_qna_category_html = "";
					let inq_qna_small_category_html = "";
					
					data.forEach(function (row, index) {
						$('#inq_cate').append(`<option value="${row.no}">${row.title}</option>`);

						inq_qna_category_html += `
							<div class="btn__row">
								<div class="faq__category__btn faq_category" data-category_idx="${row.no}">
									${row.title}
								</div>
							</div>
						`;

						inq_qna_small_category_html += `
							<div class="btn__row">
								<div class="parents__category">
									<div class="faq__category__btn faq_small_category small_category_${row.no}" data-category_idx="${row.no}">
										${row.title}
									</div>
								</div>
							</div>
						`;
					});
					
					div_category.append(inq_qna_category_html);
					div_small_category.append(inq_qna_small_category_html);
				}
				
				makeSelect('inquiry__category');
				makeSelect('inquiry__subcategory');
				
				clickFaqCategory();
				clickFaqSmallCategory();
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function clickFaqCategory() {
	let faq_category = document.querySelectorAll('.faq_category');
	faq_category.forEach(category => {
		category.addEventListener('click',function(e) {
			$('.inquiry__tab').hide();
			$('.inquiry__faq__detail__wrap').show();
			
			let el = e.currentTarget;
			let category_idx = el.dataset.category_idx;
			
			$('.small_category_' + category_idx).click();
		});
	});
}

function clickFaqSmallCategory() {
	let faq_small_category = document.querySelectorAll('.faq_small_category');
	faq_small_category.forEach(category => {
		category.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let category_idx = el.dataset.category_idx;
			
			if (!el.classList.contains('click__btn')) {
				$('.faq_small_category').removeClass('click__btn');
				el.classList.add('click__btn');
				console.log(el.classList);
				
				let category_name = $('#inq_cate option[value=' + category_idx + ']').text();
				//$('.inquiry__category .select-items div:contains("' + category_name + '")').click();
				$('.inquiry__category img').attr('src', '/images/mypage/mypage_down_tab_btn.svg');
				
				getFaqList('click',category_idx);
			} else {
				el.classList.remove('click__btn');
				
				$('.inquiry__tab').hide();
				$('.inquiry__faq__wrap').show();
			}
		});
	});
	
	$('.search__keyword').val('');
}

function makeSelect(divId) {
    var selectDiv = $('.' + divId);
    selectDiv.css('position', 'relative');
    var SelLen = selectDiv.find('select option').length;

    var selectedDiv = ` <div class="select-selected">${selectDiv.find('select option:selected').text()}</div><img src="/images/mypage/mypage_down_tab_btn.svg" style="width:10px;height:5px;position: absolute;right:10px;top:18px;">`;
    selectDiv.append(selectedDiv);

    var selectHideDiv = `<div class="select-items select-hide">`;
    for (var i = 0; i < SelLen; i++) {
        selectHideDiv += `  
                <div>${selectDiv.find(`select option:eq(${i})`).text()}</div>
            `;
    }
    selectHideDiv += `  </div>`;
    selectDiv.append(selectHideDiv);

    selectDiv.find('.select-items').find('div').on('click', function () {
        var clickCountryText = $(this).text();

        var sameCountryOption = selectDiv.find(`select option:contains("${clickCountryText}")`);
        sameCountryOption.prop('selected', true);

        selectDiv.find('.select-selected').text(clickCountryText);

        selectDiv.find('.select-items').toggle();

        if ($(this).parent().parent().attr('class') == 'inquiry__category') {
            var category_no = $('#inq_cate').val();
            getFaqList('click', category_no);
            $('.category__small').find('.faq__category__btn').removeClass('click__btn');
            $('.category__small').find('.faq__category__btn[category-no=' + category_no + ']').addClass('click__btn');
        }
    })

    selectDiv.find('.select-selected').on('click', function () {
        selectDiv.find('.select-items').toggle();
    });
}

function getFaqList(type,param) {
	var param_json = {};

	if (type == 'click') {
		param_json = {
			'category_no': param
		};
	} else if (type == 'search') {
		param_json = {
			'keyword': param
		};
	}
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/faq/get",
		headers: {
			"country":country
		},
		data: param_json,
		dataType: "json",
		error: function (d) {
			makeMsgNoti(country, 'MSG_F_ERR_0015', null);
			//notiModal('자주 묻는 질문 조회처리중 오류가 발생했습니다.');
		},
		success: function (d) {
			if (d.code == 200) {
				let toggle_list_tab = $('.toggle__list__tab.02')
				toggle_list_tab.html('');

				let inq_faq_list_html = "";

				let data = d.data;
				if (data != null && data.length > 0) {
					data.forEach(function (row) {
						inq_faq_list_html +=
							`<div class="toggle__list__tab category">
								<div class="category_title__wrap">
									<p class="title">${row.category_title}</p>
									<img class="faq_category_toggle" data-toggle_type="down" src="/images/mypage/mypage_down_tab_btn.svg">
								</div>
							`;
						if (row.faq_info.length > 0) {
							row.faq_info.forEach(function (faq_row) {
								inq_faq_list_html += `
									<div class="toggle__item">
										<div class="question">
											<span>${faq_row.question}</span>
											<img class="down__up__icon faq_toggle" data-toggle_type="down" src="/images/mypage/mypage_down_tab_btn.svg">
										</div>
										
										<div class="request hidden">
											${xssDecode(faq_row.answer)}
										</div>
									</div>
								`;
							});
						}
						inq_faq_list_html +=
							`</div>`;
					});
				} else {
					switch (country) {
						case "KR":
							inq_faq_list_html = '자주 묻는 질문 검색 결과가 존재하지 않습니다.';
							break;
						case "EN":
							inq_faq_list_html = 'There are no search results for Frequently Asked Questions.';
							break;
						case "CN":
							inq_faq_list_html = '常见问题搜索结果不存在。';
							break;
					}
				}

				toggle_list_tab.append(inq_faq_list_html);
				toggleFaqCategoryEvent();
				clickFaqToggle();
			}
		}
	});
}
function toggleFaqCategoryEvent(){
	$('.faq_category_toggle').unbind('click', toggleFaqCategoryEventHandler);
	$('.faq_category_toggle').on('click',toggleFaqCategoryEventHandler);
}
function toggleFaqCategoryEventHandler(e){
	let selectCategoryTeb = $(e.target).parents('.toggle__list__tab.category');
	let openFlg = selectCategoryTeb.hasClass('active');

	$(e.target).attr('src','/images/mypage/mypage_down_tab_btn.svg');
	$('.toggle__list__tab.category').removeClass('active');
	$('.down__up__icon.faq_toggle').attr('src','/images/mypage/mypage_down_tab_btn.svg');
	selectCategoryTeb.find('.request').addClass('hidden');
	if(openFlg == false){
		$(e.target).attr('src','/images/mypage/mypage_up_tab_btn.svg');
		selectCategoryTeb.addClass('active');
	}
}
function clickFaqToggle() {
	let faq_toggle = document.querySelectorAll('.faq_toggle');
	faq_toggle.forEach(toggle => {
		toggle.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let toggle_type = el.dataset.toggle_type;
			
			let faq_wrap = el.parentNode.parentNode;
			
			let request = faq_wrap.querySelector('.request');
			request.classList.toggle('hidden');
			
			if (toggle_type == "down") {
				el.dataset.toggle_type = "up";
				el.src = "/images/mypage/mypage_up_tab_btn.svg";
			} else {
				el.dataset.toggle_type = "down";
				el.src = "/images/mypage/mypage_down_tab_btn.svg";
			}
		});
	});
}

function faqKeywordHandler() {
	let search_icon = document.querySelectorAll('.search__icon__img');
	search_icon.forEach(search => {
		search.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let param_keyword = el.parentNode.querySelector('.search__keyword').value;
			
			if (param_keyword != null && param_keyword != "") {
				searchFaqList(param_keyword);
			} else {
				return false;
			}
		})
	});
	
	let search_keyword = document.querySelectorAll('.search__keyword');
	search_keyword.forEach(keyword => {
		keyword.addEventListener('keyup',function(e) {
			let el = e.currentTarget;
			let param_keyword = el.value;
			
			if (param_keyword != null && param_keyword != "") {
				if (window.event.keyCode == 13) {
					searchFaqList(param_keyword);
				}
			} else {
				return false;
			}
		})
	});
	
	let init_keyword = document.querySelector('.init_keyword');
	init_keyword.addEventListener('click',function() {
		$('.search__keyword').val('');
		init_keyword.classList.add('hidden');
	});
}

function searchFaqList(param_keyword) {
	$('.inquiry__tab').hide();
	$('.inquiry__faq__detail__wrap').show();
	
	$('.category__small .click__btn').removeClass('click__btn');

	$('.search__keyword').val(param_keyword);

	getFaqList('search',param_keyword);
	document.querySelector('.init_keyword').classList.remove('hidden');
}

function clickBtnSearch() {
	let btn_search_faq = document.querySelector('.btn_search_faq');
	btn_search_faq.addEventListener('click',function(e) {
		let el = e.currentTarget;
		searchAction(el)
	});
}