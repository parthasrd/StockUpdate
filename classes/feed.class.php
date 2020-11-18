<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
class feed
{
	private $db;
	private $session;

	public function __construct($host = null, $db = null, $username = null, $pw = null)
	{
		$this->db = new database();
		$this->session = new session();
	}

	public function get_all_feeds(){
	    $login_store_id = $this->store_data()->login_store_id;
        $this->db->query("select * from sync_feeds where status = 'Y' and login_store_id ='".$login_store_id."' ");
        $res = $this->db->resultset();
        return $res;
    }

    public function get_single_feed($feed_id)
    {
    	$sql="select * from sync_feeds where id ='".$feed_id."'";
		$this->db->query($sql);
		return $this->db->singleObj();
    }

    public function get_all_feed_with_details($srch_param){
    	$sql = "select f.*,ft.feed_title, fm.title as connection_title from sync_feeds as f 
INNER JOIN sync_feed_types as ft ON ft.id = f.feed_type_id
INNER JOIN sync_feed_method as fm ON fm.id = f.feed_method_id";

		$sql.= " where f.status = 'Y' and f.login_store_id = '".$this->store_data()->login_store_id."' ";

		if(trim($srch_param)!='')
		{
			$sql.= " and f.title like '".$srch_param."%' ";
		} 

        $this->db->query($sql);
        $res = $this->db->resultsetObj();
        return $res;
    }

    public function get_all_feed_types() {
        $this->db->query("select * from sync_feed_types where status='Y' ");
        $res = $this->db->resultsetObj();
        return $res;
    }

    public function get_all_feed_methods() {
        $this->db->query("select * from sync_feed_method where status='Y' ");
        $res = $this->db->resultsetObj();
        return $res;
    }

	public function ftp_method_test_connection( $ftpHost, $ftpUsername, $ftpPassword )
	{
		// open an FTP connection
        $res = false;
        $connId = ftp_connect($ftpHost);
		if ($connId){
            // try to login
            if(@ftp_login($connId, $ftpUsername, $ftpPassword)){
                $res = true;
            }else{
                $res = false;
            }
            // close the connection
            ftp_close($connId);
		}
		return $res;
	}

