const runwayWrapNode = document.querySelector(`.runway-wrap`);

window.addEventListener('DOMContentLoaded', function () {
    console.log('runwayList Loaded');
    getRunwayList();
    divFadeIn(runwayWrapNode, 0.01, 16, 100);
    scrollTop();
})
window.addEventListener("resize", function () {
    responsive()
});
function responsive() {
    let breakpoint = window.matchMedia('screen and (max-width:1025px)');
    let banner = document.querySelectorAll(".runway-wrap .banner");

    if (breakpoint.matches === true) {
        banner.forEach(el => el.classList.remove("hidden"));
    } else if (breakpoint.matches === false) {
        banner.forEach(el => el.classList.add("hidden"));
        banner[0].classList.remove("hidden");
    }
}
function getRunwayList() {
    let isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

    let size_type = "W";
    if (isMobile == true) {
        size_type = "M";
    }

    let country = getLanguage();
    $.ajax({
        type: "post",
        data: {
            'country': country,
            'size_type': size_type
        },
        dataType: "json",
        url: api_location + "posting/runway/list/get",
        error: function () {
            alert("런웨이 리스트 불러오기에 실패했습니다.");
        },
        success: function (d) {
            if (d.code == 200) {
                let cnt = 0;
                if (d.data != null) {
                    d.data.forEach(function (row) {
                        appendThumbnailTitle(row.page_title, row.page_idx, row.size_type);
                        appendThumbnailBackground(row.contents_location, row.page_title, row.page_idx, cnt++, row.size_type)
                        asideClickEvent();
                        responsive();
                    })
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
    let runwayWrap = document.querySelector(".runway-wrap");
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
        <video id="video-coustom-${idx}" autoplay loop muted playsinline onclick="moveRunwayDtail(${page_idx}, '${size_type}')" ontouchend="moveRunwayDtail(${page_idx}, '${size_type}')">
            <source src="${cdn_vid + location_arr.join('/') + '/playlist.m3u8'}" type="video/mp4">
        </video>
        `
    } else {
        backgroundHtml = `<img class="object-fit" src="${cdn_img + thumbnail_background}" onclick="moveRunwayDtail(${page_idx}, '${size_type}')" ontouchend="moveRunwayDtail(${page_idx}, '${size_type}')">`
    }
    article.innerHTML = `
        <figure onclick="moveRunwayDtail(${page_idx}, '${size_type}')">
            ${backgroundHtml}
            <figcaption>${title}</figcation>
        </figure>
    `;
    runwayWrap.appendChild(article);

    videoFomating();
}

function asideClickEvent() {
    let banner = document.querySelectorAll(".runway-wrap .banner");
    let thumTitle = document.querySelectorAll(".thumbnail-title");
    thumTitle[0].classList.add("select");

    thumTitle.forEach((el, idx) => {
        el.addEventListener("click", function () {
            if (this.classList.contains("select")) {
                let { pageidx, sizetype } = this.dataset;
                moveRunwayDtail(pageidx, sizetype)
            }
        })
        el.addEventListener("mouseover", function () {
            thumTitle.forEach(el => el.classList.remove("select"));
            this.classList.add("select");
            banner.forEach(el => el.classList.add("hidden"));
            bannerTarget(idx).classList.remove("hidden");
            divFadeIn(runwayWrapNode, 0.01, 16, 0);
        })
    })
    function bannerTarget(tidx) {
        return [...banner].find((el, idx) => idx === tidx);
    }
}

function moveRunwayDtail(page_idx, size_type) {
    location.href = `runway/detail?page_idx=${page_idx}&size_type=${size_type}`;
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
