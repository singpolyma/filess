<?php

require_once 'tagFunctions.php';
require_once 'XNC/HTML.php';

$setup = XN_Query::create('Content')
         ->filter('owner','=')
         ->filter('type','eic','Setup');
$setup = $setup->execute();
if(count($setup))
   $setup = $setup[0];
else
   $setup = false;

if(!$setup) {
   header('Content-Type: text/plain;');
   header('Location: http://'.$_SERVER['HTTP_HOST'].'/setup.php',true,303);
   exit;
}//end if ! setup

$query = XN_Query::create('Content')
         ->filter('owner','=')
         ->filter('type','eic',$setup->my->single)
         ->alwaysReturnTotalCount(true);

//sort by time
$query->order('createdDate','desc',XN_Attribute::NUMBER);

if($_REQUEST['user'])
   $query->filter('contributorName','=',$_REQUEST['user']);
if($_REQUEST['tag']) {
   $_REQUEST['tag'] = str_replace(' ','+',$_REQUEST['tag']);
   foreach(explode('+',$_REQUEST['tag']) as $tag)
      $query->filter('tag.value','eic',$tag);
}//end if tag
if($_REQUEST['q'])
   $query->filter('description','likeic',$_REQUEST['q']);

// handle pagination
$_REQUEST['maxitems'] = $_REQUEST['maxitems'] ? $_REQUEST['maxitems'] : 20;
$start = (isset($_REQUEST['start']) && ctype_digit($_REQUEST['start']) ? $_REQUEST['start'] : 0);
$end = $start + $_REQUEST['maxitems'];
$query->begin($start)->end($end);

$items = $query->execute();

// prepare pagination numbers for displaying result set info
$total = $query->getTotalCount();
$from = ($query->getResultFrom() != 0 ? 1+$query->getResultFrom() : 1);
$to = $query->getResultTo();

$title = 'Recent '.$setup->my->plural;
if($_REQUEST['tag'])
   $title .= ' in '.$_REQUEST['tag'];
if($_REQUEST['user'])
   $title .= ' from '.$_REQUEST['user'];
if($_REQUEST['q'])
   $title .= ' matching &quot;'.$_REQUEST['q'].'&quot;';

if($_REQUEST['format'] == 'rss20' || $_REQUEST['format'] == 'rss') {
   header('Content-Type: application/xml;charset=utf-8');
   echo '<?xml version="1.0"?>'."\n";
   ?>
<rss version="2.0" xmlns:wfw="http://wellformedweb.org/CommentAPI/">
<channel>
   <title><?php echo htmlspecialchars($title); ?></title>
   <description><?php echo htmlspecialchars(nl2br($setup->my->homepageblurb)); ?></description>
   <link>http://<?php echo $_SERVER['HTTP_HOST'].'/'.($_REQUEST['user'] || $_REQUEST['tag'] || $_REQUEST['q'] ? '?' : '').($_REQUEST['user'] ? 'user='.$_REQUEST['user'] : '').($_REQUEST['user'] && $_REQUEST['tag'] ? '&amp;' : '').($_REQUEST['tag'] ? 'tag='.$_REQUEST['tag'] : '').(($_REQUEST['user'] || $_REQUEST['tag']) && $_REQUEST['q'] ? '&amp;' : '').($_REQUEST['q'] ? 'q='.$_REQUEST['q'] : ''); ?></link>
   <docs>http://blogs.law.harvard.edu/tech/rss</docs>
   <generator><?php echo XN_Application::load()->name; ?> (PHP Script)</generator>
   <pubDate><?php echo date('r',strtotime($items[0]->createdDate)); ?></pubDate>
   <lastBuildDate><?php echo date('r',strtotime($items[0]->createdDate)); ?></lastBuildDate>

<?php
   foreach($items as $item) {
?>
   <item>
      <title><?php echo htmlspecialchars($item->title); ?></title>
      <description><?php echo htmlspecialchars(nl2br($item->description).'<br /><br /><a href="http://'.$_SERVER['HTTP_HOST'].'/get.php?xn_auth=no&amp;id='.$item->id.'">Get '.$setup->my->single.'</a>'); ?></description>
      <pubDate><?php echo date('r',strtotime($item->createdDate)); ?></pubDate>
      <link>http://<?php echo $_SERVER['HTTP_HOST']; ?>/xn/detail/<?php echo $item->id; ?></link>
      <guid>http://<?php echo $_SERVER['HTTP_HOST']; ?>/xn/detail/<?php echo $item->id; ?></guid>
      <wfw:commentRSS><?php echo 'http://'.$_SERVER['HTTP_HOST'].'/commentRSS.php?xn_auth=no&amp;id='.$item->id; ?></wfw:commentRSS>
   </item>
<?php } ?>

</channel>
</rss>
   <?php
   exit;
}//end if format == rss20 || rss

echo ' <a style="float:right;" href="http://'.$_SERVER['HTTP_HOST'].'/?xn_auth=no&amp;format=rss20'.($_REQUEST['user'] ? '&amp;user='.$_REQUEST['user'] : '').($_REQUEST['tag'] ? '&amp;tag='.$_REQUEST['tag'] : '').($_REQUEST['q'] ? '&amp;q='.urlencode($_REQUEST['q']) : '').'" rel="alternate"><img src="http://wrinks.ning.com/feedicon12x12.png" alt="[feed]" /></a>';

echo '<p>'.nl2br($setup->my->homepageblurb).'</p>';

echo '<h2 class="pagetitle">'.$title.' ('.$from.' - '.$to.' of '.$total.')</h2>';
echo '<xn:head><title>'.XN_Application::load()->name.' - '.$title.'</title></xn:head>';

echo '<ul>';
foreach($items as $item) {
   echo '<li><a href="/xn/detail/'.$item->id.'">'.$item->h('title').'</a>'.($item->description ? ' - ' : '').substr(strip_tags($item->description),0,100).((strlen(strip_tags($item->description)) > 100) ? '...' : '').'</li>';
}//end foreach
echo '</ul>';
if($to < $total)
   echo '<p><a href="/?'.$_SERVER['QUERY_STRING'].'&amp;start='.$to.'">More &raquo;</a></p>';

echo '<h2 class="pagetitle">Tags</h2>';
if($_REQUEST['user'])
   echo XNC_HTML::buildMap(getTagCount(), '/user/'.$_REQUEST['user'].'/?tag=%s','',true,60,300);
else
   echo XNC_HTML::buildMap(getTagCount(), '/?tag=%s','',true,60,300);

?>