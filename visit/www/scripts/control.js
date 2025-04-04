const config = {
	api : "/_api/", // API 경로
	script : "/_script/", // Script 경로
	//watchdog : "ws://localhost:3001", // 모니터링 서버
};
const gnb = {
	kiosk : {
	}, 
	counter : {
	}
};

window.is_mobile = false;
(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) window.is_mobile = true;})(navigator.userAgent||navigator.vendor||window.opera);

$.ajaxSetup({ 
	type : "post",
	dataType: "json",
	error : function() {
		alert(`
			<big><strong>500</strong></big><br>
			서버요청 실패<br>
			[ ${this.url} ]
		`,function() {
			$(".loading").removeClass();
		});
	},
	complete : function() {
	}
});

$(document).ready(function() {
	/*
	setInterval(() => {
		if(typeof config.watchdog == 'String' && (window.watchdog == null || window.watchdog.readyState == 3)) {
			try	{
				window.watchdog = new WebSocket(config.watchdog);

				watchdog.onopen = async () => {
					console.log("웹소켓 대상 연결 완료");
				}

				watchdog.onmessage = async (msg,e) => {
					let d = JSON.parse(msg.data);
				}
			}
			catch(e) {
				console.error("웹소켓 연결에 실패했습니다.");
			}
		}
	},5000);
*/
	//popup();

	get_page = (pathname) => {
		if(window.xhr != null && window.xhr.readyState == 4) {
			window.xhr.abort();
			window.xhr = null;
		}

		if(pathname == '/') pathname += Object.keys(gnb)[0];
		var pathname_arr = pathname.split("/");
		var _m = pathname_arr[1],
			_module = pathname_arr[2];

		$("body > header > h2").html("&nbsp;");
		window.xhr = $.ajax({
			dataType : "text",
			url: `/_pagebody${pathname}`,
			beforeSend: function() {
				if(window.xhr != null) {
					window.xhr.abort();
					window.xhr = null;
				}
				//$("body > section:not(.popup)").addClass("loading");
			},
			complete: function() {
				if(window.xhr != null && 'readyState' in window.xhr && window.xhr.readyState == 4) {
					window.xhr.abort();
					window.xhr = null;
				}
			},
			success: function(d) {
				$("body").attr("class",_m).html(d);
				
				if($("body > section:not(.popup)").hasClass("loading") == true) {
					// 01. 페이지 로딩
					$("body > section:not(.popup)").removeClass("loading").html(d);

					// 02. 타이틀 설정
					$("body > header:not(.popup) > h1").html($("body > section:not(.popup) > h1").html());
					$("body > header:not(.popup) > h2").html($("body > section:not(.popup) > h2").html());

					// 03. 서브 메뉴 설정
					if(gnb.hasOwnProperty(_m) == false) {
						window.location.pathname = '/';
						return false;
					}

					let obj = eval(`gnb.${_m}`);
					if(obj.page) {
						$("body > section:not(.popup)").prepend('<nav><ul></ul></nav>');
						obj.page.forEach(row => {
							$("body > section:not(.popup) > nav > ul").append(`
								<li ${(_module == row.href)?'class="on"':''}><a href="/${_m + ((row.href != '')?'/' + row.href:'')}">${row.title}</a></li>
							`);
						});
					}
					if($("body > section > nav > ul > li.on").length == 0) {
						$("body > section > nav > ul > li").first().addClass("on");
					}
					$("body > section:not(.popup) > nav > ul > li > a").on("click",function(e) {
						var url = $(this).attr("href");
						var page = url.split("/");

						if (typeof (history.pushState) != "undefined") {
							history.pushState({}, page[0], url);
							$(this).parent().siblings().removeClass("on");
							$(this).parent().addClass("on");

							get_page(url);
						} else {
							window.location.href = url;
						}

						e.preventDefault();
					});

					setUI();

					/** 달력 **/
					if(typeof get_calendar == "function") {
						$("#calendar-prev-btn,#calendar-next-btn").click(function() {
							var date = $(this).data("date");
							get_calendar(date);
						});
					}
					$("body > nav").removeClass("on");
				}
			},
			error: null
		});
	};
	window.onpopstate = (e) => {
		get_page(window.location.pathname);
	};

	/** 엑셀 다운로드 **/
	$(document).on('click','button.export',function() {
		let api = $(this).data("api"),
			obj = $("<form />")
						.html($("form").last().find("input").clone().attr("type","hidden"))
						.attr("action",`/_xls/${api}`)
						.attr("method","post");
		$("body").append(obj);
		$(obj).get(0).submit();
		$(obj).remove();
	});

	/** 로그아웃 **/
	$("#btn-logout").click(function() {
		confirm(`<h1><i class="xi-log-out"></i>로그아웃</h1>로그아웃할까요?`,function() {
			$.ajax({
				url: config.api + "account/logout",
				success: function(d) {
					if(d.code == 200) {
						location.href = "/";
					}
					else {
						alert(d.msg);
					}
				}
			});
		});
	});

	/** 테이블 검색 관련 **/
	if(window.is_mobile) {
		$("table.list tr.search button.search").click(function() {
			$(this).parent().parent().toggleClass("on");
		});
		$("table.list tr.search button.reset").click(function() {
			$(this).parent().parent().removeClass("on");
		});

		$("html").attr("data-theme","day");
	}

	/** 목록 검색 **/
	$(document).on('click','table.list tr.search > td > button[type=submit]',function() {
		$(this).parent().parent().parent().parent().parent().find("input[name='page']").val("1");
	});
	$(document).on('click','table.list tr.search > td > button.init',function() {
		$(this).parent().parent().parent().parent().parent().find("dl > dd.on").click();
		$(this).parent().parent().parent().parent().parent().get(0).reset();
		$(this).parent().find("button[type=submit]").click();
	});

	/** 입력 형식 지정 **/
	$(document).on('keyup','input',function() {
		if($(this).hasClass("uppercase")) $(this).val($(this).val().toUpperCase()); // 무조건 대문자
		else if($(this).hasClass("lowercase")) $(this).val($(this).val().toLowerCase()); // 무조건 소문자

		var val = $(this).val();
		if(trim(val) != "") {
			$(this).addClass("has-value");
		}
		else {
			$(this).val("");
			$(this).removeClass("has-value");
		}
	});

	$(document).on('click','table.list tr.search dl > dd',function(){
		$(this).toggleClass("on");

		var total = $(this).parent().find("dd").length,
			selected = $(this).parent().find("dd.on").length,
			field = $(this).parent().data("field"),
			val = $(this).data("val"),
			multiple = $(this).parent().data("multiple"),
			txt;
		if(multiple == false) {
			$(this).siblings().removeClass("on");
			selected = $(this).parent().find("dd.on").length;
			$(this).parent().find("input").remove();
		}
		if(total == selected) {
			txt = "(다중선택)";
		}
		else if(selected > 1) {
			txt = "(다중선택)";
		}
		else if(selected == 1) {
			txt = $(this).parent().find("dd.on").text();
		}
		else {
			txt = "전체";
		}
		$(this).parent().find("dt").html(txt);
		if($(this).hasClass("on")) {
			$(this).append('<input type="hidden" name="' + field + '[]" value="' + val + '">');
		}
		else {
			$(this).find("input").remove();
		}
	});

	/** 파일 선택 **/
	$(document).on('change','.form-inline.file input[type=file]',function(){
		var file = $(this)[0].files[0],
			html = '',
			blobURL;

		ext = file.name.split('.').pop().toLowerCase(); //확장자
		//배열에 추출한 확장자가 존재하는지 체크
		if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
			$(this).wrap('<form>').closest('form').get(0).reset(); 
			$(this).unwrap();
			alert('이미지 파일이 아닙니다. gif, png, jpg, jpeg만 업로드 가능합니다.');
			return false;
		} else {
			blobURL = window.URL.createObjectURL(file);

			if($(this).attr("multiple") == "multiple"){
				html  = '<div class="item no-icon" title="' + file[i].name + '" ';
				if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) > -1) {
					html += ' style="background-image:url(\'' + blobURL + '\')"';
				}
				html += '	>';
				html += '	<div class="tools">';
				html += '		<a onclick="image_delete($(this).parent().parent());"><i class="xi-trash"></i></a>';
				if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) > -1) {
					html += '		<a onclick="image_view(\'' + blobURL + '\');"><i class="xi-magnifier-expand"></i></a>';
				}
				else if($.inArray(ext, ['mov', 'mp4', 'avi', 'mpg']) > -1) {
					html += '		<a onclick="movie_view(\'' + blobURL + '\');"><i class="xi-magnifier-expand"></i></a>';
				}
				html += '	</div>';
				//html += '	<a><i class="xi-arrows"></i></a>';
				html += '</div>';
				$(this).parent().parent().append(html);
			}
			else {
				$(this).parent().find("img").attr("src",blobURL);
			}
		}
	});

	/** 스킨 변경 버튼 **/
	$(document).on('click',"#btn-chg-style", function() {
		if($("html").attr("data-theme") == "day") {
			$("html").removeAttr("data-theme");
			delCookie("theme");
		}
		else {
			$("html").attr("data-theme","day");
			setCookie("theme","day",365);
		}
	});
	if(getCookie("theme")) {
		$("html").attr("data-theme",getCookie("theme"));
	}

	/** 최상단으로 스크롤 **/
	$(document).on('click',"#btn-to-top", function() {
		$("body,html").stop().animate({scrollTop:"0px"},'fast');
	});

	location.href = "#";
	$(document).on('click',"a[href='#']", function(e) {
		modal_close();
		e.preventDefault();
	});
	$(window).bind('hashchange', function (e) {
		e.preventDefault();

		var m = (window.location.pathname).replace("/","");
		var hash = window.location.hash.slice(1);
		var parameter = hash.split("/");
		if(hash == "close") {
			modal_close();
			window.location.hash = "adererror";
		}
		else if(hash == "") {
			modal_close();
		}
		else if(hash != "adererror") {
			if(parameter[0] == "logout") {
				$.ajax({
					url: config.api + "member/logout",
					success: function(d) {
						if(d.code == 200) {
							location.href = "/";
						}
						else {
							toast(d.msg);
						}
					}
				});
			}
			else {
				m = m.replaceAll('\\/','-');

				if(parameter[0] == "join" || parameter[0] == "new-member" || parameter[0] == "non-member" || parameter[0] == "login") {
					modal_close();
				}
				if(parameter.length > 1) {
					let param = { no : parameter[1] };
					if(parameter.length > 2) {
						param.param = parameter.slice(2,parameter.length);
					}
					modal(m + "-" + parameter[0],param);
				}
				else {
					modal(m + "-" + parameter[0]);
				}
			}
		}
	});
});