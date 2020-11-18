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
                    <li><a href="/feed-mapping.php">Feed Mapping</a></li>
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
                You can choose to include/exclude certain products from being updated with filters.</p>
                <a href="#" class="learn-buttn"><i class="fa fa-floppy-o" aria-hidden="true"></i> Learn more</a>
            </div>
        </div>
        <div class="col-sm-8">
            <!--  <form action="" method="post" id="feed_extra"> -->
            
            <ul class="advance-secting-option">               
                <li>
                    <div class="opt2">
                        <p>Auto publish products :</p><br>
                        <select class="form-control" name="auto_publish_products" required>
                            <option value="not_apply">Do not apply</option>
                            <option value="store_qty_more_than_zero">All products in store with quantity more than 0</option>
                            <option value="file_qty_more_than_zero">Products within feed file with quantity more than 0</option>                        
                        </select>
                    </div>
                </li>
                <li>
                    <div class="opt2">
                        <p>Auto-hide products :</p><br>
                        <select class="form-control" name="auto_hide_products" required>
                            <option value="not_apply">Do not apply</option>
                            <option value="store_qty_zero">All products in store when quantity is 0</option>
                            <option value="file_qty_zero">Products within feed file when quantity is 0</option>                        
                        </select>
                    </div>
                </li>
                <li>
                    <div class="opt2">
                        <p>Low Stock Level :</p>
                        <input type="text" class="form-control" name="low_stock_lavel" value="0" >
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