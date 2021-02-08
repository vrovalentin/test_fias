<?php

    $arFIlter = ["SEARCH","INSERT_2MARIA"];

    if(in_array($_POST["type"],$arFIlter)) {

        require_once $_SERVER["DOCUMENT_ROOT"]."/model/fiastestwork.php";

        if($_POST["type"] == "SEARCH") {

            $arSearchResult= fiastestwork::getSearchInfo($_POST);

            if(count($arSearchResult)) {
                echo json_encode(fiastestwork::normalizeResul($arSearchResult));
            } else {
                echo false;
            }
            die();
        } elseif ($_POST["type"] == "INSERT_2MARIA") {
            if(fiastestwork::insert2MariaDb($_POST)) {
                echo true;
            } else {
                echo false;
            }
            die();
        }
    }
