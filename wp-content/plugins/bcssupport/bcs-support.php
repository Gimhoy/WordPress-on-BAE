<?php
/**
 * @package BCS Support
 * @version 1.0.1
 */
/*
Plugin Name: BCS Support
Plugin URI: ""
Description: This is a plugin for bcs.
Author: HJin.me Modified by Gimhoy
Version: 1.0.1
Author URI: http://blog.gimhoy.com
*/

if ( !defined('WP_PLUGIN_URL') )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );                           //  plugin url

define('BCS_BASENAME', plugin_basename(__FILE__));
define('BCS_BASEFOLDER', plugin_basename(dirname(__FILE__)));
define('BCS_FILENAME', str_replace(DFM_BASEFOLDER.'/', '', plugin_basename(__FILE__)));

// 初始化选项
register_activation_hook(__FILE__, 'bcs_set_options');

/**
 * 初始化选项
 */
function bcs_set_options() {
    $options = array(
        'bucket' => "",
        'ak' => "",
    	'sk' => "",
    );
    
    add_option('bcs_options', $options, '', 'yes');
}


function bcs_admin_warnings() {
    $bcs_options = get_option('bcs_options', TRUE);

    $bcs_bucket = attribute_escape($bcs_options['bucket']);
	if ( !$bcs_options['bucket'] && !isset($_POST['submit']) ) {
		function bcs_warning() {
			echo "
			<div id='bcs-warning' class='updated fade'><p><strong>".__('Bcs is almost ready.')."</strong> ".sprintf(__('You must <a href="%1$s">enter your BCS Bucket </a> for it to work.'), "options-general.php?page=" . BCS_BASEFOLDER . "/bcs-support.php")."</p></div>
			";
		}
		add_action('admin_notices', 'bcs_warning');
		return;
	} 
}
bcs_admin_warnings();
/*
 Hook 所有上传操作，上传完成后再存到云存储。
 默认设置为 public
*/
function mv_attachments_to_bcs($data) {
	
	require_once('bcs.class.php');
	$bcs_options = get_option('bcs_options', TRUE);
    $bcs_bucket = attribute_escape($bcs_options['bucket']);
    if(false === getenv ( 'HTTP_BAE_ENV_AK' )) {
	    $bcs_ak = attribute_escape($bcs_options['ak']);
    }
    if(false === getenv ( 'HTTP_BAE_ENV_SK' )) {
	    $bcs_sk = attribute_escape($bcs_options['sk']);
    }
    
	$baidu_bcs = new BaiduBCS($bcs_ak, $bcs_sk);


	$bucket = $bcs_bucket;
	$object =  "/blog/" . basename($data['file']);
	$file = $data['file'];
	$opt = array(
		"acl" => "public-read"
	);
	$baidu_bcs->create_object ( $bucket, $object, $file, $opt );

	$url = "http://bcs.duapp.com/{$bucket}{$object}"; 
	
	return array( 'file' => $url, 'url' => $url, 'type' => $data['type'] );
}

add_filter('wp_handle_upload', 'mv_attachments_to_bcs');

function format_bcs_url($url) {
	if(strpos($url, "http://bcs.duapp.com") !== false) {
		$arr = explode("http://bcs.duapp.com", $url);
		$url = "http://bcs.duapp.com" . $arr[1];
	}
	return $url;
}
add_filter('wp_get_attachment_url', 'format_bcs_url');


function bcs_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/bcs-support.php' ) ) {
		$links[] = '<a href="options-general.php?page=' . BCS_BASEFOLDER . '/bcs-support.php">'.__('Settings').'</a>';
	}

	return $links;
}

add_filter( 'plugin_action_links', 'bcs_plugin_action_links', 10, 2 );

function bcs_add_setting_page() {
    add_options_page('BCS Setting', 'BCS Setting', 8, __FILE__, 'bcs_setting_page');
}

add_action('admin_menu', 'bcs_add_setting_page');

function bcs_setting_page() {

	$options = array();
	if($_POST['bucket']) {
		$options['bucket'] = trim(stripslashes($_POST['bucket']));
	}
	if($_POST['ak'] && false === getenv ( 'HTTP_BAE_ENV_AK' )) {
		$options['ak'] = trim(stripslashes($_POST['ak']));
	}
	if($_POST['sk'] && false === getenv ( 'HTTP_BAE_ENV_SK' )) {
		$options['sk'] = trim(stripslashes($_POST['sk']));
	}
	if($options !== array() ){
	
		update_option('bcs_options', $options);
        
?>
<div class="updated"><p><strong>设置已保存！</strong></p></div>
<?php
    }

    $bcs_options = get_option('bcs_options', TRUE);

    $bcs_bucket = attribute_escape($bcs_options['bucket']);
    $bcs_ak = attribute_escape($bcs_options['ak']);
    $bcs_sk = attribute_escape($bcs_options['sk']);
?>
<div class="wrap" style="margin: 10px;">
    <h2>百度云存储 设置</h2>
    <form name="form1" method="post" action="<?php echo wp_nonce_url('./options-general.php?page=' . BCS_BASEFOLDER . '/bcs-support.php'); ?>">
        <fieldset>
            <legend>Bucket 设置</legend>
            <input type="text" name="bucket" value="<?php echo $bcs_bucket;?>" placeholder="请输入云存储使用的 bucket"/>
            <p>请先访问 <a href="http://developer.baidu.com/bae/bcs/bucket/">百度云存储</a> 创建 bucket 后，填写以上内容。</p>
        </fieldset>
        <?php
        if ( false === getenv ( 'HTTP_BAE_ENV_AK' ) || false === getenv ( 'HTTP_BAE_ENV_SK' )) :
        ?>
        <fieldset>
            <legend>Access Key / API key</legend>
            <input type="text" name="ak" value="<?php echo $bcs_ak;?>" placeholder=""/>
            <p>访问 <a href="http://developer.baidu.com/bae/ref/key/" target="_blank">BAE 密钥管理页面</a>，获取 AKSK</p>
        </fieldset>
        <fieldset>
            <legend>Secret Key</legend>
            <input type="text" name="sk" value="<?php echo $bcs_sk;?>" placeholder=""/>
        </fieldset>
        <?php
        endif;
        ?>
        <fieldset class="submit">
            <legend>更新选项</legend>
            <input type="submit" name="submit" value="更新" />
        </fieldset>
    </form>
</div>
<?php
}
