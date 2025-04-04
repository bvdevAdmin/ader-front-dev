const editorialWrapNode = document.querySelector(`.editorial-wrap`);

window.addEventListener('DOMContentLoaded', function () {
    console.log('editorialList Loaded');
    getEditorialList();
    divFadeIn(editorialWrapNode, 0.01, 16, 100);
    scrollTop();
})
window.addEventListener('load', function () {
    console.log('contents Loaded');
    setThumbSideHeight();
})
window.addEventListener("resize", function () {
    responsive();
});
function responsive() {
    let breakpoint = window.matchMedia('screen and (max-width:1025px)');
    let banner = document.querySelectorAll(".editorial-wrap .banner");
    if (breakpoint.matches === true) {
        banner.forEach(el => el.classList.remove("hidden"));
    } else if (breakpoint.matches === false) {
        banner.forEach(el => el.classList.add("hidden"));
        banner[0].classList.remove("hidden");
        setThumbSideHeight();
    }
}
function getEditorialList() {
    let isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

    let size_type = "W";
    if (isMobile == true) {
        size_type = "M";
    }
	
    $.ajax({
        type: "post",
        url: api_location + "posting/editorial/list/get",
        headers: {
        	"country": getLanguage()
        },
        data: {
            'size_type': size_type
        },
        dataType: "json",
        error: function () {
            // alert("에디토리얼 리스트 불러오기에 실패했습니다.");
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0105", null);
        },
        success: function (d) {
            if (d.code == 200) {
                let cnt = 0;
                if (d.data != null) {
                    d.data.forEach(function (row) {
                        appendThumbnailTitle(row.page_title, row.page_idx, row.size_type);
                        appendThumbnailBackground(row.contents_location, row.page_title, row.page_idx, cnt++, row.size_type);
                    })
                    asideClickEvent();
                    responsive();
                    addBtn();
                }
            }
        }
    });
}

function appendThumbnailTitle(title, pageIdx, sizeType) {
    let titleUl = document.querySelector(".thumbnail-side nav ul");
    let titleLi = document.createElement("li");
    titleLi.className = "thumbnail-title"
    titleLi.innerHTML = title;
    titleLi.dataset.pageidx = pageIdx;
    titleLi.dataset.sizetype = sizeType;
    titleUl.appendChild(titleLi);
}

function appendThumbnailBackground(thumbnail_background, title, page_idx, idx, size_type) {
    let backgroundHtml;
    let backgroundType = thumbnail_background.split('.', 2)[1];
    let editorialWrap = document.querySelector(".editorial-wrap");
    let article = document.createElement("article");

    article.className = "banner";
    if (idx > 0) article.classList.add("hidden")

    if (backgroundType === "mp4") {
        let location_arr = [];
        if (thumbnail_background != null) {
            location_arr = thumbnail_background.split('/');
            let file_name = location_arr[location_arr.length - 1];
            location_arr[location_arr.length - 1] = 'mp4:' + file_name;
        }
        
        backgroundHtml = `
	        <video id="video-coustom-${idx}" class="handle_editorial" data-page_idx="${page_idx}" data-size_type="${size_type}" autoplay loop muted playsinline>
	            <source src="${cdn_vid + location_arr.join('/') + '/playlist.m3u8'}" type="video/mp4">
	        </video>
        `;
    } else {
        backgroundHtml = `<img class="object-fit handle_editorial" data-page_idx="${page_idx}" data-size_type="${size_type}" src="${cdn_img + thumbnail_background}">`
    }
    
    article.innerHTML = `
        <figure class="handle_editorial" data-page_idx="${page_idx}" data-size_type="${size_type}">
            ${backgroundHtml}
            <figcaption>${title}</figcation>
        </figure>
    `;
    
    editorialWrap.appendChild(article);

    videoFomating();
    
    handleEditorialList();
}

function asideClickEvent() {
    let banner = document.querySelectorAll(".editorial-wrap .banner");
    let thumTitle = document.querySelectorAll(".thumbnail-title");
    thumTitle[0].classList.add("select");

    thumTitle.forEach((el, idx) => {
        el.addEventListener("click", function () {
            if (this.classList.contains("select")) {
                let { pageidx, sizetype } = this.dataset;
                moveEditorialDtail(pageidx, sizetype)
            }
        })
        el.addEventListener("mouseover", function () {
            thumTitle.forEach(el => el.classList.remove("select"));
            this.classList.add("select");
            banner.forEach(el => el.classList.add("hidden"));
            bannerTarget(idx).classList.remove("hidden");
            console.log('mouseover');
            setThumbSideHeight();
            divFadeIn(editorialWrapNode, 0.01, 16, 0);
        })
    })
    function bannerTarget(tidx) {
        return [...banner].find((el, idx) => idx === tidx);
    }
}

function moveEditorialDtail(page_idx, size_type) {
    location.href = `editorial/detail?page_idx=${page_idx}&size_type=${size_type}`;
}

function addBtn() {
    let addBtn = document.createElement("div");
    addBtn.className = "show_more_btn"
    addBtn.innerHTML = `
        <span class="add-btn">더보기 +</span>
        <img src="" alt="">
    `
    document.querySelector("main").appendChild(addBtn);
}

function setThumbSideHeight(){
    let thumbSide = document.querySelector('.thumbnail-side.open');
    let bannerHeight = $('.banner').not('.hidden').find('.plyr').css('height');
    if(typeof bannerHeight == "undefined" || bannerHeight == null || bannerHeight == ""){
        thumbSide.style.height = $('.banner').not('.hidden').css('height');
        console.log($('.banner').not('.hidden').css('height'));
    }
    else{
        let numRegex = /[^0-9]/g;
        let heightNum = bannerHeight.replace(numRegex, '');
        heightNum = Number(heightNum);
        thumbSide.style.height = `${heightNum + 2}px`;
    }
}

function handleEditorialList() {
	let handle_editorial = document.querySelectorAll('.handle_editorial');
	handle_editorial.forEach(handle => {
		handle.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let page_idx = el.dataset.page_idx;
			let size_type = el.dataset.size_type;
			
			moveEditorialDtail(page_idx,size_type);
		});
		
		handle.addEventListener('touchend',function(e) {
			let el = e.currentTarget;
			
			let page_idx = el.dataset.page_idx;
			let size_type = el.dataset.size_type;
			
			moveEditorialDtail(page_idx,size_type);
		});
	});
}

