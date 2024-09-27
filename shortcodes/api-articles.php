<?php

// Ensure the plugin is only executed within WordPress.

if (!defined('ABSPATH')) {
    exit;
}

function api_articles_shortcode($atts) {
   
    $args = shortcode_atts(array(
        'endpoint' => '',
        'count'=> 6,
        'offset' => 0,
        'format_date' => 'F d, Y',
        'post_type' => 'posts',
        'show_excerpt' => 'yes',
        'show_date' => 'yes',
        'show_category' => 'yes',
        'category' => '',
        'cat_exclude' => '',
        'tag' => '',
        'heading_level' => 'h2',
        'show_img' => 'yes',
        'article_class' => '',
		'date_format_type' => 'human',
        'post_slug' => '',
        'link_target' => '',  // e.g. '_blank' for a new tab
        'link_aria_label' => '',
        'order_by' => 'date',  // default to order by date
        'order_direction' => 'desc',  // default to descending order
        'taxonomy_name' => '',  // empty by default, specify the taxonomy name
        'taxonomy_value' => '',  // empty by default, specify the taxonomy value
    ), $atts);

    $args = array_map('sanitize_text_field', $args);
    $args['count'] = absint($args['count']);
    $args['offset'] = absint($args['offset']);
    $args['article_class'] = sanitize_html_class($args['article_class']);

    if (empty($args['endpoint'])) {
        return 'Error: Please specify the API endpoint.';
    }

    $category_query = '';
    if (!empty($args['category'])) {
        $category_query = "&categories={$args['category']}";
    }
    $category_exclude_query = '';
    if (!empty($args['cat_exclude'])) {
        $category_exclude_query = "&categories_exclude={$args['cat_exclude']}";
    }
    $tag_query = '';
    if (!empty($args['tag'])) {
        $tag_query = "&tags={$args['tag']}";
    }

    $order_query = "&orderby={$args['order_by']}&order={$args['order_direction']}";
    
    $taxonomy_query = '';
    if (!empty($args['taxonomy_name']) && !empty($args['taxonomy_value'])) {
        $taxonomy_query = "&{$args['taxonomy_name']}={$args['taxonomy_value']}"; 
    }

    if (!empty($args['post_slug'])) {
        $response = wp_safe_remote_get("{$args['endpoint']}/wp-json/wp/v2/{$args['post_type']}?_embed&slug={$args['post_slug']}");
    } else {
        $response = wp_safe_remote_get("{$args['endpoint']}/wp-json/wp/v2/{$args['post_type']}?_embed&per_page={$args['count']}&offset={$args['offset']}{$category_query}{$category_exclude_query}{$tag_query}{$order_query}{$taxonomy_query}");
    }

    if (is_wp_error($response)) {
        error_log($response->get_error_message());
        return 'Error: ' . $response->get_error_message();
    }

    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code != 200) {
        return 'There is a problem and we are now working on it. HTTP Response Code: ' . $response_code;
    }

    $posts = wp_remote_retrieve_body($response);
    $posts = json_decode($posts, true);

    if (empty($posts)) {
        return 'No articles are available.';
    }

    // Generate the appropriate HTML tag for the heading
    $heading = $args['heading_level'];

    $output = '<ul class="api-articles ' . esc_attr($args['article_class']) .'">';

    foreach ($posts as $post) {
        $link_target = !empty($args['link_target']) ? " target='" . esc_attr($args['link_target']) . "'" : "";
        $link_aria_label = !empty($args['link_aria_label']) ? " aria-label='" . esc_attr($args['link_aria_label']) . "'" : "";
        $featured_image = isset($post['_embedded']['wp:featuredmedia'][0]['source_url']) ? $post['_embedded']['wp:featuredmedia'][0]['source_url'] : '';
        $date_format = $args['format_date'];
		
        if ($args['date_format_type'] === 'human') {
            $date_published = 'Published ' . human_time_diff(strtotime($post['date']), current_time('timestamp')) . ' ago';
        } else {
            $date_published = date($date_format, strtotime($post['date']));
        }
		
        $category = isset($post['_embedded']['wp:term'][0][0]['name']) ? $post['_embedded']['wp:term'][0][0]['name'] : '';

        $output .= '<li class="api-article">';
        $output .= '<article class="news-card">';
        $output .= '<div class="news-card__content-wrapper">';
        $output .= "<$heading class='news-card__title'><a href='" . esc_url($post['link']) . "' class='news-card__link'{$link_target}{$link_aria_label}>" . esc_html($post['title']['rendered']) . "</a></$heading>";
        $output .= '<div class="news-card__meta-wrapper">';
        if ($args['show_date'] == 'yes') {
            $output .= "<p class='news-card__date'><span>" . esc_html($date_published) . "</span></p>";
        }
        if ($args['show_category'] == 'yes') {
            $output .= "<p class='news-card__category'><span>" . esc_html($category) . "</span></p>";
        }
        $output .= '</div>';
        if ($args['show_excerpt'] == 'yes') {
            $output .= "<p class='news-card__excerpt'>" . wp_kses_post($post['excerpt']['rendered']) . "</p>";
        }
        $output .= '</div>';
        if ($args['show_img'] == 'yes') {
            $alt_text = isset($post['_embedded']['wp:featuredmedia'][0]['alt_text']) && !empty($post['_embedded']['wp:featuredmedia'][0]['alt_text']) 
                        ? esc_attr($post['_embedded']['wp:featuredmedia'][0]['alt_text']) 
                        : 'Read ' . esc_attr($post['title']['rendered']);
                        
            $output .= '<figure class="news-card__img-wrapper">';
            $output .= "<img src='" . esc_url($featured_image) . "' alt='" . $alt_text . "' class='news-card__img'>";
            $output .= '</figure>';
        }
        $output .= '</article>';
        $output .= '</li>';
    }

    $output .= '</ul>';

    return $output;
}


add_shortcode('api_articles', 'api_articles_shortcode');