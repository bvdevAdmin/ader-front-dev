document.addEventListener("DOMContentLoaded", function() {
  getMileageUseList();
});

function getMileageUseList() {
  let mileageUseWrap = document.querySelector(".mileage__use__wrap");
  let mileageUseListWeb = mileageUseWrap.querySelector(".mileage_use_list_web");
  let mileageUseListMobile = mileageUseWrap.querySelector(".mileage_use_list_mobile");
  let showPaging = mileageUseWrap.querySelector(".mypage__paging");
  let rows = mileageUseWrap.querySelector(".mileage_list_rows").value;
  let page = mileageUseWrap.querySelector(".mileage_list_page").value;

  mileageUseListWeb.innerHTML = "";
  mileageUseListMobile.innerHTML = "";

  $.ajax({
      type: "post",
      data: {
          "country": getLanguage(),
          "list_type": "DEC",
          "rows": rows,
          "page": page
      },
      dataType: "json",
      url: api_location + "mypage/mileage/list/get",
      error: function() {
        makeMsgNoti(getLanguage(), "MSG_F_ERR_0066", null);
        //   notiModal("마일리지", "마일리지 정보조회에 실패했습니다.");
      },
      success: function(d) {
          let data = d.data;
          let total = d.total;

          if(data != null) {
            mileageUseListWeb.innerHTML = writeMileageListHtmlWeb(data, "DEC");
            mileageUseListMobile.innerHTML = writeMileageListHtmlMobile(data, "DEC");

            let showing_page = Math.ceil(total / rows);
            mypagePaging({
                total: total,
                el: showPaging,
                page: page,
                row: rows,
                show_paging: showing_page,
                use_form: mileageUseWrap,
                list_type: "DEC"
            }, getMileageUseList);
          } else {
            let exception_msg = "";

                switch (getLanguage()) {
                    case "KR" :
                        exception_msg = "조회 결과가 없습니다.";
                        break;
                    
                    case "EN" :
                        exception_msg = "There is no history.";
                        break;
                    
                    case "CN" :
                        exception_msg = "没有查询到相关资料。​";
                        break;

                }
              mileageUseListWeb.innerHTML = 
                  `
                      <tr class="mileage_list_tr">
                          <td class="mileage_no_history" colspan="6">
                              <p>${exception_msg}</p>
                          </td>
                      </tr>
                  `;

                mileageUseListMobile.innerHTML = 
                  `
                      <tr class="mileage_list_tr">
                          <td class="mileage_no_history" colspan="4">
                              <p>${exception_msg}</p>
                          </td>
                      </tr>
                  `;
          }
      }
  });

}