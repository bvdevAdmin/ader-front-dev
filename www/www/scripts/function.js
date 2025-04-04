String.prototype.fillZero = function(n) {
	var str   = this;
	var zeros = "";
	

	if(str.length < n) {
		for(i = 0; i < n - str.length; i++) {
			zeros += '0';
		}
	}

	return zeros + str;
}

String.prototype.fillSpace = function(n) {
	var str   = this;
	var space = "";
	
	if(str.length < n)
	{
		for(i = 0; i < n - str.length; i++)
		{
			space += ' ';
		}
	}

	return str + space;
}

String.prototype.byteLength = function() {
	var len = 0;

	for(var i=0; i<this.length; i++) {
		len += (this.charCodeAt(i) > 127) ? 2 : 1;
	}

	return len;
}

String.prototype.substrKor = function(idx, len) {
	if(!this.valueOf()) return "";

	var str = this;
	var pos = 0;

	for(var i=0; pos<idx; i++) {
		pos += (str.charCodeAt(i) > 127) ? 2 : 1;
	}

	var beg = i;
	var byteLen = str.byteLength();
	var lim = 0;			

	for(var i=beg; i<byteLen; i++) {
		lim += (str.charCodeAt(i) > 127) ? 2 : 1;

		if(lim > len) { 
			str = str.substring(beg, i);
			break;
		}
	}

	return str;   
}


function number_format(n) {
	if(isNaN(n)) return n;

	let is_minus = '';
	if(n < 0) is_minus = '-';
	n = new String(n);
	n = n.replace("-","");
	let result = '';
	for(let i = 1 ; i <= n.length ; i++) {
		if((n.length - i+1)%3  == 0 && i > 1) result += ",";
		result += n.substr(i-1,1);
	}
	return is_minus + result;
}


function nl2br(str) {
	str = trim(str);
	str = str.replace(eval("/\\n/gi"), "<br>\n");
	return str;
}


function is_numeric(input) {
	return (input - 0) == input && (''+input).trim().length > 0;
}

