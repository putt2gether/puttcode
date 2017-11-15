<?php
class Group{
	public $db,$data = array();
	function __construct(){
		global $database;
		$this->db = $database;
		$this->table = 'golf_group';
		
	}
		function createGroup($data){
		$fdata = array();

		$creation_date=date("Y-m-d H:i:s");
		if($data['group_name']!='' && $data['user_id'] > 0){
			
			$group_name = $data['group_name'] ;
			$profile_img = (isset($data['profile_img']) && $data['profile_img'] != '' )?$data['profile_img']:'' ;
			$member_list=(isset($data['member_list']) && is_array($data['member_list']) && count($data['member_list']) > 0)?$data['member_list']:array();
			$member_list[]['member_id'] = $data['user_id'];
			$sqlQuery1='SELECT group_id FROM '._GROUP_LIST_.' WHERE group_name="'.$this->db->escape($group_name).'" AND create_by="'.$data['user_id'].'"'; 
			$isExist =  $this->db->FetchSingleValue($sqlQuery1);
			$profile_img = str_replace("data:image/jpeg;base64,", "", $profile_img);
			//if(isset($isExist) && $isExist <= 0){	
				if(count($member_list)>1){
			if($profile_img!="")
			{
				$base64_string=$profile_img;
				$profile_img=time()."_".md5(time().$isExist).".jpg";
				ob_clean();
				$img_str = base64_decode($base64_string);
				
				$im = true;
				if ($im !== false) {
					file_put_contents(UPLOADS_GROUP_PATH.$profile_img, $img_str);
					resize_image(UPLOADS_GROUP_PATH.$profile_img,UPLOADS_GROUP_PATH."thumb/".$profile_img."",320,320);
					
				}
				else {
					//echo 'correupted image';die;
				}
			}else{
					$profile_img= '';
				}
				$sqlQuery='insert into '._GROUP_LIST_.' set group_name="'.$this->db->escape($data['group_name']).'", profile_img = "'.$profile_img.'", create_by="'.$data['user_id'].'",is_active="1",create_date="'.$creation_date.'"'; 
				
				$addgroup =  $this->db->FetchQuery($sqlQuery);
				$grpId = $this->db->LastInsertId();
				
				if($grpId>0){
					if(count($member_list)>0){
						foreach($member_list as $m=>$l){
							 $sqlQuery1="insert into "._GROUP_MEMBER_LIST_." (group_id,user_id,is_active,admin_id,create_date) values ('".$grpId."','".$l['member_id']."','1','".$data['user_id']."','".$creation_date."')"; 
							$queryResult = $this->db->FetchQuery($sqlQuery1) ;
						}
					}
				   $fdata['status'] = '1';
					$fdata['message']="Group Created";		
				}else{
					$fdata['status'] = '0';
					$fdata['message']="Group Creation Error";		
				}
				}	
				else{
					$fdata['status'] = '0';
					$fdata['message']="Please add atleast 1 member in this group";
				}
			/*}
			else{
				$fdata['status'] = '0';
				$fdata['message']="Group Already Exist";
			} */
		}else{
			$fdata['status'] = '0';
			$fdata['message']="Required field not found";
		}
		
		return $fdata ;
	}

