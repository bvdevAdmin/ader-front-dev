$(document).ready(function() {
	changeLanguageR();
	
	/* 간편 로그인 버튼 설정 */
	setSNSLoginWrap();
	
	/* 로그인 정보 초기화 */
	initMemberId();
	
	/* 로그인 버튼 클릭처리 */
	clickBtn_login();
	
	/* 비밀번호 찾기 링크 클릭처리 */
	clickLink_pw();
	
	/* 회원가입 버튼 클릭처리 */
	clickBtn_join();
	
	/* 로그인 화면 엔터 처리 */
	keyupLogin_enter();
});

/* 간편 로그인 버튼 설정 */
function setSNSLoginWrap() {
	let country = getLanguage();
	if (country == "KR") {
		let wrap_sns_login = document.querySelector('.content__wrap.sns_login_wrap');
		if (wrap_sns_login != null) {
			wrap_sns_login.innerHTML = `
				<div class="content__title sns__account__login">
					<div class="font__large text__align__center" data-i18n="m_login_sns">SNS 계정으로 로그인하기</div>
				</div>
				
				<div class="content__row sns__account__login" style="display:flex;">
					<img class="sns-login-btn kakao__btn" style="width:30px;height:30px;" src="${cdn_img}/btn/btn_kakao.png">
					<img class="sns-login-btn naver__btn" style="width:30px;height:30px;margin-right:10px;" src="${cdn_img}/btn/btn_naver.jpg">
					<img class="sns-login-btn google_btn" style="width:30px;height:30px;" src="${cdn_img}/btn/btn_google.png">
				</div>
			`;
		}
	}
	
	initLoginHandler();
}

/* 로그인 정보 초기화 */
function initMemberId() {
	$('.param_member_id').val('');
	
	let c_member_id = getCookie("usermember_id");
	if (c_member_id != null && c_member_id.length > 0) {
		$('.param_member_id').val(c_member_id);
		
		let member_id_flg = $('input:checkbox[name="member_id_flg"]');
		member_id_flg.prop('checked',true);
	} else {
		$('.param_member_id').val('');
	}
}

/* 로그인 버튼 클릭처리 */
function clickBtn_login() {
	let btn_login = document.querySelectorAll('.btn_login');
	if (btn_login != null && btn_login.length > 0) {
		btn_login.forEach(btn => {
			btn.addEventListener('click',function(e) {
				let el = e.currentTarget;
				let wrap_login = el.dataset.wrap_login;
				
				memberLogin(wrap_login);
			});
		});
	}
}

/* 비밀번호 찾기 링크 클릭처리 */
function clickLink_pw() {
	let link_pw = document.querySelectorAll('.link_pw');
	if (link_pw != null && link_pw.length > 0) {
		link_pw.forEach(link => {
			link.addEventListener('click',function() {
				location.href = '/login/check';
			});
		});
	}
}

/* 회원가입 버튼 클릭처리 */
function clickBtn_join() {
	let btn_join = document.querySelectorAll('.btn_join');
	if (btn_join != null && btn_join.length > 0) {
		btn_join.forEach(btn => {
			btn.addEventListener('click',function() {
				location.href = '/login/join';
			});
		});
	}
}

/* 로그인 화면 엔터 처리 */
function keyupLogin_enter() {
	let param_member_pw = document.querySelectorAll('.param_member_pw');
	if (param_member_pw != null && param_member_pw.length > 0) {
		param_member_pw.forEach(param => {
			param.addEventListener('keyup',function(e) {
				if (e.keyCode == "13") {
					let el = e.currentTarget;
					let wrap_login = el.dataset.wrap_login;
					
					memberLogin(wrap_login);
				}
			});
		});
	}
}

/* 로그아웃 버튼 클릭처리 */
function clickBtn_logout() {
	let btn_logout = document.querySelectorAll('.btn_logout');
	if (btn_logout != null && btn_logout.length > 0) {
		btn_logout.forEach(btn => {
			btn.addEventListener('click',function() {
				deleteCookie("return_url");
				location.href='/logout';
			});
		});
	}
}

