/*======================================================================================*/
/*  기본 함수 정의
/*  --------------
/*  작성일     : 2014.6.19
/*  최종수정일 :
/*	사용법     :
/*
/*======================================================================================*/
//공백 제거
String.prototype.trim = function() {
	return this.replace(/(^\s*)|(\s*$)/gi, "");
}

// 문자열 치환
String.prototype.replaceAll = function(str1, str2) {
	let temp_str = this.trim();
	temp_str = temp_str.replace(eval("/" + str1 + "/gi"), str2);
	return temp_str;
}

// HTML 태그 제거
String.prototype.strip_tags = function() {
	return this.replace(/(<([^>]+)>)/gi, "");
}

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

/*======================================================================================*/
/*  jQuery 함수 정의
/*  --------------
/*  작성일		: 2014.12.17
/*  최종수정일	:
/*	사용법		:
/*
/*======================================================================================*/
$.fn.serializeObject=function(){
	"use strict";
	var a={},
		b=function(b,c){
			var d=a[c.name];
			"undefined"!=typeof d&&d!==null?$.isArray(d)?d.push(c.value):a[c.name]=[d,c.value]:a[c.name]=c.value
		};
	return $.each(this.serializeArray(),b),a
};

// 폼 검사 함수
$.fn.formvaild = function() {
	var result = true;
	$(this).find("input").each(function() {
		if($(this).attr("required")=="required") {	// 필수값 입력 항목일 경우
			var name = $(this).attr("title");
			var max = $(this).attr("maxlength");
			var min = $(this).attr("minlength");
			var confirm = $(this).attr("confirm");
			var val_len	= $(this).val().length;

			if($(this).val().trim() == "") {		// 필수값 입력 검사
				$(this).next(".-describe").html(name + "은(는) 반드시 입력해야 합니다");
				if(result == true) $(this).focus();	// 첫 오류일 경우 포커스 이동
				$(this).addClass("input-required");	// 배경색 변환
				result = false;
			}
			else if(val_len < min || val_len > max) {
				$(this).next(".-describe").html("최소 " + min + "자 이상 " + max + "자 이내로 입력해야 합니다");
				if(result == true) $(this).focus();	// 첫 오류일 경우 포커스 이동
				$(this).addClass("input-required");	// 배경색 변환
			}
			else if(confirm != undefined && $(this).val() != $("input[name='" + confirm + "']").val()) {
				if(result == true) $(this).focus();	// 첫 오류일 경우 포커스 이동
				$(this).addClass("input-required");	// 배경색 변환
				$(this).next(".-describe").html(name + "이(가) 일치해야 합니다");
				result = false;
			}
			else {
				$(this).next(".-describe").empty();
				$(this).removeClass("input-required");	// 배경색 변환
			}
		}
	});
	return result;
}



function number_format(n) {
	var is_minus = '';
	if(n < 0) is_minus = '-';
	n = new String(n);
	n = n.replace("-","");
	var result = "";
	if (n != "free") {
		for(var i = 1 ; i <= n.length ; i++) {
			if((n.length - i+1)%3  == 0 && i > 1) result += ",";
			result += n.substr(i-1,1);
		}
	} else {
		result = n;
	}
	return is_minus + result;
}


/*======================================================================================*/
/*  페이징
/*  --------------
/*  작성일     : 2014.6.19
/*  최종수정일 : 2014.6.19
/*	사용법     : fnPaging(전체 갯수,현재 페이지,화면당 목록수,페이징 갯수,삽입 대상)
/*				 템플릿 안에 <_page> 로 페이징 삽입
/*======================================================================================*/
var paging_next	= "";	// 다음 페이지 템플릿
var paging_prev	= "";	// 이전 페이지 템플릿
var paging_head	= "";	// 처음으로 템플릿
var paging_foot	= "";	// 끝으로 템플릿
var paging_num	= "";	// 페이지 
var paging_now	= "";	// 현재 페이지

