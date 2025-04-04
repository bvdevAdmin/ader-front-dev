let map;
let centerCoordinates = { lat: 37.5400456, lng: 126.9921017 };
let storeShopData;
let mapMarkers;
let markers = []; // 마커 객체들을 저장하는 전역 배열 변수
const searchInput = document.querySelector('#store-search-input');
const clear = document.querySelector('.clear-btn');

document.addEventListener("DOMContentLoaded", function () {  

    searchInput.removeEventListener('input', searchInputEvent);
    searchInput.addEventListener('input', searchInputEvent);
    
    clear.removeEventListener('click', clearBtnClickEvent);
    clear.addEventListener('click', clearBtnClickEvent);
    
    initStore();
    window.initMap = initMap;
});

function searchInputEvent(){
    searchInput.value = searchInput.value.replace(/[^ㄱ-ㅎ가-힣A-Za-z0-9,-]+/g, '');
    debounce(searchResult(searchInput.value), 500);

}
function clearBtnClickEvent(){
    searchInput.value = "";
    console.log(searchInput.value);
    initStore();
    initMap(null);
}

function initMap(data) {
    let coordinates = {
        lat: 37.5400456,
        lng: 126.9921017
    };
    if (data != null) {
        let spaceInfo = data.space_info;
        let plugshopInfo = data.plugshop_info;
        let stockistInfo = data.stockist_info;

        if (spaceInfo.length > 0) {
            coordinates.lat = parseFloat(spaceInfo[0].lat);
            coordinates.lng = parseFloat(spaceInfo[0].lng);
        } else if (plugshopInfo.length > 0) {
            coordinates.lat = parseFloat(plugshopInfo[0].lat);
            coordinates.lng = parseFloat(plugshopInfo[0].lng);
        } else if (stockistInfo.length > 0) {
            coordinates.lat = parseFloat(stockistInfo[0].lat);
            coordinates.lng = parseFloat(stockistInfo[0].lng);
        } else {
            coordinates.lat = 37.5400456;
            coordinates.lng = 126.9921017;
        }
    } else {
        coordinates = {
            lat: 37.5400456,
            lng: 126.9921017
        };
    }

    //아이콘
    //말품선객체
    // const infoWindow = new google.maps.InfoWindow();
    //검색 디바운스
    // const searchInput = document.querySelector('#store-search-input');
    // searchInput.addEventListener('input', debounce(searchInputEvent, 500));
    // 지도 테마 객체
    const grayStyle = [{
        "elementType": "geometry",
        "stylers": [{
            "color": "#f5f5f5"
        }]
    },
    {
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    },
    {
        "elementType": "labels.text.fill",
        "stylers": [{
            "color": "#616161"
        }]
    },
    {
        "elementType": "labels.text.stroke",
        "stylers": [{
            "color": "#f5f5f5"
        }]
    },
    {
        "featureType": "administrative.land_parcel",
        "elementType": "labels.text.fill",
        "stylers": [{
            "color": "#bdbdbd"
        }]
    },
    {
        "featureType": "poi",
        "elementType": "geometry",
        "stylers": [{
            "color": "#eeeeee"
        }]
    },
    {
        "featureType": "poi",
        "elementType": "labels.text.fill",
        "stylers": [{
            "color": "#757575"
        }]
    },
    {
        "featureType": "poi.park",
        "elementType": "geometry",
        "stylers": [{
            "color": "#e5e5e5"
        }]
    },
    {
        "featureType": "poi.park",
        "elementType": "labels.text.fill",
        "stylers": [{
            "color": "#9e9e9e"
        }]
    },
    {
        "featureType": "road",
        "elementType": "geometry",
        "stylers": [{
            "color": "#ffffff"
        }]
    },
    {
        "featureType": "road.arterial",
        "elementType": "labels.text.fill",
        "stylers": [{
            "color": "#757575"
        }]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry",
        "stylers": [{
            "color": "#dadada"
        }]
    },
    {
        "featureType": "road.highway",
        "elementType": "labels.text.fill",
        "stylers": [{
            "color": "#616161"
        }]
    },
    {
        "featureType": "road.local",
        "elementType": "labels.text.fill",
        "stylers": [{
            "color": "#9e9e9e"
        }]
    },
    {
        "featureType": "transit.line",
        "elementType": "geometry",
        "stylers": [{
            "color": "#e5e5e5"
        }]
    },
    {
        "featureType": "transit.station",
        "elementType": "geometry",
        "stylers": [{
            "color": "#eeeeee"
        }]
    },
    {
        "featureType": "water",
        "elementType": "geometry",
        "stylers": [{
            "color": "#c9c9c9"
        }]
    },
    {
        "featureType": "water",
        "elementType": "labels.text.fill",
        "stylers": [{
            "color": "#9e9e9e"
        }]
    }
    ];

    if (isNaN(coordinates.lat) || isNaN(coordinates.lng)) {
        centerCoordinates = {
            lat: 37.5400456,
            lng: 126.9921017
        }
    } else {
        centerCoordinates = {
            lat: coordinates.lat,
            lng: coordinates.lng
        }
    };

    var request = {
        location: centerCoordinates,
        radius: '5000',
        query: ''
    };
    //지도 초기화 
    map = new google.maps.Map(document.getElementById("map"), {
        center: request.location,
        zoom: 12,
        styles: grayStyle
        // panControl: false,
        // // zoomControl: true,
        // mapTypeControl: false,
        // scaleControl: true,
        // streetViewControl: false,
        // overviewMapControl: false,
    });
    const service = new google.maps.places.PlacesService(map);

    // map.fitBounds(bounds);
}

