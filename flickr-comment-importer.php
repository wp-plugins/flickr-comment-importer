<?php
/*
Plugin Name: Flickr comment Importer
Plugin URI: http://inphotos.org/
Description: Add Flickr comments to your blog posts. <a href='options-general.php?page=flickr-comment-importer.php'>Configuration Page</a>
Author: Donncha O Caoimh
Version: 0.2
Author URI: http://inphotos.org/
License: GPL2+
Text Domain: flickr-comment-importer
*/ 

include_once( ABSPATH . WPINC . '/feed.php' );

function flickr_comment_importer() {
	global $wpdb;
	$url = get_option( 'flickrcommenturl' );
	if( !$url )
		return;
	$rss = fetch_feed( $url );
	if ( is_wp_error( $rss ) ) // Checks that the object is created correctly
		return false;

	$maxitems = $rss->get_item_quantity(); 
	$rss_items = $rss->get_items( 0, $maxitems );

	if ( $maxitems == 0 )
		return false;

	foreach( $rss_items as $item ) {
		$post_name            = str_replace( "comment-about-", "", sanitize_title( $item->get_title() ) );
		$comment_author       = esc_html( "Flickr: " . substr( str_replace( "nobody@flickr.com (", "", esc_html( $item->get_author() ) ), 0, -1 ) );
		$comment_author_email = 'nobody@flickr.com';
		$comment_author_url	  = esc_url( $item->get_link() );
		$comment_content      = esc_html( $item->get_description() );
		$comment_content      = substr( $comment_content, strpos( $comment_content, 'comment:' ) + 9 );
		$comment_type         = '';
		$user_ID              = '';

		$comment_date = date("Y-m-d h:i:s", strtotime( $item->get_date() ) );
		$comment_date_gmt = date("Y-m-d h:i:s", strtotime( $item->get_date() ) + 28800 );
		$rss = fetch_feed( $_POST[ 'flickrcommenturl' ] );
		if ( is_wp_error( $rss ) ) { // Checks that the object is created correctly
			$invalid_url = true;
		} else {
			update_option( 'flickrcommenturl', $_POST[ 'flickrcommenturl' ] );
		}
	}
?>

<div class="wrap">
<h2><?php _e('Flickr Comment Importer'); ?></h2>
	<p><?php _e( "If you use Flickr to host your photos this plugin will import the comments from your Flickr stream into your blog. Enter the RSS feed on Flickr's <a href='http://www.flickr.com/recent_activity.gne'>recent activity page</a> in the box below." );?></p>
	<p><?php _e( "<strong>Usage and Restrictions</strong><br /><ul><li> Your posts must have the same name as the Flickr photo. For example, <a href='http://inphotos.org/the-thieving-duck/'>The Thieving Duck</a> blog post matches <a href='http://www.flickr.com/photos/donncha/222686138/'>The Thieving Duck</a> on Flickr. It's ok to have multiple Flickr photos with the same name as one blog post.</li><li> You can't import all your old comments. It will only work with whatever Flickr puts in it's comment feed which is the last ten comments.</li><li> Your comments are imported when you're doing stuff in your WordPress backend and placed into the moderation queue. Make sure you login to WordPress often if you have a busy Flickr stream!</li></ul>" );?></p>

<form action="" method="post" id="fci-conf" style="margin: auto; ">
<?php fci_nonce_field($fci_nonce) ?>
<h3><label for="key"><?php _e('Flickr Recent Activity RSS Feed'); ?></label></h3>
<p><input id="url" name="flickrcommenturl" type="text" size="85" maxlength="300" value="<?php echo esc_attr( get_option('flickrcommenturl') ); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /></p>
<?php if ( $invalid_url ) { ?>
	<p style="padding: .5em; background-color: #f33; color: #fff; font-weight: bold; width: 30em"><?php _e('That URL is not a RSS feed. Double-check it.'); ?></p>
<?php } ?>
	<p class="submit"><input type="submit" name="submit" value="<?php _e('Update RSS Feed &raquo;'); ?>" /></p>
</form>
<p><?php _e( "Don't forget to visit <a href='http://inphotos.org/'>In Photos</a>!" ); ?>
</div>
<?php
}

add_action('admin_head', 'flickr_comment_importer');
add_action('admin_menu', 'fci_config_page');

?>
