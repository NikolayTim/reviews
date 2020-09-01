<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
CJSCore::Init(['jquery']);

global $APPLICATION;
global $USER;

if(!is_object($USER))
    $USER=new CUser;
?>

<style>
    #reviews-statistics h3{color:<?=$arParams['PRIMARY_COLOR']?>}
    #reviews-statistics .reviews-scale-full{background:<?=$arParams['PRIMARY_COLOR']?>;}

    #reviews-body #filter #custom-options-select-rating li:hover,
    #reviews-body #filter #custom-options-select-rating li:hover .uni-stars,
    #reviews-body #filter #custom-options-select-sort li:hover{
        color:<?=$arParams["PRIMARY_COLOR"]?>;
    }

    #reviews-body #filter-pagination button.current {
        color: <?=$arParams['PRIMARY_COLOR']?>;
    }

    .add-reviews .spoiler-input{background:<?=$arParams['BUTTON_BACKGROUND']?>}
    .spoiler-reviews-body .review-add-title{color:<?=$arParams['PRIMARY_COLOR']?>}
    .spoiler-reviews-body .add-check-error{color:<?=$arParams['PRIMARY_COLOR']?>;}
    .spoiler-reviews-body .not-buy-error{color:<?=$arParams['PRIMARY_COLOR']?>}
</style>

<div id="reviews-statistics">
    <div class="text-center" id="update-statistics">
        <h2><?=GetMessage("SC_REVIEWS_TAB_TITLE")?>&nbsp;(<?=$arResult["CNT_REVIEWS"]?>)</h2>
        <div class="card">
            <?if($arResult["CNT_REVIEWS"] > 0):?>
                <p class="h5title"><?=$arResult['RECOMMENDATED']?>% <?=GetMessage("SC_REVIEWS_LIST_REVIEWS_HEADER_RECOMMENDATED")?></p>
                <p class="text-fixed"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_HEADER_FIXED")?> <?=$arResult["CNT_REVIEWS"]?>
                    <?=GetMessage("SC_REVIEWS_LIST_REVIEWS_HEADER_FIXED2")?></p>
                <p class="mid-rating"><?=$arResult['MID_REVIEW']?> <?=GetMessage("SC_REVIEWS_LIST_REVIEWS_HEADER_FROM")?>
                    <?=$arParams["MAX_RATING"]?></p>
            <?endif;?>
            <div class="empty-stars" style="width:<?=$arParams["MAX_RATING"]*27?>px;">
                <div class="full-stars" style="width:<?=round($arResult['MID_REVIEW'] / $arParams["MAX_RATING"] * 100, 2)?>%"></div>
            </div>
            <?if($arResult["CNT_REVIEWS"] > 0):?>
                <?for($i = $arParams["MAX_RATING"]; $i >= 1; --$i):?>
                    <?
                    if(array_key_exists($i, $arResult['STATISTICS']))
                        $curRate = $arResult['STATISTICS'][$i];
                    else
                        $curRate = 0;
                    ?>

                    <div class="review-line">
                        <div class="col cnt-stars">
                            <div class="stars-text"><?=$i?> <?=GetMessage("SC_REVIEWS_LIST_REVIEWS_HEADER_STAR".$i)?></div>
                        </div>
                        <div class="col">
                            <div class="reviews-scale-empty">
                                <div class="reviews-scale-full"
                                     style="width:<?=round($curRate / $arResult["CNT_REVIEWS"] * 100)?>%;">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="reviews-count-block"><?=$curRate?></div>
                        </div>
                    </div>
                <?endfor;?>
            <?else:?>
                <p class="no-reviews-title1"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_NO_REVIEW_TITLE1")?> </p>
                <p class="no-reviews-title2"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_NO_REVIEW_TITLE2")?> </p>
            <?endif;?>
        </div>
        <div class="border"></div>
    </div>

    <?$APPLICATION->IncludeComponent(
        "sc.reviews:reviews.add",
        "",
        Array(
            "ID_ELEMENT" => $arParams["ID_ELEMENT"],
            "ID" => "0",
            "MAX_RATING" => $arParams["MAX_RATING"],
            "DEFAULT_RATING_ACTIVE" => "3",
            "PRIMARY_COLOR" => $arParams["PRIMARY_COLOR"],
            "BUTTON_BACKGROUND" => "#c61919",
            "BUTTON_TEXT" => GetMessage("SC_REVIEWS_LIST_BUTTON_ADD_TEXT"),
            "TEXTBOX_MAXLENGTH" => "200",
            "NOTICE_EMAIL" => "",
            "AUTHORIZED_USER" => $arParams["AUTHORIZED_USER"],
            "UPLOAD_IMAGE" => $arParams["UPLOAD_IMAGE"],
            "MAX_IMAGE_SIZE" => $arParams["MAX_IMAGE_SIZE"],
            "THUMB_WIDTH" => $arParams["THUMB_WIDTH"],
            "THUMB_HEIGHT" => $arParams["THUMB_HEIGHT"],
            "MAX_COUNT_IMAGES" => $arParams["MAX_COUNT_IMAGES"],
            "MULTIMEDIA_VIDEO_ALLOW" => $arParams["MULTIMEDIA_VIDEO_ALLOW"],
            "CACHE_TYPE" => "N",
            "CACHE_TIME" => $arParams["CACHE_TIME"]
        ),
        $component
    );
    ?>
