/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/admin/ts/package/product-image-bg.js":
/*!********************************************************!*\
  !*** ./resources/admin/ts/package/product-image-bg.js ***!
  \********************************************************/
/***/ (() => {

/**
 * Product Image Background Color Detection
 *
 * Uses Canvas to sample edge pixels of each product image and applies the
 * detected background color to its wrapper via a CSS custom property.
 *
 * Only processes wrappers that carry the attribute `data-detect-bg="1"`.
 * When an image fails to load (onerror swaps to default), the attribute is
 * removed so no detection runs for placeholder images.
 */
(function () {
  'use strict'; // In-memory cache: image src → { left: 'rgb(…)', right: 'rgb(…)' }

  var colorCache = {}; // Shared off-screen canvas (reused for every image)

  var canvas = null;
  var ctx = null;

  function getCanvas() {
    if (!canvas) {
      canvas = document.createElement('canvas');
      ctx = canvas.getContext('2d', {
        willReadFrequently: true
      });
    }

    return {
      canvas: canvas,
      ctx: ctx
    };
  }
  /**
   * Sample edge pixels and return left/right median colors.
   *
   * @param {HTMLImageElement} img - A fully-loaded <img> element
   * @returns {{ left: string, right: string }} RGB color strings
   */


  function detectEdgeColors(img) {
    var c = getCanvas();
    var w = img.naturalWidth;
    var h = img.naturalHeight;
    if (!w || !h) return null; // Draw at a small scale (max 80px tall) for performance

    var scale = Math.min(1, 80 / h);
    var sw = Math.round(w * scale);
    var sh = Math.round(h * scale);
    c.canvas.width = sw;
    c.canvas.height = sh;

    try {
      c.ctx.drawImage(img, 0, 0, sw, sh);
    } catch (e) {
      // SecurityError when cross-origin tainting prevents getImageData
      return null;
    } // Determine how many pixels to sample from each edge
    // Sample a strip ~5% of the width on each side, minimum 1px, max 4px


    var stripW = Math.max(1, Math.min(4, Math.round(sw * 0.05))); // Skip top/bottom ~10% to avoid corner artifacts

    var yStart = Math.max(0, Math.round(sh * 0.1));
    var yEnd = Math.max(yStart + 1, Math.round(sh * 0.9));
    var leftPixels = [];
    var rightPixels = [];

    try {
      // Left edge strip
      var leftData = c.ctx.getImageData(0, yStart, stripW, yEnd - yStart);

      for (var i = 0; i < leftData.data.length; i += 4) {
        var a = leftData.data[i + 3];
        if (a < 128) continue; // skip transparent

        leftPixels.push([leftData.data[i], leftData.data[i + 1], leftData.data[i + 2]]);
      } // Right edge strip


      var rightData = c.ctx.getImageData(sw - stripW, yStart, stripW, yEnd - yStart);

      for (var j = 0; j < rightData.data.length; j += 4) {
        var a2 = rightData.data[j + 3];
        if (a2 < 128) continue;
        rightPixels.push([rightData.data[j], rightData.data[j + 1], rightData.data[j + 2]]);
      }
    } catch (e) {
      // SecurityError: tainted canvas
      return null;
    }

    if (!leftPixels.length && !rightPixels.length) return null;
    var leftColor = leftPixels.length ? medianColor(leftPixels) : null;
    var rightColor = rightPixels.length ? medianColor(rightPixels) : null; // Fall back to whichever side we have

    if (!leftColor) leftColor = rightColor;
    if (!rightColor) rightColor = leftColor;
    return {
      left: leftColor,
      right: rightColor
    };
  }
  /**
   * Compute the median color from an array of [r, g, b] values.
   * Median is more robust than average against outlier product pixels.
   */


  function medianColor(pixels) {
    if (!pixels.length) return 'rgb(241,245,249)';
    var rs = [],
        gs = [],
        bs = [];

    for (var i = 0; i < pixels.length; i++) {
      rs.push(pixels[i][0]);
      gs.push(pixels[i][1]);
      bs.push(pixels[i][2]);
    }

    rs.sort(numSort);
    gs.sort(numSort);
    bs.sort(numSort);
    var mid = Math.floor(pixels.length / 2);
    return 'rgb(' + rs[mid] + ',' + gs[mid] + ',' + bs[mid] + ')';
  }

  function numSort(a, b) {
    return a - b;
  }
  /**
   * Check if two RGB color strings are "close enough" to use a single solid
   * color rather than a gradient.
   */


  function colorsAreSimilar(c1, c2) {
    if (c1 === c2) return true;
    var p1 = parseRgb(c1);
    var p2 = parseRgb(c2);
    if (!p1 || !p2) return false;
    var diff = Math.abs(p1[0] - p2[0]) + Math.abs(p1[1] - p2[1]) + Math.abs(p1[2] - p2[2]);
    return diff < 30; // threshold: combined channel difference < 30
  }

  function parseRgb(s) {
    var m = s.match(/(\d+)/g);
    return m ? [parseInt(m[0]), parseInt(m[1]), parseInt(m[2])] : null;
  }
  /**
   * Apply detected colors to a single .product-image-wrapper element.
   */


  function applyBg(wrapper, colors) {
    if (!colors) return;

    if (colorsAreSimilar(colors.left, colors.right)) {
      wrapper.style.setProperty('--product-bg', colors.left);
      wrapper.style.background = 'var(--product-bg)';
    } else {
      // Left-to-right gradient for different edge colors
      var grad = 'linear-gradient(to right, ' + colors.left + ' 0%, ' + colors.left + ' 15%, ' + colors.right + ' 85%, ' + colors.right + ' 100%)';
      wrapper.style.background = grad;
    }
  }
  /**
   * Process a single <img> inside a wrapper.
   */


  function processImage(img) {
    var wrapper = img.closest('.product-image-wrapper');
    if (!wrapper || !wrapper.hasAttribute('data-detect-bg')) return;
    var src = img.src;
    if (!src) return; // Check cache first

    if (colorCache[src]) {
      applyBg(wrapper, colorCache[src]);
      return;
    } // Detect colors


    var colors = detectEdgeColors(img);

    if (colors) {
      colorCache[src] = colors;
      applyBg(wrapper, colors);
    }
  }
  /**
   * Scan a root element for all product image wrappers and process them.
   * Handles both already-loaded and not-yet-loaded images.
   */


  function scanImages(root) {
    if (!root || !root.querySelectorAll) return;
    var wrappers = root.querySelectorAll('.product-image-wrapper[data-detect-bg]');

    for (var i = 0; i < wrappers.length; i++) {
      var img = wrappers[i].querySelector('img.product-image-main');
      if (!img) continue;

      if (img.complete && img.naturalWidth > 0) {
        processImage(img);
      } else {
        // Wait for load
        (function (imgEl) {
          imgEl.addEventListener('load', function onLoad() {
            imgEl.removeEventListener('load', onLoad);
            processImage(imgEl);
          });
        })(img);
      }
    }
  } // --- Initialization ---
  // Process images already in the DOM


  function init() {
    scanImages(document);
  } // Run when DOM is ready


  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    // Already loaded (script loaded defer or at end of body)
    setTimeout(init, 0);
  } // Observe DOM mutations (MDI tab workspace loads content via AJAX)


  if (typeof MutationObserver !== 'undefined') {
    var observer = new MutationObserver(function (mutations) {
      for (var i = 0; i < mutations.length; i++) {
        var added = mutations[i].addedNodes;

        for (var j = 0; j < added.length; j++) {
          var node = added[j];
          if (node.nodeType !== 1) continue; // If the added node itself is a wrapper

          if (node.classList && node.classList.contains('product-image-wrapper') && node.hasAttribute('data-detect-bg')) {
            var img = node.querySelector('img.product-image-main');

            if (img) {
              if (img.complete && img.naturalWidth > 0) {
                processImage(img);
              } else {
                (function (imgEl) {
                  imgEl.addEventListener('load', function onLoad() {
                    imgEl.removeEventListener('load', onLoad);
                    processImage(imgEl);
                  });
                })(img);
              }
            }
          } // Or scan descendants


          scanImages(node);
        }
      }
    });
    observer.observe(document.body || document.documentElement, {
      childList: true,
      subtree: true
    });
  } // Expose for manual invocation (e.g., after AJAX pagination reload)


  window.$detectProductImageBg = scanImages;
})();

/***/ }),

