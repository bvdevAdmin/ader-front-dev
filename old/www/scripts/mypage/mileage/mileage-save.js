document.addEventListener("DOMContentLoaded", function() {
  getMileageSaveList();
});

function getMileageSaveList() {
  let mileageSaveWrap = document.querySelector(".mileage__save__wrap");
  let mileageSaveListWeb = mileageSaveWrap.querySelector(".mileage_save_list_web");
  let mileageSaveListMobile = mileageSaveWrap.querySelector(".mileage_save_list_mobile");
  let showPaging = mileageSaveWrap.querySelector(".mypage__paging");
  let rows = mileageSaveWrap.querySelector(".mileage_list_rows").value;
  let page = mileageSaveWrap.querySelector(".mileage_list_page").value;

  mileageSaveListWeb.innerHTML = "";
  mileageSaveListMobile.innerHTML = "";

  $.ajax({
      type: "post",
      data: {
          "country": getLanguage(),
          "list_type": "INC",
          "rows": rows,
          "page": page
      },
      dataType: "json",
      url: api_location + "mypage/mileage/list/get",
      error: function() {
        makeMsgNoti(getLanguage(), 'MSG_F_ERR_0066', null);
        //   notiModal("마일리지", "마일리지 정보조회에 실패했습니다.");
      },
      success: function(d) {
          let data = d.data;
          let total = d.total;

          if(data != null) {
            mileageSaveListWeb.innerHTML = writeMileageListHtmlWeb(data, "INC");
            mileageSaveListMobile.innerHTML = writeMileageListHtmlMobile(data, "INC");

            let showing_page = Math.ceil(total / rows);
            mypagePaging({
                total: total,
                el: showPaging,
                page: page,
                row: rows,
                show_paging: showing_page,
                use_form: mileageSaveWrap,
                list_type: "INC"
            }, getMileageSaveList);
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
            mileageSaveListWeb.innerHTML = 
              `
                  <tr class="mileage_list_tr">
                      <td class="mileage_no_history" colspan="6">
                          <p>${exception_msg}</p>
                      </td>
                  </tr>
              `;

            mileageSaveListMobile.innerHTML = 
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