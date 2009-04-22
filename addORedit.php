<?php

if(!XN_Profile::current()->isLoggedIn())//Check if user is logged in and fail if they are not
   die('<b>Please log in</b>');

$setup = XN_Query::create('Content')//Get the setup data (so we know what object types we're working with)
         ->filter('owner','=')
         ->filter('type','eic','Setup');
$setup = $setup->execute();
$setup = $setup[0];

$file = false;//we have no file
if($_REQUEST['id']) {//if editing object id
   $file = XN_Content::load(intval($_REQUEST['id']));//load file by id from Content Store
   if(XN_Profile::current()->screenName != $file->contributorName)//if this user is not the object owner, die
      die('You do not have permission to edit this item');
   $file->focus();//focus sidebar -- probably deprecated
}//end if id

if(isset($_REQUEST['submit'])) {//if processing form
   if($_POST['data']) {//if files was uploaded
      $data = XN_Request::uploadedFileContents($_POST['data']);//get uploaded file
   } else
      $data = false;//no uploaded file == no data (or do not change data)

   if(!$file && !$data)//if no data and no original, die
      die('<b>You must upload a '.$setup->my->single.'!</b>');
   if(!$file)//if no existing file, create new object
      $file = XN_Content::create($setup->my->single);

   $file->title = $_REQUEST['title'];//set title
   $file->description = $_REQUEST['description'];//set description
   if($data)//if editing data
      $file->my->set('data',base64_encode($data));//set data
   if($_POST['data'])//if uploading file
      $file->my->set('filename',$_POST['data']);//set filename
   foreach(unserialize($setup->my->fields) as $field) {//set other fields
      $file->my->set($field,$_REQUEST[$field]);
   }//end foreach fields
   $file->save();//save object to Content Store
   if($_REQUEST['tags']) {//if there are tags
      XN_Tag::checkTags($_REQUEST['tags']);//make sure the tags are valid
      XN_Tag::addTags($file,$_REQUEST['tags']);//add tags to object (object must be saved at least once before this)
   }//end if tags
   $file->focus();//focus sidebar -- probably deprecated

   echo '<p><b>'.$setup->my->single.' Saved</b></p>';//Print 'saved' message
}//end if isset submit

if($file) {//If there's an existing object, we're editing
   echo '<h2>Edit '.$setup->my->single.'</h2>';
   echo '<xn:head><title>'.XN_Application::load()->name.' - Edit '.$setup->my->single.'</title></xn:head>';
} else {//otherwise we're creating/adding
   echo '<h2>Add '.$setup->my->single.'</h2>';
   echo '<xn:head><title>'.XN_Application::load()->name.' - Add '.$setup->my->single.'</title></xn:head>';
}//end if-else file

?>
<!-- File upload/object edit form -->
<fieldset>
<form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"><dl>
   <dt>Title</dt><dd><input type="text" name="title" value="<?php if($file) echo $file->h('title'); ?>" /></dd>
   <dt>Description</dt><dd><textarea name="description"><?php if($file) echo $file->h('description'); ?></textarea></dd>

<?php

//do arbitrary fields
foreach(unserialize($setup->my->fields) as $field) {
   echo '<dt>'.$field.'</dt><dd><input type="text" name="'.$field.'" value="'.($file ? $file->my->$field : '').'" /></dd>';
}//end foreach fields

?>

   <dt><?php echo ($file ? 'Update' : 'Upload'); ?> File</dt><dd><input name="data" type="file" /></dd>

   <dd><a href="editor.php?id=<?php echo $file->id; ?>">Edit Online</a> (text-based files only)</dd>

<?php if(!$file) { ?>
   <dt>Tags</dt><dd><input type="text" name="tags" value="" /></dd>
<?php } ?>
   <dt></dt><dd><?php if($file) echo '<input type="hidden" name="id" value="'.$file->id.'" />'; ?><input type="submit" name="submit" value="Save" /></dd>
</dl></form>
</fieldset>