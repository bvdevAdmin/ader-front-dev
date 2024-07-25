$(document).ready(function() {
    /** TERMS **/
    if($("body > main.terms").length > 0) {
        if ($("li#store-guide").hasClass("on")) {
            termsAPI('policy/page/guidance/get');
        }
        else if ($("li#terms-of-use").hasClass("on")) {
            termsAPI('policy/page/terms/get');
        }
        else if ($("li#privacy-policy").hasClass("on")) {
            termsAPI('policy/page/privacy/get');
        }
        else if ($("li#cookie-policy").hasClass("on")) {
            termsAPI('policy/page/cookie/get');
        }
    }

    /** STOCKIST **/
    if($("body > main.stockist").length > 0) {
        initMap();
        if ($("main.stockist #map").length > 0) {
            getStockistList();

            $(".google-map .zoom-button > .zoom-in").click(function() {
                mapInstance.setZoom(mapInstance.zoom + 3);
            })
            $(".google-map .zoom-button > .zoom-out").click(function() {
                mapInstance.setZoom(mapInstance.zoom - 3);
            })
        }

        $(".marker-card > .close").click(function() {
            $(".marker-card").removeClass("on");
        });
    }
});

/** TERMS **/
function termsAPI(_url) {
    $.ajax({
        data: {
            'country': 'KR'
        },
        url: config.api + _url,
        success: function(d) {
            if (d.code == 200) {
                if(d.data != null) {
                    termsSetDesc(d.data);
                } else {
                    alert("법적 고지사항 정보가 존재하지 않습니다.");
                }    
            }
            else {
                alert("법적 고지사항 조회에 실패했습니다.");
            }
        }
    });
}

function termsSetDesc(data) {
    let description = document.querySelector("body > main.terms > section.description > div");
    description.innerHTML = data.policy_txt;
}


/** STOCKIST **/
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

let mapInstance;
const img_server = 'https://cdn-ader-orig.fastedge.net/';

function initMap() {
    var initialLocation = { lat: 37.5400456, lng: 126.9921017  };
    mapInstance = new google.maps.Map(
        document.getElementById("map"), {
            zoom: 1.5,
            minZoom: 1.5,
            maxZoom: 13.5,
            center: initialLocation,
            styles: grayStyle,
            disableDefaultUI: true,
            gestureHandling: "greedy",
    });    
}

function getStockistList() {
    $.ajax({
        url: config.api + 'store/list/get',
        data: {
            'country': 'KR',
            'store_keyword': ''
        },
        //async: false,
        success: function(d) {
            if (d.code == 200) {
                initMarkers(d.data);
            }
            else {
                console.log("stockist 조회에 실패하였습니다.");
            }
        }
    });
}

function initMarkers(stockistList) {
    if (Array.isArray(stockistList.space_info) 
        && Array.isArray(stockistList.plugshop_info) 
        && Array.isArray(stockistList.stockist_info)) {
        
        const markers = [];

        if (stockistList.space_info.length > 0) {
            stockistList.space_info.forEach(({store_idx, store_type, lat, lng}) => {
                markers.push({
                    store_idx : store_idx,
                    store_type : store_type,
                    lat : lat,
                    lng : lng
                });
            });
        }
        if (stockistList.plugshop_info.length > 0) {
            stockistList.plugshop_info.forEach(({store_idx, store_type, lat, lng}) => {
                markers.push({
                    store_idx : store_idx,
                    store_type : store_type,
                    lat : lat,
                    lng : lng
                });
            });
        }
        if (stockistList.stockist_info.length > 0) {
            stockistList.stockist_info.forEach(({store_idx, store_type, lat, lng}) => {
                markers.push({
                    store_idx : store_idx,
                    store_type : store_type,
                    lat : lat,
                    lng : lng
                });
            });
        }

        markers.forEach(({store_idx, store_type, lat, lng}, idx) => {
            lng = parseFloat(lng);
            lat = parseFloat(lat);
            const marker = new google.maps.Marker({
                map: mapInstance,
                idx: store_idx,
                store_type: store_type,                 
                position: { lat, lng },
                icon: '/images/ico-map-marker.svg',
                scaledSize:{
                    width:  100,
                    height: 100
                }
            });

            marker.addListener("click", () => {
                mapInstance.setZoom(12);
                mapInstance.panTo(marker.getPosition());

                var card = {
                    'img_src': '',
                    'title': '',
                    'instagram': '',
                    'location': '',
                    'location_link': '',
                    'tel': '',
                    'date': '',
                    'detail_link': '',
                }

                switch (marker.store_type) {
                    case 'PLG':
                        card.img_src = img_server + stockistList.plugshop_info[marker.idx-1].contents_info[0].contents_location;
                        card.title = stockistList.plugshop_info[marker.idx-1].store_name;
                        card.instagram = "http://instagram.com/" + stockistList.plugshop_info[marker.idx-1].instagram_id;
                        card.location = stockistList.plugshop_info[marker.idx-1].store_addr;
                        card.tel = stockistList.plugshop_info[marker.idx-1].store_tel;
                        card.date = stockistList.plugshop_info[marker.idx-1].store_sale_date;
                        card.detail_link = "#";
                        break;
                    case 'SPC':
                        card.img_src = img_server + stockistList.space_info[marker.idx-1].contents_info[0].contents_location;
                        card.title = stockistList.space_info[marker.idx-1].store_name;
                        card.instagram = "http://instagram.com/" + stockistList.space_info[marker.idx-1].instagram_id;
                        card.location = stockistList.space_info[marker.idx-1].store_addr;
                        card.tel = stockistList.space_info[marker.idx-1].store_tel;
                        card.date = stockistList.space_info[marker.idx-1].store_sale_date;
                        card.detail_link = "#";
                        break;
                    case 'STC':
                        card.title = stockistList.stockist_info[marker.idx-1].store_name;
                        card.instagram = "http://instagram.com/" + stockistList.stockist_info[marker.idx-1].instagram_id;
                        card.location = stockistList.stockist_info[marker.idx-1].store_addr;
                        card.tel = stockistList.stockist_info[marker.idx-1].store_tel;
                        card.detail_link = "none";
                        break;
                };
                card.location_link = "https://www.google.com/maps/?q=" + marker.position;

                if (card.img_src !== null) {
                    $(".marker-card > img.image").attr("src", card.img_src);
                }
                if (card.title !== null) {
                    $(".marker-card > .title > span").text(card.title);
                }
                if (card.instagram !== null) {
                    $(".marker-card > .title > a.link").attr("href", card.instagram);
                }
                if (card.location !== null) {
                    $(".marker-card > .location > span").text(card.location);
                }
                if (card.location_link !== null) {
                    $(".marker-card > .location > a.location-link").attr("href", card.location_link);
                }
                if (card.tel !== null) {
                    $(".marker-card > .tel > span").text(card.tel);
                }
                if (card.date !== null) {
                    $(".marker-card > .date > span").text(card.date);
                }
                if (card.detail_link !== null) {
                    if (card.detail_link == "none") {
                        $(".marker-card > .detail").addClass("hidden");
                    }
                    else {
                        $(".marker-card > .detail").removeClass("hidden");
                        $(".marker-card > .detail").attr("href", card.detail_link);
                    }
                }

                $(".marker-card").addClass("on");
            });
        });
    }
}

/****************************************************************/