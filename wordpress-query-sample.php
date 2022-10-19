<?php

/**
 * List FAQ Itens - Custom post type
 */
if (!function_exists('custom_theme_list_faqs')) :
    function custom_theme_list_faqs($category)
    {   
        $category_slug = $category->slug;
        $data = array();
        $args = array(
            'post_type' => 'faqs',
            'post_status' => array('publish'),
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'faq_page',
                    'field' => 'slug',
                    'terms' => $category_slug
                )
            ),
        );

        $loop = new WP_Query($args);
        if ($loop->have_posts()) :
            while ($loop->have_posts()) :
                $loop->the_post();

                if (have_rows('faq_item')) :
                    while (have_rows('faq_item')) :
                        the_row();

                        $data[] = [
                            'question' => get_sub_field('faq_question'),
                            'answer' => get_sub_field('faq_answer'),
                        ];
                    endwhile;
                endif;
            endwhile;
        endif;

        wp_reset_postdata();

        return $data;
    }
endif; // custom_theme_list_faqs

/**
 * List Newsletter - Custom post type
 */
if (!function_exists('custom_theme_list_newsletter')) :
    function custom_theme_list_newsletter($newsletter_template)
    {   

        $data = array();
        $args = array(
            'post_type'     => 'block_newsletter',
            'post_status'   => array('publish'),
            'numberposts'   => 1,
            'orderby'       => 'publish_date',
            'post_status'   => array('publish'),
            'order'         => 'DESC',
            'tax_query'     => array(
                array(
                    'taxonomy' => 'newsletter_template',
                    'field' => 'slug',
                    'terms' => $newsletter_template
                )
            ),
        );

        $data = get_posts($args);

        wp_reset_postdata();
        return $data;
    }
endif; // custom_theme_list_newsletter

/**
 * List Social Media - Custom post type
 */
if (!function_exists('custom_theme_list_social_media')) :
    function custom_theme_list_social_media()
    {   

        $data = array();
        $args = array(
            'post_type' => 'social-media',
            'post_status' => array('publish'),
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );

        $loop = new WP_Query($args);
        if ($loop->have_posts()) :
            while ($loop->have_posts()) :
                $loop->the_post();

                $social_item = get_post();

                $data[] = [
                    'name' => $social_item->post_title,
                    'class' => get_field('social_media_icon', $social_item->ID),
                    'url' => get_field('social_media_url', $social_item->ID),
                    'share' => get_field('social_media_shared_support', $social_item->ID),
                    'share_url' => get_field('social_media_shared_url', $social_item->ID),
                ];
            endwhile;
        endif;

        wp_reset_postdata();

        return $data;
    }
endif; // custom_theme_list_faqs

/**
 * List Posts by category
 */
if (!function_exists('custom_theme_list_post_by_category')):
    function custom_theme_list_post_by_category($category_ID, $array = array(), $num_of_posts = 3)
    {
        $args = array(
            'post_type'     => array('post'),
            'tag'           => '',
            'numberposts'   => $num_of_posts,
            'post_status'   => array('publish'),
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
            'post__not_in'  => $array,
            'category__in'  => array($category_ID),
        );
        $data = get_posts($args);

        return $data;
    }
endif; // custom_theme_list_post_by_category

/**
 * Lists Post by taxonomy
 */
if (!function_exists('custom_theme_list_post_by_taxonomy')):
    function custom_theme_list_post_by_taxonomy($taxonomy, $taxonomy_slug, $num_of_posts = 6)
    {
        $args = array(
            'post_type'     => array('post'),
            'numberposts'   => $num_of_posts,
            'post_status'   => array('publish'),
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $taxonomy_slug,
                )
            )
        );

        $data = get_posts($args);

        return $data;
    }
endif; // custom_theme_list_post_by_taxonomy

/**
 * Select Post by taxonomy
 */
