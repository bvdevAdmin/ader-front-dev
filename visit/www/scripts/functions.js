function getSelectionParentElement() {
    var parentEl = null, sel;
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.rangeCount) {
            parentEl = sel.getRangeAt(0).commonAncestorContainer;
            if (parentEl.nodeType != 1) {
                parentEl = parentEl.parentNode;
            }
        }
    } else if ( (sel = document.selection) && sel.type != "Control") {
        parentEl = sel.createRange().parentElement();
    }
    return parentEl;
}

function setStatusCircleProgress(obj,n1,n2) {
	if(typeof n2 == "undefined") n2 = n1;

	//$(obj).attr("class","");
	$(obj).find(".percentage").animateNumber({
		number: n2,
		numberStep: $.animateNumber.numberStepFactories.separator(',')
	},{duration:1500});
	if(n2 > 0) {
		$(obj).find(".progress").css({"strokeDasharray":Math.round((n2/n1)*100) + " 100"});
		$(obj).attr("class","on");
	}
	else {
		$(obj).find(".progress").css({"strokeDasharray":"0 100"});
	}
}

function minute2hour(min) {
	var str = '';
	if(isNaN(min)) {
		return min;
	}
	else {
		if(min >= 60) {
			if(min >= 1440) str = parseInt(min/1440) + "일 ";
			if(parseInt(min/60)%24 > 0) str += parseInt(min/60)%24 + "시간 ";
			if(min%60 > 0) str += " " + min%60 + "분";
		}
		else if(min > 0) {
			str = min + "분";
		}
	}
	return str;
}

function getCurrentRotation( obj ) {
  var el = document.getElementById(elid);
  var st = window.getComputedStyle(el, null);
  var tr = st.getPropertyValue("-webkit-transform") ||
       st.getPropertyValue("-moz-transform") ||
       st.getPropertyValue("-ms-transform") ||
       st.getPropertyValue("-o-transform") ||
       st.getPropertyValue("transform") ||
       "fail...";

  if( tr !== "none") {
    var values = tr.split('(')[1];
      values = values.split(')')[0];
      values = values.split(',');
    var a = values[0];
    var b = values[1];
    var c = values[2];
    var d = values[3];

    var scale = Math.sqrt(a*a + b*b);

    // First option, don't check for negative result
    // Second, check for the negative result
    /**/
    var radians = Math.atan2(b, a);
    var angle = Math.round( radians * (180/Math.PI));
    /*/
    var radians = Math.atan2(b, a);
    if ( radians < 0 ) {
      radians += (2 * Math.PI);
    }
    var angle = Math.round( radians * (180/Math.PI));
    /**/
    
  } else {
    var angle = 0;
  }

	return angle;
}

