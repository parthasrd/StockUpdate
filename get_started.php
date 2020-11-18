<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>
<?php
$feeds_obj = new feed();

$token = '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';
$shop = 'infotechsolz-store-app';

$array = array(
	'fields' => 'id,title'
);

$productList = $feeds_obj->shopify_call($token, $shop, "/admin/api/2020-04/products.json", $array, 'GET');
$productListDecode = json_decode($productList['response'], JSON_PRETTY_PRINT);

foreach($productListDecode as $productEachList)
{
    $i=1;
	foreach($productEachList as $productEach)
	{
		echo "<pre>";
		print_r($productEach);
        echo "<pre>";
        //echo $productEach['title']."<br>";
        if($i>=5)
        {
            break;
        }
        $i++;
		
	}
}

?>