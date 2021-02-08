<?php

class fiastestwork
{

    /*
    * общая функция. должна бы по задумке вернуть результат
    */
    public static function getSearchInfo($params)
    {

        if(!$connection = self::getPgConnection()) {
            die("Не установлено соединение с БД");
        }

        if(!empty($params["house"])) {
            //если дом установлен, сначала ищем guid дома. потом используем его в рекурсивном поиске
            $house_guid = self::getHouseGuide($params,$connection);

            if(count($house_guid)) {
                $adress = self::recursiveSearche($house_guid,$params);
                if(!empty($params["street"]) && count($adress)) { //фильтровать строки на совпадение с улицей будем здесь. криво. но в текущей ситуации по другому не сделать.
                    $realAddress = [];
                    foreach ($adress as $addr_string) {
                        $have_street = false;
                        foreach ($addr_string as $realstr) {
                            if(strpos($realstr,$params["street"]) !== false || strpos($params["street"],$realstr) !== false) {
                                $have_street = true;
                            }
                        }
                        if($have_street) {
                            $realAddress[] = $addr_string;
                        }
                    }
                    return $realAddress;
                } elseif(count($adress)) {
                    return $adress;
                }
            }
        } elseif(!empty($params["street"])) {
            //todo
        }

    }

    /*
     *Если у нас задан в поиске дом и регион, ищем рекурсивно все, что подходит
     */
    private static function recursiveSearche($arHouseParams,$params)
    {
        if(!$connection = self::getPgConnection()) {
            die("Не установлено соединение с БД");
        }

        $arAddress = [];

        foreach ($arHouseParams as $house) {
            $arHouse = [];
            $arHouse["HOUSE"] = trim($params["house"]);
            $arHouse[$house["SHORTNAME"]] = $house["OFFNAME"];
            $aolevel = $house["AOLEVEL"];
            $parent_guid = $house["PARENTGUID"];

            //сильно подозреваю, что рекурсию можно и нужно организовать на стороне БД. но тут опять же, со временем обрез совсем
            while ($aolevel > 4) {
                $query = 'SELECT "PARENTGUID","OFFNAME","AOLEVEL","SHORTNAME" FROM "ADDROB01" WHERE "AOGUID" = \''.$parent_guid.'\' AND "ACTSTATUS" = \'1\'';
                $result = pg_query($connection,$query);
                $arResult = [];
                while($dbRes = pg_fetch_object($result)) {
                    $short_name =  mb_convert_encoding($dbRes->SHORTNAME, "utf-8","windows-1251");
                    $arHouse[$short_name] = mb_convert_encoding($dbRes->OFFNAME, "utf-8","windows-1251");
                    $aolevel = $dbRes->AOLEVEL;
                    $parent_guid = $dbRes->PARENTGUID;
                }
            }

            $arAddress[] = $arHouse;
        }

        return $arAddress;

    }

    /*
     * получаем GUID для дома
     */
    private static function getHouseGuide($params,$connection)
    {
        //тут вместо этой порнографии надо бы переделать на параметры. но уже не успеваю никак
        $query = 'SELECT ad."AOGUID",ad."AOLEVEL",ad."OFFNAME",ad."PARENTGUID",ad."SHORTNAME" FROM "ADDROB01" ad
                    LEFT JOIN "HOUSE01" hs ON ad."AOGUID" = hs."AOGUID"
                    WHERE 
                        ad."REGIONCODE" = \''.$params["region"].'\'
                        AND ad."OPERSTATUS" = \'1\'
                        AND hs."HOUSENUM" = \''.trim($params["house"]).'\'
                        AND hs."STARTDATE" IN (SELECT MAX(hh."STARTDATE") FROM "HOUSE01" hh WHERE hh."HOUSENUM" = \''.trim($params["house"]).'\')
                        AND hs."STRSTATUS" = \'1\';';

        $result = pg_query($connection,$query);
        $arResult = [];
        while($dbRes = pg_fetch_object($result)) {
            $arResult[] = [
                "SHORTNAME"     => mb_convert_encoding($dbRes->SHORTNAME, "utf-8","windows-1251"),
                "AOGUID"        => $dbRes->AOGUID,
                "AOLEVEL"       => $dbRes->AOLEVEL,
                "OFFNAME"       => mb_convert_encoding($dbRes->OFFNAME, "utf-8","windows-1251"),
                "PARENTGUID"    => $dbRes->PARENTGUID
            ];
        }

        return $arResult;
    }

    /*
     * получаем список регионов, который вообще есть в БД
     */
    public static function getRegions()
    {
        if(!$connection = self::getPgConnection()) {
            die("Не установлено соединение с БД");
        }

        $query = 'SELECT "REGIONCODE","OFFNAME" FROM "ADDROB01" WHERE "AOLEVEL" = \'1\' AND "OPERSTATUS" = \'1\';';
        $result = pg_query($connection, $query);

        $arResult = [];

        while($dbRes = pg_fetch_object($result)) {
            $arResult[] = [
                "REGION_CODE"   => $dbRes->REGIONCODE,
                "REGION_NAME"   => mb_convert_encoding($dbRes->OFFNAME, "utf-8","windows-1251")
            ];
        }

        return $arResult;
    }

    /*
     * приводим полученный результат к удобоваримому для рендера виду
     */
    public static function normalizeResul($address)
    {
        $arPos = ["г","г.","г.ф.з","г.о.","пос","п","у"];
        $arNormalAdress = [];
        foreach ($address as $address) {
            $arAddr = [];
            $arStreet = [];
            foreach ($address as $key => $e_addr) {
                if($key == "HOUSE") {
                    $arAddr["HOUSE"] = ["NAME" => "дом", "VALUE" => $e_addr];
                } elseif (in_array($key,$arPos)) {
                    $arAddr["CITY"] = ["NAME" => $key, "VALUE" => $e_addr];
                } else {
                    $arStreet[] = $key.": ".$e_addr;
                }
            }
            $arAddr["STREET"] = ["NAME" => "улица", "VALUE" => implode("; ",$arStreet)];
            $arNormalAdress[] = $arAddr;
        }

        return $arNormalAdress;
    }


    /*
     *
     */
    private static function getPgConnection()
    {
        $connection = pg_connect ("host=localhost dbname=fias_test user=test_user password=qwerty");
        if (!$connection)
        {
            return false;
        }
        return $connection;
    }

    /*
     * Записываем в MariaDB
     */
    public static function insert2MariaDb($params)
    {
        if(!$connection = self::getMconnection()) {
            die("Не установлено соединение с БД");
        }

        foreach ($params["arelement"] as $element) {
            $ins_element =  $element["ccity"].": ".$element["city"]."; ".$element["cstreet"].": ".$element["street"]."; ".$element["chouse"].": ".$element["house"];
            $query = "INSERT INTO fias_address (adress) VALUES ('{$ins_element}')";
            if(!mysqli_query($connection, $query)) {
                $err = mysqli_error($connection);
                return false;
            }
        }
        return true;
    }

    /*
     *
     */
    private static function getMconnection()
    {
        $connection = mysqli_connect("localhost", "root", "", "test_fias");
        if (!$connection)
        {
            return false;
        }
        return $connection;
    }
}