function is_email(email) {
	let regex = new RegExp(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
	
	return regex.test(email);
}

function nl2br(str) {
	 //return str.replace(/\n/g, "<br />" + String.fromCharCode(10));
	 return str.replace(/\n/g, "<br />");
}

function appendzero(n,space) {
	let result = n,num = 1;
	if(typeof space == 'undefined') space = 2;
	for(let i = 1 ; i<space; i++) {
		num *= 10;
	}

	for(let i = num ; i > 1 ; i/=10) {
		if(n < i) result = "0" + result;
	}

	return result;
}

function addzero(n,space) {
	return appendzero(n,space);
}

function get_query_string(sKey) {
    var sQueryString = document.location.search.substring(1);
	var aField = [],aFields = [];
    var aParam       = {};

	
    if (sQueryString != "") {
		aFields = sQueryString.split("&");
		for (var i=0; i<aFields.length; i++) {
			aField = aFields[i].split('=');
			aParam[aField[0]] = aField[1];
		}

		aParam.page = aParam.page ? aParam.page : 1;
		return sKey ? aParam[sKey] : aParam;
	}
	else {
		sQueryString = document.location.pathname;
		aFields = sQueryString.split("/");
		aParam = aFields;
		
		
		if(aFields[1] == 'category' && sKey == 'cate_no') return parseInt(aFields[3]);
		else if(aFields[1] == 'product') {
			if(sKey == 'cate_no' && aFields.length > 5) return parseInt(aFields[6]);
			else if(sKey == 'product_no') return parseInt(aFields[4]);
		}
	}
}

function link_anchor(url) {
	if (url != null && url.length > 0) {
		return url.replace('/product/list','/store').replace('/product/best','/store');
	}
}


function get_parent_by_tag(obj,tag) {
	if(obj[0].nodeName == tag.toUpperCase()) {
		return obj;
	}
	else {
		if(obj[0].nodeName == 'BODY') {
			return false;
		}
		else {
			return get_parent_by_tag($(obj).parent(),tag);
		}
	}
}

function get_parent_by_class(obj,cls) {
	if($.inArray(cls,obj[0].classList) > -1) {
		return obj;
	}
	else {
		if(obj[0].nodeName == 'BODY') {
			return false;
		}
		else {
			return get_parent_by_class($(obj).parent(),cls);
		}
	}
}


/*======================================================================================*/
/*  쿠키
/*  --
/*  작성일		: 2023.09.06
/*  최종수정일	:
/*
/*======================================================================================*/
class Cookie {

	constructor() {
	}
	
	set( name, value, expiredays ) {
		let endDate = new Date();
		endDate.setDate(endDate.getDate()+expiredays);
		document.cookie = name + "=" + escape( value ) + "; path=/; expires=" + endDate.toGMTString() + ";" ;
	}

	get(name) {
		let nameOfCookie = name + "="
			, x = 0
			, endOfCookie = -1;
		while ( x <= document.cookie.length ) {
			var y = (x+nameOfCookie.length);
			if ( document.cookie.substring( x, y ) == nameOfCookie ) {
				if ( (endOfCookie=document.cookie.indexOf( ";", y )) == -1 ) endOfCookie = document.cookie.length;
				return unescape( document.cookie.substring( y, endOfCookie ) );
			}
			x = document.cookie.indexOf( " ", x ) + 1;
			if ( x == 0 ) break;
		}
		return null;
	}
	
	remove(name) {
		// 동일한 키(name)값으로
		// 1. 만료날짜 과거로 쿠키저장
		// 2. 만료날짜 설정 않는다. 
		//    브라우저가 닫힐 때 제명이 된다    

		let date = new Date(); // 오늘 날짜 
		let validity = -1;
		date.setDate(date.getDate() + validity);
		document.cookie =
			  name + "=;expires=" + date.toGMTString();
	}
}


/*======================================================================================*/
/*  달력
/*  --
/*  작성일		: 2023.09.06
/*  최종수정일	:
/*
/*======================================================================================*/
function set_calendar(obj,date,fn) {
	switch(typeof date) {
		case 'string':
			if(date != "") {
				date = date.split('-');
				date[1] = date[1] - 1;
				date = new Date(date[0], date[1], date[2]);
			}
			else {
				date = null;
			}
		case 'object':
			break;
		case 'function':
			fn = date;
		default:
			date = new Date();
	}
	if(date == null) date = new Date();

	var currentYear = date.getFullYear(); //년도를 구함
	var currentMonth = date.getMonth() + 1; //연을 구함. 월은 0부터 시작하므로 +1, 12월은 11을 출력
	var currentDate = date.getDate(); //오늘 일자.
	var nowDate = currentYear + "-" + currentMonth + "-" + currentDate;
	date.setDate(1);
	var currentDay = date.getDay();
	//이번달 1일의 요일은 출력. 0은 일요일 6은 토요일

	var lastDate = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	if( (currentYear % 4 === 0 && currentYear % 100 !== 0) || currentYear % 400 === 0 )
	lastDate[1] = 29;
	//각 달의 마지막 일을 계산, 윤년의 경우 년도가 4의 배수이고 100의 배수가 아닐 때 혹은 400의 배수일 때 2월달이 29일 임.

	var currentLastDate = lastDate[currentMonth-1];
	var week = Math.ceil( ( currentDay + currentLastDate ) / 7 );
	//총 몇 주인지 구함.

	if(currentMonth != 1)
	var prevDate = currentYear + '-' + ( currentMonth - 1 ) + '-01';
	else
	var prevDate = ( currentYear - 1 ) + '-12-01';
	//만약 이번달이 1월이라면 1년 전 12월로 출력.

	if(currentMonth != 12) {
		var nextDate = currentYear + '-' + ( currentMonth + 1 ) + '-01';
	}
	else {
		var nextDate = ( currentYear + 1 ) + '-01-01';
	}
	//만약 이번달이 12월이라면 1년 후 1월로 출력.
	if( currentMonth < 10 )	var currentMonth = '0' + currentMonth;
	//10월 이하라면 앞에 0을 붙여준다.

	if($(obj).find("tbody").length == 0) {
		$(obj).html(`
			<table>
				<thead>
					<tr>
						<th colspan="7">
							<button type="button" class="prev"></button>
							<button type="button" class="next"></button>
							<div class="ym"></div>
						</th>
					</tr>
					<tr>
						<td>SUN</td>
						<td>MON</td>
						<td>TUE</td>
						<td>WED</td>
						<td>THU</td>
						<td>FRI</td>
						<td>SAT</td>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		`);
	}
	
	$(obj).find(".ym").text(currentYear + "." + appendzero(parseInt(currentMonth)));

	$(obj).find(".year").html(parseInt(currentYear));
	$(obj).find(".month").html(parseInt(currentMonth));
	if($(obj).hasClass("calendar")) {
		$(obj).children("button.prev").off().click(function() {
			draw_calendar(obj,prevDate,fn);
		});
		$(obj).children("button.next").off().click(function() {
			draw_calendar(obj,nextDate,fn);
		});
	}

	let calendar = '',
		dateNum = 1-currentDay;

	for(var i = 0; i < week; i++) {
		calendar += '<tr>';
		for(var j = 0; j < 7; j++, dateNum++) {
			calendar += '<td ';
			if( dateNum < 1 || dateNum > currentLastDate ) {
				calendar += ' class="none"></td>';
				continue;
			}
			calendar += 'id="c' + currentYear + '-' + appendzero(parseInt(currentMonth)) + '-' + appendzero(dateNum) + '" class="';
			if(new Date(currentYear,currentMonth-1,dateNum+1) > new Date()) {
				calendar += 'able ';
				if(new Date(currentYear,currentMonth-1,dateNum) < new Date()) {
					calendar += 'now';
				}
			}
			calendar += '"><div data-date="' + currentYear + '-' + appendzero(parseInt(currentMonth)) + '-' + appendzero(dateNum) + '">';
			calendar += dateNum + '<span class="cont"></span></div></td>';
		}
		calendar += '</tr>';
	}

	$(obj).find("tbody")
		.data("date",nowDate)
		.html(calendar);

	$(obj).find("td.able").off().click(function() {
		if($(this).hasClass("able") == false) return;
		else if($(obj).hasClass("terms") && $(this).hasClass("has-booking")) return;
		
		if($(obj).data("option") && $(obj).data("option").indexOf("multiple") > -1) {
			$(this).toggleClass("on");
		}
		else {
			$(obj).find("td.on").removeClass("on");
			$(this).addClass("on");
		}
		$(obj).data("date",$(this).find("div").data("date"));
		if(typeof fn == 'function') {
			fn.call(obj, $(this), $(this).find("div").data("date"),"date");
		}
	});

	/*
	if($(obj).hasClass("col") && $(obj).hasClass("next")) {
		$(this).parent().parent().find("button.next").click();
	}
	else {
		$(obj).find("td.able").off().click(function() {
			if($(this).hasClass("able") == false) return;
			else if($(obj).hasClass("terms") && $(this).hasClass("has-booking")) return;
			if($(obj).data("option") && $(obj).data("option").indexOf("multiple") > -1) {
				$(this).toggleClass("on");
			}
			else {
				$(obj).find("td.on").removeClass("on");
				$(this).addClass("on");
			}
			$(obj).data("date",$(this).find("div").data("date"));
			if(typeof fn == 'function') {
				fn.call(obj, $(this).find("div").data("date"),"date");
			}
		});
	}
	*/

	if(typeof fn == 'function') {
		fn.call(obj, $(this), nowDate,"month");
	}
	
	// 만약 기간을 선택하는 달력일 경우
	if($(obj).find(".next-month").length > 0) {
		draw_calendar($(obj).find(".next-month").get(0),nextDate,fn);
	}
}




/*======================================================================================*/
/*  장바구니
/*  -----
/*  작성일		: 2023.09.06
/*  최종수정일	:
/*
/*======================================================================================*/
get_cart = (is_slide, all_check) => {
	$.ajax({
		url: config.api + "cart/get",
		headers : {
			country : config.language
		},
		error: function () {
			makeMsgNoti(config.language,"MSG_F_ERR_0023", null);
		},
		success: function (d) {
			let t_delivery = {
				KR : "기본 배송지를 선택해주세요.",
				EN : "Please select the default address."
			};

			function set_total_result() {
				let result_goods_total	= 0;
				let result_discount		= 0;
				let result_delivery		= 0;
				let result_goods_qty	= 0;
				
				$("#cart-list > li").has("input[name='cart_no[]']:checked").each(function() {
					let qty	= parseInt($(this).find("input[name='qty']").val());
					price		= $(this).find(".qty-cont").data("price");
					discount	= $(this).find(".qty-cont").data("discount");
					
					result_goods_total	+= price * qty;
					result_discount		+= discount * qty;
					result_goods_qty++;
				});

				if (config.language == "KR") {
					if (result_goods_total - result_discount + result_delivery < 80000) {
						result_delivery = d.data.price_delivery;
					} else {
						result_delivery = 0;
					}
				} else if (config.language == "EN") {
					if (result_goods_total - result_discount + result_delivery < 300) {
						result_delivery = d.data.price_delivery;
					} else {
						result_delivery = 0;
					}
				}

				let price_pg = result_goods_total - result_discount + result_delivery;

				if (config.language == "KR") {
					result_goods_total	= number_format(result_goods_total)
					result_discount		= number_format(result_discount)
					result_delivery		= number_format(result_delivery)
					price_pg			= number_format(price_pg)
				} else {
					result_goods_total	= result_goods_total.toLocaleString('en-US');
					result_discount		= result_discount.toLocaleString('en-US');
					result_delivery		= result_delivery.toLocaleString('en-US');
					price_pg			= price_pg.toLocaleString('en-US');
				}
				
				$("#frm-side-cart-total-goods").text(result_goods_total);	/* 상품 합계 */
				$("#frm-side-cart-total-discount").text(result_discount);	/* 회원 할인 합계 */
				$("#frm-side-cart-total").text(price_pg);					/* 총 결제 금액 */
				$("#side-cart-num").text(result_goods_qty);

				if (d.data.default_address == true) {
					$("#frm-side-cart-delivery").text(result_delivery);		/* 배송비 */
				} else {
					$("#frm-side-cart-delivery").text(`${t_delivery[config.language]}`);		/* 기본 배송지 미선택 */
				}
			}
			
			if(d.code == 200) {
				$("#cart-list").empty();

				if(d.data && d.data.basket_cnt > 0) {
					$("#frm-side-cart .empty").removeClass("on");
					$("#side-cart-num").text(d.data.basket_cnt);
					
					d.data.basket_info.forEach(row => {
						option_name = row.option_name
						if (row.product_type === "S" && Array.isArray(row.set_product)) {
							let setOptions = row.set_product.map(product => product.option_name).join(" / ");
							row.option_name += ` ( ${setOptions} )`;
						}

						let disabled = "";
						if (row.stock_status == "STSO") {
							//disabled = "disabled";
						}

						$("#cart-list").append(`
							<li>
								<div class="thumbnail" style="background-image:url('${config.cdn + row.product_img}')"></div>
								
								<label class="check">
									<input type="checkbox" name="cart_no[]" value="${row.basket_idx}" ${disabled}>
									<i></i>
								</label>
								<div class="name">
									${row.product_name}
								</div>
								<div class="price${row.discount > 0 ? ' discount' : ''}${row.stock_status === 'STSO' ? ' soldout' : ''}" data-discount="${row.discount}" data-saleprice="${row.sales_price}">
									${row.t_price}
								</div>
								<div class="color">
									${row.color}
									<span class="colorchip" style="background-color:${row.color_rgb}"></span>
								</div>
								<div class="size">
									${row.option_name}
								</div>
								<div class="qty-cont" data-goods_no="${row.product_idx}" data-price="${row.sales_price}" data-discount="${row.member_discount}">
									<div class="label">Qty</div>
									
									<button type="button" class="decrease ${(row.basket_qty > 1)?'on':''}"></button>
									<button type="button" class="increase on"></button>
									
									<input type="number" name="qty" value="${row.basket_qty}" readonly>
								</div>
								
								<div class="total-price">
									${row.basket_price}
								</div>
							</li>
						`);
						let $parentLi = $("#cart-list").children("li").last(); // 가장 최근에 추가된 <li> 요소 선택
						let children = [];
						if (row.product_type == 'S') {
							row.set_product.forEach(row2 => {
								const $childLi = $(`
									<li class="${row.product_idx}" style="display: none;">
										<div class="thumbnail" style="background-image:url('${config.cdn + row2.product_img}')"></div>
										<div class="name">${row2.product_name}</div>
										<div class="color">${row2.color}
											<span class="colorchip" style="background-color:${row2.color_rgb}"></span>
										</div>
										<div class="size">${row2.option_name}</div>
									</li>
								`);
								children.push($childLi);
							});

							$parentLi.css('cursor', 'pointer');
							$parentLi.on('click', function () {
								$(`.${row.product_idx}`).stop().slideToggle(300); // 300ms 동안 슬라이드 효과
							});

							children.forEach($childLi => {
								$parentLi.after($childLi); // 부모의 바로 뒤에 자식들을 추가
							});
						}
					});
					

					$("#frm-side-cart input[name='cart_no[]']").change(function () {
						set_total_result();
					});

					$("#cart-list .qty-cont button").click(function() {
						if($(this).hasClass("on") == false) return;

						let obj = $(this).parent()
							, price = obj.data("price")
							, qty = parseInt(obj.find("input[name='qty']").val());
						if($(this).hasClass("decrease")) { // 수량 감소
							if(qty - 1 > 0) qty--;
							if(qty == 1) $(this).removeClass("on");
						}
						else { // 수량 증가
							qty++;
							$(this).prev().addClass("on"); // 수량감소 버튼 on
						}
						
						// 합계 계산
						$.ajax({
							url: config.api + "cart/quantity",
							data : {
								basket_idx : obj.parent().find("input[name='cart_no[]']").val(),
								stock_status : "STIN",
								basket_qty : qty,
								product_idx : obj.data("goods_no")
							},
							async:false,
							success: function (d) {
								if(d.code == 200) {	
									obj.find("input[name='qty']").val(qty);

									if (config.language == "KR") {
										obj.next().text(number_format(qty * price)); // 상품가격
									} else if (config.language == "EN") {
										obj.next().text((qty * price).toLocaleString('en-US')); // 상품가격
									}
									
									set_total_result();
								}
								else {
									alert(d.msg);
								}
							}
						});
					});
				}
				else {
					$("#frm-side-cart .empty").addClass("on");
				}
				
				if (typeof is_slide == 'boolean' && is_slide == true) {
					$("#tnb a[data-side='shoppingbag']").click();
				}
				
				if (typeof all_check == 'boolean' && all_check == true) {
					$("#frm-side-cart input[name='all_check']").click();
				}
				
				$("#frm-side-cart input[name='all_check']").click(function() {
					set_total_result();
				});
				
				set_total_result();
			} else {
				alert(d.msg);
			}
		}
	});
}


/*======================================================================================*/
/*  Modal Popup
/*  --------------
/*  작성일		: 2014.05.05
/*  최종수정일	: 2020.02.19
/*	사용법		: modal(페이지, 전달 값)
/*
/*======================================================================================*/
modal = (url,data) => {
	if(typeof url == 'boolean' && url == false) {
		modal_close();
	}
	else {
		let path_name = location.pathname;
		path_name = path_name.replace(`${config.base_url}/`,'');
		path_name = path_name.replace('/','-');
		
		$.ajax({
			//url: config.modal + location.pathname + "/" + url,
			url: config.modal + path_name + "-" + url,
			data: data,
			dataType: "text",
			error: function(msg) {
				alert("오류가 발생했습니다");
			},
			success: function(d) {
				if(d != "") {
					let id = `_modal_alert_${new Date().getTime()}_${$("body > .modal.alert").length + 1}`;
					$("body").addClass("on-modal").append(`<section class="modal" id="${id}">${d}</section>`);
					if(typeof set_ui == "function") set_ui($(`#${id}`));
					$(`#${id}`).find("button.close,button.cancel").click(function() {
						if(window.location.hash == "") {
							modal_close();
						}
						else {
							window.location.hash = "close";
						}
					});
					setTimeout(function() {
						$(`#${id}`).addClass("on");
					},1);
				}
			}
		});
	}
}

modal_close = (close_yn) => {
	let obj = $("section.modal").last();

	$(obj).addClass("off");
	setTimeout(() => {
		$(obj).removeClass("on")
		if(typeof close_yn == "undefined" || close_yn == false) {
			$(obj).remove();
		}
		if($("section.modal").length == 0) {
			$("body").removeClass("on-modal");
		}
	},1);
}

alert = (body,fn) => {
	if ($('.modal.alert').length == 0) {
		if(typeof body == 'string') {
			body = {
				msg : body,
				ok : fn
			};
		}
	
		let id = `_modal_alert_${new Date().getTime()}_${$("body > .modal.alert").length + 1}`;
	
		$("body")
			.addClass("on-modal")
			.append(`
				<section class="modal alert" id="${id}">
					<section>
						<section>
							<button type="button" class="close"></button>
							<article>
								<p>${body.msg}</p>
							</article>
						</section>
					</section>
				</section>
			`);
	
		$(`#${id}`).find("button.close,button.cancel").click(function() {
			modal_close();
			if(typeof body.ok == 'function') {
				setTimeout(body.ok,1);
			}
		});
		
		setTimeout(() => {
			$(`#${id}`).addClass("on");
		},1);
	}
}

confirm = (body,fn) => {
	if(typeof body == 'string') {
		body = {
			msg : body,
			ok : fn
		};
	}
	
	let id = `_modal_alert_${new Date().getTime()}_${$("body > .modal.confirm").length + 1}`;
	
	let msg_btn = {
		KR : {
			't_01' : "확인",
			't_02' : "취소"
		},
		EN : {
			't_01' : "Confirm",
			't_02' : "Cancel"
		}
	}

	$("body")
		.addClass("on-modal")
		.append(`
			<section class="modal confirm" id="${id}">
				<section>
					<section>
						<button type="button" class="close"></button>
						
						<article>
							<h3>${body.title}</h3>
							<p></p>
							<p>${body.body}</p>
						</article>
						
						<div class="row_flex__body">
							<button type="button" class="btn confirm">${msg_btn[config.language]['t_01']}</button>
							<button type="button" class="btn cancel">${msg_btn[config.language]['t_02']}</button>
						</div>
					</section>
				</section>
			</section>
		`);
	
	$(`#${id}`).find("button.confirm").click(function() {
		if(typeof body.ok == 'function') {
			setTimeout(body.ok,1);
			
			modal_close();
		}
	});
	
	$(`#${id}`).find("button.close,button.cancel").click(function() {
		modal_close();
	});
	
	setTimeout(() => {
		$(`#${id}`).addClass("on");
	},1);
}

decodeHTMLEntities = str => {
	if(str !== undefined && str !== null && str !== '') {
		str = String(str);
		str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
		str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');

		let element = document.createElement('div');
		element.innerHTML = str;
		str = element.textContent;
		element.textContent = '';
	}

	return str;
}


makeMsgNoti = (country, msg_code, mapping_arr, fn) => {
	$.ajax({
		url: config.api + "common/msg",
		headers: {
		   country : config.language
		},
		data: {
			msg_code : msg_code
		},
		error: function () {
			alert("메세지정보를 얻는데 실패했습니다.");
		},
		async : true, 
		success: function (d) {
			if (d.code == 200) {
				let msg_text = d.data.msg_text;
				if(mapping_arr != null){
					msg_text = editMsgByMapping(msg_text,mapping_arr);
				}
				alert(msg_text,fn);
				console.log( 'aa', msg_text, msg_code )

				if(msg_code == 'MSG_F_ERR_0074' || msg_code == 'MSG_F_ERR_0059' || msg_code == 'MSG_F_INF_0001' || msg_code == 'MSG_B_ERR_0018' || msg_code == 'MSG_B_ERR_00125'){

					let closeBtn = $("section.modal .close");
					closeBtn.on('click', () => { location.href = `/${localStorage.getItem("lang").toLowerCase()}/login` });
				}
				else if(msg_code == 'MSG_F_ERR_0112' || msg_code == 'MSG_F_ERR_0113') {
					let closeBtn = $("section.modal .close");
					closeBtn.on('click', () => {
						location.href = `/${localStorage.getItem("lang").toLowerCase()}`
					});
				}
			}
		}
	});
}

alert_noti = (msg_code, mapping_arr, fn) => {
	makeMsgNoti(null, msg_code, mapping_arr, fn);
}

editMsgByMapping = (mapping_arr, text) => {
	let editText = '';
	mapping_arr.forEach(function(e){
		let regex = new RegExp(`${e.key}`,'gi');
		editText = text.replace(regex, e.value);
	})
	return editText;
}

notiModal = (main, sub) => {
	if (document.querySelector('#notimodal-modal') !== null) {
		document.querySelector('#notimodal-modal').remove();
	}
	const body = document.body;
	const notimodalContainner = document.createElement("div");
	notimodalContainner.id = "notimodal-modal";
	notimodalContainner.className = "notimodal-containner";
	notimodalContainner.innerHTML = `
	<div class="notimodal__background">
		<div class="notimodal__wrap">
			<div class="notimodal__box">
				<div class="close-btn">
					<svg xmlns="http://www.w3.org/2000/svg" width="12.707" height="12.707" viewBox="0 0 12.707 12.707">
						<path data-name="선 1772" transform="rotate(135 6.103 2.736)" style="fill:none;stroke:#343434" d="M16.969 0 0 .001"></path>
						<path data-name="선 1787" transform="rotate(45 -.25 .606)" style="fill:none;stroke:#343434" d="M16.969.001 0 0"></path>
					</svg>
				</div>
				<h1 class="title">${main === undefined ? "" : main}</h1>
				<p>${sub === undefined ? "" : sub}</p>
			</div>
		</div>
	</div>
	`
	body.appendChild(notimodalContainner)

	this.openModal = (() => {
		notimodalContainner.classList.add("open");
		modalClose();
	})();

	function modalClose() {
		let closeBtn = document.querySelector(`#notimodal-modal .close-btn`);
		closeBtn.addEventListener('click', function () {
			notimodalContainner.classList.remove("open");
			document.querySelector('#notimodal-modal').remove();
		});
	}
}

/*======================================================================================*/
/*  페이징
/*  --------------
/*  작성일     : 2024.10.1
/*  최종수정일 :  2024.10.1
/*	사용법     : fnPaging(전체 갯수,현재 페이지,화면당 목록수,페이징 갯수,삽입 대상)
/*				 템플릿 안에 <_page> 로 페이징 삽입
/*======================================================================================*/
function paging(obj) {
	if(typeof obj != 'object' || 'total' in obj == false || 'el' in obj == false) {
		return;
	}
	if('page' in obj == false) obj.page = 1;
	if('rows' in obj == false) obj.rows = 10;
	if('show_paging' in obj == false) obj.show_paging = 9;

	let total_page = Math.ceil(obj.total/obj.rows);

	// 이전 페이징
	let prev = obj.page - 1;
	if(prev < 1) prev = 1;

	// 다음 페이징
	let next = obj.page + 1;
	if(next > total_page) next = total_page;

	// 페이지 시작 번호
	let start = obj.page - Math.ceil(obj.show_paging / 2 ) + 1;
	if(start < 1) start = 1;

	// 페이지 끝 번호
	let end = start + obj.show_paging - 1;
	if(end > total_page) {
		end = total_page;
		start = end - obj.show_paging + 1;
		if(start < 1) start = 1;
	}
	if(end < 1) {
		total_page = 1;
		end = 1;
		next = 1;
		prev = 1;
		start = 1;
	}

	let paging = [];
	for(var i = start ; i <= end ; i++) {
		paging.push(`<li ${((i==obj.page)?'class="now"':'')} data-page="${i}">${i}</li>`);
	}
	$(obj.el).html(`
			<ul class="--paging">
				<li class="first" data-page="1"></li>
				<li class="prev" data-page="${prev}"><</li>
				${paging.join("")}
				<li class="next" data-page="${next}">></li>
				<li class="last" data-page="${total_page}"></li>
			</ul>
		`);
	$(obj.el).find("ul.--paging > li").click(function() {
			if('fn' in obj == false) return;
			obj.fn($(this).data("page"));
		});
}
