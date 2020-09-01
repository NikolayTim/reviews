function sendRequestToUpdateReviews(idReviewForScroll = 0)
{
	let jsonParams = decodeURI(JSON.stringify(arParams));
	$.ajax({
		url: ajaxURL,
		data: {	'PARAMS': jsonParams,
				'RATING': $("#current-option-select-rating").attr("data-value"),
				'RECOMMENDATED': $('#recommendated').is(':checked'),
				'PLUS': $('#plus').is(':checked'),
				'MINUS': $('#minus').is(':checked')},
		success: function(data) {
			$('#reviews-list').html($(data).find('#reviews-list').html());
			$('#update-statistics').html($(data).find('#update-statistics').html());

			if(cntReviews > 0 && $('#updated-reviews-list').css('display') == 'none')
				$('#updated-reviews-list').css('display', 'flex');

			if(idReviewForScroll > 0)
			{
				$(document).scrollTop($('#review-' + idReviewForScroll).offset().top);
				$('#count-reviews').html(cntReviews);
				$('#select-rating').html($(data).find('#select-rating').html());
			}
			else if(idReviewForScroll < 0)
			{
				$('div.spoiler-reviews-body').html($(data).find('div.spoiler-reviews-body').html());
			}

			if(parseInt(arParams.NPAGES_SHOW * arParams.COUNT_REVIEWS) < parseInt(cntReviews))
			{
				$(window).on('scroll', function ()
				{
					if ($(window).scrollTop() >= $(document).height() - $(window).height() - 400)
						lazyLoadReviews();
				});
			}
		}
	});
}

function lazyLoadReviews()
{
	if(parseInt(arParams.NPAGES_SHOW * arParams.COUNT_REVIEWS) < parseInt(cntReviews))
	{
		arParams.NPAGES_SHOW = parseInt(arParams.NPAGES_SHOW) + 1;
		sendRequestToUpdateReviews();
	}
	$(window).off('scroll');
}

function showMessageAfterAction(obj, selector, topPosition, leftPosition)
{
	obj.closest('.menu').find(selector).css({'top': topPosition, 'left': leftPosition, 'z-index': 2});
	obj.closest('.menu').find(selector).animate({opacity: 1,}, 500, function()
	{
		setTimeout(function()
		{
			obj.closest('.menu').find(selector).animate({opacity: 0,}, 500, function()
			{
				obj.closest('.menu').find(selector).css({'top':'25px','left':'0','z-index':0});
				if(selector.indexOf('moderate') != -1)
					sendRequestToUpdateReviews();
			});
		}, 2000);
	});
}

$(document).ready(function(){
	$(document).on('change', 'select[name=sort]', function() {
		arParams.ORDER = $(this).val();
		sendRequestToUpdateReviews();
	});

	$(document).on('click', 'a.order-by', function() {
		if($(this).children('i').hasClass('fa-angle-down'))
			arParams.ORDER_BY = "DESC";
		else
			arParams.ORDER_BY = "ASC";

		sendRequestToUpdateReviews();
	});

	$(document).on('click','#current-option-select-rating',
		function(){
			customOptionsBlock = $("#custom-options-select-rating");

			if (customOptionsBlock.is(":hidden"))
			{
				$("#select-rating").attr("class","select-rating-open");
				$("#current-option-select-rating").find('.fa-angle-down').removeClass("fa-angle-down").addClass("fa-angle-up");
			}

			$("#custom-options-select-rating").slideToggle('normal',function()
			{
				if (customOptionsBlock.is(":hidden")) {
					$("#select-rating").attr("class","select-rating-close");
					$("#current-option-select-rating").find('.fa-angle-up').removeClass("fa-angle-up").addClass("fa-angle-down");
				}
			});
		});

	$(document).on('click','#custom-options-select-rating li',
		function(){
			oldValue=$("#current-option-select-rating").attr("data-value");
			choosenValue = $(this).attr("data-value");
			$("#current-option-select-rating span").html($(this).html());
			$("#current-option-select-rating").attr("data-value", choosenValue);

			$("#custom-options-select-rating").slideToggle('normal',function(){
				$("#select-rating").attr("class","select-rating-close");
				$("#current-option-select-rating").find('.fa-angle-up').removeClass("fa-angle-up").addClass("fa-angle-down");
			});

			if(oldValue != choosenValue)
				sendRequestToUpdateReviews();
		});

	$(document).on('click','#recommendated, #plus, #minus', function() {
		sendRequestToUpdateReviews();
	});

	$(window).on('scroll', function ()
	{
		if ($(window).scrollTop() >= $(document).height() - $(window).height() - 400)
			lazyLoadReviews();
	});

	$(document).on('click','#reviews-list .actions', function()
	{
		$(this).closest('.menu').toggleClass('open');
	});

	$(document).on('click','#reviews-list .menu ul li',	function()
	{
		var _this= $(this);
		var Action=$(this).data('action');
		var Top = $(this).position().top - 9;
		var Left = $(this).position().left - 26;
		var r = true;

		console.log('Action:', Action);

		if(Action=='ban')
			r = confirm($(_this).closest('.menu').find('#ban-confirm-text').html().trim());

		if (r != true)
			return;

		$.ajax({
			type: 'POST',
			url: ajaxURL,
			data: {'ID': $(this).closest('.item').data('id'), 'ACTION': Action},
			success: function(data)
			{
				let resAction = '';

				if(data.trim() == 'SUCCESS')
					resAction = 'success';
				else
				{
					resAction = 'error';
					$(_this).closest('.menu').find('.' + Action + '-message-' + resAction).html(data);
				}
				showMessageAfterAction($(_this), '.' + Action + '-message-' + resAction, Top, Left);
			},
			error:  function(xhr, str)
			{
				alert(xhr.responseCode);
			}
		});
	});

	$(document).on('click', '.yes',	function()
	{
		$(this).removeClass("yes").addClass("voted-yes");
		$(this).siblings(".no").removeClass("no").addClass("voted-no");
		let Like = $(this).siblings(".yescnt");

		$.ajax({
			type: 'POST',
			url: ajaxURL,
			data: {'ACTION': 'likes', 'ID': $(this).parent().data('review-id')},
			success: function(data) {
				console.log('data:', data, Like);
				if(parseInt(data) > 0)
					Like.html(data);
				else
					alert('Ошибка! ' + data);
			},
			error:  function(xhr, str){
				alert(xhr.responseCode);
			}
		});
	});

	$(document).on('click', '.no', function()
	{
		$(this).removeClass("no").addClass("voted-no");
		$(this).siblings(".yes").removeClass("yes").addClass("voted-yes");

		let Like = $(this).siblings(".nocnt");

		$.ajax({
			type: 'POST',
			url: ajaxURL,
			data: {'ACTION': 'dislikes', 'ID': $(this).parent().data('review-id')},
			success: function(data) {
				if(parseInt(data) > 0)
					Like.html(data);
				else
					alert('Ошибка! ' + data);
			},
			error:  function(xhr, str){
				alert(xhr.responseCode);
			}
		});
	}); 
});
