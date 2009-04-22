<?php

$setup = XN_Query::create('Content')
         ->filter('owner','=')
         ->filter('type','eic','Setup');
$setup = $setup->execute();
if(count($setup)) {
   $setup = $setup[0];
} else {
   header('Content-Type: text/plain;');
   header('Location: http://'.$_SERVER['HTTP_HOST'].'/setup.php',true,303);
   exit;
}//end if-else setup

$_SERVER['HTTP_X_NING_REQUEST_URI'] = explode('?',$_SERVER['HTTP_X_NING_REQUEST_URI']);
$_SERVER['HTTP_X_NING_REQUEST_URI'] = $_SERVER['HTTP_X_NING_REQUEST_URI'][0];
$pathvars = explode('/',$_SERVER['HTTP_X_NING_REQUEST_URI']);
unset($pathvars[0]);unset($pathvars[1]);
$pathvars = array_values($pathvars);

if(!count($pathvars) || !$pathvars[0]) {
   $query = XN_Query::create('Content_Count')
          ->filter('owner')
          ->rollup('contributor');
   $users = array_keys($query->execute());
   header('Content-type: application/xml;');
   header('X-Moz-Is-Feed: 1');
   echo '<?xml version="1.0" ?>'."\n";
   echo '<rdf:RDF  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
   echo '   <channel/> <!-- may be removed, just forces firefox to render as feed -->'."\n";
   foreach($users as $user) {
      echo '   <item>'."\n";
      echo '      <dc:identifier>'.htmlspecialchars($user).'</dc:identifier>'."\n";
      echo '      <link>'.htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].'/user/'.urlencode($user)).'</link>'."\n";
      echo '      <title>'.htmlspecialchars($user).'</title>'."\n";
      echo '      <dc:created>2007-04-19T16:47:11+00:00</dc:created>'."\n";
      echo '      <mime>inode/directory</mime>'."\n";
      echo '      <permissions>444</permissions>'."\n";
      echo '   </item>'."\n";
   }//end foreach inode
   echo '</rdf:RDF>';
   exit;
}//end if ! count pathvars

if(!is_numeric($pathvars[0])) {
   $query = XN_Query::create('Content')
          ->filter('owner')
          ->filter('type','eic',$setup->my->single)
          ->filter('contributorName','eic',$pathvars[0]);
   $items = $query->execute();
   header('Content-type: application/xml;');
   header('X-Moz-Is-Feed: 1');
   echo '<?xml version="1.0" ?>'."\n";
   echo '<rdf:RDF  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
   echo '   <channel/> <!-- may be removed, just forces firefox to render as feed -->'."\n";
   foreach($items as $item) {
      echo '   <item>'."\n";
      echo '      <dc:identifier>'.$item->id.'</dc:identifier>'."\n";
      echo '      <link>'.htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].'/id/'.$item->id).'</link>'."\n";
      echo '      <title>'.htmlspecialchars($item->title).'</title>'."\n";
      echo '      <dc:created>'.$item->createdDate.'</dc:created>'."\n";
      echo '      <dc:modified>'.$item->updatedDate.'</dc:modified>'."\n";
      echo '      <mime>'.htmlspecialchars($setup->my->mime).'</mime>'."\n";
      echo '      <size>'.strlen(base64_decode($item->my->data)).'</size>'."\n";
      echo '      <permissions>644</permissions>'."\n";
      echo '   </item>'."\n";
   }//end foreach inode
   echo '</rdf:RDF>';
   exit;
}//end if is_numeric

if(is_numeric($pathvars[0])) {
   header('Content-Type: text/plain;');
   header('Location: http://'.$_SERVER['HTTP_HOST'].'/id/'.$pathvars[0],true,303);
   exit;
}//end if is_numeric

?>