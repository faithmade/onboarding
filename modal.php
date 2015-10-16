<?php
/**
 * The Onboarding Modal Window
 */
?>
<div class="faithmade_modal-backdrop">&nbsp;</div>
<div class="faithmade_modal">
	<a class="faithmade_modal-close dashicons dashicons-no" href="#"
	   title="<?php echo __( 'Close', 'faithmade_modal' ); ?>"><span
			class="screen-reader-text"><?php echo __( 'Close', 'faithmade_modal' ); ?></span></a>

	<div class="faithmade_modal-content">
		<div class="faithmade_modal-main" role="main">
			<header>
				<div class="step-title">
					<div class="step-name"></div>
					<div class="step-description"></div>
				</div>
				<div class="step-progress">
					<div class="step-count">STEP <span class="step-count--current-step"></span> of <span class="step-count--total-steps"></span></div>
					<button class="next-step"></button>
				</div>
			</header>

			<section class="onboarding-intro" data-title="Welcome to Faithmade" data-description="Get started with Faithmade. Let's start building a site!">
				<video controls>
				  <source src=http://techslides.com/demos/sample-videos/small.webm type=video/webm>
				  <source src=http://techslides.com/demos/sample-videos/small.ogv type=video/ogg>
				  <source src=http://techslides.com/demos/sample-videos/small.mp4 type=video/mp4>
				  <source src=http://techslides.com/demos/sample-videos/small.3gp type=video/3gp>
				</video>
			</section>
			<section class="onboarding-logo" data-title="Customize Your Site" data-description="Allowed filetypes are .jpg, .png, and .gif.">
				<div class="onboarding-logo--title">Upload Your Logo</div>
				<div class="onboarding-logo--description">Drag and drop your logo here or click the box to select an image.</div>
				<input type="file" class="onboarding-logo--file">
			</section>

			<section class="onboarding-colors" data-title="Select Colors" data-description="Each theme offers a custom color scheme.">
				<?php echo Faithmade_Onboarding::get_color_markup(); ?>
			</section>

			<section class="onboarding-fonts" data-title="Add Fonts" data-description="Your theme includes custom fonts. Add 1 or 2 fonts from the options below.">
				<?php
					global $typecase;
					$typecase->ui();
				?>
				<!-- <div class="onboarding-fonts--header">
					<div class="onboarding-fonts--header--headings">Headings</div>
					<div class="onboarding-fonts--header--body">Body</div>
				</div>
				<div class="onboarding-fonts--list">
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
					<div class="onboarding-fonts--font">
						<div class="onboarding-fonts--font--heading" style="font-family: 'Marker Felt';">Open Sans</div>
						<div class="onboarding-fonts--font--body" style="font-family: 'Avenir';">Open Sans is utilized for all body copy.</div>
						<div class="onboarding-fonts--font--button"><button>Select</button></div>
					</div>
				</div> -->
			</section>

			<section class="onboarding-fonts2" data-title="Select Fonts" data-description="Decide which fonts you'd like to use for each headings and body content.">
				<div class="loading">
					<h1>Loading Please Wait...</h1>
				</div>
			</section>
			<section class="onboarding-sitemap" data-title="Learn Sitemap" data-description="Watch this video to learn about your built-in pages.">
				<video controls>
				  <source src=http://techslides.com/demos/sample-videos/small.webm type=video/webm>
				  <source src=http://techslides.com/demos/sample-videos/small.ogv type=video/ogg>
				  <source src=http://techslides.com/demos/sample-videos/small.mp4 type=video/mp4>
				  <source src=http://techslides.com/demos/sample-videos/small.3gp type=video/3gp>
				</video>
			</section>

			<section class="onboarding-faith_builder" data-title="Faith Builder" data-description="Learn more about our unique drag-and-drop page builder.">
				<video controls>
				  <source src=http://techslides.com/demos/sample-videos/small.webm type=video/webm>
				  <source src=http://techslides.com/demos/sample-videos/small.ogv type=video/ogg>
				  <source src=http://techslides.com/demos/sample-videos/small.mp4 type=video/mp4>
				  <source src=http://techslides.com/demos/sample-videos/small.3gp type=video/3gp>
				</video>
			</section>
		</div>
	</div>
</div>
