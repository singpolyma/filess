<?php

if(!$_REQUEST['id'])
   die('<b>No ID specified!</b>');

require_once 'XNC/Comment.php';
require_once 'removeEvils.php';

$file = XN_Content::load(intval($_REQUEST['id']));
$newComment = new XNC_Comment($file);
$comments = $file->my->content($newComment->referenceAttribute,true);
$firstcomment = new XNC_Comment($comments[0]);

header('Content-Type: application/xml;charset=utf-8');
echo '<?xml version="1.0"?>'."\n";

?>
<rss version="2.0">
<channel>
   <title>Comments on <?php echo $file->title ?></title>
   <description>On App <?php echo XN_Application::load()->name; ?></description>
   <link>http://<?php echo $_SERVER['HTTP_HOST']; ?>/view.php?id=<?php echo $file->id; ?></link>
   <docs>http://blogs.law.harvard.edu/tech/rss</docs>
   <generator><?php echo XN_Application::load()->name; ?> (PHP Script)</generator>
   <pubDate><?php echo date('r',strtotime($firstcomment->createdDate)); ?></pubDate>
   <lastBuildDate><?php echo date('r',strtotime($firstcomment->createdDate)); ?></lastBuildDate>

<?php
   foreach($comments as $comment) {
      $data = new XNC_Comment($comment);
?>
   <item>
      <title><?php echo 'Comment by '.$data->contributorName; ?></title>
      <description><?php echo htmlspecialchars(removeEvilTags(nl2br($data->description))); ?></description>
      <pubDate><?php echo date('r',strtotime($data->createdDate)); ?></pubDate>
      <link>http://<?php echo $_SERVER['HTTP_HOST']; ?>/view.php?id=<?php echo $file->id.'#c'.$data->id; ?></link>
      <guid>http://<?php echo $_SERVER['HTTP_HOST']; ?>/view.php?id=<?php echo $file->id.'#c'.$data->id; ?></guid>
   </item>
<?php } ?>

</channel>
</rss>