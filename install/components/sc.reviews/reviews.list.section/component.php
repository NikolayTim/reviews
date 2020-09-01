<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Loader;
use SouthCoast\Reviews\Internals\ReviewsBansTable,
    SouthCoast\Reviews\Internals\ReviewsTable,
    SouthCoast\Reviews\Internals\ReviewsFieldsTable,
    SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable;
use Bitrix\Main\Entity;
use \Bitrix\Main\Type\DateTime;

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

if($arParams["ID_SECTION"] <= 0)
{
    ShowError(GetMessage("SC_ID_SECTION_UNDEFINED"));
    return;
}

$arParams["ID_SECTION"] = intval($arParams["ID_SECTION"]);

$arrFilter = $GLOBALS[$arParams["FILTER"]];
if(!is_array($arrFilter))
    $arrFilter = array();

$arParams["COUNT_REVIEWS"] = intval($arParams["COUNT_REVIEWS"]);
if($arParams["COUNT_REVIEWS"] <= 0)
    $arParams["COUNT_REVIEWS"] = 10;

$arParams["MAX_RATING"] = intval($arParams["MAX_RATING"]);
if ($arParams["MAX_RATING"] <= 0)
    $arParams["MAX_RATING"] = 5;

$nav = new \Bitrix\Main\UI\PageNavigation("nav-more-reviews");
$nav->allowAllRecords(true)
    ->setPageSize($arParams["COUNT_REVIEWS"])
    ->initFromUri();

$cacheDir = "/" . SITE_ID . "/sc.reviews/reviews.list.section/" . $arParams["ID_SECTION"] . "/";
foreach ($arParams as $keyParam => $valueParam)
    if (strncmp("~", $keyParam, 1))
        $cacheId .= ",".$keyParam."=".$valueParam;

foreach ($GLOBALS[$arParams["FILTER"]] as $keyFilter => $valueFilter)
    $cacheId .= ",".$keyFilter."=".$valueFilter;

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

    $arObjectsID = [];
    $arSelectObjects = ['ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL'];
    $arFilterObjects = [
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'SECTION_ID' => $arParams["ID_SECTION"],
        'ACTIVE' => 'Y',
        'INCLUDE_SUBSECTIONS' => 'Y'
    ];
    $dbObjects = CIBlockElement::GetList([], $arFilterObjects, false, false, $arSelectObjects);
    while($arObject = $dbObjects->GetNext())
    {
        $arResult['OBJECTS'][$arObject['ID']] = $arObject;
        $arObjectsID[] = $arObject['ID'];
    }

    $arReviewFilter = [
        "@ID_ELEMENT" => $arObjectsID, 
        "ACTIVE" => "Y",
    ];

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
        'IP_USER',
        'ID_ELEMENT'
    ];

    $arReviewOrder = [$arParams["ORDER"] => $arParams["ORDER_BY"], "ID" => "DESC"];

    $offset = 0;
    $limit = $nav->getLimit(); 

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
        $CACHE_MANAGER->RegisterTag("sc.reviews_section_id_" . $arParams["ID_SECTION"]);
        $arResult["REVIEWS"][] = $arResReview;
    }

    $arResult['SECTION'] = \Bitrix\Iblock\SectionTable::getList([
        'select' => ['DESCRIPTION', 'NAME'],
        'filter' => ['ID' => $arParams["ID_SECTION"]]
    ])->fetch();

    $CACHE_MANAGER->EndTagCache();
    $templateCacheData = $this->GetTemplateCachedData();

    $obCache->EndDataCache([
        'arResult' => $arResult,
        'templateCacheData' => $templateCacheData
    ]);
}
$this->IncludeComponentTemplate();
?>