function random(array) {
	for (let i = 0; i < array.length; i++) {
		let j = Math.floor(Math.random() * array.length);
		let tmp = array[i];
		array[i] = array[j];
		array[j] = tmp;
	}

	return array;
}

function draw_calendar(obj,date,fn) {
	switch(typeof date) {
		case 'string':
			date = date.split('-');
			date[1] = date[1] - 1;
			date = new Date(date[0], date[1], date[2]);
		case 'object':
			break;
		case 'function':
			fn = date;
		default:
			date = new Date();
	}
	if(date == null) date = new Date();

	var currentYear = date.getFullYear(); //년도를 구함
	var currentMonth = date.getMonth() + 1; //연을 구함. 월은 0부터 시작하므로 +1, 12월은 11을 출력
	var currentDate = date.getDate(); //오늘 일자.
	var nowDate = currentYear + "-" + currentMonth + "-" + currentDate;
	date.setDate(1);
	var currentDay = date.getDay();
	//이번달 1일의 요일은 출력. 0은 일요일 6은 토요일

	var lastDate = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	if( (currentYear % 4 === 0 && currentYear % 100 !== 0) || currentYear % 400 === 0 )
	lastDate[1] = 29;
	//각 달의 마지막 일을 계산, 윤년의 경우 년도가 4의 배수이고 100의 배수가 아닐 때 혹은 400의 배수일 때 2월달이 29일 임.

	var currentLastDate = lastDate[currentMonth-1];
	var week = Math.ceil( ( currentDay + currentLastDate ) / 7 );
	//총 몇 주인지 구함.

	if(currentMonth != 1)
	var prevDate = currentYear + '-' + ( currentMonth - 1 ) + '-01';
	else
	var prevDate = ( currentYear - 1 ) + '-12-01';
	//만약 이번달이 1월이라면 1년 전 12월로 출력.

	if(currentMonth != 12) {
		var nextDate = currentYear + '-' + ( currentMonth + 1 ) + '-01';
	}
	else {
		var nextDate = ( currentYear + 1 ) + '-01-01';
	}
	//만약 이번달이 12월이라면 1년 후 1월로 출력.
	if( currentMonth < 10 )	var currentMonth = '0' + currentMonth;
	//10월 이하라면 앞에 0을 붙여준다.

	var calendar = '';

	$(obj).find(".year").html(parseInt(currentYear));
	$(obj).find(".month").html(parseInt(currentMonth));
	$(obj).find(".prev").data("date",prevDate);
	$(obj).find(".next").data("date",nextDate);
	$(obj).find(".prev,.next").off().click(function() {
		draw_calendar(obj,$(this).data("date"),fn);
	});

	var dateNum =1-currentDay;

	for(var i = 0; i < week; i++) {
		calendar += '<tr>';
		for(var j = 0; j < 7; j++, dateNum++) {
			calendar += '<td ';
			if( dateNum < 1 || dateNum > currentLastDate ) {
				calendar += ' class="none"></td>';
				continue;
			}
			calendar += 'id="c' + currentYear + '-' + appendzero(parseInt(currentMonth)) + '-' + appendzero(dateNum) + '" class="';
			if(new Date(currentYear,currentMonth-1,dateNum+1) > new Date()) {
				calendar += 'able ';
				if(new Date(currentYear,currentMonth-1,dateNum) < new Date()) {
					calendar += 'now';
				}
			}
			calendar += '"><div data-date="' + currentYear + '-' + appendzero(parseInt(currentMonth)) + '-' + appendzero(dateNum) + '">';
			calendar += dateNum + '<span class="cont"></span></div></td>';
		}
		calendar += '</tr>';
	}

	$(obj).find("tbody")
		.data("date",nowDate)
		.html(calendar);
	$(obj).find("td.able").click(function() {
		if($(obj).data("option") && $(obj).data("option").indexOf("multiple") > -1) {
			$(this).toggleClass("on");
		}
		else {
			$(obj).find("td.on").removeClass("on");
			$(this).addClass("on");
		}
		$(obj).data("date",$(this).find("div").data("date"));
		if(typeof fn == 'function') {
			fn($(this).find("div").data("date"),"date");
		}
	});

	if(typeof fn == 'function') {
		fn(nowDate,"month");
	}
}

