<?
use SouthCoast\Reviews\Internals\ReviewsTable,
	SouthCoast\Reviews\Internals\ReviewsFieldsTable,
	SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile( __FILE__ );

if(!Loader::includeModule('sc.reviews') || !Loader::includeModule('iblock'))
	return false;

$iModuleID = 'sc.reviews';

$accessLevel = $APPLICATION->GetGroupRight($iModuleID);

if($accessLevel == "D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

$sTableID = "sc_reviews_reviews";
$oSort = new CAdminSorting( $sTableID, "DATE_CREATION", "desc" );
$lAdmin = new CAdminList( $sTableID, $oSort );

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach( $FilterArr as $f )
		global $$f;
	return count( $lAdmin->arFilterErrors )==0;
}

$FilterArr = Array (
		"find",
		"find_id",
		"find_id_element",
		"find_xml_id_element",
		"find_id_user",
		"find_rating",
        'find_plus',
		'find_minus',
		"find_moderated",
		"find_active"
);

$lAdmin->InitFilter( $FilterArr );
$arFilter = array ();

if(CheckFilter())
{
	if(isset($find_id) && strlen($find_id) > 0)
		$arFilter["ID"] = $find_id;

	if(isset($find_id_element) && strlen($find_id_element) > 0)
		$arFilter['ID_ELEMENT'] = $find_id_element;
	
	if(isset($find_xml_id_element) && strlen($find_xml_id_element) > 0)
		$arFilter['XML_ID_ELEMENT'] = $find_xml_id_element;
	
	if(isset($find_id_user) && strlen($find_id_user) > 0)
		$arFilter['ID_USER'] = $find_id_user;
	
	if(isset($find_rating) && strlen($find_rating) > 0)
		$arFilter['RATING'] = $find_rating;
	
	if(isset($find_plus) && strlen($find_plus) > 0)
		$arFilter['%PLUS'] = $find_plus;
	
	if(isset($find_minus) && strlen($find_minus) > 0)
		$arFilter['%MINUS'] = $find_minus;
	
	if(isset($find_moderated) && strlen($find_moderated) > 0)
		$arFilter['MODERATED'] = $find_moderated;
	
	if(isset($find_active) && strlen($find_active) > 0)
		$arFilter['ACTIVE'] = $find_active;
	
	if(empty( $arFilter['ID'] ))
		unset( $arFilter['ID'] );
	if(empty( $arFilter['ID_ELEMENT'] ))
		unset( $arFilter['ID_ELEMENT'] );
	if(empty( $arFilter['XML_ID_ELEMENT'] ))
		unset( $arFilter['XML_ID_ELEMENT'] );
	if(empty( $arFilter['ID_USER'] ))
		unset( $arFilter['ID_USER'] );
	if(empty( $arFilter['RATING'] ))
		unset( $arFilter['RATING'] );
	if(empty( $arFilter['%TEXT'] ))
		unset( $arFilter['%TEXT'] );
	if(empty( $arFilter['MODERATED'] ))
		unset( $arFilter['MODERATED'] );
	if(empty( $arFilter['ACTIVE'] ))
		unset( $arFilter['ACTIVE'] );
}

if($lAdmin->EditAction())
{
	foreach( $FIELDS as $ID => $arFields )
	{
		if(!$lAdmin->IsUpdated( $ID ))
			continue;
		
		$ID = IntVal( $ID );
		if($ID>0)
		{
			foreach( $arFields as $key => $value )
				$arData[$key] = $value;

			$arData['DATE_CHANGE'] = new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' );
			
			$oldValueModerated = ReviewsTable::getList([
					'select' => ['MODERATED'],
					'filter' => ['ID' => $arData['ID']],
				])->fetch();
			
			if($arData["MODERATED"] == "Y" && $oldValueModerated == "N") 
				$arData['MODERATED_BY'] = $USER->GetID();
			elseif($arData["MODERATED"] == "N" && $oldValueModerated == "Y") 
				$arData['MODERATED_BY'] = "";

			$result = ReviewsTable::update( $ID, $arData );
			unset( $arData );
			if(!$result->isSuccess())
				$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
		}
		else
			$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
	}
}

