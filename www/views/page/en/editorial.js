$(document).ready(function() {
	$.ajax({
		url: config.api + "editorial/list",
		headers : {
			country : config.language
		},
		error: function() {
			makeMsgNoti("MSG_F_ERR_0101", null);
		},
		success: function(d) {
			if(d.code == 200) {
				d.data.forEach(row => {
					$("#list").append(`
						<li>
							<a href="${config.base_url}/editorial/detail/${row.page_idx}">
								<span class="name">${row.title}</span>
							</a>
                        </li>
					`)
				})
			}
		}
	});
});
