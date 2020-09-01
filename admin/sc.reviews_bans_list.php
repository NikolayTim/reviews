<?
use SouthCoast\Reviews\Internals\ReviewsBansTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$id_module = 'sc.reviews';

if(!Loader::includeModule( $id_module )||!Loader::includeModule( 'iblock' ))
	return false;

$accessLevel = $APPLICATION->GetGroupRight($id_module);
if($accessLevel == "D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$sTableID = "sc_reviews_bans";
$oSort = new CAdminSorting( $sTableID, "DATE_CHANGE", "desc" );
$lAdmin = new CAdminList( $sTableID, $oSort );

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach( $FilterArr as $f )
		global $$f;
	return count( $lAdmin->arFilterErrors )==0;
}

$FilterArr = Array (
		"find_id",
		"find_id_user",
		"find_active" 
);

$lAdmin->InitFilter( $FilterArr );
$arFilter = array ();

if(CheckFilter())
{
	if($find_id != '')
		$arFilter['ID'] = $find_id;
	
	$arFilter['ID_USER'] = $find_id_user;
	$arFilter['ACTIVE'] = $find_active;
	
	if(empty( $arFilter['ID'] ))
		unset( $arFilter['ID'] );
	if(empty( $arFilter['ID_USER'] ))
		unset( $arFilter['ID_USER'] );
	if(empty( $arFilter['ACTIVE'] ))
		unset( $arFilter['ACTIVE'] );
}

if($lAdmin->EditAction() && $accessLevel > "R")
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
			
			$result = ReviewsBansTable::update( $ID, $arData );
			unset( $arData );

			if(!$result->isSuccess())
				$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_BANS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_BANS_NOT_FOUND"), $ID);

			unset( $result );
		}
		else
			$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_BANS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_BANS_NOT_FOUND"), $ID);
	}
}

if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = ReviewsBansTable::getList([
				'select' => ['ID',],
				'filter' => $arFilter,
				'order' => [$by => $order] 
		]);
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
				$result = ReviewsBansTable::delete( $ID );
				if(!$result->isSuccess())
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_BANS_DEL_ERROR")." ".GetMessage("SC_REVIEWS_BANS_NOT_FOUND"), $ID);

				unset( $result );
				break;
			case "activate" :
			case "deactivate" :
				if($ID>0)
				{
					$arFields["ACTIVE"] = ($_REQUEST['action']=="activate" ? "Y" : "N");
					$result = ReviewsBansTable::update( $ID, ['ACTIVE' => $arFields["ACTIVE"]]);
					if(!$result->isSuccess())
						$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_BANS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_BANS_NOT_FOUND"), $ID);

					unset( $result );
				}
				else
					$lAdmin->AddGroupError(GetMessage("SC_REVIEWS_BANS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_BANS_NOT_FOUND"), $ID);

				break;
		}
	}
}

$rsData = ReviewsBansTable::getList( array(
		'select' => array('ID','ID_USER','IP','DATE_CREATION','DATE_CHANGE','DATE_TO','ACTIVE', 'BX_USER_ID'),
		'filter' => $arFilter,
		'order' => array($by => $order),
) );

