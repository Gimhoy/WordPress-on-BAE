<?php
/**
 * @package BCS Support
 * @version 1.2.2
 */
/*
Plugin Name: BCS Support
Plugin URI: http://blog.gimhoy.com/archives/bcs-support.html
Description: This is a plugin for bcs.
Author: Gimhoy
Version: 1.2.2
Author URI: http://blog.gimhoy.com
*/

if ( !defined('WP_PLUGIN_URL') )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );                           //  plugin url

define('BCS_BASENAME', plugin_basename(__FILE__));
define('BCS_BASEFOLDER', plugin_basename(dirname(__FILE__)));
define('BCS_FILENAME', str_replace(BCS_BASEFOLDER.'/', '', plugin_basename(__FILE__)));

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
		'referer' => "",
		'referer2' => "",
		'is_Enabled_asl'  => "",
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
	$is_Enabled_asl = attribute_escape($bcs_options['is_Enabled_asl']);
    
	$baidu_bcs = new BaiduBCS($bcs_ak, $bcs_sk);


	$bucket = $bcs_bucket;
	$year = date("Y");
 	$month = date("m");
  	$object =  "/blog/".$year.$month."/".basename($data['file']);
	$file = $data['file'];
	$opt = array(
		"acl" => "public-read"
	);
	$baidu_bcs->create_object ( $bucket, $object, $file, $opt );
	if($is_Enabled_asl){
		$referer = attribute_escape($bcs_options['referer']);
		$referer2 = attribute_escape($bcs_options['referer2']);
		if(!empty($referer)){
			if(!empty($referer2)){
				$referer = array($referer, $referer2);
				}
			else{
				$referer = array($referer);
				}
		}
		else{
				$referer = preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
				$referer = '*.'.preg_replace('#^blog\.#', '', $referer).'/*';
				$referer = array($referer);
		}
		$acl = array (
			'statements' => array (
					'0' => array (
							'user' => array (
									"*" ), 
							'resource' => array (
									$bucket . $object), 
							'action' => array (
									BaiduBCS::BCS_SDK_ACL_ACTION_GET_OBJECT
									 ), 
							'effect' => BaiduBCS::BCS_SDK_ACL_EFFECT_ALLOW,
							'referer' => $referer
									  ) ) );
		$baidu_bcs->set_object_acl ( $bucket, $object, $acl );
	}
	$url = "http://bcs.duapp.com/{$bucket}{$object}"; 
	
	return array( 'file' => $url, 'url' => $url, 'type' => $data['type'] );
}

add_filter('wp_handle_upload', 'mv_attachments_to_bcs');

function xml_to_bcs($methods) {
    $methods['wp.uploadFile'] = 'xmlrpc_upload';
    $methods['metaWeblog.newMediaObject'] = 'xmlrpc_upload';
    return $methods;
}
//hook所有xmlrpc的上传
add_filter( 'xmlrpc_methods', 'xml_to_bcs' );
function xmlrpc_upload($args){
    $data  = $args[3];
		$name = sanitize_file_name( $data['name'] );
		$type = $data['type'];
		$bits = $data['bits'];
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
	$object =  "/" . $name;
	$opt = array(
		"acl" => "public-read"
	);
	$baidu_bcs->create_object_by_content ( $bucket, $object, $bits, $opt );
	$url = "http://bcs.duapp.com/{$bucket}{$object}"; 
	
	return array( 'file' => $url, 'url' => $url, 'type' => $data['type'] );
}

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

//删除BCS上的附件 Thanks Loveyuki（loveyuki@gmail.com）
function del_attachments_from_bcs($file) {
require_once('bcs.class.php');

$bcs_options = get_option('bcs_options', TRUE);

$bcs_bucket = attribute_escape($bcs_options['bucket']);

if(false === getenv ( 'HTTP_BAE_ENV_AK' )) {
$bcs_ak = attribute_escape($bcs_options['ak']);
}

if(false === getenv ( 'HTTP_BAE_ENV_SK' )) {
$bcs_sk = attribute_escape($bcs_options['sk']);
}

if(!is_object($baidu_bcs))
$baidu_bcs = new BaiduBCS($bcs_ak, $bcs_sk);

$bucket = $bcs_bucket;

$upload_dir = wp_upload_dir();

$object = str_replace($upload_dir['basedir'],'',$file);
$object = ltrim( $object , '/' );

$object = str_replace('http://bcs.duapp.com/'.$bucket,'',$object);

$baidu_bcs->delete_object($bcs_bucket,$object);

return $file;
}

