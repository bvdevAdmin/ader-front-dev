/**
 * @author SIMJAE
 * @description 검색 생성자 함수
 */
function Search() {
    let search_keyword = "";
    let popular_product = "";

    function getSearchInfoList() {
        $.ajax({
            type: "post",
            url: api_location + "search/list/get",
            headers: {
            	"country": getLanguage()
            },
            dataType: "json",
            async: false,
            error: function () {
                // notiModal("추천검색어/실시간 인기 제품 조회중 오류가 발생했습니다.");
                makeMsgNoti(country, 'MSG_F_ERR_0006', null);
            },
            success: function (d) {
                if (d.code == 200) {
                    let data = d.data;
                    if (data != null) {
                        let keyword_info = data.keyword_info;
                        writeKeywordInfo(keyword_info);

                        let popular_info = data.popular_info;
                        writePopularInfo(popular_info);
                    }
                }
            }
        });
    }

    function writeKeywordInfo(keyword_info) {
        keyword_info.forEach(function (row) {
            search_keyword += `<li><a href="${row.menu_link}">${row.keyword_txt}</a></li>`;
        });
    }

    function writePopularInfo(popular_info) {
        popular_info.forEach(function (row) {
            popular_product += `
				<div class="popular-box" onClick="location.href='/product/detail?product_idx=${row.product_idx}'">
					<img src="${cdn_img}${row.img_location}" alt="">
					<span class="product-name">${row.product_name}</span>
				</div>
			`;
        });
    }

    this.writeHtml = () => {
        let sideBox = document.querySelector(`.side__box`);
        let sideWrap = document.querySelector(`#sidebar .side__wrap`);

        sideWrap.dataset.module = "search";

        const searchContent = document.createElement("section");
        searchContent.className = "search-wrap";

        getSearchInfoList();

        searchContent.innerHTML = `
			<div class="search-header">
				<img class="search-svg" src="/images/svg/search.svg" alt="">
				<input class="search-input" type="search" data-i18n-placeholder="ss_please_search">
				
				<div class="search-btn">
					<span data-i18n="ss_search">검색</span>
				</div>
				
				<div class="search-clear-btn hidden">
					<img src="/images/svg/reset.svg" alt="">
					<span>clear</span>
				</div>
			</div>
			<div class="search-body">
				<div class="search-content current">
					<ul class="search-recommend">
						<div class="search-recommend-title" data-i18n="ss_recomm_search">추천 검색어</div>
						${search_keyword}
					</ul>
					<div class="search-recommend-title" data-i18n="ss_real_time">실시간 인기 제품</div>
					<div class="popular-wrap">
						${popular_product}
					</div>
				</div>
                <div class="search-content result hidden">
                <div class="search-result-title" data-i18n="ss_search_results"></div>
                <div class="search-result-wrap"></div>
            </div>
			</div>
        `;
		
        sideBox.appendChild(searchContent);
        changeLanguageR();
    };

    this.mobileWriteHtml = () => {
        let mobileSearchWrap = document.querySelector(`.search__cont`);
        mobileSearchWrap.innerHTML = "";
        getSearchInfoList();
        
        const mdlBox = document.createElement("div");
        mdlBox.className = "mdlSearchBox";
        mdlBox.innerHTML = `
				<div class="search-header">
					<img class="search-svg" src="/images/svg/search.svg" alt="">
					
					<input class="search-input" type="search" data-i18n-placeholder="ss_please_search">
					
					<div class="search-btn">
						<span data-i18n="ss_search">검색</span>
					</div>
					
					<div class="search-clear-btn hidden">
						<img src="/images/svg/reset.svg" alt="">
						<span>clear</span>
					</div>
				</div>
				
				<div class="search-body">
					<div class="search-content current">
						<ul class="search-recommend">
							<div class="search-recommend-title" data-i18n="ss_recomm_search">추천 검색어</div>
							${search_keyword}
						</ul>
						<div class="search-popular-title" data-i18n="ss_real_time">실시간 인기 제품</div>
						<div class="popular-wrap">
							${popular_product}
						</div>
					</div>
					<div class="search-content result hidden">
						<div class="search-result-title" data-i18n="ss_search_results">검색 결과</div>
						<div class="search-result-wrap"></div>
					</div>
				</div>
		`;
		
        mobileSearchWrap.append(mdlBox);
        changeLanguageR();
    };

    function getSearchResult(search_keyword) {
        let searchResult = document.querySelectorAll(".search-content.result");
        let searchCurrent = document.querySelectorAll(".search-content.current");

        if (search_keyword.length == 0) {
            searchResult.forEach(function(resuleEl){
                resuleEl.classList.add("hidden");
            });
            searchCurrent.forEach(function(currentEl){
                currentEl.classList.remove("hidden");
            });
            return;
        }

        $.ajax({
            type: "post",
            url: api_location + "search/get",
            async: false,
            data: {
                'search_keyword': search_keyword
            },
            dataType: "json",
            error: function () {
                // notiModal("상품 검색처리중 오류가 발생했습니다.");
                makeMsgNoti(getLanguage(), 'MSG_F_ERR_0029', null);
            },
            success: function (d) {
                if (d.code == 200) {
                    let data = d.data;

                    let search_result_wrap = $('.search-result-wrap');
                    search_result_wrap.html('');

                    if (data != null) {
                        let search_result = "";

                        if(data.length == 0){
                            search_result += `<h3 data-i18n="ss_search_no_results">검색결과가 없습니다.</h3>`;
                        }
                        else if (data.length < 60) {
                            data.forEach(function (row) {
                                search_result += `
                                    <div class="search-result-box" onClick="location.href='/product/detail?product_idx=${row.product_idx}'">
                                        <img src="${cdn_img}${row.img_location}" alt=""/>
                                        <span class="product-name">${row.product_name}</span>
                                    </div>
                                `;
                            });
                        } else {
                            let headData = data.slice(0, 60);
                            let tailData = data.slice(61);
                            headData.forEach(function (row) {
                                search_result += `
                                    <div class="search-result-box" onClick="location.href='/product/detail?product_idx=${row.product_idx}'">
                                        <img src="${cdn_img}${row.img_location}" alt=""/>
                                        <span class="product-name">${row.product_name}</span>
                                    </div>
                                `;
                            });
                            search_result += `
                                <div class="view_more_search"><span>더보기 +</span></div>
                            `;
                        }

                        search_result_wrap.append(search_result);

                        searchResult.forEach(function(resuleEl){
                            resuleEl.classList.remove("hidden");
                        });
                        searchCurrent.forEach(function(currentEl){
                            currentEl.classList.add("hidden");
                        });
                        changeLanguageR();
                    }
                }
            }
        });
    }
    this.addSearchEvent = () => {
        let web_serarch_wrap = document.querySelector('.search-wrap');
        let web_input = document.querySelector('.search-wrap .search-input');
        let web_search_btn = document.querySelector('.search-wrap .search-btn');
        let web_search_clear_btn = document.querySelector('.search-wrap .search-clear-btn');
        
        let mobile_search_wrap = document.querySelector('.mdlSearchBox');
        let mobile_input = document.querySelector('.mobile__search .search-input');
        let mobile_search_btn = document.querySelector('.mobile__search .search-btn');
        let mobile_search_clear_btn = document.querySelector('.mobile__search .search-clear-btn');

        if(web_serarch_wrap != null){
            web_input.addEventListener('keyup', searchEnterKeyEvent);
            web_search_btn.addEventListener('click', searchBtnClickEvent);
            web_search_clear_btn.addEventListener('click', searchClearClickEvent);
        }
        if(mobile_search_wrap != null){
            mobile_input.addEventListener('keyup', searchEnterKeyEvent);
            mobile_search_btn.addEventListener('click', searchBtnClickEvent);
            mobile_search_clear_btn.addEventListener('click', searchClearClickEvent);
        }

        function searchEnterKeyEvent(ev){
            let input_value = ev.target.value;
            
            if (ev.which == 13) {
                if(input_value.trim() !== '') {
                    getSearchResult(input_value);
                    toggleSearchBtn('clear');
                } else {
                    // notiModal("검색할 내용을 입력해주세요.");
                    makeMsgNoti(getLanguage(), 'MSG_F_WRN_0055', null);
                }
            } else if (input_value.length == 0) {
                getSearchResult('');
        
                toggleSearchBtn('search');
            }
        }

        function searchBtnClickEvent(ev){
            let input_value = ev.target.closest('.search-header').querySelector('.search-input').value;
            if (input_value.trim() !== '') {
                getSearchResult(input_value);
                toggleSearchBtn('clear');
            } else {
                // notiModal("검색할 내용을 입력해주세요.");
                makeMsgNoti(getLanguage(), 'MSG_F_WRN_0055', null);
            }
        }

        function searchClearClickEvent(ev){
            let search_wrap = ev.target.closest('.search-header');
            search_wrap.querySelector(".search-input").value = '';
        
            getSearchResult('');
            toggleSearchBtn('search');
        }

        function toggleSearchBtn(type){
            let webHiddenBtn = null;
            let mobileHiddenBtn = null;
            let webShowBtn = null;
            let mobileShowBtn = null;
            if(type == 'search'){
                webHiddenBtn = web_search_clear_btn;
                webShowBtn = web_search_btn;
                mobileHiddenBtn = mobile_search_clear_btn;
                mobileShowBtn = mobile_search_btn;
            }
            else if(type == 'clear'){
                webHiddenBtn = web_search_btn;
                webShowBtn = web_search_clear_btn;
                mobileHiddenBtn = mobile_search_btn;
                mobileShowBtn = mobile_search_clear_btn;
            }
            if(web_serarch_wrap != null){
                webHiddenBtn.classList.add('hidden');
                webShowBtn.classList.remove('hidden');
            }
            if(mobile_search_wrap != null){
                mobileHiddenBtn.classList.add('hidden');
                mobileShowBtn.classList.remove('hidden');
            }
        }
    }
}