/***/ "./resources/admin/ts/package/sliderBar.js":
/*!*************************************************!*\
  !*** ./resources/admin/ts/package/sliderBar.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

var menuShowHide = localStorage.getItem("menu");

var _require = __webpack_require__(/*! ../workspace/sidebar-active */ "./resources/admin/ts/workspace/sidebar-active.js"),
    updateSidebarActive = _require.updateSidebarActive; // SIDEBAR DROPDOWN


var allDropdown = document.querySelectorAll("#sidebar .side-dropdown");
var sidebar = document.getElementById("sidebar");
updateSidebarActive(window.location.href);
allDropdown.forEach(function (item) {
  var a = item.parentElement.querySelector("a:first-child");
  a.addEventListener("click", function (e) {
    e.preventDefault();

    if (!item.classList.contains("show")) {
      allDropdown.forEach(function (i) {
        var aLink = i.parentElement.querySelector("a:first-child");
        aLink.classList.toggle("active", aLink.dataset.routeActive === "true");
        i.classList.remove("show");
      });
    }

    item.classList.toggle("show");
    this.classList.toggle("active", item.classList.contains("show") || this.dataset.routeActive === "true");
  });
}); // SIDEBAR LINK NAVIGATION (Suppresses browser bottom-left URL preview)

var sidebarLinks = document.querySelectorAll("#sidebar .side-menu a[data-url]");
sidebarLinks.forEach(function (link) {
  link.addEventListener("click", function (e) {
    if (!this.parentElement.querySelector(".side-dropdown")) {
      var url = this.getAttribute("data-url");

      if (url && url !== "#") {
        if (e.ctrlKey || e.metaKey) {
          window.open(url, "_blank");
        } else if (window.MDI && typeof window.MDI.openUrlInTab === "function") {
          e.preventDefault();
          var title = this.textContent.trim();
          var iconEl = this.querySelector("i.icon");
          var icon = "bx bx-file";

          if (iconEl) {
            var iconClasses = Array.from(iconEl.classList).filter(function (c) {
              return c !== "icon";
            });
            if (iconClasses.length > 0) icon = iconClasses.join(" ");
          }

          window.MDI.openUrlInTab(url, title, icon);
        } else {
          window.location.href = url;
        }
      }
    }
  });
  link.addEventListener("auxclick", function (e) {
    if (e.button === 1) {
      var url = this.getAttribute("data-url");

      if (url && url !== "#") {
        window.open(url, "_blank");
      }
    }
  });
});
var allSidebarInteractive = document.querySelectorAll("#sidebar .side-menu a, #sidebar .brand");
allSidebarInteractive.forEach(function (el) {
  el.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      this.click();
    }
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

if (menuShowHide === "0") {
  sidebar.classList.add("hide");
} else if (menuShowHide === "1") {
  sidebar.classList.remove("hide");
}

if (sidebar.classList.contains("hide")) {
  allSideDivider.forEach(function (item) {
    item.textContent = "-";
  });
  allDropdown.forEach(function (item) {
    var a = item.parentElement.querySelector("a:first-child");
    a.classList.toggle("active", a.dataset.routeActive === "true");
    item.classList.remove("show");
  });
} else {
  allSideDivider.forEach(function (item) {
    item.textContent = item.dataset.text;
  });
}

toggleSidebar.addEventListener("click", function () {
  sidebar.classList.toggle("hide");

  if (sidebar.classList.contains("hide")) {
    localStorage.setItem("menu", "0");
    allSideDivider.forEach(function (item) {
      item.textContent = "-";
    });
    allDropdown.forEach(function (item) {
      var a = item.parentElement.querySelector("a:first-child");
      a.classList.toggle("active", a.dataset.routeActive === "true");
      item.classList.remove("show");
    });
  } else {
    localStorage.setItem("menu", "1");
    allSideDivider.forEach(function (item) {
      item.textContent = item.dataset.text;
    });
    updateSidebarActive(window.location.href);
  }
});
sidebar.addEventListener("mouseleave", function () {
  if (this.classList.contains("hide")) {
    allDropdown.forEach(function (item) {
      var a = item.parentElement.querySelector("a:first-child");
      a.classList.toggle("active", a.dataset.routeActive === "true");
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
      a.classList.toggle("active", a.dataset.routeActive === "true");
      item.classList.remove("show");
    });
    allSideDivider.forEach(function (item) {
      item.textContent = item.dataset.text;
    });
  }
}); // PROFILE DROPDOWN

var profile = document.querySelector("nav .profile");

if (profile && !profile.hasAttribute("x-data") && !profile.closest("[x-data]")) {
  var imgProfile = profile.querySelector("img");
  var dropdownProfile = profile.querySelector(".profile-link");

  if (imgProfile && dropdownProfile) {
    imgProfile.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      dropdownProfile.classList.toggle("show");
    });
    window.addEventListener("click", function (e) {
      if (!e.target.closest(".profile")) {
        if (dropdownProfile.classList.contains("show")) {
          dropdownProfile.classList.remove("show");
        }
      }
    });
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
// Notification DROPDOWN


var notification = document.querySelector(".header.main-header-navbar .notificationGp");

if (notification) {
  var eventNotification = notification.querySelector(".notification");
  var dropdownNotification = notification.querySelector(".notification-body");

  if (eventNotification && dropdownNotification) {
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
    });
  }
} // MENU


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

