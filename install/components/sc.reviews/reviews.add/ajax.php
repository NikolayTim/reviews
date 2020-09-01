<?php
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use SouthCoast\Reviews\Internals\ReviewsTable;
use SouthCoast\Reviews\Internals\ReviewsBansTable;
use SouthCoast\Reviews\Internals\ReviewsFieldsTable;
use SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Loader;
use \Bitrix\Main\Type\DateTime;

define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/111.txt");

global $APPLICATION, $USER;
if(!is_object($USER))
    $USER=new CUser;

if(!CModule::IncludeModule('sc.reviews'))
{
    echo "Модуль отзывов не установлен!";
    return;
}

function printErrors($arErrors)
{
    $strError = '';
    foreach($arErrors as $error)
        $strError = $strError . $error . "<br>";

    echo $strError;
}

$APPLICATION->RestartBuffer();

if(array_key_exists("ACTION", $_REQUEST) && strlen($_REQUEST["ACTION"]) > 0 && $_REQUEST["ACTION"] == 'SAVE_REVIEW')
{
        $valModeration = \Bitrix\Main\Config\Option::getRealValue('sc.reviews', 'REVIEWS_MODERATION', SITE_ID);
        $arGroupUserWithoutModeration = \Bitrix\Main\Config\Option::getRealValue('sc.reviews', 'REVIEWS_USER_GROUPS_WITHOUT_MODERATION', SITE_ID);
        $arGroupUserWithoutModeration = unserialize($arGroupUserWithoutModeration);

        $userID = $_REQUEST["ID_USER"];
        $moderated = 'N';
        $moderated_by = 0;
        $arGroupCurrentUser = [];

        if($USER->IsAuthorized())
        {
            $arGroupCurrentUser = $USER->GetUserGroupArray();
            $arIntersect = array_intersect($arGroupUserWithoutModeration, $arGroupCurrentUser);
            if($arIntersect)
            {
                $moderated = 'Y';
                $moderated_by = $userID;
            }
        }

        if($valModeration == 'N')
        {
            $moderated = 'Y';
            $moderated_by = $userID;
        }

        $arFilesID = [];
        foreach($_REQUEST["photos"] as $keyFile => $base64File)
        {
            if(intval($base64File) > 0)
                $arFilesID[] = $base64File;
            else
            {
                $fileName = ($keyFile + 1) . md5( time() );
                preg_match( '#data:image\/(png|jpg|jpeg);#', $base64File, $fileTypeMatch );
                $fileType = $fileTypeMatch[1];
                $fileBody = base64_decode(preg_replace( '#^data.*?base64,#', '', $base64File ));
                if(file_put_contents( $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . $fileName . '.' . $fileType, $fileBody ))
                {
                    $arFile = CFile::MakeFileArray( '/upload/tmp/' . $fileName . '.' . $fileType );

                    $arFile["del"] = "Y";
                    $arFile["MODULE_ID"] = 'iblock';
                    $fileID = CFile::SaveFile($arFile, "iblock");

                    if($fileID > 0)
                        $arFilesID[] = $fileID;
                    else
                        $errors[] = "Ошибка сохранения файла: " . '/upload/tmp/' . $fileName . '.' . $fileType;
                }
            }
        }

        if(!isset($_REQUEST["BX_USER_ID"]) || strlen($_REQUEST["BX_USER_ID"]) <= 0)
            $_REQUEST["BX_USER_ID"] = $_COOKIE["BX_USER_ID"];

        $arFieldsReview = [
            "ACTIVE" => "Y",
            "RATING" => $_REQUEST["RATING"],
            "DATE_CHANGE" => new DateTime(),
            "TITLE" => $_REQUEST["TITLE"],
            "PLUS" => $_REQUEST["PLUS"],
            "MINUS" => $_REQUEST["MINUS"],
            "ID_ELEMENT" => $_REQUEST["ID_ELEMENT"],
            "ID_USER" => $userID,
            "BX_USER_ID" => $_REQUEST["BX_USER_ID"],
            "RECOMMENDATED" => $_REQUEST["RECOMMENDATED"],
            "FILES" => serialize($arFilesID),
            "IP_USER" => $_REQUEST["IP_USER"],
            "MODERATED" => $moderated,
            "MODERATED_BY" => $moderated_by
        ];

        if(array_key_exists("DATE_CREATION", $_REQUEST) && strlen($_REQUEST["DATE_CREATION"]) > 0)
            $arFieldsReview["DATE_CREATION"] = new DateTime($_REQUEST["DATE_CREATION"]);

        $strTitle = '';
        if(array_key_exists("TITLE", $_REQUEST) && strlen($_REQUEST["TITLE"]) > 0)
            $strTitle = $_REQUEST["TITLE"];

        $arFieldsReview["TITLE"] = $strTitle;

        $strResult = '';
        if(array_key_exists("ID", $_REQUEST) && intval($_REQUEST["ID"]) > 0)
        {
            $result = ReviewsTable::update($_REQUEST["ID"], $arFieldsReview);
            $idReview = $_REQUEST["ID"];
            if(!$result->isSuccess())
                $errors = $result->getErrorMessages();
            else
                $strResult = 'Обновлен отзыв с ID:' . $idReview;
        }
        else
        {
            $arFieldsReview["ID_USER"] = $userID;
            $arFieldsReview["LIKES"] = 0;
            $arFieldsReview["DISLIKES"] = 0;

            $result = ReviewsTable::add($arFieldsReview);
            $idReview = $result->getId();
            if(!$result->isSuccess())
                $errors = $result->getErrorMessages();
            else
                $strResult = 'Добавлен отзыв с ID:' . $idReview;
        }

        if($errors)
            printErrors($errors);
        else 
        {
            $arUserFieldsID = [];
            $rsUserFields = ReviewsFieldsTable::getList([
                'select' => ['ID', 'NAME'], 
                'filter' => ['ACTIVE' => 'Y']
            ]);
            while($arUserField = $rsUserFields->fetch())
                $arUserFieldsID[$arUserField["NAME"]."_VAL"] = $arUserField["ID"];

            $rsResUserFieldValues = ReviewsFieldsValuesTable::getList( array (
                'select' => ["VALUE", "FIELD_ID", "ID"],
                'filter' => ["REVIEW_ID" => $idReview, "FIELD_ID" => $arUserFieldsID],
            ) );
            while($arResUserFieldValue = $rsResUserFieldValues->fetch())
            {
                $key = array_search($arResUserFieldValue["FIELD_ID"], $arUserFieldsID);
                if($key)
                    $arResUserFieldValues[$key] = [ "VALUE" => $arResUserFieldValue["VALUE"],
                        "VALUE_ID" => $arResUserFieldValue["ID"]];
            }

            foreach($arUserFieldsID as $codeField => $nameField)
            {
                if($_REQUEST[$codeField] != $arResUserFieldValues[$codeField]["VALUE"])
                {
                    if(isset($_REQUEST[$codeField]) && strlen($_REQUEST[$codeField]) > 0)
                    {
                        $arUserFieldValues = [
                            "VALUE" => $_REQUEST[$codeField],
                            "REVIEW_ID" => $idReview,
                            "FIELD_ID" => $arUserFieldsID[$codeField]
                        ];

                        if(array_key_exists($codeField, $arResUserFieldValues)) 
                        {
                            $resultOperation = ReviewsFieldsValuesTable::update($arResUserFieldValues[$codeField]["VALUE_ID"], $arUserFieldValues);
                            if (!$resultOperation->isSuccess())
                                $errors = $resultOperation->getErrorMessages();
                        }
                        else 
                        {
                            $resultOperation = ReviewsFieldsValuesTable::add($arUserFieldValues);
                            if (!$resultOperation->isSuccess())
                                $errors = $resultOperation->getErrorMessages();
                        }

                        unset($arUserFieldValues);
                    }
                    else 
                    {
                        $resultOperation = ReviewsFieldsValuesTable::delete($arResUserFieldValues[$codeField]["VALUE_ID"]);
                        if (!$resultOperation->isSuccess())
                            $errors = $resultOperation->getErrorMessages();
                    }
                }
            }

            if($errors)
                printErrors($errors);
            else
                echo $strResult;
        }
}
elseif(array_key_exists("AUTH_FORM", $_POST) && strlen($_POST["AUTH_FORM"]) > 0 && $_POST["AUTH_FORM"] == 'Y')
{
    $userLogin = htmlspecialchars($_POST['USER_LOGIN']);
    $userPass = htmlspecialchars($_POST['USER_PASSWORD']);

    $rsUser = $USER->GetByLogin($userLogin);
    if($arUser = $rsUser->Fetch()) 
    {
        $arAuthResult = $USER->Login($userLogin, $userPass, "Y");
        if($arAuthResult['TYPE'] == 'ERROR') 
        {
            http_response_code(401);
            echo $arAuthResult['MESSAGE'];
        }
        else 
        {
            $APPLICATION->arAuthResult = $arAuthResult;

            $objAjaxParams = json_decode($_REQUEST["PARAMS"]);
            $arAjaxParams = (array) $objAjaxParams;
            $APPLICATION->IncludeComponent(
                "sc.reviews:reviews.add",
                "",
                Array(
                    "ID_ELEMENT" => $arAjaxParams["ID_ELEMENT"],
                    "ID" => "0", 
                    "MAX_RATING" => $arAjaxParams["MAX_RATING"],
                    "DEFAULT_RATING_ACTIVE" => "3",
                    "PRIMARY_COLOR" => $arAjaxParams["PRIMARY_COLOR"],
                    "BUTTON_BACKGROUND" => "#c61919",
                    "BUTTON_TEXT" => "Написать отзыв", 
                    "TEXTBOX_MAXLENGTH" => "200",
                    "NOTICE_EMAIL" => "",
                    "AUTHORIZED_USER" => $arAjaxParams["AUTHORIZED_USER"],
                    "UPLOAD_IMAGE" => $arAjaxParams["UPLOAD_IMAGE"],
                    "MAX_IMAGE_SIZE" => $arAjaxParams["MAX_IMAGE_SIZE"],
                    "THUMB_WIDTH" => $arAjaxParams["THUMB_WIDTH"],
                    "THUMB_HEIGHT" => $arAjaxParams["THUMB_HEIGHT"],
                    "MAX_COUNT_IMAGES" => $arAjaxParams["MAX_COUNT_IMAGES"],
                    "MULTIMEDIA_VIDEO_ALLOW" => $arAjaxParams["MULTIMEDIA_VIDEO_ALLOW"],
                    "CACHE_TYPE" => $arAjaxParams["CACHE_TYPE"], 
                    "CACHE_TIME" => $arAjaxParams["CACHE_TIME"] 
                ),
                $component
            );

        }
    }
    else
    {
        echo "Неверный логин или пароль.";
        http_response_code(401);
    }
}
elseif(array_key_exists("REGISTER", $_POST) && is_array($_POST["REGISTER"]))
{
    $APPLICATION->IncludeComponent("southcoast:main.register","error_register",
        unserialize($_POST["arparams"]),
        $component);
    $resRegister = $APPLICATION->IncludeComponent("southcoast:main.register","error_register",
        unserialize($_POST["arparams"]),
        $component);

    if(!$USER->IsAuthorized()) 
    {
        $userLogin = Bitrix\Main\UserTable::getList([
            'select' => ['LOGIN'],
            'filter' => ['LOGIN' => $_REQUEST["REGISTER"]["LOGIN"]]
        ])->fetch()['LOGIN']; 
        if(!$userLogin)
            $userLogin = Bitrix\Main\UserTable::getList([
                'select' => ['LOGIN'],
                'filter' => ['LOGIN' => $_REQUEST["REGISTER"]["PERSONAL_MOBILE"]]
            ])->fetch()['LOGIN']; 
    }
    $arAuthResult = $USER->Login($userLogin, $_REQUEST["REGISTER"]["PASSWORD"]);

    if($USER->IsAuthorized())
    {
        $APPLICATION->RestartBuffer();
        $objAjaxParams = json_decode($_REQUEST["PARAMS"]);
        $arAjaxParams = (array) $objAjaxParams;
        $APPLICATION->IncludeComponent(
            "sc.reviews:reviews.add",
            "",
            Array(
                "ID_ELEMENT" => $arAjaxParams["ID_ELEMENT"],
                "ID" => "0", 
                "MAX_RATING" => $arAjaxParams["MAX_RATING"],
                "DEFAULT_RATING_ACTIVE" => "3",
                "PRIMARY_COLOR" => $arAjaxParams["PRIMARY_COLOR"],
                "BUTTON_BACKGROUND" => "#c61919",
                "BUTTON_TEXT" => "Написать отзыв", 
                "TEXTBOX_MAXLENGTH" => "200",
                "NOTICE_EMAIL" => "",
                "AUTHORIZED_USER" => $arAjaxParams["AUTHORIZED_USER"],
                "UPLOAD_IMAGE" => $arAjaxParams["UPLOAD_IMAGE"],
                "MAX_IMAGE_SIZE" => $arAjaxParams["MAX_IMAGE_SIZE"],
                "THUMB_WIDTH" => $arAjaxParams["THUMB_WIDTH"],
                "THUMB_HEIGHT" => $arAjaxParams["THUMB_HEIGHT"],
                "MAX_COUNT_IMAGES" => $arAjaxParams["MAX_COUNT_IMAGES"],
                "MULTIMEDIA_VIDEO_ALLOW" => $arAjaxParams["MULTIMEDIA_VIDEO_ALLOW"],
                "CACHE_TYPE" => $arAjaxParams["CACHE_TYPE"], 
                "CACHE_TIME" => $arAjaxParams["CACHE_TIME"] 
            ),
            $component
        );
    }
}
die();
