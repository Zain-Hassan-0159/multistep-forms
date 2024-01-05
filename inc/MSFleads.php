<?php
//error_reporting(E_ALL); ini_set('display_errors', 1);
// We need to load it as it's not automatically loaded by WordPress
if (!class_exists('leads_data_table') ) {
    require_once('leads_data_table.php');
}
if (!class_exists('questions_data_table') ) {
    require_once('questions_data_table.php');
}
if (!class_exists('categories_data_table') ) {
    require_once('categories_data_table.php');
}


class MSFleads
{

    function __construct()
    {
        $this->initialize_leadpage();
    }

    function initialize_leadpage()
    {
        add_action('admin_menu', array($this, 'my_custom_admin_menu'));
        add_shortcode('MSF-lead-form', array($this, 'msf_lead_form_rendring'));
        add_action('wp_ajax_leads_msf',  array($this, 'msf_lead_form_ajax'));
        add_action('wp_ajax_nopriv_leads_msf', array($this, 'msf_lead_form_ajax'));

        add_action('question_bank_cron_queue_event',array($this, 'send_pending_data_to_external_api'));  
    }

    function my_custom_admin_menu()
    {

        add_menu_page(
            'Questions Bank',     // page title
            'Questions Bank',     // menu title
            'manage_options',   // capability
            'question-bank',       // menu slug
            array( $this, 'survay_page_html' ),
            'dashicons-book'
        );

        add_submenu_page(
            'questions-bank', 
            'Survey Leads', 
            'Survey Leads', 
            'manage_options', 
            'survey-leads', 
            array($this, 'survay_page_html')
        );
        add_submenu_page('question-bank', 'Survey Forms', 'Survey Forms', 'manage_options', 'msf-lead-forms', array($this, 'survay_page_html'));
        add_submenu_page('question-bank', 'Add New Form', 'Add New Form', 'manage_options', 'msf-new-form', array($this, 'survay_page_html'));
        add_submenu_page('question-bank', 'Edit Form Actions', 'Edit Form Actions', 'manage_options', 'msf-form-action', array($this, 'survay_page_html'));
        add_submenu_page('question-bank', 'Add Question', 'Add Question', 'manage_options', 'add_question', array($this, 'survay_page_html'));
        add_submenu_page('question-bank', 'Questions', 'Questions', 'manage_options', 'questions', array($this, 'survay_page_html'));
        add_submenu_page('questions-bank', 'Add Category', 'Add Category', 'manage_options', 'category-add', array($this, 'survay_page_html'));
        add_submenu_page('question-bank', 'Categories', 'Categories', 'manage_options', 'categories', array($this, 'survay_page_html'));
        add_submenu_page('question-bank', 'Default Slides', 'Default Slides', 'manage_options', 'msf-default-slides', array($this, 'survay_page_html'));
    }

    function survay_page_html()
    {
        $current_tab = $_GET['page'];
        $page_title = 'Survey Leads';
        $current = ' class = "current"';
        if ($current_tab == 'msf-lead-forms') {
            $page_title = 'Survey forms';
        } else if ($current_tab == 'msf-new-form') {
            $page_title = 'Add new survey form';
        } else if ($current_tab == 'add_question') {
            $page_title = 'Add Question';
        } else if ($current_tab == 'questions') {
            $page_title = 'Questions';
        } else if ($current_tab == 'category-add') {
            $page_title = 'Add Category';
        }else if ($current_tab == 'categories') {
            $page_title = 'All Categories';
        }
        ob_start();
        ?>
        <div class="wrap">
            <h1 class="msf-page-title"><?php echo $page_title; ?></h1>
            <div class="msf-leads-content">
                <div class="msf-leads-tabs">
                    <ul class="msf-tabs-list">
                        <?php if ($current_tab !== 'question-bank') : ?>
                            <li>
                                <a href="/wp-admin/admin.php?page=question-bank">Back</a>
                            </li>
                        <?php endif; ?>
                        <?php if(in_array($current_tab, ['questions', 'categories', 'add_question', 'category-add', 'question-bank'])): ?>
                            <li <?php if($current_tab=='questions' || $current_tab=='add_question'){ echo $current; } 
                                ?>><a href="/wp-admin/admin.php?page=questions">Question Bank</a></li>
                            <li <?php if($current_tab=='categories' || $current_tab=='category-add'){ echo $current; } 
                                ?>><a href="/wp-admin/admin.php?page=categories">Categories</a></li>
                        <?php endif; ?>

                        <?php if(in_array($current_tab, ['msf-lead-forms', 'msf-form-action', 'msf-new-form', 'survey-leads', 'question-bank'])): ?>
                            <li <?php if ($current_tab == 'survey-leads') { echo $current; } ?>>
                                <a href="/wp-admin/admin.php?page=survey-leads">Form Submissions</a>
                            </li>
                            <li <?php if ($current_tab == 'msf-lead-forms' || $current_tab == 'msf-form-action' || $current_tab == 'msf-new-form') { echo $current; } ?>>
                                <a href="/wp-admin/admin.php?page=msf-lead-forms">Survey Forms</a>
                            </li>
                        <?php endif; ?>

                        <?php if ($current_tab == 'question-bank') : ?>
                            <li <?php if ($current_tab == 'question-bank') {echo $current;} ?>>
                                <a href="/wp-admin/admin.php?page=question-bank">Setting</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div class="msf-tabs-content">
                        <?php
                        if ($current_tab == 'survey-leads') {
                            $this->show_all_leads();
                        } else if ($current_tab == 'msf-lead-forms') {
                            $this->show_all_forms();
                        } else if ($current_tab == 'msf-new-form') {
                            $this->create_new_form();
                        } else  if ($current_tab == 'add_question') {
                            $this->add_questions_html();
                        } else  if ($current_tab == 'questions') {
                            $this->questions_html();
                        } else  if ($current_tab == 'category-add') {
                            $this->add_category_html();
                        } else  if ($current_tab == 'categories') {
                            $this->all_category_html();
                        } else  if ($current_tab == 'msf-form-action') {
                            // Enqueue the style and script
                            wp_enqueue_style('select2-css');
                            wp_enqueue_script('select2-js');
                            $this->form_actions();
                        }
                         else {
                            $this->leads_form_settings();
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        echo $html;
    }
    function add_category_html(){
        $this->addUpdate_category_to_Database();
        $id = isset($_GET['id']) && $_GET['id'] !== '' ? $_GET['id'] : '';
        $category = '';
        if($id !== ''){
            global $wpdb;
            $table_name = $wpdb->prefix . 'que_categories';
            $category = $wpdb->get_results("SELECT category FROM $table_name WHERE id='$id'");
            $category = $category[0]->category;
        }
        ?>
        <style>
            .main_input_questions{
                padding: 20px;
                margin-bottom: 30px;
            }

            .row_1{
                display: flex;
                gap: 40px;
            }

            label{
                font-weight: bold;
            }

            .row_1 .input-group{
                display: flex;
                gap: 10px;
                flex-direction: column;
                margin-bottom: 20px;
            }

            .submit_button{
                background: #585f64;
                color: white;
                border: none;
                outline: none;
                font-size: 16px;
                font-weight: bold;
                padding: 10px 20px;
                cursor: pointer;
            }

            form a{
                font-size: 16px;
                color: #b91b1b;
                font-weight: 500;
                margin-left: 20px;
            }
        </style>
        <div class="row">
        </div>
        <div class="main_input_questions">
            <form class="msf-form" action="" method="post">
                <?php wp_nonce_field( 'add_category', 'add_category_nounce' ); ?>
                <div class="row_1">
                    <div class="input-box" style="flex:1;">
                        <div class="input-group">
                            <label for="category">Category</label>
                            <input type="text" name="category" id="category" value = "<?php echo $category; ?>">
                        </div>
                    </div>
                </div>
                
                <?php echo $id !== '' ? '<input type="hidden" name="cat_id" value="'. $id .'">' : ''; ?>
                <input type="hidden" name="form_type" value="<?php echo $id !== '' ? 'update_category' : 'add_category'; ?>">
                <input class="submit_button" type="submit" value="<?php echo $id !== '' ? 'Update Category' : 'Create Category'; ?>">
            </form>
        </div>
        <?php
    }
    function all_category_html(){
        global $wpdb;
        $table = new categories_data_table();
        $query = $wpdb->prefix . 'que_categories';
        $data = $wpdb->get_results(
            "SELECT * from {$query}",
            ARRAY_A
        );
    
        // check if delete action is triggered
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
            $table->handle_delete_action();
        }
    
        $table->columns = array(
            'cb'            => '<input type="checkbox" />',
            'category'          => __('Category', 'multistep-flow'),
            'id'        => __('ID', 'multistep-flow'),
        );
        echo '<div class="nav_actions"><a href="/wp-admin/admin.php?page=category-add">Add Category</a></div>';
        echo '<form method="post">';
        $table->set_table_data($data);
        $table->prepare_items();
        $table->search_box('search', 'search_id');
        $table->display();
        ?>
        </form>
        <?php
    }
    function add_questions_html(){
        $this->add_question_to_Database();
        $this->update_question_to_Database();
        $id = isset($_GET['id']) && $_GET['id'] !== '' ? absint($_GET['id']) : '';
        $cat_id = '';
        $required = 0;
        $question_text = '';
        $field_name = '';
        $question_type = '';
        $options = '';
        if($id !== ''){
            global $wpdb;
            $table_name = $wpdb->prefix . 'questions';
            $questions = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
            if(!empty($questions)){
                $question = $questions[0];
                $id = $question->id;
                $cat_id = $question->category_id;
                $required = $question->require;
                $question_text = $question->question;
                $field_name = $question->field_name;
                $question_type = $question->question_type;
                $options = unserialize($question->options);
            }
        }
        ?>
        <style>
            .main_input_questions{
                padding: 20px;
                background: #f9f9f9;
                margin-bottom: 30px;
            }

            .row_1{
                display: flex;
                gap: 40px;
            }

            label{
                font-weight: bold;
            }

            .row_1 .input-group{
                display: flex;
                gap: 10px;
                flex-direction: column;
            }

            .submit_button{
                background: #585f64;
                color: white;
                border: none;
                outline: none;
                font-size: 16px;
                font-weight: bold;
                padding: 10px 20px;
                cursor: pointer;
            }

            .available_list{
                padding: 20px;
                background: #cbdce347;
                margin-bottom: 30px;
            }

            form a{
                font-size: 16px;
                color: #b91b1b;
                font-weight: 500;
                margin-left: 20px;
            }
        </style>
        <div class="main_input_questions">
            <form class="msf-form" action="" method="post">
                <?php 
                    if($id !== '' && !empty($questions)){
                        wp_nonce_field( 'update_question', 'update_question_nounce' ); 
                    }else{
                        wp_nonce_field( 'add_question', 'add_question_nounce' ); 
                    }
                ?>
                <div class="row_1">
                    <div class="input-box" style="flex:1;">
                        <div class="input-group">
                            <label for="required">Required (*)</label>
                            <input type="checkbox" name="required" id="required" value="1" <?php echo $id !== '' && $required == 1  ? 'checked' : ''; ?>>
                        </div>
                    </div>
                </div>
                <div class="row_1" style="flex-direction: column;">
                    <div class="input-box">
                        <div class="input-group">
                            <label for="question_type">Answer Type</label>
                            <select name="question_type" id="question_type" >
                                <option value="text" <?php echo $id !== '' && $question_type === 'text'  ? 'selected' : ''; ?>  >Text</option>
                                <option value="radio" <?php echo $id !== '' && $question_type === 'radio'  ? 'selected' : ''; ?> >Single Select</option>
                                <option value="checkbox" <?php echo $id !== '' && $question_type === 'checkbox'  ? 'selected' : ''; ?>  >Multi Select </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row_1" style="flex-direction: column;">
                    <div class="input-box">
                        <div class="input-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" >
                                <option value="">Please Select Category</option>
                                <?php
                                $categories = $this->get_all_entries('que_categories');
                                foreach($categories as $category){
                                    ?>
                                    <option value="<?php echo $category->id; ?>" <?php echo $id !== '' && $cat_id ==  $category->id  ? 'selected' : ''; ?> ><?php echo $category->category; ?></option>
                                    <?php
                                }
                                ?>
                                
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row_1" style="flex-direction: column;">
                    <div class="input-box">
                        <div class="input-group">
                            <label for="field_name">Field Name</label>
                            <input type="text" name="field_name" value="<?php echo $id !== '' && $field_name !== '' ? $field_name : ''; ?>" >
                        </div>
                    </div>
                </div>
                <div class="row_1">
                    <div class="input-box" style="flex:1;">
                        <div class="input-group">
                            <label for="question">Question</label>
                            <input type="text" name="question" id="question" value="<?php echo $id !== '' && $question_text !== '' ? $question_text : ''; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="q_choices">
                    <div class="q_choices_lable">Enter Question Choices</div>
                    <div class="q_choice_input">
                        
                        <?php
                        if($id !== '' && !empty($options)){
                            foreach($options as $option){
                            ?>
                            <div class="input-group">
                                <input type="text" name="options[]" class="q_choices_input" value="<?php echo $option; ?>"  >
                                <button type="button" class="remove"><span class="dashicons dashicons-remove"></span></button>
                            </div> 
                            <?php
                            }
                        }else{
                            ?>
                            <div class="input-group" style="flex-direction: column;">
                                <input type="text" name="options[]" class="q_choices_input" value=""  required>
                            </div> 
                            <?php
                        }
                        ?>
                             
                    </div>
                    <button type="button" class="add_q_choice">Add Choice</button>
                </div>
                <input type="hidden" name="form_type" value="<?php echo $id !== '' && !empty($questions) ? 'update_question' : 'add_question'; ?>">
                <input class="submit_button" type="submit" value="<?php echo $id !== '' && !empty($questions) ? 'Update Question' : 'Create Question'; ?>">
            </form>
        </div>
        <?php
    }
    function questions_html(){
        global $wpdb;
        $table = new questions_data_table();
        $query = $wpdb->prefix . 'questions';
        $data = $wpdb->get_results(
            "SELECT * from {$query}",
            ARRAY_A
        );
    
        // check if delete action is triggered
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
            $table->handle_delete_action();
        }
    