/***/ "./resources/admin/ts/select2-lifecycle.js":
/*!*************************************************!*\
  !*** ./resources/admin/ts/select2-lifecycle.js ***!
  \*************************************************/
/***/ (() => {

// Page components own Select2 options and bindings. Only manage shared teardown
// here; automatically enhancing every select would discard AJAX configuration.
var $ = window.jQuery;
$(document).on('select2:open.workspaceSelect2', function () {
  window.$select2FocusInputSearch();
});
window.addEventListener('workspace:tab-change', function () {
  $('select.select2-hidden-accessible').each(function () {
    var instance = $(this).data('select2');
    if (instance && instance.isOpen()) instance.close();
  });
}); // Covers closed/refreshed tabs, validation replacements and Alpine x-if content.
// A moved node remains connected and must retain its selection and handlers.

new MutationObserver(function (records) {
  records.forEach(function (record) {
    return record.removedNodes.forEach(function (node) {
      if (node.nodeType !== 1 || node.isConnected) return;
      $(node).find('select.select2-hidden-accessible').add($(node).filter('select.select2-hidden-accessible')).each(function () {
        var instance = $(this).data('select2');

        if (instance) {
          // Select2 4.0 removes its detached dropdown wrapper on close.
          if (instance.isOpen()) instance.close();
          instance.destroy();
        }
      });
    });
  });
}).observe(document.body, {
  childList: true,
  subtree: true
});

/***/ }),

