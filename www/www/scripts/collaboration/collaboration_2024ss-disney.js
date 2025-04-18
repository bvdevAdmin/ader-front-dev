let mobileWeb = false;
if(window.innerWidth <= 780) {
    mobileWeb = true;
}

var logoplay_timeout = null;
$("#logoplay").click(function() {
    if($("#logoplay").hasClass("on") == false) return;
    if(logoplay_timeout != null) {
        clearTimeout(logoplay_timeout);
    }
    let s1 = $("#logoplay").hasClass("s1")
        , s2 = $("#logoplay").hasClass("s2")
        , s3 = $("#logoplay").hasClass("s3");

    if(s1 == false && s2 == false && s3 == false) {
        $("#logoplay").addClass("s1");
    } else if(s1 == true && s2 == false) {
        $("#logoplay").addClass("s2");
    } else if(s1 && s2 && s3 == false) {
        $("#logoplay").addClass("s3");
    } else {
        $("#logoplay").addClass("off");
        setTimeout(function() {
            $("#concept-summary").addClass("on");
        }, 500);
    }
    logoplay_timeout = setTimeout(function() {
        $("#logoplay").click();
    }, 1500);
});

$(window).scroll(function() {
    let page = Math.ceil($(this).scrollTop() / ($(this).height() / 12)) + 1;
    let pageurl = `url('https://adererror.com/upload/2024ss-disney/paper/pc/pc_${appendzero(page, 2)}.png')`;

    if(typeof mobileWeb != "undefined" && mobileWeb) pageurl = `url('https://adererror.com/upload/2024ss-disney/paper/mo/mo_${appendzero(page,2)}.png')`;
    if(page <= 25) {
        $("#intro-page .bg").css({backgroundImage:pageurl});
        $("#intro-page").removeClass("fix");
        $("#logoplay,#concept-summary,#bookbody").removeClass("on").removeClass("off");
        $("#logoplay").removeClass("s1").removeClass("s2").removeClass("s3");
        $("body").removeClass("show-all");
    } else if(page > 30) {
        pageurl = `url('https://adererror.com/upload/2024ss-disney/paper/pc/pc_25.png')`;
        if(typeof mobileWeb != "undefined" && mobileWeb) pageurl = `url('https://adererror.com/upload/2024ss-disney/paper/mo/mo_25.png')`;
        $("#intro-page .bg").css({backgroundImage:pageurl});
        $("#concept-summary").addClass("on");
    }
}).scroll();

for(var i = 1; i <= 37; i++) {
    $("#flipbook > .page-cover-2").before(`
        <div style="background-image:url('https://adererror.com/upload/2024ss-disney/magazine/magazine_${appendzero(i, 2)}.jpg');background-position : left center"></div>
        <div style="background-image:url('https://adererror.com/upload/2024ss-disney/magazine/magazine_${appendzero(i, 2)}.jpg');background-position : right center"></div>
    `);

    $("#swiper-book > .swiper-wrapper").append(`
        <div class="swiper-slide" style="background-image:url('https://adererror.com/upload/2024ss-disney/magazine/magazine_${appendzero(i, 2)}.jpg')"></div>
    `);
}

var book_gallery = null;
if($("#swiper-book").length > 0) {
    book_gallery = new Swiper($("#swiper-book").get(0),{
        zoom: true,
        autoplay: {
            delay: 3500,
            disableOnInteraction: false,
        },
        navigation : {
            nextEl : $("#swiper-book .swiper-button-next").get(0),
            prevEl : $("#swiper-book .swiper-button-prev").get(0),
        }
    });
    $("#btn-fullscreen").click(function() {
        $("#swiper-book").parent().addClass("on");
        book_gallery.slideTo(0);
    });
    $("#btn-bookgallery-close").click(function() {
        $(this).parent().removeClass("on");
    });
}

var flipbook_next = null;
var flipbook_width = ($(window).width() <= 480) ? $(window).width() - 40 : 1200;
var flipbook_height = ($(window).width() <= 480) ? ($(window).width() - 40) * (76 / 120) : 760;
var flipbook = $("#flipbook").turn({
    acceleration: true,
    width: flipbook_width,
    height: flipbook_height,
    autoCenter : false,
    gradients : !$.isTouch,
    elevation : 50,
    when: {
        turning: function(e, page, view) {
            if(flipbook_next != null) {
                clearTimeout(flipbook_next);
                flipbook_next = null;
            }
            if(page % 2 == 0) {
                flipbook_next = setTimeout(function() {
                    flipbook.turn("next");
                }, 5000);
            }

            page = ( (page % 2 == 1) ? page - 1 : page ) / 2;
            if(page == 0) {
                $("#flipbook").addClass("center-left");
            } else if(page == 38) {
                $("#flipbook").addClass("center-right");
            } else {
                $("#flipbook").removeClass("center-left");
                $("#flipbook").removeClass("center-right");
                $("#now-page").text(page);
            }
        }
    }
});
// 이전 페이지로 이동
$("#btn-paging-prev").click(function() {
    flipbook.turn("previous");
    if(flipbook_next != null) {
        clearTimeout(flipbook_next);
        flipbook_next = null;
    }
});
// 다음 페이지로 이동
$("#btn-paging-next").click(function() {
    flipbook.turn("next");
    if(flipbook_next != null) {
        clearTimeout(flipbook_next);
        flipbook_next = null;
    }
});
// 앞 커버에서 첫 페이지로 이동
$("#btn-paging-open1").click(function() {
    flipbook.turn("page", 2);
});
// 뒷 커버에서 마지막 페이지로 이동
$("#btn-paging-open2").click(function() {
    flipbook.turn("page", 74);
});
// 컨셉 클릭시 넘어감
$("#concept-summary").click(function() {
    function show_book_container() {
        $("#concept-summary").addClass("off");
        $("#intro-page").addClass("fix");
        $("body").addClass("show-all");
        flipbook.turn("page", 1);
        setTimeout(function() {
            $("#bookbody").addClass("on");
            setTimeout(function() {
                flipbook.turn("next");
            }, 2000);
        }, 500);
    }

    let obj = $("section.collaboration.disney > .book > .contents .concept-summary p");
    if($("#swiper-book").length > 0) {
        let chk_top = obj.scrollTop();
        obj.scrollTop(10000);
        if(chk_top == obj.scrollTop()) {
            show_book_container()
        }
    } else {
        show_book_container()
    }
});