<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>
<?php $feedObj= new feed(); ?>


<?php
// $title="2222Sweet new product";
// $type = 'Snowboard';
// $vendor ='JadedPixel';


// $title = "00AaTest01_Title_02";
// $body_html = "00AaTest01_body";
// $vendor = "Custom Vendor";
// $product_type = "CustomType";
// $handle = "";
// $new_product_tag = "CustomTags";
// $published_status = true;

// $sku = "5847";
// $price = "80.00";
// $taxable = false;
// $required_shipping = true;
// $inventory_quantity = 500;
// $img_src = "https://www.morris4x4center.com/media/catalog/product//p/d/pdint_2_image_55134.15718158117270.jpg";

$title = "189 Jeep Tuffy Security Products Security Console Series Ii 8  Wide Black | 1955-1995 Wrangler YJ, CJ5, Grand Wagoneer SJ, 012-01";
$body_html = "Important Notes:reservoir Is Includedpulley Is Not Includedfits:1984-1986 Jeep Cherokee Xj1986 Jeep Comanche Mj. the Crown Power Steering Pump Is A Direct Replacement Part From Crown Automotive. It Fits The Above Listed Jeep Applications With A 2.5L Engine.product Details:direct Oe Type Replacement Partfor 2.5L Engineincludes Reservoirpulley Not Included12 Month/12,000 Mile Limited Warrantyparts Included:(1) Crown Power Steering Pumpyears Covered:1984, 1985 And 1986 | Jeep Crown Power Steering Pump, Suspension Parts | 1984-1986 Cherokee XJ, Comanche MJ, 53003903";
$vendor = "";
$product_type = "";
$handle = "";
$new_product_tag = "CustomTags";
$published_status = 1;
$sku = "53003903";
$price = "262.99";
$taxable = true;
$required_shipping = 1;
$inventory_quantity = 24;
$img_src = "https://www.morris4x4center.com/media/catalog/product//p/d/pdint_2_image_5620.15718158689220.jpg";

/*
$tt='{
    "query":"mutation 
    {
        p1:productCreate(input: 
        {
            title: \"'.$title.'\", 
            productType: \"'.$type.'\", 
            vendor: \"'.$vendor.'\" 
        }) { product{id} }
        
        p2:productCreate(input: 
        {
            title: \"'.$title.'\", 
            productType: \"'.$type.'\", 
            vendor: \"'.$vendor.'\" 
        }) { product{id} }
    
    }"}';

$string = trim(preg_replace('/\s+/', ' ', $tt));

$g = $feedObj->graphql($string);

*/

/*

"{\"query\":\"mutation {\\n    productCreate(input: {\\n      
    title: \\\"001122 New Test\\\",\\n      
    productType: \\\"Snowboard\\\", \\n      
    vendor: \\\"JadedPixel\\\",\\n      
    variants:{\\n        
        imageSrc: \\\"\\\",\\n        
        compareAtPrice: 45.00,\\n        
        price:85.00,\\n        
        sku:\\\"7845845\\\",\\n        
        inventoryQuantity:500,\\n        
        taxable: false,\\n        
        requiresShipping:false,\\n        
        inventoryPolicy:DENY        \\n      
    }\\n      
    images: {\\n        
        src: \\\"https://www.morris4x4center.com/media/catalog/product//p/d/pdint_2_image_55134.15718158117270.jpg\\\"\\n      
    },\\n      
    published:true\\n    })\\n      { product{id} }\\n\\n    \\n  }\"}"

    */
?>


<?php

// $qrystr='{"query":"mutation {';
// for ($i = 1; $i <= 1; $i++)
// {
//     $qrystr.= 'p'.$i.':productCreate(input: 
//             {
//                 title: \"'.$title.'\", 
//                 bodyHtml: \"'.$body_html.'\", 
//                 vendor: \"'.$vendor.'\", 
//                 productType: \"'.$product_type.'\", 
//                 handle: \"'.$handle.'\", 
//                 tags: \"'.$new_product_tag.'\"
//             }) { product{id} }';
    
// }  
// $qrystr.= '  }"}';

// $string = trim(preg_replace('/\s+/', ' ', $qrystr));

// $g = $feedObj->graphql($string);