    public function create_new_feed( $feedType, $feedMethod, $defaultType = 'ADD FEED' )
    {
        $tup="select feed_title from sync_feed_types where id='".$feedType."'";
        $this->db->query($tup);
        $data = $this->db->singleObj();
        $defaultType = $data->feed_title;

        $this->db->query("INSERT INTO sync_feeds (title, feed_type_id, feed_method_id) 
        VALUES 
        ((select  if(COUNT(*) ,CONCAT(ft.feed_title, ' ', COUNT(*)), :title) as title from sync_feeds as f 
        INNER JOIN sync_feed_types as ft ON ft.id = f.feed_type_id 
        where f.feed_type_id = :feed_type_id),
        :feed_type_id, 
        (SELECT id FROM `sync_feed_method` WHERE `php_file_name` = :php_file_name)) ");
        $this->db->bind( ':feed_type_id', $feedType );
        $this->db->bind( ':php_file_name', $feedMethod );
		$this->db->bind( ':title', $defaultType );

        $res = $this->db->getLastID();

        $this->session->add_session('feed_id', $res );

        $dataSet = array('login_store_id' => $this->store_data()->login_store_id );
        $condition = "id = '".$res."' ";
	    $this->db->update("sync_feeds", $dataSet, $condition);

        // activity log data entry
		$cts=time();
		$activitylog_ts = date("Y-m-d h:m:s",$cts);
        $feed_id = $res;
		$activitylog_total_row = 0;
		$activitylog_completed_row = 0;
		$activitylog_run_status = 'I';
		$log_data = array(
			'activitylog_feedid' => $feed_id,
			'activitylog_total_row' => $activitylog_total_row,
			'activitylog_completed_row' => $activitylog_completed_row,
			'activitylog_run_status' => $activitylog_run_status,
			'activitylog_ts' => $activitylog_ts								
		);
		$this->activity_log_entry($feed_id,$log_data);


        return $res;
    }

    public function create_new_feed_metas( $dataSet , $fid_edit )
    {
    	if($fid_edit=='')
    	{
	        $res = $this->db->insert("sync_feed_values", $dataSet);
	        return $res;
	    }
	    else
	    {
	    	$condition = "feed_id = '".$fid_edit."' and meta_key ='feed_connection_param' ";
	    	$this->db->update("sync_feed_values", $dataSet, $condition);
	    }
    }

    public function get_csv_heaer_title($filename)
    {
        
    	if(file_exists($filename)){ 
		    $handle = fopen($filename, "r");
            $row = fgetcsv($handle, 0, ",");
            $jval = json_encode($row);
            
            $this->session->add_session('field_title_list', $jval );		
		}
		else{
			die();
		}
    }

    public function get_csv_heaer_title_by_id($feed_id)
    {

    	$sqlfeed="select * from sync_feeds where id ='".$feed_id."' ";
		$this->db->query($sqlfeed);
		$datafeed = $this->db->singleObj();

    	$datafeed->feed_method_id;

		$sql="select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key = 'feed_connection_param' ";
		$this->db->query($sql);
		$data = $this->db->singleObj();
		$singleObj = json_decode($data->meta_value);

		
		if($datafeed->feed_method_id==1){
			$filename_only = $singleObj->file_name;
			$filename = $_SERVER['DOCUMENT_ROOT'] . '/uploads/uploadfiles/' . $filename_only;
		}
		else{

			$ftp_user = $singleObj->ftp_user;
			$ftp_pwd = $singleObj->ftp_pwd;
			$ftp_host = $singleObj->ftp_host;
			$ftp_dir_path = $singleObj->ftp_dir_path;

			$filename = 'ftp://'.$ftp_user.':'.$ftp_pwd.'@'.$ftp_host.$ftp_dir_path;
		}
        
    	if(file_exists($filename)){ 
			$extension = pathinfo($filename, PATHINFO_EXTENSION);
			
			if ( $extension == 'csv' ) {
				$handle = fopen($filename, "r");
				$row = fgetcsv($handle, 0, ",");
				return $jval = json_encode($row);
			}

			if ( $extension == 'xlsx' ) {
				include $_SERVER['DOCUMENT_ROOT'] . "/library/SimpleXLSX.php";
				if ( $xlsx = SimpleXLSX::parse($filename) ) {
					$jval = $xlsx->rows();
					return json_encode($jval[0]);
				}
			}
		}
		else{
			die();
		}
    }


	public function ftp_estd_connction($data)
	{

		$ftp_user = $data['ftp_username'];
		$ftp_pwd = $data['ftp_password'];
		$ftp_host = $data['ftp_host'];
		$ftp_dir_path = $data['dirfile_path'];

		//$filename = 'ftp://unksac2012x:1VitAcSX@68.169.52.205/www/los.vm-host.net/sapp/shopifyapp/product_template.csv';

		$filename = 'ftp://'.$ftp_user.':'.$ftp_pwd.'@'.$ftp_host.$ftp_dir_path;
		if(file_exists($filename)){

			$table ='connection';
		    $this->db->insert($table,$data);

		}
		else{
			die('File does not exist, please check and try again.');
		}
	}

	public function file_header_fields($feed_id = '')
	{
		if(trim($feed_id)!='')
		{
			$sql="select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key = 'feed_connection_param' ";
			$this->db->query($sql);
			$data = $this->db->singleObj();
			$data = json_decode($data->meta_value);

			$ftp_user = $data->ftp_user;
			$ftp_pwd = $data->ftp_pwd;
			$ftp_host = $data->ftp_host;
			$ftp_dir_path = $data->ftp_dir_path;
			
			$after_process = $data->after_process;
			$file_format = $data->file_format;		

			//$filename = 'ftp://'.$ftp_user.':'.$ftp_pwd.'@'.$ftp_host.$ftp_dir_path;
			
			$filename = $_SERVER['DOCUMENT_ROOT'] . '/uploads' . $ftp_dir_path;

			if(file_exists($filename)){ 
				$handle = fopen($filename, "r");
				$row = fgetcsv($handle, 0, ",");
				$jval = json_encode($row);			
				return $jval;
			} else { die(); }
			
		}
		else
		{
			return false;
		}
		
	}

	public function get_mapping_fields($feed_id = '')
	{
		if(trim($feed_id)!='')
		{
			$sql="select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key = 'feed_product_param' ";
			$this->db->query($sql);
			$data = $this->db->singleObj();
			$data = json_decode($data->meta_value);	
			return $data;	
		}
		else
		{
			return false;
		}
		
	}

	public function get_product_array($feed_id, $field_title_list) {

		$mapping_fields = $this->get_mapping_fields($feed_id);

		$mapping_field_array = array();
		$krt = array();
		foreach ( $mapping_fields as $mapkey => $mapval ) {

			$vkeystrng = strtolower($mapkey);
			$search = 'metafield';
			if(preg_match("/{$search}/i", strtolower($vkeystrng))) {
				$posky = array_search($mapval, $field_title_list);
				if($posky!=''){
					$krt[]=array_search($mapval, $field_title_list);
				}				
			}

			if($mapkey !='metafield'){
		    	$newArr[$mapkey] = array_search($mapval, $field_title_list);
		    }
		    else{		    	
		    	// $newArr[$mapkey] = array_search($mapval->metafield_value, $field_title_list);		    	
		    	$newArr[$mapkey] = implode(",",$krt);		    	
		    }	
		}

		$pre_define = $newArr;
		
		$product_field_string = "body_html,title,collection,images-src,vendor,product_type,handle,tags,variants-sku,variants-title,variants-price,variants-inventory_quantity,variants-barcode,variants-compare_at_price,variants-weight,variants-inventory_policy,variants-taxable,variants-fulfillment_service,variants-inventoryItem-cost,variants-option1,variants-option2,variants-option3";

		$product_field_array = explode(",", $product_field_string);
		$product_field_array_flip = array_flip($product_field_array);
		$diff_val_arr = array_diff($field_title_list, $mapping_field_array);

		$rest_field_list = $diff_val_arr;

		$sku_array = array('SKU', 'variant sku', 'Variant SKU');
		$newArr['variants-sku'] = $this->indexpointing($rest_field_list, $sku_array);

		$title_array = array('title', 'heading', 'Product title', 'product_title');
		$newArr['title'] = $this->indexpointing($rest_field_list, $title_array);

		$price_array = array('price', 'product price', 'price per item', 'inventory item price', 'inventory price', 'inventoryItem price', 'item price', 'variants price', 'variants-price', 'variants_price','Variant Price');
		$newArr['variants-price'] = $this->indexpointing($rest_field_list, $price_array);
		

		$body_html_array = array('Description', 'Desc', 'Body', 'body html', 'body_html', 'Body (HTML)');
		$newArr['body_html'] = $this->indexpointing($rest_field_list, $body_html_array);

		$collection_array = array('collection');
		$newArr['collection'] = $this->indexpointing($rest_field_list, $collection_array);

		$image_array = array('Image', 'img', 'photo', 'img src', 'img_src', 'Main Image', 'images', 'main images','Image Src');
		$newArr['images-src'] = $this->indexpointing($rest_field_list, $image_array);

		$vendor_array = array('vendor', 'vendors');
		$newArr['vendor'] = $this->indexpointing($rest_field_list, $vendor_array);

		$product_type_array = array('Product type', 'Product Category');
		$newArr['product_type'] = $this->indexpointing($rest_field_list, $product_type_array);

		$handle_array = array('handle', 'handles');
		$newArr['handle'] = $this->indexpointing($rest_field_list, $handle_array);

		$tags_array = array('tags', 'tag');
		$newArr['tags'] = $this->indexpointing($rest_field_list, $tags_array);

		$quantity_array = array('quantity', 'quantities', 'Inventory Qty', 'qty',' Inventory quantity', 'Inventory quantities');
		$newArr['variants-inventory_quantity'] = $this->indexpointing($rest_field_list, $quantity_array);

		$barcode_array = array('barcode','barcodes');
		$newArr['variants-barcode'] = $this->indexpointing($rest_field_list, $barcode_array);

		$compare_price_array = array('Compare Price');
		$newArr['variants-compare_at_price'] = $this->indexpointing($rest_field_list, $compare_price_array);

		$weight_array = array('weight');
		$newArr['variants-weight'] = $this->indexpointing($rest_field_list, $weight_array);

		$weight_unit_array = array('weight_unit', 'unit', 'weight unit','Variant Weight Unit');
		$newArr['variants-weight_unit'] = $this->indexpointing($rest_field_list, $weight_unit_array);

		$inventory_policy_array = array('inventory_policy','inventory policy','policy');
		$newArr['variants-inventory_policy'] = $this->indexpointing($rest_field_list, $inventory_policy_array);

		$taxable_array = array('taxable');
		$newArr['variants-taxable'] = $this->indexpointing($rest_field_list, $taxable_array);

		$grams_array = array('gram','grams','Variant Grams','Variant Grams');
		$newArr['variants-grams'] = $this->indexpointing($rest_field_list, $grams_array);

		$fulfillment_array = array('fulfillment_service','fulfillment service');
		$newArr['variants-fulfillment_service'] = $this->indexpointing($rest_field_list, $fulfillment_array);

		$cost_array = array('cost', 'product cost', 'cost per item', 'inventory item cost', 'inventory cost', 'inventoryItem cost', 'item cost');
		$newArr['variants-inventoryItem-cost'] = $this->indexpointing($rest_field_list, $cost_array);

		$final_arr = [];
		foreach ( $newArr as $key => $arr ) {
		    if ( $arr > -1 ) {
		        $final_arr[$key] = $arr;
		    }
		}

		
		$new_rtn = array_merge($pre_define,$final_arr);
				
		// return $final_arr;

		return $new_rtn;
	}

	public function feed_process_start($feed_id) {

		ini_set('memory_limit', '256M'); // Try to override the memory limit for this script

		$is_feed_process_conplete = false;
		
		$sql = "SELECT * FROM sync_feeds WHERE id ='".$feed_id."'";
		$this->db->query($sql);
		$result = $this->db->single();

		$last_count = $result['last_count'];
		$processing = $result['processing'];
		$feed_started = $result['feed_started'];
		$feed_method_id = $result['feed_method_id'];
		$log_filename_db = $result['log_filename'];
		$log_filename_skipped_products_db = $result['log_filename_skipped_products'];

		$sql = "select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key = 'feed_connection_param'";
		$this->db->query($sql);
		$data = $this->db->singleObj();
		$data = json_decode($data->meta_value);

		if($feed_method_id == 2){
			$ftp_user = $data->ftp_user;
			$ftp_pwd = $data->ftp_pwd;
			$ftp_host = $data->ftp_host;
			$ftp_dir_path = $data->ftp_dir_path;
		}

		if($feed_method_id == 1){
			$ftp_dir_path = $data->file_name;
		}

		$text_file_name = $_SERVER['DOCUMENT_ROOT'] . '/uploads/ftpfiles/' .$feed_id . '_'. pathinfo($ftp_dir_path, PATHINFO_FILENAME). '.txt';	
		
		// not working now below two variable
		$after_process = $data->after_process;
		$file_format = $data->file_format;

		$this->db->query("select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key = 'feed_advance_setting'");
		$feed_advance_setting = $this->db->singleObj();
		$feed_advance_setting_data = json_decode($feed_advance_setting->meta_value);

		$auto_publish_product = $feed_advance_setting_data->auto_publish_product;
		$same_image_variant = $feed_advance_setting_data->same_image_variant;
		$first_image_to_all_variant = $feed_advance_setting_data->first_image_to_all_variant;
		$skip_zero_quantity = $feed_advance_setting_data->skip_zero_quantity;
		$new_product_tag = $feed_advance_setting_data->new_product_tag;

		if($auto_publish_product){ $published_status = true; } else { $published_status = false; }

		$processcondition = "id ='".$feed_id."'";
		$process_data = array('is_processed' => 'Y');
		$this->db->update('sync_feeds', $process_data, $processcondition);
		
		$response['success'] = false;
		$response['message'] = '';

		if ($log_filename_db) {
			$log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . $log_filename_db;
			$activity_filename = $log_filename_db;
		} else {
			$log_file = $feed_id . '_' .time() . '_product_list_log.csv';
			$log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . $log_file;
			$activity_filename = $log_file;
		}

		// For skipped products
		if ($log_filename_skipped_products_db) {
			$skipped_product_activity_filename = $log_filename_skipped_products_db;
			$skipped_product_log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . 'activitylogskippedproducts/' . $log_filename_skipped_products_db;
		} else {
			$skipped_product_log_file = $feed_id . '_' .time() . '_skipped_product_list_log.csv';
			$skipped_product_log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . 'activitylogskippedproducts/' . $skipped_product_log_file;
			$skipped_product_activity_filename = $skipped_product_log_file;
		}	

		if($feed_method_id==2){
			$filename_outer = 'ftp://' . $ftp_user . ':' . $ftp_pwd . '@' . $ftp_host . $ftp_dir_path;
		}
		else{
			$filename_outer = $_SERVER['DOCUMENT_ROOT'] . '/uploads/uploadfiles/' . $ftp_dir_path;
		}
		
		$filehandle_outer = fopen($filename_outer, "r");
		$field_title_list_outer = fgetcsv($filehandle_outer, 0, ",");
		fclose($filehandle_outer);

		// Create Mmpping array
		$parr_outer = $this->get_product_array($feed_id, $field_title_list_outer);
		// print_r($parr_outer);

		if ( $feed_started == 0 ) {
			$pau_cont = 1; // Prodduct add or update counter initilization 

			if($feed_method_id==2){
				$filename = 'ftp://' . $ftp_user . ':' . $ftp_pwd . '@' . $ftp_host . $ftp_dir_path;
			}
			else{
				$filename = $_SERVER['DOCUMENT_ROOT'] . '/uploads/uploadfiles/' . $ftp_dir_path;
			}

			$meta_value_fetch = $this->get_mapping_fields($feed_id);
			
			if ( file_exists($filename) ) {
				$arr = [];
				$filehandle1 = fopen($filename, "r");
				$field_title_list = fgetcsv($filehandle1, 0, ",");
				fclose($filehandle1);

				// Create Mmpping array
				$parr = $this->get_product_array($feed_id, $field_title_list);
				
				$filehandle2 = fopen($filename, "r"); // read file from FTP

				// Skip first row if first row is header
				if ( $data->first_row_is_header == 1 ) {
					$row = fgetcsv($filehandle2, 0, ",");
				}
				$ik=1;
				while ( $row = fgetcsv($filehandle2, 0, ",") ) { 
					
					if(trim($row[$parr['title']])){ $title = trim($row[$parr['title']]); } else { $title = ''; }
					if(trim($row[$parr['body_html']])){ $body_html = trim($row[$parr['body_html']]); } else { $body_html = ''; }
					if(trim($row[$parr['vendor']])){ $vendor = trim($row[$parr['vendor']]); } else { $vendor = ''; }
					if(trim($row[$parr['product_type']])){ $product_type = trim($row[$parr['product_type']]); } else { $product_type = ''; }
					if(trim($row[$parr['handle']])){ $handle = trim($row[$parr['handle']]); } else { $handle = ''; }
					if(trim($row[$parr['published']])){ $published = trim($row[$parr['published']]); } else { $published = true; }
					if(trim($row[$parr['variants-sku']])){ $sku = trim($row[$parr['variants-sku']]); } else { $sku = ''; }
					if(trim($row[$parr['variants-weight']])){ $weight = trim($row[$parr['variants-weight']]); } else { $weight = ''; }
					if(trim($row[$parr['variants-weight_unit']])){ $weight_unit = trim($row[$parr['variants-weight_unit']]); } else { $weight_unit = ''; }
					if(trim($row[$parr['variants-inventory_quantity']])){ $inventory_quantity = trim($row[$parr['variants-inventory_quantity']]); } else { $inventory_quantity = ''; }
					if(trim($row[$parr['variants-price']])){ $price = trim($row[$parr['variants-price']]); } else { $price = ''; }
					if(trim($row[$parr['variants-inventoryItem-cost']])){ $cost = trim($row[$parr['variants-inventoryItem-cost']]); } else { $cost = ''; }					
					if(trim($row[$parr['variants-taxable']])){ $taxable = trim($row[$parr['variants-taxable']]); } else { $taxable = true; }
					if(trim($row[$parr['variants-required_shipping']])){ $required_shipping = trim($row[$parr['variants-required_shipping']]); } else { $required_shipping = true; }

					if(trim($row[$parr['images-src']])){ $img_src = trim($row[$parr['images-src']]); } else { $img_src = ''; }
					
					if(trim($row[$parr['variants-grams']])){ $grams = trim($row[$parr['variants-grams']]); } else { $grams = ''; }
					
					if(trim($row[$parr['tags']])){ $_tags = trim($row[$parr['tags']]); } else { $_tags = ''; }

					$metafield_array = array();

					if(trim($parr['metafield'])!=''){ 
						$metafield_list = explode(",",trim($parr['metafield'])); 

						$cm = 0;						
						foreach($meta_value_fetch->metafield as $each_matafield_set)
						{
							$pos_key = array_search($each_matafield_set->metafield_value, $field_title_list);

							$metafield_value = $row[$pos_key];
							$metafield_array[] = array(
								"metafield" => array(
									"namespace" => $each_matafield_set->metafield_namespace,
									"key" => $each_matafield_set->metafield_key,
									"value" => $metafield_value,		
									"value_type" => $each_matafield_set->meta_val_type,
									"metafield_owner" => $each_matafield_set->metafield_owner
								)
							);

							$cm++;
						}

					}

					$valid_weight_unit = array('kg','oz','lb','g');

					$unit_string = strtolower($weight_unit);
					$unit ='';
					foreach($valid_weight_unit as $each_unit)
					{
					  $pos = strpos($unit_string,$each_unit);
					  if($pos>0){
					    $unit = $each_unit;
					    break;
					  }
					}


					$vrnt_s_arr = array();
					if(trim($sku)!=''){ $vrnt_s_arr["sku"] = $sku; }
					if(trim($price)!=''){ $vrnt_s_arr["price"] = $price; }
					if(trim($taxable)!=''){ $vrnt_s_arr["taxable"] = $taxable; }
					if(trim($required_shipping)!=''){ $vrnt_s_arr["required_shipping"] = $required_shipping; }
					if(trim($inventory_quantity)!=''){ $vrnt_s_arr["inventory_quantity"] = $inventory_quantity; }
					if(trim($grams)!=''){ $vrnt_s_arr["grams"] = $grams; }
					if(trim($weight)!=''){ $vrnt_s_arr["weight"] = $weight; }
					if(trim($unit)!=''){ $vrnt_s_arr["weight_unit"] = $unit; }
					if(trim($cost)!=''){ $vrnt_s_arr["cost"] = $cost; }

					$variant_array = array($vrnt_s_arr);

					$products_array = array(
						"product" => array(
							"title" => $title,
							"body_html" => $body_html,
							"vendor" => $vendor,
							"product_type" => $product_type,
							"handle" => $handle,
							"published" => $published,
							"variants" =>$variant_array,
							"images" => array(
									array(
										"src" => $img_src,					
									)
								),
							"tags" => $_tags ? ($_tags . ', ' . $new_product_tag ) : $new_product_tag,							
							"metafield" => $metafield_array,
							"published" => $published_status
						)
					);
					$arr[] = $products_array;
							
					$ik++;
				}
			}

			// Create txt file with serialzed single array of all produtcs
			file_put_contents($text_file_name, serialize($arr));

			//update sync_feeds table 'processing' status
			$condition = "id ='".$feed_id."'";
			$feed_data = array('feed_started' => 1);
			$this->db->update('sync_feeds', $feed_data, $condition);

			//Creates log file and header
			$log_file_handler = fopen($log_file_name, "w");
			$skipped_products_log_file_handler = fopen($skipped_product_log_file_name, "w");
			$titles = [];
			foreach ($parr as $key => $value) {
				if (isset($field_title_list[$value]) && $key!='metafield' ) {
					$titles[] = $field_title_list[$value];
				}
			}
			$csv_headers = array (
				$titles
			);
			foreach ($csv_headers as $csv_header) {
			  fputcsv($log_file_handler, $csv_header);
			  fputcsv($skipped_products_log_file_handler, $csv_header);
			}
			fclose($log_file_handler);
			fclose($skipped_products_log_file_handler);

			fclose($filehandle2);
		}

		$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
		$data = file_get_contents( $text_file_name, false, $context );

		$step_entry = 100;

		if ( $data ) { 
			$arr = unserialize( $data );
			$items = array_slice($arr, $last_count, $step_entry);

			if ( !$processing ) { 
				if ( $last_count < count($arr) ) {  
					// update sync_feeds table 'processing' status
					
					$condition = "id ='".$feed_id."'";
					$feed_data = array('processing' => 1);
					$this->db->update('sync_feeds', $feed_data, $condition);

					$shop = $this->store_data($feed_id)->login_store_id;
					$count = 0;
					$skip_count = 0;
					$alo = false;
					foreach ( $items as $key => $item ) {		
						$sku = 	$item['product']['variants'][0]['sku'];	
						$node = $this->search_by_sku($sku,$feed_id);	
						if(!empty($node))
						{
							$skip_count++;
							$pau_cont++;
							$is_skip = true;
							$alo = true;
							//Creates log file for existing items
							$this->product_log($skipped_product_log_file_name, $item, $parr_outer);
						}
						else
						{
							$is_skip = false;							

							$product = $item['product'];
							$metafieldraw = $product['metafield'];
							
							$will_create = false;

							$inventory_quantity = $item['product']['variants'][0]['inventory_quantity'];
							if($skip_zero_quantity){
								if($inventory_quantity>0){ 
									$will_create = true; 
								}
							}
							else {
								$will_create = true;							
							}

							if($will_create)
							{
								if(!$is_skip)
								{
									$crt_rsp = $this->create_product_api(conf::APP_API_KEY, $this->store_data($feed_id)->login_store_token, $this->store_data($feed_id)->login_store_url, $item);
									$crt_rsp_output = json_decode($crt_rsp['response'], JSON_PRETTY_PRINT);							
									$last_inserted_product_id = $crt_rsp_output['product']['id'];

									if(!empty($metafieldraw)){

										foreach ($metafieldraw as $eachmetafield) {
											$metafield = $eachmetafield['metafield'];
											$meta_arr = array(
												"metafield" => array(
													"namespace" => $metafield['namespace'],
													"key" => $metafield['key'],
													"value" => $metafield['value'],
													"value_type" => $metafield['value_type']
												)
											);

											$productMeta = $this->shopify_call($this->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-07/products/".$last_inserted_product_id."/metafields.json", $meta_arr, 'POST');
											$productMetaPrint = json_decode($productMeta['response'], JSON_PRETTY_PRINT);
											

										}
									}
									$count++;
								}

							}
						}

						$activitylog_total_row = count($arr);
						$activitylog_completed_row = $last_count + $count;								
						$completed_row = $activitylog_completed_row + $skip_count;

						if( $completed_row >= $activitylog_total_row)
						{
							$activitylog_run_status = 'C';
							$activitylog_file_name = $log_filename_db ? $log_filename_db : $log_file;
							$activitylog_skipped_file_name = $skipped_product_activity_filename;

							$processcondition = "id ='".$feed_id."'";
							$process_data = array('is_processed' => 'N', 'last_count'=>0, 'feed_started'=>0);
							$this->db->update('sync_feeds', $process_data, $processcondition);
							$is_feed_process_conplete = true;							

							if($alo)
							{
								$log_data = array(
									'activitylog_feedid' => $feed_id,
									'activitylog_total_row' => $activitylog_total_row,
									'activitylog_run_status' => 'C',
									'activitylog_file' => $activitylog_file_name,
									'activitylog_skipped_file' => $activitylog_skipped_file_name
								);
								$this->activity_log_entry($feed_id,$log_data);
							}
						}
						else
						{
							$activitylog_run_status = 'R';
							$activitylog_file_name = null;
							$activitylog_skipped_file_name = null;
						}
						
						if(!$is_skip)
						{
							$last_activity_count = $this->last_activity_count($feed_id);
							$new_activity_count = $last_activity_count + 1;

							$cts=time();
							$activitylog_ts = date("Y-m-d h:m:s",$cts);
							
							$log_data = array(
								'activitylog_feedid' => $feed_id,
								'activitylog_total_row' => $activitylog_total_row,
								'activitylog_completed_row' => $new_activity_count,
								'activitylog_run_status' => $activitylog_run_status,
								'activitylog_file' => $activitylog_file_name,
								'activitylog_skipped_file' => $activitylog_skipped_file_name,
								'activitylog_ts' => $activitylog_ts
							);
							$this->activity_log_entry($feed_id,$log_data);
							$this->product_log($log_file_name, $item, $parr_outer);
						}
						// $count++;
						$pau_cont++;						
					}

					// update sync_feeds table 'processing' status
					// update sync_feeds table 'last_count'

					if($is_feed_process_conplete){ 
						$nw_last_count = 0;
						$log_filename_for_db = null;
						$skipped_products_log_filename_for_db = null;
					}
					else{
						$log_filename_for_db = $activity_filename;
						$nw_last_count = $last_count + $count + $skip_count;
						$skipped_products_log_filename_for_db = $skipped_product_activity_filename;
					}

					$condition = "id ='".$feed_id."'";
					$feed_data = array('processing' => 0, 'last_count' => ($nw_last_count), 'log_filename' => $log_filename_for_db, 'log_filename_skipped_products' => $skipped_products_log_filename_for_db);
					$this->db->update('sync_feeds', $feed_data, $condition);
					
					$response['success'] = true;
                    $response['message'] = 'Feed is being processed! '  . 'Total feeds: ' . count($arr) . ' Feed processed: ' . ($last_count + $count);
				} else {
					$processcondition = "id ='".$feed_id."'";
					$process_data = array('is_processed' => 'N', 'last_count'=>0, 'feed_started'=>0);
					$this->db->update('sync_feeds', $process_data, $processcondition);
					// update log table set 'C'					
					$response['message'] = 'Feed is already processed!';
				}
			} else {
				$response['message'] = 'Feed is under process!';
			}
		} else {
			$response['message'] = 'Feed doesn\'t exists!';
		}
		return $response;
	}

	public function store_data($feed_id = '')
	{
		$app_auth = new app_auth();
		$auth_store = $this->session->get_session_by_key('_authstore_id');
		if (isset($feed_id) && !empty($feed_id)) {
			$sql = "SELECT * FROM sync_feeds WHERE id ='" . $feed_id . "'";
			$this->db->query($sql);
			$res1 = $this->db->singleObj();
			$login_store_id = $res1->login_store_id;

			$sql = "SELECT * FROM store_login WHERE login_store_id ='" . $login_store_id . "'";
			$this->db->query($sql);
			$res2 = $this->db->singleObj();
			$auth_store = $res2->login_store_url;
		}
		return $app_auth->store_data($auth_store);
	}


	public function product_log($log_file_name, $item, $parr) {
		// echo "<pre>";
		// print_r($log_filename);
		// print_r($item);
		// print_r($parr);
		$parray = array_keys($parr);

		$all_types = array (
			'body_html',
			'title',
			'collection',
			'images-src',
			'vendor',
			'product_type',
			'handle',
			'tags',
			'metafield',
			'variants-sku',
			'variants-title',
			'variants-price',
			'variants-inventory_quantity',
			'variants-barcode',
			'variants-compare_at_price',
			'variants-weight',
			'variants-weight_unit',
			'variants-inventory_policy',
			'variants-taxable',
			'variants-fulfillment_service',
			'variants-inventoryItem-cost',
			'variants-option1',
			'variants-option2',
			'variants-option3',
		);

		$filtered_types = array_intersect($parray, $all_types);

		foreach ($filtered_types as $key => $value) {
       		switch ($value) {
	            case 'body_html':
	               $final_list[] = $item['product']['body_html'];
	                break;
	            case 'title':
	                $final_list[] = $item['product']['title'];
	                break;
	            case 'collection':
	                $final_list[] = $item['product']['collection'];
	                break;
	            case 'images-src':
	                $final_list[] = $item['product']['images'][0]['src'];
	                break;
	            case 'vendor':
	                $final_list[] = $item['product']['vendor'];
	                break;
	            case 'product_type':
	                $final_list[] = $item['product']['product_type'];
	                break;
	            case 'handle':
	                $final_list[] = $item['product']['handle'];
	                break;
	            case 'tags':
	                $final_list[] = $item['product']['tags'];
	                break;
	            case 'metafield':
	                foreach ($item['product']['metafield'] as $key => $value) {
	                    $final_list[] = $value['metafield']['value'];
	                }
	                break;
	            case 'variants-sku':
	                $final_list[] = $item['product']['variants'][0]['sku'];
	                break;
	            case 'variants-title':
	                $final_list[] = $item['product']['variants'][0]['title'];
	                break;
	            case 'variants-price':
	                $final_list[] = $item['product']['variants'][0]['price'];
	                break;
	            case 'variants-inventory_quantity':
	                $final_list[] = $item['product']['variants'][0]['inventory_quantity'];
	                break;
	            case 'variants-barcode':
	                $final_list[] = $item['product']['variants'][0]['barcode'];
	                break;
	            case 'variants-compare_at_price':
	                $final_list[] = $item['product']['variants'][0]['compare_at_price'];
	                break;
	            case 'variants-weight':
	                $final_list[] = $item['product']['variants'][0]['weight'];
	                break;
	            case 'variants-weight_unit':
	                $final_list[] = $item['product']['variants'][0]['weight_unit'];
	                break;
	            case 'variants-inventory_policy':
	                $final_list[] = $item['product']['variants'][0]['inventory_policy'];
	                break;
	            case 'variants-taxable':
	                $final_list[] = $item['product']['variants'][0]['taxable'];
	                break;
	            case 'variants-fulfillment_service':
	                $final_list[] = $item['product']['variants'][0]['fulfillment_service'];
	                break;
	            case 'variants-inventoryItem-cost':
	                $final_list[] = $item['product']['variants'][0]['cost'];
	                break;
	            case 'variants-option1':
	                $final_list[] = $item['product']['variants'][0]['option1'];
	                break;
	            case 'variants-option2':
	                $final_list[] = $item['product']['variants'][0]['option2'];
	                break;
	            case 'variants-option3':
	                $final_list[] = $item['product']['variants'][0]['option3'];
	                break;
	            default:
	                break;
       		}
    	}

		$list = array (
			$final_list
		);

		$log_file_handler = fopen($log_file_name, "a");
		foreach ($list as $line) {
		  fputcsv($log_file_handler, $line);
		}
		fclose($log_file_handler);
	}

	public function feed_delete_process($feed_data,$feed_id)
	{
		$condition = "id ='".$feed_id."'";
		$this->db->update('sync_feeds',$feed_data,$condition);
	}

	public function update_feed_title($feed_data,$feed_id)
	{
		$condition = "id ='".$feed_id."'";
		$this->db->update('sync_feeds',$feed_data,$condition);
	}

	public function feed_mapping_fields($build_arr, $edit_fid)
	{
		if($edit_fid==''){
			$feed_id = $this->session->get_session_by_key('feed_id');
		}
		else{
			$feed_id = $edit_fid;
		}

		$check_sql = "select * from sync_feed_values where meta_key ='feed_product_param' and feed_id ='".$feed_id."'";
		$check_qry = $this->db->query($check_sql);
		$check_obj = $this->db->singleObj($check_qry);
		
		$metavalue = json_encode($build_arr);
		$data = array(
			'feed_id' => $feed_id,
			'meta_key' => 'feed_product_param',
			'meta_value' => $metavalue
		);
		if($check_obj->id!='')
		{
			$condition = "feed_id='".$edit_fid."' and meta_key='feed_product_param'";
			$this->db->update('sync_feed_values',$data, $condition);			
		}
		else
		{
			$this->db->insert('sync_feed_values',$data);
		}
	}

	public function create_feed_advance_setteng($feed_meta,$meta_key,$fid_edit)
	{
		if($fid_edit==''){
			$feed_id = $this->session->get_session_by_key('feed_id');
		}
		else{
			$feed_id = $fid_edit;
		}

		// $connection_method = ($this->session->exists('connection_method')) ? $this->session->get_session_by_key('connection_method') : '';

		$check_sql = "select * from sync_feed_values where ( meta_key ='feed_advance_setting' and feed_id ='".$feed_id."' ) or ( meta_key ='feed_update_advance_setting' and feed_id ='".$feed_id."' )";
		$check_qry = $this->db->query($check_sql);
		$check_obj = $this->db->singleObj($check_qry);


		$metavalue = json_encode($feed_meta);
		$data = array(
			'feed_id' => $feed_id,
			'meta_key' => $meta_key,
			'meta_value' => $metavalue
		);

		if($check_obj->id!='')
		{
			$condition = "feed_id = '".$feed_id."' and meta_key ='".$meta_key."' ";
			$this->db->update('sync_feed_values',$data,$condition);		
		}
		else
		{
			$this->db->insert('sync_feed_values',$data);
		}


		$feeddata = array(
			'is_completed' => 'Y'
		);
		$condition_feed = " id ='".$feed_id."'";
		$this->db->update('sync_feeds', $feeddata, $condition_feed);

		$file_with_path  = $this->get_file_only($feed_id);

		$extension = pathinfo($file_with_path, PATHINFO_EXTENSION);

		$ct = 0;
		if ( $extension == 'csv' ) {
			$filehandle = fopen($file_with_path, "r");
			while ( $row = fgetcsv($filehandle, 0, ",") ) { 
				$ct++;
			}
			fclose($filehandle);
		}

		if ( $extension == 'xlsx' ) {
			include $_SERVER['DOCUMENT_ROOT'] . "/library/SimpleXLSX.php";
			if ( $xlsx = SimpleXLSX::parse($file_with_path) ) {
				$jval = $xlsx->rows();
				$ct  = count($jval);
			}
		}

		$rowcount = $ct;

		// $fp = file($file_with_path, FILE_SKIP_EMPTY_LINES);
		// $rowcount = count($fp);
		// $rowcount = substr_count(file_get_contents($file_with_path), "\r\n");

		// activity log data entry

		$cts=time();
		$activitylog_ts = date("Y-m-d h:m:s",$cts);

		$activitylog_total_row = $rowcount-1;
		$activitylog_completed_row = 0;
		$activitylog_run_status = 'P';
		$log_data = array(
			'activitylog_feedid' => $feed_id,
			'activitylog_total_row' => $activitylog_total_row,
			'activitylog_completed_row' => $activitylog_completed_row,
			'activitylog_run_status' => $activitylog_run_status,
			'activitylog_ts' => $activitylog_ts								
		);
		$this->activity_log_entry($feed_id,$log_data);

		
	}

	public function current_feed($feed_id)
	{
		$this->db->query("
select f.*,ft.feed_title, fm.title as connection_title from sync_feeds as f 
INNER JOIN sync_feed_types as ft ON ft.id = f.feed_type_id
INNER JOIN sync_feed_method as fm ON fm.id = f.feed_method_id 
where f.id = '".$feed_id."' and  f.status = 'Y' 
");
        $res = $this->db->singleObj();
        return $res;
	}

	public function create_schedule_feed($feed_meta)
	{
		$schedule_status = $feed_meta['schedule_status'];
		$schedule_frequency = $feed_meta['schedule_frequency'];
		$schedule_time = $feed_meta['schedule_time'];
		$feed_id = $feed_meta['schedule_popup_id'];
		
		// update feed table schedule status
		$condition = "id ='".$feed_id."'";
		$feed_data = array('is_scheduled' => $schedule_status);
		$this->db->update('sync_feeds',$feed_data,$condition);

		// Insert / Update feed schedule table
		$sql = "INSERT INTO sync_feed_schedule (`schedule_feed_id`,`schedule_frequency`,`schedule_time`,`schedule_status`) 
		VALUES ('$feed_id','$schedule_frequency','$schedule_time','$schedule_status') 
		ON DUPLICATE KEY UPDATE 
		schedule_feed_id = '".$feed_id."', 
		schedule_frequency = '".$schedule_frequency."', 
		schedule_time = '".$schedule_time."', 
		schedule_status = '".$schedule_status."' ";
		$this->db->query($sql);
		$this->db->execute();

	}
	public function get_schedule_details($feed_id)
	{
		$sql="select * from sync_feed_schedule where schedule_feed_id = '".$feed_id."'";
		$this->db->query($sql);
		$result = $this->db->singleObj();
		if($result){ return $result; }
		else { return false; }        
	}

	public function indexpointing($rest_field_list = '', $string_array = '')
	{
		array_walk($rest_field_list, function(&$value){  $value = trim(strtolower($value)); });
		array_walk($string_array, function(&$value){  $value = trim(strtolower($value)); });
		$keyval = array_keys(array_unique(array_intersect($rest_field_list, $string_array)));
		return $keyval[0];    
	}

	public function create_product_api($user,$password,$store,$array)
	{
		// $url = "https://".$user.":".$password."@".$store."/admin/products.json";
		$url = "https://".$user.":".$password."@".$store."/admin/api/2020-04/products.json";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($array));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		

		// $response = curl_exec ($curl);
		// curl_close ($curl);
		

		// -------------------------------------

		// Send request to Shopify and capture any errors
		$response = curl_exec($curl);
		$error_number = curl_errno($curl);
		$error_message = curl_error($curl);
	
		curl_close($curl);
	
		// Return an error is cURL has a problem
		if ($error_number) {
			return $error_message;
		} else {
	
			// No error, return Shopify's response by parsing out the body and the headers
			$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
	
			// Convert headers into an array
			$headers = array();
			$header_data = explode("\n",$response[0]);
			$headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
			array_shift($header_data); // Remove status, we've already set it above
			foreach($header_data as $part) {
				$h = explode(":", $part);
				$headers[trim($h[0])] = trim($h[1]);
			}
	
			// Return headers and Shopify's response
			return array('headers' => $headers, 'response' => $response[1]);
	
		}

		// -------------------------------------

	}

	public function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) 
	{
    
		// Build URL
		$url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
		if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);
	
		// Configure cURL
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
		// curl_setopt($curl, CURLOPT_SSLVERSION, 3);
		curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	
		// Setup headers
		$request_headers[] = "";
		if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
	
		if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
			if (is_array($query)) $query = http_build_query($query);
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
		}
		
