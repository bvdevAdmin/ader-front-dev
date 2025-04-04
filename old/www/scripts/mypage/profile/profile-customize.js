document.addEventListener("DOMContentLoaded", function () {
	getCustomizeCategory();
	getCustomizeData();
	addPutCustomizeDataBtnEvent();
});

let upperSizeSelectBox = null;
let lowerSizeSelectBox = null;
let shoesSizeSelectBox = null;

function addPutCustomizeDataBtnEvent() {
	let btn = document.querySelector(".put_customize_data_btn");

	btn.addEventListener("click", putCustomizeData);
}

function getCustomizeCategory() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/member/customize/category/get",
		dataType: "json",
		async: false,
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0062', null);
			//notiModal("맞춤 구매 카테고리 정보 조회에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;

			if (code == 200) {
				let data = d.data;
				if (data != null) {
					let upperSizeData = [];
					let lowerSizeData = [];
					let shoesSizeData = [];

					let placeholderMsg = "";

					switch (getLanguage()) {
						case "KR":
							placeholderMsg = "사이즈를 선택해주세요.";
							break;

						case "EN":
							placeholderMsg = "Please select a size.";
							break;

						case "CN":
							placeholderMsg = "请选择尺寸。";
							break;
					}

					data.forEach(row => {
						let tmpData = {
							"label": row.category_txt,
							"value": row.category_idx
						}

						if (row.category_type == "UPC") {
							upperSizeData.push(tmpData);
						}

						if (row.category_type == "LWC") {
							lowerSizeData.push(tmpData);
						}

						if (row.category_type == "SHC") {
							shoesSizeData.push(tmpData);
						}
					});

					upperSizeSelectBox = new tui.SelectBox(".upper_size_select", {
						placeholder: placeholderMsg,
						data: upperSizeData,
						autofocus: false
					});

					lowerSizeSelectBox = new tui.SelectBox(".lower_size_select", {
						placeholder: placeholderMsg,
						data: lowerSizeData,
						autofocus: false
					});

					shoesSizeSelectBox = new tui.SelectBox(".shoes_size_select", {
						placeholder: placeholderMsg,
						data: shoesSizeData,
						autofocus: false
					});

					upperSizeSelectBox.on("open", function () {
						lowerSizeSelectBox.close();
						shoesSizeSelectBox.close();
					});

					lowerSizeSelectBox.on("open", function () {
						upperSizeSelectBox.close();
						shoesSizeSelectBox.close();
					});

					shoesSizeSelectBox.on("open", function () {
						upperSizeSelectBox.close();
						lowerSizeSelectBox.close();
					});
				}
			}
		}
	});
}

function getCustomizeData() {
	$.ajax({
		type: "post",
		dataType: "json",
		url: api_location + "mypage/member/customize/get",
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0061', null);
			//notiModal("맞춤 구매 회원 정보 조회에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;

			if (code == 200) {
				let data = d.data;

				if (data != null) {
					let maleCb = document.querySelector(".male_cb input");
					let femaleCb = document.querySelector(".female_cb input");

					let memberGender = data.member_gender;
					let upperSizeIdx = data.upper_size_idx;
					let lowerSizeIdx = data.lower_size_idx;
					let shoesSizeIdx = data.shoes_size_idx;

					if (memberGender == "M") {
						maleCb.checked = true;
					} else {
						femaleCb.checked = true;
					}

					upperSizeSelectBox.select(`${upperSizeIdx}`);
					lowerSizeSelectBox.select(`${lowerSizeIdx}`);
					shoesSizeSelectBox.select(`${shoesSizeIdx}`);
				}
			}
		}
	});
}

function putCustomizeData() {
	let member_gender = null;
	let upper_size_idx = null;
	let lower_size_idx = null;
	let shoes_size_idx = null;
	let checkbox = document.querySelectorAll(".gender_checkbox input");

	checkbox.forEach(box => {
		if (box.checked == true) {
			member_gender = box.value;
		}
	});

	if (member_gender == null) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0028', null);
		return false;
	}

	if (upperSizeSelectBox.getSelectedItem() != null) {
		upper_size_idx = parseInt(upperSizeSelectBox.getSelectedItem().value);
	} else {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0031', null);
		return false;
	}

	if (lowerSizeSelectBox.getSelectedItem() != null) {
		lower_size_idx = parseInt(lowerSizeSelectBox.getSelectedItem().value);
	} else {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0004', null);
		return false;
	}

	if (shoesSizeSelectBox.getSelectedItem() != null) {
		shoes_size_idx = parseInt(shoesSizeSelectBox.getSelectedItem().value);
	} else {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0023', null);
		return false;
	}

	$.ajax({
		type: "post",
		data: {
			"member_gender": member_gender,
			"upper_size_idx": upper_size_idx,
			"lower_size_idx": lower_size_idx,
			"shoes_size_idx": shoes_size_idx
		},
		dataType: "json",
		url: api_location + "mypage/member/customize/put",
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0063', null);
			//notiModal("맞춤 구매 정보 설정에 실패했습니다.");
		},
		success: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_INF_0013', null);
			//notiModal("맞춤 구매 정보 설정에 성공했습니다.");
		}
	});
}