<?php

define('THEME_PATH', trailingslashit(get_stylesheet_directory()));

/**
 * Theme Support: Vida Plena
 */
if (!function_exists('theme_setup')) :
    function theme_setup($wp_customize)
    {

        add_theme_support('menus');
        add_theme_support('widgets');
        add_theme_support('widgets-block-editor');
        add_theme_support('wp-block-styles');

        /** automatic feed link*/
        add_theme_support('automatic-feed-links');

        /** tag-title **/
        add_theme_support('title-tag');

        /** post formats */
        $post_formats = array('aside', 'image', 'gallery', 'video', 'audio', 'link', 'quote', 'status');
        add_theme_support('post-formats', $post_formats);

        /** post thumbnail **/
        add_theme_support('post-thumbnails');

        /** HTML5 support **/
        add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));

        /** refresh widgest **/
        add_theme_support('customize-selective-refresh-widgets');

        /** custom log **/
        add_theme_support('custom-logo', array(
            'height'      => 62,
            'width'       => 140,
            'flex-height' => true,
            'flex-width'  => true,
            'header-text' => array('site-title', 'site-description'),
        ));

        // Image size for single posts
        add_image_size( 'single_post_thumbnail', 320, 180 );
        add_image_size( 'single_filter_thumbnail', 206, 114 );
        add_image_size( 'page_top_banner', 960, 230 );

        register_nav_menus(
            array(
                'menu-header' => __('Cabeçalho', 'vida-plena'),
                'menu-principal' => __('Principal', 'vida-plena'),
                'menu-footer-left' => __("Rodapé - Esquerdo", 'vida-plena'),
                'menu-footer-right' => __("Rodapé - Direito", 'vida-plena')
            )
        );
    }
endif; // theme_setup
add_action('after_setup_theme', 'theme_setup');

/**
 * Widgets Registration
 * 
 * @param $name - string - widget name.
 * @param $id - string - widget id/slug.
 * @param $description - string.
 * @param $beforeWidget - string - element insert before the widget.
 * @param $afterWidget - string - element insert after the widget.
 * @param $beforeTitle - string - element insert after the widget title.
 * @param $afterTitle - string - element insert after the widget title.
 * @return void
 */
function custom_theme_widget_registration($name, $id, $description, $beforeWidget, $afterWidget, $beforeTitle, $afterTitle)
{
    register_sidebar(array(
        'name' => $name,
        'id' => $id,
        'description' => $description,
        'before_widget' => $beforeWidget,
        'after_widget' => $afterWidget,
        'before_title' => $beforeTitle,
        'after_title' => $afterTitle,
    ));
}
function custom_theme_multiple_widget_init()
{
    custom_theme_widget_registration('Footer Logo - Vida Plena', 'footer-logo', 'Footer logo - Vida Plena', '<li id="%1$s" class="footer-logo widget %2$s">', '</li>', '', '');
    custom_theme_widget_registration('Footer Imagem - Informações de contato', 'footer-contato', 'Footer Imagem Contato - Vida Plena', '<li id="%1$s" class="footer-contact widget %2$s">', '</li>', '', '');
    custom_theme_widget_registration('Footer Disclaimer', 'footer-disclaimer', 'Footer Disclaimer - Vida Plena', '', '', '', '');
    custom_theme_widget_registration('Footer Redes Sociais', 'footer-social-links', 'Footer Redes Sociais - Vida Plena', '', '', '', '');
}
add_action('widgets_init', 'custom_theme_multiple_widget_init');


/**
* Gutenberg Blocks - Register category "Vida Plena" and update it's order 
*
* @param $categories - array
* @return $newCategories - array
*/
function custom_theme_blocks_category_order($categories)
{
    //custom category array
    $temp = array(
        'slug'  => 'vida-plena',
        'title' => 'Vida Plena'
    );
    //new categories array and adding new custom category at first location
    $newCategories = array();
    $newCategories[0] = $temp;

    //appending original categories in the new array
    foreach ($categories as $category) {
        $newCategories[] = $category;
    }

    //return new categories
    return $newCategories;
}
add_filter( 'block_categories', 'custom_theme_blocks_category_order', 99, 1);

