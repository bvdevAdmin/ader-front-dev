$(document).ready(function() {
	getCustom_category();
	
	clickBTN_put();
});

function getCustom_category() {
	$.ajax({
		url: config.api + "member/purchase/category",
		headers : {
			country : config.language
		},
		error: function () {
			makeMsgNoti('MSG_F_ERR_0046',null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					let gender = $(`#gender_${data.gender}`);
					if (gender != null) {
						gender.prop('checked',true);
					}
					
					$('#height').val(data.height);
					
					$('#weight').val(parseInt(data.weight));
					
					let upper_size = data.upper_size;
					if (upper_size != null && upper_size.length > 0) {
						setCategory('upper',upper_size);
					}
					
					let lower_size = data.lower_size;
					if (lower_size != null && lower_size.length > 0) {
						setCategory('lower',lower_size);
					}
					
					let shoes_size = data.shoes_size;
					if (shoes_size != null && shoes_size.length > 0) {
						setCategory('shoes',shoes_size);
					}
				}
			}
		}
	});
}

function setCategory(category_type,data) {
	let div_size = $(`.div_${category_type}`);
	div_size.html('');
	
	let str_div = "";
	
	data.forEach(function(row) {
		let checked = "";
		if (row.checked == true) {
			checked = "checked";
		}
		
		switch (category_type) {
			case "upper" :
				str_div += `
					<li>
						<label>
							<input type="checkbox" name="topsize[]" value="${row.category_idx}" ${checked}>
							<span>${row.category_txt}</span>
						</label>
					</li>
				`;
				
				break;
			
			case "lower" :
				str_div += `
					<li>
						<label>
							<input type="checkbox" name="bottomsize[]" value="${row.category_idx}" ${checked}>
							<span>${row.category_txt}</span>
						</label>
					</li>
				`;
				
				break;
			
			case "shoes" :
				str_div += `
					<label>
						<input type="checkbox" name="shoesize[]" value="${row.category_idx}" ${checked}>
						<i></i>${row.category_txt}
					</label>
				`;
				
				break;
		}
		
		div_size.html(str_div);
	});
}

function clickBTN_put() {
	let btn_put = document.querySelector('.btn_put');
	if (btn_put != null) {
		btn_put.addEventListener('click',function() {
			$.ajax({
				url: config.api + "member/purchase/put",
				headers : {
					country : config.language
				},
				data: $("#frm").serialize(),
				async: false,
				error: function () {
					makeMsgNoti('MSG_F_ERR_0061', null);
				},
				success: function (d) {
					if(d.code == 200) {
						makeMsgNoti('MSG_F_INF_0013',null);
						location.href = `${config.base_url}/my/info/purchase`;
					} else if (d.code = 401) {
						$('.modal.alert.on .close').attr('onclick','location.href="/login"');
					} else {
						makeMsgNoti('MSG_F_ERR_0063',null);
					}
				}
			});
		});
	}
}

function setClick_close() {
	console.log($('.modal.alert.on .close').length);
	$('.modal.alert.on .close').attr('onclick',`location.href = "${config.base_url}/my/info/purchase"`);
}