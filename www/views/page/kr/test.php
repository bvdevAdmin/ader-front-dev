<style>
.btn {width:400px;height:40px;text-align:center;margin-top:150px;margin-bottom:150px;cursor:pointer;}

/* 팝업 모달 */
#popup-container {
  visibility: hidden;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  z-index: 99;
  position: fixed;
}
#popup-container.open {
  visibility: visible;
}
#popup-container.open .popup__background {
  display: flex;
  background:rgba(0,0,0,.15);
  backdrop-filter: blur(8px);
  transition-duration: 1s;
  height: 100vh;
}
#popup-container.open .popup__background.left {
  justify-content: start;
}
#popup-container.open .popup__background.center {
  justify-content: center;
}
#popup-container.open .popup__background.right {
  justify-content: end;
}
#popup-container.open .popup__background.top {
  align-items: start;
}
#popup-container.open .popup__background.middle {
  align-items: center;
}
#popup-container.open .popup__background.bottom {
  align-items: end;
}

#popup-container .popup__box {
  position: relative;
  display: grid;
  place-items: center;
  color: #343434;
  padding: 110px 60px 30px;
  overflow : hidden;
}

#popup-container .popup_header{
  height: 40px;
}

#popup-container .popup__wrap {
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #fff;
}

.popup__box .title {
  font-size: 13px;
  font-weight: normal;
  font-stretch: normal;
  line-height: normal;
  letter-spacing: normal;
  text-align: center;
  margin-bottom: 20px;
}

.popup__box .popup_body{
  max-height: 320px;
  overflow-y: auto;
}
#popup-container p,
#popup-container span{
  font-size: 11px;
  font-weight: normal;
  font-stretch: normal;
  line-height: 2.5;
  letter-spacing: normal;
  text-align: center;
  overflow-y: auto;
  max-height: 420px;
}
#popup-container .popup__wrap{
  position:relative;
}
.popup_close {
	width: 12px;
    height: 12px;
    position: absolute;
    top: 30px;
    right: 30px;
    background: url(/images/ico-close.svg) center no-repeat;
    transition: ease-out .2s;
	z-index: 9;
	cursor:pointer;
}
#popup-container .close-btn svg:hover {
  transform: rotate(90deg);
  transition: transform 0.5s;
}

#popup-container .popup_logo {
  margin-top: 90px;
}

#popup-container .popup_logo img {
  pointer-events: none;
  height: 10px;
}

#popup-container .do_not_open {
  position: absolute;
  left: 0;
  bottom: -25px;
  display: flex;
  align-items: center;
  gap: 5px;
}

#popup-container .do_not_open [type="checkbox"] {
  border: 1px solid #fff;
}

#popup-container .do_not_open [type="checkbox"]:checked {
  background-color: #000000;
  border: 1px solid gray;
}

#popup-container .do_not_open span {
  color: #fff;
}

@media (min-width: 1025px) and (max-width: 1300px) {
  #popup-container .popup__box {
    padding: 60px 30px 30px;
    min-width:260px;
    min-height:260px;
	overflow : hidden;
  }

  #popup-container .popup_logo{
    margin-top : 50px;
  }
}

</style>

<div class="btn btn_naver">네이버 로그인</div>
<div class="btn btn_kakao">카카오 로그인</div>
<div class="btn btn_google">구글 로그인</div>

<div class="btn btn_pass">PASS 인증</div>

<!--
드림 시큐리티 운영 URL
<script src="https://cert.mobile-ok.com/resources/js/index.js"></script>
-->

<!-- 드림 시큐리티 개발 URL -->
<script src="https://scert.mobile-ok.com/resources/js/index.js"></script>

