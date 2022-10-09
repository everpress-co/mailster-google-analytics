<?php

class MailsterGoogleAnalytics {

	private $plugin_path;
	private $plugin_url;

	public function __construct() {

		$this->plugin_path = plugin_dir_path( MAILSTER_GA_FILE );
		$this->plugin_url  = plugin_dir_url( MAILSTER_GA_FILE );

		register_activation_hook( MAILSTER_GA_FILE, array( &$this, 'activate' ) );

		load_plugin_textdomain( 'mailster-google-analytics' );

		add_action( 'plugins_loaded', array( &$this, 'init' ), 1 );
	}


	public function init() {

		if ( ! function_exists( 'mailster' ) ) {

			add_action( 'admin_notices', array( &$this, 'notice' ) );
			return;

		}

		if ( is_admin() ) {

			add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
			add_filter( 'mailster_setting_sections', array( &$this, 'settings_tab' ), 1 );
			add_action( 'mailster_section_tab_ga', array( &$this, 'settings' ) );
			add_action( 'save_post', array( &$this, 'save_post' ), 10, 2 );

		}

		add_action( 'mailster_wpfooter', array( &$this, 'wpfooter' ) );
		add_filter( 'mailster_redirect_to', array( &$this, 'redirect_to' ), 999, 3 );
		add_filter( 'mailster_campaign_content', array( &$this, 'append_utm' ), 999, 3 );

	}


	public function append_utm( $content, $campaign, $subscriber ) {

		$campaign_id   = $campaign ? $campaign->ID : false;
		$subscriber_id = $subscriber ? $subscriber->ID : false;

		// do not append when tracking is enabled
		if ( mailster( 'campaigns' )->meta( $campaign_id, 'track_clicks' ) ) {
			return $content;
		}
		// get all links from the base content
		if ( preg_match_all( '# href=(\'|")?(https?[^\'"]+)(\'|")?#', $content, $links ) ) {

			$links = $links[2];

			if ( empty( $links ) ) {
				return $content;
			}

			foreach ( $links as $link ) {
				$content = str_replace( 'href="' . $link . '"', 'href="' . $this->map_utm( $link, $campaign_id, $subscriber_id ) . '"', $content );
			}
		}

		return $content;

	}


	public function redirect_to( $target, $campaign_id, $subscriber_id ) {

		return $this->map_utm( $target, $campaign_id, $subscriber_id );

	}


	private function map_utm( $link, $campaign_id, $subscriber_id ) {

		if ( ! mailster_option( 'ga_external_domains' ) ) {
			$link_domain = parse_url( $link, PHP_URL_HOST );
			$site_domain = parse_url( site_url(), PHP_URL_HOST );

			if ( $link_domain !== $site_domain ) {
				return $link;
			}
		}

		$subscriber = mailster( 'subscribers' )->get( $subscriber_id );
		$campaign   = mailster( 'campaigns' )->get( $campaign_id );

		if ( ! $campaign || $campaign->post_type != 'newsletter' ) {
			return $link;
		}

		$link = str_replace( '&amp;', '&', $link );

		$hash  = get_query_var( '_mailster_hash', ( isset( $_REQUEST['k'] ) ? preg_replace( '/\s+/', '', $_REQUEST['k'] ) : null ) );
		$count = get_query_var( '_mailster_extra', ( isset( $_REQUEST['c'] ) ? intval( $_REQUEST['c'] ) : null ) );

		$search  = array( '%%CAMP_ID%%', '%%CAMP_TITLE%%', '%%CAMP_TYPE%%', '%%CAMP_LINK%%', '%%SUBSCRIBER_ID%%', '%%SUBSCRIBER_EMAIL%%', '%%SUBSCRIBER_HASH%%', '%%LINK%%' );
		$replace = array(
			$campaign->ID,
			$campaign->post_title,
			$campaign->post_status == 'autoresponder' ? 'autoresponder' : 'regular',
			get_permalink( $campaign->ID ),
			$subscriber ? $subscriber->ID : '',
			$subscriber ? $subscriber->email : '',
			$subscriber ? $subscriber->hash : '',
			$link,
		);

		$values = wp_parse_args( get_post_meta( $campaign->ID, 'mailster-ga', true ), mailster_option( 'ga' ) );

		parse_str( parse_url( $link, PHP_URL_QUERY ), $link_query );
		$values = wp_parse_args( $link_query, $values );

		$utms = array(
			'utm_source'   => rawurldecode( str_replace( $search, $replace, $values['utm_source'] ) ),
			'utm_medium'   => rawurldecode( str_replace( $search, $replace, $values['utm_medium'] ) ),
			'utm_term'     => rawurldecode( str_replace( $search, $replace, $values['utm_term'] ) ),
			'utm_content'  => rawurldecode( str_replace( $search, $replace, $values['utm_content'] ) ),
			'utm_campaign' => rawurldecode( str_replace( $search, $replace, $values['utm_campaign'] ) ),
		);

		$link = add_query_arg( $utms, $link );

		return $link;
	}


