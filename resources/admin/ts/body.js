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

// content scroll-to-top handler
function checkScrollState() {
  const windowScroll =
    window.pageYOffset ||
    document.documentElement.scrollTop ||
    document.body.scrollTop ||
    0;
  const contentBody = document.querySelector("#content .content-body");
  const formAdmin = document.querySelector(".form-admin");
  const bookingUi = document.querySelector(".booking-order-ui");

  const currentScroll = Math.max(
    windowScroll,
    contentBody ? contentBody.scrollTop : 0,
    formAdmin ? formAdmin.scrollTop : 0,
    bookingUi ? bookingUi.scrollTop : 0
  );

  const contentHeader = document.querySelector("#content");
  const scrollBtn = document.getElementById("jsScroll");

  if (currentScroll > 60) {
    contentHeader?.classList.add("isScrolled");
    scrollBtn?.classList.add("visible");
  } else {
    contentHeader?.classList.remove("isScrolled");
    scrollBtn?.classList.remove("visible");
  }
}

// Global scroll capture to catch window, .content-body, .form-admin, etc.
window.addEventListener("scroll", checkScrollState, true);

var el = document.getElementById("jsScroll");
el?.addEventListener("click", function () {
  window.scrollTo({ top: 0, behavior: "smooth" });
  document.documentElement.scrollTo({ top: 0, behavior: "smooth" });
  document.body.scrollTo({ top: 0, behavior: "smooth" });
  document.querySelector("#content .content-body")?.scrollTo({
    top: 0,
    behavior: "smooth",
  });
  document.querySelector(".form-admin")?.scrollTo({
    top: 0,
    behavior: "smooth",
  });
  document.querySelector(".booking-order-ui")?.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

require("s-event.js");
require("s-mask.js");
require("./package/sliderBar");
