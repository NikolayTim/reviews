<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
<?
global $APPLICATION;
global $USER;
if(!is_object($USER))
    $USER=new CUser;
?>
<?$frame=$this->createFrame()->begin();?>

<div class="bx-auth-reg">
    <?if($USER->IsAuthorized()):?>
        <p><?echo GetMessage("MAIN_REGISTER_AUTH")?></p>
    <?else:?>
        <?
        if (count($arResult["ERRORS"]) > 0):
            foreach ($arResult["ERRORS"] as $key => $error)
                if (intval($key) == 0 && $key !== 0)
                    $arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", "&quot;".GetMessage("REGISTER_FIELD_".$key)."&quot;", $error);

            ShowError(implode("<br />", $arResult["ERRORS"]));

        elseif($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):
        ?>
            <p id="register-title"><?echo GetMessage("REGISTER_EMAIL_WILL_BE_SENT")?></p>
        <?endif?>

        <form method="post" action="javascript:void(null);" name="regform" class="registration_review"  enctype="multipart/form-data" data-params='<?=serialize($arParams)?>'>
            <?if($arResult["BACKURL"] <> ''):?>
                <input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
            <?endif;?>

            <?foreach ($arResult['SHOW_FIELDS'] as $FIELD):?>
                <?$required = $arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y" ? ' required="required"' : ''?>
                <?if($FIELD == "AUTO_TIME_ZONE" && $arResult["TIME_ZONE_ENABLED"] == true):?>
                    <?echo GetMessage("main_profile_time_zones_auto")?><?if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y"):?><span class="starrequired">*</span><?endif?>
                    <select name="REGISTER[AUTO_TIME_ZONE]" onchange="this.form.elements['REGISTER[TIME_ZONE]'].disabled=(this.value != 'N')"<?=$required?>>
                        <option value=""><?echo GetMessage("main_profile_time_zones_auto_def")?></option>
                        <option value="Y"<?=$arResult["VALUES"][$FIELD] == "Y" ? " selected=\"selected\"" : ""?>><?echo GetMessage("main_profile_time_zones_auto_yes")?></option>
                        <option value="N"<?=$arResult["VALUES"][$FIELD] == "N" ? " selected=\"selected\"" : ""?>><?echo GetMessage("main_profile_time_zones_auto_no")?></option>
                    </select>

                    <?echo GetMessage("main_profile_time_zones_zones")?>
                    <select name="REGISTER[TIME_ZONE]"<?if(!isset($_REQUEST["REGISTER"]["TIME_ZONE"])) echo 'disabled="disabled"'?>>
                        <?foreach($arResult["TIME_ZONE_LIST"] as $tz=>$tz_name):?>
                            <option value="<?=htmlspecialcharsbx($tz)?>"<?=$arResult["VALUES"]["TIME_ZONE"] == $tz ? " selected=\"selected\"" : ""?>><?=htmlspecialcharsbx($tz_name)?></option>
                        <?endforeach?>
                    </select>
                <?else:?>
                    <div class="form-group" id="REGISTER_<?=$FIELD?>">
                        <?if($FIELD!='LOGIN'): ?>
                            <p><?=GetMessage("REGISTER_FIELD_".$FIELD)?>:<?if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y"):?><span class="starrequired">*</span><?endif?></p>
                        <?endif; ?>
                            <?
                        switch ($FIELD)
                        {
                            case "PASSWORD":
                                ?><input size="30" type="password" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" autocomplete="off" class="bx-auth-input" <?=$required?>/><br>
                                <?if($arResult["SECURE_AUTH"]):?>
                                    <span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
                                        <div class="bx-auth-secure-icon"></div>
                                    </span>
                                    <noscript>
                                        <span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
                                            <div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
                                        </span>
                                    </noscript>
                                    <script type="text/javascript">
                                        document.getElementById('bx_auth_secure').style.display = 'inline-block';
                                    </script>
                                <?endif?>
                                <?
                                break;
                            case "CONFIRM_PASSWORD":
                                ?><input size="30" type="password" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" autocomplete="off" <?=$required?>/><br><?
                                break;
                            case "PERSONAL_GENDER":
                                ?><select name="REGISTER[<?=$FIELD?>]" <?=$required?>>
                                    <option value=""><?=GetMessage("USER_DONT_KNOW")?></option>
                                    <option value="M"<?=$arResult["VALUES"][$FIELD] == "M" ? " selected=\"selected\"" : ""?>><?=GetMessage("USER_MALE")?></option>
                                    <option value="F"<?=$arResult["VALUES"][$FIELD] == "F" ? " selected=\"selected\"" : ""?>><?=GetMessage("USER_FEMALE")?></option>
                                </select><?
                                break;

                            case "PERSONAL_COUNTRY":
                            case "WORK_COUNTRY":
                                ?><select name="REGISTER[<?=$FIELD?>]"<?=$required?>><?
                                foreach ($arResult["COUNTRIES"]["reference_id"] as $key => $value)
                                {
                                    ?><option value="<?=$value?>"<?if ($value == $arResult["VALUES"][$FIELD]):?> selected="selected"<?endif?>><?=$arResult["COUNTRIES"]["reference"][$key]?></option>
                                <?
                                }
                                ?></select><?
                                break;

                            case "PERSONAL_PHOTO":
                            case "WORK_LOGO":
                                ?><input size="30" type="file" name="REGISTER_FILES_<?=$FIELD?>" <?=$required?>/><?
                                break;

                            case "PERSONAL_NOTES":
                            case "WORK_NOTES":
                                ?><textarea cols="30" rows="5" name="REGISTER[<?=$FIELD?>]"<?=$required?>><?=$arResult["VALUES"][$FIELD]?></textarea><?
                                break;
                            case "LOGIN":
                                ?>
                                <input size="30" type="hidden" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" />
                                <?
                                break;

                            default:
                                if ($FIELD == "PERSONAL_BIRTHDAY"):?><small><?=$arResult["DATE_FORMAT"]?></small><br /><?endif;
                                ?><input size="30" type="text" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" <?=$required?>/><br><?
                                    if ($FIELD == "PERSONAL_BIRTHDAY")
                                    {
                                        $APPLICATION->IncludeComponent(
                                            'bitrix:main.calendar',
                                            '',
                                            array(
                                                'SHOW_INPUT' => 'N',
                                                'FORM_NAME' => 'regform',
                                                'INPUT_NAME' => 'REGISTER[PERSONAL_BIRTHDAY]',
                                                'SHOW_TIME' => 'N'
                                            ),
                                            null,
                                            array("HIDE_ICONS"=>"Y")
                                        );
                                    }
                        }?>
                    </div>
                <?endif?>
            <?endforeach?>

            <?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
                <?=strlen(trim($arParams["USER_PROPERTY_NAME"])) > 0 ? $arParams["USER_PROPERTY_NAME"] : GetMessage("USER_TYPE_EDIT_TAB")?>
                <?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
                <?=$arUserField["EDIT_FORM_LABEL"]?>:<?if ($arUserField["MANDATORY"]=="Y"):?><span class="starrequired">*</span><?endif;?>
                        <?$APPLICATION->IncludeComponent(
                            "bitrix:system.field.edit",
                            $arUserField["USER_TYPE"]["USER_TYPE_ID"],
                            array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField, "form_name" => "regform"), null, array("HIDE_ICONS"=>"Y"));?>
                <?endforeach;?>
            <?endif;?>

			<button class="btn btn-primary" type="submit" name="register_submit_button"><?=GetMessage("AUTH_REGISTER")?></button>
        </form>
    <?endif?>
</div>
<script type="text/javascript">
	$('.registration_review').on("change","input[name='REGISTER[EMAIL]']" ,function() {
		var login_val = $(this).val();
		$('.registration_review').find("input[name='REGISTER[LOGIN]']").val(login_val);
	});
   
	$('body').on('input','[name="REGISTER[EMAIL]"]',function(){
		var editedInput = $(this);
		var regexp = /.+@.+\..+/i;

		if(!regexp.test(editedInput.val()) ){
			editedInput.addClass('red');
			if(!$('#email_error').length){
				editedInput.parent().append('<em id="email_error" class="help-block notshow visible">Введите корректный email</em>');
			}else{
				$('#email_error').addClass('visible');
			}
		}else{
			editedInput.removeClass('red');
			if($('#email_error').length){
				$('#email_error').removeClass('visible');
			}
		}
	});

	$('body').on('input','[name="REGISTER[PERSONAL_MOBILE]"]',function(){
		var editedInput = $(this),
			val = editedInput.val();

		editedInput.val(val.replace(/[^\+\-\(\)\d]/g,""));
	});
</script>
<?$frame->end();?>