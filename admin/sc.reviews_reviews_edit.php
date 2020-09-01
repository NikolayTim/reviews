<?php
use SouthCoast\Reviews\Internals\ReviewsTable,
    SouthCoast\Reviews\Internals\ReviewsFieldsTable,
    SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CJSCore::Init(array('jquery'));

if(!Loader::includeModule( 'iblock' ) || !Loader::includeModule( 'sc.reviews' ))
    die();

$accessLevel = $APPLICATION->GetGroupRight('sc.reviews');
if($accessLevel == "D")
    $APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$aTabs = array (
    array (
        "DIV" => "edit1",
        "TAB" => GetMessage("SC_REVIEWS_ELEMENT_EDIT_TAB_CONDITION"),
        "ICON" => "main_user_edit",
        "TITLE" => GetMessage("SC_REVIEWS_ELEMENT_EDIT_TAB_CONDITION_TITLE")
    )
);
$tabControl = new CAdminForm( "tabControl", $aTabs );
$ID = intval( $ID );

$arRuntime = [];
$arUserFields = [];
$arUserFieldsID = [];
$arUserFieldSelect = [];

$rsUserFields = ReviewsFieldsTable::getList([
    'select' => ['ID', 'NAME', 'TITLE'],
    'filter' => ['ACTIVE' => 'Y']
]);
while($arUserField = $rsUserFields->fetch())
{
    $arRuntime[] = new Entity\ReferenceField(
        $arUserField["NAME"],
        'SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable',
        array("=this.ID" => "ref.REVIEW_ID", "ref.FIELD_ID" => new Bitrix\Main\DB\SqlExpression('?', $arUserField["ID"]))
    );

    $arUserFields[$arUserField["NAME"]."_VAL"] = $arUserField["TITLE"];
    $arUserFieldsID[$arUserField["NAME"]."_VAL"] = $arUserField["ID"];
    $arUserFieldSelect[$arUserField["NAME"]."_VAL"] = $arUserField["NAME"].".VALUE";
}

