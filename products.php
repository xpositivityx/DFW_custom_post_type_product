<?php 
/*
Plugin Name: Product Manager
Plugin URI: http://dev.flairsecurity.com/
Description: Declares a plugin that will create a custom post type
Version: 1.0
Author: David Williams
License: GPLv2
*/


add_action("admin_init", 'script_enqueue');

function script_enqueue(){
	wp_enqueue_style('products-style', plugins_url('/css/main.css', __FILE__));
	wp_enqueue_script('product-script', plugins_url('/js/script.js', __FILE__));
}


add_action('init', 'create_product_post');

function create_product_post(){
	register_post_type('product',
		array(
			'labels' => array(
				'name' => "Products",
				'singular_name' => 'Product',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Product',
				'edit' => 'Edit',
				'edit_item' => 'Edit Product',
				'search_items' => "Search Products",
				'not_found' => "No Products Found",
				'not_found_in_trash' => "No Products Found In Trash"),
			'public' => true,
			'menu_position' => 15,
			'supports' => array('title'),
			'has_archive' => false	
		)
	);
	flush_rewrite_rules();
}

add_action('add_meta_boxes', 'my_admin');

function my_admin() {
	add_meta_box('product_details_meta_box',
		__('Product Details'),
		'display_products_meta_box',
		'product', 'normal', 'high');
	add_meta_box('product_pictures_meta_box',
		__('Product Media'),
		'display_picture_meta_box',
		'product', 'normal', 'low');
	add_meta_box('product_data_meta_box',
		__('Product Data Sheets'),
		'display_data_meta_box',
		'product', 'normal', 'low');
}

function display_products_meta_box($post) {
	global $wpdb;
  $results = $wpdb->get_results( 
    "SELECT * FROM $wpdb->terms
    LEFT JOIN $wpdb->term_taxonomy
    ON     $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
    WHERE $wpdb->term_taxonomy.parent = 94
    "   
 	);
 	$post_category = get_the_category($post->ID)[0]->cat_name;
	$meta = get_post_meta(get_the_ID($post));
	wp_nonce_field( plugin_basename(__FILE__), 'product_details_box_content_nonce');
?>
	<div class="label">Category</div>
	<div class="input">
		<select name="category">
			<?php 
				foreach($results as $result) {
					$arr = search_by_id($result->term_id);
					if(!empty($arr)){
						echo "<option disabled='true'>$result->name</option>";
						foreach($arr as $i){
							$selected = ($i->name == $post_category) ? "selected" : '';
							echo "<option " . $selected . " >$i->name</option>";
						}
					} else {
						echo "<option'>$result->name</option>";
					}
				} 
			?>
		</select>
	</div>
	<div class="label">Color</div>
	<div class="input"><input type="text" name="pColor" id="color"></div>
	<div class="label">Custom Options</div>
	<div class="input"><input type="text" name="pOptions"></div>
	<div class="label">Details</div>
	<div class="input"><input type="textarea" name="pDetails"></div>
	<div class="label">Electrical Rating</div>
	<div class="input"><input type="text" name="pRating"></div>
	<div class="label">Flange</div>
	<div class="input"><input type="text" name="pFlange"></div>
	<div class="label">Gap</div>
	<div class="input"><input type="text" name="pGap"></div>
	<div class="label">Input</div>
	<div class="input"><input type="text" name="pInput"></div>
	<div class="label">Lead Length(in.)</div>
	<div class="input"><input type="text" name="pLength"></div>
	<div class="label">Loop Type</div>
	<div class="input"><input type="text" name="pLoop"></div>
	<div class="label">Mounting</div>
	<div class="input"><input type="text" name="pMounting"></div>
	<div class="label">Operating Temperature</div>
	<div class="input"><input type="text" name="pTemperature"></div>
	<div class="label">Operating Voltage</div>
	<div class="input"><input type="text" name="pVoltage"></div>
	<div class="label">Other</div>
	<div class="input"><input type="text" name="pOther"></div>
	<div class="label">Output</div>
	<div class="input"><input type="text" name="Output"></div>
	<div class="label">Power</div>
	<div class="input"><input type="text" name="pPower"></div>
	<div class="label">Power Consumption</div>
	<div class="input"><input type="text" name="pConsumption"></div>
	<div class="label">Range</div>
	<div class="input"><input type="text" name="pRange"></div>
	<div class="label">Size</div>
	<div class="input"><input type="text" name="pSize"></div>
	<div class="label">Termination</div>
	<div class="input"><input type="text" name="pTermination"></div>
	<div class="label">Ul Rating</div>
	<div class="input"><input type="text" name="pRating"></div>
	<div class="label">Warranty</div>
	<div class="input"><input type="text" name="pWarranty"></div>
	<div class="label">Weight</div>
	<div class="input"><input type="text" name="pLabel"></div>
	<div class="label">Zones</div>
	<div class="input"><input type="text" name="pZones"></div>
	<script>
	window.onload = setVals();
	function setVals() {
		<?php 
			foreach($meta as $key => $value){
				if(!($key == "_edit_lock" || $key == "_edit_last")){
					if(strlen($value[0]) > 1) {
						echo "document.getElementsByName('p" . ucfirst($key) . "')[0].value = '" . $value[0] . "';";
					}
				}
			} 
		?>
	}
	</script>
<?php
}

