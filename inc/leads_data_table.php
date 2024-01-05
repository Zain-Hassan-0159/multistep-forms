<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class leads_data_table extends WP_List_Table
{
    private $table_data;
    public $columns;

 
    // Get table data
    private function get_table_data() {

        return $this->table_data ;
    }

    function set_table_data($data){
        $this->table_data = $data;
    }
    // Bind table with columns, data and all
    function prepare_items()
    {


        //data
        $this->table_data = $this->get_table_data();
        // $this->table_data = $data;

        $columns = $this->columns;
        $hidden = $hidden = ( is_array(get_user_meta( get_current_user_id(), 'managetoplevel_page_supporthost_list_tablecolumnshidden', true)) ) ? get_user_meta( get_current_user_id(), 'managetoplevel_page_supporthost_list_tablecolumnshidden', true) : array();
        $sortable = $this->get_sortable_columns();
        // $sortable = array();
        $primary  = 'title';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);

        usort($this->table_data, array(&$this, 'usort_reorder'));
        
        /* pagination */
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $per_page, // items to show on a page
                'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));
  
          $this->items = $this->table_data;
    }

    function extra_tablenav($which) {
        if(isset($_GET['page']) && $_GET['page'] === 'survey-leads'){
            if ($which == 'top') {
                $args = array(
                    'posts_per_page'   => -1,
                    'post_type'        => 'msf_lead_froms',
                );
                $the_query = new WP_Query($args);
        
                echo '<select name="filter_by_form" id="filter_by_form">';
                    echo '<option value="">Filter by form</option>';
                    while ($the_query->have_posts()) {
                        $the_query->the_post();
                        $selected = (isset($_REQUEST['filter_by_form']) && $_REQUEST['filter_by_form'] == get_the_title()) ? ' selected="selected"' : '';
                        echo '<option value="' . get_the_title() . '"' . $selected . '>' . get_the_title() . '</option>';
                    }
                    wp_reset_postdata();
                echo '</select>';
            }
        }
    }
    

    function handle_delete_all_action(){
        
    }

    function handle_delete_action() {
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) {
            if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'coltitle_nonce_action' ) ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'posts';
                $action = $wpdb->delete(
                    $table_name,
                    array( 'ID' => absint( $_GET['element'] ) ),
                    array( '%d' )
                );

                if($action !== false){
                    $post_id = absint( $_GET['element'] );
                    // get all meta keys for the post
                    $meta_keys = get_post_custom_keys( $post_id );

                    // delete all meta for the post
                    if($meta_keys){
                        foreach ( $meta_keys as $key ) {
                            delete_post_meta_by_key( $key, $post_id );
                        }
                    }
                }
            }
        }
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'title':
            case 'post_content':
            case 'shortcode':
            case 'post_status':
            case 'post_date':
                return isset($item[$column_name]) ? $item[$column_name] : '';
            default:
                return '';
        }
    }
    

    function column_cb($item)
    {
        if(array_key_exists('id',$item)){
            $id = $item['id'];
        }else{
            $id = $item['ID'];
        }
        return sprintf('<input type="checkbox" name="element[]" value="%s" />',$id);
    }

    
    function column_shortcode($item)
    {
        return sprintf('[MSF-lead-form id="%s"]',$item['ID']);
    }

    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'title'  => array('title', false),
            'status' => array('status', false),
            'post_title'   => array('post_title', true),
            'post_status'   => array('post_status', true),
			'post_date'   => array('post_date', true),
        );
        return $sortable_columns;
    }

    
    function column_post_title($item)
    {
        $edit_url = '';
        $label = '';
        $action_url = '';
        if(isset($_GET['page']) && $_GET['page'] === 'survey-leads'){
            $edit_url = admin_url( 'admin.php?page=survey-leads&preview=' . $item['ID'] );
            $label = 'Preview';
        }else{
            $edit_url = admin_url( 'admin.php?page=msf-new-form&edit_id=' . $item['ID'] );
            $label = 'Edit';
            $action_url = admin_url( 'admin.php?page=msf-form-action&form_id=' . $item['ID'] );
        }
        $delete_url = wp_nonce_url( admin_url( 'admin.php?page=' . $_REQUEST['page'] . '&action=delete&element=' . $item['ID'] ), 'coltitle_nonce_action' );
        
        $actions = array(
            'edit'   => '<a href="' . $edit_url . '">' . __( $label, 'multistep-flow' ) . '</a>',
            'delete' => '<a href="' . $delete_url . '">' . __( 'Delete', 'multistep-flow' ) . '</a>'
        );

        if(isset($_GET['page']) && $_GET['page'] === 'msf-lead-forms'){
            $actions = array(
                'edit'   => '<a href="' . $edit_url . '">' . __( $label, 'multistep-flow' ) . '</a>',
                'delete' => '<a href="' . $delete_url . '">' . __( 'Delete', 'multistep-flow' ) . '</a>',
                'action_hook' => '<a target="_blank" href="' . $action_url . '">' . __( 'Action Hooks', 'multistep-flow' ) . '</a>',
            );
        }
        

        return sprintf('%1$s %2$s', $item['post_title'], $this->row_actions($actions));
    }
    
    function get_bulk_actions()
    {
        $actions = array(
            'delete_all'    => __('Delete', 'multistep-flow'),
            'draft_all' => __('Move to Draft', 'multistep-flow')
        );
        return $actions;
    }
    
    // Sorting function
    function usort_reorder($a, $b)
    {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'post_title';

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }


}


?>