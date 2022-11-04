<?php
get_header();
//    enqueueForeignThemes();

?>
    <?php while (have_posts()): the_post();?>
	        <div id="content">
	            <div class="container">
	                <div class="row">
	                    <div class="col-xs-12">
	                    <?php
    echo do_shortcode('[contact id="' . $post->ID . '" format="page"]');
    ?>
	                    </div>
	                </div>
	            </div>
	        </div>
	    <?php endwhile;
get_footer();
