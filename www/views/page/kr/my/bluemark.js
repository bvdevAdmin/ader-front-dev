let t_column = {
    KR : {
        t_01	:"구매처",
        t_02	:"Bluemark 시리얼코드",
        t_03	:"Bluemark 인증 날짜",
		t_04	:"제품 양도하기",
		t_05	:"인증 취소"
    },
    EN : {
        t_01	:"Mall",
        t_02	:"Serial number",
        t_03	:"Verify date",
		t_04	:"Hand over",
        t_05	:"Cancel"
    }
};

let t_cancel = {
    KR : {
        t_01	:"블루마크 인증 취소",
        t_02	:"<p>취소 후 분실 등으로 인증번호를 잊으신 경우에는 재발급이 불가합니다.</p><p>블루마크 인증을 취소하시겠습니까?</p>"
    },
    EN : {
        t_01	:"Cancel verification",
        t_02	:"<p>If you forget your authentication number after cancellation due to loss, etc., it cannot be reissued.</p><p>Are you sure you want to revoke blue mark authentication?</p>"
    }
};

let msg_hanover = {
	KR : "양도 받을 이메일을 입력해주세요.",
	EN : "Please enter the hand over E-mail."
}

let txt_none = {
	KR : "조회 가능한 블루마크 인증내역이 존재하지 않습니다.",
	EN : "There is no bluemark verification history.",
}

$(document).ready(function() {
	clickBTN_verify();
	
	getBluemark_list();

	let type = get_query_string('type');
	if (type != null) {
		if (type == "regist") {
			$('.bluemark .tab-container li').eq(0).click();
		} else if (type == "list") {
			$('.bluemark .tab-container li').eq(1).click();
		}
	}
});

const data = {
	rows: 10,
	page: 1
}

function getBluemark_list() {
	$.ajax({
		url : config.api + "bluemark/list/get",
		headers : {
			country : config.language
		},
		data,
		async: false,
		success : function(d) {
			if (d.code == 200) {
				$("#list").html('');
				
				let data = d.data;
				if (data != null && data.length > 0) {
					data.forEach(row => {
						$("#list").append(`
							<li data-no="${row.bluemark_idx}">
								<div class="info">
									<div class="image" style="background-image:url('${config.cdn}${row.img_location}')"></div>
									<div class="goods">
										<big>${row.product_name}</big>
										<div class="price">${number_format(row.sales_price)}</div>
										<div class="color">${row.color}<span class="colorchip" style="background-color:${row.color_rgb}"></span></div>
										<div class="size">${row.option_name}</div>
									</div>
									<div class="bluemark">
										<dl>
											<dt>${t_column[config.language]['t_01']}</dt>
											<dd>${row.purchase_mall}</dd>
											<dt>${t_column[config.language]['t_02']}</dt>
											<dd>${row.serial_code}</dd>
											<dt>${t_column[config.language]['t_03']}</dt>
											<dd>${row.reg_date}</dd>
										</dl>
									</div>
								</div>
								<div class="buttons grid-2">
									<button type="button" class="transfer">${t_column[config.language]['t_04']}</button>
									<button type="button" class="cancel" data-bluemark_idx="${row.bluemark_idx}" data-log_idx="${row.log_idx}">${t_column[config.language]['t_05']}</button>
								</div>
							</li>			
						`);
					});
				} else {
					$("#list").append(`
						<div class="list__none">
							${txt_none[config.language]}
						</div>
					`);
				}
			} else {
				alert(
					d.msg,
					function() {
						if (d.code == 401) {
							sessionStorage.setItem('r_url',location.href);
							location.href = `${config.base_url}/login`;
						}
					}
				)
			}
			
			/** 제품 양도 **/
			$("#list button.transfer").click(function() {
				modal('transfer',{ no : $(this).parent().parent().data("no") });
				
				clickBTN_transfer();
			});
			
			/** 인증 취소 **/
			$("#list button.cancel").click(function() {
				let bluemark_idx = $(this).data('bluemark_idx');
				let log_idx = $(this).data('log_idx');
				
				if (bluemark_idx != null && log_idx != null) {
					confirm({
						title	: t_cancel[config.language]['t_01'],
						body	: t_cancel[config.language]['t_02'],
						ok : function() {
							$.ajax({
								url : config.api + "bluemark/put",
								headers : {
									country : config.language
								},
								data : {
									'action_type'		:"CANCEL",
									'bluemark_idx'		:bluemark_idx,
									'log_idx'			:log_idx
								},
								success : function(d2) {
									alert(
										d2.msg,
										function() {
											modal_close();
											if (d2.code == 200) {
												modal_close();

												getBluemark_list();
											}
										}
									);
								}
							});
						}
					});
				}
			});

			/** 페이징 처리 **/
			if('page' in d) {
				paging({
					total : d.total,
					el : $(".paging"),
					page : d.page,
					rows : data.rows,
					show_paging : 10,
					fn : function(page) {
						data.page = page
						getBluemark_list();
					}
				});
			}
		}
	});
}

function clickBTN_verify() {
	let btn_verify = document.querySelector('.blue.no-over');
	if (btn_verify != null) {
		btn_verify.addEventListener('click',function() {
			let store_no = document.querySelector('.store_no').value;
			let bluemark = document.querySelector('#frm-bluemark-regist .bluemark').value;
			
			$.ajax({
				url : config.api + "bluemark/add",
				headers : {
					country : config.language
				},
				data : {
					'store_no'		:store_no,
					'bluemark'		:bluemark
				},
				success : function(d) {
					if (d.code == 200) {
						getBluemark_list();
					}
					
					alert(d.msg);
				}
			});
		});
	}
}

function clickBTN_transfer() {
	let btn_transfer = document.querySelector('.section__transfer .btn_transfer');
	if (btn_transfer != null) {
		btn_transfer.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let bluemark_idx = el.dataset.bluemark_idx;
			let section_type = null;
			
			let section = document.querySelector('.section__tab.on');
			if (section != null) {
				section_type = section.dataset.section_type;
			}
			
			if (section_type != null) {
				let action_type		= "TRANSFER";
				let country			= $('.section__tab.on .country').val();
				let transfer_id		= null;
				let tel_mobile		= null;
				
				if (section_type == "MAIL") {
					let param_id = $('.section__transfer .input-email').val();
					if (param_id == null || param_id == "") {
						alert(`${msg_handover[config.language]}`);
						return false;
					} else {
						transfer_id = param_id;
					}
				} else if (section_type == "TEL") {
					let param_mobile = $('.section__transfer .input-tel').val();
					if (param_mobile == null || param_mobile == "") {
						alert(`${msg_hanover[config.language]}`);
						return false;
					} else {
						tel_mobile = param_mobile;
					}
				}
				
				$.ajax({
					url : config.api + "bluemark/put",
					headers : {
						country : config.language
					},
					data : {
						'action_type'		:action_type,
						'transfer_type'		:section_type,
						'bluemark_idx'		:bluemark_idx,
						'param_country'		:country,
						'transfer_id'		:transfer_id,
						'tel_mobile'		:tel_mobile
					},
					success : function(d) {
						alert(
							d.msg,
							function() {
								modal_close();

								if (d.code == 200) {
									modal_close();
									getBluemark_list();
								}
							}
						);
					}
				});
			}
		});
	}
}