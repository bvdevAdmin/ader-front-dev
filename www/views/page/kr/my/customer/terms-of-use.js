$(document).ready(function() {
    $.ajax({
        url: config.api + "policy/get",
        headers : {
            country : config.language
        },
        data: { type : 'TRM' },
        beforeSend: function(xhr) {
            xhr.setRequestHeader("country",config.language);
        },
        success : function(d) {
            if(d.code == 200) {
                $(".terms-of-use .detail").append(d.contents)
            }
            else {
                alert(d.msg);
            }
        }
    });
})
