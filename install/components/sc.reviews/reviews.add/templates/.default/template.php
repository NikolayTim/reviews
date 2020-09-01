<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?global $APPLICATION;
	global $USER;

if(!is_object($USER))
    $USER=new CUser;

if(isset($arResult["REVIEW"]["ID"]) && intval($arResult["REVIEW"]["ID"]) > 0)
{
    $codeToFieldName = "_" . $arResult["REVIEW"]["ID"];
    $idDateInput = intval($arResult["REVIEW"]["ID"]);
}
else
{
    $codeToFieldName = '';
    $idDateInput = 0;
}
?>

<div class="add-reviews card">
	<div class="success" style="display:none;"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_SUCCESS_TEXT")?></div>
	<div class="bg">
		<div class="spoiler">
			<div class="spoiler-input">
				<?=$arParams["BUTTON_TEXT"]?>
            </div>
		</div>
	</div>

	<div class="spoiler-reviews-body">

		<?if (!$USER->IsAuthorized() && $arParams['AUTHORIZED_USER'] == "Y"):?>
            <div class="auth-error text-center">
                <?= GetMessage("SC_REVIEWS_ADD_REVIEWS_NO_AUTH") ?>
            </div>
            <ul class="nav nav-tabs tabs-2 redbg" role="tablist">
                <li class="nav-item active">
                    <a class="nav-link" data-toggle="tab" href="#panel1" role="tab">Вход</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#panel2" role="tab">Регистрация</a>
                </li>
            </ul>

            <div class="container-fluid">
                <div class="tab-content row">
                    <div class="tab-pane fade in active col-sm-7" id="panel1" role="tabpanel">
                        <div class="form-auth">
                            <p id="auth-title"><?= GetMessage("SC_REVIEWS_ADD_AUTH_TITLE") . SITE_SERVER_NAME ?></p>
                            <p id="auth_review-check-error" style="display:none;"></p>
                            <? $APPLICATION->IncludeComponent("bitrix:system.auth.form", "", array(
                                "REGISTER_URL"        => SITE_DIR . "login/",
                                "FORGOT_PASSWORD_URL" => SITE_DIR . "login/?forgot_password=yes",
                                "PROFILE_URL"         => SITE_DIR . "personal/",
                                "SHOW_ERRORS"         => "Y",
                                "STORE_PASSWORD"      => "N",
                            ),
                                $component
                            ); ?>
                        </div>
                    </div>

                    <div class="tab-pane fade col-sm-7" id="panel2" role="tabpanel">
                        <div class="form-reg">
                            <p id="register-title"><?= GetMessage("SC_REVIEWS_ADD_REGISTER_TITLE") ?></p>
                            <p id="registration_review-check-error" style="display:none;"></p>
                                <? $APPLICATION->IncludeComponent("southcoast:main.register", "reviews", Array(
                                    "USER_PROPERTY_NAME" => "",
                                    "SEF_MODE"           => "Y",
                                    "SHOW_FIELDS"        => array('EMAIL', 'PERSONAL_MOBILE', 'PASSWORD', 'CONFIRM_PASSWORD', 'NAME', 'LAST_NAME'),
                                    "REQUIRED_FIELDS"    => array('PERSONAL_MOBILE', 'NAME', 'LAST_NAME'),
                                    "AUTH"               => "Y",
                                    "USE_BACKURL"        => "N",
                                    "SUCCESS_PAGE"       => "",
                                    "SET_TITLE"          => "N",
                                    "USER_PROPERTY"      => Array(),
                                    "SEF_FOLDER"         => "/",
                                    "VARIABLE_ALIASES"   => Array(),
                                ),
                                    $component
                                ); ?>
                        </div>
                    </div>

                    <div class="col-sm-5">
                        <div class="socserv">
                        </div>
                    </div>
                </div>
            </div>
		<?else:?>
			<?if($arResult['BAN']!="Y" || $arResult["MODERATIONGROUPS"]):?>
                <div class="review-add-block">
                    <p class="add-check-error" style="display:none;"></p>
                    <form  class="review add_review" action="javascript:void(null);" enctype="multipart/form-data" method="post">
                        <div class="row">

                            <?$keyUserField = 'FIO_VAL';?>
                            <div class="form-group col-sm-4">
                                <label class="add-field-title"><?=$arResult['USERFIELDS'][$keyUserField]?>:</label>
                                <input type="text" class="from-control" required
                                       name="<?=$keyUserField?>"
                                       value="<?=$arResult["REVIEW"]["FIO_VAL"]?>"
                                       placeholder="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_PLACEHOLDER_FIO")?>"/>
                            </div>

                            <?$keyUserField = 'REST_DATE_FROM_VAL';?>
                            <div class="form-group col-sm-4">
                                <label class="add-field-title"><?=$arResult['USERFIELDS'][$keyUserField]?>:</label>
                                <div class="date-picker">
                                    <input type="text" class="from-control date-picker-from" required="required"
                                           max="<?=date('Y-m-d')?>"
                                           name="<?=$keyUserField?>"
                                           data-review-id = "<?=$idDateInput?>"
                                           value="<?=$arResult["REVIEW"][$keyUserField . "_DATE"]?>"
                                           placeholder="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_PLACEHOLDER_DATE")?>"/>
                                </div>
                            </div>

                            <?$keyUserField = 'REST_DATE_TO_VAL';?>
                            <div class="form-group col-sm-4">
                                <label class="add-field-title"><?=$arResult['USERFIELDS'][$keyUserField]?>:</label>
                                <div class="date-picker">
                                    <input type="text" class="from-control date-picker-to" required="required"
                                           max="<?= date('Y-m-d')?>"
                                           name="<?=$keyUserField?>"
                                           data-review-id = "<?=$idDateInput?>"
                                           value="<?=$arResult["REVIEW"][$keyUserField . "_DATE"]?>"
                                           placeholder="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_PLACEHOLDER_DATE")?>"/>
                                </div>
                            </div>

                            <?if($arResult["MODERATIONGROUPS"]):?>
                                <?$dateCreation = new DateTime($arResult["REVIEW"]["DATE_CREATION"]);?>
                                <div class="form-group col-sm-4">
                                    <label class="add-field-title"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_DATE_CREATION")?></label>
                                    <div class="date-picker">
                                    <input type="text" class="from-control date-picker-creation" required
                                           name="DATE_CREATION"
                                           value="<?=$dateCreation->format('d.m.Y')?>"
                                           max="<?= date('Y-m-d') ?>"
                                           placeholder="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_PLACEHOLDER_DATE")?>"/>
                                    </div>
                                </div>
                            <?endif;?>

                        </div>
                        <div style="clear:both"></div>

                        <p class="review-add-rating-title1">
                            <?=GetMessage("SC_REVIEWS_ADD_REVIEWS_ADD_RATING_TITLE1")?>
                        </p>
                        <p class="review-add-rating-title2">
                            <?=GetMessage("SC_REVIEWS_ADD_REVIEWS_ADD_RATING_TITLE2")?>
                        </p>

                        <div class='rating_selection'>
                            <?if (!$arParams['DEFAULT_RATING_ACTIVE']):?>
                                <input type="radio" id="no_star" name="rating" value="0" checked/>
                                <label title="" for="no_star" style="display: none"></label>
                            <?endif;?>

                            <?if(strlen($codeToFieldName) > 0)
                                $curRating = $arResult['REVIEW']['RATING'];
                            else
                                $curRating = $arParams['DEFAULT_RATING_ACTIVE'];?>

                            <?for ($i = 1; $i <= $arParams['MAX_RATING']; ++$i):?>
                                <input id="star-<?=$i . $codeToFieldName?>" type="radio" name="RATING"
                                       value="<?= $i?>" <?= ($i == $curRating) ? 'checked' : '';?>/>
                                <label title="" for="star-<?=$i . $codeToFieldName?>"></label>
                            <?endfor; ?>
                        </div>

                        <input type="hidden" name="ID_ELEMENT" value="<?=$arParams['ID_ELEMENT']?>" />
                        <input type="hidden" name="ID" value="<?=$arParams['ID']?>" />
                        <input type="hidden" name="NOTICE_EMAIL" value="<?=$arParams['NOTICE_EMAIL']?>" />
                        <input type="hidden" name="BX_USER_ID" value="<?=(strlen($arResult["REVIEW"]["BX_USER_ID"]) > 0 ? $arResult["REVIEW"]["BX_USER_ID"] : $_COOKIE["BX_USER_ID"])?>" />
                        <input type="hidden" name="IP_USER" value="<?=(strlen($arResult["REVIEW"]["IP_USER"]) > 0 ? $arResult["REVIEW"]["IP_USER"] : $_SERVER["REMOTE_ADDR"])?>" />

                        <?if(isset($arResult["REVIEW"]["ID_USER"]) && intval($arResult["REVIEW"]["ID_USER"]) > 0)
                            $id_user = $arResult["REVIEW"]["ID_USER"];
                        elseif($USER->IsAuthorized())
                            $id_user = $USER->GetID();
                        else
                            $id_user = 0;?>

                        <input type="hidden" name="ID_USER" value="<?=$id_user?>" />

                        <input type="hidden" name="ACTION" value="SAVE_REVIEW" />

                        <?if(\Bitrix\Main\Config\Option::getRealValue('sc.reviews', 'REVIEWS_TITLE', SITE_ID) == 'Y'):?>
                            <p class="title"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_TITLE")?></p>
                            <p class="title-example"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_TITLE_EXAMPLE")?></p>
                            <input type="text" name="TITLE" value="<?=$arResult["REVIEW"]["TITLE"]?>" maxlength="255" class="title" />
                        <?endif;?>

                        <p class="text"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_TEXT")?></p>
                        <div class="row">

                            <?$arPlusMinus = ["PLUS", "MINUS"];?>
                            <?$useReviewsEditor = \Bitrix\Main\Config\Option::getRealValue('sc.reviews', 'REVIEWS_EDITOR', SITE_ID)?>

                            <?foreach($arPlusMinus as $anyValue):?>
                                <div class='col-sm-6'>
                                    <?if($useReviewsEditor == "Y"):?>
                                        <?$APPLICATION->IncludeComponent( "bitrix:main.post.form", "", Array(
                                            'BUTTONS' => array(),
                                            'PARSER' => array(),
                                            'PIN_EDITOR_PANEL' => 'N',
                                            'TEXT' => array(
                                                'SHOW' => 'Y',
                                                'VALUE' => $arResult["REVIEW"][$anyValue],
                                                'NAME' => $anyValue
                                            )
                                        ) );?>
                                    <?else:?>
                                        <div class="form-group">
                                            <label class="add-field-title"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_" . $anyValue)?></label>
                                            <div>
                                                <textarea class='w100' name="<?=$anyValue?>" required='required'
                                                    maxlength="<?=$arParams["TEXTBOX_MAXLENGTH"]?>"><?=$arResult["REVIEW"][$anyValue]?></textarea>
                                            </div>
                                        </div>
                                    <?endif;?>
                                </div>
                            <?endforeach;?>

                            <?foreach($arResult['USERFIELDS'] as $codeField => $nameField):?>
                                <?if(!in_array($codeField, ['REST_DATE_FROM_VAL', 'REST_DATE_TO_VAL', 'FIO_VAL'])):?>
                                    <div class='col-sm-6'>
                                        <?if($useReviewsEditor == "Y"):?>
                                            <?$APPLICATION->IncludeComponent( "bitrix:main.post.form", "", Array(
                                                'BUTTONS' => array(),
                                                'PARSER' => array(),
                                                'PIN_EDITOR_PANEL' => 'N',
                                                'TEXT' => array(
                                                    'SHOW' => 'Y',
                                                    'VALUE' => $arResult["REVIEW"][$codeField],
                                                    'NAME' => $codeField
                                                )
                                            ) );?>
                                        <?else:?>
                                            <div class="form-group">
                                                <label class="add-field-title"><?=$nameField?>:</label>
                                                <div>
                                                    <textarea class='w100' name="<?=$codeField?>"
                                                       maxlength="<?=$arParams["TEXTBOX_MAXLENGTH"]?>"><?=$arResult["REVIEW"][$codeField]?></textarea>
                                                </div>
                                            </div>
                                        <?endif;?>
                                    </div>
                                <?endif;?>
                            <?endforeach;?>

                            <div class="col-sm-12">
                                <p class="recommendated"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_RECOMMENDATED")?></p>
                                <div class="radio">
                                    <input class="smartRadio" type="radio" name="RECOMMENDATED"
                                           id="recommy<?=$codeToFieldName?>" value="Y"
                                        <?=$arResult["REVIEW"]["RECOMMENDATED"] != "N" ? 'checked' : ''?>/>
                                    <label for="recommy<?=$codeToFieldName?>" class="radio-label"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_RECOMMENDATED_YES")?></label>
                                </div>
                                <div class="radio">
                                    <input class="smartRadio" type="radio" name="RECOMMENDATED"
                                           id="recommn<?=$codeToFieldName?>" value="N"
                                        <?=$arResult["REVIEW"]["RECOMMENDATED"] == "N" ? 'checked' : ''?>/>
                                    <label for="recommn<?=$codeToFieldName?>" class="radio-label"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_RECOMMENDATED_NO")?></label>
                                </div>
                            </div>
                        </div>

                        <?if($arParams["UPLOAD_IMAGE"] == 'Y'):?>
                            <?$arFiles = unserialize($arResult["REVIEW"]["FILES"]);?>
                            <div class="add-photo">
                                <input type="file" multiple="multiple" name="photo[]" accept="image/jpeg,image/png">
                                <span id='add-photo-button'>
                                    <i class="fa fa-plus"></i><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_ADD_IMAGES")?>
                                </span>
                            </div>
                            <ul id="preview-photo" data-max-size="<?=$arParams["MAX_IMAGE_SIZE"]?>"
                                data-thumb-width="<?=$arParams["THUMB_WIDTH"]?>"
                                data-thumb-height="<?=$arParams["THUMB_HEIGHT"]?>"
                                data-max-count-images="<?=$arParams["MAX_COUNT_IMAGES"]?>"
                                data-error-max-size="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_ERROR_IMAGE_MAX_SIZE")?>"
                                data-error-type="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_ERROR_IMAGE_TYPE")?>"
                                data-error-max-count="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_ERROR_MAX_COUNT_IMAGES")?>">

                                <?foreach($arFiles as $idFile):?>
                                    <?$arFile = CFile::GetFileArray($idFile);?>
                                    <?$arThumbFile = CFile::ResizeImageGet($idFile, ['width' => $arParams["THUMB_WIDTH"], 'height' => $arParams["THUMB_HEIGHT"]], BX_RESIZE_IMAGE_PROPORTIONAL, true);?>
                                    <li data-id="<?=$arFile["FILE_NAME"]?>">
                                        <span class="img">
                                            <img src="<?=$arThumbFile["src"]?>">
                                        </span>
                                        <span class="delete"><i class="fa fa-times" aria-hidden="true"></i></span>
                                    </li>
                                    <input type="hidden" name="photos[]" data-id="<?=$arFile["FILE_NAME"]?>" value="<?=$idFile?>" />
                                <?endforeach;?>
                            </ul>
                        <?endif;?>
                        <div style="clear:both"></div>

                        <?if($arParams["MULTIMEDIA_VIDEO_ALLOW"] == "Y"): ?>
                            <p class="title-video"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_VIDEO")?></p>
                            <input type="text" name="VIDEO" value="" maxlength="255" class="video" />
                        <?endif; ?>

                        <input class="btn btn-primary" type="submit" name="submit" value="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_SUBMIT_VALUE")?>" />
                        <input class="btn btn-default reset-form" type="button" value="<?=GetMessage("SC_REVIEWS_ADD_REVIEWS_ADD_CANCEL")?>">
                    </form>
                </div>


		    <?else:?>
			    <p class="not-error not-ban-error"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_USER_BAN_TITLE")?></p>
			    <?if(isset($arResult['REASON']) && !empty($arResult['REASON'])): ?>
				    <p class="reason-title"><?=GetMessage("SC_REVIEWS_ADD_REVIEWS_USER_BAN_REASON_TITLE")?></p>
				    <p class="reason-text"><?=$arResult['REASON']?></p>
			    <?endif; ?>
		    <?endif;?>

	    <?endif;?>
    </div>
</div>

<style>
    .add-reviews .spoiler-input{background:<?=$arParams['BUTTON_BACKGROUND']?>}
    .spoiler-reviews-body .review-add-title{color:<?=$arParams['PRIMARY_COLOR']?>}
    .spoiler-reviews-body .add-check-error{color:<?=$arParams['PRIMARY_COLOR']?>;}
    .spoiler-reviews-body .not-buy-error{color:<?=$arParams['PRIMARY_COLOR']?>}
</style>

<?
$this->addExternalCss("/forms/dist/sa/sa.css");
$this->addExternalJS("/forms/dist/sa/sa.js");
?>

<script>
    var ajaxURLEditReview = "<?=$this->__component->GetPath() . '/ajax.php'?>";
    var MaxCountImages = parseInt("<?=$arParams['MAX_COUNT_IMAGES']?>"); 
    var previewWidth = parseInt("<?=$arParams['THUMB_WIDTH']?>"); 
    var previewHeight = parseInt("<?=$arParams['THUMB_HEIGHT']?>");
    var maxFileSize = parseInt("<?=$arParams['MAX_IMAGE_SIZE']?>") * 1024 * 1024; 
</script>