<script>
$(document).ready(function() {
	/* 카카오 로그인 버튼 클릭 */
	let btn_login_K = document.querySelector('.btn_kakao');
	if (btn_login_K != null) {
		btn_login_K.addEventListener('click', function () {
			let oauth_kakao		= "https://kauth.kakao.com/oauth/";
			let client_kakao	= "b43df682b08d3270e40a79b5c51506b5";
			let redirect_kakao	= "https://stg.adererror.com/kr/kakao-login";
			
			let tmp_url = `${oauth_kakao}authorize?client_id=${client_kakao}&scope=account_email,name,phone_number,birthyear&redirect_uri=${redirect_kakao}&response_type=code&prompt=login`;
			location.href = tmp_url;
		});
	}

	/* 네이버 로그인 버튼 클릭 */
	let btn_login_N = document.querySelector('.btn_naver');
	if (btn_login_N != null) {
		btn_login_N.addEventListener('click', function () {
			let oauth_naver		= "https://nid.naver.com/oauth2.0/";
			let client_naver	= "k4gK4Eon6TG0GwnX5zhM";
			let redirect_naver	= encodeURI("https://stg.adererror.com/kr/naver-login");
			
			let mt				= Date.now().toString();
			let rand			= Math.random().toString();
			let state			= CryptoJS.MD5(mt + rand).toString();

			let tmp_url = `${oauth_naver}authorize?response_type=code&client_id=${client_naver}&redirect_uri=${redirect_naver}&state=${state}`;
			location.href = tmp_url;
		});
	}

	/* 카카오 로그인 버튼 클릭 */
	let btn_login_G = document.querySelector('.btn_google');
	if (btn_login_G != null) {
		btn_login_G.addEventListener('click', function () {
			let oauth_google	= "https://accounts.google.com/o/oauth2/v2/auth";
			let client_google	= "999124937022-qgkvknpulb77vgvdntunoqsj90ka2jga.apps.googleusercontent.com";
			let redirect_google	= encodeURI(`https://stg.adererror.com${config.base_url}/google-login`);
			let scope			= encodeURI("https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email");
			
			let tmp_url = `${oauth_google}?client_id=${client_google}&redirect_uri=${redirect_google}&state=OK&scope=${scope}&access_type=online&include_granted_scopes=true&response_type=code`;
			location.href = tmp_url;
		});
	}

	let btn_pass = document.querySelector('.btn_pass');
	if (btn_pass != null) {
		btn_pass.addEventListener('click',function() {
			MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-request", "WB", "mok_result");
		});
	}

	checkPopup();
});

function mok_result(result) {
	console.log(' [ MOK RESULT ] ',result);
	/*
	try {
		result = JSON.parse(result);
		
		let tel_mobile = result.user_phone.replace(/[^0-9]/g, '').replace(/^(\d{0,3})(\d{0,4})(\d{0,4})$/g, "$1-$2-$3").replace(/(\-{1,2})$/g, "");
		document.querySelector('.join_tel_mobile').value = tel_mobile;
		
		let user_birth = result.user_birth;
		console.log(user_birth);
		
		document.querySelector('.birth_y').value = user_birth.substring(0,4);
		document.querySelector('.birth_m').value = user_birth.substring(4,6);
		document.querySelector('.birth_d').value = user_birth.substring(6,9);
		
		$('#mobile_authenfication_flg').val('true');
		
		$('.warn__msg.tel_confirm').css('display', 'none');
	} catch (error) {
		$('#mobile_authenfication_flg').val('false');
		
		$('.warn__msg.tel_confirm').css('display', 'none');
		$('.warn__msg.tel_confirm.format').css('display', 'block');
		$('.warn__msg.tel_confirm.format').css('visibility', 'visible');
	}
	*/
}

function checkPopup() {
	let popup_type	= null;
	let param_popup	= null;

	let path = location.pathname.replace(config.base_url,"");
	if (path.includes('/shop')) {
		popup_type	= "P";
		param_popup = location.pathname.split("/")[3];
	} else {
		popup_type	= "W";
		param_popup = path;
	}

	$.ajax({
		url : config.api + "popup",
		headers : {
			country : config.language
		},
		data : {
			'popup_type'	:popup_type,
			'param_popup'	:param_popup
		},
		success : function(d) {
			if (d.data != null) {
				setPopup(d.data);
			}
		}
	});
}

