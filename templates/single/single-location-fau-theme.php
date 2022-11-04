<?php

get_header();

?>

<?php while (have_posts()): the_post();
    get_template_part('template-parts/hero', 'small');?>

		    <div id="content">
			<div class="container">
			    <div class="row">
				 <div <?php post_class("entry-content");?>>
				    <main id="droppoint">
					<?php
    echo do_shortcode('[location id="' . $post->ID . '" format="page"]');
    ?>
				    </main>
				</div>
			    </div>
			</div>
		    </div>


		<?php endwhile;
get_template_part('template-parts/footer', 'social');
get_footer();