		// Send request to Shopify and capture any errors
		$response = curl_exec($curl);
		$error_number = curl_errno($curl);
		$error_message = curl_error($curl);
	
		// Close cURL to be nice
		curl_close($curl);
	
		// Return an error is cURL has a problem
		if ($error_number) {
			return $error_message;
		} else {
	
			// No error, return Shopify's response by parsing out the body and the headers
			$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
	
			// Convert headers into an array
			$headers = array();
			$header_data = explode("\n",$response[0]);
			$headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
			array_shift($header_data); // Remove status, we've already set it above
			foreach($header_data as $part) {
				$h = explode(":", $part);
				$headers[trim($h[0])] = trim($h[1]);
			}
	
			// Return headers and Shopify's response
			return array('headers' => $headers, 'response' => $response[1]);
	
		}
		
	}

	public function graphql($sql)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://infotechsolz-store-app.myshopify.com/admin/api/graphql.json",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 300,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $sql,
			CURLOPT_COOKIE => "__cfduid=d1d63d68a882ee406d361c29ae91415041591959734; request_method=POST",
			CURLOPT_HTTPHEADER => array(
				"content-type: application/json",
				"x-shopify-access-token: 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"
			),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		//if ($err) {	echo "cURL Error #:" . $err; } else { echo $response; }
		if ($err) {	return "cURL Error #:" . $err; } else { return $response; }
	}

	
	public function FloatVal($str) {
		if(preg_match("#([0-9\.]+)#", $str, $match)) { // search for number that may contain '.'
		  return floatval($match[0]);
		} else {
		  return floatval($str); // take some last chances with floatval
		}
	}

	public function goFormat($str)
	{
		return $str = str_replace('"', "''", $str);
		// return $str;
	}

	
	public function feed_update_start($feed_id) {
		ini_set('memory_limit', '256M'); 

		$is_feed_process_conplete = false;
		
		$sql = "SELECT * FROM sync_feeds WHERE id ='".$feed_id."'";
		$this->db->query($sql);
		$result = $this->db->single();

		$last_count = $result['last_count'];
		$processing = $result['processing'];
		$feed_started = $result['feed_started'];
		$feed_method_id = $result['feed_method_id'];
		$log_filename_db = $result['log_filename'];
		$log_filename_skipped_products_db = $result['log_filename_skipped_products'];

		$sql = "select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key = 'feed_connection_param'";
		$this->db->query($sql);
		$data = $this->db->singleObj();
		$data = json_decode($data->meta_value);

		if($feed_method_id == 2){
			$ftp_user = $data->ftp_user;
			$ftp_pwd = $data->ftp_pwd;
			$ftp_host = $data->ftp_host;
			$ftp_dir_path = $data->ftp_dir_path;
		}

		if($feed_method_id == 1){
			$ftp_dir_path = $data->file_name;
		}

		$text_file_name = $_SERVER['DOCUMENT_ROOT'] . '/uploads/ftpfiles/' .$feed_id . '_'. pathinfo($ftp_dir_path, PATHINFO_FILENAME). '.txt';	
		$after_process = $data->after_process;
		$file_format = $data->file_format;


		$this->db->query("select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key = 'feed_advance_setting'");
		$feed_advance_setting = $this->db->singleObj();
		$feed_advance_setting_data = json_decode($feed_advance_setting->meta_value);

		$auto_publish_product = $feed_advance_setting_data->auto_publish_product;
		$same_image_variant = $feed_advance_setting_data->same_image_variant;
		$first_image_to_all_variant = $feed_advance_setting_data->first_image_to_all_variant;
		$skip_zero_quantity = $feed_advance_setting_data->skip_zero_quantity;
		$new_product_tag = $feed_advance_setting_data->new_product_tag;

		if($auto_publish_product){ $published_status = true; } else { $published_status = false; }

		$processcondition = "id ='".$feed_id."'";
		$process_data = array('is_processed' => 'Y');
		$this->db->update('sync_feeds', $process_data, $processcondition);
		
		$response['success'] = false;
		$response['message'] = '';

		if ($log_filename_db) {
			$activity_filename = $log_filename_db;
			$log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . $log_filename_db;
		} else {
			$log_file = $feed_id . '_' .time() . '_product_list_log_update.csv';
			$log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . $log_file;
			$activity_filename = $log_file;
		}

		// For skipped products
		if ($log_filename_skipped_products_db) {
			$skipped_product_activity_filename = $log_filename_skipped_products_db;
			$skipped_product_log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . 'activitylogskippedproducts/' . $log_filename_skipped_products_db;
		} else {
			$skipped_product_log_file = $feed_id . '_' .time() . '_skipped_product_list_log_update.csv';
			$skipped_product_log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . 'activitylogskippedproducts/' . $skipped_product_log_file;
			$skipped_product_activity_filename = $skipped_product_log_file;
		}		

		if ($feed_method_id==2) {
			$filename_outer = 'ftp://' . $ftp_user . ':' . $ftp_pwd . '@' . $ftp_host . $ftp_dir_path;
		} else {
			$filename_outer = $_SERVER['DOCUMENT_ROOT'] . '/uploads/uploadfiles/' . $ftp_dir_path;
		}

		$filehandle_outer = fopen($filename_outer, "r");
		$field_title_list_outer = fgetcsv($filehandle_outer, 0, ",");
		fclose($filehandle_outer);

		// Create Mmpping array
		$parr_outer = $this->get_product_array($feed_id, $field_title_list_outer);
		
		if ( $feed_started == 0 ) {
			$pau_cont = 1; // Prodduct add or update counter initilization 
			
			if ($feed_method_id==2) {
				$filename = 'ftp://' . $ftp_user . ':' . $ftp_pwd . '@' . $ftp_host . $ftp_dir_path;
			} else {
				$filename = $_SERVER['DOCUMENT_ROOT'] . '/uploads/uploadfiles/' . $ftp_dir_path;
			}

			$meta_value_fetch = $this->get_mapping_fields($feed_id);
			
			if ( file_exists($filename) ) {
				$arr = [];
				$filehandle1 = fopen($filename, "r");
				$field_title_list = fgetcsv($filehandle1, 0, ",");
				fclose($filehandle1);

				// Create Mmpping array
				$parr = $this->get_product_array($feed_id, $field_title_list);

				$filehandle2 = fopen($filename, "r"); // read file from FTP

				// Skip first row if first row is header
				if ( $data->first_row_is_header == 1 ) {
					$row = fgetcsv($filehandle2, 0, ",");
				}
				$ik=1;
				while ( $row = fgetcsv($filehandle2, 0, ",") ) { 
					if(trim($row[$parr['title']])){ $title = trim($row[$parr['title']]); } else { $title = ''; }
					if(trim($row[$parr['body_html']])){ $body_html = trim($row[$parr['body_html']]); } else { $body_html = ''; }
					if(trim($row[$parr['vendor']])){ $vendor = trim($row[$parr['vendor']]); } else { $vendor = ''; }
					if(trim($row[$parr['product_type']])){ $product_type = trim($row[$parr['product_type']]); } else { $product_type = ''; }
					if(trim($row[$parr['handle']])){ $handle = trim($row[$parr['handle']]); } else { $handle = ''; }
					if(trim($row[$parr['published']])){ $published = trim($row[$parr['published']]); } else { $published = true; }
					if(trim($row[$parr['variants-sku']])){ $sku = trim($row[$parr['variants-sku']]); } else { $sku = ''; }
					if(trim($row[$parr['variants-weight']])){ $weight = trim($row[$parr['variants-weight']]); } else { $weight = ''; }
					if(trim($row[$parr['variants-weight_unit']])){ $weight_unit = trim($row[$parr['variants-weight_unit']]); } else { $weight_unit = ''; }
					if(trim($row[$parr['variants-inventory_quantity']])){ $inventory_quantity = trim($row[$parr['variants-inventory_quantity']]); } else { $inventory_quantity = ''; }
					if(trim($row[$parr['variants-price']])){ $price = trim($row[$parr['variants-price']]); } else { $price = ''; }
					if(trim($row[$parr['variants-inventoryItem-cost']])){ $cost = trim($row[$parr['variants-inventoryItem-cost']]); } else { $cost = ''; }					
					if(trim($row[$parr['variants-taxable']])){ $taxable = trim($row[$parr['variants-taxable']]); } else { $taxable = true; }
					if(trim($row[$parr['variants-required_shipping']])){ $required_shipping = trim($row[$parr['variants-required_shipping']]); } else { $required_shipping = true; }
					if(trim($row[$parr['images-src']])){ $img_src = trim($row[$parr['images-src']]); } else { $img_src = ''; }

					if(trim($row[$parr['variants-grams']])){ $grams = trim($row[$parr['variants-grams']]); } else { $grams = ''; }

					if(trim($row[$parr['tags']])){ $_tags = trim($row[$parr['tags']]); } else { $_tags = ''; }

					$metafield_array = array();

					if(trim($parr['metafield'])!=''){ 
						$metafield_list = explode(",",trim($parr['metafield'])); 

						$cm = 0;						
						foreach($meta_value_fetch->metafield as $each_matafield_set)
						{
							$pos_key = array_search($each_matafield_set->metafield_value, $field_title_list);

							$metafield_value = $row[$pos_key];
							$metafield_array[] = array(
								"metafield" => array(
									"namespace" => $each_matafield_set->metafield_namespace,
									"key" => $each_matafield_set->metafield_key,
									"value" => $metafield_value,		
									"value_type" => $each_matafield_set->meta_val_type,
									"metafield_owner" => $each_matafield_set->metafield_owner
								)
							);

							$cm++;
						}

					}

					// $variant_array = array(
					// 	array(
					// 		"sku" => $sku,
					// 		"price" => $price,
					// 		"taxable" => $taxable,
					// 		"required_shipping" => $required_shipping,
					// 		"inventory_quantity" => $inventory_quantity,
					// 		"cost" => $cost
					// 	)
					// );


					$valid_weight_unit = array('kg','oz','lb','g');

					$unit_string = strtolower($weight_unit);
					$unit ='';
					foreach($valid_weight_unit as $each_unit)
					{
					  $pos = strpos($unit_string,$each_unit);
					  if($pos>0){
					    $unit = $each_unit;
					    break;
					  }
					}


					$vrnt_s_arr = array();
					if(trim($sku)!=''){ $vrnt_s_arr["sku"] = $sku; }
					if(trim($price)!=''){ $vrnt_s_arr["price"] = $price; }
					if(trim($taxable)!=''){ $vrnt_s_arr["taxable"] = $taxable; }
					if(trim($required_shipping)!=''){ $vrnt_s_arr["required_shipping"] = $required_shipping; }
					if(trim($inventory_quantity)!=''){ $vrnt_s_arr["inventory_quantity"] = $inventory_quantity; }
					if(trim($grams)!=''){ $vrnt_s_arr["grams"] = $grams; }
					if(trim($weight)!=''){ $vrnt_s_arr["weight"] = $weight; }
					if(trim($unit)!=''){ $vrnt_s_arr["weight_unit"] = $unit; }
					if(trim($cost)!=''){ $vrnt_s_arr["cost"] = $cost; }

					$variant_array = array($vrnt_s_arr);

					$products_array = array(
						"product" => array(
							"title" => $title,
							"body_html" => $body_html,
							"vendor" => $vendor,
							"product_type" => $product_type,
							"handle" => $handle,
							"published" => $published,
							"variants" =>$variant_array,
							"images" => array(
									array(
										"src" => $img_src,					
									)
								),
							"tags" => $_tags ? ($_tags . ', ' . $new_product_tag ) : $new_product_tag,
							"metafield" => $metafield_array,
							"published" => $published_status

						)
					);
					$arr[] = $products_array;
					// if($ik==3)
					// {
					// 	break;
					// }		
					$ik++;
				}
			}

			// Create txt file with serialzed single array of all produtcs
			file_put_contents($text_file_name, serialize($arr));

			//update sync_feeds table 'processing' status
			$condition = "id ='".$feed_id."'";
			$feed_data = array('feed_started' => 1);
			$this->db->update('sync_feeds', $feed_data, $condition);

			//Creates log file and header
			$log_file_handler = fopen($log_file_name, "w");
			$skipped_products_log_file_handler = fopen($skipped_product_log_file_name, "w");
			$titles = [];
			foreach ($parr as $key => $value) {
				if (isset($field_title_list[$value]) && $key!='metafield') {
					$titles[] = $field_title_list[$value];
				}
			}
			$csv_headers = array (
				$titles
			);
			foreach ($csv_headers as $csv_header) {
			  fputcsv($log_file_handler, $csv_header);
			  fputcsv($skipped_products_log_file_handler, $csv_header);
			}
			fclose($log_file_handler);
			fclose($skipped_products_log_file_handler);

			fclose($filehandle2);
		}

		$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
		$data = file_get_contents( $text_file_name, false, $context );

		$step_entry = 150;
		if ( $data ) {
			$arr = unserialize( $data );
			$items = array_slice($arr, $last_count, $step_entry);

			if ( !$processing ) {
				if ( $last_count < count($arr) ) {
					// update sync_feeds table 'processing' status
					
					$condition = "id ='".$feed_id."'";
					$feed_data = array('processing' => 1);
					$this->db->update('sync_feeds', $feed_data, $condition);

					$count = 0;
					$skip_count = 0;
					$alo = false;
					foreach ( $items as $key => $item ) 
					{
						$sku = 	$item['product']['variants'][0]['sku'];	
						$node = $this->search_by_sku($sku,$feed_id);	

						if(!empty($node))
						{
							$is_skip = false;

							$product_id_string = $node['product']['id'];
							$variant_id_string = $node['id'];
							$inventory_id_string = $node['inventoryItem']['id'];

							$location_id_string = $node['inventoryItem']['inventoryLevels']['edges'][0]['node']['location']['id'];
							

							$product_id = $this->getlastval($product_id_string);
							$variant_id = $this->getlastval($variant_id_string);
							$inventory_id = $this->getlastval($inventory_id_string);
							$location_id = $this->getlastval($location_id_string);

							$product = $item['product'];
							$variants = $item['product']['variants'][0];

							$title = $product['title'];
							$body_html = $product['body_html'];
							$vendor = $product['vendor'];
							$product_type = $product['product_type'];
							$handle = $product['handle'];
							$published = $product['published'];
							$tags = $product['tags'];
							$published = $product['published'];


							$price = $variants['price'];
							$taxable = $variants['taxable'];
							$required_shipping = $variants['required_shipping'];
							$inventory_quantity = $variants['inventory_quantity'];
							$cost = $variants['cost'];

							$img_src = $product['images'][0]['src'];

							$weight = 0;
							$weight_unit = "";  // Valid values: g, kg, oz, and lb.

							$upd_product = array();
							$upd_product["product"] = array("id" => $product_id);
							if(trim($title)!=''){ $upd_product["product"]["title"] = $title; }
							if(trim($body_html)!=''){ $upd_product["product"]["body_html"] = $body_html; }
							if(trim($vendor)!=''){ $upd_product["product"]["vendor"] = $vendor; }
							if(trim($product_type)!=''){ $upd_product["product"]["product_type"] = $product_type; }
							if(trim($published)!=''){ $upd_product["product"]["published"] = $published; }
							if(trim($tags)!=''){ $upd_product["product"]["tags"] = $tags; }
							if(trim($handle)!=''){ $upd_product["product"]["handle"] = $handle; }

							$upd_variant = array();
							$upd_variant["variant"] = array("id" => $variant_id);
							if(trim($weight)!=''){ $upd_variant["variant"]["weight"] = $weight; }
							if(trim($weight_unit)!=''){ $upd_variant["variant"]["weight_unit"] = $weight_unit; }
							if(trim($price)!=''){ $upd_variant["variant"]["price"] = $price; }		

							$upd_inventory = array();
							$upd_inventory["inventory_item"] = array("id" => $inventory_id);
							if(trim($cost)!=''){ $upd_inventory["inventory_item"]["cost"] = $cost; }

							$upd_inventory_levels = array("location_id" => $location_id, "inventory_item_id" => $inventory_id);
							if(trim($inventory_quantity)!=''){ $upd_inventory_levels["available"] = $inventory_quantity; }

							$shop = $this->store_data($feed_id)->login_store_id;


							$metafieldraw = $product['metafield'];
							if(!empty($metafieldraw) and $product_id!='')
							{
								foreach ($metafieldraw as $eachmetafield) {
									$metafield = $eachmetafield['metafield'];
									$meta_arr = array(
										"metafield" => array(
											"namespace" => $metafield['namespace'],
											"key" => $metafield['key'],
											"value" => $metafield['value'],
											"value_type" => $metafield['value_type']
										)
									);

									$productMeta = $this->shopify_call($this->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-07/products/".$product_id."/metafields.json", $meta_arr, 'POST');
								}
							}			
							

							if($product_id!='')
							{
$this->shopify_call($this->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-04/products/".$product_id.".json", $upd_product, 'PUT');
$this->shopify_call($this->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-04/variants/".$variant_id.".json", $upd_variant, 'PUT');
$this->shopify_call($this->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-04/inventory_items/".$inventory_id.".json", $upd_inventory, 'PUT');
$this->shopify_call($this->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-04/inventory_levels/set.json", $upd_inventory_levels, 'POST');
								sleep(1);

								$count++;
								$pau_cont++;
							}
							
						}
						else
						{
							// sku not found, product to be add
							$is_skip = true;
							$skip_count++;
							$alo = true;
							//Creates log file for existing items
							$this->product_log($skipped_product_log_file_name, $item, $parr_outer);
						}

						$activitylog_total_row = count($arr);
						$activitylog_completed_row = $last_count + $count;								
						$completed_row = $activitylog_completed_row + $skip_count;

						// echo $activitylog_total_row . "---". $activitylog_completed_row . "---" . $completed_row . "\n";

						if( $completed_row >= $activitylog_total_row )
						{
							$activitylog_run_status = 'C';
							$activitylog_file_name = $log_filename_db ? $log_filename_db : $log_file;
							$activitylog_skipped_file_name = $skipped_product_activity_filename;

							$processcondition = "id ='".$feed_id."'";
							$process_data = array('is_processed' => 'N', 'last_count'=>0, 'feed_started'=>0);
							$this->db->update('sync_feeds', $process_data, $processcondition);
							$is_feed_process_conplete = true;

							// if ($alo)
							// {
							// 	$log_data = array(
							// 		'activitylog_feedid' => $feed_id,
							// 		'activitylog_total_row' => $activitylog_total_row,
							// 		'activitylog_run_status' => 'C',
							// 		'activitylog_file' => $activitylog_file_name
							// 	);
							// 	$this->activity_log_entry($feed_id,$log_data);
							// }
						}
						else
						{
							$activitylog_run_status = 'R';
							$activitylog_file_name = null;
							$activitylog_skipped_file_name = null;
						}				

						if( !$is_skip || ($completed_row >= $activitylog_total_row  && count(file($log_file_name)) != 1) )
						{
							$last_activity_count = $this->last_activity_count($feed_id);
							$new_activity_count = $last_activity_count + 1;

							$cts=time();
							$activitylog_ts = date("Y-m-d h:m:s",$cts);
							
							$log_data = array(
								'activitylog_feedid' => $feed_id,
								'activitylog_total_row' => $activitylog_total_row,
								'activitylog_completed_row' => $new_activity_count,
								'activitylog_run_status' => $activitylog_run_status,
								'activitylog_file' => $activitylog_file_name,
								'activitylog_skipped_file' => $activitylog_skipped_file_name,
								'activitylog_ts' => $activitylog_ts
							);
							$this->activity_log_entry($feed_id,$log_data);

							$this->product_log($log_file_name, $item, $parr_outer);
						}
					}

					// update sync_feeds table 'processing' status
					// update sync_feeds table 'last_count'

					if($is_feed_process_conplete){
						if (count(file($log_file_name)) == 1) {
							$log_data = array(
								'activitylog_feedid' => $feed_id,
								'activitylog_total_row' => $activitylog_total_row,
								'activitylog_run_status' => 'C',
								'activitylog_file' => $activitylog_file_name,
								'activitylog_skipped_file' => $activitylog_skipped_file_name
							);
							$this->activity_log_entry($feed_id, $log_data);
						}
						$nw_last_count = 0;
						$log_filename_for_db = null;
						$skipped_products_log_filename_for_db = null;
					}
					else{
						$nw_last_count = $last_count + $count + $skip_count;
						$log_filename_for_db = $activity_filename;
						$skipped_products_log_filename_for_db = $skipped_product_activity_filename;
					}					
					$condition = "id ='".$feed_id."'";
					$feed_data = array('processing' => 0, 'last_count' => ($nw_last_count),  'log_filename' => $log_filename_for_db, 'log_filename_skipped_products' => $skipped_products_log_filename_for_db);
					$this->db->update('sync_feeds', $feed_data, $condition);
					
					$response['success'] = true;
                    $response['message'] = 'Feed is being processed! '  . 'Total feeds: ' . count($arr) . ' Feed processed: ' . ($last_count + $count);
				} else {
					$processcondition = "id ='".$feed_id."'";
					$process_data = array('is_processed' => 'N', 'last_count'=>0, 'feed_started'=>0);
					$this->db->update('sync_feeds', $process_data, $processcondition);
					
					$response['message'] = 'Feed is already processed!';
				}
			} else {
				$response['message'] = 'Feed is under process!';
			}
		} else {
			$response['message'] = 'Feed doesn\'t exists!';
		}
		return $response;
	}


	public function search_by_sku($sku,$feed_id)
    {
        $token = $this->store_data($feed_id)->login_store_token;
	    $store = $this->store_data($feed_id)->login_store_url;

		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => "https://".$store."/admin/api/graphql.json",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "{\"query\":\"query {productVariants(first: 1, query: \\\"sku:".$sku."\\\"){edges{node{product{id}inventoryItem{id inventoryLevels(first:1){edges{node{location{id}}}}} inventoryQuantity}}edges{node{id}}}}\"}",
		CURLOPT_COOKIE => "__cfduid=d19b019a8d38b995cf6bd385cb31fadba1599474499; request_method=POST",
		CURLOPT_HTTPHEADER => array(
		"content-type: application/json",
		"x-shopify-access-token: ".$token.""
		),
		));

		$response = curl_exec($curl);
		$error_number = curl_errno($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($error_number) {
			return $id ='';
		} else {
			$product = json_decode($response, JSON_PRETTY_PRINT);
			return $product['data']['productVariants']['edges'][0]['node'];			
		}
    }


    function getlastval($string)
	{
		preg_match("/[^\/]+$/", $string, $matches);
		return $matches[0];
	}

    public function get_feed_type($feed_id)
    {
    	$sql="select * from sync_feeds where id ='".$feed_id."'";
		$this->db->query($sql);
		return $this->db->singleObj();
    }

    public function feed_param($feed_id , $feed_meta_key)
    {
    	$sql="select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key ='".$feed_meta_key."'";
		$this->db->query($sql);
		return $this->db->singleObj();
    }

    public function weighttograms($value,$unit)
    {
    	$dp = 5; //decimal places
    	$rtn_val = "none";    	
    	if($value!='' && $unit!='')
    	{
	    	$unit_lcas = strtolower($unit);    	
	    	switch ($unit_lcas) {
			  case 'kg':
			    $rtn_val = number_format($value*1000,$dp);
			    break;
			  case 'oz':
			    $rtn_val = number_format($value*28.349523125,$dp);
			    break;
			  case 'lb':
			    $rtn_val = number_format($value*453.59237,$dp);
			    break;
			  default:
			    $rtn_val = "none";
			}

		}
		return $rtn_val;
    }

    public function grams_to_other($value,$unit)
    {
    	$dp = 1; //decimal places
    	$rtn_val = "none";    	
    	if($value!='' && $unit!='')
    	{
	    	$unit_lcas = strtolower($unit);    	
	    	switch ($unit_lcas) {
			  case 'kg':
			    $rtn_val = number_format($value/1000,$dp);
			    break;
			  case 'oz':
			    $rtn_val = number_format($value/28.349523125,$dp);
			    break;
			  case 'lb':
			    $rtn_val = number_format($value/453.59237,$dp);
			    break;
			  default:
			    $rtn_val = "none";
			}

		}
		return $rtn_val;
    }

    public function activity_log_entry($feed_id,$log_data)
    {
    	$sql="select * from sync_feed_activitylog where activitylog_feedid ='".$feed_id."' order by activitylog_id desc limit 1";
		$this->db->query($sql);
		$datafeed = $this->db->singleObj();		
		$count = $this->db->rowCount();
		if($count>0){
			if($datafeed->activitylog_run_status!='C')
			{
				$condition = "activitylog_feedid = '".$feed_id."' and activitylog_id ='".$datafeed->activitylog_id."' ";
		    	$this->db->update("sync_feed_activitylog", $log_data, $condition);
			}   
			else
			{
				$this->db->insert("sync_feed_activitylog", $log_data);
			}
		}
		else{
			$this->db->insert("sync_feed_activitylog", $log_data);
		}

		// $this->db->insert("sync_feed_activitylog", $log_data);		

    }

    public function activity_log($feed_id,$limit,$page)
    {
    	$sql="select * from sync_feed_activitylog where activitylog_feedid ='".$feed_id."' and activitylog_run_status <> 'R' order by activitylog_id DESC";
    	if(trim($limit)!='')
		{
			$sql.= " limit ".$limit;
		} 
    	$this->db->query($sql);
    	$res = $this->db->resultsetObj();
        return $res;
    }

    public function activity_log_home($feed_id,$limit)
    {
    	$sql="select * from sync_feed_activitylog where activitylog_feedid ='".$feed_id."' order by activitylog_id DESC";
    	if(trim($limit)!='')
		{
			$sql.= " limit ".$limit;
		} 
    	$this->db->query($sql);
    	$res = $this->db->resultsetObj();
        return $res;
    }

    

    public function activity_status($status)
    {
    	switch ($status) {
		  case 'I':
		    $rtn_status = "Feed Initiated";
		    break;
		  case 'P':
		    $rtn_status = "Pending";
		    break;
		  case 'R':
		    $rtn_status = "Running";
		    break;
		  case 'C':
		    $rtn_status = "Completed";
		    break;
		  case 'F':
		    $rtn_status = "Failed";
		    break;
		  default:
		    $rtn_status = "Unknown";
		}
		return $rtn_status;
    }

    public function diff_time($ts_big,$ts_small)
    {
    	$diff_sec = ($ts_big - $ts_small);   // in seconds
    	$diff_sec_txt = "Few sec ago";

    	$diff_min = floor($diff_sec / (60));
    	$diff_min_txt = $diff_min." min(s) ago";

    	$diff_hrs = floor($diff_min / (60));  // in hours
    	$diff_hrs_txt = $diff_hrs." hour(s) ago";

    	$diff_day = floor($diff_hrs / (24));  // in hours
    	$diff_day_txt = $diff_day." day(s) ago";

    	$diff_week = floor($diff_day / (7));  // in hours
    	$diff_week_txt = $diff_week." week(s) ago";

    	if($diff_sec<60){
    		$rtn_status = $diff_sec_txt;
    	}
    	else if($diff_min<60){
    		$rtn_status = $diff_min_txt;
    	}
    	else if($diff_hrs<24){
    		$rtn_status = $diff_hrs_txt;
    	}
    	else if($diff_day<7){
    		$rtn_status = $diff_day_txt;
    	}
    	else{
    		$rtn_status = $diff_week_txt;
    	}

    	return $rtn_status;
    }


    public function get_file_only($feed_id)
    {

    	$sqlfeed="select * from sync_feeds where id ='".$feed_id."' ";
		$this->db->query($sqlfeed);
		$datafeed = $this->db->singleObj();

    	$datafeed->feed_method_id;

		$sql="select * from sync_feed_values where feed_id ='".$feed_id."' and meta_key = 'feed_connection_param' ";
		$this->db->query($sql);
		$data = $this->db->singleObj();
		$singleObj = json_decode($data->meta_value);

		
		if($datafeed->feed_method_id==1){
			$filename_only = $singleObj->file_name;
			$filename = $_SERVER['DOCUMENT_ROOT'] . '/uploads/uploadfiles/' . $filename_only;
		}
		else{

			$ftp_user = $singleObj->ftp_user;
			$ftp_pwd = $singleObj->ftp_pwd;
			$ftp_host = $singleObj->ftp_host;
			$ftp_dir_path = $singleObj->ftp_dir_path;

			$filename = 'ftp://'.$ftp_user.':'.$ftp_pwd.'@'.$ftp_host.$ftp_dir_path;
		}

		return $filename;
    }

    public function activity_log_with_limit($feed_id, $start_from, $per_page_record)
    {
		$sql = "SELECT * FROM sync_feed_activitylog WHERE activitylog_feedid ='".$feed_id."' and activitylog_run_status='C' ORDER BY activitylog_id DESC";
		if (trim($start_from)!='' && trim($per_page_record)!='') {
			$sql.= " LIMIT $start_from, $per_page_record";
		}
    	$this->db->query($sql);
    	$res = $this->db->resultsetObj();
        return $res;
    }

    public function last_activity_count($feed_id)
    {
    	$sql="select * from sync_feed_activitylog where activitylog_feedid ='".$feed_id."' order by activitylog_id desc limit 1";
		$this->db->query($sql);
		$datafeed = $this->db->singleObj();		
		$count = $this->db->rowCount();
		if($count>0){
			if($datafeed->activitylog_run_status!='C')
			{
				$last_activity_count = $datafeed->activitylog_completed_row;
			}   
			else
			{
				$last_activity_count = 0;
			}
		}
		else{
			$last_activity_count = 0;
		}
		return $last_activity_count;
    }

    	
}