	function addGroupMember($data){
  $fdata =array();
  $creation_date=date("Y-m-d H:i:s");
  $group_id=$data['group_id'];
  $admin_id=$data['user_id'];
  $group_member_list=(isset($data['group_member_list']) && is_array($data['group_member_list']) && count($data['group_member_list']) > 0)?$data['group_member_list']:array();
  if($group_id!=''){
	   $sqlQuery11="SELECT group_id FROM golf_group WHERE create_by='".$admin_id."' AND group_id='".$group_id."'";
      $isadmin = $this->db->FetchSingleValue($sqlQuery11) ;
	  if($isadmin  > 0){
   if(count($group_member_list) > 0){
    foreach($group_member_list as $k=>$v){
     $usrId = $v["member_id"];
     
     $sqlQuery1="SELECT grp_member_id FROM "._GROUP_MEMBER_LIST_." WHERE user_id='".$usrId."' AND group_id='".$group_id."'";
      $isExist = $this->db->FetchSingleValue($sqlQuery1) ;
     if(isset($isExist) && $isExist > 0)
     {
       unset($isExist);
    continue; 
       
     }else{
       $sqlQuery="insert into "._GROUP_MEMBER_LIST_." (group_id,user_id,is_active,admin_id,create_date) values ('".$group_id."','".$usrId."','1','".$admin_id."','".$creation_date."')"; 
       $queryResult = $this->db->FetchQuery($sqlQuery) ;
       unset($queryResult);
      }      
    }
   $fdata['status'] = '1';
       $fdata['message']="Group Member Added Successfully"; 
 
   }
   else{
    $fdata['status'] = '0';
    $fdata['message']="Friend Not Selected";  

   }
  }else{
   $fdata['status'] = '0';
    $fdata['message']="You don't have permission to update this group";  
   
  }
	}else{
		  $fdata['status'] = '0';
  $fdata['message']="Required field not found";  
	}
  return $fdata ;
 }
	
		function getGroupListing($data){
		$fdata =array();
		
		$admin_id=$data['user_id'];
		$is_admin=(isset($data['flag']) && $data['flag'] == 1)?$data['flag']:0;
		$event_id=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:0;
		
		if($admin_id > 0){
			$event_groups_arr = array();
			if($event_id>0) {
				$sql = "SELECT group_concat(distinct group_id) as group_id FROM event_player_list where event_id='{$event_id}'";
				$grp_items = $this->db->FetchSingleValue($sql);
				if(trim($grp_items)!='') {
					$event_groups_arr = explode(',',$grp_items);
				}
			}
			
			if($is_admin == 1){
				$con =" AND g.create_by= ".$admin_id."";
			}else{
				$con =" AND m.user_id= ".$admin_id."";
			}
			 $sqlQuery="SELECT g.group_id,g.group_name,g.profile_img FROM "._GROUP_LIST_." g LEFT JOIN golf_group_members m ON m.group_id=g.group_id WHERE g.is_active =1 ".$con." group by g.group_id order by g.group_id desc";
			$queryResult = $this->db->FetchQuery($sqlQuery)	;
			if(count($queryResult)>0){
				foreach($queryResult as $i=>$g){
					$g['is_admin'] = '1';
					$g['profile_img'] =($g['profile_img']!="" && file_exists(UPLOADS_GROUP_PATH."thumb/".$g['profile_img']))?(DISPLAY_GROUP_PATH."thumb/".$g['profile_img']):DISPLAY_GROUP_PATH."thumb/noimage.png";
					$query = "SELECT user_id FROM "._GROUP_MEMBER_LIST_." WHERE is_active =1 AND group_id = ".$g['group_id']."";
					$queryResult = $this->db->FetchQuery($query);
					if(count($queryResult)>0){
						foreach($queryResult as $i=>$u){
							$g['group_member_id'][]=array('user_id'=>$u['user_id']);
						}
					}else{
						$g['group_member_id']=array();
					}
					$g['added'] = 0;
					if($g['group_id'] > 0 && in_array($g['group_id'],$event_groups_arr)) {
						$g['added'] = 1;
					}
					
					$g['totl_group_member'] = count($g['group_member_id']);
					
					$data1[]=$g;

				}
				$fdata['status'] = '1';
				$fdata['data'] = $data1;
				$fdata['message']="Group Listing";	
			}else{
				$fdata['status'] = '0';
				$fdata['message']="Empty Group List";	
			}
		}else{
			$fdata['status'] = '0';
			$fdata['message']="Required field not found";		
		}
		return $fdata ;
	
}
	function getGroupDetails($data){
		$fdata =array();
		
		$group_id=$data['group_id'];
		$created_by=$data['user_id'];
		
		if($group_id > 0){
		$sqlQuery="SELECT g.group_name,g.create_date,g.profile_img,g.create_by FROM golf_group g WHERE g.is_active =1 AND g.group_id = ".$group_id.""; 
			$groupd = $this->db->FetchRow($sqlQuery)	;
		
		
		$sqlQuery1="SELECT u.user_id,u.display_name,p.photo_url,p.self_handicap,g.admin_id FROM "._GROUP_MEMBER_LIST_." g INNER JOIN ".TABLE_GOLF_USERS." u ON u.user_id = g.user_id INNER JOIN user_profile p ON p.user_id = u.user_id WHERE g.is_active =1 AND g.group_id = ".$group_id." order by g.user_id"; 
			$queryResult1 = $this->db->FetchQuery($sqlQuery1)	;
			if(count($queryResult1)>0){
				foreach($queryResult1 as $i=>$g){
					$g['member_id'] =  $g['user_id'];
					$g['photo_url']=($g['photo_url']!="" && file_exists(UPLOADS_PROFILE_PATH."thumb/".$g['photo_url']))?(DISPLAY_PROFILE_PATH."thumb/".$g['photo_url']):DISPLAY_PROFILE_PATH."noimage.png";
					$g['is_admin'] = ($g['admin_id']== $g['member_id'])?'1':'0';
					
					unset($g['user_id']);
unset($g['admin_id']);
					$data1[]=$g;
				}
				$user_name = getUserNameById($groupd['create_by']);
				$profile_img =($groupd['profile_img']!="" && file_exists(UPLOADS_GROUP_PATH."thumb/".$groupd['profile_img']))?(DISPLAY_GROUP_PATH."thumb/".$groupd['profile_img']):DISPLAY_GROUP_PATH."thumb/noimage.png";
				$created_date = date('d/m/Y',strtotime($groupd['create_date']));
				$fdata['status'] = '1';
				$fdata['group_name'] = $groupd['group_name'];
				$fdata['create_data'] = 'Created By '.$user_name.', '.$created_date;
				$fdata['profile_image'] = $profile_img;
$fdata['is_group_admin'] = ($created_by == $groupd['create_by'])?'1':'0';
				$fdata['data'] = $data1;
				$fdata['message']="Group Details";	
			}else{
				$fdata['status'] = '0';
				$fdata['data'] = '';
				$fdata['message']="No friends are listed in this group";
			}				
			
		}else{
			$fdata['status'] = '0';
			$fdata['Group Member'] = '';
			$fdata['message']="Required field not found";		
		}
		return $fdata ;
	}	
	