function paging(total,n_page,per_list,per_page,target,option) {
	var html,start,end,total,prev,next,div_page,temp;

	if(typeof option == 'undefined') {
		option = {};
	}
	if('next' in option == false) option.next = paging_next;
	if('prev' in option == false) option.prev = paging_prev;
	if('head' in option == false) option.head = paging_head;
	if('foot' in option == false) option.foot = paging_foot;
	if('num' in option == false) option.num = paging_num;
	if('now' in option == false) option.now = paging_now;	

	if(typeof option.form == 'object') {
		option.next = `<button type="button" data-page="<_page>" class="next"><i class="xi-angle-right-min"></i></button>`;
		option.prev = `<button type="button" data-page="<_page>" class="prev"><i class="xi-angle-left-min"></i></button>`;
		option.head = `<button type="button" data-page="1" class="first"><i class="xi-angle-double-left"></i></button>`;
		option.foot = `<button type="button" data-page="<_page>" class="last"><i class="xi-angle-double-right"></i></button>`;
		option.num = `<button type="button" data-page="<_page>"><_page></button>`;
		option.now = `<button type="button" data-page="<_page>" class="now"><_page></button>`;
	}

	total_page = parseInt((total+per_list-1) / per_list);
	div_page = parseInt(per_page / 2 + 0.49);

	// 이전 페이징
	prev = n_page - per_page;
	if(prev < 1) prev = 1;

	// 다음 페이징
	next = n_page + per_page;
	if(next > total_page) next = total_page;

	// 페이지 시작 번호
	start = n_page - div_page;
	if(start < 1) start = 1;

	// 페이지 끝 번호
	end = start + (per_page - 1);
	if(end > total_page) end = total_page;
	if(end < 1) {
		total_page = 1;
		end = 1;
		next = 1;
		prev = 1;
		start = 1;
	}

	html = option.head + "\n";
//	html += option.prev.replace("<_page>",prev) + "\n";
	for(var i = start ; i <= end ; i++) {
		if(i == n_page) {
			temp = option.now;
		} else {
			temp = option.num;
		}
		html += temp.replaceAll("<_page>",i) + "\n";
	}
//	html += option.next.replaceAll("<_page>",next) + "\n";
	html += option.foot.replaceAll("<_page>",total_page) + "\n";
	$(target).html(html);
	if(typeof option.form == 'object') {
		$(target).find("button").click(function() {
			if($(option.form).find("input[name='page']").length == 0) {
				$(option.form).append(`<input type="hidden" name="page">`);
			}
			$(option.form).find("input[name='page']").val($(this).data("page"));
			$(option.form).submit();
		});
	}
}




/*======================================================================================*/
/*  공백 제거
/*  ---------
/*  작성일     : 2014.6.19
/*  최종수정일 :
/*	사용법     :
/*
/*======================================================================================*/
/**************************************
	Trim 함수 (왼쪽)
***************************************/
function trimLeft(str) {
	var v_ret = "";

	if(str != null){
		var v_stop = 0;
		var v_length = str.length;
		for(var i = 0; i < v_length; i++){
			var v_char = str.charAt(i);
			if(v_char != " "){
				v_stop = i;
				break;
			}
		}
		v_ret = str.substring(i);
	}
	return v_ret;
}

/**************************************
	Trim 함수 (오른쪽)
***************************************/
function trimRight(str) {
	var v_ret = "";

	if(str != null){
		var v_stop = 0;
		var v_length = str.length;
		for(var i = (v_length -1); i > 0 ; i--){
			var v_char = str.charAt(i);
			if(v_char != " "){
				v_stop = i;
				break;
			}
		}
		v_ret = str.substring(0, i +1);
	}
	return v_ret;
}


/**************************************
	Trim 함수
***************************************/
function trim(str) {
	var v_ret = null;

	v_ret = trimLeft(str);
	v_ret = trimRight(v_ret);

	return v_ret;
}




