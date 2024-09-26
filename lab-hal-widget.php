<?php
class lab_hal_widget extends WP_widget{
    /**
     * Défini les propriétés du widget
     */
    function __construct(){
        $options = array(
            "classname" => "wp-lab-hal-widget",
            "description" => __("Affiche les dernières publications HAL d'un groupe", 'lab')
        );

        parent::__construct(
            'lab-hal-publications',
            __("Lab - Publications HAL", 'lab'),
            $options
        );
    }

    /**
     * Crée le widget
     * @param $args
     * @param $instance
     */
    function widget($args, $instance){
        extract($args);
        /**
         * @var $before_widget : defined by extract
         * @var $before_title  : defined by extract
         * @var $after_title   : defined by extract
         * @var $after_widget  : defined by extract
         */

        $content = '<ul class="lab-hal-widget">';
        if (isset($_GET['group'])) {
            $articles = lab_hal_getLastArticles($instance['nbdoc'],$_GET['group']);
        } else {
            $articles = lab_hal_getLastArticles($instance['nbdoc'],$instance['groupe']);
        }
        if (count($articles)==0) {
            $content .= '<li>'.__('Aucun article trouvé').'</li>' ;
        } else {
            foreach ($articles as $article) {
                $content .= '<li><a href="'.$article->url.'" target="'.$article->docid.'">'.$article->title.'</a></li>';
            }
        }
        $content .= '</ul>';
        echo $before_widget;
        echo $before_title . $instance['titre'] . $after_title;
        echo $content;
        echo $after_widget;
    }

    /**
     * Sauvegarde des données
     * @param $new
     * @param $old
     */
    function update($new, $old){
        return $new;
    }

    /**
     * Formulaire du widget
     * @param $instance
     */
    function form($instance){

        $defaut = array(
            'titre' => __("Publications Hal", 'wp-hal'),
            'nbdoc' => 5,
            'groupe' => 0
        );
        $instance = wp_parse_args($instance,$defaut);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id("titre");?>"><?php echo __('Titre','lab') .' :'?></label>
            <input value="<?php echo $instance['titre'];?>" name="<?php echo $this->get_field_name("titre");?>" id="<?php echo $this->get_field_id("titre");?>" class="widefat" type="text"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("nbdoc");?>"><?php echo __("Nombre de documents affichés",'lab') .' :'?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id("nbdoc");?>" name="<?php echo $this->get_field_name("nbdoc");?>" type="number" step="1" min="1" max="10" value="<?php echo $instance['nbdoc'];?>" size="3">
        </p>
        <p>Actual group : <?php echo $instance['groupe']?></p>
        <p>
            <label for="<?php echo $this->get_field_id("groupe");?>"><?php echo __("Groupe",'lab') .' :'?></label>
            <?php lab_html_select($this->get_field_id("groupe"), $this->get_field_name('groupe'), "", "lab_admin_group_select_group", "acronym", array("value"=>0,"label"=>"None")); ?>
        </p>
    <?php
    }
}
