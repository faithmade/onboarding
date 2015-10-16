(function($) {
	var Onboarding = {

		current_step: 'colors',

		initialize: function() {
			var self = this;
			this.setStep(FMOnboarding.current_step);

			$('.next-step').on('click', function() {
				self.setStep($('.next-step').attr('data-next-step'));
			});

			$('.onboarding-colors--color button').on('click',function(){
				$(this).closest('section').find('.onboarding-colors--color').removeClass('onboarding-colors--color_active');
				$(this).closest('.onboarding-colors--color').addClass('onboarding-colors--color_active');
			});

			$('.onboarding-fonts--font button').on('click',function(){
				$(this).closest('section').find('.onboarding-fonts--font').removeClass('onboarding-fonts--font_active');
				$(this).closest('.onboarding-fonts--font').addClass('onboarding-fonts--font_active');
			});

			$('.step-count--total-steps').html($('section[class^="onboarding-"]').length);
		},

		setStep: function(stepName) {
			console.log( 'Setting step to ' + stepName );
			this.current_step = stepName;
			//console.log(stepName);
			$('section.active[class^="onboarding-"]').removeClass('active');
			$('section.onboarding-' + stepName).addClass('active');

			var _currentStep = $('section.active[class^="onboarding-"]');
			this.updateStep();

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

			if( this['_'+this.current_step] && typeof this['_'+this.current_step] === 'function' ) {
				this['_'+this.current_step]()
			}
		},

		updateStep: function() {
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,
				data: {
					action: 'faithmade_onboarding',
					current_step: this.current_step
				},
				success: function( response ) {
					console.log( response );
				},
				error: function( x,t,e ) {
					console.log( e );
				}
			});
		},

		_intro: function() {
			// Sample Callback for added wonderfulness.
			console.log( 'in callback' );
		},

		_logo: function() {

		},

		_fonts: function() {

		},

		_fonts2: function() {
			var self = this;
			var section = $(".onboarding-fonts2");
			if( $(".loading").is(":visible") ) {
				$.ajax({
					type: 'POST',
					url: FMOnboarding.ajaxurl,
					data: {
						action: 'faithmade_onboarding',
						current_step: this.current_step,
						get_markup: true
					},
					success: function( response ) {
						response = $.parseJSON( response );
						section.html( response.markup );
						$("head").append( response.head );
						$(".font-select").on("change", function() {
							self.updateFont( $(this).attr('name'), $(this).val() );
						});
						$(".loading").hide();
					},
					error: function( x,t,e ) {
						console.log( e );
					}
				});
			}			
		},

		_colors: function() {
			console.log( 'In colors callback');
			self = this;
			//$(".palette-control").change(this.updateThemePalette);
			$(".palette-selector").on('click',function(e) {
				self.updatePaletteTo( $(this).attr('data-palette-value') );
			});
		},

		_fonts: function() {

		},

		updatePaletteTo: function(palette_name) {
			//$input = e.target;
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,
				data: {
					action: 'faithmade_onboarding',
					current_step: 'colors',
					palette: palette_name,
				},
				success: function( response ) {
					console.log( response );
				},
				error: function( x,t,e ) {
					console.log( e );
				}
			});
		},

		updateFont: function( locationName, fontName ) {
			console.log('Updating Font');
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,
				data: {
					action: 'faithmade_onboarding',
					current_step: 'fonts2',
					location: locationName,
					font: fontName,
				},
				success: function( response ) {
					console.log( response );
				},
				error: function( x,t,e ) {
					console.log( e );
				}
			});
		}
	};
	$onboarding = Onboarding.initialize();
})(jQuery || Zepto);