if (!function_exists('custom_theme_select_post_by_taxonomy')):
    function custom_theme_select_post_by_taxonomy($taxonomy, $taxonomy_slug, $num_of_posts = 6)
    {
        
        $args = array(
            'post_type'     => array('post'),
            'numberposts'   => $num_of_posts,
            'post_status'   => array('publish'),
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $taxonomy_slug,
                )
            )
        );

        $data = get_posts($args);
        // Get only those posts with has selected equals true   
        foreach ($data as $key => $single ) {
            $single_id = $single->ID;

            $selected = get_field('home_select', $single_id);
            if (!$selected) {
                unset($data[$key]);
            }
        }

        return $data;
    }
endif; // custom_theme_select_post_by_taxonomy

/**
 * List Posts by siblings categories
 */
if (!function_exists('custom_theme_list_post_by_siblings_categories')):
    function custom_theme_list_post_by_siblings_categories($arr_siblings_cat, $current_cat, $array = array(), $num_of_posts = 3)
    {
        if (!is_array($arr_siblings_cat) || empty($arr_siblings_cat)) {
            $arr_siblings_cat = array( );
        } 

        $args = array(
            'post_type'     => array('post'),
            'tag'           => '',
            'numberposts'   => $num_of_posts,
            'post_status'   => array('publish'),
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
            'post__not_in'  => $array,
            'category__in'  => $arr_siblings_cat,
            'category__not_in' => array( $current_cat ),
        );

        $data = get_posts($args);

        // Has sibling category but with no posts
        if (empty($data)) {

            $arr_siblings_cat = array();
            $args = array(
                'post_type'     => array('post'),
                'tag'           => '',
                'numberposts'   => $num_of_posts,
                'post_status'   => array('publish'),
                'orderby'       => 'publish_date',
                'order'         => 'DESC',
                'post__not_in'  => $array,
                'category__in'  => $arr_siblings_cat,
                'category__not_in' => array( $current_cat ),
            );

            $data = get_posts($args);
        }

        return $data;
    }
endif; // custom_theme_list_post_by_siblings_categories

/**
 * List all Posts by category
 */
if (!function_exists('custom_theme_list_all_posts_by_category')):
    function custom_theme_list_all_posts_by_category($category_slug)
    {
        $args = array(
            'post_type'         => array('post'),
            // 'tag'               => '',
            'numberposts'       => -1,
            // 'posts_per_page'    => 6,
            // 'paged'             => $paged,
            'post_status'       => array('publish'),
            'orderby'           => 'publish_date',
            'order'             => 'DESC',
            'tax_query'         => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => $category_slug,
                ),
            ),
        );

        // $data = get_posts($args);
        $data = array();
        $loop = new WP_Query($args);
        if ($loop->have_posts()) :
            while ($loop->have_posts()) :
                $loop->the_post();

                $posts = $loop->posts;
                foreach($posts as $p){
                    $arr_all_posts_ID[] = $p->ID;
                  }

                $data = custom_theme_list_tags_by_ID($arr_all_posts_ID);
            endwhile;
        endif;
        
        wp_reset_postdata();

        return $data;
    }
endif; // custom_theme_list_all_posts_by_category

/**
 * List tags by ID
 */
if (!function_exists('custom_theme_list_tags_by_ID')):
    function custom_theme_list_tags_by_ID($array = array())
    {
        $args = array(
            'post_type'     => array('post'),
            'tag'           => '',
            'numberposts'   => -1,
            'post__in'      => $array,
            'post_status'   => array('publish'),
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
        );

        $data = get_posts($args);
        $tags = array();

        foreach($data as $post){
            $posttags = get_the_tags($post->ID);

            if(is_array($posttags)){
                foreach($posttags as $objTag){
                    $tags[$objTag->term_id] = $objTag; 
                }
            }
        }

        return $tags;
    }
endif; // custom_theme_list_tags_by_ID

/**
 * List Youtube Vídeos - Custom post type
 */
