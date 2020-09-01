<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$frame=$this->createFrame()->begin();
global $APPLICATION;
global $USER;
if(!is_object($USER))
    $USER=new CUser;
?>

<div class="bx-system-auth-form">
    <?
    if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR'])
        ShowMessage($arResult['ERROR_MESSAGE']);
    ?>

    <?if($arResult["FORM_TYPE"] == "login"):?>
        <form name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="javascript:void(null);" class="auth_review">
            <div>
                <?if($arResult["BACKURL"] <> ''):?>
                    <input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
                <?endif?>

                <input type="hidden" name="AUTH_FORM" value="Y" />
                <input type="hidden" name="TYPE" value="AUTH" />
                <div class="form-group">
                    <p><?=GetMessage("AUTH_LOGIN_EMAIL")?></p>
                    <input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["USER_LOGIN"]?>" size="17" />
                </div>

                <div class="form-group">
                    <p><?=GetMessage("AUTH_PASSWORD")?><noindex><a href="/auth/?forgot_password=yes&forgot_password=yes&backurl=<?=urlencode($_SERVER['REQUEST_URI'])?>" rel="nofollow"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a></noindex></p>
                    <input type="password" name="USER_PASSWORD" maxlength="50" size="17" autocomplete="off" />
                </div>

                <?if($arResult["SECURE_AUTH"]):?>
                    <span class="bx-auth-secure" id="bx_auth_secure<?=$arResult["RND"]?>" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
                        <div class="bx-auth-secure-icon"></div>
                    </span>
                    <noscript>
                    <span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
                        <div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
                    </span>
                    </noscript>
                    <script type="text/javascript">
                        document.getElementById('bx_auth_secure<?=$arResult["RND"]?>').style.display = 'inline-block';
                    </script>
                <?endif?>

                <?if ($arResult["STORE_PASSWORD"] == "Y"):?>
                    <input type="hidden" id="USER_REMEMBER_frm" name="USER_REMEMBER" value="Y" />
                <?endif?>

                <?if ($arResult["CAPTCHA_CODE"]):?>
                    <?echo GetMessage("AUTH_CAPTCHA_PROMT")?>:<br />
                    <input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /><br /><br />
                    <input type="text" name="captcha_word" maxlength="50" value="" />
                <?endif?>
                <input class="btn btn-primary" type="submit" name="Login" value="<?=GetMessage("AUTH_LOGIN_BUTTON")?>" />
            </div>
        </form>

    <?elseif($arResult["FORM_TYPE"] == "otp"):?>
        <form name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
            <?if($arResult["BACKURL"] <> ''):?>
                <input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
            <?endif?>

            <input type="hidden" name="AUTH_FORM" value="Y" />
            <input type="hidden" name="TYPE" value="OTP" />
            <?echo GetMessage("auth_form_comp_otp")?><br />
            <input type="text" name="USER_OTP" maxlength="50" value="" size="17" autocomplete="off" />

            <?if ($arResult["CAPTCHA_CODE"]):?>
                <?echo GetMessage("AUTH_CAPTCHA_PROMT")?>:<br />
                <input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
                <img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /><br /><br />
                <input type="text" name="captcha_word" maxlength="50" value="" />
            <?endif?>

            <?if ($arResult["REMEMBER_OTP"] == "Y"):?>
                <input type="checkbox" id="OTP_REMEMBER_frm" name="OTP_REMEMBER" value="Y" />
                <label for="OTP_REMEMBER_frm" title="<?echo GetMessage("auth_form_comp_otp_remember_title")?>"><span></span><?echo GetMessage("auth_form_comp_otp_remember")?></label>
            <?endif?>
            <input type="submit" name="Login" value="<?=GetMessage("AUTH_LOGIN_BUTTON")?>" />
            <noindex><a href="<?=$arResult["AUTH_LOGIN_URL"]?>" rel="nofollow"><?echo GetMessage("auth_form_comp_auth")?></a></noindex><br />
        </form>

    <?else:?>
        <form action="<?=$arResult["AUTH_URL"]?>">
                    <?=$arResult["USER_NAME"]?><br />
                    [<?=$arResult["USER_LOGIN"]?>]<br />
                    <a href="<?=$arResult["PROFILE_URL"]?>" title="<?=GetMessage("AUTH_PROFILE")?>"><?=GetMessage("AUTH_PROFILE")?></a><br />
                <?foreach ($arResult["GET"] as $key => $value):?>
                    <input type="hidden" name="<?=$key?>" value="<?=$value?>" />
                <?endforeach?>
                <input type="hidden" name="logout" value="yes" />
                <input type="submit" name="logout_butt" value="<?=GetMessage("AUTH_LOGOUT_BUTTON")?>" />
        </form>
    <?endif?>
</div>
<?$frame->end();?>