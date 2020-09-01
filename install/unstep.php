<?
use \Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

Loc::loadMessages(__FILE__);
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
    <input type="hidden" name="id" value="sc.reviews">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <p><input type="checkbox" name="savedata" id="savedata" value="Y" checked>
        <label for="savedata"><?=Loc::getMessage("SC_RV_SAVE_DATA")?></label>
    </p>
    <input type="submit" name="del-module" value="<?=Loc::getMessage("SC_RV_DELETE_MODULE")?>">
</form>