	function editGroup($data){
		$fdata = array();
		$creation_date=date("Y-m-d H:i:s");
		if($data['group_id'] > 0 && $data['group_name']!='' && $data['user_id'] > 0){
			
			$group_name = $data['group_name'] ;
			$group_id = $data['group_id'] ;
			$profile_img = (isset($data['profile_img']) && $data['profile_img'] != '' )?$data['profile_img']:'' ;
			$deleted_user_ids = (isset($data['deleted_user_ids']) && is_array($data['deleted_user_ids']) && count($data['deleted_user_ids'])>0)?$data['deleted_user_ids']:array();

			$sqlQuery2='SELECT group_id FROM '._GROUP_LIST_.' WHERE group_id ="'.$data['group_id'].'" AND create_by = '.$data['user_id'].''; 
			$isgroupExist =  $this->db->FetchSingleValue($sqlQuery2);
			//if(isset($isgroupExist) && $isgroupExist >0){
			$sqlQuery1='SELECT group_id FROM '._GROUP_LIST_.' WHERE group_name="'.$this->db->escape($group_name).'" AND group_id !="'.$data['group_id'].'"'; 
			$isExist =  $this->db->FetchSingleValue($sqlQuery1);
		
			//if(isset($isExist) && $isExist <= 0){	
				$profile_img_str='';
				if($profile_img!=""){
					$base64_string=$profile_img;
					$profile_img=time()."_".md5(time().$data['user_id']).".jpg";
				
				
					//$base64_string=$profile_img;
					ob_clean();
					$img_str = base64_decode($base64_string);
					
					$im = true;
					if ($im !== false) {
						file_put_contents(UPLOADS_GROUP_PATH.$profile_img, $img_str);
						resize_image(UPLOADS_GROUP_PATH.$profile_img,UPLOADS_GROUP_PATH."thumb/".$profile_img."",320,320);
						$profile_img_str = ',profile_img = "'.$profile_img.'"';
					}
					else {
						//echo 'correupted image';die;
					}
				}else{
					$profile_img= '';
				}
				$sqlQuery='Update '._GROUP_LIST_.' set group_name="'.$this->db->escape($data['group_name']).'" '.$profile_img_str.' ,is_active="1" where group_id = '.$group_id.' '; 
				
				$this->db->FetchQuery($sqlQuery);
				
				if(count($deleted_user_ids)>0) {
					$sqlQuery='Delete from '._GROUP_MEMBER_LIST_.' WHERE group_id="'.$group_id.'" AND user_id in ('.implode(",",$deleted_user_ids).')'; 
				
					$this->db->FetchQuery($sqlQuery);
				}
				
				$fdata['status'] = '1';
				$fdata['message']="Group Updated Successfully";
			/* }
			else{
				$fdata['status'] = '0';
				$fdata['message']="Group Name Already Exist";
			} 
		}else{
			$fdata['status'] = '0';
			$fdata['message']="You don't have permission to update this group";
		}*/
		}else{
			$fdata['status'] = '0';
			$fdata['message']="Required field not found";
		}
		
		return $fdata ;
		
		
	}
	
