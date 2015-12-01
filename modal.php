<?php
/**
 * The Onboarding Modal Window
 */
?>
<div class="faithmade_modal-backdrop">&nbsp;</div>
<div class="faithmade_modal-fixed-container">
	<div class="faithmade_modal-wrap">
		<div class="aspect-3-2"></div>
		<div class="faithmade_modal">
			<div class="faithmade_modal-nav-wrap" role="navigation">
				<nav>
					<ul class="faithmade_modal-nav">
						<li class="step-item goto-step" data-goto-step="intro">
							<span class="step-number">1</span>
							Welcome
						</li>
						<li class="step-item goto-step" data-goto-step="logo">
							<span class="step-number">2</span>
							Upload a Logo
						</li>
						<li class="step-item goto-step" data-goto-step="colors">
							<span class="step-number">3</span>
							Choose Colors
						</li>
						<li class="step-item goto-step" data-goto-step="fonts">
							<span class="step-number">4</span>
							Choose Fonts
						</li>
						<li class="step-item goto-step" data-goto-step="sitemap">
							<span class="step-number">5</span>
							Site Map
						</li>
						<li class="step-item goto-step" data-goto-step="faith_builder">
							<span class="step-number">6</span>
							Faith Builder
						</li>
					</ul>
				</nav>
			</div>
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
							<button class="next-step">Continue</button>
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
					<section class="onboarding-logo" id="img1logo-drop-target" data-title="Upload a Logo" data-description="Allowed filetypes are .jpg, .png, and .gif.">
						<?php echo Faithmade_Onboarding::get_logo_markup(); ?>
					</section>

					<section class="onboarding-colors" data-title="Select Colors" data-description="Each theme offers a custom color scheme.">
						<?php echo Faithmade_Onboarding::get_color_markup(); ?>
					</section>

					<section class="onboarding-fonts" data-title="Select a Font Pair" data-description="Your theme includes custom fonts. We have included below a few font pairs that work well together.  Select a pair of fonts you would like to use for your site.">
						<div class="onboarding-fonts--header">
							<div class="onboarding-fonts--header--headings">Headings</div>
							<div class="onboarding-fonts--header--body">Body</div>
						</div>
						<?php echo Faithmade_Onboarding::get_font_markup(); ?>
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

					<section class="onboarding-final" data-title="Finish" data-description="You're all set!  Enjoy Faithmade!">
					</section>
					<section class="onboarding-close" data-title="Before You Close" data-description="It looks like you would rather not do this right now.  Would you like for us to remind you to complete this later?">
						<div class="close-buttons">
							<div class="close-button">
								<button class="close-modal-action" data-close-preference="later">Yes, I'll Do This Later</button>
							</div>
							<div class="close-button">
								<button class="close-modal-action" data-close-preference="never">No, Don't Show Me This Again</button>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
	</div>
</div>