/* 토스트 메시지 */
function toast(msg,duration) {
	var timestamp = new Date().getTime();

	if(typeof(duration) == "undefined") duration = 5;
	if($("body > .toast:not(.off)").length > 0) {
		$("body > .toast")
			.data("timestamp",timestamp)
			.find("span").html(msg);
	}
	else {
		$("body").append('<div class="toast" data-timestamp="' + timestamp + '"><span>' + msg + '</span></div>');
		setTimeout(function() {
			$("body > .toast").addClass("on");
		},20);
	}

	setTimeout(function() {
		if(timestamp >= parseInt($("body > .toast.on").data("timestamp"))) {
			$("body > .toast")
				.removeClass("on")
				.addClass("off");
			setTimeout(function() {
				$("body > .toast.off").remove();
			},300);
		}
	},(duration*1000)+300);
}



function nl2br(str) {
	 //return str.replace(/\n/g, "<br />" + String.fromCharCode(10));
	 return str.replace(/\n/g, "<br />");
}

function br2nl(str) { // 210817 khw
	return str.replace(/(<br>|<br\/>|<br \/>)/g, '\n');
}

function appendzero(n,space) {
	var result = n,num = 1;
	if(typeof space == 'undefined') space = 2;
	for(var i = 1 ; i<space; i++) {
		num *= 10;
	}

	for(var i = num ; i > 1 ; i/=10) {
		if(n < i) result = "0" + result;
	}

	return result;
}


/*======================================================================================*/
/*  Modal Popup
/*  --------------
/*  작성일		: 2014.05.05
/*  최종수정일	: 2020.02.19
/*	사용법		: modal(페이지, 전달 값)
/*
/*======================================================================================*/
function modal(url,opt) {
	$.ajax({
		type: "post",
		url: `/_modal/${url}`,
		data: opt,
		dataType: "text",
		error: function(msg) {
			console.log(msg);
			alert("오류가 발생했습니다");
		},
		success: function(d) {
			if(d != "") {
				let id = `_modal_alert_${new Date().getTime()}_${$("body > .modal.alert").length + 1}`;
				$("body").addClass("on-modal").append(`<section class="modal" id="${id}">${d}</section>`);

				if(typeof setUI == "function") setUI($(`section.modal#${id}`));

				$(`section.modal#${id} button.cancel`).click(function() {
					location.href = "#close";
				});
				$(`section.modal#${id} header > button.close`).click(function() {
					location.href = "#close";
				});

				setTimeout(function() {
					$(`section.modal#${id}`).addClass("on");
				},10);
			}
		}
	});
}


function modal_close(close_yn) {
	var obj = $(".modal").last();

	$(obj).addClass("off");

	setTimeout(function() {
		$(obj).removeClass("on")
		if(typeof(close_yn)=="undefined" || close_yn == false) {
			$(obj).remove();
		}
		if($(".modal").length == 0) {
			$("body").removeClass("on-modal");
		}
	},10);
}



/*
function confirm(str,ok,cancel) {
	var html;

	html = '<section class="modal">'
	     + '	<article class="confirm">'
	     + '		<figure>'
	     + '			<p>' + str + '</p>'
	     + '		</figure>'
	     + '		<footer>'
	     + '			<button type="button" class="ok"><i class="xi-check"></i> 적용</button>'
	     + '			<button type="button" class="cancel"><i class="xi-close"></i> 취소</button>'
	     + '		</footer>'
		 + '	</article>'
	     + '</section>';

	$("body").addClass("on-modal").append(html);
	$("body > section.modal button.ok").focus();

	$("body > section.modal").last().find("button.cancel").click(function() {
		if(typeof(cancel) == "function") setTimeout(cancel,1);
		$("body > section.modal").last().remove();
		if($("body > .modal").length == 0) {
			$("body").removeClass("on-modal");
		}
	});
	$("body > section.modal").last().find("button.ok").click(function() {
		if(typeof(ok) == "function") setTimeout(ok,1);
		setTimeout(function() {
			$("body > section.modal").last().remove();
			if($("body > .modal").length == 0) {
				$("body").removeClass("on-modal");
			}
		},2);
	});
}

function alert(str,ok) {
	if(typeof str == 'undefined' || str == '') return;

	$("body").addClass("on-modal").append(`
		<section class="modal">
			<article class="alert">
				<figure>
					<p>${str}</p>
				</figure>
				<footer ${(ok == 'no-footer')?'class="hidden"':''}>
					<button type="button" class="ok"><i class="xi-check"></i> 확인</button>
				</footer>
			</article>
		</section>
	`);
	$("body > section.modal button.ok").focus();


	$("body > section.modal").last().find("button.ok").click(function() {
		switch(typeof ok) {
			case "function":
				setTimeout(function() {
					location.href = "#close";
				},1);
				setTimeout(ok,5);
			break;

			case "boolean":
				if(ok == true) {
					$("body > section.modal").last().remove();
					location.href = "#close";
				}
			break;

			default:
				$("body > section.modal").last().remove();
			break;
		}

		setTimeout(function() {
			if($("body > .modal").length == 0) {
				$("body").removeClass("on-modal");
			}
		},2);
	});
}
*/