add_action('wp_delete_file', 'del_attachments_from_bcs');

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
	if($_POST['referer']) {
		$options['referer'] = trim(stripslashes($_POST['referer']));
	}	
	if($_POST['referer2']) {
		$options['referer2'] = trim(stripslashes($_POST['referer2']));
	}	
	if($_POST['is_Enabled_asl']) {
		$options['is_Enabled_asl'] = trim(stripslashes($_POST['is_Enabled_asl']));
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
	$bcs_referer = attribute_escape($bcs_options['referer']);
	$bcs_referer2 = attribute_escape($bcs_options['referer2']);
	$is_Enabled_asl = attribute_escape($bcs_options['is_Enabled_asl']);
?>
<div class="wrap" style="margin: 10px;">
    <h2>百度云存储 设置</h2> 
	<a href="http://blog.gimhoy.com/archives/bcs-support.html" target="_blank">帮助</a>
	<a href="http://blog.gimhoy.com/archives/bcs-support.html" target="_blank">反馈建议</a>
	<a href="http://blog.gimhoy.com/archives/bcs-support.html" target="_blank">下载最新版本</a>
	<a href="https://me.alipay.com/gimhoy" target="_blank">捐赠</a>
    <form name="form1" method="post" action="<?php echo wp_nonce_url('./options-general.php?page=' . BCS_BASEFOLDER . '/bcs-support.php'); ?>">
	  	<h3>基本设置</h3>	
        <fieldset>
            <legend>Bucket 设置</legend>
            <input type="text" name="bucket" value="<?php echo $bcs_bucket;?>" placeholder="请输入云存储使用的 bucket"/>
            请先访问 <a href="http://developer.baidu.com/bae/bcs/bucket/">百度云存储</a> 创建 bucket 后，填写以上内容
			<p></p>
        </fieldset>
       <?php
       if ( false === getenv ( 'HTTP_BAE_ENV_AK' ) || false === getenv ( 'HTTP_BAE_ENV_SK' )) :
       ?>
        <fieldset>
            <legend>Access Key / API key</legend>
            <input type="text" name="ak" value="<?php echo $bcs_ak;?>" placeholder=""/>
            <p></p>
        </fieldset>
        <fieldset>
            <legend>Secret Key</legend>
            <input type="text" name="sk" value="<?php echo $bcs_sk;?>" placeholder=""/>
			访问 <a href="http://developer.baidu.com/bae/ref/key/" target="_blank">BAE 密钥管理页面</a>，获取 AK/SK
        </fieldset>
	   <?php
       endif;
       ?>
	  	<h3>反盗链设置</h3>	
        <fieldset>
            <legend>开启反盗链<input type="checkbox" name="is_Enabled_asl" value="1" <?php if( $is_Enabled_asl ) { echo 'checked="checked"'; } ?> /></legend>	
			<p></p>
        </fieldset>	
		<fieldset>
            <legend>域名</legend>
            <input type="text" name="referer" value="<?php echo $bcs_referer;?>" placeholder=""/>
            反盗链设置，请按以下格式输入：*.gimhoy.com/*   若需设置多个域名，请在下面的域名2中继续输入（每个空格只限一个域名）。仅开启反盗链后有效
			<p></p>
		</fieldset>	
		<fieldset>
            <legend>域名2</legend>
            <input type="text" name="referer2" value="<?php echo $bcs_referer2;?>" placeholder=""/>
            如只有一个域名可不填写
        </fieldset>			
        <fieldset class="submit">
            <input type="submit" name="submit" value="保存更新" />
        </fieldset>
    </form>
	<h2>赞助</h2>
		<p>如果你发现这个插件对你有帮助，欢迎<a href="https://me.alipay.com/gimhoy" target="_blank">赞助</a>!</p>
		<p><a href="https://me.alipay.com/gimhoy" target="_blank"><img src="http://archives.gimhoy.cn/archives/alipay_donate.png" alt="支付宝捐赠" title="支付宝" /></a></p>
	<br />
</div>
<?php
}