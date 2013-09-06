<?php
require('./wp-blog-header.php');
header("Content-type: text/html");
$posts_to_show = 1000; // 获取文章数量
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>站点地图 - <?php bloginfo( 'name' ); ?></title>
<meta name="keywords" content="站点地图,<?php bloginfo( 'name' ); ?>" />
<meta name="generator" content="SiteMap Generator" />
<meta name="author" content="Gimhoy" />
<meta name="copyright" content="Gimhoy Studio, Gimhoy.com" />
<style type="text/css">
body {font-family:Verdana;FONT-SIZE:12px;MARGIN:0;color:#000000;background:#ffffff;}
img {border:0;}
li {margin-top:8px;list-style:none;}
.page {padding:4px;border-top:1px #EEEEEE solid}
.author {background-color:#EEEEFF;padding:6px;border-top:1px #ddddee solid}
#nav, #content, #footer {padding:8px;border:1px solid #EEEEEE;clear:both;width:95%;margin:auto;margin-top:10px;}
</style>
</head>
<body vlink="#333333" link="#333333">
<h2 style="text-align: center; margin-top: 20px"><?php bloginfo( 'name' ); ?> SiteMap </h2>
<center></center>
<div id="nav"><a href="<?php bloginfo('url'); ?>"><strong><?php bloginfo( 'name' ); ?></strong></a>  &raquo; <a href="<?php bloginfo('url'); ?>/sitemap.html">站点地图</a></div>
<div id="content1">
<h3>最新文章 RecentPosts</h3>
<ul>
<?php
$myposts = get_posts( "numberposts=" . $posts_to_show );
foreach( $myposts as $post ) { ?>
<li><span><?php the_time('[Y-m-d] ') ?><span><a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>" target="_blank"><?php the_title(); ?></a></li>
<?php } // end foreach ?>
</ul>
</div><!--文章-->
<div id="content2">
<?php wp_list_categories('orderby=name&show_count=1&title_li=<h3>分类目录 Categories</h3>&depth=3'); ?>
</div><!--分类-->
<div id="content3">
<h3>页面 Pages</h3>
<ul><?php wp_list_pages('title_li=&depth=1'); ?></ul>
</div><!--页面-->
<div id="footer">浏览博客首页 <strong><a href="<?php bloginfo('url'); ?>"><?php bloginfo( 'name' ); ?></a></strong></div><br />
<center>
<div style="text-algin: center; font-size: 11px"><strong><a href="<?php bloginfo('url'); ?>/sitemap.xml" target="_blank">SiteMap.xml</a></strong> &nbsp;
Latest Update: <?php echo get_lastpostdate('blog'); ?><br /><br /></div>
</center>
<center>
<div style="text-algin: center; font-size: 11px">&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?> | Sitemap by <a href="http://blog.gimhoy.com/" target="_blank" title="Gimhoy">Gimhoy Studio</a><br /><br /></div>
</center>
</body>
</html>