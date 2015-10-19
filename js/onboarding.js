(function($) {
	jQuery.fn.exists = function() {  
    	return jQuery(this).length > 0;
	}

	var Onboarding = {

		current_step: 'colors',

		plupload_config: false,

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
	 				console.log(response['response']);
	 
	                $('#' + file.id).fadeOut();
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
