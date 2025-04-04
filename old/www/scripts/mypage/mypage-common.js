// 페이지네이션 함수
function mypagePaging(obj, func) {
	if (typeof obj != 'object' || 'total' in obj == false || 'el' in obj == false) {
		return;
	}
	if ('page' in obj == false) obj.page = 1;
	if ('row' in obj == false) obj.row = 10;
	if ('show_paging' in obj == false) obj.show_paging = 9;

	let total_page = Math.ceil(obj.total / obj.row);

	// 이전 페이징
	let prev = obj.page - obj.show_paging;
	if (prev < 1) prev = 1;

	// 다음 페이징
	let next = obj.page + obj.show_paging;
	if (next > total_page) next = total_page;

	// 페이지 시작 번호
	let start = obj.page - Math.ceil(obj.show_paging / 2) + 1;
	if (start < 1) start = 1;

	// 페이지 끝 번호
	let end = start + obj.show_paging - 1;
	if (end > total_page) {
		end = total_page;
		start = end - obj.show_paging + 1;
		if (start < 1) start = 1;
	}
	if (end < 1) {
		total_page = 1;
		end = 1;
		next = 1;
		prev = 1;
		start = 1;
	}
	let paging = [];
	for (var i = start; i <= end; i++) {
		paging.push(`<div class="page ${((i == obj.page) ? 'now' : '')}" data-page="${i}" style="${((i == obj.page) ? 'color: black' : 'color: #dcdcdc')}">${i}</div>`);
	}
	$(obj.el).html(`
      <div class="mypage--paging">
          <div class="page prev" data-page="${prev}" style="${((obj.page == 1) ? 'color: #dcdcdc' : 'color: black')}"><</div>
          ${paging.join("")}
          <div class="page next" data-page="${next}" style="${((obj.page == end) ? 'color: #dcdcdc' : 'color: black')}">></div>
      </div>
    `);

	$(obj.el).find(".mypage--paging .page").click(function () {
		var new_page = $(this).data("page");
		$(obj.use_form).find('input[name="page"]').val(new_page);
		if (obj.list_type == null) {
			func();
		} else {
			func(obj.list_type);
		}
		$('html, body').scrollTop(0);
	});
}

