<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');
$feed_obj = new feed();
$session = new session();
$session_feed_id = $session->get_session_by_key('feed_id');
$edit_fid = ($session->exists('edit_fid')) ? $session->get_session_by_key('edit_fid') : '';
if($edit_fid!=''){
    $data = $feed_obj->feed_param($edit_fid,'feed_advance_setting');
    $data = json_decode($data->meta_value);
    
    $auto_publish_product = (trim($data->auto_publish_product)!='') ? trim($data->auto_publish_product) : '';
    $same_image_variant = (trim($data->same_image_variant)!='') ? trim($data->same_image_variant) : '';
    $first_image_to_all_variant = (trim($data->first_image_to_all_variant)!='') ? trim($data->first_image_to_all_variant) : '';
    $skip_zero_quantity = (trim($data->skip_zero_quantity)!='') ? trim($data->skip_zero_quantity) : '';
    $new_product_tag = (trim($data->new_product_tag)!='') ? trim($data->new_product_tag) : '';
}
else
{
    $auto_publish_product = 0;
    $same_image_variant = '';
    $first_image_to_all_variant = '';
    $skip_zero_quantity = 0;
    $new_product_tag = 'NEWLY-IMPORTED';
}
$new_product_tag = ($new_product_tag!='') ? $new_product_tag : 'NEWLY-IMPORTED';
?>
<div class="row">
    <div class="step-header">
        <div class="col-sm-8">
            <div class="step-header-left">
                <div class="step-seting-field">
                    <a class="setting-icon"><i class="fa fa-cog" aria-hidden="true"></i></a>
                    <a class="setting-field">
                        <small>Step 4</small>
                        <h2>Advanced Settings</h2>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <!--breadcrumb-->
            <div class="bredcum-sec">
                <ul class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/feed-mapping.php?fid=<?php echo $session_feed_id; ?>">Feed Mapping</a></li>
                    <li>Advanced Settings</li>
                </ul>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <!--Form-section-->
    <div class="feild-box-sec">
        <div class="col-sm-4">
            <div class="pro-details">
                <h3>Products</h3>
                <p>Manage how products are updated in the store. This will apply to all products in the feed<br>
                You can choose to include/exclude certain products from being added with filters.</p>
                <a href="#" class="learn-buttn"><i class="fa fa-floppy-o" aria-hidden="true"></i> Learn more</a>
            </div>
        </div>
        <div class="col-sm-8">
            <!--  <form action="" method="post" id="feed_extra"> -->
            
            <ul class="advance-secting-option">
                <li>
                    <div class="opt1">
                        <label class="switch">
                            <input class="switch-input" type="checkbox"<?php if($auto_publish_product==1){ ?> checked="checked"<?php } ?> name="auto_publish_product" />
                            <span class="switch-label" data-on="On" data-off="Off"></span> 
                            <span class="switch-handle"></span>
                        </label>
                        <p>Auto publish all new added products. By default, new products are hidden.</p>
                    </div>
                </li>
                <!-- <li>
                    <div class="opt1">
                        <label class="switch">
                            <input class="switch-input" type="checkbox"  name="same_image_variant" />
                            <span class="switch-label" data-on="On" data-off="Off"></span> 
                            <span class="switch-handle"></span>
                        </label>
                        <p>Link image to variant when the image url is same row as variant</p>
                    </div>
                </li>
                <li>
                    <div class="opt1">
                        <label class="switch">
                            <input class="switch-input" type="checkbox"  name="first_image_to_all_variant" />
                            <span class="switch-label" data-on="On" data-off="Off"></span> 
                            <span class="switch-handle"></span>
                        </label>
                        <p>Always assign first product images to all variants </p>
                    </div>
                </li> -->
                <li>
                    <div class="opt1">
                        <label class="switch">
                            <input class="switch-input" type="checkbox" <?php if($skip_zero_quantity==1){ ?> checked="checked"<?php } ?>  name="skip_zero_quantity"/>
                            <span class="switch-label" data-on="On" data-off="Off"></span> 
                            <span class="switch-handle"></span>
                        </label>
                        <p>Skip add product with zero quantity. </p>
                    </div>
                </li>


                <li>
                    <div class="opt2">
                        <p>All newly added products will tagged with :</p>
                        <input type="text" class="form-control" name="new_product_tag" value="<?php echo $new_product_tag; ?>" >
                    </div>
                </li>
            </ul>
            <!-- <input type="submit"  value="submit"> -->
        <!-- </form> -->
        </div>
        <div class="col-sm-12">
            <div class="Caution">
                <h4><b>!</b>Caution</h4>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
            </div>
            <div class="bottm-support-sec">
                <span><i class="fa fa-angle-right" aria-hidden="true"></i></span> <p><i class="fa fa-question-circle" aria-hidden="true"></i> Having problem setting up? <a href="#" class="bot-support">Click here for support </a></p>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>