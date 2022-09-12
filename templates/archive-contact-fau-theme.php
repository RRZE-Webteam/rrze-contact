<?php

get_header();
get_template_part('template-parts/hero', 'index');
$screenreadertitle = __('Contact list', 'rrze-contact');
?>
    <div id="content">
        <div class="container">
	    <div class="row">
		<div <?php post_class("entry-content");?>>
		    <main>
			<h1 id="droppoint" class="mobiletitle"><?php the_title();?></h1>
<?php while (have_posts()) {
    the_post();
    $id = $post->ID;
    if ($id) {

        echo FAU_Person\Shortcodes\Contact::shortcode_contact(array("id" => $post->ID, 'format' => 'kompakt', 'showlink' => 0, 'showlist' => 1));
    } else {?>
			    <p class="hinweis">
				<strong><?php _e('We are sorry', 'rrze-contact');?></strong><br>
				<?php _e('No information can be retrieved for the specified contact.', 'rrze-contact');?>
			    </p>
			<?php }
}?>
		    <nav class="navigation">
			<div class="nav-previous"><?php previous_posts_link(__('<span class="meta-nav">&laquo;</span> Back', 'rrze-contact'));?></div>
			<div class="nav-next"><?php next_posts_link(__('Next <span class="meta-nav">&raquo;</span>', 'rrze-contact'), '');?></div>
		    </nav>
		    </main>
		</div>
	    </div>
	</div>
    </div>
<?php
get_template_part('template-parts/footer', 'social');
get_footer();