	function exitGroup($data){
		$fdata = array();
		$creation_date=date("Y-m-d H:i:s");
		if($data['group_id'] > 0 && $data['member_id'] > 0){
			
			$group_id = $data['group_id'] ;
			$user_id = $data['member_id'] ;
		$sqlQuery1='Delete from '._GROUP_MEMBER_LIST_.' WHERE group_id="'.$group_id.'" AND user_id="'.$user_id.'"'; 
			$isDelete =  $this->db->FetchQuery($sqlQuery1);
		$fdata['status'] = '1';
			$fdata['message']="Successfully Exit from this group";
		}else{
			$fdata['status'] = '0';
			$fdata['message']="Required field not found";
		}
		
		return $fdata ;
		
		
	}
	
	function deleteGroup($data){
		$fdata = array();
		$creation_date=date("Y-m-d H:i:s");
		if($data['group_id'] > 0){
			
			$group_id = $data['group_id'] ;
			$admin_id = $data['admin_id'] ;
			$query = 'SELECT group_id FROM '._GROUP_LIST_.' WHERE group_id = '.$group_id.' AND create_by ='.$admin_id.'';
			$isDelete =  $this->db->FetchSingleValue($query);
			if(isset($isDelete) && $isDelete >0){
			$sqlQuery1='Delete from '._GROUP_LIST_.' WHERE group_id="'.$group_id.'" '; 
			$this->db->FetchQuery($sqlQuery1);
			$sqlQuery2='Delete from '._GROUP_MEMBER_LIST_.' WHERE group_id="'.$group_id.'" '; 
			$this->db->FetchQuery($sqlQuery2);
			
			$fdata['status'] = '1';
			$fdata['message']="Successfully Delete this group";
			}else{
			$fdata['status'] = '0';
			$fdata['message']="You don't have permission to delete this group";
			}
		
		}else{
			$fdata['status'] = '0';
			$fdata['message']="Required field not found";
		}
		
		return $fdata ;
	}
	
	function getGroupMemberListing($data){
		$fdata =array();
		
		$group_id=$data['group_id'];
		
		if($group_id > 0){
		 	$sqlQuery="SELECT u.user_id,u.display_name FROM "._GROUP_MEMBER_LIST_." g INNER JOIN ".TABLE_GOLF_USERS." u ON u.user_id = g.user_id WHERE g.is_active =1 AND g.group_id = ".$group_id.""; 
			$queryResult = $this->db->FetchQuery($sqlQuery)	;
			if(count($queryResult)>0){
				foreach($queryResult as $i=>$g){
					$data1[]=$g;
				}
				$fdata['status'] = '1';
				$fdata['data'] = $data1;
				$fdata['message']="Group Member Listing";	
			}else{
				$fdata['status'] = '0';
				$fdata['data'] = '';
				$fdata['message']="No friends are listed in this group";
			}				
			
		}else{
			$fdata['status'] = '0';
			$fdata['Group Member'] = '';
			$fdata['message']="Required field not found";		
		}
		return $fdata ;
	}	
	
