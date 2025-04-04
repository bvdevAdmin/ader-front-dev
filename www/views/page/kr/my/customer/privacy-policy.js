$(document).ready(function() {
    $.ajax({
        url: config.api + "policy/get",
        headers : {
            country : config.language
        },
        data: { type : 'PNL' },
        beforeSend: function(xhr) {
            xhr.setRequestHeader("country",config.language);
        },
        success : function(d) {
            if(d.code == 200) {
                $(".privacy-policy .detail").append(d.contents)
            }
            else {
                alert(d.msg);
            }
        }
    });
})
