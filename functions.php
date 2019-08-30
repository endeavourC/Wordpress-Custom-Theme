<?php

require get_theme_file_path('/inc/search-route.php');
require get_theme_file_path('/inc/like-route.php');
function college_custom_rest(){
    register_rest_field('post','authorName', array(
        "get_callback" => function(){
            return get_the_author();
        }
    ));
    register_rest_field('note','userNoteCount', array(
        "get_callback" => function(){
            return count_user_posts(get_current_user_id(), 'note');
        }
    ));
};
add_action('rest_api_init', 'college_custom_rest');
function pageBanner($args = NULL){
    
    if(!$args['title']){
        $args['title'] = get_the_title();
    };
    if(!$args['subtitle']){
      $args['subtitle'] = get_field('page_banner_subtitle'); 
    };
    if(!$args['photo']){
      if(get_field('page_banner_background_image')){
          $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
      } else {
          $args['photo'] =  get_theme_file_uri('/images/ocean.jpg');
      };
    };
    ?>
    <div class="page-banner">
        <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title">
                <?php echo $args['title'];?>
            </h1>
            <div class="page-banner__intro">
                <p>
                    <?php echo $args['subtitle']; ?>
                </p>
            </div>
        </div>
    </div>
    <?php 
};

function college_files(){
    
    wp_enqueue_script('main-college-js', get_theme_file_uri('/js/scripts-bundled.js'), NULL , microtime(), true);
    wp_enqueue_script('amazing-college-js', get_theme_file_uri('/js/main.js'), array('jquery') , microtime(), true);
    wp_enqueue_script('googleMap', '//maps.googleapis.com/maps/api/js' , NULL , microtime(), true);
    wp_enqueue_style('font-awesome','//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('custom-google-fonts','//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('college_main_styles', get_stylesheet_uri(), NULL, microtime());
    wp_localize_script('amazing-college-js','collegeData', array(        
        'root_url' => get_site_url(),
        'nonce' => wp_create_nonce('wp_rest')
    ));
};
add_action('wp_enqueue_scripts', 'college_files');

function college_features(){
    register_nav_menu('headerMenuLocation', 'Header Menu Location');
    register_nav_menu('footerLocationOne', 'Footer Location One');
    register_nav_menu('footerLocationTwo', 'Footer Location Two');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
    add_image_size('pageBanner', 1500, 350, true);
};
add_action('after_setup_theme', 'college_features');
function college_adjust_queries($query){
    if(!is_admin() and is_post_type_archive('event') and $query -> is_main_query()){
        $today = date('Ymd');
        $query -> set('meta_key', 'data_eventu');
        $query -> set('orderby', 'meta_value_num');
        $query -> set('order', 'ASC');
        $query -> set('meta_query', array(
            array(
                'key' => 'data_eventu',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
            )
        ));
    };
    if(!is_admin() and is_post_type_archive('program') and $query -> is_main_query()){
        $query -> set('order', 'ASC');
        $query -> set('orderby', 'title');
        $query -> set('posts_per_page', -1);
    }
    
}
add_action('pre_get_posts', 'college_adjust_queries');
//redirect sub acc out of admin into homepage
function redirectSubs(){
    $currentUser = wp_get_current_user();
  if(count($currentUser -> roles) == 1 and $currentUser ->roles[0] == 'subscriber'){
      wp_redirect(site_url('/'));
      exit;
  }  
};
add_action('admin_init', 'redirectSubs');

function noSubsAdminBar(){
    $currentUser = wp_get_current_user();
  if(count($currentUser -> roles) == 1 and $currentUser ->roles[0] == 'subscriber'){
    show_admin_bar(false);
  }  
};
add_action('wp_loaded', 'noSubsAdminBar');

//Customize login section
function ourHeaderUrl(){
    return esc_url( site_url('/'));
};
add_filter('login_headerurl', 'ourHeaderUrl');
function ourLoginCSS(){
    wp_enqueue_style('college_main_styles', get_stylesheet_uri(), NULL, microtime());
    wp_enqueue_style('custom-google-fonts','//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
};
add_action('login_enqueue_scripts', 'ourLoginCSS');
function ourLoginTitle(){
    return get_bloginfo('name');
};
add_filter('login_headertitle', 'ourLoginTitle');

//Force note posts to be private
add_filter('wp_insert_post_data', 'makeNotePrivate', 10, 2);
function makeNotePrivate($data, $postarr){
    if($data['post_type'] == 'note'){
        if(count_user_posts(get_current_user_id(), 'note') > 4 and !$postarr['ID']){
            die('You have reached your note limit.');
        };
        $data['post_content'] = sanitize_textarea_field($data['post_content']);
        $data['post_title'] = sanitize_text_field($data['post_title']);
    };
    if($data['post_type'] == 'note' and $data['post_status'] != 'trash'){
    $data['post_status'] = 'private';
    };
    return $data;
};
add_filter( 'rest_endpoints', function( $endpoints ){
    if ( isset( $endpoints['/wp/v2/users'] ) ) {
        unset( $endpoints['/wp/v2/users'] );
    }
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
});

?>
