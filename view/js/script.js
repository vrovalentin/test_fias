
$(document).ready(function () {

    $('#search_input').click(function () {

        var $region = $('#sregion').val();
        var $city = $('#scity').val();
        var $street = $('#sstreet').val();
        var $shouse = $('#shouse').val();
        $('.result-window').empty();
        var $url = 'controller/controller.php';
        $.ajax({
            method: "POST",
            url: $url,
            data: {type:"SEARCH",region: $region,city: $city,street: $street,house: $shouse},
            success: function (data) {
                if (data) {
                    let address = JSON.parse(data);
                    for (let value of Object.values(address)) {
                        var house_block = '<div class="result-window-caption">' + value["HOUSE"]["NAME"] +
                            ': </div><div class="result-window-block" id="res_house">' + value["HOUSE"]["VALUE"] + '</div>';
                        var city_block = '<div class="result-window-caption">' + value["CITY"]["NAME"] +
                            ': </div><div class="result-window-block" id="res_city">' + value["CITY"]["VALUE"] + '</div>';
                        var street_block = '<div class="result-window-caption">' + value["STREET"]["NAME"] +
                            ': </div><div class="result-window-block" id="street_city">' + value["STREET"]["VALUE"] + '</div>';
                        var result_row = '<div class="result-row">' + city_block + street_block + house_block + '</div>';
                        $('.result-window').append(result_row);
                    }
                } else {
                    $('.result-window').append('<div class="result-window-err">К сожалению, ничего не найдено</div>');
                }
            }
        });

    });
});