<?php
$feed_obj = new feed();
$feed_methods = $feed_obj->get_all_feed_methods();
$edit_fid = ($session->exists('edit_fid')) ? $session->get_session_by_key('edit_fid') : '';
if($edit_fid!=''){
    $data = $feed_obj->feed_param($edit_fid,'feed_connection_param');
    $data = json_decode($data->meta_value);
    
    $first_row_is_header = (trim($data->first_row_is_header)!='') ? trim($data->first_row_is_header) : '';
    $file_name = (trim($data->file_name)!='') ? trim($data->file_name) : '';
    
    $after_process = $data->after_process;
    $file_format = (trim($data->file_format)!='') ? trim($data->file_format) : '';
}
else
{
    $first_row_is_header = 1;
    $file_name = '';
    
    $after_process = '';
    $file_format = '';
}
?>

<div class="feed-setting-box-sec">
  <br><br>
        <div class=" col-sm-10 col-sm-offset-1">
            <div class="feild-form-box">
                <div class="feild-left"> 
                    <div class=" col-sm-3 no-padding">
                      <label><b>*</b> Connection Method</label>  
                    </div>
                    <div class="col-sm-9 no-padding">
                        <select id="method_changer" class="form-control" name="connection_method">
                            <option value="none">Please select</option>
                            <?php
                            foreach ($feed_methods as $key => $val){
                                $selected = ($val->php_file_name == 'file_upload.php') ? 'selected' : '';
                                ?>
                                <option value="<?php echo $val->php_file_name; ?>" <?php echo $selected; ?> ><?php echo $val->title;?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="feild-left">
                    <div class="col-sm-3 no-padding">                        
                        <label><?php if($file_name=='') { ?><b>*</b><?php } ?> File</label>

                    </div>
                    <div class="col-sm-9 no-padding">
                        <div class="feild-input">
                          <input type="file" id="upld_file" name="upld_file" class="form-control" placeholder=""<?php if($file_name=='') { ?> required <?php } ?> >
                        </div>
                        <?php if($file_name!='') { ?>
                            <div><?php echo $file_name; ?></div>
                        <?php } ?>
                        <input type="hidden" name="_is_old_file" id="_is_old_file_id" value="<?php echo $file_name; ?>">
                    </div>
                </div>
                <div class="feild-left">
                    <div class=" col-sm-3 no-padding">
                        <label><b>*</b> File Format</label>
                    </div>
                    <div class="col-sm-9 no-padding">
                        <div class="custom-select">
                          <select id="file_format" name="file_format">
                            <option value="csv">CSV</option>
                            <option value="xlsx">XLSX</option>
                          </select>
                        </div>
                        <small>Any other file format is not allowed.</small>
                        <!-- <small class="error-msg">Fail to Auto detect file format or your file format is not supported. Please select the file format.</small> --> 
                    </div>
                </div>
                <!-- <div class="feild-left">
                    <div class=" col-sm-3 no-padding">
                        <label>First Row is Header</label>
                    </div>
                    <div class="col-sm-9 no-padding">
                        <div class="opt1">
                            <label class="switch">
                                <input class="switch-input" type="checkbox" name="first_row_is_header" id="first_row_is_header" />
                                <span class="switch-label" data-on="On" data-off="Off"></span> 
                                <span class="switch-handle"></span>
                            </label>
                        </div> 
                    </div>
                </div> -->
                
            </div>
        </div>
        
        <div class="col-sm-12">
            <div id="up_txt" style="text-align: center; position:relative; display:none;">
                <div style="color:#0e93fb; padding:5px 0;">File uploading, please wait...</div>
            </div>            
        </div>

        <!-- <div class="col-sm-12">
            <div class="add-feild-buttn">
                <a id="test_conn_bttn" class="test" style="cursor: pointer;">
                    Test Connection
                </a>
                <p id="conn_status"></p>
            </div>
            <div class="bottm-support-sec">
                <span><i class="fa fa-angle-right" aria-hidden="true"></i></span> <p><i class="fa fa-question-circle" aria-hidden="true"></i> Having problem setting up? <a href="#" class="bot-support">Click here for support </a></p>
            </div>
        </div> -->
    <div class="clearfix"></div>
</div>
