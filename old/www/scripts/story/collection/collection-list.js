let observe_collection;
let setTimeoutIdArr = [];
let last_index = 0;

let swiper_collection = new Swiper(".collectionCategory-swiper", {
    slidesPerView:'auto',
    spaceBetween: 10,
    navigation: {
        nextEl: ".collection-header-wrap .swiper-button-next",
        prevEl: ".collection-header-wrap .swiper-button-prev",
    }
});

document.addEventListener("DOMContentLoaded", function () {
    /* 컬렉션 프로젝트 화면 표시처리 */
	writeCOLLECTION_project();
	
	/* URL 기준 컬렉션 표시처리 */
    actionFromUrl();
	
    imgTypeBtn();
	
    scrollTopEventHandler();
	
    scrollTop('auto');
	
    slideClickEvent();
	
    collectionClickEvent();
	
    categoryStickyEvent();
	
    //history.pushState({}, '', '/posting/collection');
});

/* 컬렉션 프로젝트 화면 표시처리 */
function writeCOLLECTION_project() {
	/* 컬렉션 프로젝트 데이터 조회처리 */
	let data_project = getCOLLECTION_project();
    data_project.forEach((el, idx) => {
        let {
			project_idx,
			project_name,
			project_title,
			thumb_location
		} = el;
		
		/* 컬렉션 프로젝트 스와이퍼 설정 */
        let slide_project = setSWIPER_project(project_idx, thumb_location, project_name, idx);
        
		let wrapper_swiper = document.querySelector(".collectionCategory-swiper .swiper-wrapper");
		wrapper_swiper.appendChild(slide_project);
        if (idx === swiper_collection.activeIndex) {
			/* 선택중인 프로젝트 변경처리 */
            change_project(project_name, project_title)
        }
    });
}

/* 컬렉션 프로젝트 데이터 조회처리 */
const getCOLLECTION_project = () => {
    let country = getLanguage();
	
    $.ajax({
        type: "post",
        url: api_location + "posting/collection/project/get",
        headers: {
            "country": country
        },
        async: false,
        dataType: "json",
        error: function () {
            // alert('컬렉션 프로젝트 조회처리중 오류가 발생했습니다.');
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0101", null);
        },
        success: function (d) {
            result = d.data;
        }
    });
	
    return result;
}

/* 컬렉션 프로젝트 스와이퍼 설정 */
function setSWIPER_project(project_idx, src, title, idx) {
    let categorySwiperSlide = document.createElement("div");
	
    let imgHtml = `
		<div class="collectionCategory-box">
			<img src="${cdn_img}${src}" alt="">
		</div>
		
		<span>${title}</span>
    `;
	
    categorySwiperSlide.className = "swiper-slide";
    categorySwiperSlide.dataset.idx = idx;
    categorySwiperSlide.dataset.projectidx = project_idx;

    if (getUrlParamValue('project_idx') == null) {
        if (categorySwiperSlide.dataset.idx == 0) {
            categorySwiperSlide.classList.add("select");
        }
    } else {
        if (categorySwiperSlide.dataset.projectidx == getUrlParamValue('project_idx')) {
            categorySwiperSlide.classList.add("select");
        }
    }
	
    categorySwiperSlide.innerHTML = imgHtml;
    
    return categorySwiperSlide;
}

/* 선택중인 프로젝트 변경처리 */
function change_project(project_name, project_title) {
    document.querySelectorAll('.collection-main__title').forEach(el => el.innerHTML = project_name);
    document.querySelectorAll('.collection-sub__title').forEach(el => el.innerHTML = project_title);
}

function imgTypeBtn() {
    let imgBtn = document.querySelector(".image-type-btn");
    imgBtn.addEventListener("click", function () {
        let theme = document.querySelector(':root');
        let styles = getComputedStyle(theme);
        styles.getPropertyValue('--collectionGrid');
        if (this.dataset.type == "L") {
            theme.style.setProperty('--collectionGrid', 'repeat(6,1fr)')
            theme.style.setProperty('--grid-column', '1/15');
            this.dataset.type = "S";
            this.children[1].innerHTML = "크게보기"
            this.children[1].dataset.i18n = "lb_zoom_in";
            this.children[1].textContent = i18next.t("lb_zoom_in");
            this.children[0].src = "/images/svg/grid-cols-3.svg"
        } else if (this.dataset.type == "S") {
            theme.style.setProperty('--collectionGrid', 'repeat(3,1fr)')
            theme.style.setProperty('--grid-column', '1/17');
            this.dataset.type = "L";
            this.children[1].innerHTML = "작게보기"
            this.children[1].dataset.i18n = "lb_zoom_out";
            this.children[1].textContent = i18next.t("lb_zoom_out");
            this.children[0].src = "/images/svg/grid-cols-6-2.svg"

        }
    })
}

