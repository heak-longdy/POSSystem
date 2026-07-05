const { default: Axios } = require("axios");

window.$validator = function (form, validator, option) {
    $("body").on("submit", form, function (e) {
        const eThis = $(this);
        const validate = validator;
        const validate_arr = Object.keys(validate);
        if (validate_arr.length > 0) {
            validate_arr.map(async (keys) => {
                const el = eThis.find(`[name='${keys}']`);
                const value = el.val().toString();
                const error = el
                    .parent()
                    .find(`.${option?.messageClass ?? "error"}`);
                let message = "";
                const el_validate = validate[keys];
                const arr = Object.keys(validate[keys]);
                for (var i = 0; i < arr.length; i++) {
                    // Required
                    if (arr[i] == "required") {
                        if (value.length == 0) {
                            message =
                                el.attr("error-message") ??
                                "Please input this field";

                            e.preventDefault();
                            break;
                        }
                    }
                    // Email
                    else if (arr[i] == "email") {
                        let regex = new RegExp(
                            "([a-zA-Z0-9._]{5,30}[@][a-zA-Z]{2,20}[.][a-zA-Z]{2,20})+$",
                            ""
                        );
                        if (!regex.test(value)) {
                            message =
                                el.attr("error-email") ??
                                "Please input a valid email";

                            e.preventDefault();
                            break;
                        }
                    }
                    // Phone
                    else if (arr[i] == "phone") {
                        let regex = new RegExp(
                            "([0]{1}[1-9]{1}[0-9]{7,8})+$",
                            ""
                        );
                        if (!regex.test(value)) {
                            message =
                                el.attr("error-phone") ??
                                "Please input a valid phone";

                            e.preventDefault();
                            break;
                        }
                    }
                    // Image
                    else if (arr[i] == "image") {
                        if (value.length == 0) {
                            message =
                                el.attr("error-message") ??
                                "Please choose a photo";

                            e.preventDefault();
                            break;
                        }
                    }
                    // Match
                    else if (arr[i] == "match") {
                        if (value.length != 0) {
                            if (
                                value !=
                                $(`[name=${validate[keys][arr[i]]}]`).val()
                            ) {
                                message =
                                    el.attr("error-message") ??
                                    "Password your entered not match.";
                                e.preventDefault();
                                break;
                            }
                        }
                    }
                    // minLength
                    else if (arr[i] == "minLength") {
                        if (
                            value.length > 0 &&
                            value.length < el_validate.minLength
                        ) {
                            message =
                                el.attr("error-minLength") ??
                                `Please input at least ${el_validate.minLength} characters`;
                            e.preventDefault();
                            break;
                        }
                    }
                    // maxLength
                    else if (arr[i] == "maxLength") {
                        if (
                            value.length > 0 &&
                            value.length > el_validate.maxLength
                        ) {
                            message =
                                el.attr("error-maxLength") ??
                                `Please input less than ${el_validate.maxLength} characters`;
                            e.preventDefault();
                            break;
                        }
                    }
                    // type
                    else if (arr[i] == "type") {
                        if (el_validate.type == "number" && isNaN(value)) {
                            message =
                                el.attr("error-type") ??
                                "Please input a valid number";
                            e.preventDefault();
                            break;
                        }
                    }
                    // range
                    else if (arr[i] == "range") {
                        const [min, max] = el_validate.range.split("-");
                        if (!isNaN(value) && !isNaN(min) && !isNaN(max)) {
                            if (!min) {
                                message =
                                    el.attr("error-range") ??
                                    `Please input less than ${max}`;
                                e.preventDefault();
                                break;
                            } else if (!max) {
                                message =
                                    el.attr("error-range") ??
                                    `Please input greater than ${min}`;
                                e.preventDefault();
                                break;
                            } else if (
                                Number(value) < min ||
                                Number(value) > max
                            ) {
                                message =
                                    el.attr("error-range") ??
                                    `Please input a number between ${min} and ${max}`;
                                e.preventDefault();
                                break;
                            }
                        }
                    }
                    // Unique
                    else if (arr[i] == "unique") {
                        if (value.length != 0) {
                            if (
                                el.data("old") &&
                                (await el.data("old").toLowerCase()) == value
                            ) {
                                e.preventDefault();
                                break;
                            }
                            await Axios.get(
                                `${validate[keys][arr[i]]["url"]}/${value}`,
                                {
                                    headers: {
                                        "Content-Type":
                                            "application/x-www-form-urlencoded;charset=utf-8",
                                        Accept: "application/json",
                                    },
                                    responseType: "json",
                                }
                            ).then((res) => {
                                let data = Object.keys(res.data);
                                if (data.length > 0) {
                                    message = validate[keys][arr[i]]["message"];
                                }
                            });
                        }
                    }
                }
                el.removeClass(option?.inputClass);
                if (error.length > 0) {
                    option?.inputClass && message.length != 0
                        ? el.addClass(option?.inputClass)
                        : null;
                    error.text(message);
                } else {
                    option?.inputClass && message.length != 0
                        ? el.addClass(option?.inputClass)
                        : null;
                    !option || option?.showMessage
                        ? el.after(
                              `<span class="${
                                  option?.messageClass ?? "error"
                              }">${message}</span>`
                          )
                        : null;
                }
            });
        }
    });
};

