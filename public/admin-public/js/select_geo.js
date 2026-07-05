/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/admin/ts/select_geo.js":
/*!******************************************!*\
  !*** ./resources/admin/ts/select_geo.js ***!
  \******************************************/
/***/ (() => {

$(document).ready(function () {
  $('select[name="store[province_id]"]').on("change", function () {
    var selectProvince = $(this).val();
    var district = $('select[name="store[district_id]"]');
    var commune = $('select[name="store[commune_id]"]');
    var village = $('select[name="store[village_id]"]');

    if (selectProvince) {
      $.ajax({
        url: "/admin/geo-api/district/" + selectProvince,
        type: "GET",
        dataType: "json",
        success: function success(data) {
          district.empty();
          district.removeAttr("disabled");
          commune.attr("disabled", "disabled");
          commune.empty();
          village.attr("disabled", "disabled");
          village.empty();
          district.append('<option value="">Please select district</option>');
          commune.append('<option value="">Please select commune</option>');
          village.append('<option value="">Please select village</option>');
          $.each(data, function (key, value) {
            district.append('<option value="' + value.id + '">' + JSON.parse(value.name).km + " - " + JSON.parse(value.name).en + " - " + JSON.parse(value.name).zh + "</option>");
          });
        }
      });
    } else {
      district.empty();
      commune.empty();
      commune.attr("disabled", "disabled");
      village.empty();
      village.attr("disabled", "disabled");
      district.attr("disabled", "disabled");
      district.append('<option value="">Please select district</option>');
      commune.append('<option value="">Please select commune</option>');
      village.append('<option value="">Please select village</option>');
    }
  });
  $('select[name="store[district_id]"]').on("change", function () {
    var selectCommune = $(this).val();
    var commune = $('select[name="store[commune_id]"]');
    var village = $('select[name="store[village_id]"]');

    if (selectCommune) {
      $.ajax({
        url: "/admin/geo-api/commune/" + selectCommune,
        type: "GET",
        dataType: "json",
        success: function success(data) {
          commune.empty();
          commune.removeAttr("disabled");
          village.attr("disabled", "disabled");
          village.empty();
          commune.append('<option value="">Please select commune</option>');
          village.append('<option value="">Please select village</option>');
          $.each(data, function (key, value) {
            commune.append('<option value="' + value.id + '">' + JSON.parse(value.name).km + " - " + JSON.parse(value.name).en + " - " + JSON.parse(value.name).zh + "</option>");
          });
        }
      });
    } else {
      commune.empty();
      village.empty();
      village.attr("disabled", "disabled");
      commune.attr("disabled", "disabled");
      commune.append('<option value="">Please select commune</option>');
      village.append('<option value="">Please select village</option>');
    }
  });
  $('select[name="store[commune_id]"]').on("change", function () {
    var selectVillage = $(this).val();

    if (selectVillage) {
      $.ajax({
        url: "/admin/geo-api/village/" + selectVillage,
        type: "GET",
        dataType: "json",
        success: function success(data) {
          $('select[name="store[village_id]"]').empty();
          $('select[name="store[village_id]"]').removeAttr("disabled");
          $('select[name="store[village_id]"]').append('<option value="">Please select commune</option>');
          $.each(data, function (key, value) {
            $('select[name="store[village_id]"]').append('<option value="' + value.id + '">' + JSON.parse(value.name).km + " - " + JSON.parse(value.name).en + " - " + JSON.parse(value.name).zh + "</option>");
          });
        }
      });
    } else {
      $('select[name="store[village_id]"]').empty();
      $('select[name="store[village_id]"]').attr("disabled", "disabled");
      $('select[name="store[village_id]"]').append('<option value="">Please select commune</option>');
    }
  });
});

/***/ }),

/***/ "./resources/admin/sass/app.scss":
/*!***************************************!*\
  !*** ./resources/admin/sass/app.scss ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/admin/sass/carHistory/invoice.scss":
/*!******************************************************!*\
  !*** ./resources/admin/sass/carHistory/invoice.scss ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


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
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/admin-public/js/select_geo": 0,
/******/ 			"admin-public/css/app": 0,
/******/ 			"admin-public/css/history/invoice": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["admin-public/css/app","admin-public/css/history/invoice"], () => (__webpack_require__("./resources/admin/ts/select_geo.js")))
/******/ 	__webpack_require__.O(undefined, ["admin-public/css/app","admin-public/css/history/invoice"], () => (__webpack_require__("./resources/admin/sass/app.scss")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["admin-public/css/app","admin-public/css/history/invoice"], () => (__webpack_require__("./resources/admin/sass/carHistory/invoice.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;