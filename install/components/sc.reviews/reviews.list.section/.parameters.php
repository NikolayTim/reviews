<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();
$arIBlock = array();
$iblockFilter = !empty($arCurrentValues['IBLOCK_TYPE'])
    ? array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
    : array('ACTIVE' => 'Y');

$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
{
    $id = (int)$arr['ID'];
    if (isset($offersIblock[$id]))
        continue;
    $arIBlock[$id] = '['.$id.'] '.$arr['NAME'];
}
unset($id, $arr, $rsIBlock, $iblockFilter);

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
        'IBLOCK_TYPE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('SC_REVIEWS_IBLOCK_TYPE'),
            'TYPE' => 'LIST',
            'VALUES' => $arIBlockType,
            'REFRESH' => 'Y',
        ),
        'IBLOCK_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('SC_REVIEWS_IBLOCK_IBLOCK'),
            'TYPE' => 'LIST',
            'ADDITIONAL_VALUES' => 'Y',
            'VALUES' => $arIBlock,
            'REFRESH' => 'Y',
        ),
        "ID_SECTION" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SC_REVIEWS_LIST_ID_SECTION"),
            "TYPE" => "STRING",
            "DEFAULT" => '={$SectionID}',
        ),
        "MAX_RATING" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SC_REVIEWS_LIST_MAX_RATING"),
			"TYPE" => "STRING",
			"DEFAULT" => 5,
		),
		"COUNT_REVIEWS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SC_REVIEWS_LIST_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => '10',
		),
		"CACHE_TIME" => array(
			"DEFAULT" => 36000000,
		),
        "ORDER" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("SC_REVIEWS_LIST_DESC_ORDER"),
            "TYPE" => "STRING",
            "DEFAULT" => 'DATE_CREATION',
        ),
        "ORDER_BY" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("SC_REVIEWS_DESC_ORDER_BY"),
            "TYPE" => "STRING",
            "DEFAULT" => 'DESC',
        ),
        "FILTER" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("SC_REVIEWS_FILTER"),
            "TYPE" => "STRING",
            "DEFAULT" => '={arrFilter}',
        ),
	),
);
?>