(function($) {
	jQuery.fn.exists = function() {  
    	return jQuery(this).length > 0;
	}

	var Onboarding = {

		current_step: 'colors',

		plupload_config: false,

		initialize: function() {
			var self = this;
			if( 'close' === FMOnboarding.current_step ) {
				FMOnboarding.current_step = 'intro';
			}
			this.setStep(FMOnboarding.current_step);

			$(".faithmade_modal-close").on('click', function(e){
				self.setStep('close');
			});

			$(".close-modal-action").on('click', function(e) {
				self.closeModal(e);
			});
			
			$('.next-step').on('click', function() {
				self.setStep($('.next-step').attr('data-next-step'));
			});

			$('.step-item').on('click', function() {
				self.setStep($(this).attr('data-goto-step') );
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

			$('a.site-logo-link').on('click', function(e){
				e.preventDefault();
				return false;
			});
		},

		setStep: function(stepName) {
			this.previous_step = this.current_step ? this.current_step : 'intro';
			this.current_step = stepName;
			var video = $('.active').find('video');
			if( video.length ) {
				video[0].pause();
			}
			$('section.active[class^="onboarding-"]').removeClass('active');
			$('section.onboarding-' + stepName).addClass('active');
			$('.step-item.active').removeClass('active');
			$('.step-item[data-goto-step="'+stepName+'"]').addClass('active');

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
			$('.step-name').show().html(_currentStep.attr('data-title'));
			$('.step-description').html(_currentStep.attr('data-description'));
			$('.next-step').removeClass('skip').html('Continue');
			$('.next-step').attr('data-next-step',_nextStep.attr('class').replace('onboarding-',''));
			$('.step-count--current-step').html($('section[class^="onboarding-"]').index(_currentStep)+1);

			if( 'close' === this.current_step || 'final' === this.current_step ) {
				$(".next-step").hide();
			}

			if( this['_'+this.current_step] && typeof this['_'+this.current_step] === 'function' ) {
				this['_'+this.current_step]()
			}
		},

		updateStep: function() {
			var self = this;
			var step = this.current_step;

			if( 'close' === this.current_step ) {
				step = this.previous_step;
			} 
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,

				data: {
					fmo_nonce: FMOnboarding.fmo_nonce,
					action: 'faithmade_onboarding',
					current_step: step,
				},
				success: function( response ) {
					self.addSuccessContext();
				},
				error: function( x,t,e ) {
					console.log( e );
				}
			});
		},

		addSuccessContext: function() {
			var step = this.current_step;
			$(".faithmade_modal-nav li").each(function() {
				if( $(this).attr('data-goto-step') === step ) {
					return false;
				}
				$(this).find('span.step-number').html('<span class="dashicons dashicons-yes"></span>');
			});
		},

		closeModal: function( e, pref ) {
			var preference = 'later';
			if( $(e).length ) {
				preference = $(e.target).attr('data-close-preference');
			}
			if( $(pref).length ) {
				preference = pref;
			}
			
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,

				data: {
					fmo_nonce: FMOnboarding.fmo_nonce,
					action: 'faithmade_onboarding',
					current_step: 'close',
					previous_step: this.previous_step,
					close_preference: preference,
				},
				success: function( response ) {
					$(".faithmade_modal-backdrop, .faithmade_modal-fixed-container").fadeOut('fast');
				},
				error: function( x,t,e ) {
					$(".faithmade_modal-backdrop, .faithmade_modal-fixed-container").fadeOut('fast');
				}
			});
			
		},

		_intro: function() {

		},

		_logo: function() {
			$(".next-step").addClass('skip').html('Skip This Step');
			var self = this;
			$("input[name='display_header_text']").on("change",function() {
				if( $(this).is(":checked") ) { 
					self.updateDisplayHeaderText(true);
				} else {
					self.updateDisplayHeaderText(false);
				}
			})
		},

		_fonts: function() {
			var self = this;
			$(".next-step").addClass('skip').html('Skip This Step');
			$(".onboarding-fonts--font--button button").on("click", function() {
				var headingFont = $(this).attr('data-heading-font');
				var headingLocation = $(this).attr('data-heading-location');
				var bodyFont = $(this).attr('data-body-font');
				var bodyLocation = $(this).attr('data-body-location');
				self.updateFonts( headingFont, headingLocation, bodyFont, bodyLocation );
			});
		},

		_colors: function() {
			self = this;
			$(".next-step").addClass('skip').html('Skip This Step');
			$(".palette-selector").on('click',function(e) {
				self.updatePaletteTo( $(this).attr('data-palette-value') );
			});
		},

		_sitemap: function() {
			$(".step-description").append('<a href="http://support.faithmade.com/2015/11/17/creating-a-menu/" target="_blank" title="Creating a Menu">Click here</a> to follow these steps when creating your menu.');
		},

		_faith_builder: function() {
			self = this;
			$(".next-step").html('Finish');
		},

		_final: function() {
			self = this;
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,

				data: {
					fmo_nonce: FMOnboarding.fmo_nonce,
					action: 'faithmade_onboarding',
					current_step: 'final',
				},
				success: function( response ) {
					$(".faithmade_modal-backdrop, .faithmade_modal-fixed-container").fadeOut('fast');
				},
				error: function( x,t,e ) {
					console.log(e);
				}
			});
		},

		updateDisplayHeaderText: function(display) {
			if( 'boolean' !== typeof(display) ) {console.log('display not bool');return;}
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,
				data: {
					fmo_nonce: FMOnboarding.fmo_nonce,
					action: 'faithmade_onboarding',
					current_step: 'logo',
					display_header_text: display,
				},
				success: function( response ) {
				},
				error: function( x,t,e ) {
					console.log(e);
				}
			});
		},

		updatePaletteTo: function(palette_name) {
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,
				data: {
					fmo_nonce: FMOnboarding.fmo_nonce,
					action: 'faithmade_onboarding',
					current_step: 'colors',
					palette: palette_name,
				},
				success: function( response ) {
					if( 200 === response.code ) {
						$(".next-step").removeClass('skip').html('Continue');
					}
				},
				error: function( x,t,e ) {
					console.log(e);
				}
			});
		},

		updateFonts: function( headingFont, headingLocation, bodyFont, bodyLocation ) {
			console.log('Updating Font');
			$.ajax({
				type: 'POST',
				url: FMOnboarding.ajaxurl,

				data: {
					fmo_nonce: FMOnboarding.fmo_nonce,
					action: 'faithmade_onboarding',
					current_step: 'fonts',
					hFont: headingFont,
					hLocation: headingLocation,
					bFont: bodyFont,
					bLocation: bodyLocation
				},
				success: function( response ) {
					if( 200 === response.code ) {
						$(".next-step").removeClass('skip').html('Continue');
					}
				},
				error: function( x,t,e ) {
					console.log(e);
				}
			});
		}
	};

	jQuery(document).ready(function($) {
 
	    if ($(".plupload-upload-uic").exists()) {
	        var pconfig = false;
	        $(".plupload-upload-uic").each(function() {
	            var $this = $(this);
	            var id1 = $this.attr("id");
	            var imgId = id1.replace("plupload-upload-ui", "");
	 
	            plu_show_thumbs(imgId);
	 
	            pconfig = JSON.parse(JSON.stringify(FMOnboarding.plupload_config));
	 
	            pconfig["browse_button"] = imgId + pconfig["browse_button"];
	            pconfig["container"] = imgId + pconfig["container"];
	            pconfig["drop_element"] = imgId + pconfig["drop_element"];
	            pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
	            pconfig["multipart_params"]["imgid"] = imgId;
	            pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");
	 
	            if ($this.hasClass("plupload-upload-uic-multiple")) {
	                pconfig["multi_selection"] = true;
	            }
	 
	            if ($this.find(".plupload-resize").exists()) {
	                var w = parseInt($this.find(".plupload-width").attr("id").replace("plupload-width", ""));
	                var h = parseInt($this.find(".plupload-height").attr("id").replace("plupload-height", ""));
	                pconfig["resize"] = {
	                    width: w,
	                    height: h,
	                    quality: 90
	                };
	            }
	 
	            var uploader = new plupload.Uploader(pconfig);
	 
	            uploader.bind('Init', function(up) {
	 				var target = $("#img1logo-drop-target");
	 				target.on("dragenter", function() {
	 					$(this).css("border", "3px dashed black");
	 				});
	 				target.on("dragleave", function() {
	 					$(this).css("border", "none");
	 				});
	            });
	 
	            uploader.init();
	 
	            // a file was added in the queue
	            uploader.bind('FilesAdded', function(up, files) {
	                $.each(files, function(i, file) {
	                    $this.find('.filelist').append('<div class="file" id="' + file.id + '"><b>' +
	 
	                    file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' + '<div class="fileprogress"></div></div>');
	                });
	 
	                up.refresh();
	                up.start();
	            });
	 
	            uploader.bind('UploadProgress', function(up, file) {
	 
	                $('#' + file.id + " .fileprogress").width(file.percent + "%");
	                $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
	            });
	 
	            // a file was uploaded
	            uploader.bind('FileUploaded', function(up, file, response) {	 
	                $('#' + file.id).fadeOut();
	                console.log(response);
	                response = response["response"]
	                // add url to the hidden field
	                if ($this.hasClass("plupload-upload-uic-multiple")) {
	                    // multiple
	                    var v1 = $.trim($("#" + imgId).val());
	                    if (v1) {
	                        v1 = v1 + "," + response;
	                    } else {
	                        v1 = response;
	                    }
	                    $("#" + imgId).val(v1);
	                } else {
	                    // single
	                    $("#" + imgId).val(response + "");
	                }
	                // Update UI
	                $(".onboarding-logo--file").hide();
	                $(".site-logo-link").hide();
	                $(".next-step").removeClass('skip').html('Continue');
	                // show thumbs
	                plu_show_thumbs(imgId);
	            });
	        });
	    }
	});
	 
	function plu_show_thumbs(imgId) {  
	    var $ = jQuery;
	    var thumbsC = $("#" + imgId + "plupload-thumbs");
	    thumbsC.html("");
	    // get urls
	    var imagesS = $("#" + imgId).val();
	    var images = imagesS.split(",");
	    for (var i = 0; i < images.length; i++) {
	        if (images[i]) {
	            var thumb = $('<div class="thumb" id="thumb' + imgId + i + '"><img src="' + images[i] + '" alt="" /><div class="thumbi"><a id="thumbremovelink' + imgId + i + '" href="#">Remove</a></div> <div class="clear"></div></div>');
	            thumbsC.append(thumb);
	            thumb.find("a").click(function() {
	                var ki = $(this).attr("id").replace("thumbremovelink" + imgId, "");
	                ki = parseInt(ki);
	                var kimages = [];
	                imagesS = $("#" + imgId).val();
	                images = imagesS.split(",");
	                for (var j = 0; j < images.length; j++) {
	                    if (j != ki) {
	                        kimages[kimages.length] = images[j];
	                    }
	                }
	                $("#" + imgId).val(kimages.join());
	                plu_show_thumbs(imgId);
	                 $(".onboarding-logo--file").show();
	                return false;
	            });
	        }
	    }
	    if (images.length > 1) {
	        thumbsC.sortable({
	            update: function(event, ui) {
	                var kimages = [];
	                thumbsC.find("img").each(function() {
	                    kimages[kimages.length] = $(this).attr("src");
	                    $("#" + imgId).val(kimages.join());
	                    plu_show_thumbs(imgId);
	                });
	            }
	        });
	        thumbsC.disableSelection();
	    }
	}

	// Initialize our Onboarding Plugin
	$onboarding = Onboarding.initialize();
})(jQuery || Zepto);