<?
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;
use SouthCoast\Reviews\Internals\ReviewsFieldsTable;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile( __FILE__ );
if(!Loader::includeModule('sc.reviews'))
	return false;

$accessLevel = $APPLICATION->GetGroupRight('sc.reviews');
if($accessLevel == "D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

$sTableID = "sc_reviews_fields";
$oSort = new CAdminSorting( $sTableID, "SORT", "desc" );
$lAdmin = new CAdminList( $sTableID, $oSort );

function CheckFilter()
{
	global $arrFilter, $lAdmin;
	foreach( $arrFilter as $f )
		global $$f;
	return count( $lAdmin->arFilterErrors )==0;
}

$arrFilter = Array (
		"find_id",
		"find_name",
		"find_title",
		"find_type",
		"find_active" 
);
$lAdmin->InitFilter( $arrFilter );
$arFilter = array ();

if(CheckFilter())
{
	if(isset($find_id) && strlen($find_id) > 0)
		$arFilter["ID"] = $find_id;

	if(isset($find_name) && strlen($find_name) > 0)
		$arFilter["NAME"] = $find_name;

	if(isset($find_title) && strlen($find_title) > 0)
		$arFilter["TITLE"] = $find_title;
	
	if(isset($find_type) && strlen($find_type) > 0)
		$arFilter["TYPE"] = $find_type;

	if(isset($find_active) && strlen($find_active) > 0)
		$arFilter["ACTIVE"] = $find_active;

	if(empty( $arFilter['ID'] ))
		unset( $arFilter['ID'] );
	
	if(empty( $arFilter['TITLE'] ))
		unset( $arFilter['TITLE'] );
	
	if(empty( $arFilter['NAME'] ))
		unset( $arFilter['NAME'] );
	
	if(empty( $arFilter['TYPE'] ))
		unset( $arFilter['TYPE'] );
	
	if(empty( $arFilter['ACTIVE'] ))
		unset( $arFilter['ACTIVE'] );
}

if($lAdmin->EditAction() && $accessLevel == "W")
{
	foreach($FIELDS as $ID => $arFields )
	{
		if(!$lAdmin->IsUpdated( $ID ))
			continue;
		$ID = IntVal( $ID );
		if($ID>0)
		{
			foreach( $arFields as $key => $value )
				$arData[$key] = $value;
			$result = ReviewsFieldsTable::update( $ID, $arData );
			unset( $arData );
			if(!$result->isSuccess())
			{
				$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_FIELDS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_FIELDS_NOT_FOUND"), $ID);
			}
			unset( $result );
		}
		else
			$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_FIELDS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_FIELDS_NOT_FOUND"), $ID);
	}
}

if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = ReviewsFieldsTable::getList( array (
				'select' => array (
						'ID',
						'SORT',
						'TITLE',
						'NAME',
						'TYPE',
						'SETTINGS',
						'ACTIVE' 
				),
				'filter' => $arFilter,
				'order' => array (
						$by => $order 
				) 
		) );
		while( $arRes = $rsData->Fetch() )
			$arID[] = $arRes['ID'];
		unset( $arRes );
		unset( $rsData );
	}
	foreach( $arID as $ID )
	{
		if(strlen( $ID )<=0)
			continue;
		$ID = IntVal( $ID );
		switch($_REQUEST['action'])
		{
			case "delete" :
				$result = ReviewsFieldsTable::delete( $ID );
				if(!$result->isSuccess())
				{
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_FIELDS_DEL_ERROR")." ".GetMessage("SC_REVIEWS_FIELDS_NOT_FOUND"), $ID);
				}
				unset( $result );
				break;
			case "activate" :
			case "deactivate" :
				if($ID>0)
				{
					$arFields["ACTIVE"] = ($_REQUEST['action']=="activate" ? "Y" : "N");
					$result = ReviewsFieldsTable::update( $ID, array (
							'ACTIVE' => $arFields["ACTIVE"] 
					) );
					if(!$result->isSuccess())
						$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_FIELDS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_FIELDS_NOT_FOUND"), $ID);
					unset( $result );
				}
				else
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_FIELDS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_FIELDS_NOT_FOUND"), $ID);
				break;
		}
	}
}

