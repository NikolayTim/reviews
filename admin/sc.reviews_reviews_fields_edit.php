<?
use SouthCoast\Reviews\Internals\ReviewsFieldsTable;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!Loader::includeModule( 'sc.reviews' ))
	die();

$accessLevel = $APPLICATION->GetGroupRight( 'sc.reviews' );
if($accessLevel == "D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$aTabs = array (
		array (
				"DIV" => "scReviewsFieldEdit",
				"TAB" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_TAB_CONDITION" ),
				"ICON" => "main_user_edit",
				"TITLE" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_TAB_CONDITION_TITLE" ) 
		) 
);
$tabControl = new CAdminForm( "tabControl", $aTabs );
$ID = intval( $ID );
if($ID>0)
{
	$Result = ReviewsFieldsTable::getById( $ID );
	$Result = $Result->fetch();
}
if($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $accessLevel == "W" && check_bitrix_sessid())
{
	$arFields = Array (
			"SORT" => $SORT,
			"NAME" => $NAME,
			"TITLE" => $TITLE,
			"TYPE" => $TYPE,
			"SETTINGS" => $SETTINGS,
			"ACTIVE" => ($ACTIVE!="Y" ? "N" : "Y") 
	);
	if($ID>0)
	{
		$result = ReviewsFieldsTable::update( $ID, $arFields );
		unset( $arFields );
		if(!$result->isSuccess())
		{
			$errors = $result->getErrorMessages();
			$res = false;
		}
		else
			$res = true;
	}
	else
	{
		$result = ReviewsFieldsTable::add( $arFields );
		if($result->isSuccess())
		{
			$ID = $result->getId();
			$res = true;
		}
		else
		{
			$errors = $result->getErrorMessages();
			$res = false;
		}
	}
	
	unset( $result );
	
	if($res)
	{
		if($apply != "")
			LocalRedirect( "/bitrix/admin/sc.reviews_reviews_fields_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam() );
		else
			LocalRedirect( "/bitrix/admin/sc.reviews_reviews_fields_list.php?lang=".LANG );
	}
}

$APPLICATION->SetTitle( ($ID>0 ? GetMessage( "SC_REVIEWS_FIELDS_EDIT_EDIT" ).$ID : GetMessage( "SC_REVIEWS_FIELDS_EDIT_ADD" )) );
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array (
		array (
				"TEXT" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_LIST" ),
				"TITLE" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_LIST_TITLE" ),
				"LINK" => "sc.reviews_reviews_fields_list.php?lang=".LANG,
				"ICON" => "btn_list" 
		) 
);
if($ID>0)
{
	$aMenu[] = array (
			"SEPARATOR" => "Y" 
	);
	$aMenu[] = array (
			"TEXT" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_ADD" ),
			"TITLE" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_ADD_TITLE" ),
			"LINK" => "sc.reviews_reviews_fields_edit.php?lang=".LANG,
			"ICON" => "btn_new" 
	);
	$aMenu[] = array (
			"TEXT" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_DEL" ),
			"TITLE" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_DEL_TITLE" ),
			"LINK" => "javascript:if(confirm('".GetMessage( "SC_REVIEWS_FIELDS_EDIT_DEL_CONF" )."'))window.location='sc.reviews_reviews_fields_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
			"ICON" => "btn_delete" 
	);
}

$context = new CAdminContextMenu( $aMenu );
unset( $aMenu );

$context->Show();
unset( $context );

if($_REQUEST["mess"] == "ok" && $ID > 0)
	CAdminMessage::ShowMessage( array (
			"MESSAGE" => GetMessage( "SC_REVIEWS_FIELDS_EDIT_SAVED" ),
			"TYPE" => "OK" 
	) );
	
if(isset( $errors )&&!empty( $errors ))
{
	foreach( $errors as $error )
	{
		CAdminMessage::ShowMessage( array (
				"MESSAGE" => $error 
		) );
	}
	unset( $error );
	unset( $errors );
}

$arTypes["REFERENCE_ID"] = array('textbox');
$arTypes["REFERENCE"] = array('textbox');

if(!isset( $Result['SORT'] )||empty( $Result['SORT'] ))
	$Result['SORT'] = 100;

$tabControl->Begin( array("FORM_ACTION" => $APPLICATION->GetCurPage()) );
$tabControl->BeginNextFormTab(); 

$tabControl->AddViewField( 'ID', GetMessage( "SC_REVIEWS_FIELDS_EDIT_ID" ), $ID, false );

$tabControl->AddCheckBoxField( "ACTIVE", GetMessage( "SC_REVIEWS_FIELDS_EDIT_ACT" ), false, "Y", ($Result['ACTIVE']=="Y"||!isset( $Result['ACTIVE'] )) );
unset( $Result['ACTIVE'] );

$tabControl->AddEditField( 'SORT', GetMessage( "SC_REVIEWS_FIELDS_EDIT_SORT" ), true, array (), htmlspecialcharsbx( $Result['SORT'] ) );
unset( $Result['SORT'] );

$tabControl->AddEditField( 'NAME', GetMessage( "SC_REVIEWS_FIELDS_EDIT_NAME" ), true, array (), htmlspecialcharsbx( $Result['NAME'] ) );
unset( $Result['NAME'] );

$tabControl->AddEditField( 'TITLE', GetMessage( "SC_REVIEWS_FIELDS_EDIT_TITLE" ), true, array (), htmlspecialcharsbx( $Result['TITLE'] ) );
unset( $Result['TITLE'] );

$tabControl->BeginCustomField( "TYPE", GetMessage( 'SC_REVIEWS_FIELDS_EDIT_TYPE' ), false );
?>
<tr id="tr_TYPE">
	<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
	<td>
		<?=SelectBoxFromArray('TYPE', $arTypes, $Result['TYPE'], '', 'style="min-width:350px"', true, '');?>
	</td>
</tr>
<?
$tabControl->EndCustomField( "TYPE" );
unset( $Result['TYPE'] );

$tabControl->AddTextField( 'SETTINGS', GetMessage( "SC_REVIEWS_FIELDS_EDIT_SETTINGS" ), $Result['SETTINGS'], false );
unset( $Result['SETTINGS'] );

$tabControl->BeginCustomField( "HID", '', false );
?>

<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>

<?
$tabControl->EndCustomField( "HID" );
$arButtonsParams = array (
		"disabled" => $readOnly,
		"back_url" => "/bitrix/admin/sc.reviews_reviews_fields_list.php?lang=".LANG 
);
$tabControl->Buttons( $arButtonsParams );
$tabControl->Show();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");