<?php
if (!isset($_SERVER['HTTP_REFERER'])) {die ('<h2>Direct File Access NOT allowed</h2>');}
else{
require_once(dirname(__FILE__).'../../config/db_config.php');
$db=new DatabaseConnectionClass();
if(isset($_POST["action"])){
  switch($_POST["action"]){
    case 'findstate':{
      $country_id=trim($_POST['country_id']);
      if(strtolower($country_id)=="other"){
        echo "no country";
      }
      else{
        $get_States="select state_id,state_name from state where country_id='".$country_id."' order by state_name";
        $state_data=$db->FetchQuery($get_States);
        if(count($state_data)>0){ ?>
          <option value="">select state</option>
          <?php
          foreach($state_data as $state){?>
            <option value="<?php echo $state['state_id']; ?>" <?php if(isset($_POST['state_id'])){ if($_POST['state_id']==$state['state_id']){ ?> selected="selected" <?php }} ?>><?php echo $state['state_name']; ?></option>
          <?php } ?>
          <option value="Other">Other</option>
        <?php }
        else{ ?>
          <option value="">select state</option>
          <option value="Other">Other</option>
        <?php }
      }
    } break;
    case 'findcity':{
      $state_id=trim($_POST['state_id']);
      if(strtolower($state_id)=="other"){
        echo "no state";
      }
      else{
        $get_Cities="select city_id,city_name from city where state_id='".$state_id."' order by city_name";
        $city_data=$db->FetchQuery($get_Cities);
        if(count($city_data)>0){ ?>
          <option value="">select city</option>
          <?php
          foreach($city_data as $city){?>
            <option value="<?php echo $city['city_id']; ?>" <?php if(isset($_POST['city_id'])){ if($_POST['city_id']==$city['city_id']){ ?> selected="selected" <?php }} ?>><?php echo $city['city_name']; ?></option>
          <?php } ?>
          <option value="Other">Other</option>
          <?php }
          else{ ?>
            <option value="">select city</option>
            <option value="Other">Other</option>
          <?php }
      }
    } break;
  }
}
}
?>