const paramStoreIdx = getUrlParamValue('storeIdx');
const paramStoreType = getUrlParamValue('storeType');
var storeShopData;

document.addEventListener("DOMContentLoaded", function () {
    backPageEventHandler();
    storeShopData = getStoreList();
    renderDetailWrap(paramStoreIdx, paramStoreType);
});

function backPageEventHandler() {
    let storeBtn = document.querySelectorAll(".find-store-btn .to-find-store");
    
    storeBtn.forEach(btn => {
        btn.addEventListener('click',function(){
            location.href = "/search/shop";
        });
    });
}

const getStoreList = () => {
    $.ajax({
        type: "post",
        url: api_location + "store/detail/get",
        headers: {
            "country": country
        },
        async: false,
        dataType: "json",
        error: function () {
            // alert('매장 조회처리중 오류가 발생했습니다.');
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0099", null);
        },
        success: function (d) {
            if(d.code == 200){
                if(!(d.data != null)){
                    return false;
                }
            }
            else{
                return false;
            }
            result = d.data;
        }
    });
    return result;
}

function renderDetailWrap(storeIdx,storeType){
    const result = document.querySelector('#result-article');
    const findStoreBtn = document.querySelector('.find-store-btn div');
    let clicked_item;
    if(storeType == 'SPC'){
        clicked_item = storeShopData.space_info.find(findStoreToIdx);;
    }else if(storeType == 'PLG' ) {
        clicked_item = storeShopData.plugshop_info.find(findStoreToIdx);
    }
    let main = new MainResult(clicked_item);
    let side = new SideResult(storeShopData.space_info, storeShopData.plugshop_info, storeShopData.stockist_info, storeIdx, storeType);

    main.loadContent();
    side.loadSpaceInfo();
    side.loadPlugshopInfo();
    let video = new Vctrbox('.vplayer')

    detailBtnEvent(storeShopData);
    result.classList.remove('hidden');
    document.querySelector('body').style.overflow = 'hidden';

    function findStoreToIdx(el){
        if(el.store_idx == storeIdx){
            return true;
        } 
    }
}

class MainResult {
    constructor(info) {
        this.info = info;
    }
    getContent() {
        return this.info
    }
    loadContent(){
        document.querySelector('#result-article .store-section.main .store-body').innerHTML = '';
        if(this.info.store_type == "SPC"){
            document.querySelector('#result-article .store-section.main .store-body').appendChild(makeMainResultHtml(this.info));
        }else if(this.info.store_type == "PLG"){
            document.querySelector('#result-article .store-section.main .store-body').appendChild(makeMainResultHtml(this.info));
        }
        let storeResultSwiper = new Swiper(".storeResult-swiper", {
            slidesPerView: 'auto',
            slidesPerView: 1,
            navigation: {
                nextEl: ".storeResult-swiper .swiper-button-next",
                prevEl: ".storeResult-swiper .swiper-button-prev",
            }
        })
    }
    display() {
        
    }
}
class SideResult {
    constructor(space_param, plugshop_param, stockist_info, clicked_idx, clicked_type) {
        this.stockist_info = stockist_info;
        this.clicked_idx = clicked_idx;
        this.clicked_type = clicked_type;
        this.space_info = [];
        this.plugshop_info = [];

        setStoreExceptClicked(this.space_info, space_param);
        setStoreExceptClicked(this.plugshop_info, plugshop_param);

        function setStoreExceptClicked(empty_arr, shop_info){
            let shop_cnt = shop_info.length;
            for(let i = 0; i < shop_cnt; i++){
                if(shop_info[i].store_idx != clicked_idx || shop_info[i].store_type != clicked_type){
                    empty_arr.push(shop_info[i]);
                }
            }
        }
        
    }
    getSpaceInfo() {
        return this.space_info;
    }
    getPlugshopInfo() {
        return this.plugshop_info;
    }
    loadSpaceInfo(){
        let spaceInfo = this.space_info
        document.querySelector('#result-article .store-section.brand-store .store-body').innerHTML = '';
        spaceInfo.forEach(space => {
            document.querySelector('#result-article .store-section.brand-store .store-body').appendChild(makeSpaceHtml(space));
        });
    }
    loadPlugshopInfo(){
        let plugshopInfo = this.plugshop_info
        document.querySelector('#result-article .store-section.plug-store .store-body').innerHTML = '';
        plugshopInfo.forEach(plug => {
            document.querySelector('#result-article .store-section.plug-store .store-body').appendChild(makePlugHtml(plug));
        });
    }
    
    display() {
    }
}