function zipcode(obj) {
	$("body").addClass("on-modal").append(`
		<section class="modal">
			<article class="zipcode">
				<header>
					<h1><i class="xi-maker"></i>주소 검색</h1>
					<button type="button" class="close"></button>
				</header>
				<div id="modal-zipcode"></div>
				<footer>
					<button type="button" class="cancel"><i class="xi-close"></i> 닫기</button>
				</footer>
			</article>
		</section>
	`);

	$("body > section.modal").last().find("button.cancel,header > button.close").click(function() {
		$("body > section.modal").last().remove();
		if($("body > .modal").length == 0) {
			$("body").removeClass("on-modal");
		}
	});

	new daum.Postcode({
		oncomplete: function(data) {
			// 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

			// 각 주소의 노출 규칙에 따라 주소를 조합한다.
			// 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
			let fullAddr = data.address // 최종 주소 변수
				,extraAddr = ''; // 조합형 주소 변수

			// 기본 주소가 도로명 타입일때 조합한다.
			if(data.addressType === 'R'){
				//법정동명이 있을 경우 추가한다.
				if(data.bname !== ''){
					extraAddr += data.bname;
				}
				// 건물명이 있을 경우 추가한다.
				if(data.buildingName !== ''){
					extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
				}
				// 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
				fullAddr += (extraAddr !== '' ? ' ('+ extraAddr +')' : '');
			}

			// 우편번호와 주소 정보를 해당 필드에 넣는다.
			let obj_p = $(obj).parent();
			$(obj_p).find("input.zipcode").val(data.zonecode);
			$(obj_p).parent().find("input[type=text].address1").val(fullAddr);

			modal_close();
		},
		width : '100%',
		height : '100%',
		maxSuggestItems : 5
	}).embed(document.getElementById("modal-zipcode"));
}


