<?php
/*
 * File Name: lab-shortcode-profile.php
 * Description: shortcode pour générer le profil d\'une personne
 * Authors: Astrid BEYER, Ivan Ivanov, Lucas URGENTI
 * Version: 0.2
*/
function lab_profile($param) {
    $param = shortcode_atts(array(
			'user' => isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : get_current_user_id(),
        ),
        $param,
        "lab-profile"
	);
	global $wp;
	$url = $wp->request;
	$param['user']=lab_profile_getID(explode("/",$url)[1]);
	$user = new labUser($param['user']);
	$is_current_user = $user->id == get_current_user_id() ? true : false; 
	$profileStr = '
    <div id="lab_profile_card">
		<div id="lab_pic_name">
			<img src="'.$user->gravatar.'" id="lab_avatar"></img>
			<div id="lab_profile_name">
				<p>'.$user->first_name.' • '.$user->last_name.' '
				. ($is_current_user || current_user_can('edit_users') ? '<i id="lab_profile_edit" class="fas fa-user-edit lab_profile_edit"></i>' : '').
				'	<i style="display:none" class="fas fa-user-check" id="lab_confirm_change" user_id="'.$user->id.'"></i>
					<div id="lab_profile_links">
						<p><i class="fas fa-at"></i>'.$user->print_mail().'</p>
						<p id="lab_profile_url">
							<span class="lab_current">'.$user->print_url().'</span>
							<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_url" placeholder="Adresse site web" value="' . $user->url .'"/>
						</p>
						<p id="lab_profile_phone">
							<span class="lab_current">'.$user->print_phone().'</span>
							<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_phone" placeholder="Numéro de téléphone" value="' . $user->phone .'"/>
						</p> 
					</div>
				<hr>
				<div id="lab_profile_bio">
					<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_bio" placeholder="Biographie" value="'.$user->description.'"/>
					<span class="lab_current">'.(isset($user->description) ? $user->description :  '...' ).'</span>
				</div>
			</div>
		</div>
	</div>';
    return $profileStr;
}

/*** CLASS LABUSER ***/
class labUser {
	public $id;
	public $first_name;
	public $last_name;
	public $email;
	public $phone;
	public $description;
	public $url;
	public $groups;
	public $gravatar;

	function __construct($id) {
		$this -> id = $id;
		$this -> first_name = lab_profile_get_FirstName($id);
		$this -> last_name = lab_profile_get_LastName($id);
		$temp = lab_profile_get_Info($id);
		$this -> email = $temp[0]->user_email;
		$this -> url = $temp[0]->user_url;
		$this -> phone = lab_profile_get_Phone($id);
		$this -> description = lab_profile_get_Description($id);
		$this -> groups = lab_profile_get_Groups($id);
		$Gravmail = trim($this->email);
		$Gravmail = strtolower($Gravmail); 
		$this -> gravatar = "https://www.gravatar.com/avatar/".md5($Gravmail)."?s=160&d=mp";
	}
	public function print_mail() {
		$temp = str_replace('@', ']AT[', $this->email);
		return '<a class="lab_profile_mail" href="mailto:'.$temp.'">'.esc_html(strrev($temp)).'</a>';
	}
	public function print_url() {
		if (strlen($this->url)!=0) {
			return '<i class="fas fa-globe-europe"></i><a target="_blank" href="'.$this->url.'">'.$this->url.'</a>';
		}
		return;
	}
	public function print_phone() {
		if (strlen($this->phone)!=0) {
			return '<i class="fas fa-phone"></i><a>'.$this->phone.'</a>';
		}
		return;
	}
}

/*** SQL ***/
function lab_profile_getID($slug) {
	global $wpdb;
	$sql = "SELECT `user_id` FROM `".$wpdb->prefix."usermeta` WHERE `meta_key`='lab_user_slug' AND `meta_value`='".$slug."';";
    $res = $wpdb -> get_var($sql);
    return (isset($res)) ? $res : 1;
}
function lab_profile_get_Description($id) {
    global $wpdb;
    $sql = "SELECT `meta_value` FROM `".$wpdb->prefix."usermeta` WHERE `meta_key`='description' AND `user_id`=".$id.";";
    $res = $wpdb -> get_var($sql);
    return $res;
}
function lab_profile_get_FirstName($id) {
    global $wpdb;
    $sql = "SELECT `meta_value` FROM `".$wpdb->prefix."usermeta` WHERE `meta_key`='first_name' AND `user_id`=".$id.";";
    $res = $wpdb->get_var($sql);
    return $res;
}
function lab_profile_get_LastName($id) {
    global $wpdb;
    $sql = "SELECT `meta_value` FROM `".$wpdb->prefix."usermeta` WHERE `meta_key`='last_name' AND `user_id`=".$id.";";
    $res = $wpdb->get_var($sql);
    return $res;
}
function lab_profile_get_Info($id) {
    // Permet de récupérer l'url et le mail
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."users` WHERE `ID`=".$id.";";
    return $wpdb->get_results($sql);
}
function lab_profile_get_Groups($id) {
    // Permet de récupérer la liste des groupes auxquel appartient l'utilisateur
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_groups` WHERE `id` IN (SELECT `group_id` FROM `".$wpdb->prefix."lab_users_groups` WHERE `".$wpdb->prefix."lab_users_groups`.`user_id`=".$id.");";
    return $wpdb->get_results($sql);
}
function lab_profile_get_Phone($id) {
	global $wpdb;
	$sql = "SELECT `meta_value` FROM `".$wpdb->prefix."usermeta` WHERE `meta_key`='lab_user_phone' AND `user_id`=".$id.";";
	$res = $wpdb->get_var($sql);
	return $res;
}
function lab_profile_setDesc($id,$desc) {
	global $wpdb;
	$wpdb->update(
        $wpdb->prefix.'usermeta',
		array('meta_value' => $desc),
		array('meta_key' => 'description','user_id'=>$id)
	);
}
function lab_profile_setURL($id,$url) {
	global $wpdb;
	$wpdb->update(
		$wpdb->prefix.'users',
		array('user_url' => $url ),
		array('ID' => $id)
	);
}
	
function lab_profile_setPhone($id,$phone) {
	global $wpdb;
	$wpdb->update(
        $wpdb->prefix.'usermeta',
		array('meta_value' => $phone),
		array('meta_key' => 'lab_user_phone','user_id'=>$id)
	);
}