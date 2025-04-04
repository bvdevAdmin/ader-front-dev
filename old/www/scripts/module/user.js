
/**
 * @author SIMJAE
 * @description 로그인 , 유저 생성자 함수 
 * 
 */

function User() {
    this.userLoad = () => {
        $.ajax({
            type: "post",
			url: api_location + "menu/get ",
            data: {
                "country": getLanguage()
            },
            dataType: "json",
            async:false,
            error: function (d) {
            },
            success: function (d) {
                if (d.code == 200) {
					let member_info = d.member_info;
                    if (member_info != null) {
						writeUserHtml(member_info);
                        
						/* 로그아웃 버튼 클릭처리 */
						clickBtn_logout();
                    } else {
                        writeLoginHtml();
						
						initLoginHandler();
						
						/* 로그인 정보 초기화 */
						initMemberId();
						
						/* 로그인 버튼 클릭처리 */
						clickBtn_login();

						/* 비밀번호 찾기 링크 클릭처리 */
						clickLink_pw();

						/* 회원가입 버튼 클릭처리 */
						clickBtn_join();

						/* 로그인 화면 엔터 처리 */
						keyupLogin_enter();
                    }
                }
            }
        });
    }
	
    this.mobileUserLoad = () => {
        $.ajax({
            type: "post",
			url: api_location + "menu/get ",
            data: {
                "country": getLanguage()
            },
            dataType: "json",
            async:false,
            error: function (d) {
            },
            success: function (d) {
                if (d.code == 200) {
					let member_info = d.member_info;
					if (member_info != null) {
                        writeMobileUserHtml(memberInfo);
                    } else {
						location.href = "/login";
                        
                        /* 로그아웃 버튼 클릭처리 */
						clickBtn_logout();
                    }
				}
            }
        });
    }
    
	/* 사이드바 유저 정보 화면설정 */
    let writeUserHtml = (data) => {
		let {member_id, member_mileage, member_name, member_voucher, whish_cnt, basket_cnt, order_cnt} = data
		
        let side_box = document.querySelector(`.side__box`);
        
		let side_wrap = document.querySelector(`#sidebar .side__wrap`);
        side_wrap.dataset.module = "user";
		
        const user_content = document.createElement("section");
        user_content.className = "user-wrap";
        user_content.innerHTML = `
			<div class="user-body">
				<div class="user-logo">
					<img src="/images/mypage/mypage_member_icon.svg">
				</div>
				<div class="content-row">
					<div>
						<p>${member_name}</p>
					</div>
					<div>
						<p>${member_id}</p>
					</div>
				</div>
				<div class="user-content">
					<div class="content-point left">
						<div data-i18n="m_order_cnt"></div>
						<a class="content-link" href="/mypage?mypage_type=orderlist">
							<div class="user-orderlist-cnt">${order_cnt}</div>
						</a>
					</div>
					<div class="content-point center">
						<div data-i18n="m_mileage"></div>
						<a class="content-link" href="/mypage?mypage_type=mileage_first">
							<div class="user-mileage">${member_mileage}</div>
						</a>
					</div>
					<div class="content-point right">
						<div data-i18n="m_voucher"></div>
						<a class="content-link" href="/mypage?mypage_type=voucher_first">
							<div class="user-voucher">${member_voucher}</div>
						</a>
					</div>
				</div>
			</div>
			<div class="user-mypage-area">
				<div class="icon__item" btn-type="orderlist">
					<a href="/mypage?mypage_type=orderlist">
						<div class="icon">
							<img src="/images/mypage/mypage_orderlist_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_order_history"></p>
					</div>
				</div>
				<div id="mileage_icon" class="icon__item" btn-type="mileage">
					<a href="/mypage?mypage_type=mileage_first">
						<div class="icon">
							<img src="/images/mypage/mypage_point_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_mileage_charging"></p>
					</div>
				</div>
				<div id="voucher_icon" class="icon__item" btn-type="voucher">
					<a href="/mypage?mypage_type=voucher_first">
						<div class="icon">
							<img src="/images/mypage/mypage_voucher_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_voucher"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="bluemark">
					<a href="/mypage?mypage_type=bluemark_verify">
						<div class="icon">
							<img src="/images/mypage/mypage_bluemark_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_blue_mark"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="stanby">
					<a href="/mypage?mypage_type=stanby_first">
						<div class="icon">
							<img src="/images/mypage/mypage_stanby_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_standby"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="preorder">
					<a href="/mypage?mypage_type=preorder_first">
						<div id="preorder_icon" class="icon">
							<img src="/images/mypage/mypage_preorder_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_preorder"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="reorder">
					<a href="/mypage?mypage_type=reorder_first">
						<div class="icon">
							<img src="/images/mypage/mypage_reorder_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_notify_me"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="membership">
					<a href="/mypage?mypage_type=membership_first">
						<div class="icon">
							<img src="/images/mypage/mypage_membership_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_membership"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="inquiry">
					<a href="/mypage?mypage_type=inquiry_first">
						<div class="icon">
							<img src="/images/mypage/mypage_inquiry_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_inquiry"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="as">
					<a href="/mypage?mypage_type=as_first">
						<div class="icon">
							<img src="/images/mypage/mypage_as_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p>A/S</p>
					</div>
				</div>
				<div class="icon__item" btn-type="service">
					<a href="/mypage?mypage_type=service_first">
						<div class="icon">
							<img src="/images/mypage/mypage_service_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_customer_care"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="profile">
					<a href="/mypage?mypage_type=profile_first">
						<div class="icon">
							<img src="/images/mypage/mypage_profile_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_account"></p>
					</div>
				</div>
			</div>
			<div class="user-button-area">
				<a href="/mypage">
					<div class="user-button mypageBtn" data-i18n="m_goto_my-page"></div>
				</a>
				<div class="user-button btn_logout" data-i18n="m_logout"></div>
			</div>
        `;
        
		side_box.appendChild(user_content);
        
        changeLanguageR();
    };
    
	/* 모바일 사이드바 유저 정보 화면설정 */
    let writeMobileUserHtml = (data) => {
		let {member_id, member_mileage, member_name, member_voucher, whish_cnt, basket_cnt, order_cnt} = data;
		
        let mypage_content = document.querySelector(`.mypage__cont`);
        mypage_content.innerHTML = '';
		
        const user_content = document.createElement("section");
        
        user_content.className = "user-wrap";
        user_content.innerHTML = `
			<div class="user-body">
				<div class="user-logo">
					<img src="/images/mypage/mypage_member_icon.svg">
				</div>
				<div class="content-row">
					<div>
						<p>${member_name}</p>
					</div>
					<div>
						<p>${member_id}</p>
					</div>
				</div>
				<div class="user-content">
					<div class="content-point left">
						<div data-i18n="m_order_cnt"></div>
						<a class="content-link" href="/mypage?mypage_type=orderlist">
							<div class="user-orderlist-cnt">${order_cnt}</div>
						</a>
					</div>
					<div class="content-point center">
						<div data-i18n="m_mileage"></div>
						<a class="content-link" href="/mypage?mypage_type=mileage_first">
							<div class="user-mileage">${member_mileage}</div>
						</a>
					</div>
					<div class="content-point right">
						<div data-i18n="m_voucher"></div>
						<a class="content-link" href="/mypage?mypage_type=voucher_first">
							<div class="user-voucher">${member_voucher}</div>
						</a>
					</div>
				</div>
			</div>
			<div class="user-mypage-area">
				<div class="icon__item" btn-type="orderlist">
					<a href="/mypage?mypage_type=orderlist">
						<div class="icon">
							<img src="/images/mypage/mypage_orderlist_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_order_history"></p>
					</div>
				</div>
				<div id="mileage_icon" class="icon__item" btn-type="mileage">
					<a href="/mypage?mypage_type=mileage_first">
						<div class="icon">
							<img src="/images/mypage/mypage_point_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_mileage_charging"></p>
					</div>
				</div>
				<div id="voucher_icon" class="icon__item" btn-type="voucher">
					<a href="/mypage?mypage_type=voucher_first">
						<div class="icon">
							<img src="/images/mypage/mypage_voucher_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_voucher"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="bluemark">
					<a href="/mypage?mypage_type=bluemark_verify">
						<div class="icon">
							<img src="/images/mypage/mypage_bluemark_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_blue_mark"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="stanby">
					<a href="/mypage?mypage_type=stanby_first">
						<div class="icon">
							<img src="/images/mypage/mypage_stanby_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_standby"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="preorder">
					<a href="/mypage?mypage_type=preorder_first">
						<div id="preorder_icon" class="icon">
							<img src="/images/mypage/mypage_preorder_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_preorder"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="reorder">
					<a href="/mypage?mypage_type=reorder_first">
						<div class="icon">
							<img src="/images/mypage/mypage_reorder_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_notify_me"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="membership">
					<a href="/mypage?mypage_type=membership_first">
						<div class="icon">
							<img src="/images/mypage/mypage_membership_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_membership"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="inquiry">
					<a href="/mypage?mypage_type=inquiry_first">
						<div class="icon">
							<img src="/images/mypage/mypage_inquiry_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_inquiry"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="as">
					<a href="/mypage?mypage_type=as_first">
						<div class="icon">
							<img src="/images/mypage/mypage_as_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p>A/S</p>
					</div>
				</div>
				<div class="icon__item" btn-type="service">
					<a href="/mypage?mypage_type=service_first">
						<div class="icon">
							<img src="/images/mypage/mypage_service_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_customer_care"></p>
					</div>
				</div>
				<div class="icon__item" btn-type="profile">
					<a href="/mypage?mypage_type=profile_first">
						<div class="icon">
							<img src="/images/mypage/mypage_profile_icon.svg">
						</div>
					</a>
					<div class="icon__title">
						<p data-i18n="m_account"></p>
					</div>
				</div>
			</div>
			<div class="user-button-area">
				<a href="/mypage">
					<div class="user-button mypageBtn" data-i18n="m_goto_my-page"></div>
				</a>
				<div class="user-button btn_logout" data-i18n="m_logout"></div>
			</div>
        `;
		
        mypage_content.appendChild(user_content);
        
        changeLanguageR();
    };
    
	/* 사이드바 로그인 화면설정 */
    let writeLoginHtml = () => {
		changeLanguageR();
		
        let login = new Login();
        let country = getLanguage();
        
		let side_box = document.querySelector(`.side__box`);
        
		let side_wrap = document.querySelector(`#sidebar .side__wrap`);
        side_wrap.dataset.module = "login";

        let sns_login_HTML = ``;
        if (country == 'KR') {
            sns_login_HTML = `
                <div class="content__title sns__account__login">
                    <div class="font__large text__align__center" data-i18n="m_login_sns"></div>
                </div>
				
                <div class="content__row sns__account__login" style="display:flex;">
                    <img class="sns-login-btn kakao__btn" style="width:30px;height:30px;" src="${cdn_img}/btn/btn_kakao.png">
                    <img class="sns-login-btn naver__btn" style="width:30px;height:30px;margin-right:10px;" src="${cdn_img}/btn/btn_naver.jpg">
					<img class="sns-login-btn google_btn" style="width:30px;height:30px;" src="${cdn_img}/btn/btn_google.png">
                </div>
            `;
        }
        
        const write_login_HTML = document.createElement("section");
        write_login_HTML.className = "user-wrap";
        write_login_HTML.innerHTML = `
			<div class="login__card">
				<div class="card__header">
					<p class="font__large" data-i18n="m_login"></p>
					<span class="font__underline font__red result_msg"></span>
				</div>
				
				<div class="card__body">
					<div id="login_side">
						<div class="content__wrap">
							<div class="content__wrap__msg">
								<div class="content__title" data-i18n="p_email"></div>
								<div class="font__underline font__red member_id_msg"></div>
							</div>
							
							<div class="content__row">
								<input class="param_member_id" type="text" name="member_id" value="" data-wrap_login="side">
							</div>
						</div>
						
						<div class="content__wrap">
							<div class="content__wrap__msg">
								<div class="content__title" data-i18n="p_password"></div>
								<div class="font__underline font__red member_pw_msg"></div>
							</div>
							
							<div class="content__row">
								<input class="param_member_pw" type="password" name="member_pw" value="" data-wrap_login="side">
							</div>
						</div>
						
						<div class="content__wrap login_btn">
							<button type="button" class="black_btn btn_login" data-wrap_login="side" data-i18n="m_login"></button>
						</div>
					</div>
					
					<div class="content__wrap">
						<div class="content__row">
							<div class="checkbox__label">
								<input id="side_member_id_flg" type="checkbox" name="member_id_flg">
								<label for="side_member_id_flg"></label>
							</div>
							
							<span class="font__small" data-i18n="p_save_mail"></span>
							
							<span class="font__underline link_pw" data-i18n="m_find_password" style="cursor:pointer;" onclick="location.href='/login/check'">
								
							</span>
						</div>
					</div>
					
					<div class="content__wrap sns_login_wrap">
						${sns_login_HTML}
					</div>
					
					<div class="contour__line"></div>
					
					<div class="content__wrap">
						<p class="font__large text__align__center" data-i18n="lm_menu_msg_02"></p>
					</div>
				</div>
				
				<div class="card__footer">
					<button type="button" class="black_btn btn_join" data-i18n="lm_create_account" onclick="location.href='/login/join'"></button>
				</div>
				
				<div class="customer-title" data-i18n="lm_customer_care_service"></div>
				
				<div class="customer-btn-box">
					<div class="customer-btn" onclick="location.href='/login/service'"><span data-i18n="lm_notice"></span></div>
					<div class="customer-btn" onclick="location.href='/login/faq'"><span data-i18n="lm_faq"></span></div>
					<div class="customer-btn" onclick="location.href='/login?r_url=/mypage?mypage_type=inquiry'">
						<span data-i18n="lm_inquiry"></span>
					</div>
				</div>
			</div>
        `;
		
        side_box.appendChild(write_login_HTML);
		
		changeLanguage(getLanguage());
    }
}