function display_picture_meta_box($post){
	$attachments = get_attachments('image', $post->ID);
	print add_attachment_fields("Image", $attachments);
	?>
	<script>
		jQuery(".destroyer").click(function() {

			var data = {
				'id': this.id,
				'action': "delete_media"
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				if(response != 'false'){
					var finder = "[destructor='" + response + "']";
					jQuery(finder).remove();
				} else {
					alert("There was an error deleting your media.");
				}
			});
		});
	</script>
	<?php
}

function display_data_meta_box($post){
 	$pdfs = get_attachments('application', $post);
	print add_attachment_fields("Document", $pdfs);
	?>
	<script>
		jQuery(".destroyer").click(function() {

			var data = {
				'id': this.id,
				'action': "delete_media"
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				if(response != 'false'){
					var finder = "[destructor='" + response + "']";
					jQuery(finder).remove();
				} else {
					alert("There was an error deleting your media.");
				}
			});
		});
	</script>
	<?php
}


add_action( 'save_post', 'product_details_box_save');

function product_details_box_save($post_id) {
	if ( defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return;
	}
	if (!wp_verify_nonce( $_POST['product_details_box_content_nonce'], plugin_basename(__FILE__))){
		return;
	}
	if ( 'page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)){
			return;
		} else {
			if (!current_user_can('edit_post', $post_id)){
				return;
			}
		}
	}

	$atts = array();
	$atts['color'] = esc_html($_POST['pColor']);
	$atts['options'] = esc_html($_POST['pOptions']);
	$atts['details'] = esc_html($_POST['pDetails']);
	$atts['rating'] = esc_html($_POST['pRating']);
	$atts['flange'] = esc_html($_POST['pFlange']);
	$atts['gap'] = esc_html($_POST['pGap']);
	$atts['input'] = esc_html($_POST['pInput']);
	$atts['length'] = esc_html($_POST['pLength']);
	$atts['loop'] = esc_html($_POST['pLoop']);
	$atts['mounting'] = esc_html($_POST['pMounting']);
	$atts['temperature'] = esc_html($_POST['pTemperature']);
	$atts['voltage'] = esc_html($_POST['pVoltage']);
	$atts['other'] = esc_html($_POST['pOther']);
	$atts['output'] = esc_html($_POST['Output']);
	$atts['power'] = esc_html($_POST['pPower']);
	$atts['consumption'] = esc_html($_POST['pConsumption']);
	$atts['range'] = esc_html($_POST['pRange']);
	$atts['size'] = esc_html($_POST['pSize']);
	$atts['termination'] = esc_html($_POST['pTermination']);
	$atts['rating'] = esc_html($_POST['pRating']);
	$atts['warranty'] = esc_html($_POST['pWarranty']);
	$atts['label'] = esc_html($_POST['pLabel']);
	$atts['zones'] = esc_html($_POST['pZones']);
	
	if(isset($_POST['category'])){
		$cat = $_POST['category'];
		wp_set_post_categories($post_id, (string)get_cat_ID($cat));
	}

	foreach($atts as $key=>$val) {
		if(strlen($val) > 1){
			update_post_meta($post_id, $key, $val );
		} else {
			update_post_meta($post_id, $key, '');
		}
	}

	upload_attachments($_FILES, $post_id);

}

function xxxx_add_edit_form_multipart_encoding() {
	echo "enctype='multipart/form-data'";
}
add_action('post_edit_form_tag', 'xxxx_add_edit_form_multipart_encoding');

function get_attachments($mime_type, $post_id){
 	$pictures = get_attached_media($mime_type, $post_id);
 	$picture_array = array();
 	foreach($pictures as $picture){
 		$picture_array[] = $picture;
 	}
 	return $picture_array;
}

