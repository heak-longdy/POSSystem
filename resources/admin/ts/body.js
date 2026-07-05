feather.replace();
Alpine.start();

$(document).ready(function () {
  // Scroll To Active
  const menu_active = $(
    ".sidebar .sidebar-wrapper .menu-list .menu-item.active"
  );
  const menu_list = menu_active.parents(".menu-list")[0];
  menu_list?.scrollTo({
    top: menu_active[0]?.offsetTop - menu_list?.clientHeight / 2,
    left: 0,
    behavior: "smooth",
  });
});

// content
const contentHeader = document.querySelector("#content");
const contentBody = document.querySelector("#content .content-body");
var lastScrollTop = 0;
contentBody?.addEventListener(
  "scroll",
  (e) => {
    var st = window.pageYOffset || contentBody.scrollTop;
    if (st > lastScrollTop) {
      contentHeader.classList.add("isScrolled");
    } else {
      contentHeader.classList.remove("isScrolled");
    }
  },
  false
);
var el = document.getElementById("jsScroll");
el?.addEventListener("click", function () {
  lastScrollTop = 0;
  contentBody?.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

require("s-event.js");
require("s-mask.js");
require("./package/sliderBar");