	function getGroupSuggessionFriendList($data){
		$users =  new users();
		$group_id=(isset($data['group_id']) && $data['group_id'] >0)?$data['group_id']:"0";
		$user_id=(isset($data['user_id']) && $data['user_id'] >0)?$data['user_id']:"0";
		
		if($group_id <= 0){
			$fdata['status'] = '0';
			$fdata['msg'] = 'Required field not found';
		}
		else{
			$my_member = $this->db->FetchQuery("select user_id FROM golf_group_members where group_id='".$group_id."' ");
			
			if(count($my_member)>0){
				foreach($my_member as $i=>$m){
					$member_id[] = $m['user_id'];
				}
			}
			$member_list = implode(',',$member_id);
			$users =  new users();
			$my_data = $this->db->FetchRow("select user_id,country from user_profile where user_id='".$user_id."' ");
			$queryString = "SELECT a.user_id as member_id,a.full_name,p.self_handicap from golf_users a left join user_profile p on p.user_id = a.user_id where a.user_id not in(".$member_list.") and a.is_active!='2'and p.country = '".trim($my_data['country'])."' group by a.user_id order by a.full_name ASC";
			$rsContentDetail = $this->db->FetchQuery($queryString);

			if(count($rsContentDetail)>0){
				foreach($rsContentDetail as $i=>$rowValues){
					$arr = $users->getUserProfileDetail(array("user_id"=>$rowValues["member_id"]));
					$rowValues['profile_image']=($arr['data']['photo_url']!="")? $arr['data']['photo_url']:__BASE_URI__."images/profile/default.jpg";
					$rowValues['thumb_image']=($arr['data']['photo_url']!="")? $arr['data']['photo_url']:__BASE_URI__."images/profile/default.jpg";
					
					$suggestionArr[]=$rowValues; 
				}
				$fdata['status'] = '1';
				$fdata['Suggestion List'] = $suggestionArr;
				$fdata['msg'] = 'Suggestion Listing';
			}else{
				$fdata['status'] = '0';
				$fdata['msg'] = 'Empty Friend List';
			}
		}

		return $fdata ; 
	}
function addSingleMemberToMultipleGroup($data){
		$fdata =array();
		$creation_date=date("Y-m-d H:i:s");
		$member_id=$data['member_id'];
		$admin_id=$data['user_id'];
		$group_id_list=(isset($data['group_list']) && is_array($data['group_list']) && count($data['group_list']) > 0)?$data['group_list']:array();
		if($member_id!=''){
			if(count($group_id_list) > 0){
$sqlQuery1="delete FROM "._GROUP_MEMBER_LIST_." WHERE user_id='".$member_id."' AND admin_id='".$admin_id."'";
					$this->db->FetchQuery($sqlQuery1) ;
				foreach($group_id_list as $k=>$v){

					$sqlQuery1="SELECT grp_member_id FROM "._GROUP_MEMBER_LIST_." WHERE user_id='".$member_id."' AND group_id='".$v['group_id']."'";
					$isExist=0;//$isExist = $this->db->FetchSingleValue($sqlQuery1) ;
					if(isset($isExist) && $isExist > 0)
					{
					unset($isExist);
					continue; 

					}else{
					$sqlQuery="insert into "._GROUP_MEMBER_LIST_." (group_id,user_id,is_active,admin_id,create_date) values ('".$v['group_id']."','".$member_id."','1','".$admin_id."','".$creation_date."')"; 
					$queryResult = $this->db->FetchQuery($sqlQuery) ;
					unset($queryResult);
					}      
				}
				$fdata['status'] = '1';
				$fdata['message']="Group Member Added Successfully"; 
			}else{
				$fdata['status'] = '0';
				$fdata['message']="Group Not Selected";  
			}
		}else{
		  $fdata['status'] = '0';
		$fdata['message']="Required field not found";  
		}
	return $fdata ;
 }

}
?>