function scrollTopEventHandler() {
    let topBtn = document.querySelector(".collection-top-btn");
    topBtn.addEventListener("click", function () {
        scrollTop('smooth');
    });
}

function scrollTop(behavior_str){
    window.scroll({
        top: 0,
        left: 0,
        behavior: behavior_str
    });
}

function slideClickEvent() {
    let collectionCategory = document.querySelector(".collectionCategory-swiper");
    collectionCategory.addEventListener("click", function (e) {
        scrollTop('auto');
        initDetailSetTimeout();
        
        let slide = document.querySelectorAll(".collectionCategory-swiper .swiper-slide");
		slide.forEach(el => { el.classList.remove("select") });
		e.target.offsetParent.classList.add("select");

        let selectSlide = document.querySelector(".collectionCategory-swiper .swiper-slide.select");
        if(selectSlide != null){
            let param_idx = selectSlide.dataset.idx;
			
			/* 컬렉션 프로젝트 데이터 조회처리 */
            let data_project = getCOLLECTION_project();
            
			let {
				project_idx,
				project_name,
				project_title
			} = data_project[param_idx];
    
            let $collectionResult = document.querySelector(".collection-result");
            $collectionResult.innerHTML = "";
			
			/* 컬렉션 제품 화면 표시처리 */
            writeCOLLECTION_product(project_idx);
            
            let titleBox = writeHTML_project(project_idx,project_name,project_title);
            $collectionResult.insertBefore(titleBox,$collectionResult.firstChild);
			
			/* 선택중인 프로젝트 변경처리 */
            change_project(project_name,project_title);
    
            let virtual_url = `/posting/collection?project_idx=${project_idx}`;
            history.pushState({}, '', virtual_url);
        }
    })
}

function writeHTML_project(project_idx,project_name, project_title) {
    let titleBox = document.createElement("div");
    
	let imgHtml = `
        <div>
            <div class="collection-main__title">${project_name}</div>
            <div class="collection-sub__title">
                ${project_title}
            </div>
        </div> 
    `;

	titleBox.className = "collection-title-box";
    titleBox.innerHTML = imgHtml;
    titleBox.dataset.project = project_idx;
    return titleBox;
}

function initDetailSetTimeout(){
    last_index = 0;
    setTimeoutIdArr.forEach(function(id){
        clearTimeout(id);
    });
    setTimeoutIdArr = [];
}

function allImgFadeIn(data, first_index){
    let current_index = first_index;
    data.forEach((el,index) => {
        let setTimeoutId = setTimeout(function(){
            let imgNode = document.querySelectorAll(`[data-index="${current_index}"] img`)[0];
            imgFadeIn(imgNode);
            current_index++;

        }, 100 * (index));
        setTimeoutIdArr.push(setTimeoutId);
    });
}

function imgFadeIn(el){
    el.style.opacity = 0;
    var tick = function () {
        el.style.opacity = +el.style.opacity + 0.01;
        if (+el.style.opacity < 1) {
            (window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16)
        }
    };
    tick();
}

function collectionClickEvent() {
    let lbResult = document.querySelector('.collection-result');
    
    lbResult.addEventListener('click', function (ev) {
        let project_idx = document.querySelector(".collectionCategory-swiper .swiper-slide.select").dataset.projectidx;
        let c_product_idx = ev.target.offsetParent.getAttribute("product");
        let product_index = ev.target.offsetParent.getAttribute("data-index");

        location.href = `/posting/collection/detail?project_idx=${project_idx}&c_product_idx=${c_product_idx}&product_index=${product_index}`;
    });
       
}

