
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
  var temp_str = this.trim();
  temp_str = temp_str.replace(eval("/" + str1 + "/gi"), str2);
  return temp_str;
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
function paging(obj) {
	if(typeof obj != 'object' || 'total' in obj == false || 'el' in obj == false) {
		return;
	}
	if('page' in obj == false) obj.page = 1;
	if('row' in obj == false) obj.row = 20;
	if('show_paging' in obj == false) obj.show_paging = 9;
	
	let total_page = Math.ceil(obj.total/obj.row);

	// 이전 페이징
	let prev = obj.page - obj.show_paging;
	if(prev < 1) prev = 1;

	// 다음 페이징
	let next = obj.page + obj.show_paging;
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
				<li class="prev" data-page="${prev}"></li>
				${paging.join("")}
				<li class="next" data-page="${next}"></li>
				<li class="last" data-page="${total_page}"></li>
			</ul>
		`);
	$(obj.el).find("ul.--paging > li").click(function() {
			if('fn' in obj == false) return;
			obj.fn($(this).data("page"));
		});
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


function tel_format(tel) {
	if(typeof tel != "string") return tel;

	if((tel).length < 10) return tel;
	var result = tel.substr(0,3) + '-';
	tel = (tel).toString();
	switch(tel.length) {
		case 10:
			result += tel.substr(3,3);
			break;
		case 11:
			result += tel.substr(3,4);
			break;
	}
	return ((tel != '')?result + '-' + tel.substr(-4):'');
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

function addzero(n,space) {
	return appendzero(n,space);
}


/*======================================================================================*/
/*  Modal Popup
/*  --------------
/*  작성일		: 2014.05.05
/*  최종수정일	: 2020.02.19
/*	사용법		: modal(페이지, 전달 값)
/*
/*======================================================================================*/
function modal(url,data) {
	if(typeof url == 'boolean' && url == false) {
		modal_close();
	}
	else {
		$.ajax({
			url: config.modal + url,
			data: data,
			dataType: "text",
			error: function(msg) {
				// alert("오류가 발생했습니다");
				makeMsgNoti(getLanguage(), "MSG_F_ERR_0098", null);
			},
			success: function(d) {
				if(d != "") {
					let id = `_modal_alert_${new Date().getTime()}_${$("body > .modal.alert").length + 1}`;
					$("body").addClass("on-modal").append(`<section class="modal" id="${id}">${d}</section>`);
					if(typeof set_ui == "function") set_ui($(`#${id}`));
					$(`#${id}`).find("button.close,button.cancel").click(function() {
						location.href = "#close";
					});
					setTimeout(function() {
						$(`#${id}`).addClass("on");
					},10);
				}
			}
		});
	}
}

function modal_close(close_yn) {
	var obj = $("section.modal").last();

	$(obj).addClass("off");

	setTimeout(function() {
		$(obj).removeClass("on")
		if(typeof(close_yn)=="undefined" || close_yn == false) {
			$(obj).remove();
		}
		if($("section.modal").length == 0) {
			$("body").removeClass("on-modal");
		}
	},10);
}



/*function confirm(str,ok,cancel) {
	$("body").addClass("on-modal")
		.append(`
			<section class="modal confirm">
				<section class="body">
					<figure>
						<p>${str}</p>
					</figure>
					<footer>
						<button type="button" class="cancel">취소</button>
						<button type="button" class="ok">확인</button>
					</footer>
				</section>
			</section>
		`);
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
}*/


/*function alert(str,ok) {
	$("body:not(.addClass)").addClass("on-modal");
	$("body").append(`
		<section class="modal alert">
			<section class="body">
				<figure>
					<p>${str}</p>
				</figure>
				<footer>
					<button type="button" class="ok">확인</button>
				</footer>
			</section>
		</section>
	`);
	$("body > section.modal button.ok").focus();

	$("body > section.modal").last().find("button.ok").click(function() {
		if(typeof(ok) == "function") {
			$("body > section.modal.alert").last().remove();
			setTimeout(ok,1);
			setTimeout(function() {
				location.href = "#close";
			},5);
		}
		if(typeof(ok) == "boolean" && ok == true) {
			$("body > section.modal").last().remove();
			location.href = "#close";
		}
		else {
			$("body > section.modal").last().remove();
		}
		if($("body.on-modal > section.modal").length == 0) {
			$("body").removeClass("on-modal");
		}
	});
}*/

function zipcode(obj) {
	$("body").addClass("on-modal")
		.append(`
			<section class="modal">
				<section class="body">
					<header>
						<h1><i class="xi-maker"></i>주소 검색</h1>
						<button type="button" class="close"></button>
					</header>
					<article class="zipcode">
						<div id="modal-zipcode"></div>
					</article>
					<footer>
						<button type="button" class="ok">확인</button>
					</footer>
				</section>
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
			var fullAddr = data.address; // 최종 주소 변수
			var extraAddr = ''; // 조합형 주소 변수

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
			var obj_p = $(obj).parent();
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
		type: "post",  
		url: config.api + "popup",
		success: function(d) {
			if(d.code == 200) { // 팝업이 있을 경우
				if('data' in d == false) return;

				var html,name;
				var offsetX,offsetY;
				offsetX = 10;
				offsetY = 10;
				if($(window).width() > 720) offsetX += 75;
				for(var i = 0 ; i < d.data.length ; i++) {
					html = "";
					name = "popup_" + d.data[i].no;
					if(getCookie(name) != 'Y' ) {
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
						offsetX += parseInt(d.data[i].width)+5;
					}
				}
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

/*======================================================================================*/
/*  이미지 경로 전역변수 지정
/*  --------------
/*  작성일		: 2023.01.27
/*  최종수정일	:
/*	사용법		:
/*
/*======================================================================================*/
let img_root = "http://116.124.128.246:81/";