        $table->columns = array(
            'cb'            => '<input type="checkbox" />',
            'question'          => __('question', 'multistep-flow'),
            'id'          => __('ID', 'multistep-flow'),
            'field_name'        => __('field_name', 'multistep-flow'),
            'question_type'        => __('question_type', 'multistep-flow'),
        );

        echo '<div class="nav_actions"><a href="/wp-admin/admin.php?page=add_question">Add Question</a></div>';
        echo '<form method="post">';
        $table->set_table_data($data);
        $table->prepare_items();
        $table->search_box('search', 'search_id');
        $table->display();
        ?>
        </form>
        <?php 
        

    }
    function addUpdate_category_to_Database(){
        if ( isset( $_POST['add_category_nounce'] ) && wp_verify_nonce( $_POST['add_category_nounce'], 'add_category' ) ) {
            // add category
            if (isset($_POST['form_type']) && $_POST['form_type'] == 'add_category') {
                if(isset($_POST['category'])){
                    $category = sanitize_text_field($_POST['category']);
                    // Insert data into the database
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'que_categories';
                    $result  = $wpdb->insert(
                        $table_name,
                        array(
                            'category' => $category,
                        ),
                        array('%s')
                    );
                    if ($result !== false) {
                        echo '<p class="success_info">Category Added Successfully</p>';
                    }else{
                        echo '<p class="error_info">Added Failed</p>';
                    }
                }
                return;
            }
            // update the category
            if (isset($_POST['form_type']) && $_POST['form_type'] == 'update_category') {
                if(isset($_POST['category'])){
                    $category = sanitize_text_field($_POST['category']);
                    $id = absint($_POST['cat_id']);
                    // Insert data into the database
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'que_categories';
          
                    $result = $wpdb->update(
                        $table_name,
                        array(
                            'category' => $category,
                        ),
                        array('id' => $id),
                        array('%s'),
                        array('%d')
                    );

                    if ($result !== false) {
                        echo '<p class="success_info">Category Updated Successfully</p>';
                    }else{
                        echo '<p class="error_info">Update Failed</p>';
                    }
                }
                return;
            }
        }
        return;
    }
    function delete_category_to_Database(){
        if ( isset( $_GET['nounce_id'] ) && wp_verify_nonce( $_GET['nounce_id'], 'delete_category' ) ) {
            // nonce verification succeeded, process the form data
            if (isset($_GET['del_id']) && $_GET['del_id'] !== '') {
                // Sanitize input data
                $id = absint($_GET['del_id']);
                
                // Delete data from the database
                global $wpdb;
                $table_name = $wpdb->prefix . 'que_categories';
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM $table_name WHERE id = %d",
                        $id
                    )
                );
            }
            
        }
        return;
    }
    function delete_question_to_Database(){
        if ( isset( $_GET['nounce_id'] ) && wp_verify_nonce( $_GET['nounce_id'], 'delete_question' ) ) {
            // nonce verification succeeded, process the form data
            if (isset($_GET['del_id']) && $_GET['del_id'] !== '') {
                // Sanitize input data
                $id = absint($_GET['del_id']);
                
                // Delete data from the database
                global $wpdb;
                $table_name = $wpdb->prefix . 'questions';
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM $table_name WHERE id = %d",
                        $id
                    )
                );
            }
            
        }
        return;
    }
    function update_question_to_Database(){
        if ( isset( $_POST['update_question_nounce'] ) && wp_verify_nonce( $_POST['update_question_nounce'], 'update_question' ) ) {
            // nonce verification succeeded, process the form data
            if (isset($_POST['form_type']) && $_POST['form_type'] == 'update_question') {
                // Sanitize input data
                $question = sanitize_text_field($_POST['question']);
                $question_type = sanitize_text_field($_POST['question_type']);
                $field_name = sanitize_text_field($_POST['field_name']);
                $category_id = sanitize_text_field($_POST['category_id']);

                $required = isset($_POST['required']) ? absint($_POST['required']) : 0;
                
                $options = serialize($_POST['options']);
                $id = isset($_GET['id']) && $_GET['id'] !== '' ? absint($_GET['id']) : '';
                // Update data in the database
                global $wpdb;
                $table_name = $wpdb->prefix . 'questions';
                $result = $wpdb->update(
                    $table_name,
                    array(
                        'category_id' => $category_id,
                        'require' => $required,
                        'question' => $question,
                        'field_name' => $field_name,
                        'options' => $options,
                        'question_type' => $question_type,
                    ),
                    array('id' => $id),
                    array('%d', '%d', '%s', '%s', '%s', '%s'),
                    array('%d')
                );
                if ($result !== false) {
                    echo '<p class="success_info">Question Updated Successfully</p>';
                }else{
                    echo '<p class="error_info">Updated Failed</p>';
                }
                return;
            }
            
        }
        return;
    }
    function add_question_to_Database(){
        if ( isset( $_POST['add_question_nounce'] ) && wp_verify_nonce( $_POST['add_question_nounce'], 'add_question' ) ) {
            // nonce verification succeeded, process the form data
            if (isset($_POST['form_type']) && $_POST['form_type'] == 'add_question') {
                // print_r($_POST);
                 // Sanitize input data
                 $question = sanitize_text_field($_POST['question']);
                 $question_type = sanitize_text_field($_POST['question_type']);
                 $field_name = sanitize_text_field($_POST['field_name']);
                 $category_id = sanitize_text_field($_POST['category_id']);

                 $required = isset($_POST['required']) ? absint($_POST['required']) : 0;

                 $options = serialize($_POST['options']);

             
                 // Insert data into the database
                 global $wpdb;
                 $table_name = $wpdb->prefix . 'questions';
                 $result = $wpdb->insert(
                     $table_name,
                     array(
                         'category_id' => $category_id,
                         'require' => $required,
                         'question' => $question,
                         'field_name' => $field_name,
                         'options' => $options,
                         'question_type' => $question_type,
                     ),
                     array('%d','%d', '%s', '%s', '%s', '%s')
                 );
                 if ($result !== false) {
                    echo '<p class="success_info">Question Added Successfully</p>';
                }else{
                    echo '<p class="error_info">Added Failed</p>';
                }
                 return;
            }
        }
        return;
    }
    function get_all_entries($table_name){
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $questions = $wpdb->get_results("SELECT * FROM $table_name");
        return $questions;
    }
    function get_specific_entry($table_name, $id){
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $questions = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
        return $questions;
    }
    function show_all_leads()
    {

        if(isset($_GET['preview']) && $_GET['preview'] !== ''){
            $this->preview_lead(absint($_GET['preview']));
            return;
        }
        global $wpdb;
        $table = new leads_data_table();
            
        // check if delete action is triggered
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
            $table->handle_delete_action();
        }


    

        $form_title = '';
        if (isset($_REQUEST['filter_by_form'])) {
            $form_title = sanitize_text_field($_REQUEST['filter_by_form']);
        }
        $query = $wpdb->prefix . 'posts where post_type="msf_leads"';
        if ($form_title !== '') {
            $query .= ' AND post_title LIKE "%' . $form_title . '%"';
        }


        $data = $wpdb->get_results(
            "SELECT * from {$query}",
            ARRAY_A
        );
        $table->columns = array(
            'cb'            => '<input type="checkbox" />',
            'post_title'          => __('Title', 'multistep-flow'),
            'post_content'   => __('Description', 'multistep-flow'),
            'post_status'        => __('Status', 'multistep-flow'),
            'post_date'         => __('Date', 'multistep-flow')
        );
        echo '<form method="post">';
            $table->set_table_data($data);
            $table->prepare_items();
            $table->search_box('search', 'search_id');
            $table->display();
            ?>
        </form>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#filter_by_form').change(function() {
                    $(this).closest('form').submit();
                });
            });
        </script>

        <?php 
    }
    function preview_lead($id){
        $post = get_post($id);
        ?>
        <div class="msf-row" style="margin-top: 10px;">
            <div class="msf-col-6">
                <div class="msf-row">
                    <div class="msf-col-12">
                        <div class="input-group">
                            <label for="">Form</label>
                            <input type="text" name="msf-form-title" placeholder="Form Title" value="<?php  echo $post->post_title; ?>">
                        </div>
                    </div>
                </div>
                <div class="msf-row">
                    <div class="msf-col-12">
                        <div class="input-group">
                            <label for="">Submission Data:</label>
                            <textarea style="width: 100%" name="msf-form-description" placeholder="Form Description" cols="100" rows="10" ><?php  echo $post->post_content; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="msf-row">
                    <div class="msf-col-12">
                        <div class="input-group">
                            <style>
                                .api-log {
                                    border: 1px solid #ccc;
                                    padding: 10px;
                                }

                                .log-entry {
                                    padding: 5px;
                                    margin-bottom: 10px;
                                    border-bottom: 1px solid #ddd;
                                }

                                .log-status {
                                    font-weight: bold;
                                    padding: 2px 5px;
                                    margin-right: 10px;
                                }

                                .log-status.success {
                                    background-color: #cfc;
                                }

                                .log-status.error {
                                    background-color: #fdd;
                                }

                                .log-message {
                                    margin-right: 10px;
                                }

                                .log-timestamp {
                                    color: #888;
                                    font-size: 0.8em;
                                }
                                label{
                                    width: 100%;
                                }

                            </style>
                            <label for="">External APi Logs</label>
                            <?php
                           
                            $existing_status = get_post_meta($post->ID, 'api_request_status', true);
                            $existing_log = get_post_meta($post->ID, 'api_request_log', true);
                            //echo $existing_status;
                            $log_html = '<div class="api-log">';
                            
                            if (!empty($existing_status)) {
                                $log_html .= '<div class="log-status">' . esc_html($existing_status) . '</div>';
                            }
                            
                            if (!empty($existing_log)) {
                                $log_html .= '<div class="log-messages">' . nl2br(esc_html($existing_log)) . '</div>';
                            }
                    
                            $log_html .= '</div>';
                            echo $log_html;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    function show_all_forms()
    {
        global $wpdb;
        $table = new leads_data_table();

        // check if delete action is triggered
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_all') {
            $table->handle_delete_all_action();
        }

        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
            $table->handle_delete_action();
        }

        $query = $wpdb->prefix . 'posts where post_type="msf_lead_froms"';
        $data = $wpdb->get_results(
            "SELECT * from {$query}",
            ARRAY_A
        );
    
        $table->columns = array(
            'cb'            => '<input type="checkbox" />',
            'post_title'          => __('Title', 'multistep-flow'),
            'shortcode'        => __('Shortcode', 'multistep-flow'),
        );
        
        echo '<div class="nav_actions"><a href="/wp-admin/admin.php?page=msf-new-form">Add New Form</a></div>';
        echo '<form method="post">';
        $table->set_table_data($data);
        $table->prepare_items();
        $table->search_box('search', 'search_id');
        $table->display();
        ?>
        </form>
        <?php 
    }  
    function create_new_form()
    {   
        $form_id = '';
        $msf_fs_title = '';
        $msf_fs_description = '';
        $msf_enable = '';
        $msf_ls_slug = '';
        $msf_ls_scripts = '';
        $post = '';
        $all_forms_ls = '';
        $msf_form_questions = '';
        if( isset($_POST['save_question']) || isset($_POST['update_question'])) {
            $this->save_new_forms();
        }
        if (isset($_GET['edit_id']) && $_GET['edit_id'] !== '') {
            $form_id = absint($_GET['edit_id']);
            $msf_fs_title = get_post_meta($form_id, 'msf_fs_title', true);
            $msf_fs_description = get_post_meta($form_id, 'msf_fs_description', true);
            $msf_enable = get_post_meta($form_id, 'msf_enable', true);
            $msf_ls_slug = get_post_meta($form_id, 'msf_ls_slug', true);
            $msf_ls_scripts = get_post_meta($form_id, 'msf_ls_scripts', true);
            $msf_ls_scripts = unserialize($msf_ls_scripts);
            $all_forms_ls = get_post_meta($form_id, 'all_forms_ls', true);
            $msf_form_questions = get_post_meta($form_id, 'msf_form_questions', true);
            $post = get_post($form_id);
            $attachment_id = get_post_thumbnail_id($form_id);
            $attachment_url = wp_get_attachment_url($attachment_id);
        }
        ob_start();
        if($form_id !== '') :
        ?>
        <div class="nav_actions">
            <a href="/wp-admin/admin.php?page=msf-form-action&form_id=<?php echo $form_id; ?>">Setup Hooks</a>
        </div>
        <?php endif; ?>
        <form class="msf-form" action="" method="post" enctype="multipart/form-data" >
            <div class="msf-row">
                <div class="msf-col-6">
                    <h2 class="msf-heading">Add Default Slide</h2>
                    <div class="msf-row">
                        <div class="input-group">
                            <input style="flex: initial;" type="radio" id="enable" name="msf_enable" value="yes" <?php echo $form_id !== '' && $msf_enable === 'yes'  ? 'checked' : ''; ?> >
                            <label for="enable">Enable</label>
                            <input style="flex: initial;" type="radio" id="disable" name="msf_enable" value="no" <?php echo $form_id !== '' && $msf_enable === 'no' ? 'checked' : ''; ?>>
                            <label for="disable">Disable</label>
                        </div>
                    </div>
                    <div class="msf-row">
                        <div class="input-group">
                            <label for="">Slide Title:</label>
                            <input type="text" name="msf_fs_title" value="<?php echo $form_id !== '' ? $msf_fs_title : ''; ?>" placeholder="Title">
                        </div>
                    </div>
                    <div class="msf-row">
                        <div class="input-group">
                            <label for="">Slide Description:</label>
                            <textarea style="width: 100%" name="msf_fs_description" placeholder="Description" cols="50" rows="4"><?php echo $form_id !== '' ? $msf_fs_description : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="msf-row">
                        <div class="input-group">
                            <label for="">Slide Image:</label>
                            <input type="file" name="msf_fs_image" accept="image/*">
                        </div>
                        <div class="input-group">
                            <img src="<?php echo $form_id !== '' ? $attachment_url : ''; ?>" alt="">
                        </div>
                    </div>
                </div>
                <div class="msf-col-6">
                    <h2 class="msf-heading">Thank You Page</h2>
                    <div class="msf-row">
                        <div class="input-group">
                            <label for="">Scritps</label>
                            
                            <textarea style="width: 100%" name="msf_ls_scripts" placeholder="Scripts" cols="50" rows="4"><?php echo $form_id !== '' ? $msf_ls_scripts : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="msf-row">
                        <div class="input-group">
                            <label for="">Slug - Thankyou page</label>
                            <input  type="text" name="msf_ls_slug" value="<?php echo $form_id !== '' ? $msf_ls_slug : ''; ?>" placeholder="Title" required>
                        </div>
                    </div>
                    <?php
                    //var_dump($all_forms_ls);
                    $query = new WP_Query(array(
                        'post_type' => 'msf_lead_froms',
                        'posts_per_page' => -1,
                    ));
                    if($query->have_posts()){
                        ?>
                        <div class="msf-row forms_ls_append">
                            <label for="">Select Upsell Forms</label>
                            <?php
                            if($all_forms_ls !== '' && $form_id !== ''){
                                $key = 0;
                                foreach($all_forms_ls as $form_id_ls){
                                    ?>
                                    <div class="input-group">
                                        <select name="all_forms_ls[]" class="all_forms_ls" id="all_forms_ls">
                                            <option value="" >Please Select the Form</option>
                                            <?php
                                            
                                            while ($query->have_posts()) {
                                                $query->the_post();
                                                $post_id = get_the_ID();
                                                ?>
                                                <option 
                                                value="<?php echo $post_id; ?>" <?php echo $post_id == $form_id_ls  ? 'selected' : ''; ?> ><?php echo get_the_title(); ?></option>
                                                <?php
                                            }
                                            
                                            ?>
                                        </select>
                                        <?php
                                        if($key !== 0){
                                            ?>
                                            <button type="button" class="remove"><span class="dashicons dashicons-remove"></span></button>
                                            <?php
                                        }
                                        $key++;
                                        ?>
                                    </div> 
                                    <?php
                                }
                            }else{
                                ?>
                                <div class="input-group">
                                    <select name="all_forms_ls[]" class="all_forms_ls" id="all_forms_ls">
                                    <option value="" >Please Select the Form</option>
                                        <?php
                                        
                                        while ($query->have_posts()) {
                                            $query->the_post();
                                            $post_id = get_the_ID();
                                            ?>
                                            <option 
                                            value="<?php echo $post_id; ?>" ><?php echo get_the_title(); ?></option>
                                            <?php
                                        }
                                        
                                        ?>
                                    </select>
                                </div> 
                                <?php 
                            }
                            ?>
                        </div>
                        <button type="button" class="add_forms_ls plus_button"><span class="dashicons dashicons-plus"></span></button>
                        <?php
                    }
                    wp_reset_query();
                    ?>
                </div>
            </div>
            <div class="msf-row" style="margin-top: 10px;">
                <div class="msf-col-6">
                    <div class="msf-row">
                        <div class="msf-col-12">
                            <div class="input-group">
                                <label for="">Form Title:</label>
                                <input type="text" name="msf-form-title" placeholder="Form Title" value="<?php echo $form_id !== '' ? $post->post_title : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="msf-row">
                        <div class="msf-col-12">
                            <div class="input-group">
                                <label for="">Form Description:</label>
                                <textarea style="width: 100%"  name="msf-form-description" placeholder="Form Description" cols="50" rows="4"><?php echo $form_id !== '' ? $post->post_content : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="msf-row">
                        <div class="msf-col-12">
                            <div class="input-group">
                                <label for="">Consent Description</label>
                                <textarea style="width: 100%"  name="msf-consent-description" placeholder="Consent Description" cols="50" rows="4"><?php echo $form_id !== '' ? get_post_meta($form_id, 'msf-consent-description', true) : ''; ?></textarea>
                                <span style="color: red;">use this shortcode to populate popup in cosent description [consentPopup label="text"]</span>
                            </div>
                        </div>
                    </div>
                    <div class="msf-row">
                        <div class="msf-col-12">
                            <div class="input-group">
                                <label for="">Consent Popup Data</label>
                                <textarea style="width: 100%"  name="msf-popup-description" placeholder="Consent Popup Data" cols="50" rows="4"><?php echo $form_id !== '' ? get_post_meta($form_id, 'msf-popup-description', true) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="msf-row questions">
                        <label>Select Questions</label>
                            <?php
                            $questions = $this->get_all_entries('questions');
                            //  echo "<pre>";
                            //  print_r($msf_form_questions);
                            ?>
                        <?php
                            if($form_id !== '' && !empty($msf_form_questions)){
                                $key = 0;
                                foreach($msf_form_questions as $ms_question){
                                ?>
                                <div class="input-group">
                                    <select name="msf-form-qs[]" class="msf-form-q" id="all_q">
                                        <?php
                                        if(!empty($questions)){
                                            foreach ($questions as $question) :
                                                ?>
                                                <option 
                                                <?php echo $question->id == $ms_question['question_id'] ? 'selected' : '';  ?> 
                                                data-type="<?php echo $question->question_type; ?>" value="<?php echo $question->id; ?>"><?php echo $question->id . " - " . $question->question; ?></option>
                                            <?php endforeach;
                                        }
                                        ?>
                                    </select>
                                    <span class="essential"><label>
                                        <input type="hidden" class="essential essential_hidden" value="0" name="essential[<?php echo $key; ?>]">
                                        <input type="checkbox" class="essential essential_checked" value="1" name="essential[<?php echo $key; ?>]" <?php echo '1' === $ms_question['essential'] ? 'checked' : '';  ?> >
                                        Essential</label>
                                    </span>
                                    <?php
                                    if($key !== 0){
                                        ?>
                                        <button type="button" class="remove"><span class="dashicons dashicons-remove"></span></button>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                                $key++;
                                }
                            }else{
                                ?>
                                <div class="input-group">
                                    <select name="msf-form-qs[]" class="msf-form-q" id="all_q">
                                        <?php
                                        if(!empty($questions)){
                                            foreach ($questions as $question) :
                                                ?>
                                                <option 
                                                data-type="<?php echo $question->question_type; ?>" value="<?php echo $question->id; ?>"><?php echo $question->question; ?></option>
                                            <?php endforeach;
                                        }
                                        ?>
                                    </select>
                                    <span class="essential"><label>
                                        <input type="hidden" class="essential essential_hidden" value="0" name="essential[0]">
                                        <input type="checkbox" class="essential essential_checked" value="1" name="essential[0]" >
                                        Essential</label>
                                    </span>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                    <div class="msf-row">
                        <div class="input-group">
                            <?php
                            ?>
                            <button type="button" class="add_question plus_button"><span class="dashicons dashicons-plus"></span></button>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top: 20px;" class="msf-row">
                <div class="input-group">
                    <?php
                    ?>
                    <button type="submit" name="<?php echo $form_id === '' ? 'save_question' : 'update_question'; ?>" class="save_question"><?php echo $form_id === '' ? 'Create' : 'Update'; ?></button>
                </div>
            </div>
        </form>

        <?php

        $html = ob_get_clean();
        echo $html;
    }
    function leads_form_settings()
    {
        if (isset($_POST['msf_form_submit'])) {
            $this->save_setting_form();
        }
        $msf_border_style = esc_attr(get_option('msf_border_style'));
        $border_type = array(
            'none' => 'None',
            'solid' => 'Solid',
            'dotted' => 'Dotted',
            'double' => 'Double',
            'dashed' => 'Dashed',
            'groove' => 'Groove',
        );
        $url_hook = unserialize(get_option('msf_url_hook')) ? unserialize(get_option('msf_url_hook')) : '';
        $url_dataType = unserialize(get_option('msf_dataType_hook')) ? unserialize(get_option('msf_dataType_hook')) : '';
        ob_start(); ?>
        <form class="msf-form" method="post" action="">
            <div class="msf-row">
                <div class="msf-col-12">
                    <h2 class="msf-heading">Form Settings</h2>
                    <?php settings_fields('msf-form-group'); ?>
                    <?php do_settings_sections('msf-form-group'); ?>
                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row">Background Gradient One</th>
                            <td><input type="color" name="msf_bg_colorOne" value="<?php echo get_option('msf_bg_colorOne') ? esc_attr(get_option('msf_bg_colorOne')) : '#eff8ff'; ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Background Gradient Two</th>
                            <td><input type="color" name="msf_bg_colorTwo" value="<?php echo get_option('msf_bg_colorTwo') ? esc_attr(get_option('msf_bg_colorTwo')) : '#bcd2ff'; ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Border color</th>
                            <td><input type="color" name="msf_border_color" value="<?php echo get_option('msf_border_color') ? esc_attr(get_option('msf_border_color')) : '#006eee6b'; ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Border style</th>
                            <td>
                                <select name="msf_border_style">
                                    <?php foreach ($border_type as $key => $type) :
                                        $selected = '';
                                        if ($msf_border_style == $key) {
                                            $selected = 'selected';
                                        }
                                    ?>
                                        <option value="<?= $key; ?>" <?= $selected ?>> <?= $type; ?></option>
                                    <?php endforeach;
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Border Width</th>
                            <td><input type="number" name="msf_border_width" min="0" value="<?php echo esc_attr(get_option('msf_border_width')); ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Next Button Background</th>
                            <td><input type="color" name="msf_button_bg_next" value="<?php echo get_option('msf_button_bg_next') ? esc_attr(get_option('msf_button_bg_next')) : '#006eee'; ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Next Button Background Hover</th>
                            <td><input type="color" name="msf_button_bg_next_hover" value="<?php echo get_option('msf_button_bg_next_hover') ? esc_attr(get_option('msf_button_bg_next_hover')) : '#006eee'; ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Previous Button Background</th>
                            <td><input type="color" name="msf_button_bg_prev" value="<?php echo get_option('msf_button_bg_prev') ? esc_attr(get_option('msf_button_bg_prev')) : '#006eee'; ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Previous Button Background Hover</th>
                            <td><input type="color" name="msf_button_bg_prev_hover" value="<?php echo get_option('msf_button_bg_prev_hover') ? esc_attr(get_option('msf_button_bg_prev_hover')) : '#006eee'; ?>" /></td>
                        </tr>

                    </table>
                </div>
            </div>
            <?php submit_button('Save Settings', 'primary', 'msf_form_submit'); ?>
        </form>
        <?php
        $html = ob_get_clean();
        echo $html;
    }

    function save_new_forms()
    {

        // First Default Slides Fields
        $msf_fs_title = sanitize_text_field($_POST['msf_fs_title']);
        $msf_fs_description = sanitize_text_field($_POST['msf_fs_description']);
        $msf_enable = sanitize_text_field($_POST['msf_enable']);

        // Thank you page slide fields
        $msf_ls_slug = sanitize_text_field($_POST['msf_ls_slug']);
        $msf_ls_scripts = serialize($_POST['msf_ls_scripts']);



        $title = sanitize_text_field($_POST['msf-form-title']);
        $descriptions = sanitize_text_field($_POST['msf-form-description']);
        $consent = sanitize_text_field($_POST['msf-consent-description']);
        $popup = sanitize_text_field($_POST['msf-popup-description']);


        $msf_form_qs = $_POST['msf-form-qs'];
        $essential = $_POST['essential'];
        $all_forms_ls = $_POST['all_forms_ls'];

        $all_questions = array();
        // echo "<pre>";
        // print_r($msf_form_qs);
        // print_r($essential);
        if (is_array($msf_form_qs) || is_object($msf_form_qs)) {
            // $msf_form_qs is an array or object, proceed with the loop
            $all_questions = array();
            foreach ($msf_form_qs as $k => $msf_q) {
                $all_questions[] = array(
                    'question_id'   =>  $msf_q,
                    'essential'     =>  $essential[$k]
                );
            }
        }
        $post_id = '';
        if(isset($_POST['update_question']) && isset($_GET['edit_id'])){
            $form_id = absint($_GET['edit_id']);
            $updates = array(
                'ID'            => $form_id,
                'post_title'    => $title,
                'post_content'  => $descriptions,
                'post_type'     =>  'msf_lead_froms'
            );
            $post_id = wp_update_post($updates);

            if($post_id){
                update_post_meta($post_id, 'msf-consent-description', $consent);
                update_post_meta($post_id, 'msf-popup-description', $popup);
            }
        }else{
            $arg = array(
                'post_title'    =>  $title,
                'post_content'  =>  $descriptions,
                'post_status'   =>  'publish',
                'post_type'     =>  'msf_lead_froms'
            );
            $post_id = wp_insert_post($arg);
            
            if($post_id){
                update_post_meta($post_id, 'msf-consent-description', $consent);
                update_post_meta($post_id, 'msf-popup-description', $popup);
            }
        }



        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, 'msf_form_questions', $all_questions);
            update_post_meta($post_id, 'msf_fs_title', $msf_fs_title);
            update_post_meta($post_id, 'msf_fs_description', $msf_fs_description);
            update_post_meta($post_id, 'msf_ls_slug', $msf_ls_slug);
            update_post_meta($post_id, 'msf_ls_scripts', $msf_ls_scripts);
            update_post_meta($post_id, 'msf_enable', $msf_enable);
            update_post_meta($post_id, 'all_forms_ls', $all_forms_ls);

            if(isset($_FILES['msf_fs_image']) && !empty($_FILES['msf_fs_image'])){

                // you can add some kind of validation here
                if( empty( $_FILES['msf_fs_image'] ) ) {
                    wp_die( 'No files selected.' );
                }
    
                $upload = wp_handle_upload( 
                    $_FILES['msf_fs_image'], 
                    array( 'test_form' => false ) 
                );
    
                if( empty( $upload[ 'error' ] ) ) {
                  //  wp_die( $upload[ 'error' ] );
                  // it is time to add our uploaded image into WordPress media library
                  $attachment_id = wp_insert_attachment(
                      array(
                          'guid'           => $upload[ 'url' ],
                          'post_mime_type' => $upload[ 'type' ],
                          'post_title'     => basename( $upload[ 'file' ] ),
                          'post_content'   => '',
                          'post_status'    => 'inherit',
                      ),
                      $upload[ 'file' ]
                  );
      
                  if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
                      wp_die( 'Upload error.' );
                  }
      
                  set_post_thumbnail($post_id, $attachment_id);
                }
    
    
            }
            if(isset($_GET['edit_id'])){
                echo '<p class="success_info">Form Updated Successfully</p>';
            }else{
                echo '<p class="success_info">Form created Successfully</p>';
            }
        } else {
            echo '<p class="error_info">' . $post_id->get_error_message() . '</p>';

            // wp_send_json_error($post_id->get_error_message());
        }
    }
    function save_setting_form()
    {
        if (isset($_POST['msf_bg_colorOne']) && !empty($_POST['msf_bg_colorOne'])) {
            update_option('msf_bg_colorOne', sanitize_text_field($_POST['msf_bg_colorOne']));
        }
        if (isset($_POST['msf_bg_colorTwo']) && !empty($_POST['msf_bg_colorTwo'])) {
            update_option('msf_bg_colorTwo', sanitize_text_field($_POST['msf_bg_colorTwo']));
        }
        if (isset($_POST['msf_border_color']) && !empty($_POST['msf_border_color'])) {
            update_option('msf_border_color', sanitize_text_field($_POST['msf_border_color']));
        }
        if (isset($_POST['msf_border_style']) && !empty($_POST['msf_border_style'])) {
            update_option('msf_border_style', sanitize_text_field($_POST['msf_border_style']));
        }
        if (isset($_POST['msf_border_width']) && !empty($_POST['msf_border_width'])) {
            update_option('msf_border_width', sanitize_text_field($_POST['msf_border_width']));
        }
        if (isset($_POST['msf_button_bg_next']) && !empty($_POST['msf_button_bg_next'])) {
            update_option('msf_button_bg_next', sanitize_text_field($_POST['msf_button_bg_next']));
        }
        if (isset($_POST['msf_button_bg_next_hover']) && !empty($_POST['msf_button_bg_next_hover'])) {
            update_option('msf_button_bg_next_hover', sanitize_text_field($_POST['msf_button_bg_next_hover']));
        }
        if (isset($_POST['msf_button_bg_prev']) && !empty($_POST['msf_button_bg_prev'])) {
            update_option('msf_button_bg_prev', sanitize_text_field($_POST['msf_button_bg_prev']));
        }
        if (isset($_POST['msf_button_bg_prev_hover']) && !empty($_POST['msf_button_bg_prev_hover'])) {
            update_option('msf_button_bg_prev_hover', sanitize_text_field($_POST['msf_button_bg_prev_hover']));
        }
        if (isset($_POST['msf_url_hook']) && !empty($_POST['msf_url_hook'])) {
            update_option('msf_url_hook', serialize($_POST['msf_url_hook']));
        }
        if (isset($_POST['msf_dataType_hook']) && !empty($_POST['msf_dataType_hook'])) {
            update_option('msf_dataType_hook', serialize($_POST['msf_dataType_hook']));
        }
    }
    function msf_lead_form_rendring($atts)
    {
        return $this->msf_lead_form_HTML_rendring($atts);
    }
    function msf_lead_form_HTML_rendring($atts)
    {
        
        ob_start();
        $form_id = absint($atts['id']);
        $msf_fs_title = get_post_meta($form_id, 'msf_fs_title', true);
        $msf_fs_description = get_post_meta($form_id, 'msf_fs_description', true);
        $msf_enable = get_post_meta($form_id, 'msf_enable', true);
        $msf_ls_slug = get_post_meta($form_id, 'msf_ls_slug', true);
        $msf_ls_scripts = get_post_meta($form_id, 'msf_ls_scripts', true);
        $msf_ls_scripts = unserialize($msf_ls_scripts);
        $all_forms_ls = get_post_meta($form_id, 'all_forms_ls', true);
        $msf_form_questions = get_post_meta($form_id, 'msf_form_questions', true);
        $post = get_post($form_id);
        $attachment_id = get_post_thumbnail_id($form_id);
        $attachment_url = wp_get_attachment_url($attachment_id);
        $class = $msf_enable !== 'yes' ? 'current' : '';
        ?>
        <style>
            .line{
                position: relative;
            }
            .line:after {
                content: "";
                width: 0;
                height: 4px;
                background-color: #f3e000;
                transition: width 1s ease;
                animation-name: grow;
                animation-duration: 10s;
                animation-fill-mode: forwards;
                animation-iteration-count: infinite;
                position: absolute;
                left: 0;
                bottom: 0;
                border-radius: 8px;
            }

            @keyframes grow {
                from {
                    width: 0;
                }
                to {
                    width: 100%;
                }
            }

            .main-wrapper {
                background-image: -webkit-gradient(linear, left top, left bottom, from(<?php echo get_option('msf_bg_colorOne') ? esc_attr(get_option('msf_bg_colorTwo')) : '#eff8ff'; ?>), to(<?php echo get_option('msf_bg_colorTwo') ? esc_attr(get_option('msf_bg_colorOne')) : '#eff8ff'; ?>));
                background-image: linear-gradient(<?php echo get_option('msf_bg_colorOne') ? esc_attr(get_option('msf_bg_colorOne')) : '#eff8ff'; ?> 0%, <?php echo get_option('msf_bg_colorTwo') ? esc_attr(get_option('msf_bg_colorTwo')) : '#eff8ff'; ?> 100%);
                background-repeat: no-repeat;
                background-size: auto;
                background-position: 50% 50%;
                background-attachment: scroll;
                min-height: 100vh;
                width: 100%;
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-pack: center;
                    -ms-flex-pack: center;
                        justify-content: center;
                -webkit-box-align: center;
                    -ms-flex-align: center;
                        align-items: center;
                }
                .main-wrapper * {
                margin: 0;
                padding: 0;
                }

                .main-form {
                padding: 10px;
                background-color: white;
                border-radius: 5px;
                max-width: 768px;
                width: 100%;
                }
                .main-form .first {
                text-align: center;
                }
                .main-form .slide {
                background-color: white;
                border: <?php echo get_option('msf_border_width') ? esc_attr(get_option('msf_border_width')) : '#006eee6b'; ?>px <?php echo get_option('msf_border_style') ? esc_attr(get_option('msf_border_style')) : 'solid'; ?> <?php echo get_option('msf_border_color') ? esc_attr(get_option('msf_border_color')) : '#006eee6b'; ?>;
                border-radius: 5px;
                padding: 20px;
                margin-bottom: 10px;
                display: none;
                }
                .main-form .slide .info {
                margin-top: 10px;
                font-size: 12px;
                line-height: 24px;
                text-align: center;
                padding: 5px 0 10px 0;
                color: rgba(10, 10, 10, 0.85);
                }
                .main-form .slide .bottom_line {
                border-bottom: 1px solid #757575;
                }
                .main-form .slide .form-img {
                padding-top: 40px;
                padding-bottom: 20px;
                }
                .main-form .slide .form-img img {
                max-width: 125px;
                border-radius: 50%;
                }
                .main-form .slide .text {
                padding: 10px 0px;
                }
                .main-form .slide .paragraph p {
                padding: 10px;
                font-size: 19px;
                line-height: 24px;
                text-align: center;
                }
                .main-form .slide .btn {
                border: none;
                border-radius: 0 0 5px 5px;
                padding: 15px 0px;
                margin-top: 20px;
                width: 100%;
                background-color: #006eee;
                color: white;
                font-weight: bold;
                cursor: pointer;
                }
                .main-form .slide .btn:hover {
                background-color: '#006eee';
                }
                .main-form .slide .next_btn {
                    position: relative;
                background-color: <?php echo get_option('msf_button_bg_next') ? esc_attr(get_option('msf_button_bg_next')) : '#006eee'; ?>;
                }
                .main-form .slide .next_btn:hover {
                background-color: <?php echo get_option('msf_button_bg_next_hover') ? esc_attr(get_option('msf_button_bg_next_hover')) : '#006eee'; ?>;
                }
                .main-form .slide .prev-btn {
                background-color: <?php echo get_option('msf_button_bg_prev') ? esc_attr(get_option('msf_button_bg_prev')) : '#006eee'; ?>;
                }
                .main-form .slide .prev-btn:hover {
                background-color: <?php echo get_option('msf_button_bg_prev_hover') ? esc_attr(get_option('msf_button_bg_prev_hover')) : '#006eee'; ?>;
                }
                .main-form .slide .question {
                margin-bottom: 15px;
                padding-bottom: 12px;
                }
                .main-form .slide .question h2 {
                font-size: 22px;
                font-weight: 400;
                }
                .main-form .slide .question h2 .essential {
                color: #f56c6c;
                margin-left: 3px;
                }
                .main-form .slide .options label {
                border: <?php echo get_option('msf_border_width') ? esc_attr(get_option('msf_border_width')) : '#006eee6b'; ?>px solid <?php echo get_option('msf_border_color') ? esc_attr(get_option('msf_border_color')) : '#006eee6b'; ?>;
                border-radius: 5px;
                padding: 10px 15px;
                font-size: 18px;
                margin-bottom: 10px;
                cursor: pointer;
                display: block;
                }
                .main-form .slide .options label input {
                vertical-align: middle;
                display: inline-block;
                position: relative;
                background: #eff7fb;
                top: -2px;
                height: 16px;
                width: 16px;
                border-radius: 50px !important;
                cursor: pointer;
                outline: none;
                border: 0.8px solid #499FFF !important;
                padding: 0;
                margin: 0 1px 0 3px;
                }
                .main-form .slide .options P {
                border: <?php echo get_option('msf_border_width') ? esc_attr(get_option('msf_border_width')) : '#006eee6b'; ?>px solid <?php echo get_option('msf_border_color') ? esc_attr(get_option('msf_border_color')) : '#006eee6b'; ?>;
                border-radius: 5px;
                padding: 10px 15px;
                font-size: 18px;
                margin-bottom: 10px;
                cursor: pointer;
                }
                .main-form .slide .options P input {
                background-color: transparent;
                width: 100%;
                font-size: 16px;
                border: 0;
                outline: 0;
                }
                .main-form .slide .options input.active {
                background: #499FFF;
                }
                .main-form .slide .options .col-2 {
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                gap: 10px;
                }
                .main-form .slide .options .col-2 p {
                -webkit-box-flex: 1;
                    -ms-flex: 1;
                        flex: 1;
                }
                .main-form .slide .options .col-2 .hidden {
                visibility: hidden;
                }
                .main-form .slide .actions {
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                }
                .main-form .slide .actions .prev-btn {
                border-bottom-right-radius: 0;
                opacity: 0.8;
                }
                .main-form .slide .actions .next-btn, .main-form .slide .actions .submit {
                border-bottom-left-radius: 0;
                }
                .main-form .current {
                display: inherit !important;
                }

                @media(max-width: 600px) {
                    .main-form .slide .actions{
                        flex-direction: column;
                    }
                }

                @keyframes spin {
                    0% {
                        transform: rotate(0deg);
                    }
                    100% {
                        transform: rotate(360deg);
                    }
                }

                .circle-loader {
                    border: 4px solid white;  /* Loader color */
                    border-radius: 50%;         /* Makes it circular */
                    border-top: 4px solid transparent; /* Top border color */
                    width: 30px;                /* Width of the circle */
                    height: 30px;               /* Height of the circle */
                    animation: spin 1s linear infinite; /* This will make it spin */
                    position: absolute;
                    top: 5px;
                    right: 20px;
                }

        </style>
        <div id="mulit_form" class="main-wrapper">
            <form method="post"  id="mulit_form_msf" class="main-form">
                <?php
                if($msf_enable === 'yes'){
                    ?>
                    <div class="slide first current">
                        <div class="form-img">
                            <?php
                            if($attachment_url !== ''){
                                ?>
                                <img class="rounded-circle" src="<?php echo $attachment_url; ?>">
                                <?php
                            }
                            ?>
                        </div>
                        <div class="text">
                            <h2><?php echo $msf_fs_title; ?></h2>
                        </div>
                        <div class="paragraph">
                            <p><?php echo $msf_fs_description; ?></p>
                            <button onclick=form_flow.next_Slide() type="button" class="btn next-btn next_btn">NEXT</button>
                        </div>
                    </div> 
                    <?php
                }
                if(!empty($msf_form_questions)){
                    $count = 0;
                    foreach($msf_form_questions as $question){
                        $questions = $this->get_specific_entry('questions', $question['question_id']);
                        if(!empty($questions)){
                            $count++;
                            $question = $questions[0];
                            $id = $question->id;
                            $cat_id = $question->category_id;
                            $required = $question->require;
                            $question_text = $question->question;
                            $field_name = $question->field_name;
                            $field_name = str_replace(' ', '_', $field_name);
                            $field_name = $field_name . '_' . $id;
                            $question_type = $question->question_type;
                            $options = unserialize($question->options);
                            ?>
                            <div class="slide <?php echo $count === 1 ? $class : ''; ?>">
                                <div class="question">
                                <h2><?php echo $question_text; ?> <sapn class="essential"><?php echo $required == 1 ? "*" : ''; ?></sapn></h2>
                                </div>
                                <div class="options">
                                    <?php
                                    if($question_type === 'radio' && !empty($options) ){
                                        $counter = 1;
                                        foreach($options as $option){
                                        ?>
                                        <Label for="<?php echo $field_name . '_' . $counter; ?>"  >
                                            <input onChange=form_flow.next_Slide() type="radio" class="" name="<?php echo $field_name; ?>" id="<?php echo $field_name . '_' . $counter; ?>" value="<?php echo $option; ?>" <?php
                                            echo $required == 1 ? "required" : ''; ?> >
                                            <span><?php echo $option; ?></span>
                                        </Label>
                                        <?php
                                        $counter++;
                                        }
                                    }else
                                    if($question_type === 'checkbox' && !empty($options) ){
                                        $counter = 1;
                                        foreach($options as $option){
                                        ?>
                                        <Label for="<?php echo $field_name . '_' . $counter; ?>">
                                            <input type="checkbox" class="" name="<?php echo $field_name; ?>[]" id="<?php echo $field_name . '_' . $counter; ?>" value="<?php echo $option; ?>" <?php
                                            echo $required == 1 ? "required" : ''; ?> >
                                            <span><?php echo $option; ?></span>
                                        </Label>
                                        <?php
                                        $counter++;
                                        }
                                    }else
                                    if($question_type === 'text' && !empty($options) ){
                                        $counter = 1;
                                        foreach($options as $option){
                                        ?>
                                        <p>
                                            <input type="text" placeholder="<?php echo $option; ?>" class="" name="<?php echo $field_name; ?>[]" id="<?php echo $field_name . '_' . $counter; ?>" <?php
                                            echo $required == 1 ? "required" : ''; ?>  >
                                            <span></span>
                                        </p>
                                        <?php
                                        $counter++;
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="actions">
                                <button onclick=form_flow.previous_Slide() type="button" class="prev-btn btn">Previous</button>
                                <button onclick=form_flow.next_Slide() type="button" class="next-btn btn next_btn">Next</button>
                                </div>
                            </div> 
                            <?php
                        }

                    }
                }
                ?>
                <div class="slide">
                    <div class="question bottom_line">
                    <h2>Street Address<sapn class="essential">*</sapn></h2>
                    </div>
                    <div class="options">
                    <div class="col-1">
                        <p>
                        <input type="text" name="address_line_1_msf" placeholder="Address Line 1" required>
                        </p>
                    </div>
                    <div class="col-2">
                        <p>
                        <input type="text" name="city_msf" placeholder="City" required>
                        </p>
                        <p>
                        <input type="text" name="state_msf" placeholder="State" required>
                        </p>
                    </div>
                    <div class="col-2">
                        <p>
                        <input type="text" name="zip_msf" placeholder="Zip" required>
                        </p>
                        <p class="hidden"></p>
                    </div>
                    </div>
                    <div class="actions">
                    <button onclick=form_flow.previous_Slide() type="button" class="prev-btn btn">Previous</button>
                    <button onclick=form_flow.next_Slide() type="button" class="next-btn btn next_btn">Next</button>
                    </div>
                </div> 
                <div class="slide last">
                    <div class="question bottom_line">
                    <h2>Your Info<sapn class="essential">*</sapn></h2>
                    </div>
                    <div class="options">
                    <div class="col-2">
                        <p>
                        <input type="text" name="firstname_msf" placeholder="First Name" required>
                        </p>
                        <p>
                        <input type="text" name="lastname_msf" placeholder="Last Name" required>
                        </p>
                    </div>
                    <div class="col-1">
                        <p>
                        <input type="email" name="email_msf" placeholder="Email Address" required>
                        </p>
                    </div>
                    <div class="col-1">
                        <p>
                        <input type="phone" name="phone_msf" placeholder="(503) 374 1346" pattern="\d{10}|\(\d{3}\) \d{3} \d{4}"  required>
                        </p>
                    </div>

                    </div>
                    <div class="actions">
                    <input type="hidden" name="form_id" value="<?php echo $form_id; ?>" >
                    <button onclick=form_flow.previous_Slide() type="button" class="prev-btn btn">Previous</button>
                    <button type="submit" name="form_data_msf" class="submit btn submit_button_msf next_btn">Get Free Quotes<div class="circle-loader d-none"></div></button>
                    </div>
                    <div class="info">
                        <?php echo get_post_meta($form_id, 'msf-consent-description', true); ?>
                    </div>
                    <div class="overlay"></div>
                    <div class="popup d-none"><?php echo get_post_meta($form_id, 'msf-popup-description', true); ?></div>
                </div> 
            </form>
        </div>
        <script>
            const form_flow = {
                current_Slide: () => {
                    let currentSlide = document.querySelector('#mulit_form .current');
                    return currentSlide;
                },
                
                next_Slide: () => {
                    let currentSlide = form_flow.current_Slide();
                    let essential = jQuery(currentSlide).find('.essential').html();
                
                    if(essential == '*'){
                        let continueItem = [];
                        var checkedItems = {};

                        jQuery(currentSlide).find('input').each(function(){
                            if(jQuery(this).prop('required')){
                                var inputType = jQuery(this).attr('type');
                                var inputName = jQuery(this).attr('name');

                                if(inputType === 'checkbox' || inputType === 'radio') {
                                    if (jQuery(this).prop('checked')) {
                                        checkedItems[inputName] = true;
                                    }
                                } else if(jQuery(this).val() === '') {
                                    continueItem.push('item');
                                    jQuery(this).parent().css('border', '2px solid red');
                                    return;
                                } else {
                                    jQuery(this).parent().css('border', '2px solid #2b2b2b');
                                }
                            }
                        });

                        jQuery(currentSlide).find('input[type="checkbox"][required], input[type="radio"][required]').each(function(){
                            var inputName = jQuery(this).attr('name');

                            if (!checkedItems[inputName]) {
                                continueItem.push('item');
                                jQuery(this).parent().css('border', '2px solid red');
                            } else {
                                jQuery(this).parent().css('border', '2px solid #2b2b2b');
                            }
                        });



                        if(currentSlide.nextElementSibling && continueItem.length == 0){
                            currentSlide.classList.remove('current');
                            currentSlide.nextElementSibling?.classList.add('current');
                        }
                    }else
                    {
                        if(currentSlide.nextElementSibling){
                            currentSlide.classList.remove('current');
                            currentSlide.nextElementSibling?.classList.add('current');
                        } 
                    }
                   // return;
                },

                previous_Slide: () => {
                    let currentSlide = form_flow.current_Slide();
                    if(currentSlide.previousElementSibling){
                        currentSlide.classList.remove('current');
                        currentSlide.previousElementSibling?.classList.add('current');
                    }
                }
            }
            jQuery(document).ready(function(){
                jQuery("#mulit_form_msf").submit(function (event) {
                    event.preventDefault();
                    // Collecting the whole form data
                    jQuery(".circle-loader").removeClass("d-none");
                    form_data = new FormData(this);
                    let form_data_array = Array.from(form_data.entries());
                    let data_entries = {};
                    form_data_array.forEach(element => {
                        data_entries[element[0]] = element[1];
                    });
                    let existingFormData = localStorage.getItem('form_data_msf');
                    if (existingFormData) {
                        localStorage.removeItem('form_data_msf');
                    }
                    localStorage.setItem('form_data_msf', JSON.stringify(data_entries));
                    // console.log(JSON.parse(existingFormData));
                    // return false;
                    form_data.append('action', 'leads_msf');
                    form_data.append('security', '<?php echo wp_create_nonce( 'leads_msf_signature' ); ?>');
                   // console.log(form_data);
                    // Transfering data through AJAX
                    jQuery.ajax({
                        url: '<?php  echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        contentType: false,
                        processData: false,
                        data: form_data,
                        success: function (response) {
                            jQuery(".circle-loader").addClass("d-none");
                            if(response.success){
                                let postID = response.data.id;
                                let formID = response.data.form_id;
                                let slug = response.data.slug;
                                window.location.href = '/'+slug+'?postID=' + postID + '&formID=' + formID;
                                return;
                            }else{
                                console.error(response.data); // You can display this to the user if needed.
                                return;
                            }
                        }
                    });
    
                    return false;
                });
            });
        </script>
        <!-- TrustedForm -->
        <script type="text/javascript">
            (function() {
            var tf = document.createElement('script');
            tf.type = 'text/javascript'; tf.async = true;
            tf.src = ("https:" == document.location.protocol ? 'https' : 'http') + "://api.trustedform.com/trustedform.js?field=xxTrustedFormCertUrl&ping_field=xxTrustedFormPingUrl&l=" + new Date().getTime() + Math.random();
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(tf, s);
            })();
        </script>
        <noscript>
            <img src="https://api.trustedform.com/ns.gif" />
        </noscript>
        <!-- End TrustedForm -->
        <?php
        $html = ob_get_clean();
        return $html;
    }

    // AJAX Call Start
    function get_hook_data_msf($type, $hook_data){
        $hook_form_data = [];
        if(isset($hook_data[$type.'_msf_url_hook']) && !empty($hook_data[$type.'_msf_url_hook'])){
            $url = $hook_data[$type . '_msf_url_hook'][0];
            if($url !== ''){
                foreach($hook_data[$type.'_msf_hook_data_name'] as $key => $name){
                    if($name !== ''){
                        $hook_form_data[$name] = $hook_data[$type.'_msf_hook_data_value'][$key];
                    }
                }
                $hook_form_data['action_url'] = $url;
            }

        }
        return $hook_form_data;
    }

    function orginal_fields($field_name){
        $field_name = preg_replace('/_\d+/', '', $field_name);
        $field_name = str_replace('_', ' ', $field_name);
        return $field_name;
    }


    function process_form_data_for_hooks($post_data, $form_id){
        $content = '';
        $form_data = [];
        $hook_data = unserialize(get_post_meta($form_id, 'form_actions', true));
        $hook_form_data = $this->get_hook_data_msf('form', $hook_data);
        $hook_json_data = $this->get_hook_data_msf('json', $hook_data);
    
        foreach ($post_data as $key => $value) {
            $key = $this->orginal_fields($key);

            $subkey = array_search($key, $hook_form_data);
            if($subkey !== false){
                if (is_array($value)) {
                    foreach ($value as $val) {
                        //$content .= $key . " : " . sanitize_text_field($val) . "\n";
                        $hook_form_data[$subkey] = $value;
                    }
                } else {
                    //$content .= $key . " : " . sanitize_text_field($value) . "\n";
                    $hook_form_data[$subkey] = $value;
                }
            }

            $subkey = array_search($key, $hook_json_data);

            if($subkey !== false){
                if (is_array($value)) {
                    foreach ($value as $val) {
                        //$content .= $key . " : " . sanitize_text_field($val) . "\n";
                        $hook_json_data[$subkey] = $value;
                    }
                } else {
                    //$content .= $key . " : " . sanitize_text_field($value) . "\n";
                    $hook_json_data[$subkey] = $value;
                }
            }


            if (is_array($value)) {
                foreach ($value as $val) {
                    $content .= $key . " : " . sanitize_text_field($val) . "\n";
                    $form_data[$key] = $value;
                }
            } else {
                $content .= $key . " : " . sanitize_text_field($value) . "\n";
                $form_data[$key] = $value;
            }
        }
    
        return [$content, $hook_form_data, $hook_json_data];
    }

    function insert_msf_lead_post($name, $form_title, $content, $trusted_form_data){
        $post_args = array(
            'post_title' => $name . ' (' . $form_title . ')',
            'post_content' => $content,
            'post_type' => 'msf_leads',
            'post_status' => 'publish'
        );
    
        $post_id = wp_insert_post($post_args);
        
        if($post_id){
            foreach ($trusted_form_data as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
        }
    
        return $post_id;
    }

    function send_pending_data_to_external_api() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'api_data_queue';
    
        // Query the table for pending items
        $pending_items = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'pending' LIMIT 5");
    
        foreach ($pending_items as $item) {
            // Decode the JSON strings back into arrays
            $sumission_id = $item->submission_id;
            $hook_form_data = json_decode($item->hook_form_data, true);
            $hook_json_data = json_decode($item->hook_json_data, true);
            $trusted_form_data = json_decode($item->trusted_form_data, true);
    
            // Send data to the external API
            $this->send_data_to_external_api($hook_form_data, 'form', $trusted_form_data, $sumission_id);
            $this->send_data_to_external_api($hook_json_data, 'json', $trusted_form_data, $sumission_id);
    
            // Update the status to 'complete'
            $wpdb->update(
                $table_name,
                array('status' => 'complete'), // New values
                array('id' => $item->id), // Where
                array('%s'), // Value format
                array('%d')  // Where format
            );
        }
    }   
    
    function send_data_to_external_api($hook_data, $data_type, $trusted_form_data, $sumission_id){
        if(empty($hook_data)){
            return;
        }

        foreach ($trusted_form_data as $key => $value) {
            $hook_data[$key] = $value;
        }
    
        $url = $hook_data['action_url'];
        unset($hook_data['action_url']);
        
        $data_to_send = $data_type === 'json' ? json_encode($hook_data) : $hook_data;
    
        $response = wp_remote_post($url, [
            'body' => $data_to_send,
            'timeout' => 15
        ]);


        // Check if the API request was successful
        if (is_wp_error($response)) {
            $status = 'error';
            $log_message = $response->get_error_message();
        } else {
            $status = 'success';
            $log_message = 'API request was successful.';
        }

        // Get existing log information
        $existing_status = get_post_meta($sumission_id, 'api_request_status', true);
        $existing_log = get_post_meta($sumission_id, 'api_request_log', true);

        // Append new log information to existing log
        $updated_status = ($existing_status ? $existing_status . ',' : '') . $status;
        $updated_log = ($existing_log ? $existing_log . "\n" : '') . $log_message;

        // Update post meta with the updated log information
        update_post_meta($sumission_id, 'api_request_status', $updated_status);
        update_post_meta($sumission_id, 'api_request_log', $updated_log);
    
        return $response;
    }

    function msf_lead_form_ajax(){
        check_ajax_referer('leads_msf_signature', 'security');
    
        // Validate form ID
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : null;

        if($form_id === null){
            wp_send_json_error('Invalid request.');
        }
    
        // Remove unnecessary POST data
        unset($_POST['action'], $_POST['security'], $_POST['form_id']);
    
        // Process trusted form data
        $trusted_form_data = [
            'xxTrustedFormToken' => $_POST['xxTrustedFormToken'] ?? '',
            'Trusted_Form_URL' => $_POST['xxTrustedFormCertUrl'] ?? '',
            'xxTrustedFormPingUrl' => $_POST['xxTrustedFormPingUrl'] ?? ''
        ];
    
        // Extract and sanitize essential form fields
        $essential_fields = [
            'address' => sanitize_text_field($_POST['address_line_1_msf']),
            'city' => sanitize_text_field($_POST['city_msf']),
            'state' => sanitize_text_field($_POST['state_msf']),
            'zip' => sanitize_text_field($_POST['zip_msf']),
            'firstName' => sanitize_text_field($_POST['firstname_msf']),
            'lastName' => sanitize_text_field($_POST['lastname_msf']),
            'email' => sanitize_text_field($_POST['email_msf']),
            'phone' => sanitize_text_field($_POST['phone_msf'])
        ];
    
        $name = $essential_fields['firstName'] .' '. $essential_fields['lastName'];
        $post = get_post($form_id);
        $form_title = $post->post_title;
        $msf_ls_slug = get_post_meta($form_id, 'msf_ls_slug', true);
    
        if(!is_email($essential_fields['email'])){
            wp_send_json_error('Invalid email address.');
        }
    
        // Process form data for hooks and content
        list($content, $hook_form_data, $hook_json_data) = $this->process_form_data_for_hooks($_POST, $form_id);
    
        // Insert post and meta data
        $post_id = $this->insert_msf_lead_post($name, $form_title, $content, $trusted_form_data);
    
        if($post_id){
            // Send data to external APIs
            // $this->send_data_to_external_api($hook_form_data, 'form', $trusted_form_data);
            // $this->send_data_to_external_api($hook_json_data, 'json', $trusted_form_data);

            global $wpdb;
            $table_name = $wpdb->prefix . 'api_data_queue';

            $data = array(
                'submission_id' => $post_id,
                'hook_form_data' => json_encode($hook_form_data),
                'hook_json_data' => json_encode($hook_json_data),
                'trusted_form_data' => json_encode($trusted_form_data),
                'status' => 'pending'
            );

            $format = array('%d','%s', '%s', '%s', '%s'); // String format for each value

            $wpdb->insert($table_name, $data, $format);

    
            wp_send_json_success([
                'id' => $post_id,
                'form_id' => $form_id,
                'slug' => $msf_ls_slug
            ]);
        } else {
            wp_send_json_error('Error processing the request.');
        }
    }
    // AJAX Call End
    

    function form_actions(){


        $post_id = '';
        $post_id = isset($_GET['form_id']) ? $_GET['form_id'] : '';
        if (isset($_POST) && !empty($_POST)) {
            update_post_meta($post_id, 'form_actions', serialize($_POST));
        }
        $hook_data = unserialize(get_post_meta($post_id, 'form_actions', true));
        // echo "<pre>";
        // var_dump($hook_data);
        // return;
   
        
        ?>
        <style>
            .select2-container{
                min-width: 100px;
            }
            .inputPair, .addInput{
                margin: 5px;
            }
            .form-table{
                border: none !important;
                background: #f0f0f1;
                padding: 10px;
                margin-bottom: 15px;
            }
            .form-table th{
                padding: 20px;
            }
            .plus_button{
                margin-bottom: 20px;
                padding: 5px 14px;
            }
        </style>
        <div class="nav_actions">
            <a href="/wp-admin/admin.php?page=msf-new-form&edit_id=<?php echo $post_id; ?>">Back to Form</a>
        </div>
        <form action="" method="post">
            <div class="msf-col-6" style="padding: 20px">
                <h2 class="msf-heading">Hooks</h2>
                <div class="form-view add_hooks_url_table">
                    <?php
                    $url = '';
                    $type = 'json';
                    if(!empty($hook_data)){

                        if(isset($hook_data['json_msf_url_hook'])){
                            $url = $hook_data['json_msf_url_hook'][0];
                            $type = 'json';
                            echo $this->get_action_html($url, $type, $hook_data,  $post_id);
                        }
                        if(isset($hook_data['form_msf_url_hook'])){
                            $url = $hook_data['form_msf_url_hook'][0];
                            $type = 'form';
                            echo $this->get_action_html($url, $type, $hook_data,  $post_id);
                        }
                    }else{
                        echo $this->get_action_html($url, $type, [],  $post_id);
                    }
                    ?>
                </div>
                <button type="button" class="add_hooks_url plus_button"><span class="dashicons dashicons-plus"></span></button>
                <button class="button button-primary" type="submit">Save</button>
            </div>
        </form>

        <script>
            window.form_state = "json";
            window.repeater_form_action = `<?php echo $this->repeater_hook_html($post_id, 'json'); ?>`;
            var addButtons = document.querySelectorAll('.addInput');
            addButtons.forEach(function(addButton) {
                addButton.addEventListener('click', function() {
                    var container = addButton.previousElementSibling;
                    var div = document.createElement('div');
                    div.className = 'inputPair';

                    var removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '-';
                    removeBtn.className = 'removeInput';
                    removeBtn.addEventListener('click', function() {
                        container.removeChild(div);
                    });
                    div.innerHTML = repeater_form_action;
                    div.appendChild(removeBtn);

                    container.appendChild(div);
                    jQuery('.hooks_select2').select2({
                        tags: true
                    }).on('change', function(e) {
                        var targetEl = e.target.parentElement.parentElement.parentElement.parentElement.parentElement;
                        window.form_state = targetEl.querySelector('.form_format').value;
                        update_form_type_names(targetEl);
                    })
                });
            });

            function update_form_format(event, value){
                window.form_state = value;
                var targetEl = event.target.parentElement.parentElement.parentElement;
                update_form_type_names(targetEl);
            }
            jQuery( document ).ready(function() {
                jQuery('.hooks_select2').select2({
                    tags: true
                }).on('change', function(e) {
                    // Do something when the value changes
                    var targetEl = e.target.parentElement.parentElement.parentElement.parentElement.parentElement;
                    window.form_state = targetEl.querySelector('.form_format').value;
                    update_form_type_names(targetEl);
                })

                jQuery(document).on('select2:open', function(e) {
                    document.querySelector('.select2-container--open .select2-search__field').focus();
                });

            });


            function delEl(event){
                event.target.parentElement.remove();
            }

            function update_form_type_names(targetEl){
                var parentElement = targetEl;                
                var inputElements = parentElement.getElementsByTagName("input");
                var selectElements = parentElement.getElementsByTagName("select");

                for (var i = 0; i < inputElements.length; i++) {
                    var inputElement = inputElements[i];
                    var dataType = form_state === "json" ? inputElement.getAttribute("data-json") : inputElement.getAttribute("data-form");
                    inputElement.name = dataType ;
                }
                for (var i = 0; i < selectElements.length; i++) {
                    var selectElement = selectElements[i];
                    var dataType = form_state === "json" ? selectElement.getAttribute("data-json") : selectElement.getAttribute("data-form");
                    selectElement.name = dataType ;
                }
            }

            function update_forms_input(e){
                var targetEl = e.target.parentElement.parentElement.parentElement.parentElement.parentElement;
                window.form_state = targetEl.querySelector('.form_format').value;
                update_form_type_names(targetEl);
            }

        </script>


        <?php
    }

    function get_action_html($url, $type, $hook_data, $form_id){
        ob_start();
        $questions = $this->get_all_entries('questions');
        $msf_form_questions = get_post_meta($form_id, 'msf_form_questions', true);

        ?>
        <table class="form-table input-group" >
            <tr valign="top">
                <th scope="row">URL</th>
                <td><input type="text" data-json="json_msf_url_hook[]" data-form="form_msf_url_hook[]" name="<?php echo $type; ?>_msf_url_hook[]" value="<?php echo $url; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Data Type</th>
                <td>
                <select class="form_format" onchange="update_form_format(event, value)" data-json="json_msf_dataType_hook[]" data-form="form_msf_dataType_hook[]" name="<?php echo $type; ?>_msf_dataType_hook[]">
                    <option value="json" <?php echo $type === 'json' ? "selected" : ""; ?> >JSON</option>
                    <option value="form" <?php echo $type === 'form' ? "selected" : ""; ?> >FORM</option>
                </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Hook Data</th>
                <td>
                    <!-- Repeater 1 -->
                    <div class="inputFieldContainer">
                        <?php
                        // echo '<pre>';
                        // var_dump($hook_data);
                        // die();
                        $constant_questions = [
                            'firstname msf' => 'First Name',
                            'lastname msf' => 'Last Name',
                            'email msf' => 'Email',
                            'phone msf' => 'Phone',
                            'address line msf' => 'Address',
                            'state msf' => 'City',
                            'zip msf' => 'Zip',
                        ];
                        if(!empty($hook_data)){
                            $iteration = 0;
                            foreach($hook_data[$type . '_msf_hook_data_name'] as $ky => $name){
                                ?>
                                <div class="inputPair" >
                                    <input onkeyup="update_forms_input(event)" data-json="json_msf_hook_data_name[]" data-form="form_msf_hook_data_name[]" name="<?php echo $type; ?>_msf_hook_data_name[]" type="text" placeholder="Name" value="<?php echo $name; ?>">
                                    <select class="hooks_select2" data-json="json_msf_hook_data_value[]" data-form="form_msf_hook_data_value[]" name="<?php echo $type; ?>_msf_hook_data_value[]">
                                        <?php
                                        $key = 0;
                                        $available_questions = [];
                                        foreach($msf_form_questions as $ms_question){
                                            foreach ($questions as $question) :
                                                if($question->id === $ms_question['question_id']){
                                                    $available_questions[$question->field_name] = $question->question;
                                                    ?>
                                                    <option 
                                                    <?php echo $hook_data[$type . '_msf_hook_data_value'][$ky] == $question->field_name ? "selected" : ""; ?>
                                                    data-type="<?php echo $question->question_type; ?>" value="<?php echo $question->field_name; ?>"><?php echo $question->id . " - " . $question->question; ?></option>
                                                    <?php 
                                                }
                                            endforeach;
                                            $key++;
                                        }
                                        foreach($constant_questions as $index => $const_question){
                                            $available_questions[$index] = $const_question;
                                            ?>
                                                <option
                                                <?php echo $hook_data[$type . '_msf_hook_data_value'][$ky] === $index ? "selected" : ""; ?>
                                                 data-type="<?php echo $question->question_type; ?>" value="<?php echo $index; ?>"><?php echo $const_question; ?>
                                                </option>
                                            <?php
                                        }
                                        if(!isset($available_questions[$hook_data[$type . '_msf_hook_data_value'][$ky]])){
                                            ?>
                                            <option 
                                            selected
                                            data-type="<?php echo $question->question_type; ?>" value="<?php echo $hook_data[$type . '_msf_hook_data_value'][$ky]; ?>"><?php echo $hook_data[$type . '_msf_hook_data_value'][$ky]; ?></option>
                                            <?php 
                                        }
                                        ?>
                                    </select>
                                    <?php
                                        if($iteration !== 0) :
                                    ?>
                                    <button onclick="delEl(event)" type="button" class="removeInput">-</button>
                                    <?php
                                        endif;
                                    ?>
                                </div>
                                <?php
                                $iteration++;
                            }
                        }else{
                            echo '<div class="inputPair" >';
                            $this->repeater_hook_html($form_id, $type);
                            echo '</div>';
                        }
                        ?>
                    </div>

                    <button type="button"  class="addInput">+</button>
                </td>
            </tr>
            <?php
             if(isset($hook_data['json_msf_url_hook']) && isset($hook_data['form_msf_url_hook']) && $type === 'form'){
                ?>
                <tr valign="top">
                    <th scope="row"></th>
                    <td><button type="button" class="remove"><span class="dashicons dashicons-remove"></span></button></td>
                </tr>
                <?php
             }
            ?>
        </table>
        <?php
        $content = ob_get_clean();
        return $content;
    }


    function repeater_hook_html($form_id, $type){
        ob_start();
        $questions = $this->get_all_entries('questions');
        $msf_form_questions = get_post_meta($form_id, 'msf_form_questions', true);
        ?>
            <input onkeyup="update_forms_input(event)" data-json="json_msf_hook_data_name[]" data-form="form_msf_hook_data_name[]" name="<?php echo $type; ?>_msf_hook_data_name[]" type="text" placeholder="Name" value="">
            <select class="hooks_select2" data-json="json_msf_hook_data_value[]" data-form="form_msf_hook_data_value[]" name="<?php echo $type; ?>_msf_hook_data_value[]">
                <?php
                $key = 0;
                foreach($msf_form_questions as $ms_question){
                    foreach ($questions as $question) :
                        if($question->id == $ms_question['question_id']){
                            ?>
                            <option 
                            data-type="<?php echo $question->question_type; ?>" value="<?php echo $question->field_name; ?>"><?php echo $question->id . " - " . $question->question; ?></option>
                            <?php 
                        }
                    endforeach;
                    $key++;
                }
                ?>
                <option data-type="<?php echo $question->question_type; ?>" value="firstname msf">First Name</option>
                <option data-type="<?php echo $question->question_type; ?>" value="lastname msf">Last Name</option>
                <option data-type="<?php echo $question->question_type; ?>" value="email msf">Email</option>
                <option data-type="<?php echo $question->question_type; ?>" value="phone msf">Phone</option>
                <option data-type="<?php echo $question->question_type; ?>" value="address line msf">Address</option>
                <option data-type="<?php echo $question->question_type; ?>" value="city msf">City</option>
                <option data-type="<?php echo $question->question_type; ?>" value="state msf">State</option>
                <option data-type="<?php echo $question->question_type; ?>" value="zip msf">Zip</option>
            </select>
        <?php
        $content = ob_get_clean();
        return $content;
    }
}
