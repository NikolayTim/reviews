<?
use SouthCoast\Reviews\Internals\ReviewsBansTable;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;

require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

if (!Loader::includeModule( 'iblock' ) || !Loader::includeModule( 'sc.reviews' ))
	die();
	
$accessLevel = $APPLICATION->GetGroupRight("sc.reviews");
if($accessLevel == "D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$banDaysOption = \Bitrix\Main\Config\Option::getRealValue('sc.reviews', 'REVIEWS_BAN_DAYS', 's1');

$aTabs = array(
		array(
				"DIV" => "scReviewsBanEdit",
				"TAB" => GetMessage("SC_REVIEWS_BAN_EDIT_TAB"),
				"ICON" => "main_user_edit",
				"TITLE" => GetMessage("SC_REVIEWS_BAN_EDIT_TAB_TITLE") 
		) 
);

$tabControl = new CAdminForm( "tabControl", $aTabs );
$ID = intval( $ID );

if ($ID > 0)
	$Result = ReviewsBansTable::getById( $ID )->fetch();

if ($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $accessLevel > "R" && check_bitrix_sessid())
{
	if ($ID > 0)
	{
		$arFields = Array(
				"DATE_CHANGE" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
				"DATE_TO" => new Type\DateTime( $DATE_TO ),
				"ID_USER" => $ID_USER,
				"IP" => $IP,
				"REASON" => $REASON,
				"ACTIVE" => ($ACTIVE != "Y" ? "N" : "Y"),
				"ID_MODERATOR" => $USER->GetID()
				);
		
		$result = ReviewsBansTable::update( $ID, $arFields );
		if (!$result->isSuccess())
			$errors = $result->getErrorMessages();
	}
	else 
	{
		$arFields = Array(
				"DATE_CREATION" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
				"DATE_CHANGE" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
				"DATE_TO" => new Type\DateTime( $DATE_TO ),
				"ID_USER" => $ID_USER,
				"IP" => $IP,
				"REASON" => $REASON,
				"ACTIVE" => ($ACTIVE != "Y" ? "N" : "Y"),
				"ID_MODERATOR" => $USER->GetID()
				);
		
		$result = ReviewsBansTable::add( $arFields );
		if (!$result->isSuccess())
			$errors = $result->getErrorMessages();
		else
			$ID = $result->getId();
	}

	if ($result->isSuccess())
	{
		if ($apply != "")
			LocalRedirect( "/bitrix/admin/sc.reviews_bans_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam() );
		else
			LocalRedirect( "/bitrix/admin/sc.reviews_bans_list.php?lang=" . LANG );
	}
}

$APPLICATION->SetTitle( ($ID > 0 ? GetMessage("SC_REVIEWS_BAN_EDIT_EDIT") . $ID : GetMessage("SC_REVIEWS_BAN_EDIT_ADD")));
require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
		array(
				"TEXT" => GetMessage("SC_REVIEWS_BAN_EDIT_LIST"),
				"TITLE" => GetMessage("SC_REVIEWS_BAN_EDIT_LIST_TITLE"),
				"LINK" => "sc.reviews_bans_list.php?lang=" . LANG,
				"ICON" => "btn_list" 
		) 
);

if ($ID > 0)
{
	$aMenu[] = array(
			"TEXT" => GetMessage("SC_REVIEWS_BAN_EDIT_DEL"),
			"TITLE" => GetMessage("SC_REVIEWS_BAN_EDIT_DEL_TITLE"),
			"LINK" => "javascript:if(confirm('" . GetMessage("SC_REVIEWS_BAN_EDIT_DEL_CONF") . "'))window.location='sc.reviews_bans_list.php?ID=" . $ID . "&action=delete&lang=" . LANG ."&" . bitrix_sessid_get() . "';",
			"ICON" => "btn_delete" 
	);
}

$context = new CAdminContextMenu( $aMenu );
$context->Show();

if ($_REQUEST["mess"] == "ok" && $ID > 0)
	CAdminMessage::ShowMessage( array(
			"MESSAGE" => GetMessage("SC_REVIEWS_BAN_EDIT_SAVED" ),
			"TYPE" => "OK" 
	) );
	
if (isset( $errors ) && !empty( $errors ))
{
	foreach ( $errors as $error )
		CAdminMessage::ShowMessage( array("MESSAGE" => $error ) );

	unset( $error );
	unset( $errors );
}

if(isset($Result["ID_MODERATOR"]))
{
	$Moderators = CUser::GetByID( $Result["ID_MODERATOR"] );
	if ($arItem = $Moderators->Fetch())
	{
		$Moderator = '[' . $arItem['ID'] . '] ' . $arItem['LAST_NAME'] . ' ' . $arItem['NAME'];
	}
	unset( $Moderators );
	unset( $arItem );
}
$tabControl->Begin( array("FORM_ACTION" => $APPLICATION->GetCurPage()) );
$tabControl->BeginNextFormTab(); 

$tabControl->AddViewField( 'ID', GetMessage("SC_REVIEWS_BAN_EDIT_ID" ), $ID, false );

$tabControl->AddCheckBoxField( "ACTIVE", GetMessage("SC_REVIEWS_BAN_EDIT_ACT"), false, "Y", ($Result['ACTIVE'] == "Y" || !isset( $Result['ACTIVE'] )) );
unset( $Result['ACTIVE'] );

$Users = array();
$Users["REFERENCE_ID"][] = 0; 
$Users["REFERENCE"][] = "[0] (" . GetMessage("SC_REVIEWS_BAN_EDIT_NOT_AUTHORIZED_USER") . ") " . GetMessage("SC_REVIEWS_BAN_EDIT_NOT_AUTHORIZED_USER");
$rsUsers = Bitrix\Main\UserTable::getList([
    'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME'],
    'filter' => ['ACTIVE' => 'Y'],
    'order' => ['ID' => 'ASC']
]);
while($arUser = $rsUsers->fetch())
{
    $Users["REFERENCE_ID"][] = $arUser["ID"];
    $Users["REFERENCE"][] = "[" . $arUser["ID"] . "] (" . $arUser["LOGIN"] . ") " . $arUser["NAME"] . " " . $arUser["LAST_NAME"];
}

