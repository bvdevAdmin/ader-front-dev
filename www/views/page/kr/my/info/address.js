$(document).ready(function() {
	getAddress_list();
});

/* 마이페이지 배송지 - 회원 배송지 목록 조회 */
function getAddress_list() {
	$.ajax({
		url: config.api + "member/address/get",
		headers : {
			country : config.language
		},
		error: function () {
			makeMsgNoti('MSG_F_ERR_0046',null);
			//notiModal("계정", "배송지 목록을 불어오는데 실패했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let str_div = "";
				
				let div_list = $('#list');
				div_list.html('');
				
				let data = d.data;
				if (data != null && data.length > 0) {
					data.forEach(function(row) {
						let t_column = {
							KR : {
								't_00' : "등록된 배송지가 없습니다.",
								't_01' : "배송지",
								't_02' : "이름",
								't_03' : "휴대전화",
								't_04' : "우편번호",
								't_05' : "주소",
								't_06' : "기본 배송지",
								
								't_07' : "기본 배송지 설정",
								't_08' : "배송지 수정",
								't_09' : "배송지 삭제"
							},
							EN : {
								't_00' : "There is no address.",
								't_01' : "Place",
								't_02' : "Receipt",
								't_03' : "Mobile number",
								't_04' : "Zipcode",
								't_05' : "Address",
								't_06' : "Default address",

								't_07' : "Default address",
								't_08' : "Edit",
								't_09' : "Delete"
							}
						}

						let msg_default = "";
						if (row.default_flg == true) {
							msg_default = `<div class="msg_default">${t_column[config.language]['t_06']}</div>`;
						}

						str_div += `
							<li data-no="146">
								<div class="info">
									<div class="address">
										<dl>
											<dt>${t_column[config.language]['t_01']}</dt>
											<dd>${row.to_place}</dd>
											<dt>${t_column[config.language]['t_02']}</dt>
											<dd>${row.to_name}</dd>
											<dt>${t_column[config.language]['t_03']}</dt>
											<dd>${row.to_mobile}</dd>
											<dt>${t_column[config.language]['t_04']}</dt>
											<dd>${row.to_zipcode}</dd>
											<dt>${t_column[config.language]['t_05']}</dt>
											<dd>${row.txt_addr}</dd>
										</dl>
										
										${msg_default}
									</div>
								</div>
								<div class="buttons grid-3">
									<button type="button" class="default" data-no="${row.order_to_idx}">${t_column[config.language]['t_07']}</button>
									<button type="button" class="update" data-no="${row.order_to_idx}">${t_column[config.language]['t_08']}</button>
									<button type="button" class="delete" data-no="${row.order_to_idx}">${t_column[config.language]['t_09']}</button>
								</div>
							</li>
						`;
					});
				} else {
					str_div += `
						<li class="empty">
							${t_column[config.language]['t_00']}
						</li>
					`;
				}
				
				div_list.append(str_div);
				
				/* 마이페이지 배송지 - 기본 배송지 설정 버튼 클릭 처리 */
				clickBTN_default();
				
				/* 마이페이지 배송지 - 배송지 수정 버튼 클릭 처리 */
				clickBTN_update();
				
				/* 마이페이지 배송지 - 배송지 삭제 버튼 클릭 처리 */
				clickBTN_delete();
			} else {
				if (d.msg != null) {
					notiModal(d.msg);
					if (d.code = 401) {
						$('#notimodal-modal .close-btn').attr('onclick',`location.href = "${config.base_url}/login"`);
					}
				} else {
					makeMsgNoti('MSG_F_WRN_0002',null);
				}
			}
		}
	});	
}

/* 마이페이지 배송지 - 기본 배송지 설정 버튼 클릭 처리 */
function clickBTN_default() {
	let btn_default = document.querySelectorAll('.default');
	btn_default.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let address_idx = el.dataset.no;
			if (address_idx != null) {
				$.ajax({
					url: config.api + "member/address/put",
					headers : {
						country : config.language
					},
					data :{
						'action_type'		:"DEFAULT",
						'address_idx'		:address_idx
					},
					error: function () {
						makeMsgNoti('MSG_F_ERR_0046',null);
					},
					success: function (d) {
						if (d.code == 200) {
							/* 마이페이지 배송지 - 회원 배송지 목록 조회 */
							getAddress_list();
							
							makeMsgNoti('MSG_F_INF_0014', null);
						} else {
							if (d.msg != null) {
								notiModal(d.msg);
								if (d.code = 401) {
									$('#notimodal-modal .close-btn').attr('onclick',`location.href = "${config.base_url}/login"`);
								}
							}
						}
					}
				});
			}
		});
	});
}

/* 마이페이지 배송지 - 배송지 수정 버튼 클릭 처리 */
function clickBTN_update() {
	let btn_update = document.querySelectorAll('.update');
	btn_update.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let address_idx = el.dataset.no;
			if (address_idx != null) {
				location.href = `${config.base_url}/my/info/address/put/${address_idx}`;
			}
		});
	});
}

/* 마이페이지 배송지 - 배송지 삭제 버튼 클릭 처리 */
function clickBTN_delete() {
	let btn_delete = document.querySelectorAll('.delete');
	btn_delete.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let address_idx = el.dataset.no;
			if (address_idx != null) {
				$.ajax({
					url: config.api + "member/address/put",
					headers : {
						country : config.language
					},
					data :{
						'action_type'		:"DELETE",
						'address_idx'		:address_idx	
					},
					error: function () {
						makeMsgNoti('MSG_F_ERR_0046',null);
					},
					success: function (d) {
						if (d.code == 200) {
							/* 마이페이지 배송지 - 회원 배송지 목록 조회 */
							getAddress_list();
							
							makeMsgNoti('MSG_F_INF_0007',null);
						} else {
							if (d.msg != null) {
								alert(
									d.msg,
									function() {
										if (d.code == 401) {
											sessionStorage.setItem('r_url',location.href);
											location.href = `${config.base_url}/login`;
										}
									}
								);
							}
						}
					}
				});
			}
		});
	});
}
