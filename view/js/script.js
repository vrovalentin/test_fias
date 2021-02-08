
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
                        var house_block = '<div class="result-window-caption" id="c_house" data-cvalue="' + value["HOUSE"]["NAME"] + '">'
                            + value["HOUSE"]["NAME"] + ': </div><div class="result-window-block" id="res_house">' + value["HOUSE"]["VALUE"] + '</div>';
                        var city_block = '<div class="result-window-caption" id="c_city" data-cvalue="' + value["CITY"]["NAME"] + '">'
                            + value["CITY"]["NAME"] + ': </div><div class="result-window-block" id="res_city">' + value["CITY"]["VALUE"] + '</div>';
                        var street_block = '<div class="result-window-caption" id="c_street" data-cvalue="' + value["STREET"]["NAME"] + '">'
                            + value["STREET"]["NAME"] + ': </div><div class="result-window-block" id="street_city">' + value["STREET"]["VALUE"] + '</div>';
                        var result_row = '<div class="result-row">' + city_block + street_block + house_block + '</div>';
                        $('.result-window').append(result_row);
                    }
                    $('.save-result-row').show();
                } else {
                    $('.result-window').append('<div class="result-window-err">К сожалению, ничего не найдено</div>');
                }
            }
        });

    });
    
    $('#save_2maria').click(function () {
        var arObjects = [];
        $('.result-row').each(function () {
            var strObject = new Object();

            strObject.ccity = $(this).find($('#c_city')).data('cvalue');
            strObject.city = $(this).find($('#res_city')).text();

            strObject.cstreet = $(this).find($('#c_street')).data('cvalue');
            strObject.street = $(this).find($('#street_city')).text();

            strObject.chouse = $(this).find($('#c_house')).data('cvalue');
            strObject.house = $(this).find($('#res_house')).text();

            arObjects.push(strObject);
        });

        var $url = 'controller/controller.php';
        $.ajax({
            method: "POST",
            url: $url,
            data: {type: "INSERT_2MARIA", arelement: arObjects},
            success: function (data) {
                if(data) {
                    $('.result-window').empty();
                    $('.result-window').append('<div class="result-row">Успешно записано в MariaDB</div>');
                }
            }
        });
    });
});