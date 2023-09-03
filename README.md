![image](https://awb4wp.com/wp-content/uploads/2023/09/grid-post-layout-scaled.jpg)

# API Element
 A shortcode that pulls articles using the WP API. Sample CSS is provided. The CSS needs to be reworked to combine both layouts in a single API request.

 ## Parameter
- Set the source (required)
- Set the number of articles - default is 6
- Set an offset (start at article 5) - default is 0
- Set the heading level (h2-h4) - default is h2
- Set the date format - default is Month Day, Year (F d, Y)
- Choose a category (requires cat ID) - Default is none.
- Choose a post type (requires post type slug) - default is post
- Whether to show category, excerpt, or date - all are visible by default

## Shortcode

- [api_articles endpoint="https://example.com" count="5" show_excerpt="yes" show_date="yes" category="2" heading_level="h2"] 
- echo do_shortcode('[api_articles endpoint="https://example.com" count="5" show_excerpt="yes" show_date="yes"]');
