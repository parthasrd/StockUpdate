<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');
$session = new session();
$feedObj= new feed();
$field_title_list = json_decode($session->get_session_by_key('field_title_list'));
$session_feed_id = $session->get_session_by_key('feed_id');

// extra added for over write
if($session_feed_id != '')
{
    $field_title_list = json_decode($feedObj->get_csv_heaer_title_by_id($session_feed_id));
}


?>

<?php
if(trim($_REQUEST['fid'])!='')
{
    $feedObj= new feed();
    $fid = trim($_REQUEST['fid']); 
    $feed_param_rcv = $feedObj->feed_param($fid,'feed_product_param');
    $feed_param = json_decode($feed_param_rcv->meta_value,true);  
    $feed_other_param = json_decode($feed_param_rcv->meta_value,true); 

    $sku = (array_key_exists("variants-sku",$feed_param)) ? trim($feed_param['variants-sku']) : '';
    $title = (array_key_exists("title",$feed_param)) ? trim($feed_param['title']) : '';
    $price = (array_key_exists("variants-price",$feed_param)) ? trim($feed_param['variants-price']) : '';

    unset($feed_other_param["variants-sku"]);
    
    if(array_key_exists("metafield",$feed_other_param))
    {
        $meta_field_array = $feed_other_param['metafield'];
        unset($feed_other_param['metafield']);
    }
    else
    {
        $meta_field_array = array();
    }
    $field_title_list = json_decode($feedObj->get_csv_heaer_title_by_id($fid));
    $feedSingle = $feedObj->get_feed_type($fid);
    $session->add_session('feed_type', $feedSingle->feed_type_id );

}
else
{
    $fid = '';
    $feed_param = array();
    $meta_field_array = array();
}
$metafield_count = count($meta_field_array);
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
<style>
.more-setting{
    display: none;
}
.more_setting_meta{
    display: none;
}
.meta_setting{
    width: 90%;
}
.metaform label, .feild-form-box .meta_setting label{
   padding-bottom: 0px !important;
}
.feild-form-box .meta_setting input[type='text'], .feild-form-box .meta_setting select{
   margin-bottom: 15px;
}
.value_type_grup label{
    display: inline;
}
.value_type_grup{
    margin-bottom: 15px;
}