function add_attachment_fields($label, array $attachments){
	ob_start();
	if(!empty($attachments)){
		$counter = 1;
		foreach($attachments as $pic){
			if($label=="Image"){
		?>
			<div class="image" destructor="<?php print $pic->ID ?>">
				<img class= "label" src="<?php print $pic->guid ?>" alt="">
				<a class="destroyer" id="<?php print $pic->ID ?>">Delete <?php print $label ?></a>
			</div>
		<?php } else { ?>
			<div class="image" destructor="<?php print $pic->ID ?>">
				<div class="label"><?php print $label . ' ' . $counter ?>:</div>
				<img class="label"src="https://cdn2.iconfinder.com/data/icons/windows-8-metro-style/128/pdf.png" alt="">
				<p class="label"><?php print $pic->post_name ?></p>
				<a class="destroyer" id="<?php print $pic->ID ?>">Delete <?php print $label ?></a>
			</div>
		<?php }
			$counter += 1;
		}
		if(($label=="Image" && $counter <= 4)){
			for($i=0; $i<=(4-$counter); $i++){
			?>
				<div class="image">
				<img src="http://vignette3.wikia.nocookie.net/java/images/0/0e/Camera_icon.gif/revision/latest?cb=20090227194712" alt="new-image" class="label">
					<div class="label"><?php print "Choose another " .$label?></div>
					<div class="input"><input type="file" name="p<?php print $label . ($i+1)?>" id=""></div>
				</div>
			<?php
			}
		} 
	}elseif(empty($attachments) && $label == "Image"){
		for($i=0; $i<4; $i++){
			?>
				<div class="image">
				<img src="http://vignette3.wikia.nocookie.net/java/images/0/0e/Camera_icon.gif/revision/latest?cb=20090227194712" alt="new-image" class="label">
					<div class="label"><?php print "Choose another " .$label?></div>
					<div class="input"><input type="file" name="p<?php print $label . ($i+1)?>" id=""></div>
				</div>
			<?php
			}
	}
	if($label == "Document"){
		?>
		<div class="image">
			<div class="label"><?php print "Choose another " .$label?></div>
			<img src="https://cdn2.iconfinder.com/data/icons/windows-8-metro-style/128/pdf.png" alt="" class="label">
			<div class="input"><input type="file" name="p<?php print $label . "1"?>" id=""></div>	
		</div>
		<?php
	}
	?>
	<?php
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}

function upload_attachments($files, $post_id){
	foreach($files as $id => $info){
		if(strlen($info['name']) > 1){
			$attachment = media_handle_upload($id, $post_id);
			if(is_wp_error($attachment)){
				die("there was an error uploading $id");
			}
			update_post_meta($id, '_wp_attachment_image_alt', get_the_title($post_id));
		}
	}
}

add_action( 'wp_ajax_delete_media', 'delete_media' );
add_action('wp_ajax_nopriv_delete_media', 'delete_media');

function delete_media(){
	$target = $_POST['id'];
	wp_delete_attachment($target, $force_delete);
	echo $target;
	wp_die();
}

function reset_attachment_parent($attachment_id){
	global $wpdb;
	$result = $wpdb->query(
		"
			UPDATE $wpdb->posts
			SET post_parent = 0
			WHERE ID = '$attachment_id'
			AND post_type = 'attachment'
		"
		);
	return $result;
}

add_filter('manage_product_posts_columns' , 'my_edit_product_columns');
add_action('manage_product_posts_custom_column', "custom_product_column", 10, 2);
function my_edit_product_columns($columns){
	unset($columns['date']);
	$columns['category'] = "Category";
	$columns["size"] = "Size";
	$columns['color'] = "Color";
	return $columns;
}

function custom_product_column($column, $post_id){
	switch($column){
		case "size" :
			echo get_post_meta($post_id, 'size', true);
			break;
		case "color" :
			echo get_post_meta($post_id, 'color', true);
			break;
		case "category" :
			$cat = get_the_category($post_id);
			echo $cat[0]->cat_name;
			break;
	}
}

add_action('restrict_manage_posts', 'admin_post_filter');
function admin_post_filter(){
	if(isset($_GET['post_type'])){
		$type = $_GET['post_type'];
	}

	if ('product' == $type) {
		$values = array();
		$meta = get_categories();
		foreach($meta as $m){
			$values[$m->cat_name] = $m->cat_name;
		}
?>
	<select name="ADMIN_FILTER_FIELD_VALUE">
		<option value="">Show All Categories</option>
		<?php 
			$current_v = isset($_GET['ADMIN_FILTER_FIELD_VALUE'])? $_GET["ADMIN_FILTER_FIELD_VALUE"] : '';
			foreach($values as $label => $value){
				printf(
					"<option value='%s' %s>%s</option>",
					$value,
					$value == $current_v ? "selected='selected'" : '',
					$label
					);
			}
		 ?>
	</select>
<?php
	}
}

add_filter('parse_query', 'posts_filter');
function posts_filter($query){
	global $pagenow;
	if(isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != ""){
		$query->set('category_name', $_GET['ADMIN_FILTER_FIELD_VALUE']);
	}
}

function get_all_meta_type($type){
	$args = array("post_type" => $type);
	$results = get_posts($args);
	$output = array();
	foreach($results as $result) {
		$meta = get_post_meta($result->ID);
		if(isset($meta['size'][0]) && $meta['size'][0] != ""){
			if(!in_array($output, $meta['size'])){
				$output[] = $meta['size'][0];
			}
		}
	}
	return $output;
}

?>