<?php
get_header(); 
//	enqueueForeignThemes();
	
?>
    <?php while ( have_posts() ) : the_post(); ?>
        <div id="content">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12">
                    <?php 
		 $args = [
		     'hstart' => 1,
		 ];

                      echo RRZE\Contact\Data::rrze_contact_page($id,array(),$args,true);
                    ?>         
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile;
get_footer();
