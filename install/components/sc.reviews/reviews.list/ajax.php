<?php
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use SouthCoast\Reviews\Internals\ReviewsTable;
use SouthCoast\Reviews\Internals\ReviewsBansTable;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Loader;
use \Bitrix\Main\Type\DateTime;

global $APPLICATION, $USER;

$APPLICATION->RestartBuffer();

if(array_key_exists("PARAMS", $_REQUEST) && strlen($_REQUEST["PARAMS"]) > 0)
{
    $objAjaxParams = json_decode($_REQUEST["PARAMS"]);
    $arAjaxParams = (array) $objAjaxParams;

    if(array_key_exists("RATING", $_REQUEST) && intval($_REQUEST["RATING"]) > 0)
        $GLOBALS[$arAjaxParams["FILTER"]]["RATING"] = $_REQUEST["RATING"];
    else
        unset($GLOBALS[$arAjaxParams["FILTER"]]["RATING"]);

    if(array_key_exists("RECOMMENDATED", $_REQUEST) && $_REQUEST["RECOMMENDATED"] == 'true')
        $GLOBALS[$arAjaxParams["FILTER"]]["RECOMMENDATED"] = 'Y';
    else
        unset($GLOBALS[$arAjaxParams["FILTER"]]["RECOMMENDATED"]);

    if(array_key_exists("PLUS", $_REQUEST) && $_REQUEST["PLUS"] == 'true')
        $GLOBALS[$arAjaxParams["FILTER"]]["!PLUS"] = false;
    else
        unset($GLOBALS[$arAjaxParams["FILTER"]]["PLUS"]);

    if(array_key_exists("MINUS", $_REQUEST) && $_REQUEST["MINUS"] == 'true')
        $GLOBALS[$arAjaxParams["FILTER"]]["!MINUS"] = false;
    else
        unset($GLOBALS[$arAjaxParams["FILTER"]]["MINUS"]);

    $APPLICATION->IncludeComponent(
        "sc.reviews:reviews.list",
        "",
        Array(
            "MAX_RATING" => $arAjaxParams["MAX_RATING"],
            "PRIMARY_COLOR" => $arAjaxParams["PRIMARY_COLOR"],
            "BUTTON_BACKGROUND" => $arAjaxParams["BUTTON_BACKGROUND"],
            "AJAX" => $arAjaxParams["AJAX"],
            "DATE_FORMAT" => $arAjaxParams["DATE_FORMAT"],
            "ID_ELEMENT" => $arAjaxParams["ID_ELEMENT"],
            "AUTHORIZED_USER" => $arAjaxParams["AUTHORIZED_USER"],
            "COUNT_REVIEWS" => $arAjaxParams["COUNT_REVIEWS"],
            "CACHE_TYPE" => $arAjaxParams["CACHE_TYPE"],
            "CACHE_TIME" => $arAjaxParams["CACHE_TIME"],
            "UPLOAD_IMAGE" => $arAjaxParams["UPLOAD_IMAGE"],
            "MAX_IMAGE_SIZE" => $arAjaxParams["MAX_IMAGE_SIZE"],
            "THUMB_WIDTH" => $arAjaxParams["THUMB_WIDTH"],
            "THUMB_HEIGHT" => $arAjaxParams["THUMB_HEIGHT"],
            "MAX_COUNT_IMAGES" => $arAjaxParams["MAX_COUNT_IMAGES"],
            "MULTIMEDIA_VIDEO_ALLOW" => $arAjaxParams["MULTIMEDIA_VIDEO_ALLOW"],
            "ORDER" => $arAjaxParams["ORDER"],
            "ORDER_BY" => $arAjaxParams["ORDER_BY"],
            "FILTER" => $arAjaxParams["FILTER"],
            "NPAGES_SHOW" => $arAjaxParams["NPAGES_SHOW"]
        ),
        $component 
    );
}
elseif(array_key_exists("ACTION", $_REQUEST) && strlen($_REQUEST["ACTION"]) > 0 &&
    array_key_exists("ID", $_REQUEST) && intval($_REQUEST["ID"]) > 0)
{
    if(!Loader::includeModule( 'sc.reviews' ))
    {
        echo "Модуль отзывов не установлен!";
        die();
    }

    if($_REQUEST["ACTION"] == 'ban')
    {
        $arUserReview = ReviewsTable::getList([
            'select' => ['ID_USER', 'BX_USER_ID', 'IP_USER'],
            'filter' => ['ID' => $_REQUEST["ID"]]
        ])->fetch();

        $banDaysOption = \Bitrix\Main\Config\Option::getRealValue("sc.reviews", "REVIEWS_BAN_DAYS", SITE_ID);
        if(empty($banDaysOption) || intval($banDaysOption) <= 0)
            $banDaysOption = 12;

        $arFields = Array(
            "DATE_CREATION" => new DateTime(),
            "DATE_CHANGE" => new DateTime(),
            "DATE_TO" => new DateTime( date('d.m.Y H:i:s',strtotime('+' . $banDaysOption . ' day')) ),
            "ACTIVE" => 'Y',
            "ID_MODERATOR" => $USER->GetID(),
            "ID_USER" => $arUserReview['ID_USER'],
            "IP" => $arUserReview['IP_USER'],
            "BX_USER_ID" => $arUserReview['BX_USER_ID']
        );

        $resAction = ReviewsBansTable::Add($arFields);
    }

    elseif($_REQUEST["ACTION"] == 'moderate')
    {
        $arFields = [
            'MODERATED' => 'Y',
            'MODERATED_BY' => $USER->GetID()
        ];
        $resAction = ReviewsTable::Update($_REQUEST["ID"], $arFields);
    }

    elseif($_REQUEST["ACTION"] == 'cancel_moderate')
    {
        $arFields = [
            'MODERATED' => 'N',
            'MODERATED_BY' => 0
        ];
        $resAction = ReviewsTable::Update($_REQUEST["ID"], $arFields);
    }

    elseif($_REQUEST["ACTION"] == 'likes')
    {
        $cntLikes = ReviewsTable::getList([
            'select' => ['LIKES'],
            'filter' => ['ID' => $_REQUEST['ID']]
        ])->fetch()["LIKES"];

        $arFields = ['LIKES' => intval($cntLikes) + 1];
        $resAction = ReviewsTable::Update($_REQUEST["ID"], $arFields);
        SetCookie('LIKE["' . $_REQUEST["ID"] . '"]', $_REQUEST["ID"], time () + 3600 * 24 * 365, '/' );
    }

    elseif($_REQUEST["ACTION"] == 'dislikes')
    {
        $cntLikes = ReviewsTable::getList([
            'select' => ['DISLIKES'],
            'filter' => ['ID' => $_REQUEST['ID']]
        ])->fetch()["DISLIKES"];

        $arFields = ['DISLIKES' => intval($cntLikes) + 1];
        $resAction = ReviewsTable::Update($_REQUEST["ID"], $arFields);
        SetCookie('LIKE["' . $_REQUEST["ID"] . '"]', $_REQUEST["ID"], time () + 3600 * 24 * 365, '/' );
    }

    if (!$resAction->isSuccess())
    {
        $strError = '';
        $errors = $resAction->getErrorMessages();
        foreach($errors as $error)
            $strError = $strError . $error . PHP_EOL;

        echo $strError;
    }
    else
    {
        if($_REQUEST["ACTION"] == 'likes' || $_REQUEST["ACTION"] == 'dislikes')
            echo intval($cntLikes) + 1;
        else
            echo "SUCCESS";
    }
}
die();