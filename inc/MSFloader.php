<?php
require_once('MSFleads.php');
class MSFloader{
    
    function __construct()
    {
        add_shortcode( 'consentPopup', array($this,'wpdocs_the_shortcode_func') );
        new MSFleads();
        add_action( 'init' , array($this, 'initialize_scripts' ) );
        // add_action('restrict_manage_posts', array($this, 'add_msf_leads_filter' ));

    }

    // function add_msf_leads_filter() {
    //     global $typenow;
    
    //     // only add filter to msf_leads post type
    //   //  if ($typenow == 'survey-leads') {
    //         $args = array(
    //             'posts_per_page'   => -1,
    //             'post_type'        => 'msf_lead_froms',
    //         );
    //         $the_query = new WP_Query($args);
    
    //         echo '<select name="msf_lead_froms" id="msf_lead_froms">';
    //             echo '<option value="">All forms</option>';
    //             while ($the_query->have_posts()) {
    //                 $the_query->the_post();
    //                 $selected = ($_GET['msf_lead_froms'] == get_the_ID()) ? ' selected="selected"' : '';
    //                 echo '<option value="' . get_the_ID() . '"' . $selected . '>' . get_the_title() . '</option>';
    //             }
    //             wp_reset_postdata();
    //         echo '</select>';
    //    // }
    // }

    function initialize_scripts(){

        add_action('wp_enqueue_scripts',array($this,'front_load_assets'),999 );
        add_action('admin_enqueue_scripts',array($this,'admin_load_assets'),999 );
        add_filter('cron_schedules', array($this,'custom_cron_schedule'));

        $this->createQuestionTable();
        $this->createCategoryTable();
        $this->createApiDataQueueTable();

        if (!wp_next_scheduled('question_bank_cron_queue_event')) {
            wp_schedule_event(time(), 'every_five_minutes', 'question_bank_cron_queue_event');
        }
        

    }

    function custom_cron_schedule($schedules) {
        $schedules['every_five_minutes'] = array(
            'interval' => 300, // Number of seconds, 300 seconds = 5 minutes
            'display'  => __('Every 5 Minutes'),
        );
        return $schedules;
    }

    function wpdocs_the_shortcode_func( $atts ) {
        $attributes = shortcode_atts( array(
            'label' => '',
        ), $atts );
        
        ob_start();
        ?>
        <a href="#" class="target_popup"><?php echo $attributes['label']; ?></a>
        <?php
    
        return ob_get_clean();
    
    }


    function admin_load_assets(){
        wp_register_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', false, '1.0', 'all');
        wp_register_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '1.0', false);

        wp_enqueue_script( 'msf-admin-js', MSF_path.'/assets/js/js_admin.js'  , array('jquery') , false , true );
        wp_enqueue_style( 'msf-admin-css', MSF_path.'/assets/css/css_admin.css'  , NULL , false , 'all' );
        
    }

    function front_load_assets(){
        wp_register_script ( 'msf-front-js', MSF_path.'/assets/js/js_front.js'  , array('jquery') , false , true );
        wp_register_style ( 'msf-front-css', MSF_path.'/assets/css/css_front.css'  , NULL , false ,'all' );
        wp_enqueue_script('msf-front-js');
        wp_enqueue_style('msf-front-css');
    }

    function createQuestionTable(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'questions';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if(!$table_exists) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'questions';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                require tinyint(4) NOT NULL, 
                category_id int(11) NOT NULL,
                question text NOT NULL,
                field_name VARCHAR(255) NOT NULL,
                options longtext NOT NULL,
                question_type varchar(255) NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }

    }

    function createCategoryTable(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'que_categories';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if(!$table_exists) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'que_categories';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                category VARCHAR(255) NOT NULL UNIQUE,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }
        

    }

    function createApiDataQueueTable(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'api_data_queue';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if(!$table_exists) {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                submission_id int(11) NOT NULL,
                hook_form_data TEXT,
                hook_json_data TEXT,
                trusted_form_data TEXT,
                status VARCHAR(50) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }
        

    }
}

new MSFloader();