$tabControl->BeginCustomField( "ID_USER", GetMessage("SC_REVIEWS_BAN_EDIT_ID_USER"), false );
?>
<tr id="tr_ID_USER">
	<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
	<td width="60%">
		<?=SelectBoxFromArray( 'ID_USER', $Users, $Result['ID_USER'], '', 'style="min-width:350px"', true, 'sc.reviews' );?>
	</td>
</tr>
<?
$tabControl->EndCustomField( "ID_USER" );

$tabControl->AddEditField( "BX_USER_ID", GetMessage("SC_REVIEWS_BAN_EDIT_BX_USER_ID"), false, array(
    "size" => 50,
    "maxlength" => 50
), htmlspecialcharsbx( $Result['BX_USER_ID'] ) );

$tabControl->AddEditField( "IP", GetMessage("SC_REVIEWS_BAN_EDIT_IP"), false, array(
		"size" => 15,
		"maxlength" => 15 
), htmlspecialcharsbx( $Result['IP'] ) );

if(isset($Result['DATE_CREATION']))
	$tabControl->AddViewField( 'DATE_CREATION', GetMessage("SC_REVIEWS_BAN_EDIT_DATE_CREATION"), new Type\DateTime( $Result['DATE_CREATION'] ), false );

if(isset($Result['DATE_CHANGE']))
	$tabControl->AddViewField( 'DATE_CHANGE', GetMessage("SC_REVIEWS_BAN_EDIT_DATE_CHANGE"), new Type\DateTime( $Result['DATE_CHANGE'] ), false );

$tabControl->BeginCustomField( "DATE_TO", GetMessage("SC_REVIEWS_BAN_EDIT_DATE_TO"), false );

if(!isset($Result['DATE_TO']))
	$Result['DATE_TO']=date('d.m.Y H:i:s',strtotime('+'.$banDaysOption.' day'));
?>

<tr id="tr_DATE_TO">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td><?echo CAdminCalendar::CalendarDate("DATE_TO", new Type\DateTime($Result['DATE_TO']), 19, true)?></td>
</tr>

<?
$tabControl->EndCustomField( "DATE_TO", '<input type="hidden" id="DATE_TO" name="DATE_TO" value="' . new Type\DateTime($Result['DATE_TO'])  . '">' );
$tabControl->BeginCustomField( "REASON", GetMessage('SC_REVIEWS_BAN_EDIT_REASON'), false );
?>

<tr id="tr_REASON">
	<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
	<td>
		<?
		$APPLICATION->IncludeComponent( "bitrix:main.post.form", "", Array(
				'BUTTONS' => array(),
				'PARSER' => array(),
				'PIN_EDITOR_PANEL' => 'N',
				'TEXT' => array(
						'SHOW' => 'Y',
						'VALUE' => $Result['REASON'],
						'NAME' => 'REASON' 
				) 
		) );
		?>

<style>
#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-top-bar-btn, #tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-top-bar-select,
	#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-fontsize-wrap {
	display: none;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-bold {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-italic {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-underline {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-strike {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-remove-format {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-top-bar-color {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-fontsize {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-ordered-list {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-unordered-list {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-align-left {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-quote {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-align-right {
	display: inline-block;
}

#tr_REASON .bxhtmled-iframe-cnt {
	overflow: hidden !important;
}
</style>


	</td>
</tr>

<?
$tabControl->EndCustomField( "REASON" );
if(isset($Result["ID_MODERATOR"]))
	$tabControl->AddViewField( "ID_MODERATOR", GetMessage("SC_REVIEWS_BAN_EDIT_MODERATED_BY"), $Moderator, false );

$tabControl->BeginCustomField( "HID", '', false );
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">

<?if($ID>0 && !$bCopy){?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?}?>
<?
$tabControl->EndCustomField( "HID" );
$arButtonsParams = array(
		"disabled" => $readOnly,
		"back_url" => "/bitrix/admin/sc.reviews_bans_list.php?lang=" . LANG
);
$tabControl->Buttons( $arButtonsParams );
$tabControl->Show();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>