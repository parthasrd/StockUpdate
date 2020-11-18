<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
class feed_two
{
	private $db;
	private $session;

	public function __construct($host = null, $db = null, $username = null, $pw = null) {
		$this->db = new database();
		$this->session = new session();
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

		include $_SERVER['DOCUMENT_ROOT'] . "/library/SimpleXLSX.php";
		if ( $xlsx = SimpleXLSX::parse($filename_outer) ) {
			$jval = $xlsx->rows();
			$field_title_list_outer = $jval[0];
		}
		fclose($filehandle_outer);

		$feedObj= new feed();

		// Create Mmpping array
		$parr_outer = $feedObj->get_product_array($feed_id, $field_title_list_outer);

		if ( $feed_started == 0 ) {
			$pau_cont = 1; // Prodduct add or update counter initilization 

			if($feed_method_id==2){
				$filename = 'ftp://' . $ftp_user . ':' . $ftp_pwd . '@' . $ftp_host . $ftp_dir_path;
			}
			else{
				$filename = $_SERVER['DOCUMENT_ROOT'] . '/uploads/uploadfiles/' . $ftp_dir_path;
			}

			$meta_value_fetch = $feedObj->get_mapping_fields($feed_id);
			if ( file_exists($filename) ) {
				$arr = [];
				$filehandle1 = fopen($filename, "r");

				// include $_SERVER['DOCUMENT_ROOT'] . "/library/SimpleXLSX.php";
				if ( $xlsx = SimpleXLSX::parse($filename) ) {
					$jval = $xlsx->rows();
					$field_title_list = $jval[0];
				}
				fclose($filehandle1);

				// Create Mapping array
				$parr = $feedObj->get_product_array($feed_id, $field_title_list);

				$filehandle2 = fopen($filename, "r"); // read file from FTP

				// Skip first row if first row is header
				if ( $data->first_row_is_header == 1 ) {
					$rows = array_slice($jval, 1, null, true); //removes the first row
				}

				$ik=1;

				foreach ($rows as $row) {
					//
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

					$shop = $feedObj->store_data($feed_id)->login_store_id;
					$count = 0;
					$skip_count = 0;
					$alo = false;
					foreach ( $items as $key => $item ) {		
						$sku = 	$item['product']['variants'][0]['sku'];	
						$node = $feedObj->search_by_sku($sku,$feed_id);	
						if(!empty($node))
						{
							$skip_count++;
							$pau_cont++;
							$is_skip = true;
							$alo = true;
							//Creates log file for existing items
							$feedObj->product_log($skipped_product_log_file_name, $item, $parr_outer);
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
									$crt_rsp = $feedObj->create_product_api(conf::APP_API_KEY, $feedObj->store_data($feed_id)->login_store_token, $feedObj->store_data($feed_id)->login_store_url, $item);
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
											$productMeta = $feedObj->shopify_call($feedObj->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-07/products/".$last_inserted_product_id."/metafields.json", $meta_arr, 'POST');
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
								$feedObj->activity_log_entry($feed_id,$log_data);
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
							$last_activity_count = $feedObj->last_activity_count($feed_id);
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
							$feedObj->activity_log_entry($feed_id,$log_data);
							$feedObj->product_log($log_file_name, $item, $parr_outer);
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
	
	public function feed_update_start($feed_id) {
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

		include $_SERVER['DOCUMENT_ROOT'] . "/library/SimpleXLSX.php";

		if ( $xlsx = SimpleXLSX::parse($filename_outer) ) {
			$jval = $xlsx->rows();
			$field_title_list_outer = $jval[0];
		}
		fclose($filehandle_outer);

		$feedObj= new feed();

		// Create Mapping array
		$parr_outer = $feedObj->get_product_array($feed_id, $field_title_list_outer);

		if ( $feed_started == 0 ) {
			$pau_cont = 1; // Prodduct add or update counter initilization 

			if($feed_method_id==2){
				$filename = 'ftp://' . $ftp_user . ':' . $ftp_pwd . '@' . $ftp_host . $ftp_dir_path;
			}
			else{
				$filename = $_SERVER['DOCUMENT_ROOT'] . '/uploads/uploadfiles/' . $ftp_dir_path;
			}

			$meta_value_fetch = $feedObj->get_mapping_fields($feed_id);

			if ( file_exists($filename) ) {
				$arr = [];
				$filehandle1 = fopen($filename, "r");

				// include $_SERVER['DOCUMENT_ROOT'] . "/library/SimpleXLSX.php";
				if ( $xlsx = SimpleXLSX::parse($filename) ) {
					$jval = $xlsx->rows();
					$field_title_list = $jval[0];
				}
				fclose($filehandle1);

				// Create Mapping array
				$parr = $feedObj->get_product_array($feed_id, $field_title_list);

				$filehandle2 = fopen($filename, "r"); // read file from FTP

				// Skip first row if first row is header
				if ( $data->first_row_is_header == 1 ) {
					$rows = array_slice($jval, 1, null, true); //removes the first row
				}

				$ik=1;

				foreach ($rows as $row) {
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

					if (trim($parr['metafield'])!='') { 
						$metafield_list = explode(",",trim($parr['metafield'])); 

						$cm = 0;						
						foreach($meta_value_fetch->metafield as $each_matafield_set) {
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
					foreach ($valid_weight_unit as $each_unit) {
					  $pos = strpos($unit_string,$each_unit);
					  if ($pos>0) {
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
					foreach ( $items as $key => $item ) {
						$sku = 	$item['product']['variants'][0]['sku'];	
						$node = $feedObj->search_by_sku($sku,$feed_id);	

						if (!empty($node)) {
							$is_skip = false;

							$product_id_string = $node['product']['id'];
							$variant_id_string = $node['id'];
							$inventory_id_string = $node['inventoryItem']['id'];

							$location_id_string = $node['inventoryItem']['inventoryLevels']['edges'][0]['node']['location']['id'];							

							$product_id = $feedObj->getlastval($product_id_string);
							$variant_id = $feedObj->getlastval($variant_id_string);
							$inventory_id = $feedObj->getlastval($inventory_id_string);
							$location_id = $feedObj->getlastval($location_id_string);

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

							$shop = $feedObj->store_data($feed_id)->login_store_id;

							$metafieldraw = $product['metafield'];
							if (!empty($metafieldraw) and $product_id!='') {
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
									$productMeta = $feedObj->shopify_call($feedObj->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-07/products/".$product_id."/metafields.json", $meta_arr, 'POST');
								}
							}			
							
							if ($product_id!='') {
								$feedObj->shopify_call($feedObj->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-04/products/".$product_id.".json", $upd_product, 'PUT');
								$feedObj->shopify_call($feedObj->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-04/variants/".$variant_id.".json", $upd_variant, 'PUT');
								$feedObj->shopify_call($feedObj->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-04/inventory_items/".$inventory_id.".json", $upd_inventory, 'PUT');
								$feedObj->shopify_call($feedObj->store_data($feed_id)->login_store_token, $shop, "/admin/api/2020-04/inventory_levels/set.json", $upd_inventory_levels, 'POST');
								sleep(1);
								$count++;
								$pau_cont++;
							}							
						} else {
							// sku not found, product to be add
							$is_skip = true;
							$skip_count++;
							$alo = true;
							//Creates log file for existing items
							$feedObj->product_log($skipped_product_log_file_name, $item, $parr_outer);
						}

						$activitylog_total_row = count($arr);
						$activitylog_completed_row = $last_count + $count;								
						$completed_row = $activitylog_completed_row + $skip_count;

						if ( $completed_row >= $activitylog_total_row ) {
							$activitylog_run_status = 'C';
							$activitylog_file_name = $log_filename_db ? $log_filename_db : $log_file;
							$activitylog_skipped_file_name = $skipped_product_activity_filename;

							$processcondition = "id ='".$feed_id."'";
							$process_data = array('is_processed' => 'N', 'last_count'=>0, 'feed_started'=>0);
							$this->db->update('sync_feeds', $process_data, $processcondition);
							$is_feed_process_conplete = true;
						} else {
							$activitylog_run_status = 'R';
							$activitylog_file_name = null;
							$activitylog_skipped_file_name = null;
						}				

						if ( !$is_skip || ($completed_row >= $activitylog_total_row  && count(file($log_file_name)) != 1) ) {
							$last_activity_count = $feedObj->last_activity_count($feed_id);
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
							$feedObj->activity_log_entry($feed_id,$log_data);

							$feedObj->product_log($log_file_name, $item, $parr_outer);
						}
					}

					// update sync_feeds table 'processing' status
					// update sync_feeds table 'last_count'

					if ($is_feed_process_conplete) {
						if (count(file($log_file_name)) == 1) {
							$log_data = array(
								'activitylog_feedid' => $feed_id,
								'activitylog_total_row' => $activitylog_total_row,
								'activitylog_run_status' => 'C',
								'activitylog_file' => $activitylog_file_name,
								'activitylog_skipped_file' => $activitylog_skipped_file_name
							);
							$feedObj->activity_log_entry($feed_id, $log_data);
						}
						$nw_last_count = 0;
						$log_filename_for_db = null;
						$skipped_products_log_filename_for_db = null;
					} else {
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
		}
	}
}