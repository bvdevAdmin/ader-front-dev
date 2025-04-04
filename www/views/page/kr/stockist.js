const gray_style = [
	{
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

const googleMarkers = [];
let swiper_store

let msg_translate = {
	KR : {
		't_01' : "검색 결과가 없습니다.",
		't_02' : "위치를 가져올 수 없습니다. 위치 서비스가 활성화되었는지 확인하세요.",
		't_03' : "이 브라우저는 위치 정보를 지원하지 않습니다."
	},
	EN : {
		't_01' : "There is no search result.",
		't_02' : "Unable to get location, please make sure location service is enabled.",
		't_03' : "This browser does not support location information."
	}
};

$(document).ready(function() {
	let markerObjs = [];
	function clearMarkers() {
		markerObjs.forEach(marker => marker.setMap(null)); // 모든 마커를 지도에서 제거
		markerObjs = []; // 배열 초기화
	}
	let map = new google.maps.Map(
        $("#map").get(0), {
            zoom: 1.5,
            minZoom: 1.5,
            maxZoom: 13.5,
            center: { lat: 37.5400456, lng: 126.9921017  },
            styles: gray_style,
            disableDefaultUI: true,
            gestureHandling: "greedy",
    });
	$("#frm").submit(function() {
		$.ajax({
			url: config.api + 'store/get',
			headers : {
				country : config.language
			},
			data: new FormData($(this).get(0)),
			processData:false,
			contentType:false,
			success: function(d) {
				function set_empty(obj){
					const noResultMessage = `
						<div class="no-results">
							<p>${msg_translate[config.language]['t_01']}</p>
						</div>
					`;
					$(obj).html(noResultMessage);
				}
				function set_contents(obj, row) {
					let image = '',addr = '',tel = '',sale_date = '';
					if(row.contents_info) {
						image = `<span class="image" style="background-image:url('${config.cdn + row.contents_info[0].contents_location}')"></span>`;
					}
					
					let msg_location = {
						KR : "위치보기",
						EN : "View location"
					}

					if(row.store_addr) {
						addr = `
							${row.store_addr}
							<a href="${row.link_map}" target="_blank" class="location">
								<span>${msg_location[config.language]}</span>
							</a>
						`;
					}
					if(row.store_tel) {
						tel = `<span class="tel">${row.store_tel}</span>`;
					}
					if(row.store_sale_date) {
						sale_date = row.store_sale_date;
					}
					
					let html = `
						<dd data-no="${row.store_idx}">
							${image}
							<h4>
								${row.store_name}
								${(row.instagram_id != null)?`<a href="http://instagram.com/${row.instagram_id}" target="_blank"><img src="/images/ico-instagram.svg"></a>`:''}
							</h4>
							${addr}
							${tel}
							${sale_date}
						</dd>
					`;

					// country 값이 있을 경우
					if(row.country) {
						if($(obj).children(`dt[data-country="${row.country}"]`).length == 0) {
							$(obj).append(`
								<dt data-country="${row.country}">${row.country}</dt>
							`);
						}
						$(obj).children(`dt[data-country="${row.country}"]`).after(html);
					}
					else {
						$(obj).append(html);
					}

					let returnValue = {
						store_idx : row.store_idx,
						store_type : row.store_type,
						lat : row.lat,
						lng : row.lng
					};
					
					return returnValue
				}

				function clickBrandStore(obj, stockistList, store_type) {
					obj.find("dd").click(function (e) {
						let storeIndex = $(this).data("no");
						let findMarker = googleMarkers.find(m => m.idx == storeIndex && m.store_type == store_type);
						clickMarker(map, findMarker, stockistList);
					})
				}

				clearMarkers();
				
				if (d.code == 200) {
					let markers = [];
					let stockistList = d.data;
					if (Array.isArray(stockistList.space_info) 
						&& Array.isArray(stockistList.plugshop_info) 
						&& Array.isArray(stockistList.stockist_info)) {

						// 브랜드 스토어 > 스페이스
						if (stockistList.space_info.length > 0) {
							$("#space-info").html('');
							stockistList.space_info.forEach(row => {
								markers.push(set_contents($("#space-info"),row, stockistList));
							});

							clickBrandStore($("#space-info"), stockistList, "SPC");
						}
						else{
							set_empty($("#space-info"));
						}
						if (stockistList.plugshop_info.length > 0) {
							$("#plug-info").html('');
							stockistList.plugshop_info.forEach(row => {
								markers.push(set_contents($("#plug-info"),row, stockistList));
							});

							clickBrandStore($("#plug-info"), stockistList, "PLG");
						}
						else{
							set_empty($("#plug-info"));
						}
						if (stockistList.stockist_info.length > 0) {
							$("#stockist-info").html('');
							stockistList.stockist_info.forEach(row => {
								markers.push(set_contents($("#stockist-info"),row, stockistList));
							});
							clickBrandStore($("#stockist-info"), stockistList, "STC");
						}
						else{
							set_empty($("#stockist-info"));
						}
						
						const infowindow = new google.maps.InfoWindow({
							content: '',
							ariaLabel: "Uluru",
						});

						markers.forEach(({store_idx, store_type, lat, lng}, idx) => {
							lng = parseFloat(lng);
							lat = parseFloat(lat);
							const marker = new google.maps.Marker({
								map: map,
								idx: store_idx,
								store_type: store_type,                 
								position: { lat, lng },
								icon: '/images/ico-map-marker.svg',
								scaledSize:{
									width:  100,
									height: 100
								}
							});
							googleMarkers.push(marker)

							markerObjs.push(marker); 
							marker.addListener("click", () => {
								clickMarker(map, marker, stockistList);
							});
						});
					}
                    
				}
				else {
					alert(d.msg);
				}
			}
		});
		return false;
	}).submit();

	$(".google-map .zoom-button > .zoom-in").click(function() {
		map.setZoom(map.zoom + 3);
	})
	$(".google-map .zoom-button > .zoom-out").click(function() {
		map.setZoom(map.zoom - 3);
	})

	$(".marker-card > .close").click(function() {
		$(".marker-card").removeClass("on");
	});

	$("#search-by-location").click(function () {
		getCurrentLocation(function (userLocation) {
			$("input[name='lat']").val(userLocation.lat);
			$("input[name='lng']").val(userLocation.lng);
			map.setCenter(userLocation);
            map.setZoom(10); 
			$("#frm").submit(); // 폼 제출로 검색 트리거
		});
	});

	swiper_store = new Swiper($("#store-slide").get(0), {
		slidesPerView : 'auto',
		loop: false,
		loopFillGroupWithBlank: true,
		effect: "slide",
		spaceBetween: 20,
		navigation: {
			nextEl: $("#store-slide .swiper-button-next").get(0),
			prevEl: $("#store-slide .swiper-button-prev").get(0)
		},
	});

});

function clickMarker(map, marker, stockistList) {
	map.setZoom(12);
	map.panTo(marker.getPosition());

	var card = {
		'store_type'	:"",
		'img_src'		:"",
		'title'			:"",
		'instagram'		:"",
		'location'		:"",
		'location_link'	:"",
		'tel'			:"",
		'date'			:"",
		'detail_link'	:"",
		'link_map'		:""
	}
	
	let img_tag = '';
	switch (marker.store_type) {
		case 'SPC':
			let space = stockistList.space_info.find(find => find.store_idx === marker.idx);
			if(space) {
				space.contents_info.forEach(row => {
					img_tag += `<div class="image swiper-slide"><img src="${config.cdn}${row.contents_location}" alt="Example Image"></div>`
				})
				
				card.store_type		= "SPC";
				card.img_src		= config.cdn + space.contents_info[0].contents_location;
				card.title			= space.store_name;
				card.instagram		= "http://instagram.com/" + space.instagram_id;
				card.location		= space.store_addr;
				card.tel			= space.store_tel;
				card.date			= space.store_sale_date;
				card.detail_link	= space.store_link;
				card.link_map		= space.link_map;
				$(".marker-card").removeClass("no-store")
			}

			break;
		
		case 'PLG':
			let plugshop = stockistList.plugshop_info.find(find => find.store_idx === marker.idx);

			if(plugshop) {
				plugshop.contents_info.forEach(row => {
					img_tag += `<div class="image swiper-slide"><img src="${config.cdn}${row.contents_location}" alt="Example Image"></div>`
				})

				card.store_type		= "PLG";
				card.img_src		= `${config.cdn}${plugshop.contents_info[0].contents_location}`;
				card.title			= plugshop.store_name;
				card.instagram		= "http://instagram.com/" + plugshop.instagram_id;
				card.location		= plugshop.store_addr;
				card.tel			= plugshop.store_tel;
				card.date			= plugshop.store_sale_date;
				card.detail_link	= plugshop.store_link;
				card.link_map		= plugshop.link_map;
				$(".marker-card").removeClass("no-store")
			}
			break;

		case 'STC':
			let stockist = stockistList.stockist_info[marker.idx - 1];
			
			card.store_type			= "STC";
			card.title				= stockist.store_name;
			card.instagram			= "http://instagram.com/" + stockist.instagram_id;
			card.location			= stockist.store_addr;
			card.tel				= stockist.store_tel;
			card.detail_link		= stockist.store_link;
			card.link_map			= "";
			$(".marker-card").addClass("no-store")
			break;
	}
	;
	card.location_link = `https://www.google.com/maps/?q=${marker.position}`;

	if(img_tag !== null) {
		$(".marker-card .swiper-wrapper").html(img_tag);
	}

	if (card.title !== null) {
		$(".marker-card > .title > span").text(card.title);

		if (card_title != null && card_title.length > 0) {
			$(".marker-card > .title > a").text('link');
			$(".marker-card > .title > a").attr('href',card.detail_link);
		}
	}

	if (card.instagram !== null) {
		$(".marker-card > .title > a.link").attr("href", card.instagram);
	}

	if (card.location !== null) {
		$(".marker-card > .location > span").text(card.location);
	}

	if (card.location_link !== null) {
		$(".marker-card > .location > a.location-link").attr("href", card.link_map);
	}

	if (card.tel !== null) {
		$(".marker-card > .tel > span").text(card.tel);
	}

	if (card.date !== null) {
		$(".marker-card > .date > span").text(card.date);
	}

	$(".marker-card > .detail").attr("href", `/${config.language.toLowerCase()}/stockist/${marker.idx}?store_type=${card.store_type}`);
	$(".marker-card > .detail").click(function () {
		localStorage.setItem("store_type", marker.store_type);
	})

	$(".stockist.modal").addClass("on");
	$(".stockist.modal button.close").click(function (e) {
		$(".stockist.modal").removeClass("on");
	})

	swiper_store.slideTo(0)
}

function getCurrentLocation(callback) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                callback({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
            },
            function (error) {
                console.error("Error getting location:", error);
                alert(msg_translate[config.language]['t_02']);
            }
        );
    } else {
        alert(msg_translate[config.language]['t_03']);
    }
}