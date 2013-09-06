<?php
/**
 * Retrieves and creates the wp-config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the wp-config.php to be created using this page.
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * We are installing.
 *
 * @package WordPress
 */
define('WP_INSTALLING', true);

/**
 * We are blissfully unaware of anything.
 */
define('WP_SETUP_CONFIG', true);

/**
 * Disable error reporting
 *
 * Set this to error_reporting( E_ALL ) or error_reporting( E_ALL | E_STRICT ) for debugging
 */
error_reporting(0);

/**#@+
 * These three defines are required to allow us to use require_wp_db() to load
 * the database class while being wp-content/db.php aware.
 * @ignore
 */
define('ABSPATH', dirname(dirname(__FILE__)).'/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_DEBUG', false);
/**#@-*/

require(ABSPATH . WPINC . '/load.php');
require(ABSPATH . WPINC . '/version.php');

// Check for the required PHP version and for the MySQL extension or a database drop-in.
wp_check_php_mysql_versions();

require_once(ABSPATH . WPINC . '/functions.php');

// Also loads plugin.php, l10n.php, pomo/mo.php (all required by setup-config.php)
wp_load_translations_early();

// Turn register_globals off.
wp_unregister_GLOBALS();

require_once(ABSPATH . WPINC . '/compat.php');
require_once(ABSPATH . WPINC . '/class-wp-error.php');
require_once(ABSPATH . WPINC . '/formatting.php');

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST )
wp_magic_quotes();

if ( ! file_exists( ABSPATH . 'wp-config-sample.php' ) )
	wp_die( __( 'Sorry, I need a wp-config-sample.php file to work from. Please re-upload this file from your WordPress installation.' ) );

$config_file = file(ABSPATH . 'wp-config-sample.php');

// Check if wp-config.php has been created
if ( file_exists( ABSPATH . 'wp-config.php' ) )
	wp_die( '<p>' . sprintf( __( "The file 'wp-config.php' already exists. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='%s'>installing now</a>." ), 'install.php' ) . '</p>' );

// Check if wp-config.php exists above the root directory but is not part of another install
if ( file_exists(ABSPATH . '../wp-config.php' ) && ! file_exists( ABSPATH . '../wp-settings.php' ) )
	wp_die( '<p>' . sprintf( __( "The file 'wp-config.php' already exists one level above your WordPress installation. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='install.php'>installing now</a>."), 'install.php' ) . '</p>' );

$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 0;

/**
 * Display setup wp-config.php file header.
 *
 * @ignore
 * @since 2.3.0
 * @package WordPress
 * @subpackage Installer_WP_Config
 */
function setup_config_display_header() {
	global $wp_version;

	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php _e( 'WordPress &rsaquo; Setup Configuration File' ); ?></title>
<link rel="stylesheet" href="css/install.css?ver=<?php echo preg_replace( '/[^0-9a-z\.-]/i', '', $wp_version ); ?>" type="text/css" />
<link rel="stylesheet" href="../wp-includes/css/buttons.css?ver=<?php echo preg_replace( '/[^0-9a-z\.-]/i', '', $wp_version ); ?>" type="text/css" />

</head>
<body class="wp-core-ui<?php if ( is_rtl() ) echo ' rtl'; ?>">
<h1 id="logo"><a href="<?php esc_attr_e( 'http://wordpress.org/' ); ?>"><?php _e( 'WordPress' ); ?></a></h1>
<?php
} // end function setup_config_display_header();