function set_list_figure(obj,row,url) {
	let self_target = true,confirm = '';
	if(url.indexOf("partner") == 0) self_target = false;

	if(row.confirm) {
		confirm = `<span class="label confirm-status ${row.confirm}"></span>`;
	}

	$(obj).append(`
		<li class="${($.inArray(url,['performance','audition']) > -1 && new Date(row.date[1]) < new Date())?'past':''}"><a href="/${url}/${row.no}" ${(self_target)?'target="_blank"':''} data-no="${row.no}">
			<span class="image"><span class="cont" style="background-image:url('${row.images.list}')"></span>${confirm}<span class="label ${row.reservation_type}"></span></span>
			<span class="info">
				<strong><span class="scroll">${row.title}</span></strong>
				<span class="category">${row.category}</span>
				<span class="date">${(row.date[0]!=row.date[1])?row.date[0] + ' ~ ' + row.date[1]:row.date[0]}</span>
				<span class="summary">${row.summary}</span>
				<span class="price"><big>${number_format(row.price)}</big>원/시간</span>
				<ul class="count">
					<li><i class="xi-user"></i>최대 ${row.person_max}인</li>
					<li><i class="xi-comment"></i>${row.review_count}</li>
					<li><i class="xi-heart"></i>${row.favorite_count}</li>
				</ul>
			</span>	
		</a><button type="button" class="favorite ${(row.is_favorite)?'on':''}" data-no="${row.no}"><i class="xi-heart-o"></i></button></li>
	`);
}

function set_image_filelist(file,obj) {
	let ext,
		feature = $(obj).data("feature"),
		is_list = '';
		if(typeof feature != 'undefined' && feature.indexOf("대표") > -1) {
			is_list = `<button type="button" class="is-list">대표</button>`;
		}

	if($(obj).attr("multiple") == "multiple"){
		file.reverse();
		for(let i=0;i<file.length;i++) {
			if(typeof file[i].no == 'undefined') continue;
			is_image = false;
			is_video = false;
			if('url' in file[i] && file[i].url.indexOf(".") > -1) {
				ext = file[i].url.split('.').pop().toLowerCase();
			}
			else {
				ext = '';
			}
			if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) > -1) is_image = true;
			else if($.inArray(ext, ['mov', 'mp4', 'avi', 'mpg']) > -1) is_video = true;
			$(obj).parent().parent().prepend(`
				<div class="item no-icon init" 
					${(is_image)?'style="background-image:url(\'' + file[i].url + '\')" data-type="image"':''}
					${(is_video)?'data-type="video"':''}>
					<input type="hidden" name="${$(obj).attr("name") + "_no"}" value="${file[i].no}">
					<input type="hidden" name="${$(obj).attr("name") + "_url"}" value="${file[i].url}">
					${is_list}
					<div class="tools">
						<a class="delete"><i class="xi-trash"></i></a>
						<a class="view"><i class="xi-magnifier-expand"></i></a>
					</div>
				</div>
			`);
			if(file[i].no == 0 || ('is_list' in file[i] && file[i].is_list)) {
				$(obj).parent().parent().find(".item").first().find("button.is-list").addClass("on");
			}
		}
		$(obj).parent().parent().find(".item.init").each(function() {
			$(this).removeClass("init");

			/** 대표 이미지 지정 **/
			$(this).find("button.is-list").click(function() {
				let formname = $(obj).attr("name"),
					pobj = $(this).parent().parent();

				$(pobj).find("button.is-list.on").removeClass("on");
				$(this).addClass("on");
				if($(pobj).find(`input[name="${formname}_list_no"]`).length == 0) {
					$(pobj).append(`<input type="hidden" name="${formname}_list_no" value="">`);
				}
				$(pobj).find(`input[name="${formname}_list_no"]`).val($(this).parent().find(`input[name="${formname}_no"]`).val());
				$(pobj).find(`input[name="${formname}_list_cnt"]`).val("");

				if(window.file_list) {
					for(let i=0;i<window.file_list.length;i++) {
						if(window.file_list[i].formname == formname) {
							window.file_list[i].is_list = "n";
						}
					}
				}
			});

			/** 삭제 **/
			$(this).find(".tools > .delete").click(function() {
				let pobj = $(this).parent().parent();
				confirm("해당 파일을 삭제할까요?",function() {
					if($(pobj).find("button.is-list").hasClass("on")) {
						$(pobj).parent().parent().find(".item").first().find("button.is-list").click();
					}
					$(pobj).remove();
				});
			});

			/** 크게보기 **/
			$(this).find(".tools > .view").click(function() {
				let img = $(this).parent().parent().css("background-image").replace(/^url\(['"](.+)['"]\)/, '$1');

				$("body").addClass("on-modal")
					.append(`
						<section class="modal">
							<section class="body preview">
								<header>
									<button type="button" class="close"></button>
								</header>
								<article><img src="${img}"></article>
							</section>
						</section>
					`);
				$("body > section.modal").last().find("button.cancel,header > button.close").click(function() {
					$("body > section.modal").last().remove();
					if($("body > .modal").length == 0) {
						$("body").removeClass("on-modal");
					}
				});

			});
		});
		if($(obj).parent().parent().find("button.is-list.on").length == 0) {
			$(obj).parent().parent().find(".item").first().find("button.is-list").click();
		}
	}
	else {
		ext = file[0].url.split('.').pop().toLowerCase();
		if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
			$(obj).parent().find("img").attr("src",file[0].url);
		}
	}
}