$qrystr='{"query":"mutation {';
    for ($i = 1; $i <= 1; $i++)
    {
        $qrystr.= 'p'.$i.':productCreate(input: 
                {
                    title: \"'.$title.'\", 
                    bodyHtml: \"'.$body_html.'\", 
                    vendor: \"'.$vendor.'\", 
                    productType: \"'.$product_type.'\", 
                    handle: \"'.$handle.'\", 
                    tags: \"'.$new_product_tag.'\",
                    variants:
                    {       
                        price: \"'.$price.'\",     
                        sku: \"'.$sku.'\", 
                        inventoryQuantity: '.$inventory_quantity.',     
                        taxable: false,
                        requiresShipping: false
                    },
                    images: {      
                        src: \"'.$img_src.'\"    
                    },
                    published: true
                }) { product{id} }';
        
    }  
    $qrystr.= '  }"}';

      
    //$string = trim(preg_replace('/\s+/', ' ', $ww));

    $string = trim(preg_replace('~[\r\n]+~', '', $qrystr));
    
    //$g = $feedObj->graphql($string);
    

?>

<?php 
echo "<pre>";
//print_r($g);
echo "</pre>"
?>

<?php

// $request = new HttpRequest();
// $request->setUrl('https://infotechsolz-store-app.myshopify.com/admin/api/graphql.json');
// $request->setMethod(HTTP_METH_POST);

// $request->setHeaders(array(
//   'x-shopify-access-token' => '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',
//   'content-type' => 'application/json'
// ));

// $request->setCookies(array(
//   'request_method' => 'POST',
//   '__cfduid' => 'd1d63d68a882ee406d361c29ae91415041591959734'
// ));

// $request->setBody('{"query":"mutation {\\n    p1:productCreate(input: {title: \\"2222Sweet new product\\", productType: \\"Snowboard\\", vendor: \\"JadedPixel\\" })\\n      { product{id} }\\n    p2:productCreate(input: {title: \\"2222Sweet new product\\", productType: \\"Snowboard\\", vendor: \\"JadedPixel\\" })\\n      { product{id} }\\n    \\n  }"}');

// try {
//   $response = $request->send();

//   echo $response->getBody();
// } catch (HttpException $ex) {
//   echo $ex;
// }

?>



<?php
$sku = 'MY-SKUPDP';
$qwe_str = '{"query":"query {  productVariants(first: 1, query: \"sku:'.$sku.'\") { 
    edges{ node{ id sku price inventoryQuantity taxable requiresShipping }} 
    edges{ node{product{ id title descriptionHtml vendor productType handle tags } }  }}}"}';

$qwe_str = trim(preg_replace('/\s+/', ' ', $qwe_str));
$rset = $feedObj->graphql($qwe_str);
$resultSet = json_decode($rset);

echo "Product Id: ".$product_id = trim($resultSet->data->productVariants->edges[0]->node->product->id);

echo "<br>Price: ".$price = trim($resultSet->data->productVariants->edges[0]->node->price);
echo "<br>QTY: ".$inventoryQuantity = trim($resultSet->data->productVariants->edges[0]->node->inventoryQuantity);
echo "<br>Taxable: ".$taxable = trim($resultSet->data->productVariants->edges[0]->node->taxable);
echo "<br>RequiresShipping: ".$requiresShipping = trim($resultSet->data->productVariants->edges[0]->node->requiresShipping);

echo "<br>Title: ".$title = trim($resultSet->data->productVariants->edges[0]->node->product->title);
echo "<br>descriptionHtml: ".$descriptionHtml = trim($resultSet->data->productVariants->edges[0]->node->product->descriptionHtml);
echo "<br>vendor: ".$vendor = trim($resultSet->data->productVariants->edges[0]->node->product->vendor);
echo "<br>productType: ".$productType = trim($resultSet->data->productVariants->edges[0]->node->product->productType);
echo "<br>handle: ".$productType = trim($resultSet->data->productVariants->edges[0]->node->product->handle);
echo "<br>tags: ".$productType = trim(implode(",",$resultSet->data->productVariants->edges[0]->node->product->tags));

echo "<pre>";
//print_r($re->data->productVariants->edges[0]->node->product->id);
// echo "<br>".$re->data->productVariants->edges[0]->node->price;
echo "<br>";
print_r($resultSet);
echo "</pre>";
?>