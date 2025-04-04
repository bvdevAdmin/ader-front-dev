let url,get_calendar,get_contents,qna,review,swiper = [];
const config = {
	api : "/_api/",
	modal : "/_modal/",
	script : "/scripts/module/",
	url : "https://dev.adererror.com"
};
Kakao.init('77c12b5718f843792a901ee506cb197c');
$.ajaxSetup({ 
	type : "post",
	dataType: "json",
	error : function() {
		notiModal("Server request failed.");
		// alert("서버요청 실패");
	},
	complete : function() {
		$(".tab.pre > *").click(function() {
			$(this).siblings().removeClass("on");
			$(this).addClass("on");
		});
		$(".tab.pre").removeClass("pre");
	}
});

$(document).ready(function() {
	url = $("body > section").eq(0).attr("class");
	if(url) url = trim(url.split(" ")[0]);

	$(".tab:not(.pre)").addClass("pre");
	$(window).scroll(function() {
		if($(this).scrollTop() > 100) {
			$("body > header").addClass("fixed");
		}
		else {
			$("body > header").removeClass("fixed");
		}
	});
	$("button#btn-rnb").click(function() {
		$("body").addClass("on-nav");
	});
	$("button#btn-rnb-close").click(function() {
		$("body").removeClass("on-nav");
	});
	$(".scrollbar-dynamic").scrollbar();

	$("input").each(function() {
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

	$("form button.cancel").click(function() {
		confirm("작성을 취소할까요?",function() {
			history.back();
		});
	});

	/** 키보드 제어 **/
	$(window).keyup(function(e) {
		switch(e.keyCode) {
			case 27: // Escape
				if($("body > section.modal").length) {
					if($("body > section.modal").hasClass("alert")) {
						$("body > section.modal").last().find("button.ok").click();
					}
					else if($("body > section.modal").hasClass("confirm")) {
						$("body > section.modal").last().find("button.cancel").click();
					}
					else {
						location.href = "#close";
					}
				}
			break;
		}
	});

	/** 폼 : 리스트 입력 **/
	$('body').on('click','.form-inline ul.list button',function() {
		if($(this).hasClass("remove")) {
			$(this).parent().remove();
		}
		else {
			$(this).parent().before(`
				<li>
					<div class="textarea" data-name="caution" contentEditable="true"></div>
					<button type="button" class="remove"><i class="xi-minus"></i></button>
				</li>
			`);
			$(this).parent().prev().find(".textarea").focus();
		}

	});

	/** 이미지 미리보기 **/
	$('body').on('change','.form-inline input[type="file"]',function(){
		let file = $(this)[0].files,
			ext = file[0].name.split('.').pop().toLowerCase(),  //확장자
			is_image = false,
			is_video = false,
			feature = $(this).data("feature"),
			is_list = '';
		if(typeof feature != 'undefined' && feature.indexOf("대표") > -1) {
			is_list = `<button type="button" class="is-list">대표</button>`;
		}

		if(typeof window.file_list != 'object') window.file_list = [];
		if($(this).attr("multiple") == "multiple"){
			for(let i=0;i<file.length;i++) {
				is_image = false;
				is_video = false;
				ext = file[i].name.split('.').pop().toLowerCase();
				if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) > -1) is_image = true;
				else if($.inArray(ext, ['mov', 'mp4', 'avi', 'mpg']) > -1) is_video = true;

				file[i].formname = $(this).attr("name") + "[]";
				file[i].id = new Date().getTime() + "_" + i;
				file[i].is_list = "n";
				window.file_list.push(file[i]);
				$(this).parent().parent().prepend(`
					<div class="item no-icon init" title="${file[i].name}" data-formname="${file[i].formname}" data-id="${file[i].id}" 
						${(is_image)?'style="background-image:url(\'' + window.URL.createObjectURL(file[i]) + '\')" data-type="image"':''}
						${(is_video)?'data-type="video"':''}>
						${is_list}
						<div class="tools">
							<a class="delete" data-id="${file[i].id}"><i class="xi-trash"></i></a>
							<a class="view"><i class="xi-magnifier-expand"></i></a>
						</div>
					</div>
				`);
			}
			if((navigator.appName == 'Netscape' && navigator.userAgent.search('Trident') != -1) || (navigator.userAgent.toLowerCase().indexOf("msie") != -1) ) {
				$(this).replaceWith($(this).clone(true));
			} else {
				$(this).val("");
			}
			$(this).parent().parent().find(".item.init").each(function() {
				$(this).removeClass("init");

				/** 대표 이미지 지정 **/
				$(this).find("button.is-list").click(function() {
					$(this).parent().parent().find("button.is-list.on").removeClass("on");
					$(this).addClass("on");

					let formname = $(this).parent().data("formname"),
						id = $(this).parent().data("id");
					for(let i=0;i<window.file_list.length;i++) {
						if(window.file_list[i].formname == formname) {
							if(window.file_list[i].id == id) {
								window.file_list[i].is_list = "y";
							}
							else {
								window.file_list[i].is_list = "n";
							}
						}
					}
				});

				/** 삭제 버튼 **/
				$(this).find(".tools > .delete").click(function() {
					let obj = $(this).parent().parent(),id = $(this).data("id");
					confirm("해당 파일을 삭제할까요?",function() {
						for(let i=0;i<window.file_list.length;i++) {
							if(window.file_list[i].id == id) {
								window.file_list.splice(i,1);
								break;
							}
						}
						$(obj).remove();
					});
				});

				/** 크게 보기 **/
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
			if($(this).parent().parent().find("button.is-list.on").length == 0) {
				$(this).parent().parent().find(".item").first().find("button.is-list").click();
			}
		}
		else if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
			$(this).parent().find("img").attr("src",window.URL.createObjectURL(file[0]));
		}
	});

	/** 편집 가능한 텍스트박스 **/
	$("form:not(#frm-search)").submit(function() {
		$(this).find(".textarea").each(function() {
			$(this).parent().find("input").val($(this).html());
		});

		tinymce.triggerSave();
		return false;
	});

	/** 파일 찾기 폼 **/
	$('.form-inline.file input[type="file"]').on('change',function() {
		let file = ($(this).val()).split("\\");
		$(this).parent().find("input[type='text']").val(file[file.length-1]);
	});

	/** 로그아웃 버튼 동작 **/
	$("#btn-logout,.logout").click(function() {
		$.ajax({
			url: config.api + "member/logout",
			success: function(d) {
				if(d.code == 200) {
					sessionStorage.clear();
					location.href = "/";
				}
				else {
					notiModal(d.msg);
					// alert(d.msg);
				}
			}
		});
	});

	/** 입력 폼 **/
	$(document).on("focusout","input,textarea,.textarea", function() {
		let val = $(this).val();
		if($(this).hasClass("textarea")) val = $(this).text();
		if(val == "") $(this).removeClass("has-value");
		else $(this).addClass("has-value");
	});
	/** 편집기 **/
	tinymce.init({
		selector: "textarea.tiny",
		menubar: false,
		statusbar: false,
		language: "ko_KR",
		style_formats: [
			{title: '굵은 글씨', inline: 'b'},
			{title: '빨간 글씨', inline: 'span', styles: {color: '#ff0000'}},
			//{name: '인라인', title: '인라인', inline: 'span', classes: [ 'my-inline' ]}
		],
		plugins: [
			'advlist autolink lists link image charmap print preview anchor',
			'searchreplace visualblocks code fullscreen',
			'insertdatetime media table wordcount'
		],
		smart_paste: true,  // note: default value for smart_paste is true
		image_file_types: 'jpg,svg,webp,png,gif',
		style_formats_merge: false,
		//icons: 'material',
		//toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
		toolbar1: 'codesample | bold italic | fontsizeselect | forecolor backcolor emoticons | alignleft aligncenter alignright alignjustify',
		toolbar2: 'bullist numlist outdent indent | link image | code',
		fontsize_formats: '8px 10px 12px 14px 16px 18px 24px 36px 48px',
		/*
		images_upload_url: config.api + 'board/upload/image',
		images_upload_handler: function (blobInfo, success, failure) {
			setTimeout(function () {
				success(blobInfo.blob());
			}, 2000);
		},
		*/


        /*** image upload ***/
        image_title: true,
        /* enable automatic uploads of images represented by blob or data URIs*/
        automatic_uploads: true,
        /*
            URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
            images_upload_url: 'postAcceptor.php',
            here we add custom filepicker only to Image dialog
        */
        file_picker_types: 'image',
        /* and here's our custom image picker*/
        file_picker_callback: function (cb, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');

            /*
            Note: In modern browsers input[type="file"] is functional without
            even adding it to the DOM, but that might not be the case in some older
            or quirky browsers like IE, so you might want to add it to the DOM
            just in case, and visually hide it. And do not forget do remove it
            once you do not need it anymore.
            */
            input.onchange = function () {
                var file = this.files[0];

                var reader = new FileReader();
                reader.onload = function () {
                    /*
                    Note: Now we need to register the blob in TinyMCEs image blob
                    registry. In the next release this part hopefully won't be
                    necessary, as we are looking to handle it internally.
                    */
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);

                    /* call the callback and populate the Title field with the file name */
                    cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        },
        /*** image upload ***/
	});

	/** 우편 번호 **/
	$(".form-inline .zipcode > button").click(function() {
		let _this=$(this).parent().find("input"),id = `_modal_alert_${new Date().getTime()}_${$("body > .modal.alert").length + 1}`;
		$("body").addClass("on-modal").append(`
			<section class="modal" id="${id}">
				<section class="body zipcode">
					<header>
						<h1>주소 검색</h1>
						<button type="button" class="close"></button>
					</header>
					<article></article>
				</section>
			</section>
		`);
		if(typeof set_ui == "function") set_ui($(`#${id}`));
		$(`#${id}`).find("button.close,button.cancel").click(function() {
			location.href = "#close";
		});
		setTimeout(function() {
			$(`#${id}`).addClass("on");
		},10);

		new daum.Postcode({
			oncomplete: function(data) {
				// 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

				// 각 주소의 노출 규칙에 따라 주소를 조합한다.
				// 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
				let fullAddr = data.address; // 최종 주소 변수
				let extraAddr = ''; // 조합형 주소 변수

				// 기본 주소가 도로명 타입일때 조합한다.
				if(data.addressType === 'R'){
					//법정동명이 있을 경우 추가한다.
					if(data.bname !== ''){
						extraAddr += data.bname;
					}
					// 건물명이 있을 경우 추가한다.
					if(data.buildingName !== ''){
						extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
					}
					// 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
					fullAddr += (extraAddr !== '' ? ' ('+ extraAddr +')' : '');
				}

				// 우편번호와 주소 정보를 해당 필드에 넣는다.
				$(_this).val(data.zonecode);
				$(_this).parent().next().val(fullAddr);
				$(`#${id} button.close`).click();
			},
			width : '100%',
			height : '100%',
			maxSuggestItems : 5
		}).embed($(`#${id} section.body.zipcode > article`).get(0));
	});

	/** 회원 정보 가져옴 **/
	/*
	if(true || 'user' in sessionStorage == false || sessionStorage.user == null) {
		$.ajax({
			url: config.api + "member/get",
			success: function(d) {
				if(d.code == 200) {
					sessionStorage.user = JSON.stringify(d);
				}
			}
		});
	}
	else {
		let user = JSON.parse(sessionStorage.user);
	}
	*/


	/** 오른쪽 NAV 처리 **/
	$("body > aside nav > article ul.list > li").each(function() {
		if($(this).find("ul").length > 0) {
			$(this).children("a").click(function() {
				$(this).parent().toggleClass("on");
			});
		}
	});

	/*** 찾기폼 동작 정의 ***/
	$("input[type='text']").on('keyup focusout',function() {
		if(trim($(this).val()) != '') {
			$(this).addClass("has-value");
		}
		else {
			$(this).val("");
			$(this).removeClass("has-value");
		}
	});
	$("div.search button[type='button']").click(function() {
		$(this).parent().find("input[type='text']").val("").removeClass("has-value");
		$(this).parent().submit();
	});


	/** 뒤로 가기 방지 **/
	if($("body > section > article").hasClass("detail") && $("body > section > article").hasClass("booking") && $("body > section > article").hasClass("result")) {
		history.pushState(null, "", location.href);
		window.addEventListener("popstate", () => history.pushState(null, "", location.href));
	}

	switch(url) {
		case 'intro':
			get_script('intro');
		break;

		case 'account':
			get_script('account');
		break;

		case 'search':
			get_script('search');
		break;

		case 'partner':

		break;

		case 'mypage':
			/** 프로필 관리 **/
			if($("section.mypage > article.info").length) {
				if($("#frm-mypage-leave").length) { // 회원 탈퇴
					get_script('mypage-leave');
				}
				else {
					get_script('mypage-info');
				}
			}

			/** 나의 글 관리 **/
			else if($("section.mypage > article.board").length) {
				get_script('mypage-board');
			}

			/** 찜리스트 **/
			else if($("section.mypage > article.favorite").length) {
				get_script('mypage-favorite');
			}

			/** 마이페이지 > 예약 현황 **/
			else if($("section.mypage > article.booking").length) {
				get_script('mypage-booking');
			}

			/** 캘린더 **/
			else if($("section.mypage > article.calendar").length) {
				get_script('mypage-calendar');
			}
		break;

		case 'community':
			/** FAQ **/
			if($("section.community > article.faq").length) {
				get_script('faq');
			}

			/** 공지사항 **/
			else if($("section.community > article.notice").length) {
				get_script('notice');
			}

		break;

		default:

	}

	function hashchange() {
		let m = (window.location.pathname).replace("/","");
		let hash = window.location.hash.slice(1);
		let parameter = hash.split("/");
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

				switch(parameter[0]) {
					case "join":
					case "new-member":
					case "non-member":
					case "login":
						modal_close();
					break;
				}

				if(parameter.length > 1) {
					modal(m + "-" + parameter[0],{ no : parameter[1] });
				}
				else {
					modal(m + "-" + parameter[0]);
				}
			}
		}
	}
	hashchange();
	$(window).bind('hashchange', function (e) {
		e.preventDefault();
		hashchange();
	});
});