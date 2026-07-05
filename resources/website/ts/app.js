window.$ = window.jQuery = window.jquery = require("./package/jquery");
window.Swal = require("./package/sweetalert");
require("../package/mdbootstrap/js/mdb");
// require("./package/validator");
// require("./package/laja");
require("./package/upload-file");

window.AOS = require("./package/aos");
import { Fancybox } from "@fancyapps/ui";
window.Fancybox = Fancybox;
window.moment = require("moment");
import Alpine from "alpinejs";
const { default: Axios } = require("axios");
window.Alpine = Alpine;
window.Axios = Axios;
const { default: anime } = require("animejs");
window.anime = anime;

Alpine.store("animate", {
  enter: (target, fn) => {
    if (!target) return;
    anime({
      targets: target, //this.$root.children[0]
      scale: [0.9, 1],
      opacity: [0, 1],
      direction: "forwards",
      easing: "easeInSine",
      duration: 200,
      complete: (res) => {
        fn ? fn(res) : null;
      },
    });
  },
  leave: (target, fn) => {
    if (!target) return;
    anime({
      targets: target,
      scale: [1, 0.9],
      opacity: [1, 0],
      direction: "forwards",
      easing: "easeOutSine",
      duration: 200,
      complete: (res) => {
        fn ? fn(res) : null;
      },
    });
  },
});
window.$select2Data = function (
  attributeID = "",
  position_id = "",
  title = ""
) {
  var option = "<option selected></option>";
  var selectOptionHTML = $(option).val(position_id).text(title);
  $(attributeID).append(selectOptionHTML).trigger("change");
};

window.$select2FocusInputSearch = function () {
  var inputSearch = document.querySelectorAll(".select2-search__field");
  inputSearch.forEach((val) => {
    val.focus();
    val.setAttribute("placeholder", "Search ...");
  });
};

window.reloadData = function (url) {
  window.location.href = url;
};

window.formatFileSize = function (sizeInBytes) {
  const units = ["B", "KB", "MB", "GB", "TB"];
  let index = 0;

  while (sizeInBytes >= 1024 && index < units.length - 1) {
    sizeInBytes /= 1024;
    index++;
  }
  return `${sizeInBytes.toFixed(2)} ${units[index]}`;
};
