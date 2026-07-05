const { default: Axios } = require("axios");
(Axios.headers = {
    "Content-Type": "application/x-www-form-urlencoded;charset=utf-8",
    Accept: "application/json",
}),
    (window.Axios = Axios);
window.$selectFile = function (contentType, multiple) {
    return new Promise((resolve) => {
        const input = document.createElement("input");
        input.type = "file";
        input.multiple = multiple;
        input.accept = contentType;
        input.onchange = (_) => {
            const files = Array.from(input.files);
            if (multiple) {
                resolve(files);
            } else {
                resolve(files[0]);
            }
        };

        input.click();
    });
};

window.$onUploadFile = async function (
    url,
    multiple = true,
    acceptType = "image/*",
    callback
) {
    const files = await $selectFile(acceptType, multiple);
    const file = Array.isArray(files) ? files : [files];
    file.map((item, index) => {
        const key = new Date().getTime() + index;
        let cancelToken = Axios.CancelToken;
        let source = cancelToken.source();
        if (file) {
            //loading, data, file, percent, key, error, cancel_source
            callback ? callback(true, false, item, 0, key, false) : false;
        }
        var progressUpload = (percent) => {
            callback ? callback(true, false, item, percent, key) : false;
        };
        let formData = new FormData();
        formData.append("file", item);
        Axios.post(url, formData, {
            headers: {
                "Content-Type":
                    "application/x-www-form-urlencoded;charset=utf-8",
                Accept: "application/json",
            },
            responseType: "json",
            onUploadProgress: function (progressEvent) {
                var percentCompleted = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
                progressUpload(percentCompleted);
            },
            CancelToken: source.token,
        })
            .then((res) => {
                callback ? callback(false, res, item, 100, key) : true;
            })
            .catch((error) => {
                callback ? callback(true, null, item, null, key, error) : true;
            });
    });
};