/*======================================================================================*/
/*  Popup
/*  -----
/*  작성일		: 2014.12.8
/*  최종수정일	: 
/*	사용법		: popup();
/*
/*======================================================================================*/
function popup() {
	$.ajax({
		url: config.api + "popup/get",
		success: function(d) {
			if(d.code == 200) { // 팝업이 있을 경우
				if('data' in d == false) return;

				let name,href,offsetX = 10,offsetY = 10;
				//if($(window).width() > 720) offsetX += 75;
				for(let i = 0 ; i < d.data.length ; i++) {
					name = "popup_" + d.data[i].no;
					if(getCookie(name) != 'Y' ) {
						/*
						// type 에 따라 처리
						if(d.data[i].type == "LAYER") {
							// html 코드 작성
							html  = '<div class="popup" ';
							html += '	style="top: ' + offsetY + 'px ; left : '+ offsetX + 'px; ';
							html += '		width:' + d.data[i].width + 'px;height:' + (parseInt(d.data[i].height) + 30) + 'px">';
							if(d.data[i].url != null) {
								html += '<a href="' + d.data[i].url + '" target="' + d.data[i].target + '">';
								html += '<img src="' + d.data[i].image + '"></a>';
							}
							else {
								html += '<img src="' + d.data[i].image + '"></a>';
							}
							html += '	<div class="foot">';
							html += '		<label><input type="checkbox"> 오늘 하루 이 창을 열지 않음</label>';
							html += '		<a href="javascript:;" onclick="popup_close(this);" data-name="' + name + '"></a>';
							html += '	</div>';
							html += '</div>';
							$('body').prepend(html);	// body 요소 앞에 추가

							// DROP 객체
							$('body').droppable({accept:'div.popup'});

							// DRAG 객체
							$('.popup').draggable({
								containment : "body",
								revert : "invalid"
							});
						}
						else if(d.data[i].type == "WINDOW") {
							var toppos = screen.height / 2 - d.data[i].height/2;
							var leftpos = screen.width / 2 - d.data[i].width/2;
							var popupWidth = d.data[i].width + 20;
							var popupHeight = d.data[i].height + 20;
							window.open("api/popup.php?no=" + d.data[i].no,"","toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,left=" + leftpos + ",top=" + toppos +", width="+popupWidth +"px, height="+popupHeight+"px");
						}
						*/
						href = '';
						if(d.data[i].url != '') href = `href="${d.data[i].url}" target="${d.data[i].target}" `;
						$('body').append(`
							<section class="popup" style="top:${offsetY}px; left:${offsetX}px">
								<a ${href}><img src="${d.data[i].image}"></a>
								<footer>
									<label><input type="checkbox"> 오늘 하루 이 창을 열지 않음</label>
									<button type="button" class="close" data-name="${name}"><i class="xi-close"></i></button>
								</footer>
							</section>
						`);	// body 요소에 추가

						offsetX += parseInt(d.data[i].width)+5;
					}
				}
				// DROP 객체
				$('body').droppable({accept:'section.popup'});

				// DRAG 객체
				$('section.popup').draggable({
					containment : "body",
					revert : "invalid"
				});

				/** 팝업 닫기 **/
				$("body > section.popup > footer > label").click(function() {
					setCookie($(this).parent().find("button.close").data("name"),"Y",1);
					$(this).parent().parent().remove();
				});
				$("body > section.popup button.close").click(function() {
					if($(this).parent().find("input:checked").length > 0) {
						setCookie($(this).data("name"),"Y",1);
					}
					if($(this).parent().parent().parent().parent().hasClass("popup-table")) {
						window.close();
					}
					else {
						$(this).parent().parent().remove();
					}
				});
			}
		},
		dataType:'json'
	});
}

function popup_close(obj) {
	if($(obj).parent().find("input:checked").length > 0) {
		setCookie($(obj).data("name"),"Y",1);
	}
	if($(obj).parent().parent().parent().parent().hasClass("popup-table")) {
		window.close();
	}
	else {
		$(obj).parent().parent().remove();
	}
}


/*======================================================================================*/
/*  쿠키 생성
/*  ------
/*  작성일		: 2014.12.08
/*  최종수정일	:
/*	사용법		: setCookie( 쿠키 이름, 쿠키 값, 만료 기간(일) )
/*======================================================================================*/
function setCookie( name, value, expiredays ) {
	var endDate = new Date();
	endDate.setDate(endDate.getDate()+expiredays);
	document.cookie = name + "=" + escape( value ) + "; path=/; expires=" + endDate.toGMTString() + ";" ;
}

/*======================================================================================*/
/*  쿠키 삭제
/*  ------
/*  작성일		: 2014.12.08
/*  최종수정일	:
/*	사용법		: delCookie( 쿠키 이름 )
/*
/*======================================================================================*/
function delCookie(cKey) {
    // 동일한 키(name)값으로
    // 1. 만료날짜 과거로 쿠키저장
    // 2. 만료날짜 설정 않는다. 
    //    브라우저가 닫힐 때 제명이 된다    

    var date = new Date(); // 오늘 날짜 
    var validity = -1;
    date.setDate(date.getDate() + validity);
    document.cookie =
          cKey + "=;expires=" + date.toGMTString();
}

