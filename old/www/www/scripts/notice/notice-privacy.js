$(document).ready(function (){
	$('.info__wrap').hide();
	$('.tab__btn').removeClass('selected');
	
	var notice_type = $('#notice_type').val();
	if(notice_type == null || notice_type.length <= 0){
		$('.info__wrap.online_store_info').show();
		$('.tab__btn.online_store').addClass('selected');
	} else {
		$('.info__wrap.' + notice_type + '_info').show();
		$('.tab__btn.' + notice_type).addClass('selected');
	}
	
	getPolicyInfo('GUD');
	getPolicyInfo('TRM');
	getPolicyInfo('PNL');
	getPolicyInfo('COK');
	
	clickTabPrivacy();
});

function clickTabPrivacy(){
	let tab_btn = document.querySelectorAll('.tab__btn');
	tab_btn.forEach(tab => {
		tab.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let tab_type = el.dataset.tab_type;
			
			if (!el.classList.contains('selected')) {
				$('.tab__btn').removeClass('selected');
				el.classList.add('selected');
				
				$('.info__wrap').hide();
				$('.info__wrap.' + tab_type + '_info').show();
			}
		});
	});
}

let language = localStorage.getItem('lang') || getLanguage();

function getPolicyInfo(policy_type) {
	let dir_api = "";
	switch (policy_type) {
		case "GUD" :
			dir_api = "guidance";
			break;
		
		case "TRM" :
			dir_api = "terms";
			break;
		
		case "PNL" :
			dir_api = "privacy";
			break
		
		case "COK" :
			dir_api = "cookie";
			break;
	}
	
	$.ajax({
		type: "post",
		url: api_location + "policy/page/" + dir_api + "/get",
		headers: {
			"country": getLanguage(),
		},
		dataType: "json",
		async: false,
		error: function() {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0036", null);
			// notiModal("법적 고지사항 조회처리중 오류가 발생했습니다.");
		},
		success: function(d) {
			if (d.code == 200) {
				let data = d.data;

				if (data != null) {
					let info_wrap = null;
					
					let policy_type = data.policy_type;
					switch (policy_type) {
						case "GUD" :
						info_wrap = document.querySelector(".notice__privacy__wrap .online_store_info .info__scroll__wrap");
						
						break;
						
						case "PNL" :
							info_wrap = document.querySelector(".notice__privacy__wrap .privacy_policy_info .info__scroll__wrap");
							
							break;
						
						case "TRM" :
							info_wrap = document.querySelector(".notice__privacy__wrap .terms_of_use_info .info__scroll__wrap");
						
							break;
						
						case "COK" :
							info_wrap = document.querySelector(".notice__privacy__wrap .cookies_policy_info .info__scroll__wrap");
							
							break;
					}
					
					info_wrap.innerHTML = data.policy_txt;
				} else {
					makeMsgNoti(getLanguage(), "MSG_F_ERR_0038", null);
					// notiModal("법적 고지사항 정보가 존재하지 않습니다.");
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}