function makeSpaceHtml(data) {
    let {
        contents_info,
        country,
        instagram_id,
        store_addr,
        store_idx,
        store_link,
        store_name,
        store_sale_date,
        store_tel,
        store_type,
        lat,
        lng
    } = data;
    let spaceArticle = document.createElement("article");
    let innerHtml =
    `
        <figure>
            <img class="banner-img" data-src="${contents_info[0].contents_location}" src="${cdn_img}${contents_info[0].contents_location}">
            <figcaption>
                <div class="store-name-box">
                    <div class="store_name">${store_name}</div>
                    <a class="instagram-logo" href="https://www.instagram.com/${instagram_id}/" target='_blank'><img src="/images/svg/store-instagram.svg" alt=""></a>
                </div>
                <div class="store_addr-box">
                    <div class="store_addr">${store_addr}</div>
                    <a href="https://google.com/maps/?q=${lat},${lng}" target='_blank'><div class="addr-svg" data-link=""><img src="/images/svg/store-addr.svg" alt=""><span data-i18n="ss_view_location">위치 보기</span></div></a>
                </div>
                <div class="store_tel">${store_tel}<img src="/images/svg/store-phone.svg" alt=""></div>
                <div class="store_open_date">${store_sale_date}</div>
            </figcaption>
        </figure>
        <div class="detail-view-btn" data-i18n="ss_view_detail"></div>
    `;
    spaceArticle.className = "banner";
    spaceArticle.dataset.store_idx = store_idx;
    spaceArticle.dataset.store_type = store_type;
    spaceArticle.innerHTML = innerHtml;
    return spaceArticle;
}
function makePlugHtml(data) {
    let {
        contents_info,
        country,
        instagram_id,
        store_addr,
        store_idx,
        store_link,
        store_name,
        store_sale_date,
        store_tel,
        store_type,
        lat,
        lng
    } = data;
    let spaceArticle = document.createElement("article");
    let innerHtml =
        `
            <figure>
                <img class="banner-img" data-src="${contents_info[0].contents_location}" src="${cdn_img}${contents_info[0].contents_location}" >
                <figcaption>
                    <div class="store-name-box">
                        <div class="store_name">${store_name}</div>
                        <a class="instagram-logo" href="https://www.instagram.com/${instagram_id}/"><img src="/images/svg/store-instagram.svg" alt=""></a>
                    </div>
                    <div class="store_addr-box">
                        <div class="store_addr">${store_addr}</div>
                        <a href="https://google.com/maps/?q=${lat},${lng}" target='_blank'><div class="addr-svg" data-link=""><img src="/images/svg/store-addr.svg" alt=""><span data-i18n="ss_view_location">위치 보기</span></div></a>
                    </div>
                    <div class="store_tel">${store_tel}<img src="/images/svg/store-phone.svg" alt=""></div>
                    <div class="store_open_date">${store_sale_date}</div>
                </figcaption>
            </figure>
            <div class="detail-view-btn" data-i18n="ss_view_detail"></div>
        `;
    spaceArticle.className = "banner";
    spaceArticle.innerHTML = innerHtml;
    spaceArticle.dataset.store_idx = store_idx;
    spaceArticle.dataset.store_type = store_type;
    return spaceArticle;
}

function detailBtnEvent(data) {
    let $$detailViewBtn = document.querySelectorAll('.detail-view-btn');
    let $$detailViewPic = document.querySelectorAll('.banner .banner-img');

    const result = document.querySelector('#result-article');
    
    $$detailViewBtn.forEach(el => el.addEventListener('click', function(e) {
        let storeIdx = parseInt(e.target.parentElement.dataset.store_idx);
        let storeType = e.target.parentElement.dataset.store_type;
        let top = document.querySelector("#result-article");
        
        renderDetailWrap(storeIdx,storeType);
        top.scrollTo(0,0);
        changeLanguageR();
    }));

    $$detailViewPic.forEach(el => el.addEventListener('click', function(e) {
        let storeIdx = parseInt(e.target.parentElement.parentElement.dataset.store_idx);
        let storeType = e.target.parentElement.parentElement.dataset.store_type;
        let top = document.querySelector("#result-article");

        renderDetailWrap(storeIdx,storeType);
        top.scrollTo(0,0);
        changeLanguageR();
    }));
}

function makeMainResultHtml(data) {
    let {
    	contents_info,
    	country,
    	instagram_id,
    	store_addr,
    	store_idx,
    	store_link,
    	store_name,
    	store_sale_date,
    	store_tel,
    	store_type,
    	lat,
    	lng
    } = data;
    
    let backgroundHtml = '';
    let spaceArticle = document.createElement("article");
    
    spaceArticle.className = "banner";
    spaceArticle.dataset.store_idx = store_idx;
    spaceArticle.dataset.store_type = store_type;
    
    let imgdata = contents_info;
    imgdata.forEach((el,idx) => {
        let backgroundType = el.contents_location.split('.', 2)[1];
        if (backgroundType === "mp4") {
            backgroundHtml += `
	            <div class="swiper-slide vplayer">
	                <video id="video-coustom-${idx}" autoplay muted loop playsinline src="${cdn_img}${el.contents_location}" "></video>
	            </div>
            `;
        } else {
            backgroundHtml +=`
                <div class="swiper-slide">
                    <img class="object-fit" src="${cdn_img}${el.contents_location}" ">
                </div>
            `;
        }
    });
    
    let innerHtml = `
        <div class="storeResult-swiper swiper">
            <div class="swiper-wrapper">
                ${backgroundHtml}
            </div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
        </div>
        <figure>
            <figcaption>
                <div class="store-name-box">
                    <div class="store_name">${store_name}</div>
                    <a href="https://www.instagram.com/${instagram_id}/"><img src="/images/svg/store-instagram.svg" alt=""></a>
                </div>
                <div class="store_addr-box">
                    <div class="store_addr">${store_addr}</div>
                    <div class="addr-svg" data-link=""><a class="store_addr_link" href="https://google.com/maps/?q=${lat},${lng}"><img src="/images/svg/store-addr.svg" alt=""><span data-i18n="ss_view_location">위치 보기</span></a></div>
                </div>
                <div class="store_tel">${store_tel}<img src="/images/svg/store-phone.svg" alt=""></div>
                <div class="store_open_date">${store_sale_date}</div>
            </figcaption>
        </figure>
    `;
    
    spaceArticle.innerHTML = innerHtml;
    
    return spaceArticle;
}

