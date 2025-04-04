/**
 * @author SIMJAE
 * @description 언어번경 생성자 함수
 */
function Language() {
    this.writeHtml = () => {
        let sideBox = document.querySelector(`.side__box`);
        let sideWrap = document.querySelector(`#sidebar .side__wrap`);
        sideWrap.dataset.module = "language";
        const languageContent = document.createElement("section");
        languageContent.className = "language-wrap";
        languageContent.innerHTML =
            `
            <div class="language-title" data-i18n="lm_choose_language">언어선택</div>
            <div class="language-content">
                <p data-i18n="lm_menu_lang_msg_01">아래 옵션에서 선택해 주세요.</p>
                <p data-i18n="lm_menu_lang_msg_02">선택한 언어에 해당되는 홈페이지로 리디렉션됩니다.</p>
            </div>
            <div class="language-btn-box">
                <div class="language-btn" data-ln="KR"><span>한국어</span></div>
                <div class="language-btn" data-ln="EN"><span>English</span></div>
                <div class="language-btn" data-ln="CN"><span>中文</span></div>
            </div>
        `
        sideBox.appendChild(languageContent);
        changeLanguageR();
    };
    this.addSelectEvent = () => {
        let $$languageBtn = document.querySelectorAll(".language-btn");
        $$languageBtn.forEach(el => {
            el.addEventListener("click", function () {
                $$languageBtn.forEach(el => el.classList.remove("select"));
                this.classList.add("select");
                //window.location.reload();
            })
        })
    }
}