function makeCalendar(status) {
	let wrap_class_name = '';
	let dropdown_class = '';
	if (status == 'bluemark') {
		wrap_class_name = '.bluemark__wrap';
		dropdown_class = '.purchase-btn';
	}
	else {
		wrap_class_name = '.orderlist__wrap';
		dropdown_class = '.date-choice-btn';
	}
	const wrap = document.querySelector(wrap_class_name);
	const dropdown_wrap = document.querySelector(".calendar-" + status + ".dropdown");
	const dropdown = dropdown_wrap.querySelector(dropdown_class);
	const calendar = document.querySelector(".calendar-" + status + " .calendar");
	const header = calendar.querySelector(".calendar-header");
	const prevBtn = header.querySelector(".prev-month-btn");
	const nextBtn = header.querySelector(".next-month-btn");
	const currentMonth = header.querySelector(".current-month");
	const weekdays = calendar.querySelector(".calendar-weekdays");
	const days = calendar.querySelector(".calendar-days");
	const calendarStart = document.querySelector(".date-choice-btn.start");
	const calendarEnd = document.querySelector(".date-choice-btn.end");
	const dateSearch = document.querySelector(".date-search-btn");
	const months = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
	const weekdaysShort = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];

	let inputSelectorStr = "";
	switch (status) {
		case 'bluemark':
			inputSelectorStr = '.bluemark-purchase-date';
			break;
		case 'orderlist-start':
			inputSelectorStr = '.selected-date.start';
			break;
		case 'orderlist-end':
			inputSelectorStr = '.selected-date.end';
			break;
	}

	let date = new Date();
	let selectedDate = new Date();

	function renderCalendar(purchaseDate) {
		let date_param = document.querySelector(inputSelectorStr);
		selectedDate = date_param.value.length > 0 ? new Date(date_param.value) : new Date();

		weekdays.innerHTML = "";
		days.innerHTML = "";

		for (let i = 0; i < weekdaysShort.length; i++) {
			const weekday = document.createElement("div");
			weekday.classList.add("weekday");
			weekday.textContent = weekdaysShort[i];
			weekdays.appendChild(weekday);
		}
		/*
			값이 없다 : today -> select
			값이 있다 : 해당날짜로 -> select
		*/

		const firstWeekOfMonth = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
		const lastDayOfMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();

		for (let i = 0; i < firstWeekOfMonth; i++) {
			const emptyDay = document.createElement("div");
			days.appendChild(emptyDay);
		}

		for (let i = 1; i <= lastDayOfMonth; i++) {
			const day = document.createElement("div");
			day.classList.add("day");
			day.dataset.day = i;
			day.innerHTML = `<div class="day_val">${i}</div>`;
			days.appendChild(day);

			if (i === selectedDate.getDate() && date.getMonth() === selectedDate.getMonth() && date.getFullYear() === selectedDate.getFullYear()) {
				day.classList.add("selected");
			}
		}
		currentMonth.textContent = `${date.getFullYear()}.${months[date.getMonth()]}`;
		daysClickEvent();
	}

	function goToPrevMonth() {
		date.setMonth(date.getMonth() - 1);
		renderCalendar();
	}

	function goToNextMonth() {
		date.setMonth(date.getMonth() + 1);
		renderCalendar();
	}

	prevBtn.addEventListener("click", () => {
		goToPrevMonth();
	});

	nextBtn.addEventListener("click", () => {
		goToNextMonth();
	});

	dropdown_wrap.addEventListener("click", (event) => {
		event.stopPropagation();
	});

	dropdown.addEventListener("click", (event) => {
		if (calendar.style.display == 'block') {
			closeCalendar();
		}
		else {
			let allCalendar = document.querySelectorAll(".calendar");
			for (let i = 0; i < allCalendar.length; i++) {
				allCalendar[i].style.display = 'none';
			}
			calendar.style.display = 'block';
			if (status == 'orderlist-start') {
				calendarStart.classList.add("clicked");
				calendarEnd.classList.remove("clicked");
				calendarEnd.style.opacity = "0.5";
				calendarStart.style.opacity = "1";
			}
			else if (status == 'orderlist-end') {
				calendarEnd.classList.add("clicked");
				calendarStart.classList.remove("clicked");
				calendarStart.style.opacity = "0.5";
				calendarEnd.style.opacity = "1";
			}
			dateSearch.style.opacity = "0.5";
			renderCalendar();
		}
	});

	wrap.addEventListener("click", (event) => {
		closeCalendar();
	});

	function closeCalendar() {
		calendar.style.display = "none";
		calendarStart.classList.remove("clicked");
		calendarEnd.classList.remove("clicked");
		calendarStart.style.opacity = "1";
		calendarEnd.style.opacity = "1";
		dateSearch.style.opacity = "1";
	}

	function daysClickEvent() {
		let purchaseDateStr = "";

		switch (getLanguage()) {
			case "KR":
				purchaseDateStr = "구매일";
				break;
			case "EN":
				purchaseDateStr = "Date of purchase";
				break;
			case "CN":
				purchaseDateStr = "购买日期";
				break;
		}
		dropdown_wrap.querySelectorAll('.calendar-days .day').forEach(el => el.addEventListener('click', function (ev) {
			let date_param = document.querySelector(inputSelectorStr);
			let selected_chk = ev.currentTarget.classList.contains('selected');
			let param_chk = date_param.value.length > 0;

			if (selected_chk == true && param_chk == true) {
				ev.currentTarget.classList.remove('selected');
				document.querySelector(inputSelectorStr).value = null;

				if (status == 'orderlist') {
					console.log("check");
					dropdown_wrap.querySelector(".date-choice-btn").innerHTML = `
                        <img class="orderlist-calendar-img" src="/images/mypage/mypage_calendar_icon.png">
                        <span data-i18n="o_calendar_select"></span>                    
                    `;
				}
			}
			else {
				document.querySelectorAll('.calendar-days .day').forEach(el => el.classList.remove('selected'));
				let day = ev.currentTarget.dataset.day;

				if (Number(day) < 10) {
					day = '0' + day;
				}

				let month = dropdown_wrap.querySelector('.current-month').textContent;

				if (status == "bluemark") {
					let todayYear = new Date().getFullYear();
					let todayMonth = ('0' + (new Date().getMonth() + 1)).slice(-2);
					let today = ('0' + new Date().getDate()).slice(-2);
					let todayDate = new Date(`${todayYear}-${todayMonth}-${today}`);

					let splitMonth = month.split(".");
					let searchDate = new Date(`${splitMonth[0]}-${splitMonth[1]}-${day}`);

					if (todayDate < searchDate) {
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0114', null);
					}
					else {
						let dateInput = document.querySelector(".bluemark-purchase-date");
						document.querySelector('.purchase-date').dataset.selectdate = `${month}.${day}`;
						document.querySelector('.purchase-date').innerHTML = `${purchaseDateStr} : ${month}.${day}`;
						dateInput.value = `${splitMonth[0]}-${splitMonth[1]}-${day}`;
					}

					ev.currentTarget.classList.add('selected');
				} else {
					dropdown_wrap.querySelector(".selected-date").value = `${month}.${day}`;

					let startDate = document.querySelector(".selected-date.start").value.split(".").map(date => Number(date));
					let endDate = document.querySelector(".selected-date.end").value.split(".").map(date => Number(date));

					if (startDate.length > 2 && endDate.length > 2) {
						if (startDate[0] > endDate[0]) {
							makeMsgNoti(getLanguage(), 'MSG_F_WRN_0050', null);
							//notiModal(checkYearMsg);
							return false;
						}
						if ((startDate[0] == endDate[0]) && (startDate[1] > endDate[1])) {
							makeMsgNoti(getLanguage(), 'MSG_F_WRN_0049', null);
							//notiModal(checkMonthMsg);
							return false;
						}
						if ((startDate[0] == endDate[0]) && (startDate[1] == endDate[1]) && (startDate[2] > endDate[2])) {
							makeMsgNoti(getLanguage(), 'MSG_F_WRN_0048', null);
							//notiModal(checkDayMsg);
							return false;
						}
					}
					ev.currentTarget.classList.add('selected');
					dropdown_wrap.querySelector(".date-choice-btn").innerHTML = `<img class="orderlist-calendar-img" src="/images/mypage/mypage_calendar_icon.png">${month}.${day}`;
				}
				closeCalendar();
			}
		}));
	}
	renderCalendar();
}

function mobileAutoHyphen(target) {
	target.value = target.value
		.replace(/[^0-9]/g, '')
		.replace(/^(\d{0,3})(\d{0,4})(\d{0,4})$/g, "$1-$2-$3").replace(/(\-{1,2})$/g, "");
}