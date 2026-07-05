/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/admin/ts/package/sliderBar.js":
/*!*************************************************!*\
  !*** ./resources/admin/ts/package/sliderBar.js ***!
  \*************************************************/
/***/ (() => {

var menuShowHide = localStorage.getItem("menu"); // SIDEBAR DROPDOWN

var allDropdown = document.querySelectorAll("#sidebar .side-dropdown");
var sidebar = document.getElementById("sidebar");
allDropdown.forEach(function (item) {
  var a = item.parentElement.querySelector("a:first-child");
  a.addEventListener("click", function (e) {
    e.preventDefault();

    if (!this.classList.contains("active")) {
      allDropdown.forEach(function (i) {
        var aLink = i.parentElement.querySelector("a:first-child");
        aLink.classList.remove("active");
        i.classList.remove("show");
      });
    }

    this.classList.toggle("active");
    item.classList.toggle("show");
  });
}); // var switchDark = localStorage.getItem("switchDarkMode");
// var switchMode = document.getElementById("switch-mode");
// if (switchDark == 1) {
//     document.body.classList.add("dark");
//     switchMode.checked = true;
// }
// switchMode.addEventListener("change", function () {
//     if (this.checked) {
//         document.body.classList.add("dark");
//         localStorage.setItem("switchDarkMode", 1);
//     } else {
//         document.body.classList.remove("dark");
//         localStorage.setItem("switchDarkMode", 0);
//     }
// });
// SIDEBAR COLLAPSE
// const toggleSidebar = document.querySelector("nav .toggle-sidebar");

var toggleSidebar = document.querySelector("button.toggle-sidebar");
var allSideDivider = document.querySelectorAll("#sidebar .divider");
menuShowHide == 0 ? sidebar.classList.toggle("hide") : "";

if (sidebar.classList.contains("hide")) {
  allSideDivider.forEach(function (item) {
    item.textContent = "-";
  });
  allDropdown.forEach(function (item) {
    var a = item.parentElement.querySelector("a:first-child");
    a.classList.remove("active");
    item.classList.remove("show");
  });
} else {
  allSideDivider.forEach(function (item) {
    item.textContent = item.dataset.text;
  });
}

toggleSidebar.addEventListener("click", function () {
  sidebar.classList.toggle("hide");
  sidebar.classList[1] ? localStorage.setItem("menu", 0) : localStorage.setItem("menu", 1);

  if (sidebar.classList.contains("hide")) {
    allSideDivider.forEach(function (item) {
      item.textContent = "-";
    });
    allDropdown.forEach(function (item) {
      var a = item.parentElement.querySelector("a:first-child");
      a.classList.remove("active");
      item.classList.remove("show");
    });
  } else {
    allSideDivider.forEach(function (item) {
      item.textContent = item.dataset.text;
    });
  }
});
sidebar.addEventListener("mouseleave", function () {
  if (this.classList.contains("hide")) {
    allDropdown.forEach(function (item) {
      var a = item.parentElement.querySelector("a:first-child");
      a.classList.remove("active");
      item.classList.remove("show");
    });
    allSideDivider.forEach(function (item) {
      item.textContent = "-";
    });
  }
});
sidebar.addEventListener("mouseenter", function () {
  if (this.classList.contains("hide")) {
    allDropdown.forEach(function (item) {
      var a = item.parentElement.querySelector("a:first-child");
      a.classList.remove("active");
      item.classList.remove("show");
    });
    allSideDivider.forEach(function (item) {
      item.textContent = item.dataset.text;
    });
  }
}); // PROFILE DROPDOWN

var profile = document.querySelector("nav .profile");
var imgProfile = profile.querySelector("img");
var dropdownProfile = profile.querySelector(".profile-link");
imgProfile.addEventListener("click", function (e) {
  e.preventDefault();
  dropdownProfile.classList.toggle("show");
});
window.addEventListener("click", function (e) {
  if (e.target !== imgProfile) {
    if (e.target !== dropdownProfile) {
      if (dropdownProfile.classList.contains("show")) {
        dropdownProfile.classList.remove("show");
      }
    }
  } // allMenu.forEach((item) => {
  //     const icon = item.querySelector(".icon");
  //     const menuLink = item.querySelector(".menu-link");
  //     if (e.target !== icon) {
  //         if (e.target !== menuLink) {
  //             if (menuLink.classList.contains("show")) {
  //                 menuLink.classList.remove("show");
  //             }
  //         }
  //     }
  // });

}); // Notification DROPDOWN

var notification = document.querySelector(".notificationGp");
var eventNotification = notification.querySelector(".notification");
var dropdownNotification = notification.querySelector(".notification-body");
eventNotification.addEventListener("click", function (e) {
  e.preventDefault();
  dropdownNotification.classList.toggle("show");
});
window.addEventListener("click", function (e) {
  if (!e.target.closest(".notificationGp")) {
    if (dropdownNotification.classList.contains("show")) {
      dropdownNotification.classList.remove("show");
    }
  }
}); // MENU

var allMenu = document.querySelectorAll("main .content-data .head .menu");
allMenu.forEach(function (item) {
  var icon = item.querySelector(".icon");
  var menuLink = item.querySelector(".menu-link");
  icon.addEventListener("click", function () {
    menuLink.classList.toggle("show");
  });
}); // MENU

var MenuDropDown = document.querySelectorAll(".menu");
MenuDropDown.forEach(function (item) {
  var icon = item.querySelector(".icon");
  var menuLink = item.querySelector(".menu-link");
  icon.addEventListener("click", function () {
    menuLink.classList.toggle("show");
  });
}); //slidBarScrollActive

var menu_activeDom = document.querySelector("#sidebar .side-menu .li .active"); // const menu_activeDom = document.querySelector("#sidebar .side-menu .navItemSiderbarGroup .navSidber li.active")

var menu_listDom = document.getElementsByClassName("side-menu")[0];
menu_listDom === null || menu_listDom === void 0 ? void 0 : menu_listDom.scrollTo({
  top: (menu_activeDom === null || menu_activeDom === void 0 ? void 0 : menu_activeDom.offsetTop) - (menu_listDom === null || menu_listDom === void 0 ? void 0 : menu_listDom.clientHeight) / 2,
  left: 0,
  behavior: "smooth"
}); // content
// const contentMain = document.querySelector("#content main");
// var el = document.getElementById("jsScroll");
// var lastScrollTop = 0;
// contentMain.addEventListener("scroll", (e) => {
//     var st = window.pageYOffset || contentMain.scrollTop;
//    if (st > lastScrollTop) {
//     el.classList.add("visible");
//    } else if (st < lastScrollTop) {
//     el.classList.remove("visible");
//    }
//    lastScrollTop = st <= 0 ? 0 : st;
// },false);
// el.addEventListener("click", function () {
//     lastScrollTop = 0;
//     contentMain.scrollTo({
//         top: 0,
//         behavior: "smooth",
//     });
// });

/***/ }),

