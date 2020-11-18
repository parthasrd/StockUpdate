<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>

<?php
$feedObj = new feed();

$meta_arr = array(
	"metafield" => array(
		"namespace" => "product_type",
		"key" => "length",		
		"value_type" => "string",
		"description" => "Hello",
		"value" => NULL,
		"owner_resource" => "product"
	)
);

$token = conf::MASTER_APP_PASSWORD;
$shop = conf::SHOPIFY_SHOP_NAME;

// MetaField create section
$productMeta = shopify_call($token, $shop, "/admin/api/2020-07/metafields.json", $meta_arr, 'POST');
$output = json_decode($productMeta['response'], JSON_PRETTY_PRINT);

echo "<pre>";
print_r($output);

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
