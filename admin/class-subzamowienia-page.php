<?php
/**
 * Creates the submenu page for the plugin.
 *
 * @package Custom_Admin_Settings
 */
 
/**
 * Creates the submenu page for the plugin.
 *
 * Provides the functionality necessary for rendering the page corresponding
 * to the submenu with which this page is associated.
 *
 * @package Custom_Admin_Settings
 */
class Submenu_Page {
 
        /**
     * This function renders the contents of the page associated with the Submenu
     * that invokes the render method. In the context of this plugin, this is the
     * Submenu class.
     */
    public function render() {
        echo '<h1>Zamówienia - Team Stores</h1>';

        ?>
        <style>
          table.tabor{
            margin-top:30px;
          }
           table.tabor tr:nth-child(2n){
            background-color:#ededed;
        }
        table.tabor td{
          padding:10px;
        }
        table.tabor thead tr td{
          font-weight:600;
        }
        .fil_rot{
          margin-top:50px;
          overflow:hidden;
          height:auto;
        }
        </style>

        <?php 
          $args = array(  
            'post_type' => 'teams',
        );
        $teams = [];

        $loop = new WP_Query( $args ); 
            
        while ( $loop->have_posts() ) : $loop->the_post(); 
        $teams[] = get_the_ID();
        endwhile;

        wp_reset_postdata(); 

        ?>

<div class="tablenav top fil_rot">

       
<div class="alignleft actions ">
  <form action="" method="post">
  <span>Data od: </span><input type="date" name="data_od" placeholder="Data od:">
  <span>Data do: </span><input type="date" name="data_do" placeholder="Data do:">
    <select name="team_store" >
      <?php  
       if($_POST['team_store']){
        
        echo  "<option value='".$_POST['team_store']."'>".get_the_title($_POST['team_store'])."</option>";
       }
  foreach ( $teams as $team ){
    echo  "<option value='".$team."'>".get_the_title($team )."</option>";
  }
      ?>
     
    </select>
  <input type="submit" name="team_filtr" class="button" value="Przefiltruj">		
</div>

<br class="clear">

</form>
</div>


        <?php


        function example_get_orders_by_product( $product_id ) {

            global $wpdb;
        
            $raw = "
                SELECT
                  `items`.`order_id`,
                  MAX(CASE WHEN `itemmeta`.`meta_key` = '_product_id' THEN `itemmeta`.`meta_value` END) AS `product_id`
                FROM
                  `{$wpdb->prefix}woocommerce_order_items` AS `items`
                INNER JOIN
                  `{$wpdb->prefix}woocommerce_order_itemmeta` AS `itemmeta`
                ON
                  `items`.`order_item_id` = `itemmeta`.`order_item_id`
                WHERE
                  `itemmeta`.`meta_key` IN('_product_id')
                GROUP BY
                  `items`.`order_item_id`
                HAVING
                  `product_id` = %d";

                  
        
            $sql = $wpdb->prepare( $raw, $product_id );
        
            return array_map(function ( $data ) {
                return wc_get_order( $data->order_id );
            }, $wpdb->get_results( $sql ) );
        
        }

        if(isset($_POST['team_filtr'])){
          if($_POST['team_store']){
            $args = array(
              'post_type' => 'product',
              'posts_per_page' => 999999,
              'meta_query' => array(
                array('key' => 'team_store', //meta key name here
                      'value' => $_POST['team_store'], 
                      'compare' => '=',
                )
            ),  
              );
          }
        }
        else{
          $args = array(
            'post_type' => 'product',
            'posts_per_page' => 999999,
        
            );
        }
      
        $products = new WP_Query( $args );

        if ( $products->have_posts() ) {
            ?>
                <table class="tabor">
                <thead>
                    <tr>
                        <td>Produkty</td>
                        <td>SKU</td>
                        <td>Warianty produktu</td>
                     
                    </tr>
                </thead>
            <?php
            while ( $products->have_posts() ) : $products->the_post();

            $product_id = get_the_ID();
            $varianty = [];
            $licz = 0;
            $product = wc_get_product($product_id );
             ?>
                 <tr>
                    <td><?php  echo get_the_title(); ?></td>
                    <td><?php echo $product->get_sku(); ?></td>
                    <td>
                        <?php
                         $prtnames = [];
                            $orders_ids = example_get_orders_by_product($product_id);
                            foreach ($orders_ids as $order){

                            $dat =   $order->get_date_created();
                            if($dat > '2022-01-21T21:34:54+01:00'){
                          
                              $items = $order->get_items();
                              
                              foreach( $items as $item ) {
                                $product_ido = $item->get_product_id();
                              
                               
                                if($product_ido ==  $product_id ){
                                  // echo $product_ido."+".$product_id." ";
                                $product_name = $item->get_name();
                                $product_type = WC_Product_Factory::get_product_type($product_ido);
                               if( $product_type == 'variable'){
                                foreach ($item->get_meta_data() as $metaData) {
                                  $attribute = $metaData->get_data();
                              
                                  // attribute value
                                  $value = $attribute['value'];
                                  
                                  if($slug != '_reduced_stock'){
                                    $prtnames[] =  strval($value ) ;
                                  }


                                  
                              
                                  // attribute slug
                                  $slug = $attribute['key'];

                               
                                
                                }
                               }
                               else{
                             
                                    $prtnames[] =  strval('Brak wariantu') ;
                                 
                                 
                                }

                             
                              }
                         
                               
                              }
                            }
                                  
                            }
                        
                            $wyniki = array_count_values($prtnames);
                            if($wyniki){
                              foreach ($wyniki as $wynik => $val){
                                echo $val."x <strong>".$wynik."</strong><br>";
                              }
                            }
                            else{
                              echo "<p style='color:#DDA360;'>Brak zamówień dla tego produktu</p>";
                            }

                         
                        ?>
                 
                 </td>
                
                 </tr>
             <?php
            endwhile;

            ?>

            </table>
            <?php
        } else {
            echo __( 'No products found' );
        }
        wp_reset_postdata();




    }
}