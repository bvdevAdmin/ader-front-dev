const grayStyle = [
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



$(document).ready(function() {
	let map = new google.maps.Map(
        $("#map").get(0), {
            zoom: 1.5,
            minZoom: 1.5,
            maxZoom: 13.5,
            center: { lat: 37.5400456, lng: 126.9921017  },
            styles: grayStyle,
            disableDefaultUI: true,
            gestureHandling: "greedy",
    });

	$("#frm").submit(function() {
		$.ajax({
			url: config.api + 'store/get',
			data: {
				store_keyword : $(this).find("input[name='keyword']").val()
			},
			success: function(d) {
				function set_contents(obj,row) {
					let image = '',addr = '',tel = '',sale_date = '';
					if(row.contents_info) {
						image = `<span class="image" style="background-image:url('${config.cdn + row.contents_info[0].contents_location}')"></span>`;
					}
					if(row.store_addr) {
						addr = `${row.store_addr} <a href="https://google.com/maps/@${row.lat},${row.lng},18z" target="_blank" class="location"><span>위치 보기</span></a>`;
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
								${(row.instagram_id != null)?'<a href="http://instagram.com/'+row.instagram_id+'" target="_blank"><img src="/images/ico-instagram.svg"></a>':''}
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
					
					return {
						store_idx : row.store_idx,
						store_type : row.store_type,
						lat : row.lat,
						lng : row.lng
					};
				}
				
				if (d.code == 200) {
					let stockistList = d.data;
					if (Array.isArray(stockistList.space_info) 
						&& Array.isArray(stockistList.plugshop_info) 
						&& Array.isArray(stockistList.stockist_info)) {
						
						const markers = [];

						// 브랜드 스토어 > 스페이스
						if (stockistList.space_info.length > 0) {
							stockistList.space_info.forEach(row => {
								markers.push(set_contents($("#space-info"),row));
							});
						}
						if (stockistList.plugshop_info.length > 0) {
							stockistList.plugshop_info.forEach(row => {
								markers.push(set_contents($("#plug-info"),row));
							});
						}
						if (stockistList.stockist_info.length > 0) {
							stockistList.stockist_info.forEach(row => {
								markers.push(set_contents($("#stockist-info"),row));
							});
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

							marker.addListener("click", () => {
								map.setZoom(12);
								map.panTo(marker.getPosition());

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
										card.img_src = config.cdn + stockistList.plugshop_info[marker.idx-1].contents_info[0].contents_location;
										card.title = stockistList.plugshop_info[marker.idx-1].store_name;
										card.instagram = "http://instagram.com/" + stockistList.plugshop_info[marker.idx-1].instagram_id;
										card.location = stockistList.plugshop_info[marker.idx-1].store_addr;
										card.tel = stockistList.plugshop_info[marker.idx-1].store_tel;
										card.date = stockistList.plugshop_info[marker.idx-1].store_sale_date;
										card.detail_link = "#";
										break;
									case 'SPC':
										card.img_src = config.cdn + stockistList.space_info[marker.idx-1].contents_info[0].contents_location;
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
});
