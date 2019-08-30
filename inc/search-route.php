<?php
    add_action('rest_api_init', 'collegeRegisterSearch');
    function collegeRegisterSearch(){
        register_rest_route('college/v1','search', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'collegeSearchResults'
        ));
    };
    function collegeSearchResults($data){
        $mainQuery = new WP_Query(array(
        'post_type' => array('post', 'page', 'profesor', 'program', 'event'),
            's' =>  sanitize_text_field($data['key'])
        ));
        $results = array(
            'generalInfo' => array(),
            'professors' => array(),
            'programs' => array(),
            'events' => array()
        );
        while($mainQuery -> have_posts()){
            $mainQuery -> the_post();
                if(get_post_type() == 'post' or get_post_type() == 'page'){
                    array_push($results['generalInfo'], array(
                        'title' => get_the_title(),
                        'url' => get_the_permalink(),
                        'postType' => get_post_type(),
                        'authorName' => get_the_author()
                    ));
                };
            if(get_post_type() == 'profesor'){
                array_push($results['professors'], array(
                        'title' => get_the_title(),
                        'url' => get_the_permalink(),
                        'image' =>  get_the_post_thumbnail_url(0,'medium')
                    ));
            };
            if(get_post_type() == 'event'){
                 $eventDate = new DateTime(get_field('data_eventu',false,false));
                 $description = null;
                if(has_excerpt()){
                    $description = get_the_excerpt();
                } else {
                    $description = wp_trim_words(get_the_content(),18);
                }
                array_push($results['events'], array(
                        'title' => get_the_title(),
                        'url' => get_the_permalink(),
                        'month' => $eventDate -> format('M'),
                        'day' => $eventDate -> format('d'),
                        'description' => $description
                    ));
            };
             if(get_post_type() == 'program'){
                array_push($results['programs'], array(
                        'title' => get_the_title(),
                        'url' => get_the_permalink(),
                        'id' => get_the_id()
                    ));
            };
        };
         
        
        if($results['programs']){
             $programsMetaQuery = array(
            'relation' => 'OR'
        );
        foreach($results['programs'] as $item){
            array_push($programsMetaQuery,  array(
                    'key' => 'related_programs',
                    'compare' => 'LIKE',
                    'value' => '"'. $item['id'] . '"'
                ));
        };
        $programRelationshipQuery = new WP_Query(array(
            'post_type' => array('profesor','event'),
            'meta_query' => $programsMetaQuery
        ));   
        while($programRelationshipQuery -> have_posts()){
            $programRelationshipQuery -> the_post();
            if(get_post_type() == 'profesor'){
                array_push($results['professors'], array(
                        'title' => get_the_title(),
                        'url' => get_the_permalink(),
                        'image' =>  get_the_post_thumbnail_url(0,'medium')
                    ));
            };
            if(get_post_type() == 'event'){
                 $eventDate = new DateTime(get_field('data_eventu',false,false));
                 $description = null;
                if(has_excerpt()){
                    $description = get_the_excerpt();
                } else {
                    $description = wp_trim_words(get_the_content(),18);
                }
                array_push($results['events'], array(
                        'title' => get_the_title(),
                        'url' => get_the_permalink(),
                        'month' => $eventDate -> format('M'),
                        'day' => $eventDate -> format('d'),
                        'description' => $description
                    ));
            };
        };
        $results['professors'] = array_values(array_unique($results['professors'], SORT_REGULAR));
        };
        $results['events']= array_values(array_unique($results['events'], SORT_REGULAR));
        
       
        return $results;
    };
?>