/***/ "./resources/admin/ts/workspace/s-events.js":
/*!**************************************************!*\
  !*** ./resources/admin/ts/workspace/s-events.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "initSEvents": () => (/* binding */ initSEvents),
/* harmony export */   "initSMask": () => (/* binding */ initSMask)
/* harmony export */ });
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/**
 * POS Enterprise MDI - s-event & s-mask handler
 * Enhanced replacement for s-event.js and s-mask.js with full MDI tab support.
 */
var PREFIX = "s";
var EVENT = ["click", "mouseover", "mouseout", "mousedown", "mouseup", "mousemove", "focus", "Keydown", "Keyup"];
var TYPE = ["fn", "link", "open"];
var FULL_EVENT_ATTRIBUTES = [];
EVENT.forEach(function (event) {
  TYPE.forEach(function (type) {
    FULL_EVENT_ATTRIBUTES.push("".concat(PREFIX, "-").concat(event, "-").concat(type));
  });
});
function initSEvents() {
  var container = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
  if (!container) return;
  var elements = container.querySelectorAll ? container.querySelectorAll("*") : [];
  elements.forEach(function (item) {
    if (item._s_event_initialized) return;
    var attrNames = item.getAttributeNames ? item.getAttributeNames() : [];
    attrNames.forEach(function (attr) {
      if (FULL_EVENT_ATTRIBUTES.includes(attr)) {
        item._s_event_initialized = true;

        var _attr$split = attr.split("-"),
            _attr$split2 = _slicedToArray(_attr$split, 3),
            prefix = _attr$split2[0],
            event = _attr$split2[1],
            type = _attr$split2[2];

        var value = item.getAttribute(attr);
        item.addEventListener(event, function (e) {
          switch (type) {
            case "fn":
              if (value) {
                try {
                  Function(value)();
                } catch (err) {
                  console.error("s-event fn error:", err);
                }
              }

              break;

            case "link":
              if (value && value !== "#" && !value.startsWith("javascript:")) {
                // If MDI is active, route internal admin links into MDI tabs!
                try {
                  var url = new URL(value, window.location.origin);

                  if (window.MDI && url.origin === window.location.origin && url.pathname.startsWith("/admin") && !url.pathname.includes("logout") && !url.pathname.includes("auth")) {
                    var _item$innerText;

                    e.preventDefault();
                    e.stopPropagation(); // Check if it's an export / download / destructive action

                    var lowerVal = value.toLowerCase();

                    if (lowerVal.includes("/export") || lowerVal.includes("/download") || lowerVal.includes("/print") || lowerVal.endsWith(".xlsx") || lowerVal.endsWith(".pdf") || lowerVal.endsWith(".csv") || lowerVal.includes("delete") || lowerVal.includes("destroy") || item.classList.contains("delete") || item.classList.contains("text-danger")) {
                      window.location.href = value;
                      return;
                    }

                    var targetPath = url.pathname + url.search;
                    var currentTab = window.MDI.tabs.find(function (t) {
                      return t.key === window.MDI.activeTabKey;
                    }); // If reload button on current active tab

                    if (currentTab && (url.href === window.location.href || currentTab.url === targetPath)) {
                      window.MDI.refreshTab(window.MDI.activeTabKey);
                      return;
                    } // Extract title


                    var title = item.getAttribute("title") || ((_item$innerText = item.innerText) === null || _item$innerText === void 0 ? void 0 : _item$innerText.trim()) || "";

                    if (!title) {
                      if (item.classList.contains("head-icon") || item.querySelector('[data-feather="arrow-left"]') || item.getAttribute("data-feather") === "arrow-left") {
                        title = "Back";
                      }
                    } // Extract icon


                    var icon = "bx bx-file";

                    if (item.classList.contains("btn-create") || lowerVal.includes("/create")) {
                      icon = "bx bx-plus-circle";
                    } else if (lowerVal.includes("/list")) {
                      icon = "bx bx-list-ul";
                    }

                    window.MDI.openUrlInTab(targetPath, title, icon);
                    return;
                  }
                } catch (err) {} // Default external or non-admin behavior


                window.location.href = value;
              }

              break;

            case "open":
              if (value) {
                window.open(value, "_blank");
              }

              break;
          }
        }); // NOTE: We do NOT remove the attribute so inspection and delegation work reliably
      }
    });
  });
}
function initSMask() {
  var container = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
  if (!container) return;
  var elements = container.querySelectorAll ? container.querySelectorAll("[s-mask]") : [];

  var maskConvert = function maskConvert(value, arg) {
    if (value && value.length > 0 && isFinite(value)) {
      var mask = value.toString();
      var mask_result = "";
      var mask_index = 0;
      var arg_mask = arg.match(/#/gm);
      var mask_symbol = mask.length >= (arg_mask ? arg_mask.length : 0) ? mask.length : arg_mask.length;

      for (var i = 0; i < mask_symbol; i++) {
        var _arg$i;

        if (((_arg$i = arg[i]) === null || _arg$i === void 0 ? void 0 : _arg$i.toLowerCase()) === "#") {
          if (mask[i - mask_index]) {
            mask_result += mask[i - mask_index];
          }
        } else if (arg[i]) {
          mask_symbol++;
          mask_index++;
          mask_result += arg[i];
        }
      }

      return mask_result;
    }

    return value;
  };

  elements.forEach(function (el) {
    var value = el.innerHTML;
    var mask = el.getAttribute("s-mask");

    if (mask) {
      el.innerHTML = maskConvert(value, mask);
    }
  });
}

/***/ }),

