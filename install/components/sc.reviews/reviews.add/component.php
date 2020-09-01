<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Loader;
use SouthCoast\Reviews\Internals\ReviewsBansTable,
    SouthCoast\Reviews\Internals\ReviewsTable,
    SouthCoast\Reviews\Internals\ReviewsFieldsTable,
    SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable;
use Bitrix\Main\Entity;
use \Bitrix\Main\Type\DateTime;

if (! Loader::includeModule ( 'sc.reviews' ))
	return false;

global $APPLICATION;
global $USER;
global $CACHE_MANAGER;

if (! isset ( $arParams["MAX_RATING"] ))
	$arParams["MAX_RATING"] = 5;

if (! isset ( $arParams["DEFAULT_RATING_ACTIVE"] ))
	$arParams["DEFAULT_RATING_ACTIVE"] = 3;

if (! isset ( $arParams["TEXTBOX_MAXLENGTH"] ))
	$arParams["TEXTBOX_MAXLENGTH"] = 100;

if (! isset ( $arParams["PRIMARY_COLOR"] ))
	$arParams["PRIMARY_COLOR"] = "#a76e6e";

if (! isset ( $arParams["BUTTON_BACKGROUND"] ))
	$arParams["BUTTON_BACKGROUND"] = "#dbbfb9";

$arParams["ID"] = intval($arParams["ID"]);

$arParams["THUMB_WIDTH"] = intval($arParams["THUMB_WIDTH"]);
if($arParams["THUMB_WIDTH"] <= 0)
    $arParams["THUMB_WIDTH"] = 150;

$arParams["THUMB_HEIGHT"] = intval($arParams["THUMB_HEIGHT"]);
if($arParams["THUMB_HEIGHT"] <= 0)
    $arParams["THUMB_HEIGHT"] = 150;

$arFilterBanedUser = [
    'ACTIVE' => 'Y',
    '>DATE_TO' => date('d.m.Y H:i:s'), 
];

if($USER->IsAuthorized())
    $arFilterBanedUser["ID_USER"] = $USER->GetID();
else
    $arFilterBanedUser["BX_USER_ID"] = $_COOKIE["BX_USER_ID"];

$arBanedUser = ReviewsBansTable::getList([
    'select' => ['ID_USER', 'IP', 'REASON', 'BX_USER_ID'],
    'filter' => $arFilterBanedUser
])->fetch();

if($arBanedUser)
{
    $arResult['BAN'] = "Y";
    $arResult['REASON'] = $arBanedUser['REASON'];
}
else
    $arResult["BAN"] = "N";

$arResult["MODERATIONGROUPS"] = false;
if($APPLICATION->GetGroupRight("sc.reviews") >= 'T')
{
    $arResult["MODERATIONGROUPS"] = true;
    $arResult['BAN'] = 'N';
}

$arRuntime = [];
$arUserFields = [];
$arUserFieldSelect = [];

$rsUserFields = ReviewsFieldsTable::getList([
    'select' => ['ID', 'NAME', 'TITLE'],
    'filter' => ['ACTIVE' => 'Y']
]);
while($arUserField = $rsUserFields->fetch())
{
    $arRuntime[] = new Entity\ReferenceField(
        $arUserField["NAME"],
        'SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable',
        array("=this.ID" => "ref.REVIEW_ID", "ref.FIELD_ID" => new Bitrix\Main\DB\SqlExpression('?', $arUserField["ID"]))
    );

    if(strpos($arUserField["NAME"], "DATE") !== false)
    {
        $arUserFieldSelect[] =
            new Entity\ExpressionField($arUserField["NAME"] . "_VAL_DATE",
                'CONVERT(%s, DATE)',
                array($arUserField["NAME"].".VALUE"));
    }

    $arUserFields[$arUserField["NAME"]."_VAL"] = $arUserField["TITLE"];
    $arUserFieldSelect[$arUserField["NAME"]."_VAL"] = $arUserField["NAME"].".VALUE";

}
$arResult["USERFIELDS"] = $arUserFields;

if($arParams["ID"] == 0) 
{
    $arParams["CACHE_TYPE"] = 'N';
    $this->IncludeComponentTemplate();
}
elseif($arParams["ID"] > 0) 
{
    $cacheDir = "/" . SITE_ID . "/sc.reviews/reviews.add/" . $arParams["ID_REVIEW"] . "/";
    foreach ($arParams as $keyParam => $valueParam)
        if (strncmp("~", $keyParam, 1))
            $cacheId .= ",".$keyParam."=".$valueParam;

    $cacheId = $arResult["BAN"] . $cacheId;

    $obCache = Bitrix\Main\Data\Cache::createInstance();
    if($obCache->initCache($arParams["CACHE_TIME"], $cacheId, $cacheDir))
    {
        $arVars = $obCache->GetVars();
        $arResult = $arVars['arResult'];
        $this->SetTemplateCachedData($arVars['templateCacheData']);
    }
    elseif($obCache->StartDataCache())
    {
        $CACHE_MANAGER->StartTagCache($cacheDir);

        $arReviewSelect = [
            'ID',
            'ID_USER',
            'BX_USER_ID',
            'IP_USER',
            'RATING',
            'TITLE',
            'PLUS',
            'MINUS',
            'ANSWER',
            'DATE_CREATION',
            'RECOMMENDATED',
            'MULTIMEDIA',
            'FILES',
            'MODERATED'
        ];

        $arEditReview = ReviewsTable::getList([
            'select' => array_merge($arReviewSelect, $arUserFieldSelect),
            'filter' => ["ID" => $arParams["ID"], "ACTIVE" => "Y"],
            'runtime' => $arRuntime,
        ])->fetch();
        $CACHE_MANAGER->RegisterTag("sc.reviews_review_id_" . $arParams["ID"]);
        $arResult["REVIEW"] = $arEditReview;

        $CACHE_MANAGER->EndTagCache();
        $templateCacheData = $this->GetTemplateCachedData();

        $obCache->EndDataCache([
            'arResult' => $arResult,
            'templateCacheData' => $templateCacheData
        ]);
    }

    if(($USER->IsAuthorized() &&
            (
                $APPLICATION->GetGroupRight( 'sc.reviews' ) >= "V" ||
                $arResult["REVIEW"]["ID_USER"] == $USER->GetID() ||
                ($arResult["REVIEW"]["ID_USER"] == 0 && $arResult["REVIEW"]["BX_USER_ID"] == $_COOKIE["BX_USER_ID"])
            )
        ) ||
        (!$USER->IsAuthorized() && $arResult["REVIEW"]["BX_USER_ID"] == $_COOKIE["BX_USER_ID"]))
        $this->IncludeComponentTemplate ();
}
?>