/* 회원 로그인 - 독립몰 회원 로그인 */
function memberLogin(wrap_login) {
	let div_login = $(`#login_${wrap_login}`);
	
	/* 로그인 에러 메시지 초기화 */
	let font_red = $('.font__underline.font__red');
	font_red.text('');
	
	let regex_mail = new RegExp('^[a-zA-Z0-9+-\_.]+@[a-zA-Z0-9-]+\.([a-zA-Z0-9-]|([a-zA-Z0-9-]+\.[a-zA-Z0-9-]))+$');
	
	let member_id = div_login.find('.param_member_id').val();
	let member_pw = div_login.find('.param_member_pw').val();
	
	if (member_id == '' || member_id == null) {
		let tmp_msg = getMSG_login(getLanguage(),"MSG_LOGIN_01");
		
		$('.member_id_msg').text(tmp_msg);
		
		msgScrollAction('.font__underline.font__red.member_id_msg', 100, 40);
		return false;
	}
	
	if (regex_mail.test(member_id) == false) {
		let tmp_msg = getMSG_login(getLanguage(),"MSG_LOGIN_02");
		
		$('.member_id_msg').text(tmp_msg);

		msgScrollAction('.font__underline.font__red.member_id_msg', 100, 40);
		return false;
	}
	
	if (member_pw == '' || member_pw == null) {
		let tmp_msg = getMSG_login(getLanguage(),"MSG_LOGIN_03");
		
		$('.member_pw_msg').text(tmp_msg);

		msgScrollAction('.font__underline.font__red.member_pw_msg', 100, 120);
		return false;
	}
	
	let member_id_flg = $('input:checkbox[name="member_id_flg"]');
	if (member_id_flg.prop('checked') == true) {
		setCookie("usermember_id", div_login.find('.param_member_id').val(), 7);
	} else {
		deleteCookie("usermember_id");
	}
	
	$.ajax({
		type: 'POST',
		url: api_location + "account/login",
		headers: {
			"country":getLanguage()
		},
		data: {
			'member_id'	:member_id,
			'member_pw'	:member_pw,
			'r_url'		:$('.r_url').val()
		},
		dataType: "json",
		async: false,
		error: function(e) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0067", null);
		},
		success: function(d) {
			if(d.code == "200") {
				sessionStorage.login_session = "true";
				
				location.href = "/main";
			} else {
				$('.result_msg').text(d.msg);
				
				msgScrollAction('.result_msg', 72, 0);
			}
		}
	});
}

function logout() {
	deleteCookie("return_url");
	location.href='/logout';
}

//login-update
$(document).ready(function() {
	$('input[name="password"]').keyup(function(){
		if(memberPwConfirm($(this).val()) || $(this).val().length == 0){
			$('.font__underline.warn__msg.member_pw').css('visibility','hidden');
		} else {
			$('.font__underline.warn__msg.member_pw').css('visibility','visible');
		};
	});
	urlParsing();
});

function urlParsing() {
	var url = location.href;
	var idx = url.indexOf("?");
	
	if(idx >= 0) {
		var data = url.substring( idx + 1, url.length);
		var data_arr = data.split("=");
		if(data_arr[0] == 'member_idx') {
			$('input [name="idx"]').val(data_arr[1]);
		}
	}
}

