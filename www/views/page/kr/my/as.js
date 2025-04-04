$(document).ready(function() {
    let msg_alert = {
        KR : "로그인 후 다시 시도해주세요.",
        EN : "Please Log in and try again."
    }
    
    if (!sessionStorage.MEMBER) {
        alert(
            msg_alert[config.language],
            function() {
                sessionStorage.setItem('r_url',location.href);
                location.href = `${config.base_url}/login`;
            }
        );
    }
});