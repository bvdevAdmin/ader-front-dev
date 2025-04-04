function writeMileageListHtmlWeb(data, status) {
  let mileageListHtml = "";

  data.forEach(row => {
    let mileageHistoryHtml = "";
    let orderCodeHtml = "";
    let orderCode = row.order_code.split("_");
    
    if(orderCode.length > 1) {
      orderCodeHtml = `
        <p>${orderCode[0]}_</p>
        <p>${orderCode[1]}</p>
      `;
    } else {
      orderCodeHtml = `
        <p>${orderCode[0]}</p>
      `;
    }
    
    
    if(status == "ALL") {
      let mileageInc = "";
      let mileageDec = "";

      if(row.mileage_inc == "0") {
        mileageInc = row.mileage_inc;
        mileageDec = `- ${row.mileage_dec}`;
      }

      if(row.mileage_dec == "0") {
        
        if(row.mileage_code == "PIN" && row.mileage_unu > 0) {
          mileageInc = `
            <p>+ ${row.txt_mileage_unu}</p>
            <p class="mileage_unu_txt">(적립 예정)</p>
          `;
        } else {
          mileageInc = `+ ${row.mileage_inc}`;
        }

        mileageDec = row.mileage_dec;
      }

      mileageHistoryHtml = 
        `
          <td class="mileage_point_td">
              <p>${mileageInc}</p>
          </td>
          <td class="mileage_point_td">
              <p>${mileageDec}</p>
          </td>
        `;
    }

    if(status == "INC") {

      if(row.mileage_code == "PIN" && row.mileage_unu > 0) {
        mileageInc = `
            <p>+ ${row.txt_mileage_unu}</p>
            <p class="mileage_unu_txt">(적립 예정)</p>
          `;
      } else {
        mileageInc = `+ ${row.mileage_inc}`;
      }
      
      mileageHistoryHtml = 
        `
          <td class="mileage_point_td">
              <p>${mileageInc}</p>
          </td>
          <td class="mileage_point_td">
              <p>${row.mileage_sum}</p>
          </td>
        `;
    }

    if(status == "DEC") {
      mileageHistoryHtml = 
        `
          <td class="mileage_point_td">
              <p>- ${row.mileage_dec}</p>
          </td>
          <td class="mileage_point_td">
              <p>${row.mileage_sum}</p>
          </td>
        `;
    }

    mileageListHtml += 
      `
        <tr class="mileage_list_tr">
            <td>
                <p>${row.update_date}</p>
            </td>
            <td>
                <div class="underline">
                  ${orderCodeHtml}
                </div>
            </td>
            <td>
                <p>${row.mileage_type}</p>
            </td>
            <td>
                <p>${row.price_total}</p>
            </td>
            ${mileageHistoryHtml}
        </tr> 
      `;
  });

  return mileageListHtml;
}

function writeMileageListHtmlMobile(data, status) {
  let mileageListHtml = "";
  
  
  data.forEach(row => {
    let mileagePoint = "";
    let orderCodeHtml = "";
    let orderCode = row.order_code.split("_");
    
    if(orderCode.length > 1) {
      orderCodeHtml = `
        <p>${orderCode[0]}_</p>
        <p>${orderCode[1]}</p>
      `;
    } else {
      orderCodeHtml = `
        <p>${orderCode[0]}</p>
      `;
    }
    
    if(status == "ALL") {

      if(row.mileage_inc == "0") {
        mileagePoint = `- ${row.mileage_dec}`;
      }

      if(row.mileage_dec == "0") {
        
        if(row.mileage_code == "PIN" && row.mileage_unu > 0) {
          mileagePoint = `
            <p>+ ${row.txt_mileage_unu}</p>
            <p class="mileage_unu_txt">(적립 예정)</p>
          `;
        } else {
          mileagePoint = `+ ${row.mileage_inc}`;
        }
      }
    }

    if(status == "INC") {

      if(row.mileage_code == "PIN" && row.mileage_unu > 0) {
        mileagePoint = `
          <p>+ ${row.txt_mileage_unu}</p>
          <p class="mileage_unu_txt">(적립 예정)</p>
        `;
      } else {
        mileagePoint = `+ ${row.mileage_inc}`;
      }
    }

    if(status == "DEC") {
      mileagePoint = `- ${row.mileage_dec}`;
    }

    mileageListHtml += 
      `
        <tr class="mileage_list_tr">
            <td>
              <div class="underline">
                ${orderCodeHtml}
              </div>
            </td>
            <td>
                <p>${row.update_date}</p>
            </td>
            <td>
                <p>${row.mileage_type}</p>
            </td>
            <td class="mileage_point_td">
                <p>${mileagePoint}</p>
            </td>
        </tr> 
      `;
  });

  return mileageListHtml;
}