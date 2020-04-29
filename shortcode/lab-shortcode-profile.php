<?php
/*
 * File Name: lab-shortcode-profile.php
 * Description: shortcode pour générer le profil d\'une personne
 * Authors: Astrid BEYER, Ivan Ivanov, Lucas URGENTI
 * Version: 0.62
*/
function lab_profile($id=0) {
	apply_filters( 'document_title_parts', array("oui") );
	if ($id==0) {
		global $wp;
		$url = $wp->request;
		$user = isset(explode("/",$url)[1]) ? new labUser(lab_profile_getID(explode("/",$url)[1])) : new labUser(get_current_user_id());
	} else {
		$user = new labUser($id);
	}
	$is_current_user = $user->id == get_current_user_id() ? true : false; 
	$HalID_URL = "https://api.archives-ouvertes.fr/search/?authIdHal_s:(".$user->hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
	$HalName_URL = "https://api.archives-ouvertes.fr/search/?q=authLastNameFirstName_s:%22".$user->hal_name."%22&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
	$editIcons = '<div id="lab_profile_icons" class="lab_profile_edit">
					<i title="'.esc_html__("Modifier la couleur d'arrière plan","lab").'" style="display:none" id="lab_profile_colorpicker" class="fas fa-fill-drip lab_profile_edit"></i>
					<i id="lab_profile_edit" title="'.esc_html__('Modifier le profil','lab').'" class="fas fa-user-edit"></i> 
					<i style="display:none" class="fas fa-user-check" title="'.esc_html__('Valider les changements','lab').'" id="lab_confirm_change" user_id="'.$user->id.'"></i>
				</div>';
	$SocialIcons = '<div id="lab_profile_social">
						<a style="display:none" class="lab_profile_social" title="Facebook" href="'.$user->social['facebook'].'" social="facebook" target="_blank"><i class="fab fa-facebook-square"></i></a>
						<a style="display:none" class="lab_profile_social" title="Pinterest" href="'.$user->social['pinterest'].'" social="pinterest" target="_blank"><i class="fab fa-pinterest-square"></i></a>
						<a style="display:none" class="lab_profile_social" title="Instagram" href="'.$user->social['instagram'].'" social="instagram" target="_blank"><i class="fab fa-instagram-square"></i></a>
						<a style="display:none" class="lab_profile_social" title="Twitter" href="'.$user->social['twitter'].'" social="twitter" target="_blank"><i class="fab fa-twitter-square"></i></a>
						<a style="display:none" class="lab_profile_social" title="Linkedin" href="'.$user->social['linkedin'].'" social="linkedin" target="_blank"><i class="fab fa-linkedin"></i></a>
						<a style="display:none" class="lab_profile_social" title="Tumblr" href="'.$user->social['tumblr'].'" social="tumblr" target="_blank"><i class="fab fa-tumblr-square"></i></a>
						<a style="display:none" class="lab_profile_social" title="Youtube" href="'.$user->social['youtube'].'" social="youtube" target="_blank"><i class="fab fa-youtube-square"></i></a>
					</div>';
	$editSocial ='<p><input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_social" placeholder="'.esc_html__("Cliquez sur l'icône d'un réseau social pour le modifier","lab").'"/></p>';
	$halFields = '<p id="lab_profile_halID">
					<span class="lab_current">'.(strlen($user->hal_id) ? '<i>'.esc_html__('Votre ID HAL','lab').' : </i>'.$user->hal_id : '<i>'.esc_html__('Vous n\'avez pas défini votre ID HAL','lab').'</i>').'</span>
					<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_halID" placeholder="ID HAL" value="' . $user->hal_id .'"/><a id="lab_profile_testHal_id" target="_blank" style="display:none" class="lab_profile_edit" href="'.$HalID_URL.'">'.esc_html__('Tester sur HAL','lab').'</a>
				  </p>
				  <p id="lab_profile_halName">
					<span class="lab_current">'.(strlen($user->hal_name) ? '<i>'.esc_html__('Votre nom HAL','lab').' : </i>'.$user->hal_name : '<i>'.esc_html__('Vous n\'avez pas défini votre nom HAL','lab').'</i>').'</span>
					<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_halName" placeholder="'.esc_html__('Nom HAL','lab').'" value="' . $user->hal_name .'"/><a id="lab_profile_testHal_name" target="_blank" style="display:none" class="lab_profile_edit" href="'.$HalName_URL.'">'.esc_html__('Tester sur HAL','lab').'</a>
				  </p>';
	$profileStr = '
    <div id="lab_profile_card" bg-color="'.$user->bg_color.'">
		<div id="lab_pic_name">
			<div>
				<img src="'.$user->gravatar.'" id="lab_avatar"></img>'
				.($is_current_user || current_user_can('edit_users') ? '<p id="lab_avatar_change" class="lab_profile_edit"><a target="_blank" href="https://fr.gravatar.com/">Modifier l\'avatar</a></p>' :'').
				$SocialIcons.
			'</div>
			<div id="lab_profile_info">
				<div id="lab_profile_name"><span id="lab_profile_name_span">'.$user->first_name.' • '.$user->last_name.'</span>'
					.($is_current_user || current_user_can('edit_users') ? $editIcons : ' ').
				'</div>
				<div id="lab_profile_links">
					<p><i class="fas fa-at"></i>'.$user->print_mail().'</p>
					<p id="lab_profile_url">
						<span class="lab_current">'.$user->print_url().'</span>'
						.($is_current_user || current_user_can('edit_users') ? '<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_url" placeholder="Adresse site web" value="' . $user->url .'"/>' : '').
					'</p>
					<p id="lab_profile_phone">
						<span class="lab_current">'.$user->print_phone().'</span>'
						.($is_current_user || current_user_can('edit_users') ? '<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_phone" placeholder="Numéro de téléphone" value="' . $user->phone .'"/>' : '').
					'</p>'
					.($is_current_user || current_user_can('edit_users') ? $editSocial.$halFields : '').'
				</div>
			</div>
			</div>
			<hr/>
			<div id="lab_alert"></div>
			<div id="lab_profile_bio">'
				.($is_current_user || current_user_can('edit_users') ? '<textarea style="display:none;" rows="4" cols="50" class="lab_profile_edit" id="lab_profile_edit_bio" placeholder="Biographie (200 caractères max)">'.$user->description.'</textarea>' : '').
				'<span style="display:block; max-width:700px;" class="lab_current">'.(isset($user->description) ? $user->description :  '...' ).'</span>
			</div>
		<hr/>
		Groupes de l\'utilisateur : 
				<ul id="lab_profile_groups">'
					.$user->print_groups().
				'</ul>
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
	public $bg_color;
	public $hal_name;
	public $hal_id;
	public $social;

	function __construct($id) {
		$this -> id = $id;
		$this -> first_name = lab_profile_get_metaKey($id,'first_name');
		$this -> last_name = lab_profile_get_metaKey($id,'last_name');
		$temp = lab_profile_get_Info($id);
		$this -> email = $temp[0]->user_email;
		$this -> url = $temp[0]->user_url;
		$this -> phone = lab_profile_get_metaKey($id,'lab_user_phone');
		$this -> description = lab_profile_get_metaKey($id,'description');
		$this -> groups = lab_profile_get_Groups($id);
		$Gravmail = trim($this->email);
		$Gravmail = strtolower($Gravmail); 
		$this -> gravatar = "https://www.gravatar.com/avatar/".md5($Gravmail)."?s=160&d=mp";
		$this -> bg_color = lab_profile_get_metaKey($id,'lab_profile_bg_color');
		$this -> hal_id = lab_profile_get_metaKey($id,'lab_hal_id');
		$this -> hal_name = lab_profile_get_metaKey($id,'lab_hal_name');
		foreach (['facebook','instagram','linkedin','pinterest','twitter','tumblr','youtube'] as $reseau) {
			$this->social[$reseau] = lab_profile_get_metaKey($id,'lab_'.$reseau);
		}
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
		/* Display numbers correctly */
		if (strlen($this->phone)!=0) {
			$currentNumber = str_replace(" ", "", $this->phone);
			$currentNumber = str_replace(".", "", $currentNumber);
			$currentNumber = chunk_split($currentNumber, 2, ' ');
			return '<i class="fas fa-phone"></i><a>'.$currentNumber.'</a>';
		}
		return;
	}
	public function print_groups() {
		if (count($this->groups)==0) { return "<i>(Aucun)</i>";}
		$output='';
		foreach ($this->groups as $g) {
			$output .= "<li><a href=\"$g->url\" target=\"_blank\"> $g->acronym • $g->group_name </a></li>";
		}
		return $output;
	}
}
/*** SQL ***/
function lab_profile_getID($slug) {
	global $wpdb;
	$sql = "SELECT `user_id` FROM `".$wpdb->prefix."usermeta` WHERE `meta_key`='lab_user_slug' AND `meta_value`='".$slug."';";
	$res = $wpdb -> get_var($sql);
	return (isset($res)) ? $res : 1;
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
function lab_profile_get_metaKey($id,$key) {
	global $wpdb;
	$sql = "SELECT `meta_value` FROM `".$wpdb->prefix."usermeta` WHERE `meta_key`='".$key."' AND `user_id`=".$id.";";
	$res = $wpdb->get_var($sql);
	return $res;
}

// Fonctions utilisées par une fonction Ajax Externe
function lab_profile_set_MetaKey($id,$key,$val) {
	global $wpdb;
	$wpdb->update(
		$wpdb->prefix.'usermeta',
		array('meta_value' => $val),
		array('meta_key' => $key,'user_id'=>$id)
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