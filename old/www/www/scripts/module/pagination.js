/**
   * @author SIMJAE
   * @param {string} el: 페이지네이션을 추가할 DOM 요소
   * @param {number} total: 전체 데이터 개수
   * @param {number} page: 현재 페이지
   * @param {number} row: 한 페이지에 보여줄 데이터 개수
   * @param {number} show_paging: 한 번에 보여줄 페이지 수
   * @param {string} api: 호출할 api함수명 
   */
// class Pagination {
//     constructor(obj) {
//         this.obj = obj;
//         this.totalPage = Math.ceil(obj.total / obj.row);
//         this.currentPage = obj.page || 1;
//         this.showPaging = obj.show_paging || 5;
//         this.prev = this.currentPage - this.showPaging;
//         this.next = this.currentPage + this.showPaging;
//         this.start = this.currentPage - Math.ceil(this.showPaging / 2) + 1;
//         this.end = this.start + this.showPaging - 1;
//         this.orderStatus = document.querySelector('#param_status').value;
//         this.api = obj.api || ''; 
//         this.paging = [];
//     }

//     setPrev() {
//         if (this.prev < 1) {
//             this.prev = 1;
//         }
//     }

//     setNext() {
//         if (this.next > this.totalPage) {
//             this.next = this.totalPage;
//         }
//     }

//     setStart() {
//         if (this.start < 1) {
//             this.start = 1;
//         }
//     }

//     setEnd() {
//         if (this.end > this.totalPage) {
//             this.end = this.totalPage;
//             this.start = this.end - this.showPaging + 1;
//             if (this.start < 1) {
//                 this.start = 1;
//             }
//         }
//     }

//     setPaging() {
//         for (let i = this.start; i <= this.end; i++) {
//             this.paging.push(`<div class="page number ${((i == this.currentPage) ? 'now' : '')}" data-page="${i}">${i}</div>`);
//         }
//     }
//     updateUiPagination(currentPage){
//         this.obj.el.querySelectorAll(".page.number").forEach(number => {
//             if(parseInt(number.dataset.page) == currentPage){
//                 console.log(number.classList.add('now'));
//             }else{
//                 number.classList.remove('now')
//             }
//         })
//         if(this.currentPage == this.prev ){
//             this.obj.el.querySelector(".navigation.prev").classList.add('opacity');
//         }else {
//             this.obj.el.querySelector(".navigation.prev").classList.remove('opacity');
//         }
//         if(this.currentPage == this.next ){
//             this.obj.el.querySelector(".navigation.next").classList.add('opacity');
//         }else {
//             this.obj.el.querySelector(".navigation.next").classList.remove('opacity');
//         }

//     }
//     setPagination() {
//         this.setPrev();
//         this.setNext();
//         this.setStart();
//         this.setEnd();
//         this.setPaging();

//         const pagePaging = document.createElement('div');
//         pagePaging.classList.add('custom-pagination');
//         pagePaging.innerHTML = `
//             <div class="navigation prev ${((this.currentPage == this.start) ? 'opacity':'')}" data-page="${this.prev}" }"><</div>
//             ${this.paging.join("")}
//             <div class="navigation next ${((this.currentPage == this.end) ? 'opacity':'')}" " data-page="${this.next}">></div>
//         `;
//         this.obj.el.appendChild(pagePaging);

//         this.obj.el.querySelectorAll(".page").forEach(page => {
//             page.addEventListener('click', () => {
//                 const newPage = parseInt(page.dataset.page);
//                 this.currentPage = newPage; // 현재 페이지 번호 변경
//                 console.log("🏂 ~ file: pagination.js:79 ~ Pagination ~ page.addEventListener ~ newPage:", newPage)
//                 this.updateUiPagination(newPage);
//                 window.scrollTo(0, 0);
//             });
//         });
//     }
// }
class Pagination {
    constructor(obj) {
        this.obj = obj;
        this.totalPage = Math.ceil(obj.total / obj.row);
        this.currentPage = obj.page || 1;
        this.showPaging = obj.show_paging || 5;
        this.orderStatus = document.querySelector("#param_status").value;
        this.api = obj.api || "";
        this.paging = [];
    }

