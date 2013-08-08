<?php get_header(); ?>

<?php if (have_posts()) : ?>
<?php get_amazon()?>
<div id="post-area">
<?php while (have_posts()) : the_post(); ?>	


<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="gridly-copy">
		<a href="<?php the_permalink() ?>"><img src="<?php echo catch_that_image(); ?>" alt="<?php the_title(); ?>" width="" height="" /></a>
	</div>
</div>
       
       
       
       
       

<?php endwhile; ?>
</div>
<?php else : ?>
<?php endif; ?>
    
<?php next_posts_link('<p class="view-older">View Older Entries</p>') ?>
    
 
<?php get_footer(); ?>
