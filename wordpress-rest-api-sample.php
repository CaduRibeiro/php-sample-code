<?php

/**
 * Wordpress Rest API
 */

//The Following registers an api route with multiple parameters. 
add_action( 'rest_api_init', 'add_custom_users_api');
function add_custom_users_api(){
    register_rest_route( 'api', '/artigos', array(
     'methods' => WP_REST_Server::READABLE, 
     'callback' => 'query_posts_and_pages_with_params' 
     ) );
     register_rest_route( 'api', '/filter', array(
        'methods' => WP_REST_Server::READABLE, 
        'callback' => 'query_posts_filter_with_params' 
    ) );
}

function query_posts_filter_with_params(WP_REST_Request $request) {

    $args_filter = array();
    $args_filter['limit'] = $request->get_param( 'limit' );
    $args_filter['tax_id']   = $request->get_param( 'tax_id' );

    $args_filter_query = array(
        'post_type'         => array('post'),
        'numberposts'       => $args_filter['limit'],
        'posts_per_page'    => -1,
        'post_status'       => array('publish'),
        'orderby'           => 'publish_date',
        'order'             => 'DESC',
        'tax_query'         => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'human_body_filter',
                'field'    => 'term_id',
                'terms'    => $args_filter['tax_id'],
            )
        )
    );

    $meta_filter_query = new WP_Query($args_filter_query);
    $filter_data = "";

    if($meta_filter_query->have_posts()) {

        $post_count = 0;
        $post_rel_delay = 0.4;
        $filter_data = "";
        $obj = array();
        $tag_link = get_tag_link($args_filter['tax_id']);
        $tax_obj = get_term( $args_filter['tax_id'] );
        $tax_name = $tax_obj->name;

        while($meta_filter_query->have_posts()) :

            $meta_filter_query->the_post();
            $id = get_the_ID();
            $post = get_post($id);
            $no_thumb = false;

            $post_title = $post->post_title;
            $excerpt = $post->post_excerpt;
            $link = get_permalink($id);

            $featured_image = get_the_post_thumbnail($id, "single_filter_thumbnail");
            if (!$featured_image) {
                $featured_image = get_template_directory_uri() . '/assets/img/thumbnail-padrao.jpg'; 
                $no_thumb = true;
            }

            $filter_data .='
                <div class="filter-post animate__animated animate__fadeInUp" data-wow-delay="' . $post_rel_delay . 's">
                    <a href="' . $link . '" class="mtr-site" data-category="Artigo" data-label="' . $post_title . '">';
                    if ($no_thumb) {
                        $filter_data .= '<img width="206" height="114" src="' . $featured_image  . '" class="attachment-single_post_thumbnail size-single_post_thumbnail wp-post-image" alt="Vida Plena" loading="lazy" sizes="(max-width: 320px) 100vw, 320px" />';
                    } else {
                        $filter_data .= $featured_image;
                    }
                    $filter_data .= '</a>
                    <div class="filter-post-item__content">
                        <a href="' . $link . '" class="mtr-site" data-category="Artigo" data-label="' . $post_title . '">
                            <h4 class="filter-post-item__title">' . $post_title . '</h4>
                        </a>';
                        if (!empty($excerpt)) : 
                            $filter_data .=  '<p>' . custom_theme_limite_destaque_excerpt($excerpt) . '</p>';
                        else : 
                            $filter_data .= '<p>' . get_the_excerpt($id) . '</p>';
                        endif; 
                        $filter_data .= '<a class="filter-excerpt-link" href="' . $link . '" class="btn btn-green" data-category="Artigo" data-label="' . $post_title . '">Continuar lendo';
                        $filter_data .= '</a>
                    </div>
                </div>';

            $post_rel_delay += 0.4; 
            $post_count++;

            if ($post_count = 1) : 
                $post_count = 0;
                $post_rel_delay = 0.4;
            endif;

        endwhile; 

        $filter_data .= '<div class="filter-post filter-last animate__animated animate__fadeInUp" data-wow-delay="' . $post_rel_delay . 's">
                            <a href="' . $tag_link . '" class="filter-link-all mrt-site" data-category="Tag - Navegue pelo corpo" data-label="' . $tax_name  . '">
                                <p class="filter-last__text">Ver todas as matérias</p>
                            </a>
                        </div>';

        $obj['html'] = $filter_data;


    } else {
        $filter_data .= "<p>Nenhum artigo encontrado.</p>";
        $obj['html'] = $filter_data;
    }

    return $obj;
}