async function initmarker(data) {
    const bounds = new google.maps.LatLngBounds();

    const newData = [];
    const beachFlagImg = "/images/svg/map-marker.svg";

    if (Array.isArray(data.space_info) && data.space_info.length) {
        data.space_info.forEach(({ store_idx, lat, lng, store_type }) => {
            newData.push({
                store_idx: store_idx,
                lat: lat,
                lng: lng,
                store_type: store_type
            });
        });

    }
    if (Array.isArray(data.plugshop_info) && data.plugshop_info.length) {
        data.plugshop_info.forEach(({ store_idx, lat, lng, store_type }) => {
            newData.push({
                store_idx: store_idx,
                lat: lat,
                lng: lng,
                store_type: store_type
            });
        });
    }
    if (Array.isArray(data.stockist_info) && data.stockist_info.length) {
        data.stockist_info.forEach(({ store_idx, lat, lng, store_type }) => {
            newData.push({
                store_idx: store_idx,
                lat: lat,
                lng: lng,
                store_type: store_type
            });
        });
    }


    newData.forEach(({ store_idx, lat, lng, store_type }, idx) => {
        lng = parseFloat(lng)
        lat = parseFloat(lat)
        const marker = new google.maps.Marker({
            position: { lat, lng },
            map,
            icon: beachFlagImg,
            idx: store_idx,
            scaledSize: {
                width: 100,
                height: 100
            },
            store_type: store_type

        });
        bounds.extend(marker.position);
        const closeBtn = document.createElement('div');

        closeBtn.className = 'map-open__close-btn';
        closeBtn.innerHTML =
            `
            <svg xmlns="http://www.w3.org/2000/svg" width="12.707" height="12.707" viewBox="0 0 12.707 12.707">
                <path data-name="선 1772" transform="rotate(135 6.103 2.736)" style="fill:none;stroke:#343434" d="M16.969 0 0 .001"></path>
                <path data-name="선 1787" transform="rotate(45 -.25 .606)" style="fill:none;stroke:#343434" d="M16.969.001 0 0"></path>
            </svg>
        `;

        marker.addListener("click", function (e) {

            mobileOpenResult();
            map.panTo(marker.position);
            const banners = document.querySelectorAll('.banner');
            banners.forEach(el => {
                if (this.idx == el.dataset.store_idx && this.store_type == el.dataset.store_type) {
                    let cloneNode = el.cloneNode(true);
                    if (window.matchMedia('screen and (min-width:1025px)').matches === true) {
                        document.querySelector('#web-detail-wrap').innerHTML = '';
                        document.querySelector('#web-detail-wrap').appendChild(cloneNode);
                        document.querySelector('#web-detail-wrap').appendChild(closeBtn);
                        closeBtn.addEventListener('click', function () {
                            document.querySelector('#web-detail-wrap').innerHTML = '';
                        })
                    } else if (window.matchMedia('screen and (min-width:1025px)').matches === false) {
                        document.querySelector('#store-mobile-modal').innerHTML = '';
                        document.querySelector('#store-mobile-modal').appendChild(cloneNode);
                        document.querySelector('#store-mobile-modal .banner').appendChild(closeBtn);
                        closeBtn.addEventListener('click', function () {
                            document.querySelector('#dimmer').classList.remove('show');
                            document.querySelector('#store-mobile-modal').innerHTML = '';
                            document.querySelector('#store-mobile-modal').classList.remove('open');
                        })
                    };

                }
            })

            detailBtnEvent();
        });
        markers.push(marker);
    });
}

