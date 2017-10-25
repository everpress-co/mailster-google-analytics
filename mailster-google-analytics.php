<?php
/*
Plugin Name: Mailster Google Analytics
Description: Integrates Google Analytics with Mailster Newsletter Plugin to track your clicks with the popular Analytics service
Version: 1.1
Author: EverPress
Author URI: https://mailster.co
Text Domain: mailster-google-analytics
License: GPLv2 or later
 */

define( 'MAILSTER_GA_VERSION', '1.1' );
define( 'MAILSTER_GA_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/classes/google.analytics.class.php';
new MailsterGoogleAnalytics();
