<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("iblock"))
	return;

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"MAX_RATING" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_LIST_MAX_RATING"),
			"TYPE" => "STRING",
			"DEFAULT" => 5,
		),
		"PRIMARY_COLOR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_LIST_PRIMARY_COLOR"),
			"TYPE" => "STRING",
			"DEFAULT" => "#a76e6e",
		),
        "BUTTON_BACKGROUND" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SC_REVIEWS_LIST_BUTTON_BACKGROUND"),
            "TYPE" => "STRING",
            "DEFAULT" => "#dbbfb9",
        ),
		"AJAX" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_LIST_AJAX"),
			"TYPE" => "LIST",
			"VALUES"=>array('N'=>GetMessage("SC_REVIEWS_LIST_OFF"),'Y'=>GetMessage("SC_REVIEWS_LIST_ON"))
		),
		"DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("SC_REVIEWS_LIST_DATE_FORMAT"), "BASE"),
		"ID_ELEMENT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_LIST_ID_ELEMENT"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$ElementID}',
		),
		"AUTHORIZED_USER" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_LIST_AUTHORIZE_USER"),
			"TYPE" => "LIST",
			"VALUES"=> array('N' => GetMessage("SC_REVIEWS_LIST_NO_AUTHORIZED_USER"),'Y' => GetMessage("SC_REVIEWS_LIST_AUTHORIZED_USER"))
		),
		"COUNT_REVIEWS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_LIST_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => '10',
		),
		"CACHE_TIME" => array(
			"DEFAULT" => 36000000,
		),

        "UPLOAD_IMAGE" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_LIST_UPLOAD_IMAGE"),
            "TYPE" => "LIST",
            "VALUES"=>array('N' => GetMessage("SC_REVIEWS_LIST_NO"), 'Y' => GetMessage("SC_REVIEWS_LIST_YES"))
        ),
        "MAX_IMAGE_SIZE" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_LIST_MAX_IMAGE_SIZE"),
            "TYPE" => "STRING",
            "DEFAULT" => '2',
        ),
        "THUMB_WIDTH" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_LIST_THUMB_WIDTH"),
            "TYPE" => "STRING",
            "DEFAULT" => '150',
        ),
        "THUMB_HEIGHT" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_LIST_THUMB_HEIGHT"),
            "TYPE" => "STRING",
            "DEFAULT" => '150',
        ),
        "MAX_COUNT_IMAGES" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_LIST_MAX_COUNT_IMAGES"),
            "TYPE" => "STRING",
            "DEFAULT" => '5',
        ),
        "MULTIMEDIA_VIDEO_ALLOW" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_LIST_MULTIMEDIA_VIDEO_ALLOW"),
            "TYPE" => "LIST",
            "VALUES"=>array('N' => GetMessage("SC_REVIEWS_LIST_NO"), 'Y' => GetMessage("SC_REVIEWS_LIST_YES"))
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
            "DEFAULT" => '=arrFilter',
        ),
        "NPAGES_SHOW" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("SC_REVIEWS_NPAGES_SHOW"),
            "TYPE" => "STRING",
            "DEFAULT" => '1',
        ),
	),
);
?>