function getDate2Str(d) {
	d = d.split("-");

	return d[0] + "년 " + parseInt(d[1]) + "월 " + parseInt(d[2]) + "일";
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

function is_tel(hp){	
	if(hp == ""){
		return true;	
	}	
	var phoneRule = /^(01[016789]{1})[0-9]{3,4}[0-9]{4}$/;	
	return phoneRule.test(hp);
}


function getTime2Str(t,showDay) {
	var result = '',remine_time = parseInt(t)/60;
	if(typeof showDay == 'string') {
		switch(showDay.toLowerCase()) {
			case 'studyroom':
			case 'studyroom_single':
			case 'studyroom_time_ticket':
				showDay = false;
				break;
			default:
				showDay = true;
		}
	}
	else if(typeof showDay != 'boolean') showDay = false;

	if(remine_time/24 > 0 && showDay) {
		if(Math.floor(remine_time/24) > 0) result = Math.floor(remine_time/24) + "일";
		if(parseInt(remine_time%24) > 0) result += " " + parseInt(remine_time%24) + "시간";
	}
	else if(parseInt(remine_time) > 0) {
		result = parseInt(remine_time) + "시간";
	}
	if(parseInt(t%60) > 0) {
		if(result != '') result += ' ';
		result +=  parseInt(t%60) + "분";
	}

	return (t > 0)?result:'';
}

function confirmDeleteDataRow(obj,ok,cancel) {
	obj = $(obj).parent().parent();

	var title = $(obj).data("title");
	var no = $(obj).data("no");

	$(obj).siblings().addClass("blur");
	$(obj).addClass("on-confirm");

	if(typeof cancel != "function") {
		cancel = function() {
			$(obj).siblings().removeClass("blur");
			$(obj).removeClass("on-confirm");
		};
	}

	confirm("<h1><i class='xi-trash-o color-red'></i>" + title + " 삭제</h1>삭제 후 복구는 불가능하니 신중히 결정해 주세요. 삭제할까요?",ok,cancel);
}

function list(query,f) {
	if(typeof f == "undefined") {
		//f = $("section:not(.modal)").find("form").last();
		f = $("form").last();
	}
	if(typeof query == "object") {
		f = query;
	}
	else if(typeof query == "string") {
		var row = query.split("&");
		for(var i=0;i<row.length;i++) {
			var data = row[i].split("=");
			if($(f).find("input[name='" + data[0] + "']").length > 0) {
				$(f).find("input[name='" + data[0] + "']").val(data[1]);
			}
			else {
				$(f).prepend('<input type="hidden" name="' + data[0] + '" value="' + data[1] + '">');
			}
		}
	}
	$(f).submit();
}

function dialog_config(obj) {
	var w = $(obj).width();
	var h = $(obj).height();
	var id = $(obj).attr("id");
	var cat = $(obj).data("cat");
	var num = $(obj).data("num");
	var name = $(obj).data("name");
	var type = $(obj).data("type");
	var map_type = $(obj).data("map_type");
	var status = $(obj).data("status");
	var pos_y = parseInt($(obj).css("top").replace("px",""));
	var pos_x = parseInt($(obj).css("left").replace("px",""));

	var rt = $(obj).css("transform").replace("matrix(","").replace(")","");
	rt = rt.split(",");
	var radians = Math.atan2(rt[1], rt[0]);
	if(radians < 0) {
		radians += (2 * Math.PI);
	}
	var r = Math.round(radians * (180/Math.PI));

	$("#dialog-config > div").eq(0).removeClass("hidden");

	$("#layout-wyswyg figure").removeClass("selected");
	$(obj).addClass("selected");
	$("#layout-wyswyg").addClass("selected");

	/* if($("input[name='layout']:checked").val() == 'locker') {
		$("#dialog-config").find("input[type='radio']").prop("disabled",true).prop("checked",false);
	} */	// 210208 khw
	if($("#select-layout-editor > li.on").data("val") == 'locker') { 
		$("#dialog-config").find("select[name='cat']").prop("disabled",true);
		$("#dialog-config").find("select[name='map_type']").prop("disabled",true);
	} else if($("#select-layout-editor > li.on").data("val") == 'seatmap') {
		$("#dialog-config").find("select[name='cat']").prop("disabled",false);
		$("#dialog-config").find("select[name='map_type']").prop("disabled",false);
	}

	$("#dialog-config").find("input[name='type_no[]']").prop("checked",false);
	if(typeof type != "undefined") {
		if(isNaN(type)) {
			var type_no = type.split(",");
		}
		else {
			var type_no = [type];
		}
		for(var i=0;i<type_no.length;i++) {
			$("#dialog-config").find("input[name='type_no[]'][value='" + type_no[i] + "']").prop("checked",true);
		}
	}
	if(name == '') name = num;

	$("#dialog-config").find("input[name=num]").val(num).prop("disabled",false);
	$("#dialog-config").find("input[name=y]").val(pos_y).prop("disabled",false);
	$("#dialog-config").find("input[name=x]").val(pos_x).prop("disabled",false);
	$("#dialog-config").find("input[name=w]").val(w);
	$("#dialog-config").find("input[name=h]").val(h);
	$("#dialog-config").find("input[name=r]").val(r);
	$("#dialog-config").find("input[name=name]").val(name);
	$("#dialog-config").find("input[name=cat][value='" + cat + "']").prop("checked",true);
	$("#dialog-config").find("select[name=cat] > option[value='" + cat + "']").prop("selected",true);
	$("#dialog-config").find("select[name=map_type] > option[value='" + map_type + "']").prop("selected",true);

	if(status != 'n') $("#dialog-config").find("input[name=status]").prop("checked",true);
	else $("#dialog-config").find("input[name=status]").prop("checked",false);
	$("#dialog-config")
		.data("id",id)
		.css({"top":(pos_y-120) + "px","left":((pos_x+(w/2))-140) + "px"})
		.addClass("on");
}


function showCalendar(date,fn) {
	if( typeof( date ) !== 'undefined' ) {
		date = date.split('-');
		date[1] = date[1] - 1;
		date = new Date(date[0], date[1], date[2]);
	} else {
		var date = new Date();
	}
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

	$("#calendar-year").html(parseInt(currentYear));
	$("#calendar-month").html(parseInt(currentMonth));
	$("#calendar-prev-btn").data("date",prevDate);
	$("#calendar-next-btn").data("date",nextDate);

	var dateNum =1-currentDay;

	for(var i = 0; i < week; i++) {
		calendar += '<tr>';
		for(var j = 0; j < 7; j++, dateNum++) {
			calendar += '<td id="c' + currentYear + '-' + appendzero(parseInt(currentMonth)) + '-' + appendzero(dateNum) + '" class="';
			if( dateNum < 1 || dateNum > currentLastDate ) {
				calendar += '"></td>';
				continue;
			}
			/*
			if(log_list[currentYear][parseInt(currentMonth)][dateNum]) {
				calendar += 'able ';
				if(new Date(currentYear,currentMonth-1,dateNum) < new Date()) {
					//calendar += 'now';
				}
			}
			*/
			calendar += '"><div data-date="' + currentYear + '-' + appendzero(parseInt(currentMonth)) + '-' + appendzero(dateNum) + '">';
			calendar += dateNum + '<span></span></div></td>';
		}
		calendar += '</tr>';
	}

	$("#calendar")
		.data("date",nowDate)
		.html(calendar);


	/** 상세 가져오기 **/
	if(typeof fn == "function") {
		$("#calendar td.able > div").click(function() {
			fn(this);
		});
	}
	$("#c" + nowDate + " > div").click();
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
		if($(this).hasClass("able") == false) return;
		if($(obj).data("option") && $(obj).data("option").indexOf("multiple") > -1) {
			$(this).toggleClass("on");
		}
		else {
			$(obj).find("td.on").removeClass("on");
			$(this).addClass("on");
		}
		$(obj).data("date",$(this).find("div").data("date"));
		if(typeof fn == 'function') {
			fn.call(obj, $(this).find("div").data("date"),"date");
		}
	});

	if(typeof fn == 'function') {
		fn.call(obj, nowDate,"month");
	}
}