if (!function_exists('custom_theme_list_youtube_video_by_category')):
    function custom_theme_list_youtube_video_by_category($category_ID = array())
    {
        $yout_slug_arr = array();
        foreach ($category_ID as $cat) {
            $term = get_term( $cat, 'youtube_category' );
            $slug = $term->slug;
            $yout_slug_arr[] = $slug;
        }

        $args = array(
            'post_type'     => array('youtube-video'),
            'tag'           => '',
            'post_status'   => array('publish'),
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
            'tax_query'     => array(
                array(
                    'taxonomy' => 'youtube_category',
                    'field' => 'slug',
                    'terms' => $yout_slug_arr
                )
            ),
        );
        
        $data = get_posts($args);

        return $data;
    }
endif; // custom_theme_list_youtube_video_by_category

/**
 * List Aplicativo - Custom post type
 */
if (!function_exists('custom_theme_list_app_by_category')):
    function custom_theme_list_app_by_category($category_ID = array())
    {
        $app_slug_arr = array();
        foreach ($category_ID as $key => $cat) {
            $term = get_term( $cat, 'app_category' );
            $slug = $term->slug;
            $app_slug_arr[] = $slug;
        }

        $args = array(
            'post_type'     => array('custom_theme_app'),
            'tag'           => '',
            'post_status'   => array('publish'),
            'numberposts'   => -1,
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
            'tax_query'     => array(
                array(
                    'taxonomy' => 'app_category',
                    'field' => 'slug',
                    'terms' => $app_slug_arr
                )
            ),
        );
        
        $data = get_posts($args);

        return $data;
    }
endif; // custom_theme_list_app_by_category

/**
 * List Instagram posts - Custom post type - by category
 */
if (!function_exists('custom_theme_list_instagram_by_category')):
    function custom_theme_list_instagram_by_category($category_ID = array())
    {
        $insta_slug_arr = array();
        foreach ($category_ID as $cat) {
            $term = get_term( $cat, 'instagram_category' );
            $slug = $term->slug;
            $insta_slug_arr[] = $slug;
        }

        $args = array(
            'post_type'     => array('custom_theme_instagram'),
            'tag'           => '',
            'post_status'   => array('publish'),
            'numberposts'   => -1,
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
            'tax_query'     => array(
                array(
                    'taxonomy' => 'instagram_category',
                    'field' => 'slug',
                    'terms' => $insta_slug_arr
                )
            ),
        );
        
        $data = get_posts($args);

        return $data;
    }
endif; // custom_theme_list_instagram_by_category

/**
 * List Popular posts - Custom Taxonomy
 */
if (!function_exists('custom_theme_popular_posts_by_destaque')):
    function custom_theme_popular_posts_by_destaque($taxonomy_ID, $number_of_posts = 9)
    {
        $area_slug_arr = array();
        $term = get_term( $taxonomy_ID, 'posts_destaque' );
        $slug = $term->slug;
        $area_slug_arr[] = $slug;

        $args = array(
            'post_type'     => array('post'),
            'tag'           => '',
            'post_status'   => array('publish'),
            'numberposts'   => $number_of_posts,
            'orderby'       => 'publish_date',
            'order'         => 'DESC',
            'tax_query'     => array(
                array(
                    'taxonomy' => 'posts_destaque',
                    'field' => 'slug',
                    'terms' => $area_slug_arr
                )
            ),
        );
        
        $data = get_posts($args);

        return $data;
    }
endif; // custom_theme_popular_posts_by_destaque

/**
 * Main search - Search bar
 */
if(!function_exists('custom_theme_search_main')):
    function custom_theme_search_main(){
        $data = array();
        $args = array(
            'post_type' => array('page', 'post'),
            'tag' => '',
            'numberposts' => -1,
            'posts_per_page' => -1,
            'post_status' => array('publish'),
            'post__not_in'      => array(118),
            'orderby' => 'publish_date',
            'order' => 'DESC',
            's' => esc_attr($_POST["searchForm"])
        );

        $loop = new WP_Query($args);

        if ($loop->have_posts()){
            while($loop->have_posts()){
                $loop->the_post();
                $link = get_permalink();
                $data[] = array(
                    'title' => get_the_title(),
                    'link' => $link
                );
            }
        }

        wp_reset_query();

        return $data;
    }
endif;// custom_theme_search_main