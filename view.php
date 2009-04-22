<div style="margin:0px;padding:0px;" class="hfeed hentry">
<?php

if(!$_REQUEST['id'])
   die('<b>No ID specified!</b>');

require_once 'tagFunctions.php';
require_once 'getSweeties.php';

$setup = XN_Query::create('Content')
         ->filter('owner','=')
         ->filter('type','eic','Setup');
$setup = $setup->execute();
$setup = $setup[0];

$file = XN_Content::load(intval($_REQUEST['id']));
$file->focus();

$sweeties = getSweeties($file->id);

echo '<div id="sidebar" style="float:right;width:200px;">'."\n";
echo '<ul>'."\n";

echo '   <li>Tags ';
foreach(fetchTags($file) as $tag)
   echo '<a href="http://'.$_SERVER['HTTP_HOST'].'/?tag='.$tag.'" rel="tag">'.$tag.'</a> ';
echo '</li>'."\n";

echo '   <li><address style="font-style:normal;" class="author vcard">User - <a href="http://'.$_SERVER['HTTP_HOST'].'/?user='.$file->contributorName.'" class="url fn user">'.$file->contributorName.'</a></address></li>'."\n";

echo '   <li style="margin-bottom:20px;">Updated - <span class="updated"><abbr title="'.date('c',strtotime($file->updatedDate)).'">'.date('Y-m-d',strtotime($file->updatedDate)).'</abbr></span></li>'."\n";

foreach(unserialize($setup->my->fields) as $field) {
   if(!$file->my->$field) continue;
   if(substr($file->my->$field,0,4) == 'http')
      echo '   <li><a href="'.$file->my->$field.'">'.$field.'</a></li>'."\n";
   else
      echo '   <li>'.$field.' - '.$file->my->$field.'</li>'."\n";
}//end foreach fields

echo '</ul>'."\n";

if(!count($sweeties))
   echo '<br /><a href="http://sweeties.ning.com/give.php?recip_id='.$file->id.'&amp;giver_app='.XN_Application::load()->relativeUrl.'&amp;url='.urlencode('http://'.$_SERVER['HTTP_HOST'].'/view.php?id='.$file->id).'"><img src="http://sweeties.ning.com/img/toffee.gif" alt="Add Sweetie" title="Add Sweetie" /></a>';
if(XN_Profile::current()->screenName == $file->contributorName)
   echo '<br /><a href="/addORedit.php?id='.$file->id.'">Edit &raquo;</a><br /><a href="/delete.php?id='.$file->id.'">Delete &raquo;</a>';
   echo '<br /><a href="http://'.$_SERVER['HTTP_HOST'].'/view.php?id='.$file->id.'" rel="bookmark">permalink</a>';
echo '</div>'."\n";

echo '<h2 class="entry-title">'.$file->title.'</h2>'."\n";
echo '<xn:head><title>'.XN_Application::load()->name.' - '.$file->title.'</title></xn:head>';
echo '<p class="entry-content">'.nl2br($file->description).'</p>'."\n";

echo '<p><a href="http://'.$_SERVER['HTTP_HOST'].'/id/'.$file->id.'" rel="external">Get '.$setup->my->single.'</a>';
$size = strlen(base64_decode($file->my->data));
$sizeunit = ' bytes';
if($size > 1000) {
   $size /= 1000;
   $sizeunit = ' K';
}//end if size > 1000
if($size > 1000) {
   $size /= 1000;
   $sizeunit = ' M';
}//end if size > 1000
echo ' <span style="font-style:italic;font-size:10pt;">('.round($size,1).$sizeunit.')</span> ';
echo '</p>'."\n";
echo '<br />'."\n";

