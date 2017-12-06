<?php

/*
 * You can add your own functions here. You can also override functions that are
 * called from within the parent theme. 
 */

/** 
 * Adding filter below to keep Jetpack Publicize from triggering without explicitly choosing boxes first
 * Found via http://jetpack.me/2013/10/15/ever-accidentally-publicize-a-post-that-you-didnt/
 * This can be useful for micropub and other related posts one might not want to fire off automatically
 */
add_filter( 'publicize_checkbox_default', '__return_false' );


/** 
 * Adding filter allows jetpack to toggle whether subscribers receive email update of the post
 * Found via https://jetpack.me/support/subscriptions/
 * Note: can't use both this filter AND the following filter at the same time due to collisions. Use one OR the other.
 * add_filter( 'jetpack_allow_per_post_subscriptions', '__return_true' );
*/

/** jetpack_subscriptions_exclude_these_categories: Exclude certain categories from ever emailing to subscribers.
 * Filter: jetpack_subscriptions_exclude_these_categories
 * Will never send subscriptions emails to whatever categories are in that array
 * This example uses the categories "social stream" and "checkin" so that subscribers don't get updates for posts
 * with these categories checked. Just change these values to those you prefer, or remove these lines altogether.
*/
add_filter( 'jetpack_subscriptions_exclude_these_categories', 'exclude_these' );
function exclude_these( $categories ) {
$categories = array( 'social-stream', 'checkin');
return $categories;
}


/**
 * Filter to remove jetpack sharing when viewed from mobile set up (via https://jetpack.com/2016/04/15/hook-month-customizing-sharing/#more-12360)
 */
// Check if we are on mobile
function jetpack_developer_is_mobile() {
  
    // Are Jetpack Mobile functions available?
    if ( ! function_exists( 'jetpack_is_mobile' ) ) {
        return false;
    }
  
    // Is Mobile theme showing?
    if ( isset( $_COOKIE['akm_mobile'] ) && $_COOKIE['akm_mobile'] == 'false' ) {
        return false;
    }
  
    return jetpack_is_mobile();
}
  
// Let's remove the sharing buttons when on mobile
function jetpack_developer_maybe_add_filter() {
  
    // On mobile?
    if ( jetpack_developer_is_mobile() ) {
        add_filter( 'sharing_show', '__return_false' );
    }
}
add_action( 'wp_head', 'jetpack_developer_maybe_add_filter' );

/** 
 * Temporary Code to accept all webmentions as suggested by snarfed at https://github.com/indieweb/wordpress-indieweb/issues/38  */
   function unspam_webmentions($approved, $commentdata) {
     return $commentdata['comment_type'] == 'webmention' ? 1 : $approved;
   }
   add_filter('pre_comment_approved', 'unspam_webmentions', '99', 2);


// For allowing [shortcode] in widget text per http://stephanieleary.com/2010/02/using-shortcodes-everywhere/
add_filter( 'widget_text', 'shortcode_unautop');
add_filter( 'widget_text', 'do_shortcode');

add_filter( 'the_title', 'shortcode_unautop');
add_filter( 'the_title', 'do_shortcode');


/** For allowing exotic webmentions on homepages and archive pages
* 
* function handle_exotic_webmentions($id, $target) {
*  // If $id is homepage, reset to mentions page
*  if ($id == 55669927) {
*    return 55672667;
*  }
*
*  // do nothing if id is set
*  if ($id) {
*    return $id;
*  }
*
*  // return "default" id if plugin can't find a post/page
*  return 55672667;
* }
* 
* add_filter("webmention_post_id", "handle_exotic_webmentions", 10, 2);
*/

/**
 * Removing feeds from WordPress based on https://kinsta.com/knowledgebase/wordpress-disable-rss-feed/
 * function itsme_disable_feed() {
 * wp_die( __( 'No feed available, please visit the <a href="'. esc_url( home_url( '/' ) ) .'">homepage</a>!' ) );
 * }
 * add_action('do_feed', 'itsme_disable_feed', 1);
 * add_action('do_feed_rdf', 'itsme_disable_feed', 1);
 * add_action('do_feed_rss', 'itsme_disable_feed', 1);
 * add_action('do_feed_rss2', 'itsme_disable_feed', 1);
 * add_action('do_feed_atom', 'itsme_disable_feed', 1);
 * add_action('do_feed_rss2_comments', 'itsme_disable_feed', 1);
 * add_action('do_feed_atom_comments', 'itsme_disable_feed', 1);
 */


/**
 * Removing feeds from WordPress based on http://wordpress.stackexchange.com/questions/126174/disable-comments-feed-but-not-the-others
 * 
 * add_action( 'after_setup_theme', 'head_cleanup' );
 * 
 * function head_cleanup(){
 * 
 *     // Add default posts and comments RSS feed links to head.
 *     add_theme_support( 'automatic-feed-links' );
 * 
 *     // disable comments feed
 *     add_filter( 'feed_links_show_comments_feed', '__return_false' ); 
 * 
 * }
 */


/*
 * Force DsgnWrks Instagram Importer plugin to massage the geolocation data so Simple Location plugin can handle it
 * At this point, I can't change it directly as it will break backwards-compatibility, but i've added a filter to allow modifying the meta key/values pre-save. To do so with the 
 * geodata: (from https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/29)
 */

function dw_modify_dsgnwrks_instagram_post_meta_pre_save( $meta, $pic ) {
	$meta['geo_latitude'] = $meta['instagram_location_lat'];
	unset( $meta['instagram_location_lat'] );

	$meta['geo_longitude'] = $meta['instagram_location_long'];
	unset( $meta['instagram_location_long'] );

	if ( isset( $pic->location->public ) ) {
		$meta['geo_public'] = ! empty( $pic->location->public ) ? 1 : 0;
	}

	if ( ! empty( $meta['instagram_location_name'] ) ) {
		$meta['geo_address'] = $meta['instagram_location_name'];
	}
	unset( $meta['instagram_location_name'] );

	return $meta;
}
add_filter( 'dsgnwrks_instagram_post_meta_pre_save', 'dw_modify_dsgnwrks_instagram_post_meta_pre_save', 10, 2 );


/*
 * Function to add u-featured to the post thumbnail
 * per details at https://miklb.com/microformats2-wordpress-and-featured-images-classes/ and 
 * https://wordpress.stackexchange.com/questions/102158/add-class-name-to-post-thumbnail/102250#102250
 */

function mf2_featured_image($attr) {
  remove_filter('wp_get_attachment_image_attributes','mf2_featured_image');
  $attr['class'] .= ' u-featured';
  return $attr;
}
add_filter('wp_get_attachment_image_attributes','mf2_featured_image');
