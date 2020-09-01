<?
IncludeModuleLangFile(__FILE__);
$iModuleID = 'sc.reviews';

$accessLevel = $APPLICATION->GetGroupRight($iModuleID);

if($accessLevel >= "R"){
	$rsSites = CSite::GetList($by="sort", $order="desc", Array("ACTIVE"=>"Y"));
	while ($arSite = $rsSites->Fetch())
	{
		$Sites[]=$arSite;
	}
	unset($rsSites);
	unset($arSite);

	$Paths=array('reviews'=>'_settings.php','comments'=>'_settings.php','questions'=>'_settings.php');
	if(count($Sites)==1)
	{
		foreach($Paths as $key=>$Path)
			$Settings[$key]=array(
				"text" => GetMessage($iModuleID."_MENU_REVIEWS_".$key."_SETTINGS_TEXT"),
				"url" => "sc.reviews_".$key.$Path."?lang=".LANGUAGE_ID.'&site='.$Sites[0]['LID'],
				"title" => GetMessage($iModuleID."_MENU_REVIEWS_".$key."_SETTINGS_TEXT")
			);
	}
	else
	{
		$Items=array();
		foreach($Paths as $key=>$Path)
		{
			foreach($Sites as $Site)
			{
				$Items[$key][]=array(
					"text" => '['.$Site['LID'].'] '.$Site['NAME'],
					"url" => "sc.reviews_".$key.$Path."?lang=".LANGUAGE_ID.'&site='.$Site['LID'],
					"title" => $Site['NAME']
				);
			}
		}


		foreach($Paths as $key=>$Path)
			$Settings[$key]=array(
				"text" => GetMessage($iModuleID."_MENU_REVIEWS_".$key."_SETTINGS_TEXT"),
					"items_id" => "menu_sc.reviews_settings".$key,
				"items"=>
						$Items[$key]
				,
				"title" => GetMessage($iModuleID."_MENU_REVIEWS_".$key."_SETTINGS_TEXT")
			);
	}

		$arReviewsFields = array(
							"text" => GetMessage($iModuleID."_MENU_REVIEWS_REVIEWS_FIELDS_TEXT"),
							"url" => "sc.reviews_reviews_fields_list.php?lang=".LANGUAGE_ID,
							"more_url" => array("sc.reviews_reviews_fields_list.php", "sc.reviews_reviews_fields_edit.php"),
							"title" => GetMessage($iModuleID."_MENU_REVIEWS_REVIEWS_FIELDS_TEXT")
						);

	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => 'sc.reviews',
		"sort" => 221,
		"text" => GetMessage($iModuleID."_MENU_REVIEWS_TEXT"),
		"title" => GetMessage($iModuleID."_MENU_REVIEWS_TITLE"),
		"url" => "sc.reviews_reviews_list.php?lang=".LANGUAGE_ID,
		"icon" => "sotbit_reviews_menu_icon",
		"page_icon" => "sotbit_reviews_page_icon",
		"items_id" => "menu_sc.reviews",
		"items" => array(
			array(
				"text" => GetMessage($iModuleID."_MENU_REVIEWS_REVIEWS_TEXT"),
				"title" => GetMessage($iModuleID."_MENU_REVIEWS_REVIEWS_TITLE"),
				"dynamic" => true,
				"items_id" => "menu_sc.reviews.reviews",
				"items"=>array(
					array(
						"text" => GetMessage($iModuleID."_MENU_REVIEWS_REVIEWS_TEXT_LIST"),
						"url" => "sc.reviews_reviews_list.php?lang=".LANGUAGE_ID,
                        "more_url" => array("sc.reviews_reviews_list.php", "sc.reviews_reviews_edit.php"),
						"title" => GetMessage($iModuleID."_MENU_REVIEWS_REVIEWS_TEXT_LIST")
					),
					$arReviewsFields,
					array(
						"text" => GetMessage($iModuleID."_MENU_REVIEWS_REVIEWS_CHECKS_LIST"),
						"url" => "sc.reviews_reviews_checks_list.php?lang=".LANGUAGE_ID,
                        "more_url" => array("sc.reviews_reviews_checks_list.php", "sc.reviews_reviews_checks_edit.php"),
						"title" => GetMessage($iModuleID."_MENU_REVIEWS_REVIEWS_CHECKS_LIST")
					),
					$Settings['reviews'],
				),
			) ,
			array(
					"text" => GetMessage($iModuleID."_MENU_REVIEWS_BANS_TEXT"),
					"title" => GetMessage($iModuleID."_MENU_REVIEWS_BANS_TITLE"),
					"dynamic" => true,
					"items_id" => "menu_sc.reviews.bans",
					"items"=>array(
							array(
									"text" => GetMessage($iModuleID."_MENU_REVIEWS_bans_SETTINGS_TEXT"),
									"url" => "sc.reviews_bans_list.php?lang=".LANGUAGE_ID,
									"more_url" => array("sc.reviews_bans_list.php", "sc.reviews_bans_edit.php"),
									"title" => GetMessage($iModuleID."_MENU_REVIEWS_bans_SETTINGS_TEXT")
							),
					),
			) ,
		)
	);
	return $aMenu;
}

return false;
?>