window.$readURL = function (input, callback) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            callback(e.target.result, input.files[0]);
        };
        reader.readAsDataURL(input.files[0]); // convert to base64 string
    }
};

window.$onConfirmMessage = function (url, msg, btn, input, fn) {
    let option = {
        customClass: "confirm-message",
        icon: "warning",
        html: msg,
        showCloseButton: false,
        showCancelButton: true,
        focusConfirm: false,
        focusCancel: true,
        confirmButtonText: btn.confirm,
        cancelButtonText: btn.cancel,
    };
    if (input) {
        option = Object.assign({}, option, {
            input: input.type,
            inputValue: input.value,
            inputPlaceholder: input.text,
        });
    }
    Swal.fire(option).then((result) => {
        if (result.isConfirmed) {
            if (url) {
                if (Array.isArray(url)) {
                    if (result.value == 1) {
                        location.href = url[0];
                    } else {
                        location.href = url[1];
                    }
                } else {
                    location.href = url;
                }
            } else {
                fn();
            }
        }
    });
};

window.$quillPatchValue = function (content, input) {
    $(input).val(content.root.innerHTML);
    content.on("text-change", function (delta, oldDelta, source) {
        $(input).val(content.root.innerHTML);
    });
};

window.$quillSetContentHtml = function (arr) {
    arr.map(function (item) {
        const delta = item.content.clipboard.convert(item.html);
        item.content.setContents(delta, "user");
    });
};
