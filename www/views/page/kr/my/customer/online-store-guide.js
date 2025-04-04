$(document).ready(function() {
    $.ajax({
        url: config.api + "policy/get",
        headers : {
            country : config.language
        },
        data: { type : 'GUD' },
        beforeSend: function(xhr) {
            xhr.setRequestHeader("country",config.language);
        },
        success : function(d) {
            if(d.code == 200) {
                $(".online-store-guide .detail").append(d.contents)
            }
            else {
                alert(d.msg);
            }
        }
    });
})
