<?php

if(!$_REQUEST['id'])
   die('<b>No ID Specified</b>');

$content = XN_Content::load($_REQUEST['id']);

if(XN_Profile::current()->screenName != $content->contributorName)
   die('You do not have permission to delete this item');

XN_Content::delete($content);

$setup = XN_Query::create('Content')
         ->filter('owner','=')
         ->filter('type','eic','Setup');
$setup = $setup->execute();
$setup = $setup[0];

echo '<p>'.$setup->my->single.' #'.$_REQUEST['id'].' Deleted</p>';

?>