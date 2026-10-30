<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<!-- profile section start -->
<?php 
$team_phone = get_post_meta(get_the_ID(),'team_phone',true);
$team_experience = get_post_meta(get_the_ID(),'team_experience',true);
$team_language = get_post_meta(get_the_ID(),'team_language',true);
$team_designation = get_post_meta(get_the_ID(),'team_designation',true);
$team_fb = get_post_meta(get_the_ID(), 'team_fb', true);
$team_tw = get_post_meta(get_the_ID(), 'team_tw', true);
$team_linkedin = get_post_meta(get_the_ID(), 'team_linkedin', true);
$icon_path =  get_template_directory_uri().'/assets/images/' ;

?>
<div class="al-profile-wrapper">
	<div class="container">
		<div class="al-prfle-parent">
			<div class="al-profile-main">
				<div class="al-profile-left">
					<div class="al-prfl-img">
						<?php 
                		astrologer_post_thumbnail();
                		?>
					</div>
				</div>
				<div class="al-profile-right">
					<div class="al-prfile-right-contnt">
						<h3><?php echo get_the_title(); ?></h3>
						<?php
						if($team_designation):
						?>
						<p><?php esc_html_e('Designation', 'astrologer'); ?> : <?php echo esc_html($team_designation); ?></p>
						<?php
					    endif;
						if($team_language):
						 ?>
						<p><?php esc_html_e('Language', 'astrologer'); ?> : <?php echo esc_html($team_language); ?></p>
						<?php
						endif;
						if($team_experience):
						    ?>
						<p><?php esc_html_e('Experience', 'astrologer'); ?> : <?php echo esc_html($team_experience); ?></p>
						<?php
						endif;
						if($team_phone):
						  ?>  
						<p><?php esc_html_e('Phone', 'astrologer'); ?> : <?php echo esc_html($team_phone); ?></p>
						<?php
						endif;
						?>
						<div class="al-social-icon">
						<?php if($team_tw):	?>
						<a href="<?php echo esc_html($team_tw); ?>"><img src="<?php echo ($icon_path.'Twitter.png'); ?>" alt="<?php echo esc_attr__('Twitter','astrologer'); ?>"></a>
						<?php
						endif;
						if($team_linkedin):	?>
						    <a href="<?php echo esc_html($team_linkedin); ?>"><img src="<?php echo($icon_path.'Linkedin.png'); ?>" alt="<?php echo esc_attr__('Linkedin','astrologer'); ?>"></a>
						<?php
						 endif;
						if($team_fb):  ?>
						<a href="<?php echo esc_html($team_fb); ?>"><img src="<?php echo ($icon_path.'Facebook.png'); ?>" alt="<?php echo esc_attr__('Facebook','astrologer'); ?>"></a>
						<?php
						endif;
						?>
						</div>	
					</div>
				</div>
			</div>
			<div class="al-about-astrologer">
				<h4><?php echo esc_html('about me', 'astrologer'); ?></h4>
				<p><?php echo esc_html( get_the_content()); ?></p>
			</div>
		</div>
	</div>
</div>
<!-- profile section end -->
</article>