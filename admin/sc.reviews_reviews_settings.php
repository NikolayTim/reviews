<?
use Bitrix\Main\Loader;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile( __FILE__ );

if(!Loader::includeModule( 'sc.reviews' ))
	return;

if(Loader::includeModule( 'forum' ))
	$LoadForum = true;
else
	$LoadForum = false;

$iModuleID = 'sc.reviews';
$accessLevel = $APPLICATION->GetGroupRight($iModuleID);

if($accessLevel < "R")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

$arTabs = array(
	array(
			'DIV' => 'editOptions',
			'TAB' => GetMessage("SC_REVIEWS_SETTINGS_edit1"),
			'ICON' => '',
			'TITLE' => GetMessage("SC_REVIEWS_SETTINGS_edit1"),
			'SORT' => '10'
	),
);
$tabControl = new CAdminForm( "tabControl", $arTabs );

if($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $accessLevel == "W" && check_bitrix_sessid())
{
	$arDefaultOptions = \Bitrix\Main\Config\Option::getDefaults($iModuleID);
	foreach($arDefaultOptions as $keyOption => $valueOption)
	{
		if(array_key_exists($keyOption, $_REQUEST))
		{
            if($keyOption == "REVIEWS_USER_GROUPS_WITHOUT_MODERATION")
				\Bitrix\Main\Config\Option::set($iModuleID, $keyOption, serialize($_REQUEST[$keyOption]), $_REQUEST['site']);
			else
				\Bitrix\Main\Config\Option::set($iModuleID, $keyOption, $_REQUEST[$keyOption], $_REQUEST['site']);
		}
	}
	LocalRedirect( "/bitrix/admin/sc.reviews_reviews_settings.php?&mess=ok&lang=" . LANG . "&site=" . $_REQUEST["site"] . "&" . $tabControl->ActiveTabParam() );
}

$arReviewsIDElementValues["REFERENCE_ID"] = ['ID_ELEMENT',	'XML_ID_ELEMENT'];
$arReviewsIDElementValues["REFERENCE"] = [GetMessage("SC_REVIEWS_ID_ELEMENT_ID"), GetMessage("SC_REVIEWS_ID_ELEMENT_XML")];

$arGroupsUser = [];
$rsGroups = CGroup::GetList ($by = "c_sort", $order = "asc", Array ("ACTIVE" => 'Y'));
while($arGroup = $rsGroups->Fetch())
{
	$arGroupsUser["REFERENCE_ID"][] = $arGroup['ID'];
	$arGroupsUser["REFERENCE"][] = '['.$arGroup['ID'].'] '.$arGroup['NAME'];
}

$arFilter = Array(
		'ACTIVE' => 'Y',
		'EVENT_NAME' => GetMessage("SC_RV_ADD_MAILING_EVENT_TYPE_NAME")
);
$rsMess = CEventMessage::GetList( $by = 'id', $order = "desc", $arFilter );
if($arMess = $rsMess->fetch())
{
	$arEventType = CEventType::GetList(["TYPE_ID" => GetMessage("SC_RV_ADD_MAILING_EVENT_TYPE_NAME"), "LID" => "ru"])->Fetch();
	$mailNotice = "<a target=\"_blank\" href=\"/bitrix/admin/message_edit.php?lang=ru&ID=".$arMess['ID']."\">".
		GetMessage('SC_REVIEWS_MAIL_LINK_EVENT')."</a><br>".GetMessage("SC_REVIEWS_AVAILABLE_EVENT")."<br>".
		nl2br($arEventType["DESCRIPTION"]);
}
else
	$mailNotice = GetMessage("SC_REVIEWS_NO_MAIL_EVENT");

$arGroups = array(
	'GROUP_SETTINGS' => array(
			'TITLE' => GetMessage('SC_REVIEWS_GROUP_SETTINGS'),
			'TAB' => 0
	),
	'GROUP_RECAPTCHA2' => array(
			'TITLE' => GetMessage('SC_REVIEWS_GROUP_RECAPTCHA2'),
			'TAB' => 0
	),
);