    setPrev() {
        this.prev = this.currentPage - this.showPaging;
        if (this.prev < 1) {
            this.prev = 1;
        }
    }

    setNext() {
        this.next = this.currentPage + this.showPaging;
        if (this.next > this.totalPage) {
            this.next = this.totalPage;
        }
    }

    setStart() {
        this.start = this.currentPage - Math.floor(this.showPaging / 2);
        if (this.start < 1) {
            this.start = 1;
        }
        if (this.currentPage > this.totalPage - Math.floor(this.showPaging / 2)) {
            this.start = this.totalPage - this.showPaging + 1;
            if (this.start < 1) {
                this.start = 1;
            }
        }
    }

    setEnd() {
        this.end = this.start + this.showPaging - 1;
        if (this.end > this.totalPage) {
            this.end = this.totalPage;
        }
    }

    setPaging() {
        for (let i = this.start; i <= this.end; i++) {
            this.paging.push(
                `<div class="page number ${i === this.currentPage ? "now" : ""}" data-page="${i}">${i}</div>`
            );
        }
    }

    updateUiPagination(currentPage) {
        this.obj.el.querySelectorAll(".page.number").forEach((number) => {
            if (parseInt(number.dataset.page) === currentPage) {
                number.classList.add("now");
            } else {
                number.classList.remove("now");
            }
        });
        this.obj.el.querySelector(".navigation.prev").classList.toggle("opacity", this.currentPage === this.start);
        this.obj.el.querySelector(".navigation.next").classList.toggle("opacity", this.currentPage === this.end);
    }

    setPagination() {
        this.currentPage = this.obj.page || 1; // 현재 페이지 업데이트
        this.setPrev();
        this.setNext();
        this.setStart();
        this.setEnd();
        this.setPaging();
      
        const pagePaging = document.createElement("div");
        pagePaging.classList.add("custom-pagination");
        pagePaging.innerHTML = `
          <div class="navigation prev ${this.currentPage === this.start ? "opacity" : ""}" data-page="${this.prev}"><</div>
          ${this.paging.join("")}
          <div class="navigation next ${this.currentPage === this.end ? "opacity" : ""}" data-page="${this.next}">></div>
        `;
        this.obj.el.innerHTML = "";
        this.obj.el.appendChild(pagePaging);
      
        this.obj.el.querySelectorAll(".page").forEach((page) => {
          page.addEventListener("click", () => {
            const newPage = parseInt(page.dataset.page);
            this.currentPage = newPage; // 현재 페이지 업데이트
            this.updateUiPagination(newPage);
            window.scrollTo(0, 0);
          });
        });
      
        const prevButton = this.obj.el.querySelector(".navigation.prev");
        prevButton.addEventListener("click", () => {
          if (this.currentPage > 1) {
            this.currentPage = this.prev; // 현재 페이지 업데이트
            this.setPrev();
            this.setNext();
            this.setStart();
            this.setEnd();
            this.setPaging();
            this.updateUiPagination(this.currentPage);
            window.scrollTo(0, 0);
          }
        });
        prevButton.style.display = this.start === 1 ? "none" : "inline-block";
      
        const nextButton = this.obj.el.querySelector(".navigation.next");
        nextButton.addEventListener("click", () => {
          if (this.currentPage < this.totalPage) {
            this.currentPage = this.next; // 현재 페이지 업데이트
            this.setPrev();
            this.setNext();
            this.setStart();
            this.setEnd();
            this.setPaging();
            this.updateUiPagination(this.currentPage);
            window.scrollTo(0, 0);
          }
        });
        nextButton.style.display = this.end === this.totalPage ? "none" : "inline-block";
      }
      
}



// 페이지네이션 생성
// const data = {
//     total: 30,
//     row: 5,
//     page: 1,
//     show_paging: 5,
//     el: document.querySelector('#pagination'),
//     api: 'https://example.com/api',
// };

//const pagination = new Pagination(data);
//pagination.setPagination();