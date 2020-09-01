<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SC_REVIEWS_DESC_LIST"),
	"DESCRIPTION" => GetMessage("SC_REVIEWS_DESC_LIST_DESC"),
	"ICON" => "/images/reviews.gif",
	"SORT" => 11,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "screviews",
			"NAME" => GetMessage("SC_REVIEWS_DESC_NEWS"),
			"SORT" => 11,
			),
		),
);
?>