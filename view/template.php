<?
$arRegions = fiastestwork::getRegions();
?>
<!DOCTYPE html>
    <head>
        <link rel="stylesheet" href="view/css/style.css">
        <script src="view/js/jquery-2.1.1.min.js"></script>
        <script src="view/js/script.js"></script>
    </head>
    <body>
        <div class="container">
            <form class="search-form">
                <fieldset>
                    <legend>Поиск адресов ФИАС</legend>

                    <select  class="search-form-input" id="sregion" name="sregion">
                        <? foreach ($arRegions as $region) {?>
                            <option value="<?=$region["REGION_CODE"]?>"><?=$region["REGION_NAME"]?></option>
                        <? } ?>
                    </select>
                    <input type="text" class="search-form-input" id="scity" name="scity" placeholder="Город">
                    <input type="text" class="search-form-input" id="sstreet" name="sstreet" placeholder="Улица">
                    <input type="text" class="search-form-input" id="shouse" name="shouse" placeholder="Дом">
                    <div class="stbtn" id="search_input">Поиск</div>
                </fieldset>
            </form>
            <div class="result-window"></div>
            <div class="save-result-row">
                <div class="stbtn" id="save_2maria">Сохранить в MariaDB</div>
            </div>
        </div>
    </body>
</html>