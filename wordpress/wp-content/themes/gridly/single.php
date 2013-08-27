<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			
           
       
   		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( has_post_thumbnail() ) { ?>			
				<div class="gridly-image"><?php the_post_thumbnail( 'detail-image' );  ?></div>
                <div class="gridly-category"><p><?php the_category(', ') ?></p></div>
             <?php } ?>                   

       			<div class="gridly-copy">
                <h1><?php the_title(); ?></h1>
           		 <?php the_content(); ?>
           		 
                 <p><?php the_tags(); ?></p>

                
                <div class="clear"></div> 
                </div>


                
                
       </div>
       
		<?php endwhile; endif; ?>
       
       <?php //$prevpost = get_adjacent_post(true, '', true); print_r($prevpost);exit;?>
       
       <div class="post-nav">
               <div class="post-prev"><?php previous_post_link('%link'); ?> </div>
			   <div class="post-next"><?php next_post_link('%link'); ?></div>
        </div>      
   
       
       
       
  
 

<?php get_footer(); ?>
