<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("SC_REVIEWS_ADD_EC_NAME"),
	"DESCRIPTION" => GetMessage("SC_REVIEWS_ADD_EC_DESCRIPTION"),
	"ICON" => "/images/reviews.gif",
	"SORT" => 12,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "screviews",
			"NAME" => GetMessage("SC_REVIEWS_ADD_EC_COMMENTS"),
			"SORT" => 11,
		),
	),
);
?>