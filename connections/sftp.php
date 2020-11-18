<?php
$feed_obj = new feed();
$feed_methods = $feed_obj->get_all_feed_methods();
?>
<div class="feed-setting-box-sec">
    <ul class="feed-setting-header">
        <li><span> <img src="assets/images/ftp-icon.png" alt="ftp-icon"></span> <p>SFTP</p></li>
        <li><span> <img src="assets/images/direction-icon.png" alt="direction-icon"></span> <p>Direction </p></li>
        <li><span> <img src="assets/images/shopify-icon.png" alt="shopify-icon"></span> <p>Signatureautoparts</p></li>
    </ul>
    <div class=" col-sm-10 col-sm-offset-1">
        <div class="feild-form-box">
            <div class="feild-left">
                <div class=" col-sm-3 no-padding">
                    <label><b>*</b> Connection Method</label>
                </div>
                <div class="col-sm-9 no-padding">
                    <select id="method_changer" class="form-control" name="connection_method" required>
                        <option value="none">Please select</option>
                        <?php
                        foreach ($feed_methods as $key => $val){
                            $selected = ($val->php_file_name == 'sftp.php') ? 'selected' : '';
                            ?>
                            <option value="<?php echo $val->php_file_name; ?>" <?php echo $selected; ?> ><?php echo $val->title;?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="feild-left">
                <div class=" col-sm-3 no-padding">
                    <label><b>*</b> User</label>
                </div>
                <div class="col-sm-9 no-padding">
                    <div class="feild-input">
                        <input type="text" id="ftp_user" name="ftp_user" class="form-control" placeholder="" required>
                    </div>
                </div>
            </div>
            <div class="feild-left">
                <div class=" col-sm-3 no-padding">
                    <label><b>*</b> Password</label>
                </div>
                <div class="col-sm-9 no-padding">
                    <div class="feild-input">
                        <input type="password" id="ftp_pwd" name="ftp_pwd" class="form-control" placeholder="(Use Save password)" required>
                        <small>select a field to uniquely identify store products</small>
                    </div>
                </div>
            </div>
            <div class="feild-left">
                <div class=" col-sm-3 no-padding">
                    <label><b>*</b> Host</label>
                </div>
                <div class="col-sm-9 no-padding">
                    <div class="feild-input">
                        <input type="text" id="ftp_host" name="ftp_host" class="form-control" placeholder="" required>
                    </div>
                    <small>FTP Host, eg. vendor.com:20, ftp.vendor.com, 112.2.1.150</small>
                </div>
            </div>
            <div class="feild-left">
                <div class=" col-sm-3 no-padding">
                    <label><b>*</b> Directory and filename </label>
                </div>
                <div class="col-sm-9 no-padding">
                    <div class="feild-input">
                        <input type="text" id="ftp_dir_path" name="ftp_dir_path" class="form-control" placeholder="" required>
                    </div>
                    <small>eg. Directory and filename. eg. /www/project/data/inventory.csv</small>
                    <small>for filename with timestamp, please click here for more info.</small>
                </div>
            </div>
            <div class="feild-left">
                <div class=" col-sm-3 no-padding">
                    <label>After Process</label>
                </div>
                <div class="col-sm-9 no-padding">
                    <div class="custom-select">
                        <select id="after_process" name="after_process">
                            <option value="0">Do Nothing To My File</option>
                        </select>
                    </div>
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
                        </select>
                    </div>
                    <small>Sync-itz auto-detects compressed file such as .zip or .gz</small>
                    <small class="error-msg">Fail to Auto detect file format or your file format is not dupported. Please select the file format.</small>
                </div>
            </div>
            <div class="feild-left">
                <div class=" col-sm-3 no-padding">
                    <label>First Row is Header</label>
                </div>
                <div class="col-sm-9 no-padding">
                    <div class="opt1">
                        <label class="switch">
                            <input id="first_row_is_header" name="first_row_is_header" class="switch-input" type="checkbox" value="first_row_is_header" />
                            <span class="switch-label" data-on="On" data-off="Off"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>
                </div>
            </div>
            <!-- <div class="feild-left">
                <div class=" col-sm-3 no-padding">
                    <label>IP Whitelisting</label>
                </div>
                <div class="col-sm-9 no-padding">
                    <div class="opt1">
                        <label class="switch">
                            <input class="switch-input" type="checkbox" />
                            <span class="switch-label" data-on="On" data-off="Off"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
    <div class="col-sm-12">
        <div class="add-feild-buttn">
            <a id="test_conn_bttn" class="test" style="cursor: pointer;">
                Test Connection
            </a>
            <p id="conn_status"></p>
        </div>
        <div class="bottm-support-sec">
            <span><i class="fa fa-angle-right" aria-hidden="true"></i></span> <p><i class="fa fa-question-circle" aria-hidden="true"></i> Having problem setting up? <a href="#" class="bot-support">Click here for support </a></p>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
