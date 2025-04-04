function divFadeIn(el, fadeinInveval, tickTime, timeoutTime){
    setTimeout(function(){
        elementFadeIn(el, fadeinInveval, tickTime);
    }, timeoutTime);
}

function scrollTop() {
    let topBtn = document.querySelector(".top-btn");
    topBtn?.addEventListener("click", function () {
        window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
    })
}