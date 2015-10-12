$(function() {
	$('.next-step').on('click',function(){
		setStep($('.next-step').attr('data-next-step'));
	});

	setStep('intro');
	$('.step-count--total-steps').html($('section[class^="onboarding-"]').length);
});

function setStep(stepName) {
	$('section.active[class^="onboarding-"]').removeClass('active');
	$('section.onboarding-' + stepName).addClass('active');

	var _currentStep = $('section.active[class^="onboarding-"]');

	if ($('section.onboarding-' + stepName).is(':last-of-type')) {
		var nextStep = 'intro';
	}
	else {
		var nextStep = _currentStep.next('section').attr('class').replace('onboarding-','');
	}

	var _nextStep = $('section.onboarding-' + nextStep);

	// Update header
	$('.step-name').html(_currentStep.attr('data-title'));
	$('.step-description').html(_currentStep.attr('data-description'));
	$('.next-step').html(_nextStep.attr('data-title'));
	$('.next-step').attr('data-next-step',_nextStep.attr('class').replace('onboarding-',''));
	$('.step-count--current-step').html($('section[class^="onboarding-"]').index(_currentStep)+1);
}