async function initStore(data) {
    const searchInput = document.querySelector('#store-search-input');

    // 현재 위치로 검색하기 - HTTPS에서만 작동
    // currentLocationBtn.addEventListener('click', function() {
    //     let infoWindow = new google.maps.InfoWindow();

    //     if(navigator.geolocation) {
    //         navigator.geolocation.getCurrentPosition((position) => {
    //             const pos = {
    //                 lat: position.coords.latitude,
    //                 lng: position.coords.longitude,
    //             }
    //             infoWindow.setPosition(pos);
    //             infoWindow.setContent("Location found.");
    //             infoWindow.open(map);
    //             map.setCenter(pos);
    //         }, 
    //         () => {
    //             handleLocationError(true, infoWindow, map.getCenter());
    //         });
    //     } else {
    //         handleLocationError(false, infoWindow, map.getCenter());
    //     }
    // });

    // function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    //     infoWindow.setPosition(pos);
    //     infoWindow.setContent(
    //       browserHasGeolocation
    //         ? "Error: The Geolocation service failed."
    //         : "Error: Your browser doesn't support geolocation."
    //     );
    //     infoWindow.open(map);
    // };

    try {
        if (data === undefined) {
            storeShopData = await getSearchStoreList();
            await initmarker(storeShopData);
        } else {
            storeShopData = data;
            deleteMarker();
            await initmarker(storeShopData);
        }

        let { space_info, plugshop_info, stockist_info } = storeShopData;

        document.querySelector('.store-section.brand-store .store-body').innerHTML = '';
        document.querySelector('.store-section.plug-store .store-body').innerHTML = '';

        if (space_info.length > 0) {
            space_info.forEach(space => {
                document.querySelector('.store-section.brand-store .store-body').appendChild(makeSpaceHtml(space));
                document.querySelector('.store-section.brand-store .store-title').style.display = 'block';
                document.querySelector('.store-section.brand-store .store-subtitle').style.display = 'block';
            });
        } else {
            document.querySelector('.store-section.brand-store .store-title').style.display = 'none';
            document.querySelector('.store-section.brand-store .store-subtitle').style.display = 'none';
        }

        if (plugshop_info.length > 0) {
            plugshop_info.forEach(plug => {
                document.querySelector('.store-section.plug-store .store-body').appendChild(makePlugHtml(plug));
                document.querySelector('.store-section.plug-store .store-subtitle').style.display = 'block';
            });
        } else {
            document.querySelector('.store-section.plug-store .store-subtitle').style.display = 'none';
        }
        stockistCountry(stockist_info);
        detailBtnEvent();
        changeLanguageR();
        document.querySelector('.store-section.stockist-store .store-title').style.display = 'none';
    } catch (error) {
        console.error(error);
    }
}

function mobileOpenResult() {
    if (window.matchMedia('screen and (min-width:1025px)').matches === true) {
        document.querySelector('#dimmer').classList.remove('show');
        document.querySelector('#store-mobile-modal').classList.remove('open');
    } else if (window.matchMedia('screen and (min-width:1025px)').matches === false) {
        document.querySelector('#dimmer').classList.add('show');
        document.querySelector('#store-mobile-modal').classList.add('open');
    };
}

const getStoreLocationList = () => {
    $.ajax({
        type: "post",
        url: api_location + "store/location/get",
        async: false,
        dataType: "json",
        error: function () {
            // alert('점포 조회처리중 오류가 발생했습니다.');
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0100", null);
        },
        success: function (d) {
            result = d.data;
        }
    });
    return result;
}

// 검색결과 반환
const getSearchStoreList = async (search) => {

    let search_data = search === undefined ? '' : search;

    try {
        const response = await $.ajax({
            type: "post",
            url: api_location + "store/list/get",
            headers: {
	            "country": getLanguage()
	        },
            data: {
                'store_keyword': search_data
            },
            dataType: "json"
        });
        return response.data;
    } catch (error) {
        console.error('점포 조회처리중 오류가 발생했습니다.', error);
        throw error;
    }
}
async function searchResult(val) {
    let data = val;

    let result;

    try {
        result = await getSearchStoreList(data);

        document.querySelector('#web-detail-wrap').innerHTML = '';
        document.querySelector('#store-mobile-modal').innerHTML = '';
        changeLanguageR();
        initStore(result);
        // 20230406 윤재은

        initMap(result);
        initmarker(result);
        
    } catch (error) {
        console.error(error);
    }
}

function mobileModalCloseBtn() {
    let closeBtn = document.querySelector('.map-open__close-btn');
    closeBtn.addEventListener('click', modalClose);

    function modalClose() {
        document.querySelector("#store-mobile-modal").classList.remove('open');
    }

}