</div>

<div class="card" <?if($arResult["CNT_REVIEWS"] <= 0):?>style="display: none"<?endif;?> id="updated-reviews-list">
    <div class="card-block">
        <div class="tabs">
            <ul class="tabs-caption" data-id-element="<?=$arParams['ID_ELEMENT']?>" data-site-dir="<?=SITE_DIR?>">
                    <li  id="reviews" data-ajax="N" class="active">
                        <i class="fa  fa-star"></i><?=GetMessage("SC_REVIEWS_TAB_TITLE")?> (<span id="count-reviews"><?=$arResult["CNT_REVIEWS"]?></span>)</li>
            </ul>
        </div>

        <div class="tabs-content active" id="reviews-body">

            <div class="panel-top" id="filter" data-url="<?=$APPLICATION->GetCurPage()?>"
                 data-max-rating="<?=$arParams["MAX_RATING"]?>" data-id-element="<?=$arParams["ID_ELEMENT"]?>"
                 data-site-dir="<?=SITE_DIR?>" data-template="<?=$templateName?>" >
                <div class="border">
                    <p class="filter"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_FILTER")?></p>

                    <div id="select-rating" class="select-rating-close">
                        <div id="current-option-select-rating" data-value="-1">
                            <span><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_FILTER_GENERAL_RATING")?> (<?=$arResult["CNT_REVIEWS"]?>)</span>
                            <b><i class="fa fa-angle-down"></i>
                            </b>
                        </div>

                        <ul id="custom-options-select-rating">
                            <li data-value="-1"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_FILTER_GENERAL_RATING")?> (<?=$arResult["CNT_REVIEWS"]?>)</li>
                            <?for($i = 1; $i <= $arParams["MAX_RATING"]; ++$i):?>
                                <?$cntRate = 0;?>
                                <?if(array_key_exists($i, $arResult["STATISTICS"]))
                                    $cntRate = intval($arResult["STATISTICS"][$i]);?>

                                <li data-value="<?=$i?>">
                                    <span class="stars-in">
                                        <?for($k = 1; $k <= $i; ++$k):?>
                                            <span class="uni-stars"><i class="fa fa-star" aria-hidden="true"></i></span>
                                        <?endfor;?>
                                    </span>
                                    (<?=$cntRate?>)
                                </li>
                            <?endfor;?>
                        </ul>
                    </div>

                    <p class="filter"><?=GetMessage("SC_REVIEWS_TITLE_HAVE_RECOMMENDATED")?>
                        <input type="checkbox" name="recommendated" id="recommendated" />
                    </p>
                    <p class="filter"><?=GetMessage("SC_REVIEWS_TITLE_HAVE_PLUS")?>
                        <input type="checkbox" name="plus" id="plus" />
                    </p>
                    <p class="filter"><?=GetMessage("SC_REVIEWS_TITLE_HAVE_MINUS")?>
                        <input type="checkbox" name="minus" id="minus" />
                    </p>

                    <div class="sort-group">
                        <p class="filter-sort-text" id="sortIcons"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_FILTER_SORT")?>
                            <a href="javascript:" class="order-by">
                                <i class="fa fa-angle-down" aria-hidden="true"></i>
                            </a>
                            <a href="javascript:" class="order-by">
                                <i class="fa fa-angle-up" aria-hidden="true"></i>
                            </a>
                        </p>
                        <div id="select-sort" class="select-sort-close">

                            <select name="sort">
                                <option value="DATE_CREATION"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_SORT_DATE_CREATION")?></option>
                                <option value="RATING"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_SORT_RATING")?></option>
                                <option value="RECOMMENDATED"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_SORT_RECOMMENDATED")?></option>
                                <option value="REST_DATE_FROM_VAL_DATE"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_SORT_REST_DATE_FROM")?></option>
                                <option value="REST_DATE_TO_VAL_DATE"><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_SORT_REST_DATE_TO")?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="reviews-list" data-primary-color="<?=$arParams['PRIMARY_COLOR']?>" data-order="" data-order-by="">
                <div class="list-row">
                    <div class="list" data-items-count="<?=$arResult["REVIEWS_FILTER_CNT"]?>" data-date-format="<?=$arParams['DATE_FORMAT']?>">

                        <?foreach($arResult['REVIEWS'] as $Review):?>
                            <?$moderated = $Review['MODERATED'] == 'Y' ? '' : ' nomoderated'?>
                            <? $Review['NAME'] = $Review['ADD_FIELDS']['FIO'] ?: $Review['NAME']; ?>

                            <div data-id="<?=$Review['ID']?>" data-site-dir="<?=SITE_DIR?>" class="item<?=$moderated?>"
                                id="review-<?=$Review['ID']?>" itemscope itemtype="http://schema.org/Review">

                                <?if($moderated):?>
                                    <div class="nomoderated__note">
                                        <i class="fa fa-exclamation" aria-hidden="true"></i> Отзыв ожидает модерации
                                    </div>
                                <?endif;?>

                                <span itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">
                                    <span itemprop="name" class="dnone">
                                        <?=$arResult["ELEMENT_NAME"]?>
                                    </span>
                                </span>

                                <div class="table">
                                    <div class="user-info">
                                        <div>
                                            <div class="username" itemprop="author" itemscope
                                                 itemtype="http://schema.org/Person">
                                                <span itemprop="name">
                                                    <?=$Review['FIO_VAL']?>
                                                </span>
                                            </div>
                                        </div>

                                        <?if(isset($Review['AGE']) && !empty($Review['AGE'])):?>
                                            <div class="age">
                                                <span><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_AGE")?></span><?=$Review['AGE']?>
                                            </div>
                                        <?endif;?>

                                        <?if(isset($Review['COUNTRY']) && !empty($Review['COUNTRY'])):?>
                                            <div class="country">
                                                <span><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_COUNTRY")?></span><?=$Review['COUNTRY']?>
                                            </div>
                                        <?endif;?>

                                        <div class="clearfix"></div>
                                        <?if(!empty($Review['REST_DATE_FROM_VAL']) && !empty($Review['REST_DATE_TO_VAL'])):?>
                                            <div class="restDates">
                                                <div class="badge">
                                                    <?if(isset($Review['REST_DATE_FROM_VAL'])):?>
                                                        <span  class="REST_DATE_FROM">
                                                            <?=$arResult["USERFIELDS"]['REST_DATE_FROM_VAL']?>
                                                        </span>
                                                        <span><?=$Review['REST_DATE_FROM_VAL']?></span>
                                                    <?endif;?>

                                                    <?if(isset($Review['REST_DATE_TO_VAL']) && !empty($Review['REST_DATE_TO_VAL'])):?>
                                                        <span  class="REST_DATE_TO">
                                                            <?=$arResult["USERFIELDS"]['REST_DATE_TO_VAL']?>
                                                        </span>
                                                        <span><?=$Review['REST_DATE_TO_VAL']?></span>
                                                    <?endif;?>
                                                </div>
                                            </div>
                                        <?endif;?>

                                        <div class="rec">
                                            <?if(isset($Review['RECOMMENDATED']) && $Review['RECOMMENDATED']=='Y'):?>
                                                <i class="fa fa-check"></i><?=GetMessage("SC_REVIEWS_LIST_REVIEWS_I_RECOMMENDATED")?>
                                            <?endif; ?>
                                        </div>
                                    </div>

                                    <div class="text">
                                        <?if($APPLICATION->GetGroupRight('sc.reviews') >= "T"):?>
                                            <div class="menu">
                                                <div class="ban-message-success message message-success">
                                                    <?=GetMessage("SC_REVIEWS_LIST_BAN_USER_SUCCESS") ?>
                                                </div>
                                                <div class="ban-message-error message message-error">
                                                    <?=GetMessage("SC_REVIEWS_LIST_BAN_USER_ERROR") ?>
                                                </div>
                                                <div class="moderate-message-success message message-success">
                                                    <?=GetMessage("SC_REVIEWS_LIST_MODERATE_USER_SUCCESS") ?>
                                                </div>
                                                <div class="moderate-message-error message message-error">
                                                </div>
                                                <div class="cancel_moderate-message-success message message-success">
                                                    <?=GetMessage("SC_REVIEWS_LIST_CANCEL_MODERATE_USER_SUCCESS") ?>
                                                </div>
                                                <div class="cancel_moderate-message-error message message-error">
                                                </div>

                                                <div style="display:none" id="ban-confirm-text">
                                                    <?=GetMessage("SC_REVIEWS_LIST_BAN_USER_CONFIRM") ?>
                                                </div>
                                                <i class="fa fa-cog actions"></i>
                                                <ul>
                                                    <li data-action="ban">
                                                        <?=GetMessage("SC_REVIEWS_LIST_BAN_USER") ?>
                                                    </li>
                                                    <?if(isset($Review["MODERATED"]) && $Review["MODERATED"] == "Y"):?>
                                                        <li data-action="cancel_moderate">
                                                            <?=GetMessage("SC_REVIEWS_LIST_CANCEL_MODERATE") ?>
                                                        </li>
                                                    <?else:?>
                                                        <li data-action="moderate">
                                                            <?=GetMessage("SC_REVIEWS_LIST_MODERATE") ?>
                                                        </li>
                                                    <?endif;?>
                                                </ul>
                                            </div>
                                        <?endif;?>

                                        <?if(\Bitrix\Main\Config\Option::getRealValue("sc.reviews", "REVIEWS_TITLE", SITE_ID) == "Y"):?>
                                            <p class="title"><?=$Review["TITLE"]?></p>
                                        <?endif;?>

                                        <?$dateSplit = explode('.',$Review['DATE_CREATION'])?>
                                        <div class="time" itemprop="datePublished"
                                             content="<?=substr($dateSplit[2], 0, 4).'-'.$dateSplit[1].'-'.$dateSplit[0]?>">
                                            <i class="fa fa-clock-o"></i><?=$dateSplit[0] . "." . $dateSplit[1] . "." .
                                            substr($dateSplit[2], 0, 4)?>
                                        </div>

                                        <div class="rating">
                                            <div class="stars star<?=$Review['RATING']?>">
                                                <span class="stars-sort"><?=$Review['RATING']?></span>
                                                <?for($i=1; $i<=$Review["RATING"]; ++$i):?>
                                                    <i class="fa fa-star <?=($i <= $Review['RATING']) ? 'full' : 'empty'?>"></i>
                                                <?endfor;?>
                                            </div>
                                            <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
                                                <span itemprop="ratingValue" class="dnone">
                                                    <?=$Review['RATING']?>
                                                </span>
                                                <span itemprop="bestRating" class="dnone">
                                                    <?=$arParams['MAX_RATING']?>
                                                </span>
                                                <span itemprop="worstRating" class="dnone">
                                                    1
                                                </span>
                                            </span>
                                        </div>

                                        <div class='reviewAddField'>
                                            <span class="reviewAddField__iconTitle reviewAdd__iconTitle--plus" title="<?=GetMessage("SC_REVIEWS_TITLE_PLUS")?>">
                                                <img src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCIgdmlld0JveD0iMCAwIDQwMS45OTQgNDAxLjk5NCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDAxLjk5NCA0MDEuOTk0OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPHBhdGggZD0iTTM5NCwxNTQuMTc1Yy01LjMzMS01LjMzLTExLjgwNi03Ljk5NC0xOS40MTctNy45OTRIMjU1LjgxMVYyNy40MDZjMC03LjYxMS0yLjY2Ni0xNC4wODQtNy45OTQtMTkuNDE0ICAgQzI0Mi40ODgsMi42NjYsMjM2LjAyLDAsMjI4LjM5OCwwaC01NC44MTJjLTcuNjEyLDAtMTQuMDg0LDIuNjYzLTE5LjQxNCw3Ljk5M2MtNS4zMyw1LjMzLTcuOTk0LDExLjgwMy03Ljk5NCwxOS40MTR2MTE4Ljc3NSAgIEgyNy40MDdjLTcuNjExLDAtMTQuMDg0LDIuNjY0LTE5LjQxNCw3Ljk5NFMwLDE2NS45NzMsMCwxNzMuNTg5djU0LjgxOWMwLDcuNjE4LDIuNjYyLDE0LjA4Niw3Ljk5MiwxOS40MTEgICBjNS4zMyw1LjMzMiwxMS44MDMsNy45OTQsMTkuNDE0LDcuOTk0aDExOC43NzFWMzc0LjU5YzAsNy42MTEsMi42NjQsMTQuMDg5LDcuOTk0LDE5LjQxN2M1LjMzLDUuMzI1LDExLjgwMiw3Ljk4NywxOS40MTQsNy45ODcgICBoNTQuODE2YzcuNjE3LDAsMTQuMDg2LTIuNjYyLDE5LjQxNy03Ljk4N2M1LjMzMi01LjMzMSw3Ljk5NC0xMS44MDYsNy45OTQtMTkuNDE3VjI1NS44MTNoMTE4Ljc3ICAgYzcuNjE4LDAsMTQuMDg5LTIuNjYyLDE5LjQxNy03Ljk5NGM1LjMyOS01LjMyNSw3Ljk5NC0xMS43OTMsNy45OTQtMTkuNDExdi01NC44MTlDNDAxLjk5MSwxNjUuOTczLDM5OS4zMzIsMTU5LjUwMiwzOTQsMTU0LjE3NXoiIGZpbGw9IiNGRkZGRkYiLz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K" />
                                            </span>
                                            <div class="reviewAddField__fieldText PLUS">
                                                <?=$Review["PLUS"]?>
                                            </div>
                                        </div>
                                        <div class='reviewAddField'>
                                            <span class="reviewAddField__iconTitle reviewAdd__iconTitle--minus" title="<?=GetMessage("SC_REVIEWS_TITLE_PLUS")?>">
                                                <img src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDI5NyAyOTciIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDI5NyAyOTc7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iMTZweCIgaGVpZ2h0PSIxNnB4Ij4KPGc+Cgk8cGF0aCBkPSJNMjk0LjA4OCw5OS41MmMtMS44NjUtMS44NjUtNC4zOTUtMi45MTMtNy4wMzItMi45MTNMOS45NTUsOTYuNjJjLTUuNDkxLDAtOS45NDIsNC40NTEtOS45NDMsOS45NDNMMCwxOTAuNDQ4ICAgYzAsMi42MzgsMS4wNDcsNS4xNjgsMi45MTIsNy4wMzJjMS44NjUsMS44NjUsNC4zOTQsMi45MTMsNy4wMzMsMi45MTNsMjc3LjEtMC4wMTZjNS40OTEsMCw5Ljk0Mi00LjQ1MSw5Ljk0My05Ljk0M0wyOTcsMTA2LjU1MiAgIEMyOTcsMTAzLjkxNCwyOTUuOTUzLDEwMS4zODQsMjk0LjA4OCw5OS41MnoiIGZpbGw9IiNGRkZGRkYiLz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K" />
                                            </span>
                                            <div class="reviewAddField__fieldText MINUS">
                                                <?=$Review["MINUS"]?>
                                            </div>
                                        </div>

                                        <?if(isset($arParams["UPLOAD_IMAGE"]) && $arParams["UPLOAD_IMAGE"] == "Y"):?>
                                            <ul class="images-reviews">
                                                <?$arFiles = unserialize($Review["FILES"]);
                                                foreach($arFiles as $valFile)
                                                {
                                                    $arFile = CFile::GetFileArray($valFile);
                                                    $arThumbFile = CFile::ResizeImageGet($arFile,
                                                        array('width' => $arParams["THUMB_WIDTH"], 'height' => $arParams["THUMB_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, true);

                                                    if(isset($arThumbFile["src"]) && strlen($arThumbFile["src"]) > 0):
                                                    ?>
                                                        <a href="<?=$arFile["SRC"]?>" class="image-review" rel="<?=$Review['ID']?>">
                                                            <img src="<?=$arThumbFile['src']?>" class="img-responsive">
                                                        </a>
                                                    <?endif;?>
                                                <?}?>

                                            </ul>
                                        <?endif;?>

                                        <div class="likes">
                                            <div class="vote" data-review-id="<?=$Review['ID']?>"
                                                 data-site-dir="<?=SITE_DIR?>">
                                                <p class="likes-title">
                                                    <?=GetMessage('SC_REVIEWS_LIST_REVIEWS_LIKES_TITLE')?>
                                                </p>

                                                <?if(isset($Review['ID']) && !empty($Review['ID']) && isset($_COOKIE['LIKE']) &&
                                                    is_array($_COOKIE['LIKE']) && in_array($Review['ID'],$_COOKIE['LIKE'])):?>
                                                    <div class="voted-yes"></div>
                                                <?else:?>
                                                    <div class="yes"></div>
                                                <?endif;?>

                                                <span class="yescnt"><?=$Review['LIKES']?></span>

                                                <?if(isset($Review['ID']) && !empty($Review['ID']) && isset($_COOKIE['LIKE']) &&
                                                    is_array($_COOKIE['LIKE']) && in_array($Review['ID'],$_COOKIE['LIKE'])):?>
                                                    <div class="voted-no"></div>
                                                <?else:?>
                                                    <div class="no"></div>
                                                <?endif;?>

                                                <span class="nocnt"><?=$Review['DISLIKES']?></span>
                                            </div>
                                        </div>

                                        <?if($USER->IsAuthorized() && $APPLICATION->GetGroupRight('sc.reviews') >= "V"):?>
                                            <a class="item__edit" href="/bitrix/admin/sc.reviews_reviews_edit.php?ID=<?=$Review['ID']?>" target="_blank">
                                                <i class="fa fa-pencil" aria-hidden="true"></i><?=GetMessage("SC_REVIEWS_LIST_BUTTON_EDIT")?>
                                            </a>
                                        <?endif;?>

                                    </div>
                                </div>

                                        <?$APPLICATION->IncludeComponent(
                                            "sc.reviews:reviews.add",
                                            "",
                                            Array(
                                                "ID_ELEMENT" => $arParams["ID_ELEMENT"],
                                                "ID" => $Review["ID"],
                                                "MAX_RATING" => $arParams["MAX_RATING"],
                                                "DEFAULT_RATING_ACTIVE" => "3",
                                                "PRIMARY_COLOR" => $arParams["PRIMARY_COLOR"],
                                                "BUTTON_BACKGROUND" => "#c61919",
                                                "BUTTON_TEXT" => GetMessage("SC_REVIEWS_LIST_BUTTON_EDIT_TEXT"),
                                                "TEXTBOX_MAXLENGTH" => "200",
                                                "NOTICE_EMAIL" => "",
                                                "AUTHORIZED_USER" => $arParams["AUTHORIZED_USER"],
                                                "UPLOAD_IMAGE" => $arParams["UPLOAD_IMAGE"],
                                                "MAX_IMAGE_SIZE" => $arParams["MAX_IMAGE_SIZE"],
                                                "THUMB_WIDTH" => $arParams["THUMB_WIDTH"],
                                                "THUMB_HEIGHT" => $arParams["THUMB_HEIGHT"],
                                                "MAX_COUNT_IMAGES" => $arParams["MAX_COUNT_IMAGES"],
                                                "MULTIMEDIA_VIDEO_ALLOW" => $arParams["MULTIMEDIA_VIDEO_ALLOW"],
                                                "CACHE_TYPE" => $arParams["CACHE_TYPE"], 
                                                "CACHE_TIME" => $arParams["CACHE_TIME"] 
                                            ),
                                            $component
                                        );?>
                            </div>
                        <?endforeach;?>
                    </div>
                </div>

                <div id="idsReviews" style="display:none" data-site-dir="<?=SITE_DIR?>"><?=$arResult['REVIEWS_IDS']?></div>
            </div>
        </div>
    </div>
</div>

<script>
    var ajaxURL = "<?=$this->__component->GetPath() . '/ajax.php'?>";
    var arParams = <?=CUtil::PhpToJSObject($arParams)?>;
    var cntReviews = parseInt('<?=$arResult["CNT_REVIEWS"]?>');
</script>