if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = ReviewsTable::getList( array (
				'select' => array_merge($arSelect, $arUserFieldSelect),
				'filter' => $arFilter,
				'order' => array ($by => $order),
				'runtime' => $arRuntime
		) );

		while( $arRes = $rsData->Fetch() )
			$arID[] = $arRes['ID'];
	}
	
	foreach( $arID as $ID )
	{
		if(strlen( $ID )<=0)
			continue;
		
		$ID = IntVal( $ID );
		switch($_REQUEST['action'])
		{
			case "delete" :
				$result = ReviewsTable::delete( $ID );
				if(!$result->isSuccess())
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_DEL_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND" ), $ID);

                $rsResUserFieldValues = ReviewsFieldsValuesTable::getList( array (
                    'select' => ["ID"],
                    'filter' => ["REVIEW_ID" => $ID],
                ) );
                while($arResUserFieldValue = $rsResUserFieldValues->fetch())
                {
                    $result = ReviewsFieldsValuesTable::delete($arResUserFieldValue["ID"]);
                    if(!$result->isSuccess())
                        $lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_DEL_ERROR_FIELD")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND" ), $arResUserFieldValue["ID"]);
                }

				break;
			case "activate" :
				if($ID>0)
				{
					$result = ReviewsTable::update( $ID, ['ACTIVE' => "Y"]);
					if(!$result->isSuccess())
						$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
				}
				else
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
				
				break;
			case "deactivate" :
				if($ID>0)
				{
					$result = ReviewsTable::update( $ID, ['ACTIVE' => "N"]);
					if(!$result->isSuccess())
						$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
				}
				else
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
				
				break;
			case "moderate" :
				if($ID>0)
				{
					$result = ReviewsTable::update( $ID, ['MODERATED_BY' => $USER->GetID(),
														  'MODERATED' => 'Y']);
					if(!$result->isSuccess())
						$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
				}
				else
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
				
				break;
			case "unmoderate" :
				if($ID>0)
				{
					$result = ReviewsTable::update( $ID, array (
							'MODERATED' => "N",
							'MODERATED_BY' => ""
					) );
					if(!$result->isSuccess())
						$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);
				}
				else
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_ELEMENT_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_ELEMENT_NOT_FOUND"), $ID);

				break;
		}
	}
}

$arRuntime = [];
$arUserFieldSelect = [];
$arUserFieldsHeaders = [];
$rsFields = ReviewsFieldsTable::getList([
					'select' => ['ID', 'NAME', 'TITLE'],
					'filter' => ['ACTIVE' => 'Y']
										]);
while($arField = $rsFields->fetch())
{
	$arUserFieldsHeaders[] = array (
				"id" => $arField["NAME"],
				"content" => $arField["TITLE"],
				"sort" => $arField["NAME"],
				"default" => true
		);
		
	$arRuntime[] = new Entity\ReferenceField(
                $arField["NAME"],
                'SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable',
                array("=this.ID" => "ref.REVIEW_ID", "ref.FIELD_ID" => new Bitrix\Main\DB\SqlExpression('?', $arField["ID"]))
            );
			
	$arUserFieldSelect[] = $arField["NAME"].".VALUE";
}

$arSelect = array (
				'ID',
				'ID_ELEMENT',
				'XML_ID_ELEMENT',
				'ID_USER',
				'BX_USER_ID',
				'IP_USER',
				'RATING',
				'DATE_CREATION',
				'PLUS',
				'MINUS',
				'MODERATED',
				'ACTIVE',
		);

$rsData = ReviewsTable::getList( array (
		'select' => array_merge($arSelect, $arUserFieldSelect),
		'filter' => $arFilter,
		'order' => array ($by => $order),
		'runtime' => $arRuntime
) );

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SC_REVIEWS_ELEMENT_NAV")));

$arHeaders = array (
	array (
			"id" => "ID",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_ID"),
			"sort" => "ID",
			"align" => "right",
			"default" => true
	),
	array (
			"id" => "ID_ELEMENT",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_ID_ELEMENT"),
			"sort" => "ID_ELEMENT",
			"default" => true
	),
	array (
			"id" => "ID_USER",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_ID_USER"),
			"sort" => "ID_USER",
			"align" => "right",
			"default" => true
	),
    array (
        "id" => "BX_USER_ID",
        "content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_BX_USER_ID"),
        "sort" => "BX_USER_ID",
        "align" => "right",
        "default" => true
    ),
    array (
        "id" => "IP_USER",
        "content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_IP_USER"),
        "sort" => "IP_USER",
        "align" => "right",
        "default" => true
    ),
	array (
			"id" => "RATING",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_RATING"),
			"sort" => "RATING",
			"align" => "right",
			"default" => true
	),
	array (
			"id" => "DATE_CREATION",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_DATE_CREATION"),
			"sort" => "DATE_CREATION",
			"default" => true
	),
	array (
			"id" => "PLUS",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_PLUS"),
			"sort" => "PLUS",
			"default" => true
	),
	array (
			"id" => "MINUS",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_MINUS"),
			"sort" => "MINUS",
			"default" => true
	),
	array (
			"id" => "MODERATED",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_MODERATED"),
			"sort" => "MODERATED",
			"default" => true
	),
	array (
			"id" => "ACTIVE",
			"content" => GetMessage("SC_REVIEWS_ELEMENT_TABLE_ACTIVE"),
			"sort" => "ACTIVE",
			"default" => true
	)
);

