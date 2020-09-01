<?
use SouthCoast\Reviews\Internals\ChecksReviewTable;
use Bitrix\Main\Loader;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!Loader::includeModule( 'sc.reviews' ))
    die();

$accessLevel = $APPLICATION->GetGroupRight( 'sc.reviews' );
if($accessLevel == "D")
    $APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$aTabs = array (
    array (
        "DIV" => "scReviewsCheckEdit",
        "TAB" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_TAB_CONDITION" ),
        "ICON" => "main_user_edit",
        "TITLE" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_TAB_CONDITION_TITLE" )
    )
);
$tabControl = new CAdminForm( "tabControl", $aTabs );

$ID = intval( $ID );
if($ID > 0)
{
    $Result = ChecksReviewTable::getById( $ID );
    $Result = $Result->fetch();
}

if($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $accessLevel == "W" && check_bitrix_sessid())
{
    $arFields = Array (
        "NAME" => $NAME,
        "VALUE" => $VALUE,
        "PATTERN" => $PATTERN,
        "RESULT" => $RESULT
    );

    if($ID > 0)
    {
        $result = ChecksReviewTable::update( $ID, $arFields );
        if(!$result->isSuccess())
            $errors = $result->getErrorMessages();
    }
    else
    {
        $result = ChecksReviewTable::add( $arFields );
        if($result->isSuccess())
            $ID = $result->getId();
        else
            $errors = $result->getErrorMessages();
    }

    if(!$errors)
    {
        if($apply != "")
            LocalRedirect( "/bitrix/admin/sc.reviews_reviews_checks_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam() );
        else
            LocalRedirect( "/bitrix/admin/sc.reviews_reviews_checks_list.php?lang=".LANG );
    }
}

$APPLICATION->SetTitle( ($ID > 0 ? GetMessage( "SC_REVIEWS_CHECKS_EDIT_EDIT" ).$ID : GetMessage( "SC_REVIEWS_CHECKS_EDIT_ADD" )) );
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array (
    array (
        "TEXT" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_LIST" ),
        "TITLE" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_LIST_TITLE" ),
        "LINK" => "sc.reviews_reviews_checks_list.php?lang=".LANG,
        "ICON" => "btn_list"
    )
);
if($ID > 0)
{
    $aMenu[] = array (
        "SEPARATOR" => "Y"
    );
    $aMenu[] = array (
        "TEXT" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_ADD" ),
        "TITLE" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_ADD" ),
        "LINK" => "sc.reviews_reviews_checks_edit.php?lang=".LANG,
        "ICON" => "btn_new"
    );
    $aMenu[] = array (
        "TEXT" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_DEL" ),
        "TITLE" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_DEL_TITLE" ),
        "LINK" => "javascript:if(confirm('".GetMessage( "SC_REVIEWS_CHECKS_EDIT_DEL_CONF" )."'))window.location='sc.reviews_reviews_checks_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
        "ICON" => "btn_delete"
    );
}

$context = new CAdminContextMenu( $aMenu );
$context->Show();

if($_REQUEST["mess"] == "ok" && $ID > 0)
    CAdminMessage::ShowMessage( array (
        "MESSAGE" => GetMessage( "SC_REVIEWS_CHECKS_EDIT_SAVED" ),
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
    unset( $errors );
}

$tabControl->Begin( array("FORM_ACTION" => $APPLICATION->GetCurPage()) );
$tabControl->BeginNextFormTab();
$tabControl->AddViewField( 'ID', GetMessage( "SC_REVIEWS_CHECKS_EDIT_ID" ), $ID, false );
$tabControl->AddEditField( 'NAME', GetMessage( "SC_REVIEWS_CHECKS_EDIT_NAME" ), true, array (), htmlspecialcharsbx( $Result['NAME'] ) );
$tabControl->AddEditField( 'VALUE', GetMessage( "SC_REVIEWS_CHECKS_EDIT_VALUE" ), true, array (), htmlspecialcharsbx( $Result['VALUE'] ) );
$tabControl->AddEditField( 'PATTERN', GetMessage( "SC_REVIEWS_CHECKS_EDIT_PATTERN" ), true, array (), htmlspecialcharsbx( $Result['PATTERN'] ) );
$tabControl->AddEditField( 'RESULT', GetMessage( "SC_REVIEWS_CHECKS_EDIT_RESULT" ), true, array (), htmlspecialcharsbx( $Result['RESULT'] ) );

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
    "back_url" => "/bitrix/admin/sc.reviews_reviews_checks_list.php?lang=".LANG
);
$tabControl->Buttons( $arButtonsParams );
$tabControl->Show();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");