/***/ "./resources/admin/ts/workspace/sidebar-active.js":
/*!********************************************************!*\
  !*** ./resources/admin/ts/workspace/sidebar-active.js ***!
  \********************************************************/
/***/ ((module) => {

// Match the same comma-separated, wildcard paths used by PHP routeActive().
function matchesSidebarPath(path, patterns) {
  return patterns.split(',').some(function (pattern) {
    if (!pattern) return false;
    var expression = pattern.replace(/[.+?^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
    return new RegExp('^' + expression + '$', 'u').test(path);
  });
}

function sidebarPath(url) {
  var path = new URL(url, window.location.origin).pathname.replace(/^\/+|\/+$/g, '') || '/';

  try {
    return decodeURIComponent(path);
  } catch (e) {
    return path;
  }
}

function updateSidebarActive(url) {
  var sidebar = document.getElementById('sidebar');
  if (!sidebar || !url) return;
  var currentPath;

  try {
    currentPath = sidebarPath(url);
  } catch (e) {
    return;
  }

  var links = sidebar.querySelectorAll('.side-menu a');
  var dropdowns = sidebar.querySelectorAll('.side-dropdown');
  links.forEach(function (link) {
    var patterns = link.getAttribute('data-active-patterns');
    var linkUrl = link.getAttribute('data-url');
    var active = false;

    if (patterns) {
      active = matchesSidebarPath(currentPath, patterns);
    } else if (linkUrl) {
      try {
        active = sidebarPath(linkUrl) === currentPath;
      } catch (e) {}
    }

    link.dataset.routeActive = String(active);
    link.classList.toggle('active', active);
  }); // Highlight ancestors even when a group's own pattern omits a visible child.

  links.forEach(function (link) {
    if (link.dataset.routeActive !== 'true') return;
    var dropdown = link.closest('.side-dropdown');

    while (dropdown) {
      var parentLink = dropdown.parentElement.querySelector('a:first-child');

      if (parentLink) {
        parentLink.dataset.routeActive = 'true';
        parentLink.classList.add('active');
      }

      dropdown = dropdown.parentElement.closest('.side-dropdown');
    }
  }); // Keep the closed sidebar rail collapsed; reveal the current group when expanded.

  var collapsed = sidebar.classList.contains('hide') && !sidebar.matches(':hover');
  dropdowns.forEach(function (dropdown) {
    var parentLink = dropdown.parentElement.querySelector('a:first-child');
    dropdown.classList.toggle('show', !collapsed && (parentLink === null || parentLink === void 0 ? void 0 : parentLink.dataset.routeActive) === 'true');
  });
}

module.exports = {
  matchesSidebarPath: matchesSidebarPath,
  updateSidebarActive: updateSidebarActive
};

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
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!************************************!*\
  !*** ./resources/admin/ts/body.js ***!
  \************************************/
feather.replace();

__webpack_require__(/*! ./select2-lifecycle */ "./resources/admin/ts/select2-lifecycle.js");

Alpine.start();
window.AlpineStarted = true;

var _require = __webpack_require__(/*! ./workspace/s-events */ "./resources/admin/ts/workspace/s-events.js"),
    initSEvents = _require.initSEvents,
    initSMask = _require.initSMask;

window.initSEvents = initSEvents;
window.initSMask = initSMask;
initSEvents(document);
initSMask(document);
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
}); // content scroll-to-top handler

function checkScrollState() {
  var windowScroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
  var contentBody = document.querySelector("#content .content-body");
  var formAdmin = document.querySelector(".form-admin");
  var bookingUi = document.querySelector(".booking-order-ui");
  var currentScroll = Math.max(windowScroll, contentBody ? contentBody.scrollTop : 0, formAdmin ? formAdmin.scrollTop : 0, bookingUi ? bookingUi.scrollTop : 0);
  var contentHeader = document.querySelector("#content");
  var scrollBtn = document.getElementById("jsScroll");

  if (currentScroll > 60) {
    contentHeader === null || contentHeader === void 0 ? void 0 : contentHeader.classList.add("isScrolled");
    scrollBtn === null || scrollBtn === void 0 ? void 0 : scrollBtn.classList.add("visible");
  } else {
    contentHeader === null || contentHeader === void 0 ? void 0 : contentHeader.classList.remove("isScrolled");
    scrollBtn === null || scrollBtn === void 0 ? void 0 : scrollBtn.classList.remove("visible");
  }
} // Global scroll capture to catch window, .content-body, .form-admin, etc.


window.addEventListener("scroll", checkScrollState, true);
var el = document.getElementById("jsScroll");
el === null || el === void 0 ? void 0 : el.addEventListener("click", function () {
  var _document$querySelect, _document$querySelect2, _document$querySelect3;

  window.scrollTo({
    top: 0,
    behavior: "smooth"
  });
  document.documentElement.scrollTo({
    top: 0,
    behavior: "smooth"
  });
  document.body.scrollTo({
    top: 0,
    behavior: "smooth"
  });
  (_document$querySelect = document.querySelector("#content .content-body")) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.scrollTo({
    top: 0,
    behavior: "smooth"
  });
  (_document$querySelect2 = document.querySelector(".form-admin")) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.scrollTo({
    top: 0,
    behavior: "smooth"
  });
  (_document$querySelect3 = document.querySelector(".booking-order-ui")) === null || _document$querySelect3 === void 0 ? void 0 : _document$querySelect3.scrollTo({
    top: 0,
    behavior: "smooth"
  });
});

__webpack_require__(/*! ./package/sliderBar */ "./resources/admin/ts/package/sliderBar.js");

__webpack_require__(/*! ./package/product-image-bg */ "./resources/admin/ts/package/product-image-bg.js");
})();

/******/ })()
;