function memberPwConfirm(str) {	
	//  대소문자/숫자/특수문자 중 3가지 이상 조합, 8자-16자
	//  입력 가능 특수문자 : '!@#$%^()_-={}[]|;:<>,.?/					
	var password_reg = /^(?=.*[\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(])(?=.*\d)(?=.*[A-Za-z])[\da-zA-Z\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(]{8,16}/;
	//  공백 입력 불가능
	var space_reg = /\s/g;

	if(space_reg.test(str) == false) {
		return password_reg.test(str);
	} else {
		return false;
	}
}

//login-password-pub 
$(document).ready(function() {
	$('#pw_desciption').hide();
	$('input[name="member_pw"]').keyup(function() {
		if(memberPwConfirm($(this).val()) || $(this).val().length == 0) {
			$('.font__underline.warn__msg.member_pw').css('visibility','hidden');
			hidePwDescription();
		} else {
			$('.font__underline.warn__msg.member_pw').css('visibility','visible');
			showPwDescription();
		};
	});
});

//join
$(document).ready(function() {
	$('#pw_desciption').hide();
	$('input[name="member_pw"]').keyup(function() {
		if(memberPwConfirm($(this).val()) || $(this).val().length == 0) {
			$('.font__underline.warn__msg.member_pw').css('visibility','hidden');
			hidePwDescription();
		} else {
			$('.font__underline.warn__msg.member_pw').css('visibility','visible');
			showPwDescription();
		};
	});
});

function showPwDescription() {
	$('#pw_desciption').show();
	$('#hide_area').hide();
}

function hidePwDescription() {
	$('#pw_desciption').hide();
	$('#hide_area').show();
}

function msgScrollAction(selector, targetTop, scrollTop){
	let target = document.querySelector(selector);
	let targetRectTop = target.getBoundingClientRect().top;

	if(targetRectTop < targetTop){
		window.scroll({top:scrollTop, behavior:'smooth'});
	}
}

/* 간편 로그인 */
function memberLogin_sns(sns_type,account_key,member_id,member_name,gender,tel_mobile,member_birth) {
	$.ajax({
		type: 'POST',
		url: api_location + "account/sns/login",
		headers: {
			"country":getLanguage()
		},
		data: {
			'sns_type'		:sns_type,
			'account_key'	:account_key,
			'member_id'		:member_id,
			'member_name'	:member_name,
			'gender'		:gender,
			'tel_mobile'	:tel_mobile,
			'member_birth'	:member_birth
		},
		dataType: "json",
		error: function() {
			returnLoginPage();
		},
		success: function(d) {
			if(d.code == "200") {
				sessionStorage.login_session = "true";
				
				location.href = '/main';
			} else {
				returnLoginPage(d.msg);
			}
		}
	});
	
	function returnLoginPage(){
		makeMsgNoti(getLanguage(),'MSG_F_ERR_0074',null);
	}
}

//쿠키값 delete
function deleteCookie(cookieName) {
	setCookie(cookieName, '', 0);
}

//쿠키값 get
function getCookie(cookieName) {
	cookieName = cookieName + "=";
	let cookieData = document.cookie;
	let start = cookieData.indexOf(cookieName);
	let cookieValue = '';
	if(start != -1){
		start += cookieName.length;
		let end = cookieData.indexOf(';', start);
		if(end == -1)end = cookieData.length;
		cookieValue = cookieData.substring(start, end);
	}
	return unescape(cookieValue); //unescape로 디코딩 후 값 리턴
}

function setCookie(cookieName, value, exdays) {
	let exdate = new Date();
	exdate.setDate(exdate.getDate() + exdays);
	let cookieValue = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toGMTString());
	document.cookie = cookieName + "=" + cookieValue;
}

function getMSG_login(country,msg_code) {
	let msg_login = ""
	
	if (country != null && msg_code != null) {
		switch (country) {
			case "KR" :
				switch (msg_code) {
					case "MSG_LOGIN_01" :
						tmp_msg = "이메일 입력해주세요.";
						break;
					
					case "MSG_LOGIN_02" :
						tmp_msg = "올바른 이메일을 형식을 입력해주세요.";
						break;
					
					case "MSG_LOGIN_03" :
						tmp_msg = "비밀번호를 입력해주세요.";
						break;
				}
				
				break;
			
			case "EN" :
				switch (msg_code) {
					case "MSG_LOGIN_01" :
						tmp_msg = "Please enter your email.";
						break;
					
					case "MSG_LOGIN_02" :
						tmp_msg = "Please enter your email correctly.";
						break;
					
					case "MSG_LOGIN_03" :
						tmp_msg = "Please enter you password.";
						break;
				}
				
				break;
			
			case "CN" :
				switch (msg_code) {
					case "MSG_LOGIN_01" :
						tmp_msg = "请输入用户email。";
						break;
					
					case "MSG_LOGIN_02" :
						tmp_msg = "请输入正确的email格式。";
						break;
					
					case "MSG_LOGIN_03" :
						tmp_msg = "请输入密码。";
						break;
				}
				
				
				break;
		}
	}
	
	msg_login = tmp_msg;
	
	return msg_login;
}

/*
function loginSidebar() {
	var country = getLanguage();
	$(".side__wrap #frm-login").find('input[name=country]').val(country);

	var regex_mail = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}');
	var member_id = $('.side__wrap .param_member_id').val();
	var member_pw = $('.side__wrap .param_member_pw').val();
	regex_mail.test(member_id);

	$('.side__wrap .font__underline.font__red').text('');
	if (member_id == '') {
		let tmp_msg = "";
		switch (country) {
			case "KR" :
				tmp_msg = "이메일 입력해주세요.";
				
				break;
			
			case "EN" :
				tmp_msg = "Please enter your email.";
				
				break;
			
			case "CN" :
				tmp_msg = "请输入用户email。";
				
				break;
		}
		
		$('.side__wrap .member_id_msg').text(tmp_msg);
		return false;
	}

	if (regex_mail.test(member_id) == false) {
		let tmp_msg = "";
		switch (country) {
			case "KR" :
				tmp_msg = "올바른 이메일을 형식을 입력해주세요.";
				
				break;
			
			case "EN" :
				tmp_msg = "Please enter your email correctly.";
				
				break;
			
			case "CN" :
				tmp_msg = "请输入正确的email格式。";
				break;
		}
		
		$('.side__wrap .member_id_msg').text(tmp_msg);
		return false;
	}

	if (member_pw == '') {
		let tmp_msg = "";
		switch (country) {
			case "KR" :
				tmp_msg = "비밀번호를 입력해주세요.";
				break;
			
			case "EN" :
				tmp_msg = "Please enter you password.";
				break;
			
			case "CN" :
				tmp_msg = "请输入密码。";
				break;
		}
		
		$('.side__wrap .member_pw_msg').text(tmp_msg);
		return false;
	}

	let currentLink = document.location.href; 
	let currentURL = currentLink.replace(domain_url,'');
	
	let loginParam = $(".side__wrap #frm-login").serialize();
	loginParam += `&r_url=${currentURL}`;

	$.ajax({
		type: 'POST',
		url: api_location + "account/login",
		data: loginParam,
		dataType: "json",
		async: false,
		error: function(e) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0067", null);
			// alert("로그인 처리중 오류가 발생했습니다.");
		},
		success: function(d) {
			if (d.code == "200") {
				let recent_url = document.location.href;
				if (recent_url != null && recent_url != undefined) {
					location.href = recent_url;
				} else {
					if(d.data != null && d.data != '/login/join'){
						location.href = d.data;
					}
					else{
						location.href = `/main`;
					}
				}
			} else {
				$('.side__wrap .result_msg').text(d.msg);
			}
		}
	});
}
*/