$rsData = ReviewsFieldsTable::getList( array (
		'select' => array (
				'ID',
				'SORT',
				'NAME',
				'TITLE',
				'TYPE',
				'SETTINGS',
				'ACTIVE' 
		),
		'filter' => $arFilter,
		'order' => array (
				$by => $order 
		) 
) );
$rsData = new CAdminResult( $rsData, $sTableID );
$rsData->NavStart();
$lAdmin->NavText( $rsData->GetNavPrint( GetMessage( "SC_REVIEWS_FIELDS_NAV" ) ) );

$lAdmin->AddHeaders( array (
		array (
				"id" => "ID",
				"content" => GetMessage( "SC_REVIEWS_FIELDS_TABLE_ID" ),
				"sort" => "ID",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "SORT",
				"content" => GetMessage( "SC_REVIEWS_FIELDS_TABLE_SORT" ),
				"sort" => "SORT",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "NAME",
				"content" => GetMessage( "SC_REVIEWS_FIELDS_TABLE_NAME" ),
				"sort" => "NAME",
				"default" => true 
		),
		array (
				"id" => "TITLE",
				"content" => GetMessage( "SC_REVIEWS_FIELDS_TABLE_TITLE" ),
				"sort" => "TITLE",
				"default" => true 
		),
		array (
				"id" => "TYPE",
				"content" => GetMessage( "SC_REVIEWS_FIELDS_TABLE_TYPE" ),
				"sort" => "TYPE",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "SETTINGS",
				"content" => GetMessage( "SC_REVIEWS_FIELDS_TABLE_SETTINGS" ),
				"sort" => "SETTINGS",
				"default" => true 
		),
		array (
				"id" => "ACTIVE",
				"content" => GetMessage( "SC_REVIEWS_FIELDS_TABLE_ACTIVE" ),
				"sort" => "ACTIVE",
				"default" => true 
		) 
) );

while( $arRes = $rsData->NavNext( true, "f_" ) )
{
	$row = & $lAdmin->AddRow( $f_ID, $arRes );

	if($accessLevel >= "W")	
		$row->AddViewField( "ID", '<a href="sc.reviews_reviews_fields_edit.php?ID='.$f_ID.'&lang='.LANG.'"">'.$f_ID.'</a>' );
	else
		$row->AddViewField("ID", $f_ID);

	if($accessLevel >= "W")	
		$row->AddInputField("SORT", $f_SORT);
	else
		$row->AddViewField("SORT", $f_SORT);

	if($accessLevel >= "W")	
		$row->AddInputField("NAME", $f_NAME);
	else
		$row->AddViewField("NAME", $f_NAME);

	if($accessLevel >= "W")	
		$row->AddInputField("TITLE", $f_TITLE);
	else
		$row->AddViewField("TITLE", $f_TITLE);

	if($accessLevel >= "W")	
		$row->AddInputField("TYPE", $f_TYPE);
	else
		$row->AddViewField("TYPE", $f_TYPE);

	if($accessLevel >= "W")	
		$row->AddInputField("SETTINGS", $f_SETTINGS);
	else
		$row->AddViewField("SETTINGS", $f_SETTINGS);

	if($accessLevel >= "W")	
		$row->AddCheckField( "ACTIVE" );
	else
		$row->AddViewField("ACTIVE", ($f_ACTIVE == "Y" ? 
										GetMessage("SC_REVIEWS_FIELDS_POST_YES") : 
										GetMessage("SC_REVIEWS_FIELDS_POST_NO")));

	$arActions = Array ();
	if($accessLevel >= "W")
	{
		$arActions[] = array (
				"ICON" => "edit",
				"DEFAULT" => true,
				"TEXT" => GetMessage( "SC_REVIEWS_FIELDS_EDIT" ),
				"ACTION" => $lAdmin->ActionRedirect( "sc.reviews_reviews_fields_edit.php?ID=".$f_ID ) 
		);
	
		$arActions[] = array (
				"ICON" => "delete",
				"TEXT" => GetMessage( "SC_REVIEWS_FIELDS_DEL" ),
				"ACTION" => "if(confirm('".GetMessage( 'SC_REVIEWS_FIELDS_DEL_CONF' )."')) ".$lAdmin->ActionDoGroup( $f_ID, "delete" ) 
		);
	}
	
	$arActions[] = array (
			"SEPARATOR" => true 
	);
	
	if(is_set( $arActions[count( $arActions )-1], "SEPARATOR" ))
		unset( $arActions[count( $arActions )-1] );
	
	$row->AddActions( $arActions );
	unset( $f_ID );
	unset( $arActions );
}

