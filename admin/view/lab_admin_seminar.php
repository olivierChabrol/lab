<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>
    <div class="wrap">
      <h1 class="wp-heading-inline"><?php esc_html_e('IT budget management','lab'); ?></h1>
      <hr class="wp-header-end">
      <h2 class="nav-tab-wrapper">
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'new' ? 'nav-tab-active' : ''; ?>"   href="<?php echo add_query_arg(array('tab' => 'new'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('New Seminar','lab'); ?></a>
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'list' ? 'nav-tab-active' : ''; ?>"  href="<?php echo add_query_arg(array('tab' => 'list'), remove_query_arg("id", $_SERVER['REQUEST_URI']))  ; ?>"><?php esc_html_e('Seminar list','lab'); ?></a>
      </h2>