function setUI(obj) {
	if(typeof obj == "undefined") obj = $("html");

	//$(obj).find("input[type='number']").attr("pattern","\d*");
	$(obj).find("input[type='number']").attr("type","text");

	/** 02. 입력 형식 마스크 **/
	$(obj).find("input.phone").mask("000-0000-0000");
	$(obj).find("input.zipcode").mask("00000");
	$(obj).find("input.number").mask("0000000000");
	$(obj).find("input.number-2").mask("00");
	$(obj).find("input.number-3").mask("000");
	$(obj).find("input.number-4").mask("0000");
	$(obj).find("input.number-5").mask("00000");
	$(obj).find("input.number-6").mask("000000");
	$(obj).find("input.number-7").mask("0000000");
	$(obj).find("input.number-8").mask("00000000");
	$(obj).find("input.number-9").mask("000000000");

	/** 03. 포스기 목록 **/
	if($(obj).find("ul#select-device").length && config.kiosk != null) {
		config.kiosk.forEach(row => {
			$(obj).find("ul#select-device").append(`<li data-no="${row.no}">${row.name}</li>`);
		});
	}

	/** 04. 분류 목록 **/
	if($(obj).find("dl#select-category").length && typeof config.goods == 'object') {
		Object.keys(config.goods).forEach(key => {
			$(obj).find("dl#select-category").append(`<dd data-val="${key}">${config.goods[key]}</dd>`);
		});
	}

	// 06. 탭 컨텐츠 동작 설정
	$(obj).find("ul.tab > li").click(function() {
		$(this).siblings().removeClass("on");
		$(this).addClass("on");
	});
	$(obj).find("ul.tab > li").eq(0).click();

	// 07. 테이블 체크박스 설정
	$(obj).find("table th > input[type=checkbox]").click(function() {
		var checked = $(this).prop("checked");
		$(this).parent().parent().parent().parent().find("tr > td:first-child > input[type=checkbox]").prop("checked",checked);
	});


	// 08. 기간 설정 인풋 박스
	if($(obj).find("input[name='to_date']").length) {
		$(obj).find("input[name='to_date']")
			.parent().append(`
				<div class="select-popup">
					<label><input type="radio" name="dateterm" value="당일"><span>당일</span></label>
					<label><input type="radio" name="dateterm" value="전일"><span>전일</span></label>
					<label><input type="radio" name="dateterm" value="1주일"><span>1주일</span></label>
					<label><input type="radio" name="dateterm" value="당월"><span>당월</span></label>
					<label><input type="radio" name="dateterm" value="전월"><span>전월</span></label>
					<label><input type="radio" name="dateterm" value="전분기"><span>전분기</span></label>
					<label><input type="radio" name="dateterm" value="금년"><span>금년</span></label>
					<label><input type="radio" name="dateterm" value="전년"><span>전년</span></label>
				</div>
			`);

		$(obj).find("input[name='to_date'],input[name='from_date']").change(function() {
			$("input[name='dateterm']").prop("checked",false);
		});

		$(obj).find("input[name='dateterm']").bind('click',function() {
			var date = new Date();
			var val = $(this).val();

			$(obj).find("input[name='to_date']").val(date.getFullYear() + "-" + appendzero(date.getMonth()+1) + "-" + appendzero(date.getDate()));

			switch(val) {
				case "당일":
					break;
				case "전일":
					date.setDate(date.getDate()-1);
					$(obj).find("input[name='to_date']").val(date.getFullYear() + "-" + appendzero(date.getMonth()+1) + "-" + appendzero(date.getDate()));
					break;
				case "1주일":
					date.setDate(date.getDate()-7);
					break;
				case "당월":
					date.setDate(1);
					break;
				case "전월":
					var lastDate = (new Date(date.getFullYear(), date.getMonth(), 0)).getDate();
					date.setMonth(date.getMonth()-1);
					$(obj).find("input[name='to_date']").val(date.getFullYear() + "-" + appendzero(date.getMonth()+1) + "-" + appendzero(lastDate));
					date.setDate(1);
				break;
				case "전분기":
					if(date.getMonth()+1 < 7) {
						date.setYear(date.getFullYear()-1);
						$(obj).find("input[name='to_date']").val(date.getFullYear() + "-12-31");
						date.setMonth(5);
					}
					else {
						$(obj).find("input[name='to_date']").val(date.getFullYear() + "-06-30");
						date.setMonth(0);
					}
					date.setDate(1);
				break;
				case "금년":
					date.setMonth(0);
					date.setDate(1);
				break;
				case "전년":
					date.setYear(date.getFullYear()-1);
					$(obj).find("input[name='to_date']").val(date.getFullYear() + "-12-31");
					date.setMonth(0);
					date.setDate(1);
				break;
			}

			$(obj).find("input[name='from_date']").val(date.getFullYear() + "-" + appendzero(date.getMonth()+1) + "-" + appendzero(date.getDate()));
		});
	}


	/** 10. 순서 드래그 **/
	$(obj).find("table.list > tbody" ).sortable({
		connectWith: "table.list > tbody",
		items: "tr:not(.disabled)",
		cancel: ".disabled",
		handle: "td.seq",
		placeholder: "dragable-placeholder",
		containment: "table.list",
		scroll: false,
		update: function (event, ui) {
			let idxs = [];
			$(this).find("tr").each(function() {
				if(isNaN($(this).data("no")) == false) {
					idxs.push($(this).data("no"));
				}
			});

			// 정리된 순서를 db에 업데이트
			$.ajax({
				url: config.api + $(this).data("sortapi"),
				data: { seq : idxs },
				success: function(d) {
					if(d.code == 200) {
						toast("성공적으로 순서를 저장했습니다");
					}
					else {
						alert(d.msg);
					}
				}
			});
		}
	});
}