/***/ "./node_modules/s-event.js/index.min.js":
/*!**********************************************!*\
  !*** ./node_modules/s-event.js/index.min.js ***!
  \**********************************************/
/***/ (() => {

// author : LY SARI
// s-event version: 1.0.0
// release date: 2022-02-09
(function () {
  const PREFIX = "s";
  const EVENT = [
    "click",
    "mouseover",
    "mouseout",
    "mousedown",
    "mouseup",
    "mousemove",
    "focus",
    "Keydown",
    "Keyup",
  ];
  const TYPE = ["fn", "link", "open"];
  let FULL_EVENT_ATTRIBUTES = [];
  EVENT.map((event) => {
    TYPE.map((type) => {
      FULL_EVENT_ATTRIBUTES.push(`${PREFIX}-${event}-${type}`);
    });
  });
  const elements = document.querySelectorAll("*");
  elements.forEach((item) => {
    item.getAttributeNames().map((attr) => {
      if (FULL_EVENT_ATTRIBUTES.includes(attr)) {
        const [prefix, event, type] = attr.split("-");
        const value = item.getAttribute(attr);
        item.addEventListener(event, () => {
          switch (type) {
            case "fn":
              value ? Function(value)() : false;
              break;
            case "link":
              value ? (window.location.href = value) : false;
              break;
            case "open":
              value ? window.open(value, "_blank") : false;
              break;
          }
        });
        item.removeAttribute(attr);
      }
    });
  });
})();


/***/ }),

