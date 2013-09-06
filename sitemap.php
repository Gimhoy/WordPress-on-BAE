<?php
include 'includes/connection.php';
require('./wp-blog-header.php');
header("Content-type: text/xml");
header('HTTP/1.1 200 OK');
$posts_to_show = 1000; // 获取文章数量
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>'; // XSL地址
echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
?>
<!-- generated-on=<?php echo get_lastpostdate('blog'); ?> Modified Gimhoy(http://blog.gimhoy.com)-->
  <url>
      <loc><?php echo get_home_url(); ?></loc>
      <lastmod><?php $ltime = get_lastpostmodified(GMT);$ltime = gmdate('Y-m-d\TH:i:s+00:00', strtotime($ltime)); echo $ltime; ?></lastmod>
      <changefreq>daily</changefreq>
      <priority>1.0</priority>
  </url>
<?php
header("Content-type: text/xml");
$myposts = get_posts( "numberposts=" . $posts_to_show );
foreach( $myposts as $post ) { ?>
  <url>
      <loc><?php the_permalink(); ?></loc>
      <lastmod><?php the_time('c') ?></lastmod>
      <changefreq>monthly</changefreq>
      <priority>0.6</priority>
  </url>
<?php } // end foreach ?>
</urlset>