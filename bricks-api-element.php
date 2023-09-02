<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Element_API_Articles extends \Bricks\Element {
    
    public $icon = 'ti-layout-list-thumb';
    public $category = 'VA Corsair';
	public $name  = 'api-articles';
	public $id  = 'api-articles';
	public $css_selector = '';
	
public function get_label() {
    return esc_html__( 'WP API', 'bricks' );
  }
	public function set_control_groups()
	{
		$this->control_groups['general'] = [
			'title' => esc_html__('General', 'bricks'),
			'tab' => 'content',
		];
	}

    // Set element default values
    public function setDefaultValues()
    {
        return [
            'endpoint' => '',
            'count' => 6,
            'category_id' => '',
        ];
    }
// Render element HTML
    public function render()
    {
        $args = $this->props;

        if (empty($args['endpoint'])) {
            return 'Error: Please specify the API endpoint.';
        }

        $category_query = '';
        if (!empty($args['category_id'])) {
            $category_query = "&categories={$args['category_id']}";
        }

        $response = wp_remote_get("{$args['endpoint']}/wp-json/wp/v2/posts?_embed&per_page={$args['count']}{$category_query}");

        if (is_wp_error($response)) {
            return 'Error: ' . $response->get_error_message();
        }

        $posts = wp_remote_retrieve_body($response);
        $posts = json_decode($posts, true);

        if (empty($posts)) {
            return 'No articles are available.';
        }

        // Start the loop
        ob_start();
        foreach ($posts as $post) {
            // Render the child elements
            echo $this->renderChildren();
        }
        $html = ob_get_clean();

        return $html;
    }
    // Render element controls
    public function set_controls()
    {

        // Endpoint control
        $this->controls ['endpoint'] =
          
            [
                'label' => __('API Endpoint', 'bricks'),
                'type' => 'text',
                'placeholder' => 'https://example.com',
			'tab' => 'content',
			'group' => 'general',
            ];
       
        // Count control
        $this->controls ['count'] =
            [
                'label' => __('Number of Articles', 'bricks'),
                'type' => 'number',
                'default' => 6,
			'tab' => 'content',
			'group' => 'general',
            ];

        // Category ID control
        $this->controls['category_id'] =
            
            [
                'label' => __('Category ID', 'bricks'),
                'type' => 'number',
                'default' => '',
                'help' => __('<a id="api-articles-category-link" href="#" target="_blank">View Categories</a>', 'bricks'),
			'tab' => 'content',
			'group' => 'general',
            ];
 
        // Categories Note control
        $this->controls ['categories_note'] =
            [
                'label' => 'Category Lookup',
                'type' => 'note',
                'content' => __('To view a list of available categories and their IDs, click the "View Categories" link above after specifying the API endpoint.', 'bricks'),
			'tab' => 'content',
			'group' => 'general',
           ];
	}
        
}