function setImagePreview(file,obj) {
	var ext,
		html,
		modal,
		duration = 3.5,
		is_image = false;

	if(typeof file == "string") {
		ext = file.split('.').pop().toLowerCase(); //확장자
	}
	else {
		ext = file.name.split('.').pop().toLowerCase(); //확장자
	}

	if(typeof $(obj).data("duration-inp") != "undefined") {
		duration = eval($(obj).data("duration-inp")).val();
	}

		//배열에 추출한 확장자가 존재하는지 체크
	if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
		if($(obj).hasClass("image") == true) {
			$(this).wrap('<form>').closest('form').get(0).reset(); 
			$(this).unwrap();
			alert('이미지 파일이 아닙니다. gif, png, jpg, jpeg만 업로드 가능합니다.');
			return false;
		}
	} else {
		is_image = true;
		if(typeof file == "string") {
			blobURL = file;
		}
		else {
			blobURL = window.URL.createObjectURL(file);
		}
	}

	if(is_image) {
		html =  '<div class="item" style="background-image:url(\'' + blobURL + '\')" data-duration="' + duration + '">'
		     +  '	<div class="tools">'
		     +  '		<button type="button" class="delete"></button>'
		     +  '		<button type="button" class="view"></button>'
		     +  '		<button type="button" class="config"></button>'
		     +  '	</div>'
			 +  (($(obj).data("sort"))?'<button type="button" class="move"></button>':'')
		     +  '	<div class="detail">'
		     +  '		<div class="duration"></div>'
		     +  '		<label class="switch">'
		     +  '			<input type="checkbox" name="intro_banner" value="y" checked="">'
		     +  '			<span class="bg"></span>'
		     +  '			<span class="trigger"></span>'
		     +  '		</label>'
		     +  '	</div>';
			 +  '</div>';
	}
	else {
		html  = '<div class="item">';
		html += '	<div class="tools">';
		html += '		<a onclick="image_delete($(this).parent().parent());"><i class="xi-trash"></i></a>';
		html += '	</div>';
		html += '	<div class="filename '+ ext + '">' + file.name + '</div>';
		if(is_sort) html += '	<a><i class="xi-arrows"></i></a>';
		html += '</div>';
	}
	$(obj).append(html);
	$(obj).find(".item").last().find("button.delete").click(function() {
		$(this).parent().parent().remove();
	});
	$(obj).find(".item").last().find("button.view").click(function() {
		$("body")
			.addClass("on-modal")
			.append(
				'<section class="modal">' +
				'	<article class="preview">' +
				'		<img src="' + $(this).parent().parent().css("backgroundImage").replace(/^url\(['"](.+)['"]\)/, '$1') + '">' +
				'	</article>' +
				'</section>'
			);

		setTimeout(function() {
			$("section.modal").last().addClass("on").click(function() {
				$("body").removeClass("on-modal");
				$(this).remove();
			});
		},50);
	});
	$(obj).find(".item").last().find("button.config").click(function() {
		var html,
			duration = $(this).parent().parent().data("duration"),
			top = $(this).offset().top-140,
			left = $(this).offset().left-200;

		$(this).parent().parent().addClass("selected");

		html = '<div class="modal-dialog on">'
		     + '	<div class="row padding-5">'
		     + '		<div class="col-2">'
		     + '			<div class="form-inline no-title">'
		     + '				<div class="inp-row">'
		     + '					<span><i class="xi-timer-o"></i></span>'
		     + '					<input type="text" name="duration_s" value="' + duration + '" class="text-right">'
		     + '					<span>초</span>'
		     + '				</div>'
		     + '			</div>'
		     + '		</div>'
		     + '	</div>'
		     + '	<div class="buttons footer">'
		     + '		<button type="button" class="ok"><i class="xi-check"></i>APPLY</button>'
		     + '		<button type="button" class="cancel"><i class="xi-close"></i>CLOSE</button>'
		     + '	</div>'
		     + '</div>';
		$(".modal-dialog").remove();
		$(html).css({"top":top + "px","left":left+"px"}).appendTo("body");
		$(".modal-dialog").find("button.ok").click(function() {
			var duration = $(".modal-dialog").find("input[name='duration_s']").val();
			$(obj).find(".item.selected")
				.data("duration",duration)
				.removeClass("selected");
			$(this).parent().parent().remove();
		});
		$(".modal-dialog").find("button.cancel").click(function() {
			$(obj).find(".item.selected").removeClass("selected");
			$(this).parent().parent().remove();
		});
	});
	$(obj).find(".item").last().mouseleave(function() {
		if($(".modal-dialog").length == 0) {
			$(this).removeClass("selected");
		}
	});


	if($(obj).data("sort")==true) {
		$(obj).sortable({
			connectWith: $(obj),
			containment: $(obj),
			items: ".item:not(.disabled)",
			cancel: ".disabled",
			handle: "button.move",
			placeholder: "item",
			update: function (event, ui) {

			}
		});
	}
}


function previewPlay(obj) {
	var objPreview = $(obj).find(".preview");

	setTimeout(function() {
		var duration,image,playtime,
			index = $(obj).find(".gallery > .item.play").index();

		if($(obj).find("button.pause").length == 0) return false;

		if($(obj).find(".gallery > .item.play").length == 0) {
			$(obj).find(".gallery > .item").eq(0).addClass("play").data("playtime",0);
			index = -1;
		}
		console.log($(obj).find(".gallery > .item").length + "," + index);

		if($(obj).find(".gallery > .item").length > index) {
			if($(obj).hasClass("play") == false) {
				$(obj).addClass("play");
			}
			duration = parseFloat($(obj).find(".gallery > .item.play").data("duration"))-0.1;
			playtime = parseFloat($(obj).find(".gallery > .item.play").data("playtime"));
			if(isNaN(playtime)) {
				$(obj).find(".gallery > .item.play").data("playtime","0")
				playtime = 0;
			}

			if(duration <= playtime) {
				if($(obj).find(".gallery > .item").length == index+1) {
					$(objPreview).empty();
					$(obj).removeClass("play");
					$(obj).find("button.pause").removeClass("pause");
					$(obj).find(".gallery > .item.play").removeClass("play");
					toast("재생이 종료되었습니다.");
				}
				else {
					$(obj).find(".gallery > .item.play").data("playtime",0);
					previewPlay(obj);
				}
			}
			else {
				if(playtime == 0 && $(obj).find(".gallery > .item").length > index+1) {
					image = $(obj).find(".gallery > .item").eq(index+1).css("backgroundImage").replace(/^url\(['"](.+)['"]\)/, '$1');
					$(obj).find(".gallery > .item").eq(index).removeClass("play");
					$(obj).find(".gallery > .item").eq(index+1).addClass("play");
					$(objPreview)
						.html('<img src="' + image + '">');
				}
				playtime += 0.1;
				$(obj).find(".gallery > .item.play").data("playtime",playtime)
				previewPlay(obj);
			}

		}
		else {
			$(objPreview).empty();
			$(obj).removeClass("play");
			$(obj).find("button.pause").removeClass("pause");
			$(obj).find(".gallery > .item.play").removeClass("play");
			toast("재생이 종료되었습니다.");
		}
	},100);
}


function get_script(module) {
	$.ajax({
		url: `${config.script}${module}.js`,
		async: true,
		dataType: "script"
	});
}


function get_contents(obj,data) {
	$(obj).submit(function() {
		let page = 1;
		if($(this).find("input[name='page']").length > 0 && isNaN($(this).find("input[name='page']").val()) == false) {
			page = $(this).find("input[name='page']").val();
		}
		else {
			$(this).prepend(`<input type="hidden" name="page" value="${page}">`);
		}
		if(typeof data != 'object') {
			if(isNaN(data) == false) page = data;
			data = {
				param : $(this).serializeObject()
			};
			data.param.page = page;
		}
		$(this).find("input[name='page']").val(page);
		if('param' in data == false) data.param = { page : page };
		if('page' in data == false) data.param.page = page;
		if('html' in data == false) return false;
		if('pageObj' in data == false) data.pageObj = $("#paging");
		let param = $(this).serializeObject();
		for(key in param) {
			data.param[key] = param[key];
		}

		$.ajax({
			url: config.api + $(this).attr("action"),
			data: data.param,
			success: function(d) {

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


				if(d.code == 200) {
					if('pagenum' in d == false) d.pagenum = 20;
					if(d.data && d.total > 0) {
						num = d.total - ((d.page-1)*d.pagenum);
						d.data.forEach(function(row) {
							row.num = num--;
						});
						data.html(d.data);
					}
					else if('nodata' in data && typeof data.nodata == 'function') {
						data.nodata(d);
					}
					if('complete' in data && typeof data.complete == 'function') {
						data.complete(d);
					}

					if('page' in d) {
						paging({
							total : d.total,
							el : data.pageObj,
							page : d.page,
							row : ('pagenum' in d)?d.pagenum:20,
							show_paging : (is_mobile)?3:9,
							fn : function(page) {
								$(obj).find("input[name='page']").val(page);
								$(obj).submit();
							}
						});
					}
				}
				else {
					alert(d.msg);
				}
			}
		});

		return false;
	}).submit();
};

function get_parent_by_tag(obj,tag) {
	if(obj[0].nodeName == tag.toUpperCase()) {
		return obj;
	}
	else {
		if(obj[0].nodeName == 'BODY') {
			return false;
		}
		else {
			return get_parent_by_tag($(obj).parent(),tag);
		}
	}

}

/****************************************************************************/
/* 콘솔 로그 재정의
/****************************************************************************/
window._console = window.console;
window.console = {
	/*
		모든 로그 서버로 전송 (형식, 데이터, 옵션 값들)
	*/
	_tofile : function(type,msg, ... args) {
		const date = new Date(),
			now_date = date.getFullYear() + "-" + (date.getMonth() + 1).toString().fillZero(2) + "-" + (date.getDate()).toString().fillZero(2),
			now_time = (date.getHours()).toString().fillZero(2) + ":" + (date.getMinutes()).toString().fillZero(2) + ":" + (date.getSeconds()).toString().fillZero(2);

		if(typeof args == 'object' && args.length > 0) {
			if(typeof args[0][0] == 'string') {
				msg = `[${args[0][0]}] ${msg}`;
			}
			else if(typeof args[0][0] == 'object') {
				if('_attr' in args[0][0]) {
					msg = `[${((args[0][0]._attr.category.name)?args[0][0]._attr.category.name:args[0][0]._attr.category.keyword)}/${args[0][0]._attr.port}] [${args[0][0]._attr.name}] ${msg}`;
				}
			}
		}


		/** 모니터링 서버 전송 **/
		if(window.watchdog != null && typeof window.watchdog == 'object' && watchdog.readyState != null && watchdog.readyState == 1) {
			watchdog.send(JSON.stringify({
				from : 'control',
				type : 'debug',
				store_no : config.store.no,
				id : config.user.id,
				log_type : type,
				msg : (typeof msg != 'string')?msg.toString():msg,
				data : args
			}));
		}

		let is_object = null;
		if(typeof msg != 'string') is_object = msg;
		msg = (`[${now_date} ${now_time}] ${(typeof msg == 'string')?msg:JSON.stringify(msg)}`).strip_tags();

		/** 콘솔창에 표시 **/
		if(args != null && args.length > 1 && $.inArray(type,['warn']) == -1) {
			_console[type](msg,args[0]);
		}
		else {
			if(type == 'warn') _console.log(msg);
			if(is_object) _console[type](msg,is_object);
			else _console[type](msg);
		}
	},

	/* assertion 조건이 거짓일 경우 로그 */
	assert : function(assertion, ... d) { 
		if(typeof assertion == 'boolean' && assertion == false) {
			this._tofile('log', d); // 파일 기록
			_console.assert(assertion, d); // 콘솔 표시
		}
	},
	/* 로그 화면 초기화 */
	clear : function() {
		_console.clear();
	},
	
	context : function(d, ... args) {
		_console.context(d, ... args);
	},

	/* 특정값 카운팅 */
	count : function(d) {
		this._tofile('count', d); // 파일 기록
		_console.count(d);
	},
	/* 카운터 초기화 */
	countReset : function(d) {
		this._tofile('countReset', d); // 파일 기록
		_console.countReset(d);
	},

	/* 디버깅 메시지 */
	debug : function(d, ... args) {
		this._tofile('debug', d, args); // 파일 기록
	},
	dir : function(d, ... args) {
		this._tofile('dir', d, args); // 파일 기록
	},
	dirxml : function(d, ... args) {
		this._tofile('dirxml', d, args); // 파일 기록
	},
	error : function(d, ... args) {
		this._tofile('error', d, args); // 파일 기록
	},
	group : function(d, ... args) {
		this._tofile('group', d, args); // 파일 기록
	},
	groupCollapsed : function(d, ... args) {
		this._tofile('groupCollapsed', d, args); // 파일 기록
	},
	groupEnd : function(d, ... args) {
		this._tofile('groupEnd', d, args); // 파일 기록
	},
	info : function(d, ... args) {
		this._tofile('info', d, args); // 파일 기록
	},

	/* 기본 로깅 함수 */
	log : function(d, ... args) {
		this._tofile('log', d, args); // 파일 기록
	},

	memory : function(d, ... args) {
		_console.memory(d, ... args);
	},
	profile : function(d, ... args) {
		_console.profile(d, ... args);
	},
	profileEnd : function(d, ... args) {
		_console.profileEnd(d, ... args);
	},
	table : function(d, ... args) {
		this._tofile('table', d, args); // 파일 기록
	},
	
	/* 로그 타이머 */
	time : function(d) {
		this._tofile('log', `${d} 기록 시작`); // 파일 기록
		_console.time(d);
	},
	/* 로그 타이머 종료 */
	timeEnd : function(d) {
		this._tofile('log', `${d} 기록 종료`); // 파일 기록
		_console.timeEnd(d);
	},
	/* 로그 타이머 기록 보기 */
	timeLog : function(d) {
		_console.timeLog(d, ... args);
	},
	timeStamp : function(d, ... args) {
		_console.timeStamp(d, args);
	},

	/* 코드 추적 */
	trace : function(d, ... args) {
		_console.trace(d, ... args);
	},

	/* 주의 */
	warn : function(d, ... args) {
		this._tofile('warn', d, args); // 파일 기록
	}
};
window.log = function(msg, ... args) {
	console.log(msg,args);
}

function set_remote(title,fn) {
	let t = [],icon = '';
	if(typeof title == 'string') {
		t = [title,'',title];
	}
	else {
		t = title;
		if(t.length < 3) t[2] = title[0];

		icon = `<i class="${t[1]}"></i>`;
	}

	$("#remocon").append(`<button type="button" data-action="${t[2]}">${icon}${t[0]}</button>`);

	if(typeof fn != 'function') {
		fn = () => {
			confirm(
				`<h1>${icon}${t[0]}</h1>원격 명령을 실행하시겠습니까?`,
				() => {
					let kiosk = config.kiosk[$("#remocon > ul > li.on").index()];
					remote({
						kiosk_no : kiosk.no,
						serial_code : kiosk.device[0].serial_code,
						order : t[2]
					});
				}
			);
		}
	}
	$(`#remocon > button[data-action="${t[2]}"]`).click(fn);
}


/**
	원격 명령 전송
	======
	d.command = {
		type : 명령 형식 ("script", "object"),
		kiosk_no : 키오스크 인덱스키,
		serial_code : 키오스크 시리얼코드,
		device_no 기기 번호(type == object 일 경우 필수),
		order : 원격 내용
				type "script" 인 경우 : 스크립트를 그대로 명령(함수 실행 포함)
				type "object" 인 경우 : json형태 그대로 전달

	}
**/
function remote(obj) {
	if(watchdog == null || watchdog.readyState != 1 || typeof obj != 'object') return;

	watchdog.send(JSON.stringify({
		from : 'control',
		type : 'remote',
		store_no : config.store.no,
		id : config.user.id,
		command : obj
	}));

	console.log(`[원격명령 전송] ${obj.serial_code} ${JSON.stringify(obj.order)}`);
}

function is_tel(hp){	
	if(hp == ""){
		return true;	
	}	
	var phoneRule = /^(01[016789]{1})[0-9]{3,4}[0-9]{4}$/;	
	return phoneRule.test(hp);
}