$arOptions = array(
	'REVIEWS_MODERATION' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_MODERATION_REVIEWS'),
			'TYPE' => 'CHECKBOX',
			'REFRESH' => 'N',
			'SORT' => '2',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_MODERATION', $_REQUEST['site'])
	),
	'REVIEWS_BAN_DAYS' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage("SC_REVIEWS_BAN_DAYS"),
			'TYPE' => 'INT',
			'REFRESH' => 'N',
			'SORT' => '3',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_BAN_DAYS', $_REQUEST['site'])
	),
	'REVIEWS_USER_GROUPS_WITHOUT_MODERATION' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_USER_GROUPS_WITHOUT_MODERATION'),
			'TYPE' => 'MSELECT',
			'REFRESH' => 'N',
			'VALUES' => $arGroupsUser,
			'VALUE' => unserialize(\Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_USER_GROUPS_WITHOUT_MODERATION', $_REQUEST['site'])),
			'SORT' => '5'
	),
	'REVIEWS_TITLE' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_TITLE_REVIEWS'),
			'TYPE' => 'CHECKBOX',
			'REFRESH' => 'N',
			'SORT' => '6',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_TITLE', $_REQUEST['site'])
	),
	'REVIEWS_EDITOR' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_REVIEWS_EDITOR'),
			'TYPE' => 'CHECKBOX',
			'REFRESH' => 'N',
			'SORT' => '10',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_EDITOR', $_REQUEST['site'])
	),
	'REVIEWS_NO_USER_IMAGE' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_NO_USER_IMAGE'),
			'TYPE' => 'FILE',
			'REFRESH' => 'N',
			'SORT' => '12',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_NO_USER_IMAGE', $_REQUEST['site'])
	),
	'REVIEWS_ANSWER_IMAGE' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_ANSWER_IMAGE'),
			'TYPE' => 'FILE',
			'REFRESH' => 'N',
			'SORT' => '14',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_ANSWER_IMAGE', $_REQUEST['site'])
	),
	'REVIEWS_NOTICE_MAIL' => array(	// Это не опция
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_REVIEWS_MAIL_LINK_NOTICE'),
			'TYPE' => 'CUSTOM',
			'REFRESH' => 'Y',
			'VALUE' => '<tr><td align="center" colspan="2">
									<div align="center" class="adm-info-message-wrap">
										<div class="adm-info-message">
											'.$mailNotice.'
										</div>
									</div>
								</td></tr>',
			'SORT' => '18'
	),
	'REVIEWS_DELETE' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_REVIEWS_DELETE'),
			'TYPE' => 'CHECKBOX',
			'REFRESH' => 'N',
			'SORT' => '20',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_DELETE', $_REQUEST['site'])
	),
	'REVIEWS_REPEAT' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => GetMessage('SC_REVIEWS_REVIEWS_REPEAT'),
			'TYPE' => 'INT',
			'REFRESH' => 'N',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_REPEAT', $_REQUEST['site']),
			'SORT' => '25',
			'NOTES' => GetMessage('SC_REVIEWS_REVIEWS_REPEAT_NOTE'),
	),
	'REVIEWS_RECAPTCHA2_SITE_KEY' => array(
			'GROUP' => 'GROUP_RECAPTCHA2',
			'TITLE' => GetMessage('SC_REVIEWS_REVIEWS_RECAPTCHA2_SITE_KEY'),
			'TYPE' => 'STRING',
			'REFRESH' => 'N',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_RECAPTCHA2_SITE_KEY', $_REQUEST['site']),
			'SORT' => '10'
	),
	'REVIEWS_RECAPTCHA2_SECRET_KEY' => array(
			'GROUP' => 'GROUP_RECAPTCHA2',
			'TITLE' => GetMessage('SC_REVIEWS_REVIEWS_RECAPTCHA2_SECRET_KEY'),
			'TYPE' => 'STRING',
			'REFRESH' => 'N',
			'VALUE' => \Bitrix\Main\Config\Option::getRealValue($iModuleID, 'REVIEWS_RECAPTCHA2_SECRET_KEY', $_REQUEST['site']),
			'SORT' => '20'
	),
);

$APPLICATION->SetTitle(GetMessage('SC_REVIEWS_TITLE'));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($_REQUEST["mess"] == "ok")
	CAdminMessage::ShowMessage( array (
		"MESSAGE" => GetMessage( "SC_REVIEWS_OPTIONS_EDIT_SAVED" ),
		"TYPE" => "OK"
	) );


$tabControl->Begin(array("FORM_ACTION" => $APPLICATION->GetCurPage()));
$tabControl->BeginNextFormTab();

