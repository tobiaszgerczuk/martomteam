<?php
/*
Plugin Name: Martomteam - tools
Author: Tobiasz Gerczuk

*/





function tutsplus_movie_styles() {
    wp_enqueue_style( 'style',  plugin_dir_url( __FILE__ ) . '/css/style.css' );                      
}
add_action( 'wp_enqueue_scripts', 'tutsplus_movie_styles' );




// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
     die;
}
 
// Include the dependencies needed to instantiate the plugin.
foreach ( glob( plugin_dir_path( __FILE__ ) . 'admin/*.php' ) as $file ) {
    include_once $file;
}
 
add_action( 'plugins_loaded', 'tutsplus_custom_admin_settings' );
/**
 * Starts the plugin.
 *
 * @since 1.0.0
 */
function tutsplus_custom_admin_settings() {
 
    $plugin = new Submenu( new Submenu_Page() );
    $plugin->init();
 
}



// dodanie meta do produktu podczas aktualizacji lub utworzenia


// dodanie meta team store
add_action( 'woocommerce_update_product', 'mp_sync_on_product_save', 10, 1 );
function mp_sync_on_product_save($post_id) {

    $product = wc_get_product($post_id);
    $wybor_team = get_field('wybierz_team_store');
   
    if($wybor_team != ''){
  
        update_post_meta($post_id, 'team_store',$wybor_team);
    }
}

// echo $met['team_store'][0];






add_action('save_post','save_post_callback');
function save_post_callback($post_id){
    global $post; 

    $admin = get_field('dodaj_administratora',$post_id);
    if($admin != ''){
        $admin_id = $admin;
        update_user_meta($admin_id, 'team_store_status','manager' );
        update_user_meta($admin_id, 'team_store_smanager_team',$post_id );
        $wp_user_object = new WP_User($admin_id);
        $wp_user_object->add_role('manager');
  
    }

    $team_store = get_field('wybierz_team_store',$post_id);
    if($team_store != ''){
        $team_store_id = $team_store;
        update_post_meta( $post_id, 'team_store', $team_store_id, true );
    }
 
 
}


add_action( 'user_register', 'myplugin_registration_save', 10, 1 );

function myplugin_registration_save( $user_id ) {

    if ( isset( $_GET['team_store'] ) ){
    
        $meta  =  $_GET['team_store'];
        update_user_meta($user_id, 'team_store_zawodnik',$meta);
        update_user_meta($user_id, 'team_store_status','zawodnik' );
        $wp_user_object = new WP_User($user_id);
        $wp_user_object->set_role('zawodnik');

    }

}

// callback function
function wpdocs_send_welcome_email( $user_login, WP_User $user ) {
 
    $user_id =$user->ID;
    if ( isset( $_GET['team_store'] ) ){
        $meta2 = get_user_meta($user_id, 'team_store_zawodnik',true);

        if(empty( $meta2 )){
            update_user_meta($user_id, 'team_store_zawodnik',$_GET['team_store']);
        }
        else{
        $team_st  =  $_GET['team_store'];
        

        if(is_array($meta2) ) {
            if(!in_array($_GET['team_store'], $meta2)){
                $meta2[] = $team_st; //I'm sure you would do more processing here
            }
        }
        else{
            $meta2 = [];
            $meta2[] = $team_st;
        }

        update_user_meta($user_id, 'team_store_zawodnik',$meta2);
       
    }

    }
 
}
 
// action hook
add_action( 'wp_login', 'wpdocs_send_welcome_email', 10, 2 );






function ui_new_role() {  
 
    //add the new user role
    add_role(
        'manager',
        'Manager teamu',
        array(
            'read'  => true,
            'delete_posts'  => true,
            'delete_published_posts' => true,
            'edit_posts'   => true,
            'publish_posts' => true,
            'upload_files'  => true,
            'edit_pages'  => true,
            'edit_published_pages'  =>  true,
            'publish_pages'  => true,
            'delete_published_pages' => false, // This user will NOT be able to  delete published pages.
        )
    );

    add_role(
        'zawodnik',
        'Zawodnik',
        array(
            'read'         => false,
            'delete_posts' => false
        )
    );

    add_role(
        'ambasador',
        'Ambasador',
        array(
            'read'         => false,
            'delete_posts' => false
        )
    );
 
 
}
add_action('admin_init', 'ui_new_role');