window.$validatorOption = function (selector, validator, lang, callback) {
    const eThis = $(selector);
    const validate = validator;
    const validate_arr = Object.keys(validate);
    let result = [false];
    if (validate_arr.length > 0) {
        validate_arr.map(async (keys) => {
            const el = eThis.find(`[name='${keys}']`);
            const value =
                typeof el.val() == "object" ? el.val() : el.val().toString();
            // const el = eThis.find(`[name=${keys}]`);
            // const value = el.val().toString();
            const error = el.parent().find(".error");
            let message = "";
            const arr = Object.keys(validate[keys]);
            for (var i = 0; i < arr.length; i++) {
                // Required
                if (arr[i] == "required") {
                    if (value.length == 0) {
                        if (lang == "km") {
                            message = "សូមបញ្ចូលប្រអប់ខាងលើ";
                        } else {
                            message = "Please input this field";
                        }
                        result.push(true);
                        break;
                    }
                }
                // Checkbox
                if (arr[i] == "checkbox") {
                    if (!el[0].checked) {
                        if (lang == "km") {
                            message = "សូមបញ្ចូលធីកប្រអប់ខាងលើ";
                        } else {
                            message = "Please check the box above";
                        }
                        result.push(true);
                        break;
                    }
                }
                // Email
                else if (arr[i] == "email") {
                    let regex = new RegExp(
                        "([a-zA-Z0-9._]{5,30}[@][a-zA-Z]{2,20}[.][a-zA-Z]{2,20})+$",
                        ""
                    );
                    if (!regex.test(value)) {
                        if (lang == "km") {
                            message = "អ៊ីម៉ែលមិនត្រឹមត្រូវ";
                        } else {
                            message = "Please input a valid email";
                        }
                        result.push(true);
                        break;
                    }
                }
                // Phone
                else if (arr[i] == "phone") {
                    let regex = new RegExp("([0]{1}[1-9]{1}[0-9]{7,8})+$", "");
                    if (!regex.test(value)) {
                        if (lang == "km") {
                            message = "លេខទូរស័ព្ទមិនត្រឹមត្រូវ";
                        } else {
                            message = "Please input a valid phone";
                        }
                        result.push(true);
                        break;
                    }
                }
                // Image
                else if (arr[i] == "image") {
                    if (value.length == 0) {
                        if (lang == "km") {
                            message = "សូមបញ្ចូលរូបភាព";
                        } else {
                            message = "Please choose a photo";
                        }
                        result.push(true);
                        break;
                    }
                }
                // Match
                else if (arr[i] == "match") {
                    if (value.length != 0) {
                        if (
                            value !=
                            $(`[name="${validate[keys][arr[i]]}"]`).val()
                        ) {
                            if (lang == "km") {
                                message = "ពាក្យសម្ងាត់មិនដូចគ្នា";
                            } else {
                                message = "Password your entered not match.";
                            }
                            result.push(true);
                            break;
                        }
                    }
                }
                // Unique
                else if (arr[i] == "unique") {
                    if (value.length != 0) {
                        if (
                            el.data("old") &&
                            (await el.data("old").toLowerCase()) == value
                        ) {
                            result.push(true);
                            break;
                        }
                        await Axios.get(
                            `${validate[keys][arr[i]]["url"]}/${value}`,
                            {
                                headers: {
                                    "Content-Type":
                                        "application/x-www-form-urlencoded;charset=utf-8",
                                    Accept: "application/json",
                                },
                                responseType: "json",
                            }
                        ).then((res) => {
                            let data = Object.keys(res.data);
                            if (data.length > 0) {
                                message = validate[keys][arr[i]]["message"];
                                result.push(true);
                            }
                        });
                    }
                }
            }
            if (error.length > 0) {
                error.text(message);
            } else {
                el.after(`<span class="error">${message}</span>`);
            }
        });
        callback(result);
    }
};
