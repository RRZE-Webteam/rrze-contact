<?php

get_header();
?>

<?php while (have_posts()): the_post();?>

			<?php get_template_part('template-parts/hero', 'small');?>

			<div id="content">
				<div class="container">
					<div class="row">
						 <div <?php post_class("entry-content");?>>
						    <main>
	<?php
    if (!empty($post->ID)) {?>
								<h1 id="droppoint" class="mobiletitle"><?php the_title();?></h1>
	<?php

        echo do_shortcode('[contact id="' . $post->ID . '" format="page"]');

    } else { ?>
							<h1 id="droppoint" class="mobiletitle"><?php _e('Error', 'fau');?></h1>
							<p class="hinweis">
							<strong><?php _e('We are sorry', 'rrze-contact');?></strong><br>
						<?php _e('No information can be retrieved for the specified contact.', 'rrze-contact');?>
							</p>
						    <?php }?>
						    </main>
					    </div>

					</div>
				</div>
			</div>


		<?php endwhile;
get_template_part('template-parts/footer', 'social');
get_footer();