function woocommerce_pre_get_posts( $query ) {
    if(isset($_GET['team_store'])){
        if ( ! is_admin() && is_user_logged_in() && is_archive( 'product' ) && $query->is_main_query() ) {

     
        $args = array(
            array(
              'meta_key'  => 'team_store',
              'value'     => $_GET['team_store'],
              'compare'   => '='
            )
          );
          
          
          $query->set( 'meta_query', $args ); 
        }
        else if( ! is_admin() && is_archive() && $query->is_main_query()){
            $query->set( 'post_type', 'asd' );
        }

    }else{

   
  
    if ( ! is_admin() && is_user_logged_in() && is_archive( 'product' ) && $query->is_main_query() ) {
        $user_id = get_current_user_id();
       
		$team_zawodnik = get_user_meta( $user_id, 'team_store_zawodnik', true );
		$team_manager = get_user_meta( $user_id, 'team_store_smanager_team', true );
  
        if($team_manager != ''){
            $team = $team_manager;
        }
        if($team_zawodnik != ''){
            $team = $team_zawodnik;
        }
    


       
      // Option 1
      $args = array(
        array(
          'meta_key'  => 'team_store',
          'value'     => $team,
          'compare'   => '='
        )
      );
      
      
      $query->set( 'meta_query', $args ); 
    }
    else if( ! is_admin() && is_archive() && $query->is_main_query()){
        $query->set( 'post_type', 'asd' );
    }
   
    else{

    }
}
  }
  add_action( 'pre_get_posts', 'woocommerce_pre_get_posts', 20 );

  


  add_filter( 'woocommerce_get_price_html', 'bbloomer_alter_price_display', 9999, 2 );
 
    function bbloomer_alter_price_display( $price_html, $product ) {
        
        $cena_amasador = get_field('cena_dla_ambasadora',$product->id);
        $cena_manager = get_field('cena_dla_managera',$product->id);

        // ONLY ON FRONTEND
        if ( is_admin() ) return $price_html;
        
        // ONLY IF PRICE NOT NULL
        if ( '' === $product->get_price() ) return $price_html;
        
        // IF CUSTOMER LOGGED IN, APPLY 20% DISCOUNT   
        if ( wc_current_user_has_role( 'ambasador' ) &&  $cena_amasador != '') {
      
            $price_html = wc_price($cena_amasador);
        }
        else if ( wc_current_user_has_role( 'manager' ) &&  $cena_manager != '') {
  
            $price_html = wc_price($cena_manager);
        }
        
        return $price_html;
    
    }


    add_action( 'woocommerce_before_calculate_totals', 'bbloomer_alter_price_cart', 9999 );
 
    function bbloomer_alter_price_cart( $cart ) {
        
    
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
    
        if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) return;

        // LOOP THROUGH CART ITEMS & APPLY 20% DISCOUNT
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {

           

            $product = $cart_item['data'];
            $cena_amasador = get_field('cena_dla_ambasadora',$product->id);
            $cena_manager = get_field('cena_dla_managera',$product->id);
            $price = $product->get_price();

            if ( wc_current_user_has_role( 'ambasador' ) &&  $cena_amasador != '') {
                $cart_item['data']->set_price( $cena_amasador);
            }
            else  if ( wc_current_user_has_role( 'manager' ) &&  $cena_manager != '') {
                $cart_item['data']->set_price( $cena_manager);
            }
          
            
            
        }
    
    }




  

    
function mrpu_plugin_init() {
	load_plugin_textdomain( 'multiple-roles-per-user', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}
add_action( 'plugins_loaded', 'mrpu_plugin_init' );

function mrpu_admin_enqueue_scripts( $handle ) {
	if ( 'user-edit.php' == $handle ) {
		// We need jQuery to move things around :)
		wp_enqueue_script( 'jquery' );
	}
}
add_action( 'admin_enqueue_scripts', 'mrpu_admin_enqueue_scripts', 10 );

/**
 * Adds the GUI for selecting multiple roles per user
 */
function mrpu_add_multiple_roles_ui( $user ) {
	// Not allowed to edit user - bail
	if ( ! current_user_can( 'edit_user', $user->ID ) ) {
		return;
	}
	$roles = get_editable_roles();
	$user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) ); ?>
	<div class="mrpu-roles-container">
		<h3><?php _e( 'User Roles', 'multiple-roles-per-user' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="user_credits"><?php _e( 'Roles', 'multiple-roles-per-user' ); ?></label></th>
				<td>
					<?php foreach ( $roles as $role_id => $role_data ) : ?>
						<label for="user_role_<?php echo esc_attr( $role_id ); ?>">
							<input type="checkbox" id="user_role_<?php echo esc_attr( $role_id ); ?>" value="<?php echo esc_attr( $role_id ); ?>" name="mrpu_user_roles[]"<?php echo in_array( $role_id, $user_roles ) ? ' checked="checked"' : ''; ?> />
							<?php echo $role_data['name']; ?>
						</label>
						<br />
					<?php endforeach; ?>
					<br />
					<span class="description"><?php _e( 'Select one or more roles for this user.', 'multiple-roles-per-user' ); ?></span>
					<?php wp_nonce_field( 'mrpu_set_roles', '_mrpu_roles_nonce' ); ?>
				</td>
			</tr>
		</table>
	</div>
	<?php 
	// Do some hacking around to hide the built-in user roles selector
	// First hide it with CSS and then get rid of it with jQuery ?>
	<style>
		label[for="role"],
		select#role {
			display: none;
		}
	</style>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				var row = $('select#role').closest('tr');
				var clone = row.clone();
				// clone.insertAfter( $('select#role').closest('tr') );
				row.html( $('.mrpu-roles-container tr').html() );
				$('.mrpu-roles-container').remove();
			})
		})(jQuery)
	</script>
