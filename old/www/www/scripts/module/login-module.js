function Login() {
    (() => {
        function login() {
            var country = getLanguage();
            $("#frm-login").find('input[name=country]').val(country);

            var mail_regex = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}');
            var member_id = $('#member_id').val();
            var member_pw = $('#member_pw').val();
            mail_regex.test(member_id);

            $('.font__underline.font__red').text('');
            if (member_id == '') {
                $('.member_id_msg').text('이메일을 입력해주세요');

                return false;
            }
            else {
                if (!mail_regex.test(member_id)) {
                    $('.member_id_msg').text('이메일을 올바르게 입력해주세요');

                    return false;
                }
            }

            if (member_pw == '') {
                $('.member_pw_msg').text('비밀번호를 입력해주세요');

                return false;
            }


            $.ajax(
                {
                    url: api_location + "account/login",
                    type: 'POST',
                    data: $("#frm-login").serialize(),
                    error: function (jqxhr) {
                        console.warn(jqxhr.responseText)
                        $('.result_msg').text("모듈에 문제가 발생했습니다.");
                    },
                    success: function (data) {
                        if (data.code == "200") { // 로그인 성공
                            location.href = 'main';
                            //location.href='main';
                        }
                        else {	// 로그인 실패
                            var err_msg = '로그인 실패입니다. 로그인정보 재확인 후 다시 시도하여 주십시오.';
                            if (data.msg != null) {
                                err_msg = data.msg;
                            }
                            $('.result_msg').text(err_msg);
                        }
                    },
                    complete: function (data) {
                        //$("#result1").html(data.responseText);
                    }
                }
            );
        }

        function setCookie(cookieName, value, exdays) {
            let exdate = new Date();
            exdate.setDate(exdate.getDate() + exdays);
            let cookieValue = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toGMTString());
            document.cookie = cookieName + "=" + cookieValue;
        }

        //쿠키값 delete
        function deleteCookie(cookieName) {
            let expireDate = new Date();
            expireDate.setDate(expireDate.getDate() - 1);
            document.cookie = cookieName + "= " + "; expires=" + expireDate.toGMTString();
        }

        //쿠키값 get
        function getCookie(cookieName) {
            cookieName = cookieName + "=";
            let cookieData = document.cookie;
            let start = cookieData.indexOf(cookieName);
            let cookieValue = '';
            if (start != -1) {
                start += cookieName.length;
                let end = cookieData.indexOf(';', start);
                if (end == -1) end = cookieData.length;
                cookieValue = cookieData.substring(start, end);
            }
            return unescape(cookieValue); //unescape로 디코딩 후 값 리턴
        }

        // search
        function password_find() {
            var mail_regex = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}');
            var member_id = $('#member_id').val();
            mail_regex.test(member_id);

            $('.font__underline.font__red').css('visibility', 'hidden');
            if (member_id == '') {

                $('.member_id_msg').css('visibility', 'visible');
                $('.member_id_msg').text('이메일을 입력해주세요');

                return false;
            }
            else {
                if (!mail_regex.test(member_id)) {

                    $('.member_id_msg').css('visibility', 'visible');
                    $('.member_id_msg').text('이메일을 올바르게 입력해주세요');

                    return false;
                }
            }

            //Test용 STMP APP Password : wnaqvncvlugpjdvl
            /*
            Email.send({
                Host: "smtp@gmail.com",
                Username : "dhpark3610@gmail.com",
                Password : "psh1300411!",
                To: "shpark@bvdev.co.kr",
                From: "dhpark3610@gmail.com",
                Subject: "SMTP Test",
                Body : "SMTP Test context",
        
            }).then(
                message => alert(message)
            );
            */

            $.ajax(
                {
                    url: api_location + "account/search/get",
                    type: 'POST',
                    data: $("#frm-find").serialize(),
                    error: function (data) {
                        $('.member_id_msg').css('visibility', 'hidden');
                        $('.result_msg').css('visibility', 'visible');
                        $('.result_msg').text("모듈에 문제가 발생했습니다.");
                    },
                    success: function (data) {
                        if (data.code == "200") { // 이메일검사 성공
                            $('.member_id_msg').css('visibility', 'hidden');
                            $('.result_msg').css('visibility', 'visible');
                            $('.result_msg').text(data.data.temp_password);
                        }
                        else {	// 이메일검사 실패
                            $('.member_id_msg').css('visibility', 'visible');
                            $('.member_id_msg').text('존재하지 않는 이메일입니다');
                        }
                    },
                    complete: function (data) {
                        //$("#result1").html(data.responseText);
                    },
                    dataType: 'json'
                }
            );

        }
        // check
        function password_find_check() {
            var mail_regex = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}');
            var member_id = $('#member_id').val();
            mail_regex.test(member_id);

            $('.font__underline.font__red').css('visibility', 'hidden');
            if (member_id == '') {

                $('.member_id_msg').css('visibility', 'visible');
                $('.member_id_msg').text('이메일을 입력해주세요');

                return false;
            }
            else {
                if (!mail_regex.test(member_id)) {

                    $('.member_id_msg').css('visibility', 'visible');
                    $('.member_id_msg').text('이메일을 올바르게 입력해주세요');

                    return false;
                }
            }

            //Test용 STMP APP Password : wnaqvncvlugpjdvl
            /*
            Email.send({
                Host: "smtp@gmail.com",
                Username : "dhpark3610@gmail.com",
                Password : "psh1300411!",
                To: "shpark@bvdev.co.kr",
                From: "dhpark3610@gmail.com",
                Subject: "SMTP Test",
                Body : "SMTP Test context",
        
            }).then(
                message => alert(message)
            );
            */

            $.ajax(
                {
                    url: api_location + "account/check",
                    type: 'POST',
                    data: $("#frm-find").serialize(),
                    error: function (data) {
                        $('.member_id_msg').css('visibility', 'hidden');
                        $('.result_msg').css('visibility', 'visible');
                        $('.result_msg').text("모듈에 문제가 발생했습니다.");
                    },
                    success: function (data) {
                        if (data.code == "200") { // 이메일검사 성공
                            $('.member_id_msg').css('visibility', 'hidden');
                            $('.result_msg').css('visibility', 'visible');
                            $('.result_msg').text(data.data.temp_password);
                        }
                        else {	// 이메일검사 실패
                            $('.member_id_msg').css('visibility', 'visible');
                            $('.member_id_msg').text('존재하지 않는 이메일입니다');
                        }
                    },
                    complete: function (data) {
                        //$("#result1").html(data.responseText);
                    },
                    dataType: 'json'
                }
            );

        }

        //login-update
        $(document).ready(function () {
            $('input[name="password"]').keyup(function () {
                if (memberPwConfirm($(this).val()) || $(this).val().length == 0) {
                    $('.font__underline.warn__msg.member_pw').css('visibility', 'hidden');
                }
                else {
                    $('.font__underline.warn__msg.member_pw').css('visibility', 'visible');
                };
            });
            urlParsing();
        });
        function urlParsing() {
            var url = location.href;
            var idx = url.indexOf("?");

            if (idx >= 0) {
                var data = url.substring(idx + 1, url.length);
                var data_arr = data.split("=");
                if (data_arr[0] == 'member_idx') {
                    $('input [name="idx"]').val(data_arr[1]);
                    console.log($('input [name="idx"]').val());
                }
            }
        }
        function memberPwConfirm(str) {
            //  대소문자/숫자/특수문자 중 3가지 이상 조합, 8자-16자
            //  입력 가능 특수문자 : '!@#$%^()_-={}[]|;:<>,.?/                    
            var password_reg = /^(?=.*[\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(])(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[\da-zA-Z\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(]{8,16}/;
            //  공백 입력 불가능
            var space_reg = /\s/g;
            //var password_str = $('input[name="password"]').eq(0).val();

            if (space_reg.test(str) == false) {
                return password_reg.test(str)
                return true;
            }
            else {
                return false;
                //공백 포함 예외처리
            }
        }
        //.css('visibility','hidden');
        function updateMemberPw() {
            var member_idx = $('input[name="member_idx"]').val();
            var member_pw = $('input[name="member_pw"]').val();
            var member_pw_confirm = $('input[name="member_pw_confirm"]').val();

            $('.warn__msg').css('visibility', 'hidden');

            if (memberPwConfirm(member_pw) == false) {
                $('.font__underline.warn__msg.member_pw').css('visibility', 'visible');
                return false;
            }
            if (member_pw != member_pw_confirm) {
                $('.warn__msg.member_pw_confirm').css('visibility', 'visible');
                return false;
            }

            $.ajax(
                {
                    url: api_location + "account/put",
                    type: 'POST',
                    data: {
                        'member_idx': member_idx,
                        'member_pw': member_pw
                    },
                    error: function (data) {
                    },
                    success: function (data) {
                        if (data.code == "200") {
                            //location.reload();
                            console.log('비밀번호 변경 성공');
                            location.href = '/login';
                        }
                    },
                    complete: function (data) {
                        //$("#result1").html(data.responseText);
                    },
                    dataType: 'json'
                }
            );
        }
        //login-password-pub 
        $(document).ready(function () {
            $('#pw_desciption').hide();
            $('input[name="member_pw"]').keyup(function () {
                if (memberPwConfirm($(this).val()) || $(this).val().length == 0) {
                    $('.font__underline.warn__msg.member_pw').css('visibility', 'hidden');
                    hidePwDescription();
                }
                else {
                    $('.font__underline.warn__msg.member_pw').css('visibility', 'visible');
                    showPwDescription();
                };
            });
        });
        function memberPwConfirm(str) {
            //  대소문자/숫자/특수문자 중 3가지 이상 조합, 8자-16자
            //  입력 가능 특수문자 : '!@#$%^()_-={}[]|;:<>,.?/                    
            var password_reg = /^(?=.*[\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(])(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[\da-zA-Z\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(]{8,16}/;
            //  공백 입력 불가능
            var space_reg = /\s/g;
            //var password_str = $('input[name="password"]').eq(0).val();

            if (space_reg.test(str) == false) {
                return password_reg.test(str)
                return true;
            }
            else {
                return false;
                //공백 포함 예외처리
            }
        }
        function showPwDescription() {
            $('#pw_desciption').show();
            $('#hide_area').hide();
        }
        function hidePwDescription() {
            $('#pw_desciption').hide();
            $('#hide_area').show();
        }

        //join
        $(document).ready(function () {
            $('#pw_desciption').hide();
            $('input[name="member_pw"]').keyup(function () {
                if (memberPwConfirm($(this).val()) || $(this).val().length == 0) {
                    $('.font__underline.warn__msg.member_pw').css('visibility', 'hidden');
                    hidePwDescription();
                }
                else {
                    $('.font__underline.warn__msg.member_pw').css('visibility', 'visible');
                    showPwDescription();
                };
            });
            $('.component').click(function () {
                var sel_cnt = $('.component:checked').length;
                if (sel_cnt == 3) {
                    $('.select__all').prop('checked', true);
                }
                else {
                    $('.select__all').prop('checked', false);
                }
            });
        });
        $(function () {
            /*
            if($('#postcodify').find('postcodify_search_controls').length == 0){
                $("#postcodify").postcodify({
                    insertPostcode5 : "#zipcode",
                    insertAddress : "#road_addr",
                    insertDetails : "#detail_ddr",
                    insertJibeonAddress : "#lot_addr",
                    hideOldAddresses: false,
                    results:".post-change-result",
                    hideSummary:true,
                    useFullJibeon:true,
                    onReady:function(){
                        document.querySelector(".post-change-result").style.display="none";
                        $(".postcodify_search_controls .keyword").attr("placeholder","예) 성동구 연무장길 53, 성수동2가 315-57");
                        // $(".post-change-result").hide();
                    },
                    onSuccess:function(){
                        document.querySelector(".post-change-result").style.display="block";
                        $("#postcodify div.postcode_search_status.too_many").hide();
                        // $(".post-change-result").hide();
                    },
                    afterSelect: function(selectedEntry) {
        
                        $("#postcodify div.postcode_search_result").remove();
                        $("#postcodify div.postcode_search_status.too_many").hide();
                        $("#postcodify div.postcode_search_status.summary").hide();
                        document.querySelector(".post-change-result").style.display="none";
                        $("#entry_box").show();
                        $("#entry_details").focus();
                        $(".postcodify_search_controls .keyword").val($("#road_addr").val());
                    }
                });
                $('.postcodify_search_controls .keyword').keyup(function(){
                    $('.postcodify_search_controls .keyword').attr('chk-flg', 'false');
                });
                
                $('.post-change-result.postcodify_search_form.postcode_search_form').on('click',function(){
                    $('.postcodify_search_controls .keyword').attr('chk-flg', 'true');
                });
            }
            */
        });
        function selectAllClick(obj) {
            if ($(obj).prop('checked') == true) {
                $(obj).prop('checked', true);
                $(".login__check__option").prop('checked', true);
            } else {
                $(obj).attr('checked', false);
                $(".login__check__option").prop('checked', false);
            }
        }
        function memberPwConfirm(str) {
            //  대소문자/숫자/특수문자 중 3가지 이상 조합, 8자-16자
            //  입력 가능 특수문자 : '!@#$%^()_-={}[]|;:<>,.?/                    
            var password_reg = /^(?=.*[\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(])(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[\da-zA-Z\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(]{8,16}/;
            //  공백 입력 불가능
            var space_reg = /\s/g;
            //var password_str = $('input[name="password"]').eq(0).val();

            if (space_reg.test(str) == false) {
                return password_reg.test(str)
                return true;
            }
            else {
                return false;
                //공백 포함 예외처리
            }
        }
        //.css('visibility','hidden');
        function joinAction() {
            var mail_regex = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}');
            var member_id = $('input[name="member_id"]').val();
            var member_pw = $('input[name="member_pw"]').val();
            var member_pw_confirm = $('input[name="member_pw_confirm"]').val();
            var member_name = $('input[name="member_name"]').val();
            var birth_year = $('input[name="birth_year"]').val();
            var birth_month = $('input[name="birth_month"]').val();
            var birth_day = $('input[name="birth_day"]').val();
            var terms_of_service_flg = $('#terms_of_service_flg').is(':checked');

            mail_regex.test(member_id);

            $('.warn__msg').css('visibility', 'hidden');
            if (memberPwConfirm(member_pw) == false) {
                $('.font__underline.warn__msg.member_pw').css('visibility', 'visible');
                showPwDescription();
                return false;
            }
            if (member_id == '' || !mail_regex.test(member_id)) {
                $('.warn__msg.member_id').css('visibility', 'visible');
                return false;
            }
            else if (member_pw != member_pw_confirm) {
                $('.warn__msg.member_pw_confirm').css('visibility', 'visible');
                return false;
            }
            else if (member_name == '') {
                $('.warn__msg.member_name').css('visibility', 'visible');
                return false;
            }
            else if (birth_year == '' || birth_month == '' || birth_day == '') {
                $('.warn__msg.birth').css('visibility', 'visible');
                return false;
            }
            else if (terms_of_service_flg == false) {
                $('.warn__msg.essential').css('visibility', 'visible');
                $('.warn__msg.essential').text('필수항목을 선택해주세요');
                return false;
            }

            $.ajax(
                {
                    url: api_location + "account/add",
                    type: 'POST',
                    data: $("#frm-regist").serialize(),
                    error: function (data) {
                    },
                    success: function (data) {
                        if (data.code == "200") {
                            //location.reload();
                            location.href = '/main';
                            console.log('회원가입 성공');
                        }
                        else {
                            if (data.code == "303") {
                                $('.warn__msg.essential').css('visibility', 'visible');
                                $('.warn__msg.essential').text(data.msg);
                            }
                        }
                    },
                    complete: function (data) {
                        //$("#result1").html(data.responseText);
                    },
                    dataType: 'json'
                }
            );
        }

        function showPwDescription() {
            $('#pw_desciption').show();
            $('#hide_area').hide();
        }
        function hidePwDescription() {
            $('#pw_desciption').hide();
            $('#hide_area').show();
        }
    })();
}