switch($step) {
	case 0:
		setup_config_display_header();
?>

<p><?php _e( 'Welcome to WordPress. Before getting started, we need some information on the database. You will need to know the following items before proceeding.' ) ?></p>
<ol>
	<li><?php _e( 'Database name' ); ?></li>
	<li><?php _e( 'Table prefix (if you want to run more than one WordPress in a single database)' ); ?></li>
	<li><?php _e( 'BCMS queue name (用于发送邮件，<a href="http://developer.baidu.com/bae/bms/list/" target="_blank">点击获取</a>)' ); ?></li>
</ol>
<p><strong><?php _e( "If for any reason this automatic file creation doesn&#8217;t work, don&#8217;t worry. All this does is fill in the database information to a configuration file. You may also simply open <code>wp-config-sample.php</code> in a text editor, fill in your information, and save it as <code>wp-config.php</code>." ); ?></strong></p>
<p><?php _e( "In all likelihood, these items were supplied to you by your Web Host. If you do not have this information, then you will need to contact them before you can continue. If you&#8217;re all ready&hellip;" ); ?></p>

<p class="step"><a href="setup-config.php?step=1<?php if ( isset( $_GET['noapi'] ) ) echo '&amp;noapi'; ?>" class="button button-large"><?php _e( 'Let&#8217;s go!' ); ?></a></p>
<?php
	break;

	case 1:
		setup_config_display_header();
	?>
<form method="post" action="setup-config.php?step=2">
	<p><?php _e( "Below you should enter your database connection details. If you&#8217;re not sure about these, contact your host." ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname"><?php _e( 'Database Name' ); ?></label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="wordpress" /></td>
			<td><?php _e( 'The name of the database you want to run WP in.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix"><?php _e( 'Table Prefix' ); ?></label></th>
			<td><input name="prefix" id="prefix" type="text" value="wp_" size="25" /></td>
			<td><?php _e( 'If you want to run multiple WordPress installations in a single database, change this.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="bcmsqueuename"><?php _e( 'BCMS Queue name' ); ?></label></th>
			<td><input name="bcmsqueuename" id="bcmsqueuename" type="text" value="" size="50" /></td>
			<td><?php _e( '如果你希望使用邮件功能，请输入' ); ?><a href="http://developer.baidu.com/bae/bms/list/" target="_blank">消息队列名称</a></td>
		</tr>
	</table>
	<?php if ( isset( $_GET['noapi'] ) ) { ?><input name="noapi" type="hidden" value="1" /><?php } ?>
	<p class="step"><input name="submit" type="submit" value="<?php echo htmlspecialchars( __( 'Submit' ), ENT_QUOTES ); ?>" class="button button-large" /></p>
</form>
<?php
	break;

	case 2:
	foreach ( array( 'dbname', 'prefix', "bcmsqueuename" ) as $key )
		$$key = trim( wp_unslash( $_POST[ $key ] ) );

	$tryagain_link = '</p><p class="step"><a href="setup-config.php?step=1" onclick="javascript:history.go(-1);return false;" class="button button-large">' . __( 'Try again' ) . '</a>';

	if ( empty( $prefix ) )
		wp_die( __( '<strong>ERROR</strong>: "Table Prefix" must not be empty.' . $tryagain_link ) );

	// Validate $prefix: it can only contain letters, numbers and underscores.
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
		wp_die( __( '<strong>ERROR</strong>: "Table Prefix" can only contain numbers, letters, and underscores.' . $tryagain_link ) );

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define('DB_NAME', $dbname);
	define('DB_USER', getenv('HTTP_BAE_ENV_AK'));
	define('DB_PASSWORD', getenv('HTTP_BAE_ENV_SK'));
	define('DB_HOST', getenv('HTTP_BAE_ENV_ADDR_SQL_IP') . ":" . getenv('HTTP_BAE_ENV_ADDR_SQL_PORT'));
	define('BCMS_QUEUE', $bcmsqueuename);
	/**#@-*/

	// We'll fail here if the values are no good.
	require_wp_db();
	if ( ! empty( $wpdb->error ) )
		wp_die( $wpdb->error->get_error_message() . $tryagain_link );

	// Fetch or generate keys and salts.
	$no_api = isset( $_POST['noapi'] );
	if ( ! $no_api ) {
		require_once( ABSPATH . WPINC . '/class-http.php' );
		require_once( ABSPATH . WPINC . '/http.php' );
		wp_fix_server_vars();
		/**#@+
		 * @ignore
		 */
		function get_bloginfo() {
			return ( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . str_replace( $_SERVER['PHP_SELF'], '/wp-admin/setup-config.php', '' ) );
		}
		/**#@-*/
		$secret_keys = wp_remote_get( 'https://api.wordpress.org/secret-key/1.1/salt/' );
	}

	if ( $no_api || is_wp_error( $secret_keys ) ) {
		$secret_keys = array();
		require_once( ABSPATH . WPINC . '/pluggable.php' );
		for ( $i = 0; $i < 8; $i++ ) {
			$secret_keys[] = wp_generate_password( 64, true, true );
		}
	} else {
		$secret_keys = explode( "\n", wp_remote_retrieve_body( $secret_keys ) );
		foreach ( $secret_keys as $k => $v ) {
			$secret_keys[$k] = substr( $v, 28, 64 );
		}
	}

	$key = 0;
	// Not a PHP5-style by-reference foreach, as this file must be parseable by PHP4.
	foreach ( $config_file as $line_num => $line ) {
		if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
			$config_file[ $line_num ] = '$table_prefix  = \'' . addcslashes( $prefix, "\\'" ) . "';\r\n";
			continue;
		}

		if ( ! preg_match( '/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match ) )
			continue;

		$constant = $match[1];
		$padding  = $match[2];

		switch ( $constant ) {
			case 'DB_NAME'     :
			case 'DB_USER'     :
			case 'DB_PASSWORD' :
			case 'DB_HOST'     :
			case 'BCMS_QUEUE'     :
				$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'" . addcslashes( constant( $constant ), "\\'" ) . "');\r\n";
				break;
			case 'AUTH_KEY'         :
			case 'SECURE_AUTH_KEY'  :
			case 'LOGGED_IN_KEY'    :
			case 'NONCE_KEY'        :
			case 'AUTH_SALT'        :
			case 'SECURE_AUTH_SALT' :
			case 'LOGGED_IN_SALT'   :
			case 'NONCE_SALT'       :
				$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'" . $secret_keys[$key++] . "');\r\n";
				break;
		}
	}
	unset( $line );


		setup_config_display_header();
?>
<p><?php _e( "Sorry, but I can&#8217;t write the <code>wp-config.php</code> file." ); ?></p>
<p><?php _e( 'You can create the <code>wp-config.php</code> manually and paste the following text into it.' ); ?></p>
<textarea id="wp-config" cols="98" rows="15" class="code" readonly="readonly"><?php
		foreach( $config_file as $line ) {
			echo htmlentities($line, ENT_COMPAT, 'UTF-8');
		}
?></textarea>
<p><?php _e( 'After you&#8217;ve done that, click &#8220;Run the install.&#8221;' ); ?></p>
<p class="step"><a href="install.php" class="button button-large"><?php _e( 'Run the install' ); ?></a></p>
<script>
(function(){
var el=document.getElementById('wp-config');
el.focus();
el.select();
})();
</script>
<?php
	break;
}
?>
</body>
</html>
