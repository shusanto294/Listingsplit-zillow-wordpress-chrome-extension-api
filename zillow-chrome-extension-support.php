<?php
/*
Plugin Name: Zillow Chrome Extension Support
Version: 1.0.0
Description: This plugin is developer for providing api support for the zillow chrome extension
Author: Shusanto Modak
Author URI: https://cloudnineweb.co/
*/


add_action('rest_api_init', function () {
    register_rest_route('zillow/v1', '/commission-rate', array(
        'methods' => 'POST',
        'callback' => 'get_commission_rate',
        'permission_callback' => function () {
            return true; // Adjust the permission callback as needed
        }
    ));
});

function get_commission_rate($request) {
    $address = $request->get_param('address');

    // Ensure the address parameter is not empty
    if (empty($address)) {
        return new WP_REST_Response(array(
            'status' => 'error',
            'message' => 'Address parameter is empty'
        ), 400);
    }

    // Use meta_query instead of directly searching post_title
    $args = array(
        'post_type' => 'listing',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        's' => $address
    );

    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        $query->the_post();
        $custom_fields = get_post_meta(get_the_ID()); // Get all custom fields
        $response = array(
			'status' => 'found',
			'postID' => get_the_ID(),
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'custom_fields' => $custom_fields // Include all custom fields
        );
    } else {
        $response = array(
            'status' => 'not found',
        );
    }
    
    wp_reset_postdata(); // Reset post data after custom query

    // Add CORS headers
    $response = new WP_REST_Response($response, 200);
    $response->set_headers(array(
        'Access-Control-Allow-Origin' => '*', // Adjust the origin as needed
        'Access-Control-Allow-Methods' => 'POST',
        'Access-Control-Allow-Headers' => 'Content-Type',
    ));
    
    return $response;
}
