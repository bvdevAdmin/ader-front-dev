function Sidebar() {
    this.appendSidebar = (() => {
        const sidebar = document.createElement("div");
        sidebar.id = "sidebar";
        document.body.appendChild(sidebar);
    })();
    this.makeSidebar = (() => {
        const docflag = document.createDocumentFragment();
        const $sidebar = document.getElementById("sidebar");
        const sideWrap = document.createElement('div');
        let sideContent = "";
        sideWrap.className = "side__background";
        sideContent = `
	        <div class="side__wrap">
	            <div class="sidebar-close-btn btn_close_sidebar">
	                <svg xmlns="http://www.w3.org/2000/svg" width="12.707" height="12.707" viewBox="0 0 12.707 12.707">
	                    <path data-name="선 1772" transform="rotate(135 6.103 2.736)" style="fill:none;stroke:#343434" d="M16.969 0 0 .001"/>
	                    <path data-name="선 1787" transform="rotate(45 -.25 .606)" style="fill:none;stroke:#343434" d="M16.969.001 0 0"/>
	                </svg>
	            </div>
	            <div class="side__box">
	                
	            </div>
	        </div>
        `;
        
        sideWrap.innerHTML = sideContent;
        docflag.appendChild(sideWrap);
        $sidebar.appendChild(docflag);
        
        clickBtnCloseSidebar();
    })();
}

function clickBtnCloseSidebar() {
	let btn_close_sidebar = document.querySelector('.btn_close_sidebar');
	btn_close_sidebar.addEventListener('click',function() {
		sidebarClose();
	});
}