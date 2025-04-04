const param_project_idx = getUrlParamValue('project_idx');
const param_c_product_idx = getUrlParamValue('c_product_idx');
const param_product_index = getUrlParamValue('product_index');
let timer = null;

window.addEventListener('resize', function () {
    clearTimeout(timer);
    timer = setTimeout(function () {
        responsive();
    }, 300);
});

document.addEventListener("DOMContentLoaded", function () {
    initTitleBox(param_project_idx);
    initCollectionDetailPage();
    backBtn();
    responsive();

});

function initTitleBox(project_idx){
    let country = getLanguage();
    let project_list = getCollectionProjectList(country);

    let project_name = '';
    let project_title = '';

    project_list.forEach(function(el){
        if(el.project_idx == project_idx){
            project_name = el.project_name;
            project_title = el.project_title;
        }
    })

    document.querySelector('.collection-main__title').innerText = project_name;
    document.querySelector('.collection-sub__title').innerText = project_title;
}

const getCollectionProjectList = () => {
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

function responsive() {
    let breakpoint = window.matchMedia('screen and (max-width:1025px)');
    if (breakpoint.matches === true) {
        document.getElementById("related-wrap").classList.add("mobile");
        document.getElementById("related-wrap").classList.remove("web");
    } else if (breakpoint.matches === false) {
        document.getElementById("related-wrap").classList.add("web");
        document.getElementById("related-wrap").classList.remove("mobile");
    }
}

function initCollectionDetailPage() {
    let detailData = getCollectionProduct(param_project_idx);
    let relevantData = getRelevantProduct(param_c_product_idx);

    appendDetailSwiper(detailData);
    appendRelated(relevantData);

    collectionDetailSwiper.slideTo(param_product_index);
}

const getCollectionProduct = (project_idx) => {
    let result;
    $.ajax({
        type: "post",
        url: api_location + "posting/collection/product/get",
        data: {
            'project_idx': project_idx
        },
        async: false,
        dataType: "json",
        error: function () {
            // alert('컬렉션 상품 개별 조회처리중 오류가 발생했습니다.');
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0102", null);
        },
        success: function (d) {
            result = d.data;
        }
    })
    return result;
}

const getRelevantProduct = (c_product_idx) => {
    let country = getLanguage();
    
    let result;
    $.ajax({
        type: "post",
        url: api_location + "posting/collection/relevant/get",
        headers: {
        	"country": country
        },
        data: {
            'c_product_idx': c_product_idx
        },
        async: false,
        dataType: "json",
        error: function () {
            // alert('컬렉션 관련상품 조회처리중 오류가 발생했습니다.');
            makeMsgNoti(country, "MSG_F_ERR_0103", null);
        },
        success: function (d) {
            result = d.data;
        }
    })
    return result;
}

let collectionDetailSwiper  = new Swiper(".collection-detail-swiper", {
    slidesPerView: 'auto',
    slidesPerView: 1,
    navigation: {
        nextEl: ".collection-detail-wrap .swiper-button-next",
        prevEl: ".collection-detail-wrap .swiper-button-prev",
    },
    pagination: {
        el: '.collection-detail-wrap .swiper-pagination',
        type: 'fraction',
    },
    on:{
        slideChangeTransitionEnd: function () {
            let idx = this.realIndex;
            console.log("🏂 ~ file: collection-detail.js:491 ~ idx:", idx)
            this.slides.forEach(function(el){
                if(el.classList.contains('swiper-slide-active')){
                    let c_product_idx = $(el).attr('product');
                    let relevantData = getRelevantProduct(c_product_idx);
                    appendRelated(relevantData);
                    relatedSwiper.slideTo(0);
                    //let virtual_url = `/posting/collection/detail?project_idx=${param_project_idx}&c_product_idx=${c_product_idx}&product_index=${idx}`;
                    //history.pushState({}, '', virtual_url);
                    return false;
                }
            })
        }
    }
});

let relatedSwiper = new Swiper(".related-product-swiper", {
    slidesPerView: 'auto',
    spaceBetween: 10,
    navigation: {
        nextEl: "#related-wrap .swiper-button-next",
        prevEl: "#related-wrap .swiper-button-prev",
    },
    breakpoints: {
        1024: {
            slidesPerView: 3
        }
    }
})

function appendDetailSwiper(data) {
    let collectionDetailSwiperWrapper = document.querySelector(".collection-detail-swiper .swiper-wrapper");
    collectionDetailSwiperWrapper.innerHTML= "";
    data.forEach(el => {
        let {
            c_product_idx,
            img_url
        } = el;
        let slide = makeDetailSlide(c_product_idx, img_url);
        collectionDetailSwiperWrapper.appendChild(slide);
    })
}

function makeDetailSlide(c_product_idx ,src) {
    let detailSwiperSlide = document.createElement("div");
    let imgHtml = ` <div class="collection-detail" product=${c_product_idx}>
                        <img src="${src}" alt="">
                    </div>
    `;
    detailSwiperSlide.className = "swiper-slide";
    detailSwiperSlide.setAttribute('product', c_product_idx);
    detailSwiperSlide.innerHTML = imgHtml;
    return detailSwiperSlide;
}

function backBtn() {
    let backBtn = document.querySelectorAll(".back-btn");
    backBtn.forEach(el => {
        el.addEventListener("click", function () {
            location.href = `/posting/collection?project_idx=${param_project_idx}`;
        });
    });
}

function appendRelated(data) {
    let relatedSwiperWrapper = document.querySelector(".related-product-swiper .swiper-wrapper");
    relatedSwiperWrapper.innerHTML= "";
    if(data !== undefined){
        document.querySelector("#related-wrap").style.display="block";
        data.forEach(el => {
            let {
                product_idx,
                img_location,
                product_name,
                stcl_flg,
                whish_flg
            } = el;
            
            let wish_btn_html = writeWishBtn(product_idx,whish_flg);
            let slide = makeRelatedSlide(product_idx,img_location, product_name, stcl_flg, wish_btn_html);
            
            relatedSwiperWrapper.appendChild(slide);
        });
        
        relatedSwiper.update();
        
        clickBtnUpdateWish();
    } else {
        document.querySelector("#related-wrap").style.display="none";
    }
}

function makeRelatedSlide(product_idx,img_location,title,stcl_flg,wish_btn_html) {
    let relatedSwiperSlide = document.createElement("div");
    
    let red_dot = stcl_flg==true?`<div class="red-dot"></div>`:'';
    let imgHtml = `
		<div class="related-box">
			<img src="${cdn_img}${img_location}" alt="" onclick="location.href='/product/detail?product_idx=${product_idx}'">
			${wish_btn_html}
		</div>
		<span class="related-title" >
			${title}${red_dot}
		</span>
    `;
    
    relatedSwiperSlide.className = "swiper-slide";
    relatedSwiperSlide.innerHTML = imgHtml;
    
    return relatedSwiperSlide;
}

function writeWishBtn(product_idx,whish_flg) {
	let wish_btn_html = "";
	
	let whish_img = "";
	
	let txt_dataset = `data-location="collection" data-wish_flg="${whish_flg}" data-product_idx="${product_idx}"`;
	
	let login_status = getLoginStatus();
	if (login_status == "true") {
		if (whish_flg == true) {
			whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist-bk.svg" alt="">`;
		} else if (whish_flg == false) {
			whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist.svg" alt="">`;
		}
	} else {
		whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist.svg" alt="">`;
	}
	
	wish_btn_html = `
		<div class="wish__btn btn_update_wish" product_idx="${product_idx}" ${txt_dataset}>
			${whish_img}
		</div>
	`;

	return wish_btn_html;
}