function categoryStickyEvent(){
    let header = document.querySelector('header');
    let main = document.querySelector('main');
    let category = document.querySelector('.collection-header-wrap');
    let prevScrollpos = window.pageYOffset;

    window.onscroll = function() {
        let currentScrollPos = window.pageYOffset;

        if (prevScrollpos > currentScrollPos + 15) {
            // 스크롤을 15만큼 올릴 때
            main.style.overflow = 'initial';
            category.classList.add('stricky');
        } else if (prevScrollpos < currentScrollPos - 15) {
            // 스크롤을 15만큼 내릴 때
            //category.style.top = `${header.offsetHeight}px`;
            main.style.overflow = 'hidden';
            category.classList.remove('stricky');
        }

        prevScrollpos = currentScrollPos;
    };
}

/* URL 기준 컬렉션 표시처리 */
function actionFromUrl(){
	let selector_project = null;
	
    let project_idx = Number(getUrlParamValue('project_idx'));
    if (project_idx == 0 || project_idx == null) {
        selector_project = $(`.collectionCategory-swiper .swiper-slide[data-idx=0]`);
        project_idx = selector_project.attr('data-projectidx');
    } else {
        selector_project = $(`.collectionCategory-swiper .swiper-slide[data-projectidx=${project_idx}]`);
    }

    if (selector_project != null) {
		/* 컬렉션 제품 화면 표시처리 */
        let data_product = writeCOLLECTION_product(project_idx);
		console.log(data_product);
		
        let project_name = null;
        let project_title = null;
		
		if (data_product != null && data_product.length > 0) {
			data_product.forEach(function(row){
				if (row.project_idx == project_idx) {
					project_name = row.project_name;
					project_title = row.project_title;
				}
			});
		}
        
		/* 선택중인 프로젝트 변경처리 */
		change_project(project_name,project_title);
    }
    else{
        return false;
    }
}

/* 컬렉션 제품 화면 표시처리 */
function writeCOLLECTION_product(projectIdx, last_idx) {
    let colection_product = document.querySelector(".collection-result");
	
	/* 컬렉션 제품 데이터 조회처리 */
    let result = getCOLLECTION_product(projectIdx, last_idx);
	
    let current_index = 0;
    let first_index = 0;
    
    if (result != null) {
        let data = result.data;
		
        first_index		= result.first_index;
        current_index	= first_index;
		
        data.forEach((el) => {
            let {
				c_product_idx,
				img_location
			} = el;
			
            let list = writeHTML_product(c_product_idx,img_location,current_index);

            current_index++;
            colection_product.appendChild(list);
        });

        allImgFadeIn(data,first_index);

        const items = document.querySelectorAll('.collection-result .collection');
        const ioCallback = (entries, io) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    let lastIdx = document.querySelectorAll('.collection-result .collection').length;
                    io.unobserve(entry.target);
                    if(last_index != lastIdx){
						/* 컬렉션 제품 화면 표시처리 */
                        writeCOLLECTION_product(projectIdx, lastIdx);
                        last_index = lastIdx;
                    }
                    observeLastItem(io, document.querySelectorAll('.collection-result .collection'));
                }
            });
        };
		
        const observeLastItem = (io, items) => {
            const lastItem = items[items.length - 1];
            io.observe(lastItem);
            return lastItem;
        };
		
        observe_collection = new IntersectionObserver(ioCallback, { threshold: 0.7 });
        observeLastItem(observe_collection, items);
    } else {
		if (observe_collection != null) {
			observe_collection.disconnect();
		}
    }
	
    $(".collection-body").css('min-height','0');
}

/* 컬렉션 제품 데이터 조회처리 */
const getCOLLECTION_product = (project_idx, last_idx) => {
    let result = null;
	
    $.ajax({
        type: "post",
        url: api_location + "posting/collection/product/list/get",
        headers: {
        	"country": getLanguage()
        },
        data: {
            'project_idx': project_idx,
            'last_idx': last_idx,
        },
        async: false,
        dataType: "json",
        error: function () {
            // alert('컬렉션 상품 이미지 조회처리중 오류가 발생했습니다.');
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0104", null);
			
            if (observe_collection != null) {
				observe_collection.disconnect();
			}
        },
        success: function (d) {
            result = d;
        }
    });
	
    return result;
};

function writeHTML_product(c_product_idx,img_location,idx) {
    let collection = document.createElement("div");
    
	let img_html = `
		<img idx="${c_product_idx}" src="${cdn_img}${img_location}" alt="">
	`;
	
    collection.className = "collection";
    collection.innerHTML = img_html;
    collection.dataset.index = idx;
    collection.setAttribute('product', c_product_idx);
	
    return collection;
}