<?php
function getSweeties($id) {
   $file = XN_Content::load(intval($id));
   $bag = XN_Query::create('Content')
         ->filter('owner','=',XN_Application::load('sweeties'))
         ->filter('type','eic','SweetieBag')
         ->filter('contributorName','eic',$file->contributorName);
   $bag = $bag->execute();
   $bag = $bag[0];
   $rtrn = array();
   $sweeties = unserialize($bag->my->rec_trans);
   if($sweeties) {
      foreach($sweeties as $sweetie) {
         if($sweetie['recip_obj'] == $id)
            $rtrn[] = $sweetie;
      }//end foreach sweeties
   }//end if sweeties
   return array_reverse($rtrn);
}//end function getSweeties

?>