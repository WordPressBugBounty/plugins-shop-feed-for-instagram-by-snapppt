<?php

if(!defined( 'ABSPATH' )) exit; // Exit if accessed directly

function sauce_account_id() {
  # $snapppt_options was set when account ID required manual copy and paste
  # we now push in via API setting, but fallback to $snapppt_options to help transition
  global $snapppt_options;

  $account_id = get_option('sauce_account_id');
  if(empty($account_id)) { $account_id = $snapppt_options['account_id']; }
  return $account_id;
}

# use with Gutenberg Editor by adding a 'Shortcode' block with e.g:
# [snapppt_embed embed_type="grid"]
function snapppt_shortcodes_init() {
  function snapppt_embed_func($atts) {
    $account_id = sauce_account_id();
    $embed_endpoint = SNAPPPT_URL . '/widgets/widget_loader/';
    $embed_data = shortcode_atts(array(
      'embed_type' => 'grid',
      'account_id' => $account_id
    ), $atts);
    if($embed_data['account_id'] == '') {
      return "<p>[ Sauce Embed - No account ID provided! ]</p>";
    } else {
      return "<script src='" . $embed_endpoint . esc_html($embed_data['account_id']) . "/" .
        esc_html($embed_data['embed_type']) . ".js' class='snapppt-widget'></script>";
    }
  }
  add_shortcode('snapppt_embed', 'snapppt_embed_func');
}
add_action('init', 'snapppt_shortcodes_init');

function insert_snapppt_conversion_code($order_id) {
  $account_id = sauce_account_id();
  if(empty($account_id)) { return; }

  $order = wc_get_order($order_id);
  if(!$order) { return; }

  $order_number    = $order-> get_order_number();

  // get_currency and get_total were only introduced in Woo V3
  // and it'll complain if you try and access order_total directly
  if(method_exists($order, 'get_currency')) {
    $order_currency  = $order-> get_currency();
    $order_total     = $order-> get_total();
  } else {
    $order_currency  = $order-> order_currency;
    $order_total     = $order-> order_total;
  }

  $conversion_url  = SNAPPPT_URL . '/conversion-tracker.js';

$snapppt_conversion_code = <<<EOT
  <!-- Sauce conversion code -->
  <script>
    window.snapppt_order_number = '$order_number';
    window.snapppt_order_total = '$order_total';
    window.snapppt_order_currency = '$order_currency';
    window.snapppt_account = '$account_id';
    window.snapppt_platform = 'woocommerce';
  </script>
  <script src="$conversion_url"></script>
EOT;

  echo($snapppt_conversion_code);
}

// to render on the homepage to verify account ID
// add_action('wp_head', 'insert_snapppt_conversion_code');

add_action('woocommerce_thankyou', 'insert_snapppt_conversion_code');

?>
