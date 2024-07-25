$(document).ready(function() {
	$.ajax({
		url: config.api + "member/address/get",
		error: function () {
			makeMsgNoti('MSG_F_ERR_0046', null);
			//notiModal("계정", "배송지 목록을 불어오는데 실패했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let defaultList = $('.default__list');
				let otherList = $('.other__list');
				let defaultListStr = "";
				let otherListStr = "";

				defaultList.html('');
				otherList.html('');

				if(d.data != null) {
					d.data.forEach((row) => {
						let addr = row.to_road_addr ? row.to_road_addr : row.to_lot_addr;
						let detailAddr = row.to_detail_addr ? row.to_detail_addr : '';

						let fullAddrStr = '';
						if (config.language == 'KR') {
							fullAddrStr = `${addr} ${detailAddr}`;
						}
						else {
							fullAddrStr = `${row.to_address}, ${row.to_city}, ${row.to_province_name}, ${row.to_country_name}`;
						}

						if (row.default_flg == true) {
							defaultListStr +=
								`
							  <tr class="default_destination">
								  <td>${row.to_place}</td>
								  <td>${row.to_name}</td>
								  <td>${row.to_mobile}</td>
								  <td>${fullAddrStr}</td>
								  <td>${row.to_zipcode}</td>
								  <td class="order_to_btn_td">
									  <div class="order_to_btn_wrap">
											<div class="gray__mypage__btn update_order_to" idx="${row.order_to_idx}" data-i18n="p_edit">수정</div>
											<div class="white__full__width__btn delete_order_to"  data-i18n="p_delete" idx="${row.order_to_idx}">삭제</div>
									  </div>
								  </td>
							  </tr>
						  `;
						} else {
							otherListStr +=
								`
							<tr class="other_destination">
								<td>
									<div class="other_destination_header">
										<div>${row.to_place}</div>
										<div class="order_to_idx change_default_order_to" idx="${row.order_to_idx}" data-i18n="p_set_default_address">기본 배송지로 저장</div>
									</div>
								</td>
								<td>${row.to_name}</td>
								<td>${row.to_mobile}</td>
								<td>${fullAddrStr}</td>
								<td>${row.to_zipcode}</td>
								<td class="order_to_btn_td">
									<div class="order_to_btn_wrap">
										<div class="gray__mypage__btn update_order_to" idx="${row.order_to_idx}" data-i18n="p_edit">수정</div>
										<div class="white__full__width__btn delete_order_to"  data-i18n="p_delete" idx="${row.order_to_idx}">삭제</div>
									</div>
								</td>
							</tr>
						`;
						}
					});

					let default_exception_msg = "";
					let other_exception_msg = "";

					if (defaultListStr.length == 0) {

						switch (getLanguage()) {
							case "KR":
								default_exception_msg = "기본 배송지 정보가 없습니다.";
								break;

							case "EN":
								default_exception_msg = "There is no history.";
								break;

							case "CN":
								default_exception_msg = "没有查询到相关资料。​";
								break;

						}
						defaultListStr =
							`
							<tr>
								<td class="no_order_to_msg">
									<div>${default_exception_msg}</div>
								</td>
							</tr>
						`;
					}

					if (otherListStr.length == 0) {
						switch (getLanguage()) {
							case "KR":
								other_exception_msg = "다른 배송지 정보가 없습니다.";
								break;

							case "EN":
								other_exception_msg = "There is no history.";
								break;

							case "CN":
								other_exception_msg = "没有查询到相关资料。​";
								break;

						}
						otherListStr =
							`
							  <tr>
								  <td class="no_order_to_msg">
									  <div>${other_exception_msg}</div>
								  </td>
							  </tr>
						  `;
					}

					defaultList.append(defaultListStr);
					otherList.append(otherListStr);

					$(".update_order_to").on("click", function () {
						let order_to_idx = $(this).attr('idx');

						$('.profile__tab').hide();
						$('.hidden_order_to_idx').val(order_to_idx);
						getOrderTo();
						$('.order__to__update__wrap').show();
					});

					$(".delete_order_to").on("click", function () {
						deleteOrderTo(this);
					});

					$(".change_default_order_to").on("click", function () {
						changeDefaultOrderTo(this);
					});

					changeLanguageR();
				} else {
					let default_exception_msg = "";
					let other_exception_msg = "";
					switch (getLanguage()) {
						case "KR":
							default_exception_msg = "기본 배송지 정보가 없습니다.";
							other_exception_msg = "다른 배송지 정보가 없습니다.";
							break;

						case "EN":
							default_exception_msg = "There is no history.";
							other_exception_msg = "There is no history.";
							break;

						case "CN":
							default_exception_msg = "没有查询到相关资料。​";
							other_exception_msg = "没有查询到相关资料。​";
							break;

					}
					defaultListStr =
						`
						  <tr>
							  <td class="no_order_to_msg">
								  <div>${default_exception_msg}</div>
							  </td>
						  </tr>
					  `;
					otherListStr =
						`
						  <tr>
							  <td class="no_order_to_msg">
								  <div>${other_exception_msg}</div>
							  </td>
						  </tr>
					  `;

					defaultList.append(defaultListStr);
					otherList.append(otherListStr);
				}
			} 
			
			else {
				if(d.msg != null) {
					notiModal(d.msg);
					if (d.code = 401) {
						$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
					}
				}
				else {
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0002', null);
				}
			}
		}
	});

});