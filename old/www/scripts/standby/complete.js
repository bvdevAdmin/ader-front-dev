var standby_idx = $('main').attr('data-standby_idx');
document.addEventListener('DOMContentLoaded', function() {
	entryStandby();
});
function entryStandby(){
	$.ajax({
		type: "post",
		url: api_location + "standby/add",
		data: { 
			'standby_idx': standby_idx
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0005", null);
			// notiModal("스탠바이", "페이지 불러오기가 실패했습니다. 다시 진행해주세요.");
			redirectStandby();
		},
		success: function (d) {
			if(d != null) {
				if(d.code == 200) {
					let strDiv = `
						<div class="join-title-wrap">
							<div class="standby-join-title">STANDBY</div>
							<div class="standby-join-subtitle">ADER Callio Tote Bag</div>
							<div class="standby-join-noti1" data-i18n="sb_complete_msg"></div>
							<div class="standby-join-noti2" data-i18n="sb_purchase_link"></div>
						</div>
						<div class="join-btn-wrap">
							<a href="/mypage?mypage_type=stanby_second">
								<div class="join--btn my">
									<span data-i18n="sb_participation_history"></span>
								</div>
							</a>
							<a href="/main">
								<div class="join--btn">
									<span data-i18n="sb_return_home"></span>
								</div>
							</a>
						</div>
					`;

					$('.join-wrap').append(strDiv);
				} else {
					notiModal(d.msg);
					redirectStandbyEntry(standby_idx);  
				}
			} else {
				makeMsgNoti(getLanguage(), "MSG_F_ERR_0020", null);
				// notiModal("스탠바이", "스탠바이정보를 찾을 수 없습니다.");
				redirectStandbyEntry(standby_idx);
			}
		}
	});
}

function redirectStandbyEntry(standby_idx){
	$('#notimodal-modal .close-btn').attr('onclick', `location.href="/standby/entry?standby_idx=${standby_idx}"`);
}