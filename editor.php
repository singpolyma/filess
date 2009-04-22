<?php

if(!XN_Profile::current()->isLoggedIn())//Check if user is logged in and fail if they are not
   die('<b>Please log in</b>');

if(!$_REQUEST['id'])
   die('<b>No ID Specified</b>');

$file = XN_Content::load(intval($_REQUEST['id']));//load file by id from Content Store
if(XN_Profile::current()->screenName != $file->contributorName)//if this user is not the object owner, die
   die('You do not have permission to edit this item');

if($_REQUEST['_content']) {//save
   $file->my->set('data',base64_encode($_REQUEST['_content']));
   $file->save();
   echo '<h2>Saved</h2>';
}//end if _content

?>
<h2>Edit File</h2>
<fieldset>
<form method="post" action="?id=<?php echo $_REQUEST['id']; ?>" style="width:auto;"><dl>
   <dt style="float:none;clear:both;text-align:left;width:auto;">File Contents</dt>
   <dd style="clear:both;padding:0px;float:none;margin:0px;"><textarea name="_content" cols="100" rows="25"><?php echo xnhtmlentities(base64_decode($file->my->data)); ?></textarea></dd>

   <dd style="display:block;width:auto;text-align:right;"><input type="submit" value="Save" /></dd>
</dl></form>
</fieldset>