$rsData = new CAdminResult( $rsData, $sTableID );
$rsData->NavStart();
$lAdmin->NavText( $rsData->GetNavPrint( GetMessage("SC_REVIEWS_BANS_NAV") ) );
$lAdmin->AddHeaders( array (
		array (
				"id" => "ID",
				"content" => GetMessage("SC_REVIEWS_BANS_TABLE_ID"),
				"sort" => "ID",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "ID_USER",
				"content" => GetMessage("SC_REVIEWS_BANS_TABLE_ID_USER"),
				"sort" => "ID_USER",
				"align" => "right",
				"default" => true 
		),
        array (
                "id" => "BX_USER_ID",
                "content" => GetMessage("SC_REVIEWS_BANS_TABLE_BX_USER_ID"),
                "sort" => "BX_USER_ID",
                "align" => "right",
                "default" => true
        ),
        array (
				"id" => "IP",
				"content" => GetMessage("SC_REVIEWS_BANS_TABLE_IP"),
				"sort" => "IP",
				"align" => "right",
				"default" => true
		),
		array (
				"id" => "DATE_CREATION",
				"content" => GetMessage("SC_REVIEWS_BANS_TABLE_DATE_CREATION"),
				"sort" => "DATE_CREATION",
				"default" => true 
		),
		array (
				"id" => "DATE_CHANGE",
				"content" => GetMessage("SC_REVIEWS_BANS_TABLE_DATE_CHANGE"),
				"sort" => "DATE_CHANGE",
				"default" => true
		),
		array (
				"id" => "DATE_TO",
				"content" => GetMessage("SC_REVIEWS_BANS_TABLE_DATE_TO"),
				"sort" => "DATE_TO",
				"default" => true
		),
		array (
				"id" => "ACTIVE",
				"content" => GetMessage("SC_REVIEWS_BANS_TABLE_ACTIVE"),
				"sort" => "ACTIVE",
				"default" => true 
		) 
) );
while( $arRes = $rsData->NavNext( true, "f_" ) )
{
	$row = & $lAdmin->AddRow( $f_ID, $arRes );

	if($f_ID_USER > 0)
	{
		$Users = CUser::GetByID( $f_ID_USER );
		if($arItem = $Users->Fetch())
			$row->AddViewField( "ID_USER", '['.$arItem['ID'].'] '.$arItem['LAST_NAME'].' '.$arItem['NAME'] );
	}
	elseif($f_ID_USER==0)
		$row->AddViewField( "ID_USER", '' );

    $row->AddViewField( "BX_USER_ID", $f_BX_USER_ID );

	$row->AddViewField( "IP", $f_IP );
	unset( $f_IP );
	
	$row->AddViewField( "DATE_CREATION", $f_DATE_CREATION );
	unset( $f_DATE_CREATION );
	
	$row->AddViewField( "DATE_CHANGE", $f_DATE_CHANGE );
	unset( $f_DATE_CHANGE );
	
	$row->AddViewField( "DATE_TO", $f_DATE_TO );
	unset( $f_DATE_TO );
	
	$row->AddViewField( "ACTIVE", ($f_ACTIVE == "Y" ? "Да" : "Нет") );

	$arActions = Array ();
	
	if($accessLevel > "R")
	{
		$arActions[] = array (
				"ICON" => "edit",
				"DEFAULT" => true,
				"TEXT" => GetMessage("SC_REVIEWS_BANS_EDIT"),
				"ACTION" => $lAdmin->ActionRedirect( "sc.reviews_bans_edit.php?ID=".$f_ID ) 
		);
	
		$arActions[] = array (
				"ICON" => "delete",
				"TEXT" => GetMessage("SC_REVIEWS_BANS_DEL"),
				"ACTION" => "if(confirm('".GetMessage('SC_REVIEWS_BANS_DEL_CONF')."')) ".$lAdmin->ActionDoGroup( $f_ID, "delete" ) 
		);
	}
	$arActions[] = ["SEPARATOR" => true];
	
	if(is_set( $arActions[count( $arActions )-1], "SEPARATOR" ))
		unset( $arActions[count( $arActions )-1] );
	
	$row->AddActions( $arActions );
	unset( $f_ID );
	unset( $arActions );
}

$lAdmin->AddFooter( array (
		array (
				"title" => GetMessage("SC_REVIEWS_BANS_LIST_SELECTED"),
				"value" => $rsData->SelectedRowsCount() 
		),
		array (
				"counter" => true,
				"title" => GetMessage("SC_REVIEWS_BANS_LIST_CHECKED"),
				"value" => "0" 
		) 
) );

$Moderation = [];
if($accessLevel > "R")
{
	$Moderation = array (
			"delete" => GetMessage("SC_REVIEWS_BANS_LIST_DELETE"),
			"activate" => GetMessage("SC_REVIEWS_BANS_LIST_ACTIVATE"),
			"deactivate" => GetMessage("SC_REVIEWS_BANS_LIST_DEACTIVATE") 
	);
}
$lAdmin->AddGroupActionTable( $Moderation );
unset($Moderation);

$aContext = [];
if($accessLevel > "R")
{
	$aContext = array(
		array(
		"TEXT" => GetMessage("SC_REVIEWS_BANS_ADD_TEXT"),
		"LINK" => "sc.reviews_bans_edit.php?lang=".LANG,
		"TITLE" => GetMessage("SC_REVIEWS_BANS_ADD_TITLE"),
		"ICON" => "btn_new",
		),   
	);
}
$lAdmin->AddAdminContextMenu( $aContext );
unset( $aContext );

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SC_REVIEWS_BANS_TITLE"));
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$Moderation = array (
		GetMessage("SC_REVIEWS_BANS_ID"),
		GetMessage("SC_REVIEWS_BANS_ID_USER"),
		GetMessage("SC_REVIEWS_BANS_ACTIVE") 
);
$oFilter = new CAdminFilter($sTableID."_filter", $Moderation);
?>
<form name="find_form" method="get"	action="<?echo $APPLICATION->GetCurPage();?>">
	<?$oFilter->Begin();?>
	<tr>
		<td><?=GetMessage("SC_REVIEWS_BANS_ID")?>:</td>
		<td><input type="text" name="find_id" size="47"	value="<?echo htmlspecialchars($find_id)?>">
			<?unset( $find_id );?>
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("SC_REVIEWS_BANS_ID_USER")?>:</td>
		<td><input type="text" name="find_id_user" size="47"
			value="<?echo htmlspecialchars($find_id_user)?>">
			<?unset( $find_id_user );?>
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("SC_REVIEWS_BANS_ACTIVE")?>:</td>
		<td>
			<?
			$arr = array (
					"reference" => array (
							GetMessage("SC_REVIEWS_BANS_POST_YES" ),
							GetMessage("SC_REVIEWS_BANS_POST_NO" ) 
					),
					"reference_id" => array ("Y", "N" ) 
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
?>