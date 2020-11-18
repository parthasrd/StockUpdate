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
        $this->db->query("select * from sync_feeds where status = 'Y'");
        $res = $this->db->resultset();
        return $res;
    }

    public function get_all_feed_with_details(){
        $this->db->query("
select f.*,ft.feed_title, fm.title as connection_title from sync_feeds as f 
INNER JOIN sync_feed_types as ft ON ft.id = f.feed_type_id
INNER JOIN sync_feed_method as fm ON fm.id = f.feed_method_id 
where f.status = 'Y' 
");
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
        return $res;
    }

    public function create_new_feed_metas( $dataSet )
    {
        $res = $this->db->insert("sync_feed_values", $dataSet);
        return $res;
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

			$filename = 'ftp://'.$ftp_user.':'.$ftp_pwd.'@'.$ftp_host.$ftp_dir_path;
			
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


	public function file_headerfields($connection_id = '')
	{
		if(trim($connection_id)!='')
		{
			$sql="select * from sync_feed_values where feed_id ='1' and meta_key = 'feed_connection_param' ";
			$this->db->query($sql);
			$data = $this->db->singleObj();
			$data = json_decode($data->meta_value);

			$ftp_user = $data->ftp_user;
			$ftp_pwd = $data->ftp_pwd;
			$ftp_host = $data->ftp_host;
			$ftp_dir_path = $data->ftp_dir_path;
			
			$after_process = $data->after_process;
			$file_format = $data->file_format;	
			
			$parr = array(
				'variants-sku' => 0,
				'title' => 1,
				'variants-price' => 5,
				'body_html' => 2,
				'images-src' => 6,
				'variants-inventory_quantity' => 4,
				'variants-inventoryItem-cost' => 12
			);

			$filename = 'ftp://'.$ftp_user.':'.$ftp_pwd.'@'.$ftp_host.$ftp_dir_path;
			if(file_exists($filename)){ 

				/*
				$handle = fopen($filename, "r");
				$row = fgetcsv($handle, 0, ",");
				$jval = json_encode($row);
				echo $jval;	
				*/

				$filehandle = fopen($filename, "r");
				$row = fgetcsv($filehandle, 0, ",");
				//echo count($row);
				
				$t=1;
				$ik=0;
				$list_arr = array();
				while($row = fgetcsv($filehandle, 0, ","))
				{ 
					
					if(trim($row[$parr['title']])){ $title = trim($row[$parr['title']]); } else { $title = ''; }
					if(trim($row[$parr['body_html']])){ $body_html = trim($row[$parr['body_html']]); } else { $body_html = ''; }
					if(trim($row[$parr['vendor']])){ $vendor = trim($row[$parr['vendor']]); } else { $vendor = ''; }
					if(trim($row[$parr['product_type']])){ $product_type = trim($row[$parr['product_type']]); } else { $product_type = ''; }
					if(trim($row[$parr['handle']])){ $handle = trim($row[$parr['handle']]); } else { $handle = ''; }
					if(trim($row[$parr['published']])){ $published = trim($row[$parr['published']]); } else { $published = true; }
					if(trim($row[$parr['variants-sku']])){ $sku = trim($row[$parr['variants-sku']]); } else { $sku = ''; }
					if(trim($row[$parr['variants-inventory_quantity']])){ $inventory_quantity = trim($row[$parr['variants-inventory_quantity']]); } else { $inventory_quantity = ''; }
					if(trim($row[$parr['variants-price']])){ $price = trim($row[$parr['variants-price']]); } else { $price = ''; }					
					if(trim($row[$parr['variants-taxable']])){ $taxable = trim($row[$parr['variants-taxable']]); } else { $taxable = true; }
					if(trim($row[$parr['variants-required_shipping']])){ $required_shipping = trim($row[$parr['variants-required_shipping']]); } else { $required_shipping = true; }
					if(trim($row[$parr['images-src']])){ $img_src = trim($row[$parr['images-src']]); } else { $img_src = ''; }


					$products_array = array(
						"product"=>array(
							"title"=> $title,
							"body_html"=> $body_html,
							"vendor"=> $vendor,
							"product_type"=> $product_type,
							"handle" => $handle,
							"published"=> $published,
							"variants"=>array(
								array(
									"sku"=> $sku,
									"price"=> $price,
									"taxable"=>$taxable,
									"required_shipping" => $required_shipping,
									"inventory_quantity" => $inventory_quantity,
					
								)
							),
							"images"=>array(
								array(
									"src"=> $img_src,					
								)
							)

						)
					);

					$list_arr[$ik][] = $products_array;


					
					//echo "<pre>";
					//print_r($products_array);
					//echo "</pre>";

					//$this->create_product_api(conf::MASTER_APP_USER_ID,conf::MASTER_APP_PASSWORD,conf::SHOPIFY_STORE_ID,$products_array);
					//echo $t." ";
					$t++;
					if($t%500==0){
						$ik++;
						//break;
					}
				}
				//echo "cc->".count($list_arr);
				//echo "<pre>";
				//print_r($list_arr);
				//echo "</pre>";

				foreach($list_arr as $arky => $newarvl){
					//$this->randompush($newarvl);											
					$arky = function($newarvl) {
					    foreach($newarvl as $nkar)
						{
							$this->create_product_api(conf::MASTER_APP_USER_ID,conf::MASTER_APP_PASSWORD,conf::SHOPIFY_STORE_ID,$nkar);
						}
					};
					$arky();

				}

				

			}
			else{
				die();
			}
		}
		else
		{
			return false;
		}
		
	}

	public function randompush($newarvl)
	{
		foreach($newarvl as $nkar)
		{
			$this->create_product_api(conf::MASTER_APP_USER_ID,conf::MASTER_APP_PASSWORD,conf::SHOPIFY_STORE_ID,$nkar);
		}
	}

	public function feed_delete_process($feed_data,$feed_id)
	{
		$condition = "id ='".$feed_id."'";
		$this->db->update('sync_feeds',$feed_data,$condition);
	}

	public function feed_mapping_fields($build_arr)
	{
		$feed_id = $this->session->get_session_by_key('feed_id');
		$metavalue = json_encode($build_arr);
		$data = array(
			'feed_id' => $feed_id,
			'meta_key' => 'feed_product_param',
			'meta_value' => $metavalue
		);
		$this->db->insert('sync_feed_values',$data);		
	}

	public function create_feed_advance_setteng($feed_meta,$meta_key)
	{
		$feed_id = $this->session->get_session_by_key('feed_id');
		$metavalue = json_encode($feed_meta);
		$data = array(
			'feed_id' => $feed_id,
			'meta_key' => $meta_key,
			'meta_value' => $metavalue
		);
		$this->db->insert('sync_feed_values',$data);
	}

	public function current_feed()
	{
		$feed_id = $this->session->get_session_by_key('feed_id');
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
		$url = "https://".$user.":".$password."@".$store."/admin/products.json";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($array));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec ($curl);
		curl_close ($curl);
		return $response;
	}

	public function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) 
	{
    
		// Build URL
		return $url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
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
	
}