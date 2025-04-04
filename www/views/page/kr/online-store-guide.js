$(document).ready(function() {
    $.ajax({
        url: config.api + "policy/get",
        headers : {
            country : config.language
        },
        data: { type : 'GUD' },
        success: function(d) {
            if (d.code == 200) {
                if (d.contents) {
                    $("#policy-contents").append(d.contents);
                } else {
                    alert("법적 고지사항 정보가 존재하지 않습니다.");
                }    
            }
            else {
                alert("법적 고지사항 조회에 실패했습니다.");
            }
        }
    });
});