/***/ "./node_modules/s-mask.js/index.min.js":
/*!*********************************************!*\
  !*** ./node_modules/s-mask.js/index.min.js ***!
  \*********************************************/
/***/ (() => {

// author : LY SARI
// s-event version: 1.0.0
// release date: 2022-03-07
(function () {
  "use strict";
  const element = document.querySelectorAll("[s-mask]");
  const maskConvert = (value, arg) => {
    if (value && value.length > 0 && isFinite(value)) {
      let mask = value.toString();
      let mask_result = "";
      let mask_index = 0;
      let arg_mask = arg.match(/#/gm);
      let mask_symbol =
        mask.length >= arg_mask.length ? mask.length : arg_mask.length;
      for (let i = 0; i < mask_symbol; i++) {
        if (arg[i].toLowerCase() == "#") {
          if (mask[i - mask_index]) {
            mask_result += mask[i - mask_index];
          }
        } else {
          mask_symbol++;
          mask_index++;
          mask_result += arg[i];
        }
      }
      return mask_result;
    }
    return value;
  };
  element.forEach((el) => {
    const value = el.innerHTML;
    const mask = el.getAttribute("s-mask");
    el.innerHTML = maskConvert(value, mask);
  });
})();


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!************************************!*\
  !*** ./resources/admin/ts/body.js ***!
  \************************************/
feather.replace();
Alpine.start();
$(document).ready(function () {
  var _menu_active$;

  // Scroll To Active
  var menu_active = $(".sidebar .sidebar-wrapper .menu-list .menu-item.active");
  var menu_list = menu_active.parents(".menu-list")[0];
  menu_list === null || menu_list === void 0 ? void 0 : menu_list.scrollTo({
    top: ((_menu_active$ = menu_active[0]) === null || _menu_active$ === void 0 ? void 0 : _menu_active$.offsetTop) - (menu_list === null || menu_list === void 0 ? void 0 : menu_list.clientHeight) / 2,
    left: 0,
    behavior: "smooth"
  });
}); // content

var contentHeader = document.querySelector("#content");
var contentBody = document.querySelector("#content .content-body");
var lastScrollTop = 0;
contentBody === null || contentBody === void 0 ? void 0 : contentBody.addEventListener("scroll", function (e) {
  var st = window.pageYOffset || contentBody.scrollTop;

  if (st > lastScrollTop) {
    contentHeader.classList.add("isScrolled");
  } else {
    contentHeader.classList.remove("isScrolled");
  }
}, false);
var el = document.getElementById("jsScroll");
el === null || el === void 0 ? void 0 : el.addEventListener("click", function () {
  lastScrollTop = 0;
  contentBody === null || contentBody === void 0 ? void 0 : contentBody.scrollTo({
    top: 0,
    behavior: "smooth"
  });
});

__webpack_require__(/*! s-event.js */ "./node_modules/s-event.js/index.min.js");

__webpack_require__(/*! s-mask.js */ "./node_modules/s-mask.js/index.min.js");

__webpack_require__(/*! ./package/sliderBar */ "./resources/admin/ts/package/sliderBar.js");
})();

/******/ })()
;