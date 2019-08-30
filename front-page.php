<?php get_header();?>
<div class="page-banner">
    <div class="page-banner__bg-image" style="background-image: url(<?php echo get_theme_file_uri('/images/library-hero.jpg') ?>);"></div>
    <div class="page-banner__content container t-center c-white">
        <h1 class="headline headline--large">Welcome!</h1>
        <h2 class="headline headline--medium">We think you&rsquo;ll like it here.</h2>
        <h3 class="headline headline--small">Why don&rsquo;t you check out the <strong>major</strong> you&rsquo;re interested in?</h3>
        <a href="<?php echo get_post_type_archive_link('program')?>" class="btn btn--large btn--blue">Find Your Major</a>
    </div>
</div>

<div class="full-width-split group">
    <div class="full-width-split__one">
        <div class="full-width-split__inner">
            <h2 class="headline headline--small-plus t-center">Upcoming Events</h2>
            <?php
            $today = date('Ymd');
            $events = new WP_Query(array(
                'post_type' => 'event',
                'posts_per_page' => 2,
                'orderby' => 'meta_value_num',
                'meta_key' => 'data_eventu',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'data_eventu',
                        'compare' => '>=',
                        'value' => $today,
                        'type' => 'numeric'
                    )
                )
            ));
            while($events -> have_posts()){
                $events -> the_post();
                    get_template_part('template-parts/content', 'event');
                 };
            wp_reset_postdata();
          ?>


                <p class="t-center no-margin"><a href="<?php echo get_post_type_archive_link('event') ?>" class="btn btn--blue">View All Events</a></p>

        </div>
    </div>
    <div class="full-width-split__two">
        <div class="full-width-split__inner">
            <h2 class="headline headline--small-plus t-center">From Our Blogs</h2>
            <?php
            $homepagePosts = new WP_Query(array(
                'posts_per_page' => 2,
                'post_type' => 'post'
                
            ));
            
            while($homepagePosts ->have_posts()){
                $homepagePosts-> the_post();?>
                <div class="event-summary">
                    <a class="event-summary__date event-summary__date--beige t-center" href="<?php the_permalink(); ?>">
            <span class="event-summary__month"><?php the_time('M');?></span>
            <span class="event-summary__day"><?php the_time('d')?></span>  
          </a>
                    <div class="event-summary__content">
                        <h5 class="event-summary__title headline headline--tiny">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title();?>
                            </a>
                        </h5>
                        <p>
                            <?php if(has_excerpt()){
                    echo get_the_excerpt();
                }else {
                    echo wp_trim_words(get_the_content(),18);
                };  ?><a href="<?php the_permalink(); ?>" class="nu gray">Read more</a></p>
                    </div>
                </div>
                <?php };  
       wp_reset_postdata();
          ?>
                <p class="t-center no-margin"><a href="<?php echo site_url('/blog') ?>" class="btn btn--yellow">View All Blog Posts</a></p>
        </div>
    </div>
</div>

<div class="hero-slider">
<?php 
    $slides = new WP_Query(array(
       'post_type' => 'slide',
        'post_per_page' => -1
    ));
    while($slides -> have_posts()){
        $slides -> the_post();
        $title = get_the_title();
        $content = get_field('slider_content');
        $image = get_field('slider_image');
        ?>
            <div class="hero-slider__slide" style="background-image: url(<?php echo $image['url']?>);">
        <div class="hero-slider__interior container">
            <div class="hero-slider__overlay">
                <h2 class="headline headline--medium t-center"><?php echo $title; ?></h2>
                <p class="t-center"><?php echo $content?></p>
                <p class="t-center no-margin"><a href="#" class="btn btn--blue">Learn more</a></p>
            </div>
        </div>
    </div>
    <?php };
    ?>

</div>

<?php 
get_footer();
?>
