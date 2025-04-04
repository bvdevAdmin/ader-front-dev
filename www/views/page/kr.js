$(document).ready(function () {
	$.ajax({
		url: config.api + 'landing/get',
		headers: {
			country: config.language,
		},
		success: function (d) {
			if (d.code == 200) {
				['W', 'M'].forEach((key) => {
					let mst = d.mst[key];
					if (mst != null) {
						if (mst.banner_style != null) {
							let banner_style = document.createElement('style');
							banner_style.type = 'text/css';
							banner_style.innerHTML = mst.banner_style;
				
							document.head.appendChild(banner_style);
						}
						
						if (mst.banner_script != null) {
							let banner_script = document.createElement('script');
							banner_script.type = 'text/javascript';
							banner_script.innerHTML = mst.banner_script;
				
							document.body.appendChild(banner_script);
						}
					}
				});

				let data = d.data;
				if (data != null) {
					setBanner_flex(data);
				}
			}
		},
	});
});

function setBanner_flex(all_data) {
	['W', 'M'].forEach((key) => {
		let data = all_data[key] || [];
		if (data.length === 0) {
			return;
		}

		let section = null;
		if (key === 'W') {
			section = $('section.campaign.web');
		}
		else if (key === 'M') {
			section = $('section.campaign.mobile');
		}
		section.html('');

		data.forEach(function(row) {
			let div_banner = document.createElement('div');
			div_banner.classList.add('flex-banner');
	
			if (row.title_flg == true || row.sub_title_flg == true || row.link_flg == true) {
				let banner_txt = document.createElement('div');
				banner_txt.classList.add('banner-text', `vertical-${row.title_vrt}`, `horizontal-${row.title_hrz}`, `text-align-${row.align_title}`);
				
				if (row.title_flg == true) {
					let div_b_title = document.createElement('div');
					div_b_title.classList.add('banner-title', `text-align-${row.align_title}`);
	
					div_b_title.textContent = row.banner_title;
					div_b_title.style.color = row.title_color;
					
					banner_txt.append(div_b_title);
				}
				
				if (row.sub_title_flg == true) {
					let div_b_s_title = document.createElement('div');
					div_b_s_title.classList.add('banner-sub', `text-align-${row.align_sub_title}`);
	
					div_b_s_title.textContent = row.banner_sub_title;
					div_b_s_title.style.color = row.sub_title_color;
					
					banner_txt.append(div_b_s_title);
				}
	
				if (row.link_flg == true) {
					let div_b_link = document.createElement('a');
					div_b_link.classList.add('banner-link', `text-align-${row.align_link}`);
	
					div_b_link.textContent = row.link_txt;
					div_b_link.style.color = row.link_color;
	
					let link_url = row.link_url;
					if (row.ext_flg == true) {
						link_url = `https://${link_url}`;
					}
	
					div_b_link.href = link_url;
					
					banner_txt.append(div_b_link);
				}
	
				div_banner.append(banner_txt);
			}
			
			let banner_contents = row.banner_contents;
			if (banner_contents != null && banner_contents.length > 0) {
				banner_contents.forEach(function(con) {
					let div_contents = null;
					
					if (con.contents_type == "I") {
						div_contents = document.createElement('picture');
						div_contents.classList.add('banner-content');
						div_contents.style.flex = con.ratio ? con.ratio : 1;
					
						let source_i_w = document.createElement("img");
						source_i_w.src = `${config.cdn}${con.w_location}`;
						source_i_w.classList.add("image");
						
						div_contents.appendChild(source_i_w);
					} else if (con.contents_type == "V") {
						div_contents = document.createElement('div');
						div_contents.classList.add('video', 'banner-content');
						div_contents.style.flex = con.ratio ? con.ratio : 1;
					
						// 동적으로 <video> 요소 추가
						let video = document.createElement("video");
						video.autoplay = true;
						video.loop = true;
						video.muted = true;
						video.playsInline = true;
						video.setAttribute("webkit-playsinline", "");
						video.style.width = "100%";
						video.style.height = "100vh";
					
						let source_v_w = document.createElement("source");
						source_v_w.src = `${config.cdn}${con.w_location}`;
						source_v_w.type = "video/mp4";
						
						video.appendChild(source_v_w);
	
						div_contents.appendChild(video);
					}
					
					if (con.title_flg == true || con.sub_title_flg == true || con.link_flg == true) {
						let contents_txt = document.createElement('div');
						contents_txt.classList.add('content-text', `vertical-${con.title_vrt}`, `horizontal-${con.title_hrz}`, `text-align-${con.align_title}`);
						
						if (con.title_flg == true) {
							let div_c_title = document.createElement('div');
							div_c_title.classList.add('content-title', `text-align-${con.align_title}`);
	
							div_c_title.textContent = con.contents_title;
							div_c_title.style.color = con.title_color;
	
							contents_txt.append(div_c_title);
						}
			
						if (con.sub_title_flg == true) {
							let div_c_s_title = document.createElement('div');
							div_c_s_title.classList.add('content-sub', `text-align-${con.align_sub_title}`);
	
							div_c_s_title.textContent = con.contents_sub_title;
							div_c_s_title.style.color = con.sub_title_color;
	
							contents_txt.append(div_c_s_title);
						}
			
						if (con.link_flg == true) {
							let div_c_link = document.createElement('a');
							div_c_link.classList.add('content-link', `text-align-${con.align_link}`);
	
							div_c_link.textContent = con.link_txt;
							div_c_link.style.color = con.link_color;
	
							let c_link_url = con.link_url;
							if (con.ext_flg == true) {
								c_link_url = con.link_url;
							}
							
							div_c_link.href = c_link_url;
							
							contents_txt.append(div_c_link);
						}
			
						div_contents.append(contents_txt);
					}
			
					div_banner.append(div_contents);
				});
			}
			
			section.append(div_banner);
		});
	})
}