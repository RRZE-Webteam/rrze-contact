<?php
 
get_header(); 
?>
   
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">	
    <?php
    $display = 'title, telefon, email, fax, url, kurzbeschreibung, adresse, bild, permalink';  
    $adisplay = array_map('trim', explode(',', $display));
    $showfields = array();
    foreach ($adisplay as $val) {
	$showfields[$val] = 1;
    }
    while ( have_posts() ) {
	the_post();
	echo RRZE_Contact\Data::create_rrze_location($post->ID, $showfields, 'h1'); 
    }  ?>
	 <nav class="navigation">
	    <div class="nav-previous"><?php previous_posts_link(__('<span class="meta-nav">&laquo;</span> Back', 'rrze-contact')); ?></div>
	    <div class="nav-next"><?php next_posts_link(__('Next <span class="meta-nav">&raquo;</span>', 'rrze-contact'), '' ); ?></div>
	</nav>
    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php
get_footer();