$arHeaders = array_merge($arHeaders, $arUserFieldsHeaders);
$lAdmin->AddHeaders($arHeaders);

while( $arRes = $rsData->NavNext( true, "f_" ) )
{
	$row = & $lAdmin->AddRow( $f_ID, $arRes );

	$el_res = CIBlockElement::GetByID( $f_ID_ELEMENT );
	if($el_arr = $el_res->GetNext())
		$row->AddViewField( "ID_ELEMENT", '<a href="sc.reviews_reviews_edit.php?ID='.$f_ID.'&lang='.LANG.'"">['.$el_arr['ID'].'] '.$el_arr['NAME'].'</a>' );

	$row->AddViewField( "XML_ID_ELEMENT", $f_XML_ID_ELEMENT );

	$arUser = \Bitrix\Main\UserTable::getList(['select' => ['NAME', 'LAST_NAME'], 'filter' => ['ID' => $f_ID_USER] ])->fetch();
	$row->AddViewField("ID_USER", '['.$f_ID_USER.'] '.$arUser['LAST_NAME'].' '.$arUser['NAME']);
	
	$Rating = '';
	for($i = 1; $i<=$f_RATING; ++$i)
		$Rating .= '&#9733;';

	$row->AddField( "RATING", $Rating );
	$row->AddViewField( "DATE_CREATION", $f_DATE_CREATION );

	if($accessLevel >= "V")
		$row->AddInputField( "PLUS", $f_PLUS );
	
	$row->AddViewField( "PLUS", $f_PLUS );
	if($accessLevel >= "V")
		$row->AddInputField( "MINUS", $f_MINUS );

	$row->AddViewField( "MINUS", $f_MINUS );
	
	$row->AddField( "MODERATED", ($f_MODERATED=='Y') ? '<span style="display:block;background:green;width:16px;height:16px;"></span>' : '<span style="display:block;background:red;width:16px;height:16px;"></span>' );
	
	if($accessLevel >= "V")
		$row->AddCheckField( "ACTIVE" );
	
	$row->AddViewField("ACTIVE", ($f_ACTIVE == "Y" ? 
									GetMessage("SC_REVIEWS_ELEMENT_POST_YES") : 
									GetMessage("SC_REVIEWS_ELEMENT_POST_NO")));

	foreach($arUserFieldsHeaders as $arUserFld)
	{
		if(is_null($arRes["SOUTHCOAST_REVIEWS_INTERNALS_REVIEWS_".$arUserFld["id"]."_VALUE"]))
			$arRes["SOUTHCOAST_REVIEWS_INTERNALS_REVIEWS_".$arUserFld["id"]."_VALUE"] = "";
		
		$row->AddViewField($arUserFld["id"], $arRes["SOUTHCOAST_REVIEWS_INTERNALS_REVIEWS_".$arUserFld["id"]."_VALUE"]);
	}

	$arActions = Array ();
	
	if($accessLevel >= "V")
	{
		$arActions[] = array (
				"ICON" => "edit",
				"DEFAULT" => true,
				"TEXT" => GetMessage("SC_REVIEWS_ELEMENT_EDIT"),
				"ACTION" => $lAdmin->ActionRedirect("sc.reviews_reviews_edit.php?ID=".$f_ID)
		);

		$arActions[] = array (
				"ICON" => "delete",
				"TEXT" => GetMessage("SC_REVIEWS_ELEMENT_DEL"),
				"ACTION" => "if(confirm('".GetMessage('SC_REVIEWS_ELEMENT_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);
	}
	$arActions[] = array ("SEPARATOR" => true);
	
	if(is_set( $arActions[count( $arActions )-1], "SEPARATOR" ))
		unset( $arActions[count( $arActions )-1] );
	
	$row->AddActions( $arActions );
}

$lAdmin->AddFooter( array (
		array (
				"title" => GetMessage("SC_REVIEWS_ELEMENT_LIST_SELECTED"),
				"value" => $rsData->SelectedRowsCount()
		),
		array (
				"counter" => true,
				"title" => GetMessage("SC_REVIEWS_ELEMENT_LIST_CHECKED"),
				"value" => "0"
		)
) );

$arGroupActions = [];
if($accessLevel >= "V")
{
	$arGroupActions["delete"] = GetMessage("SC_REVIEWS_ELEMENT_LIST_DELETE");
	$arGroupActions["activate"] = GetMessage("SC_REVIEWS_ELEMENT_LIST_ACTIVATE");
	$arGroupActions["deactivate"] = GetMessage("SC_REVIEWS_ELEMENT_LIST_DEACTIVATE");
}

if(in_array($accessLevel, ["T", "W"]))	
{
	$arGroupActions["moderate"] = GetMessage("SC_REVIEWS_ELEMENT_LIST_MODERATE");
	$arGroupActions["unmoderate"] = GetMessage("SC_REVIEWS_ELEMENT_LIST_UNMODERATE");
}

$lAdmin->AddGroupActionTable($arGroupActions);

$arContextMenu = [];
if($accessLevel >= "V")
	$arContextMenu = array(array(
		"TEXT"=>GetMessage("SC_REVIEWS_ADD_REVIEW"),
		"LINK"=>"sc.reviews_reviews_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("SC_REVIEWS_ADD_TITLE"),
		"ICON"=>"btn_new",
	  ));

$lAdmin->AddAdminContextMenu($arContextMenu);
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage("SC_REVIEWS_ELEMENT_TITLE"));

require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arFilterFields = array (
		GetMessage("SC_REVIEWS_ELEMENT_ID"),
		GetMessage("SC_REVIEWS_ELEMENT_ID_ELEMENT"),
		GetMessage("SC_REVIEWS_ELEMENT_XML_ID_ELEMENT"),
		GetMessage("SC_REVIEWS_ELEMENT_ID_USER"),
		GetMessage("SC_REVIEWS_ELEMENT_RATING"),
        GetMessage("SC_REVIEWS_ELEMENT_PLUS"),
        GetMessage("SC_REVIEWS_ELEMENT_MINUS"),
		GetMessage("SC_REVIEWS_ELEMENT_MODERATED"),
		GetMessage("SC_REVIEWS_ELEMENT_ACTIVE")
);
$oFilter = new CAdminFilter($sTableID."_filter", $arFilterFields);
?>

<form name="find_form" method="get"	action="<?echo $APPLICATION->GetCurPage();?>">
	<?$oFilter->Begin();?>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_ID")?>:</td>
		<td><input type="text" name="find_id" size="47"	value="<?echo htmlspecialchars($find_id)?>">
			<?unset( $find_id );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_ID_ELEMENT")?>:</td>
		<td><input type="text" name="find_id_element" size="47"	value="<?echo htmlspecialchars($find_id_element)?>">
			<?unset( $find_id_element );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_XML_ID_ELEMENT")?>:</td>
		<td><input type="text" name="find_xml_id_element" size="47"	value="<?echo htmlspecialchars($find_xml_id_element)?>">
			<?unset( $find_xml_id_element );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_ID_USER")?>:</td>
		<td><input type="text" name="find_id_user" size="47" value="<?echo htmlspecialchars($find_id_user)?>">
			<?unset( $find_id_user );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_RATING")?>:</td>
		<td><input type="text" name="find_rating" size="47"	value="<?echo htmlspecialchars($find_rating)?>">
			<?unset( $find_rating );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_PLUS")?>:</td>
		<td><input type="text" name="find_plus" size="47" value="<?echo htmlspecialchars($find_plus)?>">
			<?unset( $find_plus );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_MINUS")?>:</td>
		<td><input type="text" name="find_minus" size="47" value="<?echo htmlspecialchars($find_minus)?>">
			<?unset( $find_minus );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_MODERATED")?>:</td>
		<td>
			<?
			$arr = array (
					"reference" => array (
							GetMessage("SC_REVIEWS_ELEMENT_POST_YES"),
							GetMessage("SC_REVIEWS_ELEMENT_POST_NO")
					),
					"reference_id" => ["Y",	"N"]
			);
			echo SelectBoxFromArray( "find_moderated", $arr, $find_moderated, "", "" );
			unset( $find_moderated );
			?>
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("SC_REVIEWS_ELEMENT_ACTIVE")?>:</td>
		<td>
			<?
			echo SelectBoxFromArray( "find_active", $arr, $find_active, "", "" );
			unset( $find_active );
			?>
		</td>
	</tr>
	
	<?
	$oFilter->Buttons( array (
			"table_id" => $sTableID,
			"url" => $APPLICATION->GetCurPage(),
			"form" => "find_form"
	) );
	$oFilter->End();
	?>
</form>

<?
$lAdmin->DisplayList();
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
