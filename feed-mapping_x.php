<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
$session = new session();
$field_title_list = json_decode($session->get_session_by_key('field_title_list'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title>Feed Step 3 | <?php echo conf::SITE_TITLE; ?></title>
<?php include('header-script.php'); ?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body class="">
    <?php include('header.php'); ?>
    <div class="step-2-sec">
        <div class="container">
            <div class="row">
                <div class="step-header">
                    <div class="col-sm-8">
                        <div class="step-header-left">
                            <div class="step-seting-field">
                                <a class="setting-icon"><i class="fa fa-cog" aria-hidden="true"></i></a>
                                <a class="setting-field">
                                    <small>Step 3</small>
                                    <h2>Feed Mapping</h2>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <!--breadcrumb-->
                        <div class="bredcum-sec">
                            <ul class="breadcrumb">
                                <li><a href="<?php echo conf::SITE_URL; ?>">Home</a></li>
                                <li><a href="/feed-settings.php">Feed Settings</a></li>
                                <li>Feed Mapping</li>
                            </ul>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

<!-- Default structure -->
    <div  id="default_structure" class="default_structure">
        <div class="feild-form-box">
            <div class="col-sm-6">
                <div class="feild-left">
                    <label>Assign product identifier for your new product</label>
                    <div class="custom-select">
                        <select name="mapping['keys'][]" class="available_fields">
                        </select>
                    </div>
                    <small>select a field to uniquely identify store products</small>
                </div>
                <a class="field-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
            </div>
            <div class="col-sm-6">
                <div class="feild-right top-blank">
                    <div class="custom-select">
                        <select name="mapping['values'][]">
                            <option value="">-----Select-----</option>
                            <?php
                            foreach ($field_title_list as $key => $data) {
                                ?>
                                <option value="<?php echo $data; ?>"><?php echo $data; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <small>select a field to uniquely identify store products</small>
                </div>
                <a href="#" class="field-cross remove_field"><i class="fa fa-times-circle-o" aria-hidden="true"></i></a>
            </div>
            <div class="more-setting">
                <div class="more_setting_toggle_wrapper">
                    <a href="javascript:void(0)" class="more_setting_wrap_toggle more_info"><i class="fa fa-angle-right" aria-hidden="true"></i> Click for more settings</a>
                </div>
                <div class="more_setting_wrap" style="display: none;">
                    Coming soon
                </div>
            </div>
        </div>
    </div>

    <form action="" id="feed_mapping"  method="POST">
        <div class="step-3-sec">
            <div class="container">
                <div class="row">
                    <!--Form-section-->
                    <div class="feild-box-sec">
                        <div class="col-sm-12">
                            <div class="feild-form-box">
                                <div class="col-sm-6">
                                    <div class="feild-left">
                                        <label>Assign product identifier for your new product</label>
                                        <p>SKU <span>*</span></p>
                                        <input type="hidden" name="mapping['keys'][]" value="variants-sku">
                                        <small>Select a field to uniquely identify store products</small>
                                    </div>
                                    <a class="field-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-sm-6">
                                    <div class="feild-right top-blank">
                                        <div class="custom-select">
                                          <select name="mapping['values'][]">
                                            <option value="">-----Select-----</option>
                                            <?php
                                            foreach ($field_title_list as $key => $data) {
                                            ?>
                                            <option value="<?php echo $data; ?>"><?php echo $data; ?></option>
                                            <?php
                                            }
                                            ?>
                                          </select>
                                        </div>
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                </div>
                                <div class="more-setting">
                                    <div class="more_setting_toggle_wrapper">
                                        <a href="javascript:void(0)" class="more_setting_wrap_toggle more_info"><i class="fa fa-angle-right" aria-hidden="true"></i> Click for more settings</a>
                                    </div>
                                    <div class="more_setting_wrap" style="display: none;">
                                        Coming soon
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="feild-form-box">
                                <div class="col-sm-6">
                                    <div class="feild-left">
                                        <label>Assign product identifier for your new product</label>
                                        <p>Title <span>*</span></p>
                                        <input type="hidden" name="mapping['keys'][]" value="title">
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                    <a class="field-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-sm-6">
                                    <div class="feild-right top-blank">
                                        <div class="custom-select">
                                          <select name="mapping['values'][]">
                                            <option value="">-----Select-----</option>
                                            <?php
                                            foreach ($field_title_list as $key => $data) {
                                            ?>
                                            <option value="<?php echo $data; ?>"><?php echo $data; ?></option>
                                            <?php
                                            }
                                            ?>
                                          </select>
                                        </div>
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                </div>
                                <div class="more-setting">
                                    <div class="more_setting_toggle_wrapper">
                                        <a href="javascript:void(0)" class="more_setting_wrap_toggle more_info"><i class="fa fa-angle-right" aria-hidden="true"></i> Click for more settings</a>
                                    </div>
                                    <div class="more_setting_wrap" style="display: none;">
                                        Coming soon
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="feild-form-box">
                                <div class="col-sm-6">
                                    <div class="feild-left">
                                        <label>Assign product identifier for your new product</label>
                                        <p>Price <span>*</span></p>
                                        <input type="hidden" name="mapping['keys'][]" value="variants-price">
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                    <a class="field-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-sm-6">
                                    <div class="feild-right top-blank">
                                        <div class="custom-select">
                                          <select name="mapping['values'][]">
                                            <option value="">-----Select-----</option>
                                            <?php
                                            foreach ($field_title_list as $key => $data) {
                                            ?>
                                            <option value="<?php echo $data; ?>"><?php echo $data; ?></option>
                                            <?php
                                            }
                                            ?>
                                          </select>
                                        </div>
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                </div>
                                <div class="more-setting">
                                    <div class="more_setting_toggle_wrapper">
                                        <a href="javascript:void(0)" class="more_setting_wrap_toggle more_info"><i class="fa fa-angle-right" aria-hidden="true"></i> Click for more settings</a>
                                    </div>
                                    <div class="more_setting_wrap" style="display: none;">
                                        Coming soon
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="feild-form-box">
                                <div class="col-sm-6">
                                    <div class="feild-left">
                                        <label>Assign product identifier for your new product</label>
                                        <p>Variant Group <span>*</span></p>
                                        <input type="hidden" name="mapping['keys'][]" value="variant">
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                    <a class="field-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-sm-6">
                                    <div class="feild-right top-blank">
                                        <div class="custom-select">
                                          <select name="mapping['values'][]">
                                            <option value="">-----Select-----</option>
                                            <?php
                                            foreach ($field_title_list as $key => $data) {
                                            ?>
                                            <option value="<?php echo $data; ?>"><?php echo $data; ?></option>
                                            <?php
                                            }
                                            ?>
                                          </select>
                                        </div>
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                </div>
                                <div class="more-setting">
                                    <div class="more_setting_toggle_wrapper">
                                        <a href="javascript:void(0)" class="more_setting_wrap_toggle more_info"><i class="fa fa-angle-right" aria-hidden="true"></i> Click for more settings</a>
                                    </div>
                                    <div class="more_setting_wrap" style="display: none;">
                                        Coming soon
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field-wrapper col-sm-12" id="dynamic_fields"></div>

                        <div class="col-sm-12">
                            <div class="add-feild-buttn">
                                <a href="#" class="add-buttn add-more-field" id="add-more-field">
                                    <b>+ Add field</b>
                                    Which product field you want to sync?
                                </a>
                            </div>
                            <div class="bottm-support-sec">
                                <span><i class="fa fa-angle-right" aria-hidden="true"></i></span> <p><i class="fa fa-question-circle" aria-hidden="true"></i> Having problem setting up? <a href="#" class="bot-support">Click here for support </a></p>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>

        <!--progress-bar-->
        <div class="step-progress-bar-sec">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <form id="msform">
                            <span class="subError"></span>
                            <a href="/feed-setting.php">
                                <input type="button" name="previous" class="previous action-button-previous" value="Back"/>
                            </a>
                            <!-- progressbar -->
                            <ul id="progressbar">
                                <li class="active"></li>
                                <li class="active"></li>
                                <li></li>
                            </ul>
                            <!-- fieldsets -->
                            <input type="submit" name="next" class="next action-button" value="Next"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
<?php include('footer-script.php'); ?>

<!--popup-->
 <script>
    $(document).ready(function() {

         window.field_keys = {
            'variants-sku': {
                    title:  'SKU(Variant)',
                    enable: true
                },
            'variants-title': {
                title:  'Title(Variant)',
                enable: true
            },
            'variants-price': {
                title:  'Price(Variant)',
                enable: true
            },
            'body_html': {
                title: 'Description',
                enable: true
            },
            'collection': {
                title: 'Collection',
                enable: true
            },
            'images-src':{
                title: 'Product Images',
                enable: true
            },
            'variants-barcode':{
                title: 'Barcode',
                enable: true
            },
            'inventory_quantity':{
                title: 'Quantity',
                enable: true
            },
            'variants-compare_at_price':{
                title: 'Compare at Price',
                enable: true
            },
            'variants-option1':{
                title: 'Variant Option 1',
                enable: true
            },
            'variants-option2':{
                title: 'Variant Option 2',
                enable: true
            },
            'variants-option3':{
                title: 'Variant Option 3',
                enable: true
            },
            'vendor':{
                title: 'Vendor',
                enable: true
            },
            'product_type': {
                title: 'Product Type',
                enable: true
            },
            'handle':{
                title: 'Handle',
                enable: true
            },
            'tags':{
                title: 'Tags',
                enable: true
            },
            'variants-weight':{
                title: 'Weight',
                enable: true
            },
            'variants-inventory_policy':{
                title: 'Inventory Policy',
                enable: true
            },
            'variants-taxable':{
                title: 'Taxable',
                enable: true
            },
            'variants-fulfillment_service':{
                title: 'Fulfillment Service',
                enable: true
            },
            'variants-inventoryItem-cost':{
                title: 'Cost',
                enable: true
            }
        };

        $(document).on('click', '.more_info', function (event) {
            $(this).closest('.more-setting').children('.more_setting_wrap').toggle();
        });

        $(document).on('focus', 'select.available_fields', function (event) {
            let old_key = this.value;
            window.field_keys[old_key]['enable'] = true;
            reset_current_list( this );
        }).on('change', 'select.available_fields', function (e) {
            let current_key = this.value;
            window.field_keys[current_key]['enable'] = false;
        });

        $(document).on('click', '#add-more-field', function (e) { //on add input button click
            e.preventDefault();
            generate_default_field_set();
            $( "#default_structure .feild-form-box").clone().appendTo( "#dynamic_fields" );
        });

        $(document).on("click",".remove_field", function(e){ //user click on remove text
            e.preventDefault();
            let key = $(this).parent().parent().find('.available_fields').val();
            window.field_keys[key]['enable'] = true;
            $(this).closest('.feild-form-box').remove();
        });

        $(document).on("submit","#feed_mapping", function(e){
            e.preventDefault();
            $.post("ajax/ajax_feeds.php", {
                method: 'create_field_mapping',
                data: $( this ).serialize()
            },  function(data) {
                console.log(data.data_back);

            }, 'json');
        });
    });

    function generate_default_field_set(){
        let i = 0;
        let option = '';
        $.each( window.field_keys, function( key, value ) {
            if( value.enable ){
                if(i == 0){
                    window.field_keys[key]['enable'] = false;
                    i++;
                }
                option += '<option value="'+ key + '">' + value.title + '</option>';
            }

        });
        $('#default_structure .available_fields').html(option);
    }
    function reset_current_list( currentblock ){
        let option = '';
        $.each( window.field_keys, function( key, value ) {
            if( value.enable ){
                option += '<option value="'+ key + '">' + value.title + '</option>';
            }

        });
        $(currentblock).html(option);
    }
</script>
</body>
</html>