if(count($sweeties)) {
   echo '<h3 id="sweeties" style="display:inline;margin:0px;padding:0px;">Sweeties</h3>';
   echo '<a href="http://sweeties.ning.com/give.php?recip_id='.$file->id.'&amp;giver_app='.XN_Application::load()->relativeUrl.'&amp;url='.urlencode('http://'.$_SERVER['HTTP_HOST'].'/view.php?id='.$file->id).'"><img src="http://sweeties.ning.com/img/toffee.gif" alt="Add Sweetie" title="Add Sweetie" /></a><br />';
   echo '<ul>'."\n";
   foreach($sweeties as $sweetie) {
      echo '   <li id="sweetie-'.$sweetie['sweetie'].'">';
      echo '<a href="http://sweeties.ning.com/sweetieTrail.php?sweetie='.$sweetie['sweetie'].'">';
      echo '<img src="http://sweeties.ning.com/img/'.str_replace(' ','_',$sweetie['flavour']).'.gif" alt="" /></a> ';
      echo $sweetie['flavour'];
      echo ' given at '.date('Y-m-d H:i',$sweetie['timestamp']);
      echo ' by <a href="http://browse.ning.com/any/'.$sweetie['giver'].'/any/any" class="user">'.$sweetie['giver'].'</a>';
      if($sweetie['message'])
         echo '<br />'.$sweetie['message'];
      echo '</li>'."\n";
   }//end foreach sweeties
   echo '</ul>'."\n";
}//end if count sweeties

echo '<h3 id="comments" style="display:inline;margin:0px;padding:0px;">Comments</h3>'."\n";
echo ' <a href="http://'.$_SERVER['HTTP_HOST'].'/view.php?id='.$file->id.'#comments" rel="comments">&raquo;</a> '."\n";
echo '<a href="http://'.$_SERVER['HTTP_HOST'].'/commentRSS.php?xn_auth=no&amp;id='.$file->id.'" rel="comments alternate"><img src="http://wrinks.ning.com/feedicon12x12.png" alt="[feed]" /></a><br />'."\n";
require_once 'XNC/Comment.php';
require_once 'removeEvils.php';
$newComment = new XNC_Comment($file);
// Handle any form submission of adding a new comment
if ($newComment->willProcessForm()) {
   $newComment->processForm();
   $cnt = XN_Content::load($newComment->id);
   $cnt->my->set('parentid',$file->id);
   $cnt->isPrivate = false;
   $cnt->save();
} elseif ($newComment->lastError() != XNC_Comment::ERROR_FORM_ABSENT)
   print $newComment->lastError();
// Display a list of comments belonging to a parent object
if ($file->my->content($newComment->referenceAttribute,true)) {
echo '<ul class="xoxo comments">';
   foreach ($file->my->content($newComment->referenceAttribute,true) as $comment) {
      $data = new XNC_Comment($comment);
      ?>
<li id="<?php echo 'c'.$data->id; ?>">
   Posted on <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/view.php?id=<?php echo $file->id; ?>#<?php echo 'c'.$data->id; ?>" title="<?php echo strtotime($data->createdDate); ?>"><?php echo date('Y-m-d H:i',strtotime($data->createdDate)); ?></a>
   by <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/?user=<?php echo $data->contributorName ?>" class="author user"><?php echo $data->contributorName ?></a>
<dl>
   <dt>body</dt>
      <dd class="content"><?php echo removeEvilTags(nl2br($data->description)); ?></dd>
</dl>
</li>
      <?php
   }//end foreach
echo '</ul>';
}//end if
// Display the add a comment form
if(XN_Profile::current()->isLoggedIn()) {
?>
<form id="commentForm" method="post" action="?id=<?php echo $file->id; ?>">
<input type="hidden" name="xnc_comment" value="xnc_comment" /><input type="hidden" name="Comment:_parent_id" value="<?php echo $file->id; ?>" />Comment: <br />
<textarea name="Comment:description" rows="5" cols="50"></textarea><br />
<input type="submit" name="submit" value="Save Comment" class="button"/><br />
</form>
<a href="http://cocomment.com/"><img src="http://cocomment.com/images/cocomment-integrated.gif" alt="coComment Integrated" /></a>
<script type="text/javascript">
  var blogTool              = "Ning App";
  var blogURL               = "http://<?php echo $_SERVER['HTTP_HOST'];?>/";
  var blogTitle             = "<?php echo addslashes(XN_Application::load()->name); ?>";
  var postURL               = "http://<?php echo $_SERVER['HTTP_HOST'];?>/view.php?id=<?php echo $file->id; ?>";
  var postTitle             = "<?php echo addslashes($file->title); ?>";
  var commentTextFieldName  = "Comment:description";
  var commentButtonName     = "submit";
  var commentAuthorLoggedIn = true;
  var commentAuthor         = "<?php echo XN_Profile::current()->screenName; ?>";
  var commentFormID         = "commentForm";
  var cocomment_force       = false;
</script>
<script type="text/javascript" src="http://www.cocomment.com/js/cocomment.js"></script>
<?php } ?>
</div>