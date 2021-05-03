<?php
/*
 * File Name: lab-shortcode-profile.php
 * Description: shortcode pour générer le profil d\'une personne
 * Authors: Astrid BEYER, Ivan Ivanov, Lucas URGENTI, Olivier CHABROL
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
	$HalID_URL = "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$user->hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=xml";
	$HalName_URL = "https://api.archives-ouvertes.fr/search/I2M/?q=authLastNameFirstName_s:%22".$user->hal_name."%22&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=xml";
	$editIcons = '<div id="lab_profile_icons">
					<i title="'.esc_html__("Modifier la couleur d'arrière plan","lab").'" style="display:none" id="lab_profile_colorpicker" class="fas fa-fill-drip lab_profile_edit"></i>
					<i id="lab_profile_edit" title="'.esc_html__('Modifier le profil','lab').'" class="fas fa-user-edit"></i> 
					<i style="display:none" class="fas fa-user-check" title="'.esc_html__('Valider les changements','lab').'" id="lab_confirm_change" user_id="'.$user->id.'"></i>
				</div>';
	$SocialIcons = '<div id="lab_profile_social">
						<a style="display:none" class="lab_profile_social no-link-icon" title="Facebook" href="'.$user->social['facebook'].'" social="facebook" target="_blank"><i class="fab fa-facebook-square"></i></a>
						<a style="display:none" class="lab_profile_social no-link-icon" title="Pinterest" href="'.$user->social['pinterest'].'" social="pinterest" target="_blank"><i class="fab fa-pinterest-square"></i></a>
						<a style="display:none" class="lab_profile_social no-link-icon" title="Instagram" href="'.$user->social['instagram'].'" social="instagram" target="_blank"><i class="fab fa-instagram-square"></i></a>
						<a style="display:none" class="lab_profile_social no-link-icon" title="Twitter" href="'.$user->social['twitter'].'" social="twitter" target="_blank"><i class="fab fa-twitter-square"></i></a>
						<a style="display:none" class="lab_profile_social no-link-icon" title="Linkedin" href="'.$user->social['linkedin'].'" social="linkedin" target="_blank"><i class="fab fa-linkedin"></i></a>
						<a style="display:none" class="lab_profile_social no-link-icon" title="Tumblr" href="'.$user->social['tumblr'].'" social="tumblr" target="_blank"><i class="fab fa-tumblr-square"></i></a>
						<a style="display:none" class="lab_profile_social no-link-icon" title="Youtube" href="'.$user->social['youtube'].'" social="youtube" target="_blank"><i class="fab fa-youtube-square"></i></a>
					</div>';
	$editSocial ='<p><input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_social" placeholder="'.esc_html__("Cliquez sur l'icône d'un réseau social pour le modifier","lab").'"/></p>';
	$halFields = '<p id="lab_profile_halID">
					<span class="lab_current">'.(strlen($user->hal_id) ? '<i>'.esc_html__('Votre ID HAL','lab').' : </i>'.$user->hal_id : '<i>'.esc_html__('Vous n\'avez pas défini votre ID HAL','lab').'</i>&nbsp;<a href="https://doc.archives-ouvertes.fr/identifiant-auteur-idhal-cv/" target="hal"><i class="fa fa-info"></i></a>').'</span>
					<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_halID" placeholder="ID HAL" value="' . $user->hal_id .'"/><a id="lab_profile_testHal_id" target="_blank" style="display:none" class="lab_profile_edit" href="'.$HalID_URL.'">'.esc_html__('Tester sur HAL','lab').'</a>
				  </p>
				  <p id="lab_profile_halName">
					<span class="lab_current">'.(strlen($user->hal_name) ? '<i>'.esc_html__('Votre nom HAL','lab').' : </i>'.$user->hal_name : '<i>'.esc_html__('Vous n\'avez pas défini votre nom HAL','lab').'</i>').'</span>
					<input style="display:none;" type="text" class="lab_profile_edit" id="lab_profile_edit_halName" placeholder="'.esc_html__('Nom HAL','lab').'" value="' . $user->hal_name .'"/><a id="lab_profile_testHal_name" target="_blank" style="display:none" class="lab_profile_edit" href="'.$HalName_URL.'">'.esc_html__('Tester sur HAL','lab').'</a>
				  </p>';
	$metaDatas = "";
	if (isset($user->funding) && !empty($user->funding))
	{
		$metaDatas .='<p id="lab_profile_funding"><span class="lab_current">'.esc_html__('Funding','lab').' : '.$user->funding.'</span></p>';
	}
	if (isset($user->sectionCn) && !empty($user->sectionCn))
	{
		$metaDatas .='<p id="lab_profile_section_cn"><span class="lab_current">'.esc_html__('CN Section','lab').' : '.$user->sectionCn.'</span></p>';
	}
	if (isset($user->sectionCnu) && !empty($user->sectionCnu))
	{
		$metaDatas .='<p id="lab_profile_section_cnu"><span class="lab_current">'.esc_html__('CNU Section','lab').' : '.$user->sectionCnu.'</span></p>';
	}
	if (isset($user->thesisTitle) && !empty($user->thesisTitle))
	{
		$metaDatas .='<p id="lab_profile_thesis_title"><span class="lab_current">'.esc_html__('Thesis','lab').' : '.$user->thesisTitle.'</span></p>';
	}
	if (isset($user->phdSchool) && !empty($user->phdSchool))
	{
		$metaDatas .='<p id="lab_profile_php_school"><span class="lab_current">'.esc_html__('PHD School','lab').' : '.$user->phdSchool.'</span></p>';
	}
	if (isset($user->hdrTitle) && !empty($user->hdrTitle))
	{
		$metaDatas .='<p id="lab_profile_hdr_title"><span class="lab_current">'.esc_html__('HDR','lab').' : '.$user->hdrTitle.'</span></p>';
	}
	if ($user->historics != null && isset($user->historics))
	{
		$lastHisto = $user->historics;
		//var_dump($lastHisto);
		$metaDatas .='<p id="lab_profile_historic"><span class="lab_current">'.esc_html('Mobility','lab')." ".esc_html('Begin','lab').' : '.strftime('%d %B %G',$lastHisto->begin->getTimestamp()).' - '.
		($lastHisto->end == null?esc_html__('present','lab'):esc_html('End','lab').' : '.strftime('%d %B %G',$lastHisto->end->getTimestamp())).' • '.AdminParams::get_param($lastHisto->function);
		if ($lastHisto->host) {
			$metaDatas .= " ".esc_html('Host','lab')." : " .$lastHisto->host;
		}
		if ($lastHisto->mobility) {
			$metaDatas .= "<br>".esc_html('Mobility','lab')." : " .$lastHisto->mobility;
			if ($lastHisto->mobility_status) {
				$metaDatas .= " " .$lastHisto->mobility_status;
			}
		}
		else
		{$metaDatas .= "<br>".esc_html('Mobility','lab')." : " .$lastHisto->mobility_status;

		}
		$metaDatas .='</span></p>';
	}
	  
	  				  
	$profileStr = '
	<div id="lab_profile_card" bg-color="'.$user->bg_color.'">
		<input type="hidden" id="userId" value="'.$user->id.'"/>
		<div id="lab_pic_name">
			<div>';
	$imgId = get_user_meta($user->id, 'lab_user_picture_display', true);
	if ($imgId != NULL && !empty($imgId))
	{
		$imgUrl = wp_get_attachment_image($imgId, array('112', '112'));
		$profileStr .= $imgUrl;
	}
	else
	{
		$profileStr .= '<img src="https://www.gravatar.com/avatar/ab8bfaf41e8f9f4c34cbf0f4c516e414?s=160&amp;d=mp" id="lab_user_picture_display">';
	}

	if($is_current_user || current_user_can('edit_users')) {
		$profileStr .= '<div id="lab_upload_image" class="lab_profile_edit pointer" userId="'.$user->id.'"><a href="#">Edit here</a></div>';//<input type="button" value="Change picture" class="btn btn-info" id="lab_upload_image"/>';
		$profileStr .= '<input type="hidden" name="attachment_id" class="wp_attachment_id" id="attachment_id" value="" /> </br>';
	}
	$profileStr .=  $SocialIcons.
			'</div>
			<div id="lab_profile_info">
				<div id="lab_profile_name"><span id="lab_profile_name_span">'.$user->first_name.' • '.$user->last_name.'</span>'
					.($is_current_user || current_user_can('edit_users') ? $editIcons : ' ').
				'</div>
				<div id="lab_profile_function">'.$user->function.' • '.esc_html__('Affiliation','lab').' : '.$user->affiliation.'</div>
				<div id="lab_profile_function">'.esc_html__('Site','lab').' : '.$user->location.' • '.esc_html__('Office','lab').' : '.$user->office.' • '.esc_html__('Office Floor','lab').' : '.$user->officeFloor.' • </div>
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
					.($is_current_user || current_user_can('edit_users') ? $editSocial.$metaDatas.$halFields : '').'
				</div>
			</div>
		</div>
		<hr/>
		<div id="lab_alert"></div>
		<div id="lab_profile_bio">'
			.($is_current_user || current_user_can('edit_users') ? '<textarea style="display:none;" rows="4" cols="50" class="lab_profile_edit" id="lab_profile_edit_bio" placeholder="Biographie (200 caractères max)">'.$user->description.'</textarea>' : '').
			'<span style="display:block; max-width:700px;" class="lab_current">'.(isset($user->description) ? $user->description :  '...' ).'</span>
		</div>
		<hr/>'.esc_html__("User group(s) : ", "lab").'<ul id="lab_profile_groups">'.$user->print_groups().'</ul>	
		<div id="lab_profile_keywords">
			'.$user->print_keywords().'
		</div>
		<hr/><div id="lab_profile_thematics_div">'.esc_html__("User Thematic(s) : ", "lab").$user->print_thematics().'</div><hr/></div>';
    return $profileStr;
}
/*** CLASS LABUSER ***/
class labUser {
	public $id;
	public $first_name;
	public $last_name;
	public $email;
	public $location;
	public $function;
	public $affiliation;
	public $office;
	public $officeFloor;
	public $phone;
	public $description;
	public $url;
	public $groups;
	public $gravatar;
	public $bg_color;
	public $hal_name;
	public $hal_id;
	public $social;
	public $keywords;
	public $funding;
	public $sectionCn;
	public $sectionCnu;
	public $thesisTitle;
	public $hdrTitle;
	public $phdSchool;