/*
* Gutenberg - Define blocks
*/
function custom_theme_define_blocks()
{

    if (function_exists('acf_register_block_type')) {
        acf_register_block_type(array(
            'name' => 'social-links',
            'title' => __('Redes sociais - Footer'),
            'description' => __('Redes sociais do Rodapé'),
            'render_callback' => 'custom_theme_render_social_links_block',
            'category' => 'vida-plena',
            'icon' => 'share',
            'keywords' => array('Vida Plena', 'Redes socais', "Social links", 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-social-footer-preview.jpg'
                    )
                )
            ),
        ));

        acf_register_block_type(array(
            'name' => 'block-image-left',
            'title' => __('Bloco Imagem + texto - Esquerda'),
            'description' => __('Bloco Imagem + texto - Esquerda'),
            'render_callback' => 'custom_theme_render_block_image_left',
            'category' => 'vida-plena',
            'icon' => 'align-pull-left',
            'keywords' => array('Vida Plena', 'Blocos', "Imagem", 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-block-image-left.jpg'
                    )
                )
            ),
        ));

        acf_register_block_type(array(
            'name' => 'block-image-right',
            'title' => __('Bloco Imagem + texto - Direita'),
            'description' => __('Bloco Imagem + texto - Direita'),
            'render_callback' => 'custom_theme_render_block_image_right',
            'category' => 'vida-plena',
            'icon' => 'align-pull-right',
            'keywords' => array('Vida Plena', 'Blocos', "Imagem", 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-block-image-right.jpg'
                    )
                )
            ),
        ));

        acf_register_block_type(array(
            'name' => 'block-divider',
            'title' => __('Divisória'),
            'description' => __('Divisória'),
            'render_callback' => 'custom_theme_render_block_divider',
            'category' => 'vida-plena',
            'icon' => 'minus',
            'keywords' => array('Vida Plena', 'Divisória', 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-block-divider.jpg'
                    )
                )
            ),
        ));

        acf_register_block_type(array(
            'name' => 'block-posts',
            'title' => __('Bloco estilo posts'),
            'description' => __('Bloco estilo posts'),
            'render_callback' => 'custom_theme_render_block_posts',
            'category' => 'vida-plena',
            'icon' => 'layout',
            'keywords' => array('Vida Plena', 'Post', 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-block-posts.jpg'
                    )
                )
            ),
        ));

        acf_register_block_type(array(
            'name' => 'theme-button',
            'title' => __('Botão - Tema'),
            'description' => __('Botão - Tema'),
            'render_callback' => 'custom_theme_render_button',
            'category' => 'vida-plena',
            'icon' => 'button',
            'keywords' => array('Vida Plena', 'Botão', 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-button.jpg'
                    )
                )
            ),
        ));

        /* Theme Citation */
        acf_register_block_type(array(
            'name' => 'block-theme-quote',
            'title' => __('Citação - Simples'),
            'description' => __('Citaação - Simples'),
            'render_callback' => 'custom_theme_render_block_theme_quote',
            'category' => 'vida-plena',
            'icon' => 'editor-quote',
            'keywords' => array('Vida Plena', 'Citação', 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-quote.jpg'
                    )
                )
            ),
        ));

        /* Theme Author Citation */
        acf_register_block_type(array(
            'name' => 'block-author-quote',
            'title' => __('Citação - Autor'),
            'description' => __('Citação - Autor'),
            'render_callback' => 'custom_theme_render_block_author_quote',
            'category' => 'vida-plena',
            'icon' => 'editor-quote',
            'keywords' => array('Vida Plena', 'Citação', 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-author-quote.jpg'
                    )
                )
            ),
        ));

        /* Theme Vídeo Full */
        acf_register_block_type(array(
            'name' => 'block-video-full',
            'title' => __('Vídeo - Full'),
            'description' => __('Vídeo - Full'),
            'render_callback' => 'custom_theme_render_block_video_full',
            'category' => 'vida-plena',
            'icon' => 'format-video',
            'keywords' => array('Vida Plena', 'Vídeo', 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-video-full.jpg'
                    )
                )
            ),
        ));

        acf_register_block_type(array(
            'name' => 'block-newsletter',
            'title' => __('Bloco Newsletter'),
            'description' => __('Bloco Newsletter'),
            'render_callback' => 'custom_theme_render_block_newsletter',
            'category' => 'vida-plena',
            'icon' => 'email-alt',
            'keywords' => array('Vida Plena', 'Newsletter', 'acf'),
            'example'  => array(
                'attributes' => array(
                    'mode' => 'preview',
                    'data' => array(
                        '_is_preview'   => 'true',
                        'preview_image_help' => get_template_directory_uri() . '/assets/img/ACF/acf-block-newsletter.jpg'
                    )
                )
            ),
        ));
    }
}
add_action('acf/init', 'custom_theme_define_blocks');