$lAdmin->AddFooter( array (
		array (
				"title" => GetMessage( "SC_REVIEWS_FIELDS_LIST_SELECTED" ),
				"value" => $rsData->SelectedRowsCount() 
		),
		array (
				"counter" => true,
				"title" => GetMessage( "SC_REVIEWS_FIELDS_LIST_CHECKED" ),
				"value" => "0" 
		) 
) );

$arGroupActions = [];
if($accessLevel >= "W")
{
	$arGroupActions["delete"] = GetMessage("SC_REVIEWS_FIELDS_LIST_DELETE");
	$arGroupActions["activate"] = GetMessage("SC_REVIEWS_FIELDS_LIST_ACTIVATE");
	$arGroupActions["deactivate"] = GetMessage("SC_REVIEWS_FIELDS_LIST_DEACTIVATE");
}

$lAdmin->AddGroupActionTable($arGroupActions);

$arContextMenu = [];
if($accessLevel >= "W")
	$arContextMenu = array (
			array (
					"TEXT" => GetMessage( "SC_REVIEWS_FIELDS_POST_ADD_TEXT" ),
					"LINK" => "sc.reviews_reviews_fields_edit.php?lang=".LANG,
					"TITLE" => GetMessage( "SC_REVIEWS_FIELDS_POST_ADD_TITLE" ),
					"ICON" => "btn_new" 
			) 
	);

$lAdmin->AddAdminContextMenu($arContextMenu);

$lAdmin->CheckListMode();
$APPLICATION->SetTitle( GetMessage( "SC_REVIEWS_FIELDS_TITLE" ) );

require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter( $sTableID."_filter", array (
		GetMessage( "SC_REVIEWS_FIELDS_ID" ),
		GetMessage( "SC_REVIEWS_FIELDS_TITLE" ),
		GetMessage( "SC_REVIEWS_FIELDS_NAME" ),
		GetMessage( "SC_REVIEWS_FIELDS_TYPE" ),
		GetMessage( "SC_REVIEWS_FIELDS_ACTIVE" ) 
) );
?>

<form name="find_form" method="get"	action="<?echo $APPLICATION->GetCurPage();?>">
	<?$oFilter->Begin();?>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_FIELDS_ID")?>:</td>
		<td><input type="text" name="find_id" size="47"	value="<?echo htmlspecialchars($find_id)?>">
			<?unset( $find_id );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_FIELDS_TITLE")?>:</td>
		<td><input type="text" name="find_title" size="47" value="<?echo htmlspecialchars($find_title)?>">
			<?unset( $find_title );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_FIELDS_NAME")?>:</td>
		<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialchars($find_name)?>">
			<?unset( $find_name );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_FIELDS_TYPE")?>:</td>
		<td><input type="text" name="find_type" size="47" value="<?echo htmlspecialchars($find_type)?>">
			<?unset( $find_type );?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_FIELDS_ACTIVE")?>:</td>
		<td>
			<?
			$arr = array (
					"reference" => array (
							GetMessage( "SC_REVIEWS_FIELDS_POST_YES" ),
							GetMessage( "SC_REVIEWS_FIELDS_POST_NO" ) 
					),
					"reference_id" => array (
							"Y",
							"N" 
					) 
			);
			echo SelectBoxFromArray( "find_active", $arr, $find_active, "", "" );
			unset( $arr );
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
