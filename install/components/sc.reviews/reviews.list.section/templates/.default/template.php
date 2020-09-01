<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arMonths = [
    '1' => 'Январь',
    '2' => 'Февраль',
    '3' => 'Март',
    '4' => 'Апрель',
    '5' => 'Май',
    '6' => 'Июнь',
    '7' => 'Июль',
    '8' => 'Август',
    '9' => 'Сентябрь',
    '10' => 'Октябрь',
    '11' => 'Ноябрь',
    '12' => 'Декабрь'
];
?>
<div class="col-lg-8">
    <div class="card">
        <div class="card-body">
            <?=$arResult['SECTION']['DESCRIPTION']?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2><?=GetMessage('REVIEWS_LIST_SECTION_LAST_REVIEWS')?><?=$arResult['SECTION']['NAME']?></h2>
            <hr>
            <div id="reviews-container">
                <?foreach($arResult['REVIEWS'] as $arReview):?>
                    <div class="review">
                        <div class="review-header row">
                            <div class="col-lg-4 col-md-6">
                                <div class="accom-box clearfix">
                                    <div class="accom-thumb">
                                        <?
                                        $arFile = CFile::GetFileArray($arResult['OBJECTS'][$arReview['ID_ELEMENT']]["PREVIEW_PICTURE"]);
                                        ?>
                                        <a class="img-cover" style="background-image: url('http://194.67.92.166'<?=$arFile['SRC']?>)"
                                           title="" href="<?=$arResult['OBJECTS'][$arReview['ID_ELEMENT']]["DETAIL_PAGE_URL"]?>">
                                            <img class="hide" src="http://194.67.92.166<?=$arFile['SRC']?>"
                                                 alt="<?=$arResult['OBJECTS'][$arReview['ID_ELEMENT']]['NAME']?>">
                                        </a>
                                    </div>
                                    <a title="" href="<?=$arResult['OBJECTS'][$arReview['ID_ELEMENT']]["DETAIL_PAGE_URL"]?>">
                                        <?=$arResult['OBJECTS'][$arReview['ID_ELEMENT']]['NAME']?>
                                    </a>
                                </div>
                            </div>

                            <div class="col-lg-8 col-md-6">
                                <div class="review__title">
                                                <span class="review__guest-name">
                                                    <?=$arReview['FIO_VAL']?>,
                                                </span>
                                    <span class="review__guest-place-from">
                                                    Москва
                                                </span>
                                </div>
                                <div class="rest__period">
                                    <?$arDate = explode('.', $arReview['REST_DATE_FROM_VAL']);?>
                                    <?=GetMessage('REVIEWS_LIST_SECTION_PERIOD')?><?=$arMonths[intval($arDate[1])]?> <?=$arDate[2]?>
                                </div>
                                <div class="review__rating">
                                    <?for($i = 1; $i <= intval($arReview['RATING']); $i++):?>
                                        <i class="fas fa-star"></i>
                                    <?endfor;?>
                                    <?=$i - 1?>,0
                                </div>
                            </div>
                        </div>
                        <div class="review__body">
                            <p><?=$arReview['PLUS']?></p>
                            <p><?=$arReview['MINUS']?></p>
                            <div class="review-grade">
                                <?=GetMessage('REVIEWS_LIST_SECTION_USEFUL')?>
                                <a class="btn btn-outline-secondary btn-grape" href="#">
                                    <i class="fa fal fa-thumbs-up"></i><?=GetMessage('REVIEWS_LIST_SECTION_YES')?>
                                </a>
                                <?=GetMessage('REVIEWS_LIST_SECTION_OR')?>
                                <a class="btn btn-outline-secondary btn-grape" href="#">
                                    <i class="fa fal fa-thumbs-down"></i><?=GetMessage('REVIEWS_LIST_SECTION_NO')?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?endforeach;?>
                <a class="btn btn-primary outline" href="#"
                   title="<?=GetMessage('REVIEWS_LIST_SECTION_REVIEWS')?><?=$arResult['SECTION']['NAME']?>">
                    <?=GetMessage('REVIEWS_LIST_SECTION_READ')?>
                </a>
            </div>
        </div>
    </div>
</div>