	public function save_post( $post_id, $post ) {

		if ( isset( $_POST['mailster_ga'] ) && $post->post_type == 'newsletter' ) {

			$save = get_post_meta( $post_id, 'mailster-ga', true );

			$ga_values = mailster_option( 'ga' );

			$save = wp_parse_args( $_POST['mailster_ga'], $save );
			update_post_meta( $post_id, 'mailster-ga', $save );

		}

	}


	public function settings_tab( $settings ) {

		$position = 11;
		$settings = array_slice( $settings, 0, $position, true ) +
					array( 'ga' => 'Google Analytics' ) +
					array_slice( $settings, $position, null, true );

		return $settings;
	}


	public function add_meta_boxes() {

		if ( mailster_option( 'ga_campaign_based' ) ) {
			add_meta_box( 'mailster_ga', 'Google Analytics', array( $this, 'metabox' ), 'newsletter', 'side', 'low' );
		}
	}


	public function metabox() {

		global $post;

		$readonly = ( in_array( $post->post_status, array( 'finished', 'active' ) ) || $post->post_status == 'autoresponder' && ! empty( $_GET['showstats'] ) ) ? 'readonly disabled' : '';

		$values = wp_parse_args( get_post_meta( $post->ID, 'mailster-ga', true ), mailster_option( 'ga' ) );

		?>
		<style>#mailster_ga {display: inherit;}</style>
		<p><label><?php esc_html_e( 'Campaign Source', 'mailster-google-analytics' ); ?>*:<input type="text" name="mailster_ga[utm_source]" value="<?php echo esc_attr( $values['utm_source'] ); ?>" class="widefat" <?php echo $readonly; ?>></label></p>
		<p><label><?php esc_html_e( 'Campaign Medium', 'mailster-google-analytics' ); ?>*:<input type="text" name="mailster_ga[utm_medium]" value="<?php echo esc_attr( $values['utm_medium'] ); ?>" class="widefat" <?php echo $readonly; ?>></label></p>
		<p><label><?php esc_html_e( 'Campaign Term', 'mailster-google-analytics' ); ?>:<input type="text" name="mailster_ga[utm_term]" value="<?php echo esc_attr( $values['utm_term'] ); ?>" class="widefat" <?php echo $readonly; ?>></label></p>
		<p><label><?php esc_html_e( 'Campaign Content', 'mailster-google-analytics' ); ?>:<input type="text" name="mailster_ga[utm_content]" value="<?php echo esc_attr( $values['utm_content'] ); ?>" class="widefat" <?php echo $readonly; ?>></label></p>
		<p><label><?php esc_html_e( 'Campaign Name', 'mailster-google-analytics' ); ?>*: <input type="text" name="mailster_ga[utm_campaign]" value="<?php echo esc_attr( $values['utm_campaign'] ); ?>" class="widefat" <?php echo $readonly; ?>></label></p>
		<?php
	}

	public function settings() {

		include $this->plugin_path . '/views/settings.php';

	}


	public function notice() {
		$msg = sprintf( esc_html__( 'You have to enable the %s to use the Google Analytics Extension!', 'mailster-google-analytics' ), '<a href="https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=plugin&utm_term=Google+Analytics+for+Mailster">Mailster Newsletter Plugin</a>' );
		?>
		<div class="error"><p><strong><?php	echo $msg; ?></strong></p></div>
		<?php

	}


	public function wpfooter() {

		$ua            = mailster_option( 'ga_id' );
		$setDomainName = mailster_option( 'ga_setdomainname' );

		if ( ! $ua ) {
			return;
		}
		?>

	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '<?php echo $ua; ?>']);
		<?php echo $setDomainName ? "_gaq.push(['_setDomainName', '$setDomainName']);" : ''; ?>
		_gaq.push(['_trackPageview']);
		(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
		<?php

	}

	public function activate() {

		if ( function_exists( 'mailster' ) ) {

			if ( ! mailster_option( 'ga_id' ) ) {
				mailster_notice( sprintf( esc_html__( 'Please enter your Web Property ID on the %s!', 'mailster-google-analytics' ), '<a href="edit.php?post_type=newsletter&page=mailster_settings&mailster_remove_notice=google_analytics#ga">Settings Page</a>' ), '', false, 'google_analytics' );
			}

			$defaults = array(
				'ga'                  => array(
					'utm_source'   => 'newsletter',
					'utm_medium'   => 'email',
					'utm_term'     => '%%LINK%%',
					'utm_content'  => '',
					'utm_campaign' => '%%CAMP_TITLE%%',
				),
				'ga_external_domains' => true,
			);

			$mailster_options = mailster_options();

			foreach ( $defaults as $key => $value ) {
				if ( ! isset( $mailster_options[ $key ] ) ) {
					mailster_update_option( $key, $value );
				}
			}
		}

	}


}
