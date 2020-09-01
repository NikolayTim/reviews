<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("iblock"))
	return;

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
        "ID_ELEMENT" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SC_REVIEWS_ADD_ID_ELEMENT"),
            "TYPE" => "STRING",
            "DEFAULT" => '={$ElementID}',
        ),
        "ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SC_REVIEWS_ADD_ID_REVIEW"),
            "TYPE" => "STRING",
            "DEFAULT" => '0',
        ),
        "MAX_RATING" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_ADD_MAX_RATING"),
			"TYPE" => "STRING",
			"DEFAULT" => 5,
		),
		"DEFAULT_RATING_ACTIVE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_ADD_DEFAULT_RATING_ACTIVE"),
			"TYPE" => "STRING",
			"DEFAULT" => 3,
		),
		"PRIMARY_COLOR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_ADD_PRIMARY_COLOR"),
			"TYPE" => "STRING",
			"DEFAULT" => "#a76e6e",
		),
		"BUTTON_BACKGROUND" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_ADD_BUTTON_BACKGROUND"),
			"TYPE" => "STRING",
			"DEFAULT" => "#dbbfb9",
		),
        "BUTTON_TEXT" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SC_REVIEWS_ADD_BUTTON_TEXT"),
            "TYPE" => "STRING",
            "DEFAULT" => GetMessage("SC_REVIEWS_ADD_BUTTON_TEXT_DEFAULT"),
        ),
        "TEXTBOX_MAXLENGTH" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_ADD_TEXTBOX_MAXLENGTH"),
			"TYPE" => "STRING",
			"DEFAULT" => 200,
		),
		"NOTICE_EMAIL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SC_REVIEWS_ADD_NOTICE_EMAIL"),
			"TYPE" => "STRING",
		),
        "AUTHORIZED_USER" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SC_REVIEWS_ADD_AUTHORIZE_USER"),
            "TYPE" => "LIST",
            "VALUES"=> array('N' => GetMessage("SC_REVIEWS_ADD_NO_AUTHORIZED_USER"),'Y' => GetMessage("SC_REVIEWS_ADD_AUTHORIZED_USER"))
        ),
        "UPLOAD_IMAGE" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_ADD_UPLOAD_IMAGE"),
            "TYPE" => "LIST",
            "VALUES"=>array('N' => GetMessage("SC_REVIEWS_ADD_NO"), 'Y' => GetMessage("SC_REVIEWS_ADD_YES"))
        ),
        "MAX_IMAGE_SIZE" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_ADD_MAX_IMAGE_SIZE"),
            "TYPE" => "STRING",
            "DEFAULT" => '2',
        ),
        "THUMB_WIDTH" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_ADD_THUMB_WIDTH"),
            "TYPE" => "STRING",
            "DEFAULT" => '150',
        ),
        "THUMB_HEIGHT" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_ADD_THUMB_HEIGHT"),
            "TYPE" => "STRING",
            "DEFAULT" => '150',
        ),
        "MAX_COUNT_IMAGES" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_ADD_MAX_COUNT_IMAGES"),
            "TYPE" => "STRING",
            "DEFAULT" => '5',
        ),
        "MULTIMEDIA_VIDEO_ALLOW" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SC_REVIEWS_ADD_MULTIMEDIA_VIDEO_ALLOW"),
            "TYPE" => "LIST",
            "VALUES"=>array('N' => GetMessage("SC_REVIEWS_ADD_NO"), 'Y' => GetMessage("SC_REVIEWS_ADD_YES"))
        ),
        "CACHE_TIME" => array(
			"DEFAULT" => 36000000,
		), 
	),
);
?>