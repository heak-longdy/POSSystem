var menuShowHide = localStorage.getItem("menu");

// SIDEBAR DROPDOWN
const allDropdown = document.querySelectorAll("#sidebar .side-dropdown");
const sidebar = document.getElementById("sidebar");

allDropdown.forEach((item) => {
    const a = item.parentElement.querySelector("a:first-child");
    a.addEventListener("click", function (e) {
        e.preventDefault();

        if (!this.classList.contains("active")) {
            allDropdown.forEach((i) => {
                const aLink = i.parentElement.querySelector("a:first-child");

                aLink.classList.remove("active");
                i.classList.remove("show");
            });
        }

        this.classList.toggle("active");
        item.classList.toggle("show");
    });
});

// var switchDark = localStorage.getItem("switchDarkMode");

// var switchMode = document.getElementById("switch-mode");
// if (switchDark == 1) {
//     document.body.classList.add("dark");
//     switchMode.checked = true;
// }
// switchMode.addEventListener("change", function () {
//     console.log(switchMode, "switchMode");
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
const toggleSidebar = document.querySelector("button.toggle-sidebar");
const allSideDivider = document.querySelectorAll("#sidebar .divider");
menuShowHide == 0 ? sidebar.classList.toggle("hide") : "";

if (sidebar.classList.contains("hide")) {
    allSideDivider.forEach((item) => {
        item.textContent = "-";
    });
    allDropdown.forEach((item) => {
        const a = item.parentElement.querySelector("a:first-child");
        a.classList.remove("active");
        item.classList.remove("show");
    });
} else {
    allSideDivider.forEach((item) => {
        item.textContent = item.dataset.text;
    });
}

toggleSidebar.addEventListener("click", function () {
    sidebar.classList.toggle("hide");
    console.log(sidebar.classList, "sidebar.classList");
    sidebar.classList[1]
        ? localStorage.setItem("menu", 0)
        : localStorage.setItem("menu", 1);
    if (sidebar.classList.contains("hide")) {
        allSideDivider.forEach((item) => {
            item.textContent = "-";
        });

        allDropdown.forEach((item) => {
            const a = item.parentElement.querySelector("a:first-child");
            a.classList.remove("active");
            item.classList.remove("show");
        });
    } else {
        allSideDivider.forEach((item) => {
            item.textContent = item.dataset.text;
        });
    }
});

sidebar.addEventListener("mouseleave", function () {
    if (this.classList.contains("hide")) {
        allDropdown.forEach((item) => {
            const a = item.parentElement.querySelector("a:first-child");
            a.classList.remove("active");
            item.classList.remove("show");
        });
        allSideDivider.forEach((item) => {
            item.textContent = "-";
        });
    }
});

sidebar.addEventListener("mouseenter", function () {
    if (this.classList.contains("hide")) {
        allDropdown.forEach((item) => {
            const a = item.parentElement.querySelector("a:first-child");
            a.classList.remove("active");
            item.classList.remove("show");
        });
        allSideDivider.forEach((item) => {
            item.textContent = item.dataset.text;
        });
    }
});

// PROFILE DROPDOWN
const profile = document.querySelector("nav .profile");
const imgProfile = profile.querySelector("img");
const dropdownProfile = profile.querySelector(".profile-link");

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
    }

    allMenu.forEach((item) => {
        const icon = item.querySelector(".icon");
        const menuLink = item.querySelector(".menu-link");

        if (e.target !== icon) {
            if (e.target !== menuLink) {
                if (menuLink.classList.contains("show")) {
                    menuLink.classList.remove("show");
                }
            }
        }
    });
});

// MENU
const allMenu = document.querySelectorAll("main .content-data .head .menu");

allMenu.forEach((item) => {
    const icon = item.querySelector(".icon");
    const menuLink = item.querySelector(".menu-link");

    icon.addEventListener("click", function () {
        menuLink.classList.toggle("show");
    });
});

// MENU
var MenuDropDown = document.querySelectorAll(".menu");
MenuDropDown.forEach(function (item) {
    var icon = item.querySelector(".icon");
    var menuLink = item.querySelector(".menu-link");
    icon.addEventListener("click", function () {
        menuLink.classList.toggle("show");
    });
});

//slidBarScrollActive
const menu_activeDom = document.querySelector("#sidebar .side-menu .li .active");
// const menu_activeDom = document.querySelector("#sidebar .side-menu .navItemSiderbarGroup .navSidber li.active")
console.log(menu_activeDom,'navSidber');
const menu_listDom = document.getElementsByClassName("side-menu")[0];
menu_listDom?.scrollTo({
    top: menu_activeDom?.offsetTop - menu_listDom?.clientHeight / 2,
    left: 0,
    behavior: "smooth",
});

// content
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
