<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class categories_data_table extends WP_List_Table
{
    private $table_data;
    public $columns;

 
    // Get table data
    private function get_table_data() {
        // global $wpdb;

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
        $primary  = 'category';
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

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'category':
            case 'id':
                return isset($item[$column_name]) ? $item[$column_name] : '';
            default:
                return '';
        }
    }
    
    function handle_delete_action() {
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) {
            if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'coltitle_nonce_action' ) ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'que_categories';
                $action = $wpdb->delete(
                    $table_name,
                    array( 'id' => absint( $_GET['element'] ) ),
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

    function column_cb($item)
    {
        if(array_key_exists('id',$item)){
            $id = $item['id'];
        }else{
            $id = $item['ID'];
        }
        return sprintf('<input type="checkbox" name="element[]" value="%s" />',$id);
    }


    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'category'  => array('category', false)
        );
        return $sortable_columns;
    }

    function column_title($item)
    {
        $edit_url = "/wp-admin/admin.php?page=category-add&id= " . $item['id'];
        $delete_url = wp_nonce_url( admin_url( 'admin.php?page=' . $_REQUEST['page'] . '&action=delete&element=' . $item['id'] ), 'coltitle_nonce_action' );
        
        $actions = array(
            'edit'   => '<a href="' . $edit_url . '">' . __( 'Edit', 'multistep-flow' ) . '</a>',
            'delete' => '<a href="' . $delete_url . '">' . __( 'Delete', 'multistep-flow' ) . '</a>',
        );
        

        return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions));
    }
    
    function column_category($item)
    {
        $edit_url = "/wp-admin/admin.php?page=category-add&id= " . $item['id'];
        $delete_url = wp_nonce_url( admin_url( 'admin.php?page=' . $_REQUEST['page'] . '&action=delete&element=' . $item['id'] ), 'coltitle_nonce_action' );
        
        $actions = array(
            'edit'   => '<a href="' . $edit_url . '">' . __( 'Edit', 'multistep-flow' ) . '</a>',
            'delete' => '<a href="' . $delete_url . '">' . __( 'Delete', 'multistep-flow' ) . '</a>',
        );
        

        return sprintf('%1$s %2$s', $item['category'], $this->row_actions($actions));
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
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'category';

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }


}


?>