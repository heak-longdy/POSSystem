$(document).ready(function () {
    $('select[name="store[province_id]"]').on("change", function () {
        var selectProvince = $(this).val();
        const district = $('select[name="store[district_id]"]');
        const commune = $('select[name="store[commune_id]"]');
        const village = $('select[name="store[village_id]"]');

        if (selectProvince) {
            $.ajax({
                url: "/admin/geo-api/district/" + selectProvince,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    district.empty();
                    district.removeAttr("disabled");
                    commune.attr("disabled", "disabled");
                    commune.empty();
                    village.attr("disabled", "disabled");
                    village.empty();
                    district.append(
                        '<option value="">Please select district</option>'
                    );
                    commune.append(
                        '<option value="">Please select commune</option>'
                    );
                    village.append(
                        '<option value="">Please select village</option>'
                    );
                    $.each(data, function (key, value) {
                        district.append(
                            '<option value="' +
                                value.id +
                                '">' +
                                JSON.parse(value.name).km +
                                " - " +
                                JSON.parse(value.name).en +
                                " - " +
                                JSON.parse(value.name).zh +
                                "</option>"
                        );
                    });
                },
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
        const commune = $('select[name="store[commune_id]"]');
        const village = $('select[name="store[village_id]"]');
        if (selectCommune) {
            $.ajax({
                url: "/admin/geo-api/commune/" + selectCommune,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    commune.empty();
                    commune.removeAttr("disabled");
                    village.attr("disabled", "disabled");
                    village.empty();
                    commune.append(
                        '<option value="">Please select commune</option>'
                    );
                    village.append(
                        '<option value="">Please select village</option>'
                    );
                    $.each(data, function (key, value) {
                        commune.append(
                            '<option value="' +
                                value.id +
                                '">' +
                                JSON.parse(value.name).km +
                                " - " +
                                JSON.parse(value.name).en +
                                " - " +
                                JSON.parse(value.name).zh +
                                "</option>"
                        );
                    });
                },
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
                success: function (data) {
                    $('select[name="store[village_id]"]').empty();
                    $('select[name="store[village_id]"]').removeAttr(
                        "disabled"
                    );
                    $('select[name="store[village_id]"]').append(
                        '<option value="">Please select commune</option>'
                    );
                    $.each(data, function (key, value) {
                        $('select[name="store[village_id]"]').append(
                            '<option value="' +
                                value.id +
                                '">' +
                                JSON.parse(value.name).km +
                                " - " +
                                JSON.parse(value.name).en +
                                " - " +
                                JSON.parse(value.name).zh +
                                "</option>"
                        );
                    });
                },
            });
        } else {
            $('select[name="store[village_id]"]').empty();
            $('select[name="store[village_id]"]').attr("disabled", "disabled");
            $('select[name="store[village_id]"]').append(
                '<option value="">Please select commune</option>'
            );
        }
    });
});