function set_ui(obj) {
	$(obj).find("table.grid input").focusout(function() {
		$(this).parent().parent().parent().parent().find(".focus").removeClass("focus");
	});
	$(obj).find("table.grid input").focus(function() {
		let col = $(this).parent().index(),row = $(this).parent().parent().index();
		$(this).parent().parent().parent().parent().parent().find("tr").each(function() {
			$(this).find("td,th").eq(col).addClass("focus");
		});
		$(this).parent().parent().addClass("focus");
	});
	$(obj).find("table input[type='file']").change(function() {
		let val = ($(this).val()).split("\\");
		$(this).next().val(val[val.length-1]);
	});
	$(obj).find("input").each(function() {
		if(typeof $(this).data("mask") != 'undefined') {
			$(this).mask($(this).data("mask"));
		}
		else {
			switch($(this).attr("type")) {
				case "tel":
					$(this).mask("000-0000-0000");
				break;
			}
		}
	});
	$(obj).find("form").submit(function() {
		$(this).find(".textarea").each(function() {
			$(this).parent().find("input").val($(this).html());
		});

		tinymce.triggerSave();
		return false;
	});

}

function pay(opt) {
	switch(opt.pay_method) {
		case "카카오페이":
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0062', null);
			// alert("서버 점검으로 현재 카카오페이 이용이 어렵습니다. 빠른 시일 내에 조치하겠습니다.");
			return;
		break;
	}

	let f;
	if('obj' in opt && typeof opt.obj == 'object') {
		f = opt.obj;
		delete opt['obj'];
	}

	$.ajax({
		url: config.api + "pay/inicis",
		data: opt,
		success: function(d) {
			if(d.code == 200) {
				$.ajax({
					url: d.url.script,
					dataType: 'script',
					success: function(d2) {
						let merchantData = [];
						if(typeof f == 'object') {
							merchantData = $(f).serializeArray(),merchant = new Object();
							merchantData.forEach(function(row) {
								merchant[row.name] = row.value;
							});
						}

						let pay_method = 'Card';
						switch(opt.pay_method) {
							case '신용카드':
								pay_method = 'Card';
							break;

							case '계좌이체':
								pay_method = 'Vbank';
							break;
						}

						$("#SendPayForm_mobile").remove();
						$("body").append(`
							<form id="SendPayForm_mobile" method="POST" action="https://mobile.inicis.com/smart/payment/" accept-charset='EUC-KR'>
								<input type="hidden" name="P_INI_PAYMENT" value="${pay_method.toUpperCase()}" >
								<input type="hidden" name="P_MID" value="${d.mid}">
								<input type="hidden" name="P_OID" value="${d.order_number}">
								<input type="hidden" name="P_NOTI">
								<input type="hidden" name="P_GOODS" value="${opt.goods_name + ((d.test)?' (테스트)':'')}">
								<input type="hidden" name="P_AMT" value="${d.price}">
								<input type="hidden" name="P_UNAME" value="${d.member.name}">
								<input type="hidden" name="P_MOBILE" value="${d.member.tel}">
								<input type="hidden" name="P_EMAIL" value="${d.member.email}">
								<input type="hidden" name="P_NEXT_URL" value="${(opt.return_url_mobile)?opt.return_url_mobile:d.url.mobile.return}">
								<input type="hidden" name="P_MERCHANT" value="${JSON.stringify(merchant)}">
							</form>
						`);

						/** 이니시스 폼 데이터 업데이트 **/
						$("#SendPayForm_id").remove();
						$("body").append(`
							<form id="SendPayForm_id" method="POST">
								<input type="hidden" name="version" value="1.0">
								<input type="hidden" name="mid" value="${d.mid}">
								<input type="hidden" name="goodname" value="${opt.goods_name + ((d.test)?' (테스트)':'')}">
								<input type="hidden" name="oid" value="${d.order_number}">
								<input type="hidden" name="price" value="${d.price}">
								<input type="hidden" name="currency" value="WON">
								<input type="hidden" name="buyername" value="${d.member.name}">
								<input type="hidden" name="buyertel" value="${d.member.tel}">
								<input type="hidden" name="buyeremail" value="${d.member.email}">
								<input type="hidden" name="timestamp" value="${d.timestamp}">
								<input type="hidden" name="signature" value="${d.signature}">
								<input type="hidden" name="returnUrl" value="${(opt.return_url)?opt.return_url:d.url.return}">
								<input type="hidden" name="closeUrl" value="${d.url.close}">
								<input type="hidden" name="mKey" value="${d.mkey}">
								<input type="hidden" name="merchantData" value="${escape(JSON.stringify(merchant))}">
								<input type="hidden" name="gopaymethod" value="${pay_method}">
							</form>
						`);
						INIStdPay.pay('SendPayForm_id');
					}
				});
			}
			else {
				notiModal(d.msg);
			}
		}
	});
}

function pay_ok() {
	notiModal('pay_ok');
}

function get_script(module) {
	$.ajax({
		url: `${config.script}${module}.js`,
		async: true,
		dataType: "script"
	});
}