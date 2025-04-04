let contents_no = 0,detail_no = 0,booking_date;

$("#tab > li").click(function() {
	$(this).addClass("on").siblings().removeClass("on");
	$("section.mypage > article.calendar h1").text($(this).text());

	switch($(this).index()) {
		// 찜한 연습실
		case 1:
			$("#list").html(`<li class="empty"><i class="xi-close-thin"></i>찜한 연습실이 없습니다.</li>`);
			$.ajax({
				url: config.api + "calendar/favorite/get",
				data: {
					page : 1,
					pagenum : 1000
				},
				success: function(d) {
					if(d.code == 200) {

						if(d.data) {
							$("#list").empty();
							d.data.forEach(function(row) {
								$("#list").append(`
									<li data-no="${row.no}">
										<div class="cont">
											<button type="button" class="fold"><i class="xi-caret-down"></i></button>
											<button type="button" class="move"><i class="xi-arrows-v"></i></button>
											${row.title}
										</div>
										<div class="thumbnail" style="background-image:url('${row.images.list}')">
											<div class="color">
												<input type="text" name="color" value="${row.color}" readonly>
												<i class="xi-eyedropper"></i>
											</div>
										</div>
										<div class="picker"></div>
									</li>
								`);
								$($("#list > li").last().find(".picker")).farbtastic($("#list > li").last().find("input[name='color']"));
							});

							$("#list .picker").append(`<button type="button" class="close"><i class="xi-close"></i></button>`);
							$("#list .picker button.close").click(function() {
								$(this).parent().removeClass("on");
							});

							$("#list .color").click(function() {
								if($(this).parent().next().hasClass("on")) {
									$(this).parent().next().removeClass("on");
								}
								else {
									$("#list .picker.on").removeClass("on");
									$(this).parent().next().addClass("on");
								}
							});
							$("#list").sortable({
								axis : "y",
								placeholder: "move-highlight",
								stop : function(event, ui) { // 순서 변경
									if($("#tab > li.on").index() == 1) {
										let no = [];
										$("#list > li").each(function() {
											no.push($(this).data("no"));
										});
										$.ajax({
											url: config.api + "calendar/favorite/seq",
											data: { no : no },
											success: function(d) {
												if(d.code == 200) {
													toast("순서를 변경했습니다.");
												}
											}
										});
									}
								}
							});
							$("#list").disableSelection();
							/** 색상 변경 **/
							$("#list > li > .picker").mouseup(function() {
								$.ajax({
									url: config.api + "calendar/favorite/color",
									data: { 
										no : $(this).parent().data("no"), 
										color : $(this).parent().find("input[name='color']").val() 
									}
								});
							});

							/** 세부 연습실 폴딩 **/
							$("#list button.fold").click(function() {
								$(this).parent().parent().toggleClass("fold");
							});

							$("#list button.move").mousedown(function() {
								$(this).addClass("on");
							});
							$("#list button.move").mouseup(function() {
								$(this).removeClass("on");
							});
							$("#list > li > .cont").click(function() {
								$("#list .picker.on,#list li.on").removeClass("on");

								if($(this).parent().hasClass("on")) {
									$(this).parent().removeClass("on");
									contents_no = 0;
									$("#calendar span.schedule").show();
									$("#calendar .cont > span").show();
								}
								else {
									$(this).parent().parent().find("li.on").removeClass("on");
									$(this).parent().addClass("on");
									contents_no = $(this).parent().data("no");
									/*
									$("#calendar span.schedule").each(function() {
										if($(this).data("father_no") == contents_no) {
											$(this).show();
										}
										else {
											$(this).hide();
										}
									});
									*/
								}
								get_calendar(booking_date);
								booking_date = null;

								/** 세부 연습실 정보가 없을 경우 **/
								if($(this).parent().find("ul").length == 0) {
									$(this).parent().append("<ul></ul>");
									let obj = $(this).parent().find("ul");
									$.ajax({
										url: config.api + "contents/get",
										data: {
											no : $(this).parent().data("no")
										},
										success: function(d) {
											d.data[0].place.forEach(function(row) {
												$(obj).append(`
													<li data-no="${row.no}">
														<div class="cont">
															${row.title}
														</div>
														<div class="thumbnail" style="background-image:url('${row.images.list}')">
															<div class="color">
																<input type="text" name="color" value="${row.color}" readonly>
																<i class="xi-eyedropper"></i>
															</div>
														</div>
														<div class="picker"></div>
													</li>
												`);
												$($(obj).find("li").last().find(".picker")).farbtastic($(obj).find("li").last().find("input[name='color']"));
											});

											$(obj).find(".picker").append(`<button type="button" class="close"><i class="xi-close"></i></button>`);
											$(obj).find(".picker button.close").click(function() {
												$(this).parent().removeClass("on");
											});

											/** 색상 변경 **/
											$(obj).find(".picker").mouseup(function() {
												$.ajax({
													url: config.api + "calendar/favorite/color",
													data: { 
														detail_no : $(this).parent().data("no"), 
														color : $(this).parent().find("input[name='color']").val() 
													}
												});
											});
											$(obj).find(".color").click(function() {
												if($(this).parent().next().hasClass("on")) {
													$(this).parent().next().removeClass("on");
												}
												else {
													$("#list .picker.on").removeClass("on");
													$(this).parent().next().addClass("on");
												}
											});

											/** 선택 **/
											$(obj).children("li").click(function() {
												$(this).parent().find("li.on").removeClass("on");
												$(this).addClass("on");

												let detail_no = $(this).data("no");
												$("#calendar .cont > span").each(function() {
													let _detail_no = $(this).data("detail_no");
													if(typeof _detail_no != 'undefined') {
														if(detail_no != _detail_no) {
															$(this).hide();
														}
														else {
															$(this).show();
														}
													}
												});
											});
											if(detail_no > 0) {
												$(obj).find("li").each(function() {
													if($(this).data("no") == detail_no) {
														detail_no = 0;
														$(this).click();
													}
												});
											}
										}
									});
								}
							});
							if(contents_no > 0) {
								$("#list > li").each(function() {
									if($(this).data("no") == contents_no) {
										contents_no = 0;
										//$(this).find(".cont").click();
										$(this).find("button.fold").click();
									}
								});
							}
							else {
								$("#list > li:eq(0) > .cont").click();
							}
						}
					}
					else {
						alert(d.msg);
					}
				}
			});

			get_calendar = function(date) {
				draw_calendar($("#calendar"),(typeof date == 'string')?date:null,function(date,type) {
					if(type != "month") return;
					/** 일정 버튼 추가 **/
					$("#calendar td.able > div").each(function() {
						$(this).append(`
							<a>예약+</a>
						`);
					});
					$("#calendar td.able > div > a").click(function() {
						if($("#list > li.on > ul > li.on").length > 0) {
							location.href = `#booking/${$(this).parent().data("date")}`;
						}
						else {
							alert("세부 연습실을 선택해주세요.");
						}
					});

					/** 일정 불러옴 **/
					$.ajax({
						url: config.api + "calendar/get",
						data: { date : date, contents_no : contents_no, detail_no : detail_no },
						success: function(d) {
							if(d.code == 200) {
								if(d.data) {
									d.data.forEach(function(row) {
										row.schedule.forEach(function(col) {
											$(`#c${row.date} .cont`).append(`
												<span data-father_no="${col.father_no}" data-detail_no="${col.detail_no}"  data-no="${col.no}" class="schedule" style="background-color:${col.color}">
													<span class="time">${addzero(col.time[0])}~${addzero(parseInt(col.time[1]+1))}</span>
													<span class="name">${col.name}</span>
												</span>
											`);
										});
										$(`#c${row.date}`).click(function() {
											localStorage.setItem('schedule',JSON.stringify(row));
										});
										$(`#c${row.date} .cont > span.schedule`).click(function() {
											let row_detail,no = $(this).data("no");
											row.schedule.forEach(function(row2) {
												if(row2.no == no) {
													localStorage.setItem('schedule_detail',JSON.stringify(row2));
												}
											});
											modal('calendar-booking-detail',{
												detail_no : $(this).data("detail_no")
											});
										});
									});

									$("#list > li.on > ul > li.on").click();
								}
							}
						}
					});
				});
			};
			//get_calendar();

		break;

		// 내 캘린더
		case 0:
			let cont_type = "",_date = localStorage.getItem("calendar"),member_config = JSON.parse(sessionStorage.user),
				list_title = [
					["예약","booking","연습실 예약"],
					["연습","practice","연습 캘린더"],
					["개인","private","개인 캘린더"]
				];
			if('calendar' in member_config.config && member_config.config.calendar.type) {
				for(let i=0;i<list_title.length;i++) {
					if(member_config.config.calendar.type[list_title[i][1]]) {
						list_title[i][2] = member_config.config.calendar.type[list_title[i][1]];
					}
				}
			}
			$("#list").empty();
			list_title.forEach(function(row) {
				$("#list").append(`
					<li data-type="${row[0]}" class="type-${row[1]}">
						<div class="cont middle">
							<button type="button" class="move"><i class="xi-check"></i></button>
							<div class="edit">
								<div class="input" data-name="${row[1]}">${row[2]}</div>
								<button type="button" class="edit"><i class="xi-pen"></i></button>
							</div>
						</div>
						<div class="thumbnail"></div>
					</li>
				`);
			});
			$("#list > li button.edit").click(function() {
				$(this).prev().attr("contentEditable",true).focus();
			});
			$("#list > li div.input").focusout(function() {
				$(this).removeAttr("contentEditable");
				$(this).text(trim($(this).text()));
				// DB에 반영
				$.ajax({
					url: config.api + "calendar/type/modify",
					data: { type : $(this).data("name"), text : $(this).text() }
				});
			});
			$("#list > li div.input").keyup(function(e) {
				switch(e.keyCode) {
					case 13: // Enter
						$(this).focusout();
					break;
				}
			});
			$("#list > li").click(function() {
				if($(this).hasClass("on")) {
					$(this).removeClass("on");
					$("#calendar span.schedule").show();
					cont_type = '';
				}
				else {
					$(this).parent().find("li.on").removeClass("on");
					$(this).addClass("on");
					cont_type = $(this).data("type");
					/*
					$("#calendar span.schedule").each(function() {
						if($(this).hasClass(cont_type)) {
							$(this).show();
						}
						else {
							$(this).hide();
						}
					});
					*/
				}
				get_calendar();
			});

			get_calendar = function(date) {
				draw_calendar($("#calendar"),(typeof date == 'string')?date:_date,function(date,type) {
					if(type != "month") return;
					/** 일정 버튼 추가 **/
					$("#calendar td.able > div").each(function() {
						$(this).append(`
							<a href="#memo/${$(this).data("date")}"><i class="xi-plus"></i></a>
						`);
					});

					/** 일정 불러옴 **/
					$.ajax({
						url: config.api + "calendar/memo/get",
						data: { date : date, type : cont_type },
						success: function(d) {
							if(d.code == 200) {
								if(d.data) {
									d.data.forEach(function(row) {
										$(`#c${row.date} .cont`).append(`
											<span class="schedule ${row.type}">
												<span class="time">${addzero(row.time[0])}~${addzero(parseInt(row.time[1])+1)}</span>
												<span class="name">${row.title}</span>
											</span>
										`);
										$(`#c${row.date} .cont`).addClass("has");
									});
									localStorage.setItem('schedule',JSON.stringify(d.data));
									$(`#calendar td .cont.has`).click(function() {
										modal('calendar-detail',{
											date : $(this).parent().data("date")
										});
									});
									if(_date != null) {
										localStorage.removeItem("calendar");
										$(`#c${_date}`).click();
										$(`#c${_date} .cont`).click();
										_date = null;
									}
								}
							}
						}
					});
				});
			};
			get_calendar();

		break;
	}
}).eq(0).click();


if(localStorage.getItem('pay-ok')) {
	if(location.pathname == '/pay/ok') {
		$("body").empty();
		location.href = "/calendar";
	}
	else {
		let data = JSON.parse(localStorage.getItem('pay-ok'));
		localStorage.removeItem('pay-ok');
		booking_date = data.date;
		contents_no = data.contents_no;
		detail_no = data.detail_no;
		$("#tab > li").eq(1).click(); // 찜한 연습실
	}
}