function query_posts_and_pages_with_params(WP_REST_Request $request) {

    $args = array();
    $args['category'] = $request->get_param( 'category' );
    $args['page']     = $request->get_param( 'page' );
    $args['max']      = $request->get_param( 'max' );
    $args['tags']     = $request->get_param( 'tags' );
    $args['order']    = $request->get_param( 'order' );
    $args['tag']      = $request->get_param( 'tag' );
    $args['new']      = $request->get_param( 'new' );
    $args['search']   = $request->get_param( 'search' );
    $args['tax']      = $request->get_param( 'tax' );
    $args['tax_id']   = $request->get_param( 'tax_id' );

    return query_posts_and_pages($args);
  }
  function query_posts_and_pages($args){

    $paged = $args['page'];
    $category = $args['category'];
    $order = $args['order'];
    $tag = $args['tags'];
    $new = $args['new'];
    $search = $args['search'];
    $tax = $args['tax'];
    $tax_id = $args['tax_id'];
    $arr_tag = array();
    $query_cat = array();
    $query_tax = array();
    $tax_query = array();

    if (!empty($category)) {
        $query_cat = array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $category,
        );
    }

    if (!empty($tax)) {
        $query_tax =  array(
            'taxonomy' => $tax,
            'field'    => 'term_id',
            'terms'    => $tax_id,
        );

        $arr_tag = '';
    }



    if ( (!empty($search) && $search !== "") ) {
        $tax_query = array();
    } elseif (($search === '' && ( !empty($query_cat)))) {
        $tax_query = array(
            'relation' => 'AND',
            $query_cat
        );
    } elseif (($search === '' && ( !empty($query_tax)))) { 
        $tax_query = array(
            'relation' => 'AND',
            $query_tax
        );
    } else {
        $tax_query = array();
    }

    if (!empty($tag)) $arr_tag = explode(",", $tag); 

    if ($new == 'true') $new = true;
    else $new = false;

    if ($new) $page = 1;

    $args_query = array(
        'post_type'             => array('post', 'page'),
        'tag__in'           => $arr_tag,
        'numberposts'       => -1,
        'posts_per_page'    => 24, // 24 Posts per page
        'paged'             => $paged,
        'post_status'       => array('publish'),
        'orderby'           => 'publish_date',
        'post__not_in'      => array(118),
        'order'             => $order,
        'tax_query'         => $tax_query,
        's'                 => $search,
    );

    return fetchByPostTypeAndTax($args_query , $args);
}