foreach($arOptions as $keyOption => $arrOption)
{
	switch ($arrOption['TYPE'])
	{
		case 'CHECKBOX':
			$tabControl->AddCheckBoxField($keyOption, $arrOption["TITLE"], true, ["Y", "N"], ($arrOption['VALUE'] == "Y" || !isset($arrOption['VALUE'])));
			break;

		case 'STRING': case 'INT':
			$tabControl->AddEditField($keyOption, $arrOption["TITLE"], true, array (), htmlspecialcharsbx( $arrOption['VALUE'] ) );
			break;

        case 'SELECT': case 'MSELECT':
            $tabControl->BeginCustomField( $keyOption, $arrOption["TITLE"], false );
            ?>
            <tr id="tr_<?=$keyOption?>">
                <td width="40%"><?echo "<b>".$arrOption["TITLE"]."</b>"; //$tabControl->GetCustomLabelHTML(); ?></td>
                <td width="60%">
                    <?if($arrOption['TYPE'] == 'SELECT')
                        echo SelectBoxFromArray($keyOption, $arReviewsIDElementValues, $arrOption['VALUE'], '', 'style="min-width:350px"'); //, true, 'tabControl_form');
                    else
                        echo SelectBoxMFromArray($keyOption."[]", $arrOption['VALUES'], $arrOption['VALUE'], '', 'style="min-width:350px"');?>
                </td>
            </tr>
            <?
            $tabControl->EndCustomField($keyOption);
            break;

        case 'CUSTOM':
            $tabControl->BeginCustomField( $keyOption, $arrOption["TITLE"], false );
            echo $arrOption['VALUE'];
            $tabControl->EndCustomField($keyOption);
            break;

        case 'FILE':

            $tabControl->BeginCustomField( $keyOption, $arrOption["TITLE"], false );

            if(!isset($arrOption['FIELD_SIZE']))
                $arrOption['FIELD_SIZE'] = 25;

            if(!isset($arrOption['BUTTON_TEXT']))
                $arrOption['BUTTON_TEXT'] = '...';
?>
            <tr id="tr_<?=$keyOption?>">
                <td width="40%"><?echo "<b>".$arrOption["TITLE"]."</b>";?></td>
                <td width="60%">
<?
            CAdminFileDialog::ShowScript( Array (
                'event' => 'BX_FD_'.$keyOption,
                'arResultDest' => ['FUNCTION_NAME' => 'BX_FD_ONRESULT_'.$keyOption],
                'arPath' => [],
                'select' => 'F',
                'operation' => 'O',
                'showUploadTab' => true,
                'showAddToMenuTab' => false,
                'fileFilter' => '',
                'allowAllFiles' => true,
                'SaveConfig' => true
            ) );

            echo '<input id="__FD_PARAM_'.$keyOption.'" name="'.$keyOption.'" 
				size="'.$arrOption['FIELD_SIZE'].'" value="'.htmlspecialchars($arrOption["VALUE"]).
                '" type="text" style="float: left;" '.($arrOption['FIELD_READONLY']=='Y' ? 'readonly' : '').' />
				<input value="'.$arrOption['BUTTON_TEXT'].'" type="button" onclick="window.BX_FD_'.$keyOption.'();" />
				<script>
					setTimeout(function(){
						if (BX("bx_fd_input_'.strtolower($keyOption).'"))
							BX("bx_fd_input_'.strtolower($keyOption).'").onclick = window.BX_FD_'.$keyOption.';
					}, 200);
					window.BX_FD_ONRESULT_'.$keyOption.' = function(filename, filepath)
					{
						var oInput = BX("__FD_PARAM_'.$keyOption.'");
						if (typeof filename == "object")
							oInput.value = filename.src;
						else
							oInput.value = (filepath + "/" + filename).replace(/\/\//ig, \'/\');
					}
				</script>';?>
                </td>
            </tr>

            <?$tabControl->EndCustomField($keyOption);
            break;
	}
}

$tabControl->BeginCustomField("HID", '', false );
echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<input type="hidden" name="site" value="<?=$_REQUEST["site"]?>">
<?
$tabControl->EndCustomField( "HID" );

$tabControl->Buttons(
			["disabled" => ($accessLevel < "W"),
			"back_url" => $APPLICATION->GetCurPage(false)."?lang=".LANG."&site=".$_REQUEST["site"]]
);

$tabControl->Show();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
