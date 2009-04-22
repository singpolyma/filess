<?php

echo '<xn:head><title>'.XN_Application::load()->name.' - Setup</title></xn:head>';

if(!XN_Profile::current()->isOwner())
   die('<b>Only Owner can do this</b>');

$setup = XN_Query::create('Content')
         ->filter('owner','=')
         ->filter('type','eic','Setup');
$setup = $setup->execute();
if(count($setup))
   $setup = $setup[0];
else
   $setup = false;

if(isset($_REQUEST['submit'])) {
   if(!$setup)
      $setup = XN_Content::create('Setup');
   //update header
   $header = file_get_contents('xn_header.view');
   if($setup->my->plural) {
      $header = str_replace('My '.$setup->my->plural,'My '.$_REQUEST['plural'],$header);
      $header = str_replace('Get Multiple '.$setup->my->plural,'Get Multiple '.$_REQUEST['plural'],$header);
   }//end if plural
   $header = str_replace('My Files','My '.$_REQUEST['plural'],$header);
   if($setup->my->single)
      $header = str_replace('Add '.$setup->my->single,'Add '.$_REQUEST['single'],$header);
   $header = str_replace('Add File','Add '.$_REQUEST['single'],$header);
   if($setup->my->single && file_exists('xn_pivot.'.$setup->my->single.'.view'))
      rename('xn_pivot.'.$setup->my->single.'.view','xn_pivot.'.$_REQUEST['single'].'.view');
   if(file_exists('xn_pivot.File.view'))
      rename('xn_pivot.File.view','xn_pivot.'.$_REQUEST['single'].'.view');
   $handle = fopen('xn_header.view', "w");//open file
   if(!ftruncate($handle,0)) {echo "TRUNCATE ERROR";fclose($handle);exit;}//erase file first (display message on error)
   if(!fwrite($handle, $header)) {echo "WRITE ERROR";fclose($handle);exit;}//write to file (display message on error)
   fclose($handle);//close file
   //update data
   $setup->my->set('single',$_REQUEST['single']);
   $setup->my->set('plural',$_REQUEST['plural']);
   $setup->my->set('mime',$_REQUEST['mime']);
   $setup->my->set('fields',serialize(explode(' ',$_REQUEST['fields'])));
   $setup->my->set('homepageblurb',$_REQUEST['homepageblurb']);
   $setup->isPrivate = TRUE;
   $setup->save();
   echo '<p><b>Setup Updated</b></p>';
}//end if isset submit

?>
<h2>Setup</h2>
<p><i>NOTE : Changing the value of 'single' in an app with data will cause data loss.</i></p>
<fieldset>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"><dl>
   <dt>Single noun describing content</dt><dd><input type="text" name="single" value="<?php if($setup) echo $setup->my->h('single'); ?>" /></dd>
   <dt>Plural noun describing content</dt><dd><input type="text" name="plural" value="<?php if($setup) echo $setup->my->h('plural'); ?>" /></dd>
   <dt>MIME-type for content</dt><dd><input type="text" name="mime" value="<?php if($setup) echo $setup->my->h('mime'); ?>" /></dd>
   <dt>Fields on content <span style="font-size:8pt;font-weight:normal;">(space-separated)</span></dt><dd><input type="text" name="fields" value="<?php if($setup) echo xnhtmlentities(implode(' ',unserialize($setup->my->fields))); ?>" /></dd>
   <dt>Homepage Text</dt><dd><textarea name="homepageblurb"><?php if($setup) echo $setup->my->h('homepageblurb'); ?></textarea></dd>
   <dt></dt><dd><input type="submit" name="submit" value="Update" /></dd>
</dl></form>
</fieldset>