</style>
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
                                <!-- <li><a href="/feed-settings.php?fid=<?php echo $session_feed_id; ?>">Feed Settings</a></li> -->
                                <li><a href="javascript:void(0)">Feed Settings</a></li>
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
                <div id="changer_cont">
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
                <div class="more_setting_wrap">
                    Coming soon
                </div>
            </div>
        </div>
    </div>


    <div id="static_metafield" class="default_structure">
        <div class="feild-left meta_setting">
            <input type="hidden" value="metafield" class="spcl_cls" readonly="readonly">
            <input type="text" name="mapping['keys'][]" id="metafield_name" class="form-control" placeholder="Metafield Name"  readonly="readonly">

            <label>Metafield Namespace</label>
            <input type="text" name="metafield_namespace[]" class="form-control" placeholder="Metafield Namespace">

            <label>Metafield Key</label>
            <input type="text" name="metafield_key[]" class="form-control" placeholder="Metafield Key">        

            <label>Metafield Owner</label>
            <select class="form-control" name="meta_val_type[]">
            <option value="string">String</option>
            <option value="integer">Integer</option>
            </select>

            <label>Metafield Owner</label>
            <input type="text" name="metafield_owner[]" class="form-control" placeholder="Metafield Owner" value="Product" readonly="readonly">
        </div>
        <a class="field-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
    </div>

    <input type="hidden" id="metanum" value="0">

    <form action="" id="feed_mapping"  method="POST">
        <input type="hidden" name="_edit_id" id="_edit_id" value="<?php echo $fid; ?>">
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
                                            <option value="<?php echo $data; ?>"<?php if($data == $sku){ ?> selected="selected" <?php } ?> ><?php echo $data; ?></option>
                                            <?php
                                            }
                                            ?>
                                          </select>
                                        </div>
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                </div>
                                <div class="more-setting" style="display: none;">
                                    <div class="more_setting_toggle_wrapper">
                                        <a href="javascript:void(0)" class="more_setting_wrap_toggle more_info"><i class="fa fa-angle-right" aria-hidden="true"></i> Click for more settings</a>
                                    </div>
                                    <div class="more_setting_wrap" style="display: none;">
                                        Coming soon
                                    </div>
                                </div>
                            </div>
                        </div>

                                               
                        <div id="edit_structure">
                        <?php $m=1; foreach($feed_other_param as $each_param_key => $each_other_param ){ ?>

                        <div class="feild-form-box">
                            <div class="col-sm-6">
                                <div id="changer_cont">
                                    
                                    <?php if(strpos($each_param_key,"Metafield")!==false) { ?>
                                    <?php
                                    $findkey = array_keys(array_column($meta_field_array, 'metafield_value'), $each_other_param);
                                    $metafieldvalue = $meta_field_array[$findkey[0]];
                                    ?>

                                    <div class="feild-left meta_setting">
                                        <input type="hidden" value="metafield" class="spcl_cls" readonly="readonly">
                                        <input type="text" name="mapping['keys'][]" class="form-control" placeholder="Metafield Name" value="Metafield <?php echo $m; ?>"  readonly="readonly">

                                        <label>Metafield Namespace</label>
                                        <input type="text" name="metafield_namespace[]" value="<?php echo $metafieldvalue['metafield_namespace']; ?>" class="form-control" placeholder="Metafield Namespace">

                                        <label>Metafield Key</label>
                                        <input type="text" name="metafield_key[]" value="<?php echo $metafieldvalue['metafield_key']; ?>" class="form-control" placeholder="Metafield Key">        

                                        <label>Metafield Owner</label>
                                        <select class="form-control" name="meta_val_type[]">
                                            <option value="string"<?php if($metafieldvalue['meta_val_type']=='string'){ ?> selected="selected"<?php } ?>>String</option>
                                            <option value="integer"<?php if($metafieldvalue['meta_val_type']=='integer'){ ?> selected="selected"<?php } ?>>Integer</option>
                                        </select>

                                        <label>Metafield Owner</label>
                                        <input type="text" name="metafield_owner[]" class="form-control" placeholder="Metafield Owner" value="Product" readonly="readonly">
                                    </div>
                                    <a class="field-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></a>

                                    <?php $m++; } else { ?>
                                    <div class="feild-left">
                                        <label>Assign product identifier for your new product</label>
                                        <div class="custom-select">
                                            <select name="mapping['keys'][]" class="available_fields" id="<?php echo $each_param_key; ?>">
                                            </select>
                                        </div>
                                        <small>select a field to uniquely identify store products</small>
                                    </div>
                                    <a class="field-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                                    <?php } ?>


                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="feild-right top-blank">
                                    <div class="custom-select">
                                        <select name="mapping['values'][]">
                                            <option value="">-----Select-----</option>
                                            <?php
                                            foreach ($field_title_list as $key => $data) {
                                            ?>
                                            <option value="<?php echo $data; ?>"<?php if($data == $each_other_param){ ?> selected="selected" <?php } ?>><?php echo $data; ?></option>
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

                        <?php } ?>
                        </div>

                        
                        <!-- <div class="col-sm-12">
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
                        </div>  -->                        

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
                            <!-- <a href="/feed-settings.php?fid=<?php echo $session_feed_id; ?>">
                                <input type="button" name="previous" class="previous action-button-previous" value="Back"/>
                            </a> -->
                            <a href="/">
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
            'title': {
                title: 'Title',
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
            'metafield':{
                title: 'Metafield',
                enable: true
            },
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
             'variants-inventory_quantity':{
                 title: 'Quantity(Variant)',
                 enable: true
             },
             'variants-barcode':{
                 title: 'Barcode(Variant)',
                 enable: true
             },
             'variants-compare_at_price':{
                 title: 'Compare at Price(Variant)',
                 enable: true
             },
             'variants-weight':{
                 title: 'Weight(Variant)',
                 enable: true
             },
             'variants-inventory_policy':{
                 title: 'Inventory Policy(Variant)',
                 enable: true
             },
             'variants-taxable':{
                 title: 'Taxable(Variant)',
                 enable: true
             },
             'variants-fulfillment_service':{
                 title: 'Fulfillment Service(Variant)',
                 enable: true
             },
             'variants-inventoryItem-cost':{
                 title: 'Cost(Variant)',
                 enable: true
             },
             'variants-option1':{
                 title: 'Variant Option 1(Variant)',
                 enable: true
             },
             'variants-option2':{
                 title: 'Variant Option 2(Variant)',
                 enable: true
             },
             'variants-option3':{
                 title: 'Variant Option 3(Variant)',
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
            //window.field_keys[current_key]['enable'] = false;
            
            if(current_key=='metafield'){ 

                var curnum = $('#metanum').val( eval($('#metanum').val()) + 1 );
                var metanum = $('#metanum').val(); 
                var meta_auto_txt = "Metafield " + metanum;
                $('#metafield_name').val(meta_auto_txt);
                $(this).parent().parent().parent().parent().find('#changer_cont').html($( "#static_metafield .meta_setting").clone());                                            
            }
            else{
                $(this).parent().parent().parent().find('.cntnr_cls').remove(); 
            }
        });

        $(document).on('click', '#add-more-field', function (e) { //on add input button click
            e.preventDefault();
            var is_meta = generate_default_field_set();

            if(is_meta=='metafield'){

                var curnum = $('#metanum').val( eval($('#metanum').val()) + 1 );
                var metanum = $('#metanum').val();

                var meta_auto_txt = "Metafield " + metanum;
                $('#metafield_name').val(meta_auto_txt);

                $( "#metafield_structure .feild-form-box").clone().appendTo( "#dynamic_fields" );   
            }
            else{
                $( "#default_structure .feild-form-box").clone().appendTo( "#dynamic_fields" );
            }  

                        
            console.log(is_meta);
        });

        $(document).on("click",".remove_field", function(e){ //user click on remove text
            e.preventDefault();
            let key = $(this).parent().parent().find('.available_fields').val();
            let spcl_key = $(this).parent().parent().find('.spcl_cls').val();

            $(this).closest('.feild-form-box').remove();
        });

        $(document).on("submit","#feed_mapping", function(e){
            e.preventDefault();
            var edit_id = $('#_edit_id').val();
            $.post("ajax/ajax_feeds.php", {
                method: 'create_field_mapping',
                data: $( this ).serialize()
            },  function(data) { console.log(data);
                if( data.success )
                {
                    if(edit_id==''){
                        var redict_page = '/feed-advance-settings.php';
                    }
                    else{
                        var redict_page = '/feed-advance-settings.php?fid='+edit_id;
                    }
                    window.location.href = redict_page;
                }
                else
                {
                    $('.subError').html(data.message);
                }
            }, 'json');
        });
    });

    function generate_default_field_set(){
        let i = 0;
        var key_set = [];        
        let option = '';
        $.each( window.field_keys, function( key, value ) {
            if( value.enable ){
                if(i == 0){
                    window.field_keys[key]['enable'] = false;
                    i++;
                }
                option += '<option value="'+ key + '">' + value.title + '</option>';
                key_set.push(key);
            }
        });
        $('#default_structure .available_fields').html(option);
        if(key_set[0]=='metafield'){
           var rtn = "metafield";
        }
        else{
           var rtn = "other";
        }
        return rtn;
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

<script type="text/javascript">
    $(document).ready(function(){
        let i = 0;       
        let option = '';
        $.each( window.field_keys, function( key, value ) {
            if( value.enable ){
                if(i == 0){
                    // window.field_keys[key]['enable'] = false;
                    i++;
                }
                option += '<option value="'+ key + '">' + value.title + '</option>';
            }
        });
        $('#edit_structure .available_fields').html(option);

        <?php foreach($feed_other_param as $each_param_key => $each_other_param ){ ?>

        $('#edit_structure select#<?php echo $each_param_key; ?> option[value="<?php echo $each_param_key; ?>"]').attr('selected','selected');

        <?php } ?>
    });
</script>
</body>
</html>