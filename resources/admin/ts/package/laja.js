"use strict";

function DevMode(mode) {
    if (mode === true) {
        console.log("This Framework under Development.");
        console.log("Server is run on http://localhost:8000");
    }
}

DevMode(false);

$(document).ready(function() {
    const base_url = window.location.origin;
    let body = $("body");
    let route = $("[_route]");
    let htmlContent = $("[_htmlContent]");
    let allPath = {};

    function _route(slt) {
        body.on("click", slt, function() {
            const eThis = $(this);
            let url = $(this).attr("_route");
            let title = $(this).attr("_title");
            let path = url.replace(base_url, "");
            if (path == "") {
                path = "/";
            }
            if (removeOcc(url) != removeOcc(window.location.href)) {
                pageChange(path, title, url).then(() => {
                    const active = eThis
                        .parent()
                        .parent()
                        .find(".active");
                    active.each(function(index) {
                        this.classList.remove("active");
                    });
                    eThis.addClass("active");
                });
            }
        });
    }

    function pathUrl(state, title, url) {
        // history.replaceState(state, title, url);
        if (title != "" && typeof title !== typeof undefined) {
            document.title = title;
        }
        history.pushState(state, title, url);
    }

    function reloadPage() {
        let fullUrl = window.location.href;
        let title = $("[_route='" + fullUrl + "']").attr("_title");
        if (title != "" && typeof title !== typeof undefined) {
            document.title = title;
        }
    }

    function handlePopState(event) {
        if (event.state.page) {
            changePage(event.state.page, event.state.title, false);
        }
    }

    async function pageChange(path, title, url) {
        $.ajax({
            url: path,
            cache: true,
            async: true,
            dataType: "text",
            success: function(data) {
                let virtualDocument = document.implementation.createHTMLDocument(
                    "Virtual Document"
                );
                virtualDocument.documentElement.innerHTML = data;

                let html = virtualDocument.querySelector("[_htmlContent]")
                    .innerHTML;
                htmlContent.html(html);
                pathUrl(null, title, path);
            },
            error: function(err) {
                console.log(err);
            }
        });
        let fullUrl = window.location.href;
        pathUrl(null, title, path);
        htmlContent.html(allPath[removeOcc(url)]);
    }

    function getAllPath() {
        route.each(function() {
            let path = $(this).attr("_route");
            $.ajax({
                url: path,
                cache: false,
                async: true,
                dataType: "text",
                success: function(data) {
                    let virtualDocument = document.implementation.createHTMLDocument(
                        "Virtual Document"
                    );
                    virtualDocument.documentElement.innerHTML = data;
                    let html = virtualDocument.querySelectorAll(
                        "[_htmlContent]"
                    )[0];
                    if (html != undefined) {
                        html = html.innerHTML;
                    } else {
                        html = virtualDocument.all[0].outerHTML;
                    }
                    allPath[removeOcc(path)] = html;
                },
                error: function(err) {
                    throw err;
                }
            });
        });
    }

    function removeOcc(str) {
        return str.replace(/[-\/\\^$*+?.()|[\]{}\:]/g, "");
    }

    getAllPath();

    // Backward & forward
    $(window).on("popstate", function() {
        let fullUrl = window.location.href;
        htmlContent.html(allPath[removeOcc(fullUrl)]);
    });

    // Get data
    function getData() {
        let foreach = $("[_foreach]");
        foreach.each(function(key) {
            let foreachPath = $(this).attr("_foreach");
            let eThis = $(this);
            $.ajax({
                url: foreachPath,
                cache: false,
                async: true,
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    let fc = foreach[key].outerHTML;
                    foreach.each(function() {
                        // $("[_foreach]").html("okk")
                    });
                    // $.each(response.user, function(key) {
                    //     let data = this;
                    //     // eThis.find("[_item]").each(function(key) {
                    //     //     let field = $(this).attr("_item");
                    //     //     // $(this).text(response.user.id);
                    //     //     console.log(data)
                    //     //     $(this).text(data[key])

                    //     // })
                    // });
                },
                error: function(err) {
                    throw err;
                }
            });
        });
    }

    $("body").on("DOMSubtreeModified", "[_htmlContent]", function() {
        getData();
    });

    _route("[_route]");
    reloadPage();
    getData();
});
