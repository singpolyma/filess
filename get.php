<?php

if(stristr($_SERVER['HTTP_REFERER'],'mam'))
   die('Throttled');

define('JSMIN_AS_LIB',true);

if($_REQUEST['id']) {
   $_REQUEST['id'] = explode('?',$_REQUEST['id']);
   if($_REQUEST['id'][1] == 'minify') $_REQUEST['minify'] = true;
   $_REQUEST['id'] = $_REQUEST['id'][0];
}//end if id

$query = XN_Query::create('Content_Count')
       ->filter('owner')
       ->rollup('contributor');
$users = array_keys($query->execute());
if($_REQUEST['id'] && !is_array($_REQUEST['id']) && in_array($_REQUEST['id'],$users)) {
   //see api.php
   header('Content-type: inode/directory');
   header("Content-disposition: attachment; filename=".strtolower($_REQUEST['id']));
   exit;
}//end if is a username

if($_REQUEST['ids']) {
   $_REQUEST['ids'] = explode('?',$_REQUEST['ids']);
   if($_REQUEST['ids'][1] == 'minify') $_REQUEST['minify'] = true;
   $_REQUEST['ids'] = $_REQUEST['ids'][0];
}//end if ids

if(!$_REQUEST['id'] && !$_REQUEST['ids']) {
   header('Content-type: text/html;charset=utf-8');
   ?>
<form method="get" action="/get.php"><div>
   Enter space-separated IDs: <input type="text" name="ids" />
   <input type="submit" value="Go" />
</div></form>
   <?php
   exit;
}//end if ! id

if($_REQUEST['ids']) {
   if($_REQUEST['xn_auth'] != 'no') {//if accessing GET string directly (ie, from form)
      header('Content-type: text/plain;');
      header('Location: http://'.$_SERVER['HTTP_HOST'].'/ids/'.urlencode($_REQUEST['ids']));
      exit;
   }//end if ! isset xn_auth
   $ids = explode(' ',$_REQUEST['ids']);
   $testarray = $ids;
   sort($ids);
   if($ids != $testarray) {
      sort($ids);
      header('Content-type: text/html;charset=utf-8');
      echo '<h2>Please use <a href="/ids/'.implode('+',$ids).'">this link</a>.</h2>';
      exit;
   }//end if ids[0] > ids[1]
   $_REQUEST['id'] = $ids;
}//end if ids

if($_REQUEST['xn_auth'] != 'no') {//if accessing GET string directly (ie, from form)
   header('Content-type: text/plain;');
   header('Location: http://'.$_SERVER['HTTP_HOST'].'/id/'.urlencode($_REQUEST['id']));
   exit;
}//end if ! isset xn_auth

$setup = XN_Query::create('Content')
         ->filter('owner','=')
         ->filter('type','eic','Setup');
$setup = $setup->execute();
$setup = $setup[0];

if(is_array($_REQUEST['id'])) {
   $output = '';
   $testarray = $_REQUEST['id'];
   sort($_REQUEST['id']);
   if($_REQUEST['id'] != $testarray) {
      sort($ids);
      header('Content-type: text/html;charset=utf-8');
      echo '<h2>Please use <a href="/ids/'.implode('+',$_REQUEST['id']).'">this link</a>.</h2>';
      exit;
   }//end if [0] > [1]
   foreach($_REQUEST['id'] as $id) {
      @$file = XN_Content::load(intval($id));
      if(!$file) continue;
      $output .= base64_decode($file->my->data)."\n\n";
   }//end foreach
   if(isset($_REQUEST['minify'])) {
      require_once 'minify.php';
      $mini = new JSMin($output,false);
      $output = $mini->minify();
   }//end if minify
   header('Content-Type: '.$setup->my->mime);
   echo $output;
   if($_REQUEST['callback']) echo "\n".$_REQUEST['callback'].'();';
} else {
   try {$file = XN_Content::load(intval($_REQUEST['id']));} catch(Exception $ex) {$file = false;}
   if(!$file) {header('Content-type: text/plain'); header('HTTP 1.1/404',true,404); exit;}
   header('Content-Type: '.$setup->my->mime);
   $mimesplit = explode('/',$setup->my->mime);
   if($file->my->filename && $mimesplit[0] != 'text' && $setup->my->mime != 'application/xml')
      header("Content-disposition: attachment; filename=" . $file->my->filename);
   $output = base64_decode($file->my->data);
   if(isset($_REQUEST['minify'])) {
      require_once 'minify.php';
      $mini = new JSMin($output,false);
      $output = $mini->minify();
   }//end if minify
   echo $output;
   if($_REQUEST['callback']) echo "\n".$_REQUEST['callback'].'();';
}//end if-else is_array id

?>