function fetchByPostTypeAndTax($args_query, $args) {
    // Run a custom query
    $category = $args['category'];
    $tags     = $args['tags'];
    $order    = $args['order'];
    $search   = $args['search'];
    $tax      = $args['tax'];
    $tax_id   = $args['tax_id'];
    $page     = $args['page'];
    // $new      = $args['new'];

    $meta_query = new WP_Query($args_query);
    $total_posts = $meta_query->found_posts;
    $posts_per_page =  $meta_query->query_vars['posts_per_page'];
    $no_page = false;
    if ($total_posts <= $posts_per_page) $no_page = true;

    $data = "";
    $max = "";
    $result = "";
    $obj = array();

    if($meta_query->have_posts()) {

        $post_rel_delay = 0;
        $post_count = 0;
        $total_posts = $meta_query->found_posts;

        if ($total_posts == 1) $result =  $total_posts . " resultado";
        else $result =  $total_posts . " resultados";

        $total = (empty($args['search'])) ? "<p class='results'>" . $result . ":</p>"
        : 'Nós encontramos <strong>'. $result.'</strong> para a sua pesquisa:';

        // Store each post's data in the array
        while($meta_query->have_posts()) :

            $meta_query->the_post();
            $id = get_the_ID();
            $post = get_post($id);
            $post_type = get_post_type($id);
            $post_title = $post->post_title;
            $excerpt = $post->post_excerpt;
            $link = get_permalink($id);
            $no_thumb = false;

            
            if ($post_type == "page") :

                $cat_into = get_field("category_intro", $id);
                if (!empty( $cat_into)) $excerpt = $cat_into;

                if (empty($excerpt)) {
                    if (have_rows('top_banner', $id)) :
                        while (have_rows('top_banner', $id)) : the_row();
                        $subtitle = get_sub_field('top_subtitle', $id);
                        $bg_image = get_sub_field('top_background-img', $id);
                        endwhile;
                    endif;

                    if (!empty($subtitle)) $excerpt = $subtitle;
                }

                if (empty($excerpt)) {
                    if (have_rows('top_home', $id)) :
                        while (have_rows('top_home', $id)) : the_row();
                        $home_subtitle = get_sub_field('top_home_subtitle', $id);
                        $home_bg_image = get_sub_field('top_home_background_img', $id);
                        endwhile;
                    endif;

                    if (!empty($home_subtitle)) $excerpt = $home_subtitle;
    
                }
            endif;

            $featured_image = get_the_post_thumbnail($id, "single_post_thumbnail");
            if (!$featured_image) {
                $no_thumb = true;
                if (!empty($bg_image))  $featured_image = $bg_image['sizes']['single_post_thumbnail'];
                elseif (!empty($home_bg_image))  $featured_image = $home_bg_image['sizes']['single_post_thumbnail'];
                else {
                    $featured_image = get_template_directory_uri() . '/assets/img/thumbnail-padrao.jpg'; 
                }
            }

            $bg_image = "";
            $home_bg_image = "";
        
            $data .='
                <div class="cat-post-item wow animate__animated animate__fadeInUp" data-wow-delay="' . $post_rel_delay . 's">
                    <div class="post-item__destaque">
                        <a href="' . $link . '" class="mtr-site" data-category="' . ($post_type == "page" ? "Página" : "Artigo") . '" data-label="' . $post_title . '">';
                        if ($no_thumb) {
                            $data .= '<img width="320" height="180" src="' . $featured_image  . '" class="attachment-single_post_thumbnail size-single_post_thumbnail wp-post-image" alt="Vida Plena" loading="lazy" sizes="(max-width: 320px) 100vw, 320px" />';
                        } else {
                            $data .= $featured_image;
                        }
                        $data .= '</a>
                    </div>
                    <div class="post-item__content">
                        <a href="' . $link . '" class="mtr-site" data-category="' . ($post_type == "page" ? "Página" : "Artigo") . '" data-label="' . $post_title . '">
                            <h4 class="post-item__title">' . $post_title . '</h4>
                        </a>';
                        if (!empty($excerpt)) : 
                            $data .=  '<p>' . custom_theme_limite_excerpt($excerpt) . '</p>';
                        else : 
                            $data .= '<p>' . get_the_excerpt($id) . '</p>';
                        endif; 
                        $data .= '<a href="' . $link . '" class="btn btn-green" data-category="' . ($post_type == "page" ? "Página" : "Artigo") . '" data-label="' . $post_title . '">';
                        if ($post_type == 'post') :
                        $data .= ' Continuar lendo';
                        else :
                        $data .= ' Acesse';
                        endif;
                        $data .= '</a>
                    </div>
                </div>';

            $post_rel_delay += 0.4; 
            $post_count++;

            if ($post_count > 2) : 
                $post_count = 0;
                $post_rel_delay = 0.4;
            endif;

        endwhile; 

        $max = $meta_query->max_num_pages;
        // $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $obj['html']    = $data;
        $obj['max']     = $max;
        $obj['total']   = $total;
        $obj['paged']   = $page;
        $obj['no_page'] = $no_page;
        $obj['search']  = $search;
        $obj['cat']     = $category;
        $obj['tags']    = $tags;
        $obj['order']   = $order;
        $obj['tax']     = $tax;
        $obj['tax_id']  = $tax_id;

        // Return the data
        return $obj;
    } else {
        // If there is no post
        $data .= "<p>Nenhum artigo encontrado.</p>";
        $obj['html']    = $data;
        $obj['max']     = $max;
        $obj['no_page'] = $no_page;
        $obj['search']  = $search;
        $obj['paged']   = $page;
        $obj['cat']     = $category;
        $obj['tags']    = $tags;
        $obj['order']   = $order;
        $obj['tax']     = $tax;
        $obj['tax_id']  = $tax_id;

        return $obj;
    }
}