<?php }
add_action( 'edit_user_profile', 'mrpu_add_multiple_roles_ui', 0 );

/**
 * Saves the selected roles for the user
 */
function mrpu_save_multiple_user_roles( $user_id ) {
	// Not allowed to edit user - bail
	if ( ! current_user_can( 'edit_user', $user_id ) || ! wp_verify_nonce( $_POST['_mrpu_roles_nonce'], 'mrpu_set_roles' ) ) {
		return;
	}
	
	$user = new WP_User( $user_id );
	$roles = get_editable_roles();
	$new_roles = isset( $_POST['mrpu_user_roles'] ) ? (array) $_POST['mrpu_user_roles'] : array();
	// Get rid of any bogus roles
	$new_roles = array_intersect( $new_roles, array_keys( $roles ) );
	$roles_to_remove = array();
	$user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) );
	if ( ! $new_roles ) {
		// If there are no roles, delete all of the user's roles
		$roles_to_remove = $user_roles;
	} else {
		$roles_to_remove = array_diff( $user_roles, $new_roles );
	}

	foreach ( $roles_to_remove as $_role ) {
		$user->remove_role( $_role );
	}

	if ( $new_roles ) {
		// Make sure that we don't call $user->add_role() any more than it's necessary
		$_new_roles = array_diff( $new_roles, array_intersect( array_values( $user->roles ), array_keys( $roles ) ) );
		foreach ( $_new_roles as $_role ) {
			$user->add_role( $_role );
		}
	}
}
add_action( 'edit_user_profile_update', 'mrpu_save_multiple_user_roles' );

/**
 * Gets rid of the "Role" column and adds-in the "Roles" column
 */
function mrpu_add_roles_column( $columns ) {
	$old_posts = isset( $columns['posts'] ) ? $columns['posts'] : false;
	unset( $columns['role'], $columns['posts'] );
	$columns['mrpu_roles'] = __( 'Roles', 'multiple-roles-per-user' );
	if ( $old_posts ) {
		$columns['posts'] = $old_posts;
	}

	return $columns;
}
add_filter( 'manage_users_columns', 'mrpu_add_roles_column' );

/**
 * Displays the roles for a user
 */
function mrpu_display_user_roles( $value, $column_name, $user_id ) {
	static $roles;
	if ( ! isset( $roles ) ) {
		$roles = get_editable_roles();
	}
	if ( 'mrpu_roles' == $column_name ) {
		$user = new WP_User( $user_id );
		$user_roles = array();
		$_user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) );
		foreach ( $_user_roles as $role_id ) {
			$user_roles[] = $roles[ $role_id ]['name'];
		}

		return implode( ', ', $user_roles );
	}

	return $value;
}
add_filter( 'manage_users_custom_column', 'mrpu_display_user_roles', 10, 3 );



add_filter( 'woocommerce_get_endpoint_url', 'wptips_custom_woo_endpoint', 10, 2 );
function wptips_custom_woo_endpoint( $url, $endpoint ){
    $user_id = get_current_user_id();
    $team_store_id =  get_usermeta($user_id, 'team_store_smanager_team');

     if( $endpoint == 'produkty' ) {
        $url = "/sklep/?team_store=".$team_store_id.""; // Your custom URL to add to the My Account menu
    }
    return $url;
}

add_action( 'admin_menu', 'no_woo' );

function no_woo() {
    if ( current_user_can('manager') == true ) {
      
            remove_all_actions('admin_notices');
      
      //Hide "Marketing".
	remove_menu_page('woocommerce-marketing');
    remove_menu_page('upload.php');
	//Hide "Tools → Scheduled actions".
	remove_submenu_page('tools.php', 'action-scheduler');
	//Hide "WooCommerce".
	remove_menu_page('woocommerce');
	//Hide "WooCommerce → Desktop".
	remove_submenu_page('woocommerce', 'wc-admin');
	//Hide "WooCommerce → Orders".
	remove_submenu_page('woocommerce', 'edit.php?post_type=shop_order');
	//Hide "WooCommerce → Coupons".
	remove_submenu_page('woocommerce', 'edit.php?post_type=shop_coupon');
	//Hide "WooCommerce → Customers".
	remove_submenu_page('woocommerce', 'wc-admin&path=/customers');
	//Hide "WooCommerce → Reports".
	remove_submenu_page('woocommerce', 'wc-reports');
	//Hide "WooCommerce → Settings".
	remove_submenu_page('woocommerce', 'wc-settings');
	//Hide "WooCommerce → Status".
	remove_submenu_page('woocommerce', 'wc-status');
	//Hide "WooCommerce → Extensions".
	remove_submenu_page('woocommerce', 'wc-addons');
	//Hide "Products".
	remove_menu_page('edit.php?post_type=product');

	
    }
}

?>