function setPopup(data) {
	let close_key	= `popup_close_${data.popup_idx}`;
	let close_time	= localStorage.getItem(close_key);

	if (close_time != null) {
		let now = new Date();
		now = now.setTime(now.getTime());
		
		if (parseInt(close_time) <= now) {
			localStorage.removeItem(`popup_close_${data.popup_idx}`);
		} else if (parseInt(close_time) > now) {
			return false;
		}
	}

	const body = document.body;

	let tmp_popup = document.querySelector('#popup-container');
	if (tmp_popup != null) {
		tmp_popup.remove();
	}

	let t_close		= "popup_close_none";
	let id_close	= "none";

	if (data.close_flg == 'TODAY') {
		t_close		= "popup_close_tday"
		id_close	= 'tday';
	}

	const popup = document.createElement("div");
	popup.id		= "popup-container";
	popup.className	= "popup-containner open";
	popup.innerHTML = `
		<div class="popup__background center middle">
			<div class="popup__wrap" style="width:${data.width}px;height:${data.height}px">
				<button type="button" class="popup_close"></button>
				<div class="popup__box">
					<div class="popup_header">
						<h1 class="title">
							${data.popup_title}
						</h1>
					</div>
					
					<div class="popup_body" style="">
						${data.popup_contents}
					</div>
					<div class="popup_logo">
						<img src="/images/landing/mini-logo.svg" alt="">
					</div>
				</div>
				<div class="do_not_open">
					<input type="checkbox" id="${id_close}">
						<label for="${id_close}"></label>
					<span>${t_close}</span>
				</div>
			</div>
		</div>
	`

	body.appendChild(popup);

	document.querySelectorAll('#popup-container h1, #popup-container p').forEach(function (el) {
		el.style.removeProperty('font-size');
		el.style.removeProperty('font-family');
		el.style.removeProperty('font-weight');
		el.style.removeProperty('font-stretch');
		el.style.removeProperty('line-height');
		el.style.removeProperty('letter-spacing');
		el.style.removeProperty('text-align');
		el.style.removeProperty('color');
	});

	let close_btn = document.querySelector(`#popup-container .popup_close`);
	close_btn.addEventListener('click', function () {
		document.querySelector('#popup-container').remove();
	});

	document.getElementById(id_close).addEventListener('change', function(){
		if (this.checked) {
			setPopup_close(id_close,data.popup_idx);
		} else {
			localStorage.removeItem(`popup_close_${data.popup_idx}`);
		}
	});

	setPopup_resize(data.width,data.height);
}

function setPopup_close(popup_type,popup_idx){
	let key = `popup_close_${popup_idx}`;
	let param_day = 0;
	if(popup_type == 'tday'){
		param_day = 1;
	}
	else{
		param_day = 9999;
	}

	var date = new Date();
	date = date.setTime(date.getTime() + param_day * 24 * 60 * 60 * 1000);
	localStorage.setItem(key, date);
}

function setPopup_resize(width,height) {
	let windowWidth		= window.innerWidth;
	let popup__wrap		= document.querySelector('.popup__wrap');
	let popup_header	= document.querySelector('.popup_header');
	let popup_body		= document.querySelector('.popup_body');
	
	popup__wrap.style.removeProperty('width');
	popup__wrap.style.removeProperty('height');
	popup_header.style.removeProperty('width');
	popup_header.style.removeProperty('height');
	popup_body.style.removeProperty('width');
	popup_body.style.removeProperty('height');
	
	let header_width = popup_header.clientWidth;
	let body_width = popup_body.clientWidth;

	let contents_width = header_width>body_width?header_width:body_width;
	let contents_height = 0;

	if(windowWidth > 1024){
		popup__wrap.style.width = `${width}px`;
		popup__wrap.style.height = `${height}px`;
	} else {
		if (contents_width > windowWidth - 100) {
			popup_header.style.width = `${windowWidth - 100}px`;
			popup_body.style.width = `${windowWidth - 100}px`;
		}
	
		contents_height = popup_header.clientHeight + popup_body.clientHeight;
		if (contents_height > 350) {
			popup_body.style.height = `${350 - popup_header.clientHeight}px`;
		}
	}
}

</script>