function detailBtnEvent() {
    let clickAbleObj = document.querySelectorAll('.detail-view-btn, .banner .banner-img');

    clickAbleObj.forEach(el => el.addEventListener('click', function (ev) {
        let storeIdx = parseInt(ev.target.closest('article').dataset.store_idx);
        let storeType = ev.target.closest('article').dataset.store_type;

        location.href = `/search/shop/detail?storeIdx=${storeIdx}&storeType=${storeType}`;
    }));
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
    spaceArticle.innerHTML = innerHtml;
    spaceArticle.dataset.store_idx = store_idx;
    spaceArticle.dataset.store_type = store_type;
    return spaceArticle;
}

function makeStockistHtml(data) {
    let {
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
    let stockistArticle = document.createElement("article");
    let innerHtml = "";



    if (country == '온라인' || country == 'Online') {
        let storeAddrHtml = "";

        if (store_name == 'WeChat') {
            storeAddrHtml = `
                <div class="store_addr-box">
                    <div class="store_link wechat_qr_btn">QR</div>
                </div>
            `;
        } else {
            storeAddrHtml = `
                <div class="store_addr-box">
                    <a class="store_link" href="https://${store_link}/" target="_blank" rel="noopener noreferrer">${store_link}</a>
                </div>
            `;
        }

        innerHtml = `
            <div class="store-name-box">
                <div class="store_name">${store_name}</div>
            </div>
            ${storeAddrHtml}
        `;
    } else {
        innerHtml =
            `
            <div class="store-name-box">
                <div class="store_name">${store_name}</div>
                <a class="instagram-logo" href="https://www.instagram.com/${instagram_id}/" target='_blank'><img src="/images/svg/store-instagram.svg" alt=""></a>
            </div>
            <div class="store_addr-box">
                <div class="store_addr">${store_addr}</div>
                <a href="https://google.com/maps/?q=${lat},${lng}" target='_blank'><div class="addr-svg" data-link=""><img src="/images/svg/store-addr.svg" alt=""><span data-i18n="ss_view_location">위치 보기</span></div></a>
            </div>
            <div class="store_tel">${store_tel === null ? '' : store_tel}<img src="/images/svg/store-phone.svg" alt=""></div>
        `;
    }

    stockistArticle.className = "banner";
    stockistArticle.innerHTML = innerHtml;
    stockistArticle.dataset.country = country;
    stockistArticle.dataset.store_idx = store_idx;
    stockistArticle.dataset.store_type = store_type;
    return stockistArticle;
}
function stockistCountry(data) {
    let countrySet = new Set();
    let stockistArr = [];
    data.forEach((el) => {
        countrySet.add(el.country)
        stockistArr.push(makeStockistHtml(el));
    });
    let countryArr = Array.from(countrySet);

    let stockistFlag = document.createDocumentFragment();

    let titleDiv = document.createElement("div");
    titleDiv.className = 'store-title';
    titleDiv.innerHTML = '스톡키스트';
    titleDiv.dataset.i18n = "ss_stockist";
    stockistFlag.appendChild(titleDiv);

    countryArr.forEach(el => {
        let stockistWrap = document.createElement("div");
        stockistWrap.className = 'stockist-country-wrap';
        let stockistCountry = document.createElement("div");
        let stockistBody = document.createElement("div");
        stockistCountry.className = 'stockist-country-header';
        stockistBody.className = 'stockist-country-body';
        stockistCountry.innerHTML = el;
        stockistBody.dataset.country = el;

        stockistWrap.appendChild(stockistCountry);
        stockistWrap.appendChild(stockistBody);
        stockistFlag.appendChild(stockistWrap);
    });

    stockistArr.forEach(el => {
        let $$flagCountry = stockistFlag.querySelectorAll('.stockist-country-body');
        $$flagCountry.forEach(cn => {
            if (el.dataset.country == cn.dataset.country) {
                cn.appendChild(el);
            }
        })
    });
    document.querySelector('.store-section.stockist-store').innerHTML = '';
    document.querySelector('.store-section.stockist-store').appendChild(stockistFlag);
    showWechatQr();
}
function deleteMarker() {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
}
function showWechatQr() {
    let qrBtn = document.querySelector(".store_link.wechat_qr_btn");
    let qrImg = document.querySelector(".search-shop-wrap.wechat_qr");

    qrBtn.forEach(btn => {
        btn.addEventListener("click", function () {
            qrImg.classList.remove("hidden");
        });
    });

    qrImg.addEventListener("click", function () {
        qrImg.classList.add("hidden");
    });
} 
