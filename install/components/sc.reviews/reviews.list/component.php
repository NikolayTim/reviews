<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Loader;
use SouthCoast\Reviews\Internals\ReviewsBansTable,
    SouthCoast\Reviews\Internals\ReviewsTable,
    SouthCoast\Reviews\Internals\ReviewsFieldsTable,
    SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable;
use Bitrix\Main\Entity;
use \Bitrix\Main\Type\DateTime;

define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/123.txt");

global $CACHE_MANAGER;

if (!Loader::includeModule('iblock'))
{
    ShowError(GetMessage("SC_IBLOCK_MODULE_NOT_INSTALLED"));
    return;
}

if (!Loader::includeModule('sc.reviews'))
{
    ShowError(GetMessage("SC_REVIEWS_MODULE_NOT_INSTALLED"));
    return;
}

if($arParams["ID_ELEMENT"] <= 0)
{
    ShowError(GetMessage("SC_ID_ELEMENT_UNDEFINED"));
    return;
}

$arParams['AUTHORIZED_USER'] = "N";
$arParams["ID_ELEMENT"] = intval($arParams["ID_ELEMENT"]);

$arrFilter = $GLOBALS[$arParams["FILTER"]];
if(!is_array($arrFilter))
    $arrFilter = array();

$arParams["NPAGES_SHOW"] = intval($arParams["NPAGES_SHOW"]);
if($arParams["NPAGES_SHOW"] <= 0)
    $arParams["NPAGES_SHOW"] = 1;

$arParams["COUNT_REVIEWS"] = intval($arParams["COUNT_REVIEWS"]);
if($arParams["COUNT_REVIEWS"] <= 0)
    $arParams["COUNT_REVIEWS"] = 10;

$arParams["MAX_RATING"] = intval($arParams["MAX_RATING"]);
if ($arParams["MAX_RATING"] <= 0)
    $arParams["MAX_RATING"] = 5;

$arParams["THUMB_WIDTH"] = intval($arParams["THUMB_WIDTH"]);
if($arParams["THUMB_WIDTH"] <= 0)
    $arParams["THUMB_WIDTH"] = 150;

$arParams["THUMB_HEIGHT"] = intval($arParams["THUMB_HEIGHT"]);
if($arParams["THUMB_HEIGHT"] <= 0)
    $arParams["THUMB_HEIGHT"] = 150;

$nav = new \Bitrix\Main\UI\PageNavigation("nav-more-reviews");
$nav->allowAllRecords(true)
    ->setPageSize($arParams["COUNT_REVIEWS"])
    ->initFromUri();

$arFilterBanedUser = [
    'ACTIVE' => 'Y',
    '>DATE_TO' => date('d.m.Y H:i:s'),
];

$arResult["USERTYPE"] = "NORMAL";
if ($APPLICATION->GetGroupRight( 'sc.reviews' ) >= "T")
    $arResult["USERTYPE"] = "MODERATOR";

$cacheDir = "/" . SITE_ID . "/sc.reviews/reviews.list/" . $arParams["ID_ELEMENT"] . "/";
foreach ($arParams as $keyParam => $valueParam)
    if (strncmp("~", $keyParam, 1))
        $cacheId .= ",".$keyParam."=".$valueParam;

foreach ($GLOBALS[$arParams["FILTER"]] as $keyFilter => $valueFilter)
    $cacheId .= ",".$keyFilter."=".$valueFilter;

$cacheId = $arResult["USERTYPE"] . $cacheId;
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

    $arReviewFilter = [
        "ID_ELEMENT" => $arParams["ID_ELEMENT"],
        "ACTIVE" => "Y",
        "MODERATED" => "Y"
    ];
    if ($USER->IsAuthorized() && $APPLICATION->GetGroupRight( 'sc.reviews' ) >= "T")
        unset($arReviewFilter["MODERATED"]);

    $cntReviews = 0;
    $cntRating = 0;
    $arResult["STATISTICS"] = [];
    $rsResStatistics = ReviewsTable::getList( array (
        'select' => ['RATING', 'CNT'],
        'filter' => $arReviewFilter,
        'group' => ['RATING'],
        'runtime' => [
            new Entity\ExpressionField('CNT', 'COUNT(*)')
        ],
        'cache' => ['ttl' => $arParams["CACHE_TIME"]]
    ));
    while($arResStatistics = $rsResStatistics->fetch())
    {
        $arResult["STATISTICS"][$arResStatistics["RATING"]] = $arResStatistics["CNT"];
        $cntReviews = $cntReviews + intval($arResStatistics["CNT"]);
        $cntRating = $cntRating + $arResStatistics["RATING"] * $arResStatistics["CNT"];
    }
    $arResult["CNT_REVIEWS"] = ReviewsTable::getCount($arReviewFilter);

    if($cntReviews > 0) 
    {
        $arResult['RECOMMENDATED'] = round (
            (ReviewsTable::GetCount(array_merge(['=RECOMMENDATED' => 'Y'], $arReviewFilter))
                / $cntReviews) * 100 );
        $arResult['MID_REVIEW'] = round($cntRating / $cntReviews, 1);
    }
    else
    {
        $arResult['RECOMMENDATED'] = 0;
        $arResult['MID_REVIEW'] = 0;
    }

    if($arResult["CNT_REVIEWS"] > 0)
    {
        $arRuntime = [];
        $arUserFields = [];
        $arUserFieldSelect = [];

        $rsUserFields = ReviewsFieldsTable::getList([
            'select' => ['ID', 'NAME', 'TITLE'],
            'filter' => ['ACTIVE' => 'Y'],
            'cache' => ['ttl' => $arParams["CACHE_TIME"]]
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

        $arResult["REVIEWS"] = [];
        $arReviewSelect = [
            'ID',
            'ID_USER',
            'RATING',
            'TITLE',
            'PLUS',
            'MINUS',
            'ANSWER',
            'LIKES',
            'DISLIKES',
            'DATE_CREATION',
            'RECOMMENDATED',
            'MULTIMEDIA',
            'FILES',
            'SHOWS',
            'MODERATED',
            'BX_USER_ID',
            'IP_USER'
        ];

        $arReviewOrder = [$arParams["ORDER"] => $arParams["ORDER_BY"], "ID" => "DESC"];

        $offset = 0;
        $limit = $nav->getLimit() * $arParams["NPAGES_SHOW"];

        $rsResReviews = ReviewsTable::getList( array (
            'select' => array_merge($arReviewSelect, $arUserFieldSelect),
            'filter' => array_merge($arReviewFilter, $arrFilter),
            'order' => $arReviewOrder,
            'runtime' => $arRuntime,
            "count_total" => true,
            "offset" => $offset, 
            "limit" => $limit, 
            'cache' => ['ttl' => $arParams["CACHE_TIME"]]
        ) );
        $nav->setRecordCount($rsResReviews->getCount());
        while($arResReview = $rsResReviews->fetch())
        {
            $CACHE_MANAGER->RegisterTag("sc.reviews_element_id_" . $arParams["ID_ELEMENT"]);
            $arResult["REVIEWS"][] = $arResReview;
        }
    }

    $arResult["ELEMENT_NAME"] = \Bitrix\Iblock\ElementTable::getList([
        'select' => ['NAME'],
        'filter' => ['ID' => $arParams['ID_ELEMENT']]
    ])->fetch()["NAME"];

    $CACHE_MANAGER->EndTagCache();
    $templateCacheData = $this->GetTemplateCachedData();

    $obCache->EndDataCache([
        'arResult' => $arResult,
        'templateCacheData' => $templateCacheData
    ]);
}
$this->IncludeComponentTemplate();
?>