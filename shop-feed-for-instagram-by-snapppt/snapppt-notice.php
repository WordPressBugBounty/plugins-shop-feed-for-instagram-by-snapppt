<?php
if(!defined( 'ABSPATH' )) exit; // Exit if accessed directly


function snapppt_show_review_notice() {
  global $snapppt_options;
  if(!snapppt_is_setup()) { return false; }

  $current_page = get_current_screen()->id;
  if($current_page != 'plugins') { return false; }

  if(isset($snapppt_options['snapppt_notice_never'])) { return false; }

  // when maybe later is selected, we store the date at which it should show again - here we check if that time has elapsed
  if(isset($snapppt_options['snapppt_notice_later']) && $snapppt_options['snapppt_notice_later'] > time()) { return false; }

  return true;
}

function snapppt_render_review_notice() {
  # disable the notice till we update and resolve any potential issue
  # with it not being dismissable as it should be
  return;

// only showing the review notice on the plugins page
if(!snapppt_show_review_notice()) { return; }

$maybe_later_url = add_query_arg('snapppt-notice-later', '1');
$never_url = add_query_arg('snapppt-notice-never', '1');

$snapppt_notice = <<<EOT
  <div class="snapppt-notice notice is-dismissible">
    <p class="snapppt-notice-first-line">We're totally grateful for the exceptionally supportive reviews we receive...</p>
    <p class="snapppt-notice-second-line">Would you be okay if we prompted you to consider supporting us by leaving us a review?</p>
    <p class="snapppt-notice-link-line">
      <a href="https://wordpress.org/support/plugin/shop-feed-for-instagram-by-snapppt/reviews/" target="_blank">Yes, I'll leave a review</a>
      <a href="$never_url">I've already left one</a>
      <a href="$maybe_later_url">Maybe later</a>
    </p>
  </div>
EOT;

echo $snapppt_notice;

}
add_action('admin_notices', 'snapppt_render_review_notice');


function snapppt_handle_notice_action() {
  global $snapppt_options;

  if(isset($_GET['snapppt-notice-never']) && $_GET['snapppt-notice-never'] == '1') {
    $snapppt_options['snapppt_notice_never'] = true;
    update_option('snapppt', $snapppt_options);
  } elseif(isset($_GET['snapppt-notice-never']) && $_GET['snapppt-notice-later'] == '1') {
    $snapppt_options['snapppt_notice_later'] = strtotime("+7 day");
    update_option('snapppt', $snapppt_options);
  }
}
add_action('admin_init', 'snapppt_handle_notice_action');

?>