function custom_theme_render_social_links_block($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_block_image_left($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_block_image_right($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_block_divider($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_block_posts($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_button($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_block_theme_quote($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_block_author_quote($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_block_video_full($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}
function custom_theme_render_block_newsletter($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(THEME_PATH . "blocks/content-{$slug}.php")) {
        include(THEME_PATH . "blocks/content-{$slug}.php");
    }
}

/*
* Register Theme Styles 
*/
function custom_theme_register_styles()
{

    // $version = wp_get_theme()->get( 'Version' );
    $version = rand(11111, 99999);

    // Font Awesome
    wp_enqueue_style('custom_theme-fontawesome-style', get_template_directory_uri() . "/assets/vendor/fontawesome/css/all.min.css", array(), '5.15.3', 'all');
    // Animate Plugin
    wp_enqueue_style('custom_theme-animate-style', get_template_directory_uri() . "/assets/vendor/animate.css/animate.min.css", array(), '4.1.1', 'all');
    // Slick Slide
    wp_enqueue_style('custom_theme-slick-style', get_template_directory_uri() . "/assets/vendor/slick/slick.css", array(), '1.8.0', 'all');
    wp_enqueue_style('custom_theme-slick-theme-style', get_template_directory_uri() . "/assets/vendor/slick/slick-theme.css", array(), '1.8.0', 'all');
    // Custom Scrollbar
    wp_enqueue_style('custom_theme-custom-scrollbar-style', get_template_directory_uri() . "/assets/vendor/custom-scrollbar/jquery.mCustomScrollbar.min.css", array(), '3.1.13', 'all');
    // Theme Style
    wp_enqueue_style('custom_theme-style', get_template_directory_uri() . "/assets/css/style.min.css", array(), $version, 'all');
}
add_action('wp_enqueue_scripts', 'custom_theme_register_styles');


/**
 * Gutenberg - Add block editor styles
*/
function add_block_editor_assets()
{
    wp_enqueue_style('block_editor_css', get_template_directory_uri() . "/assets/css/editor-blocks.css");
    wp_enqueue_style('block_fonts_css', get_template_directory_uri() . "/assets/css/editor-fonts.css");
}
add_action('enqueue_block_editor_assets', 'add_block_editor_assets', 10, 0);

/**
* Hook for additional special mail tag - Date formt d/m/Y H:i
*
* @param $output - string
* @param $name - string
* @param $html - string
* @return $output - string - data formatted
*/
add_filter( 'wpcf7_special_mail_tags', 'custom_theme_wti_format_date_tag', 20, 3 );
function custom_theme_wti_format_date_tag( $output, $name, $html )
{
   // For backwards compatibility
   $name = preg_replace( '/^wpcf7\./', '_', $name );

   if ( '_format_date_time' == $name ) {
        date_default_timezone_set('America/Sao_Paulo');
        $output = date("d/m/Y H:i");
   }
 
   return $output;
}


 /*
 * Check if email_already_in_db - Newsletter - Advanced contact form 7 
 */
add_filter( 'wpcf7_validate', 'custom_theme_newsletter_email_already_in_db', 10, 2 );

function custom_theme_newsletter_email_already_in_db ( $result, $tags ) {
    $form  = WPCF7_Submission::get_instance();
    $form_posted_data = $form->get_posted_data();

    $contact_form = WPCF7_ContactForm::get_current();
    $contact_form_id = $contact_form -> id;

    $unique_field_name = preg_grep("/unique(\w+)/", array_keys($form_posted_data));
    reset($unique_field_name);
    $first_key = key($unique_field_name);
    $unique_field_name = $unique_field_name[$first_key];

    // Check the form submission unique field vs what is already in the database
    $email = $form->get_posted_data($unique_field_name);
    global $wpdb;
    $entry = $wpdb->get_results( "SELECT * FROM vp_db7_forms WHERE form_post_id = $contact_form_id AND form_value LIKE '%$unique_field_name%' AND form_value LIKE '%$email%'" );

    // If already in database, invalidate
    if (!empty($entry)) {
      $result->invalidate($unique_field_name, 'E-mail já cadastrado!');
      }
    // return the filtered value
  return $result;
}


/**
 * Custom Login CSS
 */
function custom_theme_login_stylesheet() {
    wp_enqueue_style( 'custom-login', get_template_directory_uri() . '/css/admin-style-login.css' );
}
add_action( 'login_enqueue_scripts', 'custom_theme_login_stylesheet' );