if($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $accessLevel >= "V" && check_bitrix_sessid())
{
    $arFile = [];
    $arIDFiles = [];
    if(isset($_REQUEST["FILES"]) && count($_REQUEST["FILES"]) > 0)
    {
        foreach($_REQUEST["FILES"] as $keyFile => $arFl)
        {
            if(is_array($arFl))
            {
                $arFile = $arFl;
                $arFile["tmp_name"] = $_SERVER["DOCUMENT_ROOT"] . "/upload/tmp" . $arFile["tmp_name"];
                $arFile["old_file"] = "";
                $arFile["del"] = "Y"; 
                $arFile["MODULE_ID"] = 'iblock';

                $fileID = CFile::SaveFile($arFile, "iblock");
                if(intval($fileID) > 0)
                    $arIDFiles[$keyFile] = $fileID;
            }
            else 
            {
                $arIDFiles[$keyFile] = $arFl;
            }
        }
    }

    foreach($_REQUEST["FILES_del"] as $keyDel => $valDel)
        if($valDel == "Y")
            unset($arIDFiles[$keyDel]);

    $arFields = Array (
        "ACTIVE" => ($ACTIVE!="Y" ? "N" : "Y"),
        "RATING" => $RATING,
        "DATE_CREATION" => new Type\DateTime( $DATE_CREATION ),
        "DATE_CHANGE" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
        "TITLE" => $TITLE,
        "PLUS" => $PLUS,
        "MINUS" => $MINUS,
        "ANSWER" => $ANSWER,
        "LIKES" => intval($LIKES),
        "DISLIKES" => intval($DISLIKES),
        "RECOMMENDATED" => ($RECOMMENDATED != "Y" ? "N" : "Y"),
        "MULTIMEDIA" => $_REQUEST["MULTIMEDIA"], 
        "FILES" => serialize($arIDFiles), 
        "IP_USER" => $_REQUEST['IP_USER'],
        "BX_USER_ID" => $_REQUEST['BX_USER_ID']
    );

    if($ID > 0) 
    {
        $result = ReviewsTable::update( $ID, $arFields );
        if(!$result->isSuccess())
            $errors = $result->getErrorMessages();
        else
        {
            $rsResUserFieldValues = ReviewsFieldsValuesTable::getList( array (
                'select' => ["VALUE", "FIELD_ID", "ID"],
                'filter' => ["REVIEW_ID" => $ID, "FIELD_ID" => $arUserFieldsID],
            ) );
            while($arResUserFieldValue = $rsResUserFieldValues->fetch())
            {
                $key = array_search($arResUserFieldValue["FIELD_ID"], $arUserFieldsID);
                if($key)
                    $arResUserFieldValues[$key] = [ "VALUE" => $arResUserFieldValue["VALUE"],
                                                    "VALUE_ID" => $arResUserFieldValue["ID"]];
            }

            foreach($arUserFields as $codeField => $nameField)
            {
                if($_REQUEST[$codeField] != $arResUserFieldValues[$codeField]["VALUE"])
                {
                    if(isset($_REQUEST[$codeField]) && strlen($_REQUEST[$codeField]) > 0)
                    {
                        $arUserFieldValues = [  "VALUE" => $_REQUEST[$codeField],
                                                "REVIEW_ID" => $ID,
                                                "FIELD_ID" => $arUserFieldsID[$codeField]];

                        if(array_key_exists($codeField, $arResUserFieldValues)) 
                        {
                            $resultOperation = ReviewsFieldsValuesTable::update($arResUserFieldValues[$codeField]["VALUE_ID"], $arUserFieldValues);
                            if (!$resultOperation->isSuccess())
                                $errors = $resultOperation->getErrorMessages();
                        }
                        else
                        {
                            $resultOperation = ReviewsFieldsValuesTable::add($arUserFieldValues);
                            if (!$resultOperation->isSuccess())
                                $errors = $resultOperation->getErrorMessages();
                        }

                        unset($arUserFieldValues);
                    }
                    else 
                    {
                        $resultOperation = ReviewsFieldsValuesTable::delete($arResUserFieldValues[$codeField]["VALUE_ID"]);
                        if (!$resultOperation->isSuccess())
                            $errors = $resultOperation->getErrorMessages();
                    }
                }
            }

            if(isset($errors) && count($errors) > 0)
                $errors[] = "Пользовательские поля частично обновлены!";

        }
    }
    else 
    {
        $arFields["ID_ELEMENT"] = $_REQUEST["ID_ELEMENT"];
        $arFields["ID_USER"] = $USER->GetID();
        $arFields["XML_ID_ELEMENT"] = $_REQUEST["ID_ELEMENT"];

        $result = ReviewsTable::add($arFields);
        if(!$result->isSuccess())
            $errors = $result->getErrorMessages();
        else
        {
            $ID = $result->getId();

            foreach($arUserFields as $codeField => $nameField)
            {
                if(isset($_REQUEST[$codeField]) && strlen($_REQUEST[$codeField]) > 0)
                {
                    $arUserFieldValues = [  "VALUE" => $_REQUEST[$codeField],
                                            "REVIEW_ID" => $ID,
                                            "FIELD_ID" => $arUserFieldsID[$codeField]];

                    $resultOperation = ReviewsFieldsValuesTable::add($arUserFieldValues);
                    if (!$resultOperation->isSuccess())
                        $errors = $resultOperation->getErrorMessages();

                    unset($arUserFieldValues);
                }
            }
        }
    }

    if(!isset($errors) || count($errors) == 0)
    {
        if ($apply != "")
            LocalRedirect("/bitrix/admin/sc.reviews_reviews_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
        else
            LocalRedirect("/bitrix/admin/sc.reviews_reviews_list.php?lang=" . LANG);
    }
}

$arReviewSelect = [];
$arReviewFields = ReviewsTable::getMap();
foreach($arReviewFields as $arFld)
{
    $curNameField = $arFld->getName();
    if ($curNameField != "USER")
        $arReviewSelect[] = $curNameField;
}

if($ID > 0)
{
    $rsRes = ReviewsTable::getList( array (
        'select' => array_merge($arReviewSelect, $arUserFieldSelect),
        'filter' => ["ID" => $ID],
        'order' => array ("ID" => "desc"),
        'runtime' => $arRuntime
    ) );
    $arRes = $rsRes->fetch();
    $arFiles = unserialize($arRes["FILES"]);
}

$strUserData = "";
if(isset($arRes["ID_USER"]) && intval($arRes["ID_USER"]) > 0)
{
    $arUserData = Bitrix\Main\UserTable::getList([
        'select' => ["LAST_NAME", "NAME"],
        'filter' => ["ID" => $arRes["ID_USER"]]
    ])->fetch();
    $strUserData = '['.$arRes['ID_USER'].'] '.$arUserData['LAST_NAME'].' '.$arUserData['NAME'];
}
else
    $strUserData = '['.$USER->GetID().'] '.$USER->GetLastName().' '.$USER->GetFirstName();

$strModeratorData = "";
if(isset($arRes["MODERATED_BY"]) && intval($arRes["MODERATED_BY"]) > 0)
{
    if ($arRes["MODERATED_BY"] == $arRes["ID_USER"])
        $strModeratorData = $strUserData;
    else
    {
        $arModeratorData = Bitrix\Main\UserTable::getList([
            'select' => ["LAST_NAME", "NAME"],
            'filter' => ["ID" => $arRes["MODERATED_BY"]]
        ])->fetch();
        $strModeratorData = '['.$arRes['MODERATED_BY'].'] '.$arModeratorData['LAST_NAME'].' '.$arModeratorData['NAME'];
    }
}

$SITE_ID = "s1"; 

$rsResEl = CIBlockElement::GetByID( $arRes['ID_ELEMENT'] );
if($arResEl = $rsResEl->GetNext())
{
    $SITE_ID = $arResEl['LID'];
    $elementName = '['.$arResEl['ID'].'] '.$arResEl['NAME'];
    $elementLink = SITE_SERVER_NAME.$arResEl['DETAIL_PAGE_URL'];
    $elementLinkAdmin = 'iblock_element_edit.php?IBLOCK_ID='.htmlspecialcharsbx($arResEl['IBLOCK_ID']).
        '&type='.htmlspecialcharsbx( $arResEl['IBLOCK_TYPE_ID'] ).'&ID='.htmlspecialcharsbx( $arRes['ID_ELEMENT'] ).
        '&lang='.htmlspecialcharsbx( LANG ).'&find_section_section='.htmlspecialcharsbx($arResEl['IBLOCK_SECTION_ID']);
}

$APPLICATION->SetTitle( ($ID > 0 ? GetMessage("SC_REVIEWS_ELEMENT_EDIT_EDIT").$ID : GetMessage("SC_REVIEWS_ELEMENT_EDIT_ADD")) );
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/admin_tools.php");

$aMenu = array (
    array (
        "TEXT" => GetMessage("SC_REVIEWS_ELEMENT_EDIT_LIST"),
        "TITLE" => GetMessage("SC_REVIEWS_ELEMENT_EDIT_LIST_TITLE"),
        "LINK" => "sc.reviews_reviews_list.php?lang=".LANG,
        "ICON" => "btn_list"
    )
);

if($ID > 0)
{
    $aMenu[] = array (
        "TEXT" => GetMessage("SC_REVIEWS_ELEMENT_EDIT_DEL"),
        "TITLE" => GetMessage("SC_REVIEWS_ELEMENT_EDIT_DEL_TITLE"),
        "LINK" => "javascript:if(confirm('".GetMessage("SC_REVIEWS_ELEMENT_EDIT_DEL_CONF")."'))window.location='sc.reviews_reviews_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
        "ICON" => "btn_delete"
    );
}
$context = new CAdminContextMenu( $aMenu );
$context->Show();

if($_REQUEST["mess"] == "ok" && $ID > 0)
    CAdminMessage::ShowMessage( array (
        "MESSAGE" => GetMessage("SC_REVIEWS_ELEMENT_EDIT_SAVED"),
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
?>

<style>
    .bxhtmled-top-bar-wrap
    {
        display:none;
    }
</style>

<?
$tabControl->Begin( array ("FORM_ACTION" => $APPLICATION->GetCurPage()) );
$tabControl->BeginNextFormTab();

$tabControl->AddViewField( 'ID', GetMessage("SC_REVIEWS_ELEMENT_EDIT_ID"), $ID, true );
$tabControl->AddCheckBoxField( "ACTIVE", GetMessage("SC_REVIEWS_ELEMENT_EDIT_ACT"), true, ["Y", "N"],
    ($arRes['ACTIVE'] == "Y" || !isset($arRes['ACTIVE'])));

if($ID > 0) 
    $tabControl->AddViewField( "MODERATED", GetMessage("SC_REVIEWS_ELEMENT_EDIT_MODERATED"),
        ($arRes["MODERATED"] == "N" ? GetMessage("SC_REVIEWS_ELEMENT_EDIT_NO") :
        GetMessage("SC_REVIEWS_ELEMENT_EDIT_YES")), true);
else 
    $tabControl->AddViewField( "MODERATED", GetMessage("SC_REVIEWS_ELEMENT_EDIT_MODERATED"),
        GetMessage("SC_REVIEWS_ELEMENT_EDIT_NO"), true);

$tabControl->AddViewField( "ID_ELEMENT", GetMessage("SC_REVIEWS_ELEMENT_EDIT_ID_ELEMENT" ), $elementName, true );

if($ID > 0) 
{
    $tabControl->BeginCustomField("ID_ELEMENT_LINK", "", true);
    ?>
    <tr id="tr_ID_ELEMENT_LINK">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td><a href="<?= $elementLink ?>" target="_blank"><?= GetMessage("SC_REVIEWS_ELEMENT_EDIT_LINK") ?></a>
            <a href="<?= $elementLinkAdmin ?>"
               target="_blank"><?= GetMessage("SC_REVIEWS_ELEMENT_EDIT_LINK_ADMIN") ?></a>
            <input name="ID_ELEMENT" type="hidden" value="<?= $arRes["ID_ELEMENT"] ?>"></td>
    </tr>
    <?
    $tabControl->EndCustomField("ID_ELEMENT_LINK");
}
else 
{
    $arElements = array();
    $rsElements = \Bitrix\Iblock\ElementTable::getList([
        'select' => ['ID', 'NAME'],
        'filter' => ['ACTIVE' => 'Y', 'IBLOCK_ID' => 22],
        'order' => ['ID' => 'asc']
        ]);
    while($arElement = $rsElements->fetch())
    {
        $arElements["REFERENCE_ID"][] = $arElement["ID"];
        $arElements["REFERENCE"][] = "[" . $arElement["ID"] . "] " . $arElement["NAME"];
    }

    $tabControl->BeginCustomField( "ID_ELEMENT", GetMessage("SC_REVIEWS_ELEMENT_EDIT_ID_ELEMENT" ), false );
    ?>
    <tr id="tr_ID_ELEMENT">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td width="60%">
            <?=SelectBoxFromArray( 'ID_ELEMENT', $arElements, '', '', 'style="min-width:350px"', true, 'sc.reviews' );?>
        </td>
    </tr>
    <?
    $tabControl->EndCustomField( "ID_ELEMENT" );
}

$tabControl->AddViewField( "XML_ID_ELEMENT", GetMessage("SC_REVIEWS_ELEMENT_EDIT_XML_ID_ELEMENT"), $arRes['XML_ID_ELEMENT'], true );
$tabControl->AddViewField( "ID_USER", GetMessage("SC_REVIEWS_ELEMENT_EDIT_ID_USER"), $strUserData, true );

if(!isset($arRes['IP_USER']) || strlen($arRes['IP_USER']) <= 0)
    $arRes['IP_USER'] = $_SERVER['REMOTE_ADDR'];

$tabControl->AddViewField( "IP_USER", GetMessage("SC_REVIEWS_ELEMENT_EDIT_IP_USER"), $arRes['IP_USER'], true );?>

<?if(!isset($arRes['BX_USER_ID']) || strlen($arRes['BX_USER_ID']) <= 0)
    $arRes['BX_USER_ID'] = $_COOKIE['BX_USER_ID'];

$tabControl->AddViewField( "BX_USER_ID", GetMessage("SC_REVIEWS_ELEMENT_EDIT_BX_USER_ID"), $arRes['BX_USER_ID'], true );?>

<?$tabControl->BeginCustomField( "RATING", GetMessage('SC_REVIEWS_ELEMENT_EDIT_RATING'), true );
?>
    <tr id="tr_RATING">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td>
            <?
            if(isset($arRes['RATING']))
                $rating = $arRes['RATING'];
            else
                $rating = 3;

            for($i = 1; $i <= $rating; ++$i)
                echo '&#9733;';
            ?>
            <br> <input min="1" name="RATING" type="number"
                        value="<?=$rating?>">
        </td>
    </tr>
<?
$tabControl->EndCustomField( "RATING" );
$tabControl->AddCalendarField("DATE_CREATION", GetMessage("SC_REVIEWS_ELEMENT_EDIT_DATE_CREATION"), new Type\DateTime($arRes['DATE_CREATION']));
$tabControl->AddViewField( 'DATE_CHANGE', GetMessage("SC_REVIEWS_ELEMENT_EDIT_DATE_CHANGE"), new Type\DateTime( $arRes['DATE_CHANGE'] ), true );
$tabControl->AddEditField( 'TITLE', GetMessage("SC_REVIEWS_ELEMENT_EDIT_TITLE"), true, array (
    "size" => 100,
    "maxlength" => 255
), htmlspecialcharsbx( $arRes['TITLE'] ), true );

$tabControl->BeginCustomField( "PLUS", GetMessage('SC_REVIEWS_ELEMENT_EDIT_PLUS'), true );?>
<tr id="tr_PLUS">
    <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
    <td>
        <?$APPLICATION->IncludeComponent( "bitrix:main.post.form", "", Array(
            'BUTTONS' => array(),
            'PARSER' => array(),
            'PIN_EDITOR_PANEL'=>'N',
            'TEXT' => array(
                'SHOW'=>'Y',
                'VALUE'=>$arRes['PLUS'],
                'NAME'=>'PLUS'
            )
        ) );?>
    </td>
</tr>
<?$tabControl->EndCustomField( "PLUS" );

$tabControl->BeginCustomField( "MINUS", GetMessage('SC_REVIEWS_ELEMENT_EDIT_MINUS'), true );?>
    <tr id="tr_MINUS">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td>
            <?$APPLICATION->IncludeComponent( "bitrix:main.post.form", "", Array(
                'BUTTONS' => array(),
                'PARSER' => array(),
                'PIN_EDITOR_PANEL'=>'N',
                'TEXT' => array(
                    'SHOW'=>'Y',
                    'VALUE'=>$arRes['MINUS'],
                    'NAME'=>'MINUS'
                )
            ) );?>
        </td>
    </tr>
<?$tabControl->EndCustomField( "MINUS" );

$tabControl->BeginCustomField( "ANSWER", GetMessage('SC_REVIEWS_ELEMENT_EDIT_ANSWER'), true );?>
    <tr id="tr_ANSWER">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td>
            <?$APPLICATION->IncludeComponent( "bitrix:main.post.form", "", Array(
                'BUTTONS' => array(),
                'PARSER' => array(),
                'PIN_EDITOR_PANEL'=>'N',
                'TEXT' => array(
                    'SHOW'=>'Y',
                    'VALUE'=>$arRes['ANSWER'],
                    'NAME'=>'ANSWER'
                )
            ) );?>
        </td>
    </tr>
<?$tabControl->EndCustomField( "ANSWER" );

$tabControl->AddEditField('LIKES', GetMessage("SC_REVIEWS_ELEMENT_EDIT_LIKES"), true, array (), htmlspecialcharsbx( $arRes['LIKES'] ));
$tabControl->AddEditField( 'DISLIKES', GetMessage("SC_REVIEWS_ELEMENT_EDIT_DISLIKES"), true, array (), htmlspecialcharsbx( $arRes['DISLIKES'] ) );
$tabControl->AddCheckBoxField( "RECOMMENDATED", GetMessage("SC_REVIEWS_ELEMENT_EDIT_RECOMMENDATED" ), true, ["Y", "N"],
    ($arRes['RECOMMENDATED'] == "Y" || !isset( $arRes['RECOMMENDATED'] )) );

$tabControl->BeginCustomField("FILES", GetMessage("SC_REVIEWS_ELEMENT_EDIT_FILES"), false);
?>
<tr id="tr_FILES" class="adm-detail-file-row">
    <td class="adm-detail-valign-top" width="40%">
        <?echo $tabControl->GetCustomLabelHTML();?></td>
    <td width="60%">
        <?_ShowFilePropertyField("FILES", [], $arFiles)?>
    </td>
</tr>
<?
$tabControl->EndCustomField("FILES");
$tabControl->AddEditField('MULTIMEDIA', GetMessage("SC_REVIEWS_ELEMENT_EDIT_VIDEO"), true,
        ["size" => 100, "maxlength" => 255], htmlspecialcharsbx($arRes["MULTIMEDIA"]));
$tabControl->AddViewField( "MODERATED_BY", GetMessage("SC_REVIEWS_ELEMENT_EDIT_MODERATED_BY" ), $strModeratorData, true );
$tabControl->BeginCustomField( "SEPARATOR", GetMessage('SC_REVIEWS_ELEMENT_EDIT_SEPARATOR' ), true );
?>
    <tr id="tr_SEPARATOR" class="heading">
        <td colspan="2"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
    </tr>
<?
$tabControl->EndCustomField( "SEPARATOR" );

foreach($arUserFields as $codeField => $nameField)
{
    $tabControl->BeginCustomField($codeField, $nameField, false );?>
    <tr id="tr_<?=$codeField?>">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td>
            <?$APPLICATION->IncludeComponent( "bitrix:main.post.form", "", Array(
                'BUTTONS' => array(),
                'PARSER' => array(),
                'PIN_EDITOR_PANEL'=>'N',
                'TEXT' => array(
                    'SHOW'=>'Y',
                    'VALUE'=>$arRes[$codeField],
                    'NAME'=>$codeField
                )
            ) );?>
        </td>
    </tr>
    <?$tabControl->EndCustomField($codeField);
}

$tabControl->BeginCustomField( "HID", '', false );
?>
<input type="hidden" name="IP_USER" value="<?=$arRes['IP_USER']?>">
<input type="hidden" name="BX_USER_ID" value="<?=$arRes['BX_USER_ID']?>">

<?echo bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID > 0 && !$bCopy){?>
    <input type="hidden" name="ID" value="<?=$ID?>">
<?}?>
<?
$tabControl->EndCustomField( "HID" );

$arButtonsParams = array (
    "disabled" => $readOnly,
    "back_url" => "/bitrix/admin/sc.reviews_reviews_list.php?lang=".LANG
);
$tabControl->Buttons( $arButtonsParams );

$tabControl->Show();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