/*======================================================================================*/
/*  쿠키 가져오기
/*  --------
/*  작성일		: 2014.12.08
/*  최종수정일	:
/*	사용법		: getCookie( 쿠키 이름 )
/*
/*======================================================================================*/
function getCookie(name) {
	var nameOfCookie = name + "=";
	var x = 0;
	while ( x <= document.cookie.length ) {
		var y = (x+nameOfCookie.length);
		if ( document.cookie.substring( x, y ) == nameOfCookie ) {
			if ( (endOfCookie=document.cookie.indexOf( ";", y )) == -1 ) endOfCookie = document.cookie.length;
			return unescape( document.cookie.substring( y, endOfCookie ) );
		}
		x = document.cookie.indexOf( " ", x ) + 1;
		if ( x == 0 ) break;
	}
	return "";
} 


function version_compare (v1, v2, operator) { // eslint-disable-line camelcase
  //       discuss at: https://locutus.io/php/version_compare/
  //      original by: Philippe Jausions (https://pear.php.net/user/jausions)
  //      original by: Aidan Lister (https://aidanlister.com/)
  // reimplemented by: Kankrelune (https://www.webfaktory.info/)
  //      improved by: Brett Zamir (https://brett-zamir.me)
  //      improved by: Scott Baker
  //      improved by: Theriault (https://github.com/Theriault)
  //        example 1: version_compare('8.2.5rc', '8.2.5a')
  //        returns 1: 1
  //        example 2: version_compare('8.2.50', '8.2.52', '<')
  //        returns 2: true
  //        example 3: version_compare('5.3.0-dev', '5.3.0')
  //        returns 3: -1
  //        example 4: version_compare('4.1.0.52','4.01.0.51')
  //        returns 4: 1
  // Important: compare must be initialized at 0.
  let i
  let x
  let compare = 0
  // vm maps textual PHP versions to negatives so they're less than 0.
  // PHP currently defines these as CASE-SENSITIVE. It is important to
  // leave these as negatives so that they can come before numerical versions
  // and as if no letters were there to begin with.
  // (1alpha is < 1 and < 1.1 but > 1dev1)
  // If a non-numerical value can't be mapped to this table, it receives
  // -7 as its value.
  const vm = {
    dev: -6,
    alpha: -5,
    a: -5,
    beta: -4,
    b: -4,
    RC: -3,
    rc: -3,
    '#': -2,
    p: 1,
    pl: 1
  }
  // This function will be called to prepare each version argument.
  // It replaces every _, -, and + with a dot.
  // It surrounds any nonsequence of numbers/dots with dots.
  // It replaces sequences of dots with a single dot.
  //    version_compare('4..0', '4.0') === 0
  // Important: A string of 0 length needs to be converted into a value
  // even less than an unexisting value in vm (-7), hence [-8].
  // It's also important to not strip spaces because of this.
  //   version_compare('', ' ') === 1
  const _prepVersion = function (v) {
    v = ('' + v).replace(/[_\-+]/g, '.')
    v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.')
    return (!v.length ? [-8] : v.split('.'))
  }
  // This converts a version component to a number.
  // Empty component becomes 0.
  // Non-numerical component becomes a negative number.
  // Numerical component becomes itself as an integer.
  const _numVersion = function (v) {
    return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10))
  }
  v1 = _prepVersion(v1)
  v2 = _prepVersion(v2)
  x = Math.max(v1.length, v2.length)
  for (i = 0; i < x; i++) {
    if (v1[i] === v2[i]) {
      continue
    }
    v1[i] = _numVersion(v1[i])
    v2[i] = _numVersion(v2[i])
    if (v1[i] < v2[i]) {
      compare = -1
      break
    } else if (v1[i] > v2[i]) {
      compare = 1
      break
    }
  }
  if (!operator) {
    return compare
  }
  // Important: operator is CASE-SENSITIVE.
  // "No operator" seems to be treated as "<."
  // Any other values seem to make the function return null.
  switch (operator) {
    case '>':
    case 'gt':
      return (compare > 0)
    case '>=':
    case 'ge':
      return (compare >= 0)
    case '<=':
    case 'le':
      return (compare <= 0)
    case '===':
    case '=':
    case 'eq':
      return (compare === 0)
    case '<>':
    case '!==':
    case 'ne':
      return (compare !== 0)
    case '':
    case '<':
    case 'lt':
      return (compare < 0)
    default:
      return null
  }
}