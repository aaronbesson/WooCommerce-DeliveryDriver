<?php

/*
* Plugin Name: WooCommerce Driver Assignment
* Description: Allows admin to assign a driver to an order and add driver data to order meta
* Author: Aaron Besson (https://github.com/aaronbesson/WooCommerce-DeliveryDriver)
* Version: 1.0
*/

// Add driver user role
function create_driver_user_role()
{
    add_role(
        'driver',
        __('Driver'),
        array(
            'read'         => true,
            'edit_posts'   => false,
            'delete_posts' => false
        )
    );
}
add_action('init', 'create_driver_user_role');

// Add driver selection field to order edit page
add_action('woocommerce_admin_order_data_after_billing_address', 'add_driver_selection_field', 10, 1);
function add_driver_selection_field($order)
{
    $drivers = get_users(array('role' => 'driver'));
    $driver_id = get_post_meta($order->get_id(), '_driver_id', true);
?>
    <p>
        <strong><?php _e('Assign Driver', 'woocommerce'); ?>:</strong>
        <select name="_driver_id" id="_driver_id" class="widefat">
            <option value=""><?php _e('Select a driver', 'woocommerce'); ?></option>
            <?php foreach ($drivers as $driver) : ?>
                <option value="<?php echo $driver->ID; ?>" <?php selected($driver_id, $driver->ID); ?>>
                    <?php echo $driver->display_name; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
<?php
}
// Save driver selection field
add_action('woocommerce_process_shop_order_meta', 'save_driver_selection_field', 10, 1);
function save_driver_selection_field($post_id)
{
    if (isset($_POST['_driver_id'])) {
        update_post_meta($post_id, '_driver_id', sanitize_text_field($_POST['_driver_id']));
    }
}

// Add driver data to order meta
add_action('woocommerce_checkout_update_order_meta', 'add_driver_data_to_order_meta', 10, 2);
function add_driver_data_to_order_meta($order_id, $posted)
{
    $driver_id = get_post_meta($order_id, '_driver_id', true);
    if (!empty($driver_id)) {
        $driver_data = get_userdata($driver_id);
        update_post_meta($order_id, '_driver_name', $driver_data->display_name);
        update_post_meta($order_id, '_driver_email', $driver_data->user_email);
    }
}

?>
