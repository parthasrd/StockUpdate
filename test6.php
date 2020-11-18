<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>

<?php
$feedObj = new feed();
// echo $sku = $feedObj->feed_update_start(3);



$text_file_name = $_SERVER['DOCUMENT_ROOT'] . '/uploads/ftpfiles/2_30699001269780_products_list.txt';


$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
$data = file_get_contents( $text_file_name, false, $context );

$arr = unserialize( $data );
$items = array_slice($arr, 0, 1);

$count = 0;
foreach ( $items as $key => $item ) {

	echo "<pre>";
	print_r($item);

	$sku = $item['product']['variants'][0]['sku'];	
	$node = search_by_sku($sku);	

	// print_r($node);

	$product_id_string = $node['product']['id'];
	$variant_id_string = $node['id'];
	$inventory_id_string = $node['inventoryItem']['id'];

	$location_id_string = $node['inventoryItem']['inventoryLevels']['edges'][0]['node']['location']['id'];
	

	$product_id = getlastval($product_id_string);
	$variant_id = getlastval($variant_id_string);
	$inventory_id = getlastval($inventory_id_string);
	$location_id = getlastval($location_id_string);

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


	// $metafield = $product['metafield']['metafield'];
	$metafieldraw = $product['metafield'];

	//echo $metafield['namespace'];
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

		echo "<pre>";
		print_r($meta_arr);
	}
	

	
	// $meta_arr = array(
	// 	"metafield" => array(
	// 		"namespace" => "dimension",
	// 		"key" => "length",
	// 		"value" => "5.5",		
	// 		"value_type" => "string"
	// 	)
	// );

	// $product_id = "5803218337944";

		
	
	$shop = conf::SHOPIFY_SHOP_NAME;

	// $productEdit = shopify_call(conf::MASTER_APP_PASSWORD, $shop, "/admin/api/2020-04/products/".$product_id.".json", $upd_product, 'PUT');
	// $variantEdit = shopify_call(conf::MASTER_APP_PASSWORD, $shop, "/admin/api/2020-04/variants/".$variant_id.".json", $upd_variant, 'PUT');
	// $inventoryEdit = shopify_call(conf::MASTER_APP_PASSWORD, $shop, "/admin/api/2020-04/inventory_items/".$inventory_id.".json", $upd_inventory, 'PUT');
	// $inventoryLevelsEdit = shopify_call(conf::MASTER_APP_PASSWORD, $shop, "/admin/api/2020-04/inventory_levels/set.json", $upd_inventory_levels, 'POST');

	// $productMeta = shopify_call(conf::MASTER_APP_PASSWORD, $shop, "/admin/api/2020-07/products/".$product_id."/metafields.json", $meta_arr, 'POST');
	// $productMetaPrint = json_decode($productMeta['response'], JSON_PRETTY_PRINT);

	// print_r($productMetaPrint);



$count++;						
}


function search_by_sku($sku)
{
    
    $token = conf::MASTER_APP_PASSWORD;
    $store = conf::SHOPIFY_STORE_ID;

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
		
		// $id_string = $product['data']['productVariants']['edges'][0]['node']['product']['id'];
		// preg_match("/[^\/]+$/", $id_string, $matches);
		// $id = $matches[0];

		// if($id==''){
		// 	return $id ='';
		// }
		// else{
		// 	return $id;
		// }
	}
}



function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) 
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

	function getlastval($string)
	{
		preg_match("/[^\/]+$/", $string, $matches);
		return $matches[0];
	}

?>
