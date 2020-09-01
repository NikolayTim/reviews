function showMessageAndUpdateReviewsList(txtMess, txtData, objForm = 0)
{
	let idReview = 0;
	if(objForm !== 0)
	{
		idReview = parseInt(txtData.substr(txtData.indexOf(':') + 1));
		objForm.find('.reset-form').click();
	}

	swal({title: '', html: txtMess,	type: 'success'});
	sendRequestToUpdateReviews(idReview);
}

function showErrorMessage(txtMess, selector, objForm)
{
	objForm.find(selector).html(txtMess);
	objForm.find(selector).show();
	$(document).scrollTop(objForm.find(selector).offset().top);
}

$(document).ready(function(){
	$(document).on('click', '.review .date-picker input.date-picker-from', function ()
	{
		let dateTo = $(this).parents('form.add_review').find('input.date-picker-to');
		dateTo.datepicker({
			defaultDate: new Date(),
			value: new Date(),
			format: 'dd.mm.yyyy',
			changeYear: true,
			changeMonth: true,
			maxDate: "0",
			onClose: function (date, inst)
			{
				let dateFrom = $(this).parents('form.add_review').find('input.date-picker-from');
				if(dateFrom.val() == '') 
					dateFrom.click();
				else if($(this).datepicker("getDate") < dateFrom.datepicker("getDate"))
					dateFrom.click();
			}
		});

		$(this).datepicker({
			defaultDate: new Date(),
			value: new Date(),
			format: 'dd.mm.yyyy',
			changeYear: true,
			changeMonth: true,
			maxDate: "0",
			onClose: function (date, inst)
			{
				if(dateTo.val() == '') 
					dateTo.click();
				else if($(this).datepicker("getDate") > dateTo.datepicker("getDate"))
					dateTo.click();
			}
		});
		$(this).datepicker('show');
	});

	$(document).on('click', '.review .date-picker input.date-picker-to', function ()
	{
		let dateFrom = $(this).parents('form.add_review').find('input.date-picker-from');
		dateFrom.datepicker({
			defaultDate: new Date(),
			value: new Date(),
			format: 'dd.mm.yyyy',
			changeYear: true,
			changeMonth: true,
			maxDate: "0",
			onClose: function (date, inst)
			{
				let dateTo = $(this).parents('form.add_review').find('input.date-picker-to');
				if(dateTo.val() == '')
					dateTo.click();
				else if(dateTo.datepicker("getDate") < $(this).datepicker("getDate"))
					dateTo.click();
			}
		});

		$(this).datepicker({
			defaultDate: new Date(),
			value: new Date(),
			format: 'dd.mm.yyyy',
			changeYear: true,
			changeMonth: true,
			maxDate: "0",
			onClose: function (date, inst)
			{
				if(dateFrom.val() == '')
					dateFrom.click();
				else if(dateFrom.datepicker("getDate") > $(this).datepicker("getDate"))
					dateFrom.click();
			}
		});
		$(this).datepicker('show');
	});

	$(document).on('click', '.review .date-picker input.date-picker-creation', function ()
	{
		$(this).datepicker({
			value: new Date($(this).val()),
			format: 'dd.mm.yyyy',
			changeYear: true,
			changeMonth: true,
			maxDate: "0"
		});
		$(this).datepicker('show');
	});

    $(document).on('click','.spoiler', function(e){
    	$('.spoiler-reviews-body').each(function() {
    		$(this).removeClass('open').css('display', 'none');
		});

        $(this).parent().next('.spoiler-reviews-body').addClass('open').toggle('normal');
		return false;
    });

	$(document).on('keyup','.add_review  #contentbox',function()
		{
			var MaxInput=$(this).attr('maxlength');
			var box=$(this).val();
			if(box.length <= MaxInput)
			{
				$('.add_review').find('.count-now').html(box.length);
			}
			else{}
			return false;
	});

	$(document).on('click',".add_review > .reset-form",function() {
		$(this).closest('.add_review').find('#review-editor').find('iframe').contents().find('body').empty();
		$('.spoiler-reviews-body').find('.count-now').html(0);
		$('.spoiler-reviews-body').find('#preview-photo').empty();
		$('.add_review')[0].reset();
        $('.spoiler-reviews-body').hide(600);

	});

	$(document).on('click', 'div.radio',  function() {
		$(this).siblings().each(function() {
			$(this).prop('checked', false);
		});
		$(this).prop('checked', 'checked');
	});

	$(document).on('submit',".auth_review, .add_review, .registration_review", function()
	{
		let _this = $(this);
		let formData = new FormData($(this)[0]);
		let jsonParams = decodeURI(JSON.stringify(arParams)); 

		formData.append("PARAMS", jsonParams);
		if($(_this)[0].classList.contains('registration_review'))
		{
			formData.append("arparams", $(_this).attr("data-params"));
			formData.append("register_submit_button", "register_submit_button");
		}

		$.ajax({
			type: 'POST',
			url: ajaxURLEditReview,
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (data)
			{
				console.log('data:', data);

				let curAddReview = $(_this).parents('div.add-reviews');
				let textMessage = '';

				if ($(_this)[0].classList.contains('add_review'))
				{
					if (data.indexOf('Добавлен отзыв с ID:') !== -1)
					{
						cntReviews = cntReviews + 1;
						if (cntReviews > 1)
							arParams.NPAGES_SHOW = parseInt(arParams.NPAGES_SHOW) + 1;

						textMessage = 'Спасибо, Ваш отзыв добавлен.<br/> Он появится на сайте после проверки модератором';
						showMessageAndUpdateReviewsList(textMessage, data, _this);
					}
					else if (data.indexOf('Обновлен отзыв с ID:') !== -1)
					{
						textMessage = 'Спасибо, Ваш отзыв обновлен.<br/> Он появится на сайте после проверки модератором';
						showMessageAndUpdateReviewsList(textMessage, data, _this);
					}
					else if (data.indexOf('BAN') !== -1)
						sendRequestToUpdateReviews(-1); 
					else if (data.indexOf('Не заполнено обязательное поле "BX_USER_ID"') !== -1)
					{
						swal({title: '', html: 'При добавлении отзыва произошла ошибка!<br>Страница будет обновлена', type: 'error'});
						window.location.reload();
					}
					else
						showErrorMessage(data, ".add-check-error", _this.parents('div.spoiler-reviews-body'));
				}
				else if ($(_this)[0].classList.contains('auth_review')) 
				{
					textMessage = 'Вы вошли на сайт.<br/> Теперь вы можете оставить отзыв.';
					showMessageAndUpdateReviewsList(textMessage, data);

					$(_this).parents('div.add-reviews').html($(data).html());
					$(curAddReview).find('.spoiler-reviews-body').css('display', 'block'); 
				}
				else if ($(_this)[0].classList.contains('registration_review')) 
				{
					if(data.indexOf('<div class="add-reviews card">') !== -1) 
					{
						textMessage = 'Вы успешно зарегистрированы. Вам необходимо подтвердить email';
						showMessageAndUpdateReviewsList(textMessage, data);

						$(_this).parents('div.add-reviews').html($(data).html());
						$(curAddReview).find('.spoiler-reviews-body').css('display', 'block'); 
					}
					else
						showErrorMessage(data, "#registration_review-check-error", _this.parents('div.spoiler-reviews-body'));
				}
			},
			statusCode: {
				401: function(data) {
					if ($(_this)[0].classList.contains('auth_review')) 
						showErrorMessage(data.responseText, "#auth_review-check-error", _this.parents('div.spoiler-reviews-body'));
					else
						swal({title: 'Ошибка', html: data.responseText, type: 'error'});
				}
			},
			error: function (jqXHR, exception)
			{
				if(jqXHR.status != 401)
					swal({title: 'Ошибка', html: jqXHR.responseText, type: 'error'});
			}
		});
	});

	$('body').on('change','[name="REGISTER[EMAIL]"]',function(){
		var userLogin = $(this).val(),
			regLoginInput =	$('[name="REGISTER[LOGIN]"]');
		regLoginInput.val(userLogin);
	});

	function change_captcha(e,SiteDir)
	{
		$.ajax({
			type: 'POST',
			url: SiteDir+'local/components/shaggy/reviews.reviews.add/ajax/change_captcha.php',
			success: function(data){
				e.find("input[name='captcha_sid']").val(data);
				e.find("img").attr({"src":"/bitrix/tools/captcha.php?captcha_sid="+data});
			},
			error:  function(xhr, str){
				alert(xhr.responseCode);
			}
		});
	}

	$(document).on('click', 'div.spoiler-reviews-body #add-photo-button', function() {
		$(this).siblings('input[type=file][name="photo[]"]').click();
	});

    var MaxCountImages = parseInt(arParams.MAX_COUNT_IMAGES); 
    var previewWidth = parseInt(arParams.THUMB_WIDTH); 
    var previewHeight = parseInt(arParams.THUMB_HEIGHT); 
    var maxFileSize = parseInt(arParams.MAX_IMAGE_SIZE) * 1024 * 1024; 
	var selectedFiles = {},
	queue = [],
	image = new Image(),
	imgLoadHandler,
	isProcessing = false,
	errorMsg, 
	previewPhotoContainer = document.querySelector('#preview-photo'); 

	$(document).on('change', 'input[type=file][name="photo[]"]', function() {
		var newFiles = $(this)[0].files; 

		previewPhotoContainer = $(this).parent('div.add-photo').siblings('ul')[0];
		if(($(previewPhotoContainer).children('li').size()+newFiles.length)>MaxCountImages)
		{
			alert($(previewPhotoContainer).data('error-max-count')+' - '+MaxCountImages);
			return;
		}

		for (var i = 0; i < newFiles.length; i++) {

			var file = newFiles[i];

			if (selectedFiles[file.name] != undefined) continue;

			if ( errorMsg = validateFile(file) ) {
				alert(errorMsg);
				return;
			}

			selectedFiles[file.name] = file;
			queue.push(file);
		}

		processQueue();
	});

	var validateFile = function(file)
	{
		if ( !file.type.match(/image\/(jpeg|jpg|png)/) ) {
			return $(previewPhotoContainer).data('error-type');
		}

		if ( file.size > maxFileSize ) {
			return $(previewPhotoContainer).data('error-max-size') + ' ' + $(previewPhotoContainer).data('max-size')+' Mb';
		}

	};

	var listen = function(element, event, fn) {
		return element.addEventListener(event, fn, false);
	};

	var processQueue = function()
	{
		if (isProcessing) { return; }

		if (queue.length == 0) {
			isProcessing = false;
			return;
		}

		isProcessing = true;

		var file = queue.pop(); 

		var li = document.createElement('LI');
		var span = document.createElement('SPAN');
		var spanDel = document.createElement('SPAN');
		var canvas = document.createElement('CANVAS');
		canvas.setAttribute("width", previewWidth);
		canvas.setAttribute("height", previewHeight);
		var ctx = canvas.getContext('2d');

		span.setAttribute('class', 'img');
		spanDel.setAttribute('class', 'delete');
		spanDel.innerHTML = '<i class="fa fa-times"></i>';

		li.appendChild(span);
		li.appendChild(spanDel);
		li.setAttribute('data-id', file.name);

		image.removeEventListener('load', imgLoadHandler, false);

		imgLoadHandler = function() {
			ctx.drawImage(image, 0, 0, previewWidth, previewHeight);
			URL.revokeObjectURL(image.src);
			span.appendChild(canvas);
			isProcessing = false;
			setTimeout(processQueue, 200); 
		};

		previewPhotoContainer.appendChild(li);
		listen(image, 'load', imgLoadHandler);
		image.src = URL.createObjectURL(file);

		var fr = new FileReader();
		fr.readAsDataURL(file);
		fr.onload = (function (file) {
			return function (e) {
				$(previewPhotoContainer).append(
					'<input type="hidden" name="photos[]" value="' + e.target.result + '" data-id="' + file.name+ '">'
				);
			}
		}) (file);
	};

	$(document).on('click', '#preview-photo li span.delete', function() {
		var fileId = $(this).parents('li').attr('data-id');

		if (selectedFiles[fileId] != undefined)
			delete selectedFiles[fileId]; 

		$(this).parents('li').remove();
		$('input[name^=photo][data-id="' + fileId + '"]').remove(); 
	});
});