	function __construct($id) {
		$this -> id = $id;
		$this -> first_name  = lab_profile_get_metaKey($id,'first_name');
		$this -> last_name   = lab_profile_get_metaKey($id,'last_name');
		$this -> location    = lab_profile_get_param_metaKey($id,'lab_user_location', AdminParams::PARAMS_SITE_ID);
		$this -> function    = lab_profile_get_param_metaKey($id,'lab_user_function', AdminParams::PARAMS_USER_FUNCTION_ID);
		$this -> affiliation = lab_profile_get_param_metaKey($id,'lab_user_employer', AdminParams::PARAMS_EMPLOYER);
		$this -> funding     = lab_profile_get_param_metaKey($id,'lab_user_funding', AdminParams::PARAMS_FUNDING_ID);
		$this -> sectionCn   = lab_profile_get_param_metaKey($id,'lab_user_section_cn', AdminParams::PARAMS_USER_SECTION_CN);
		$this -> sectionCnu  = lab_profile_get_param_metaKey($id,'lab_user_section_cnu', AdminParams::PARAMS_USER_SECTION_CNU);
		$this -> historics   = lab_admin_load_lastUserHistory($id);
		$this -> thesisTitle = stripslashes(lab_profile_get_metaKey($id,'lab_user_thesis_title'));
		//$this -> thesisTitle = lab_profile_get_metaKey($id,'lab_user_thesis_title');
		$this -> hdrTitle    = stripslashes(lab_profile_get_metaKey($id,'lab_user_hdr_title'));
		$this -> phdSchool   = lab_profile_get_param_metaKey($id,'lab_user_phd_school', AdminParams::PARAMS_USER_ECOLE_DOCTORALE);

		$this -> office      = lab_profile_get_metaKey($id,'lab_user_office_number');
		$this -> officeFloor = lab_profile_get_metaKey($id,'lab_user_office_floor');
		$temp 				 = lab_profile_get_Info($id);
		$this -> keywords 	 = lab_profile_get_keywords($id);
		$this -> email 		 = $temp[0]->user_email;
		$this -> url 		 = $temp[0]->user_url;
		$this -> phone 		 = lab_profile_get_metaKey($id,'lab_user_phone');
		$this -> description = lab_profile_get_metaKey($id,'description');
		$this -> groups 	 = lab_profile_get_Groups($id);
		$this -> thematics 	 = lab_admin_thematic_get_thematics_by_user($id);
		$Gravmail 			 = trim($this->email);
		$Gravmail 			 = strtolower($Gravmail); 
		$this -> gravatar 	 = "https://www.gravatar.com/avatar/".md5($Gravmail)."?s=160&d=mp";
		$this -> bg_color 	 = lab_profile_get_metaKey($id,'lab_profile_bg_color');
		$this -> hal_id 	 = lab_profile_get_metaKey($id,'lab_hal_id');
		$this -> hal_name 	 = lab_profile_get_metaKey($id,'lab_hal_name');
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
	public function print_thematics() {
		$output = "";
		if (count($this->thematics)==0) 
		{ 
			$output .= "<i>".esc_html__("None","lab")."</i>";
		}
		else {
			$output .= '<ul id="lab_profile_thematics">';
			foreach ($this->thematics as $g) {
				$output .= "<li>".$g->name;
				if ($g->main == 1)
				{
					$output .= '<span class="lab_thematic_main"><i class="fa fa-star"></i></span>';
				}
				//$output .= ($is_current_user || current_user_can('edit_users')) ? '&nbsp;<span class="lab_profile_edit delete_thematic" thematic_id="'.$g->id.'"><i thematic_id="'.$g->id.'" class="fa fa-trash"></i></span>':'';

				$output .= "</li>";
			}
			$output .= '</ul>';
		}
		$output .= '<div id="lab_profile_thematic_add_div" class="lab_profile_edit">';
		$output .= lab_html_select_str('lab_fe_thematic','lab_fe_thematic','lab_profile_edit','lab_admin_thematic_load_all',null,array("value"=>0,"label"=>"--- Select thematic ---"),0);
        $output .= '<button class="btn btn-primary lab_profile_edit" id="lab_fe_add_thematic">'.esc_html__("Add","lab").'</button></div>';
		return $output;
	}
	public function print_keywords() {
		$output='';
		if ($this->keywords != null) {
			$output .= "<hr/>";
			$output .= esc_html__("Research keywords : ", "lab");
			$output .= "<ul>";
			foreach($this->keywords as $k) {
				$output .= '<span class="badge badge-pill badge-primary">'.$k->value.'</span> ';
			}
			$output .= "</ul>";
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
function lab_profile_get_param_metaKey($id,$key, $paramType) {
	global $wpdb;
	$sql = "SELECT lp.value AS 'meta_value' FROM `".$wpdb->prefix."usermeta` JOIN `".$wpdb->prefix."lab_params` AS lp ON lp.id=meta_value WHERE `meta_key`='".$key."' AND `user_id`=".$id." AND lp.type_param=".$paramType.";";
	$res = $wpdb->get_var($sql);
	return $res;
}
function lab_profile_get_keywords($id) {
	global $wpdb;
	$sql = "SELECT value FROM `".$wpdb->prefix."lab_hal_keywords` AS kw JOIN `".$wpdb->prefix."lab_hal_keywords_user` AS kwU ON kwU.keyword_id = kw.id WHERE kwU.user_id = ".$id." ORDER BY number DESC LIMIT 7;";
	return $wpdb->get_results($sql);
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