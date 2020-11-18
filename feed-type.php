<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');

$feed_obj = new feed();
$feed_types = $feed_obj->get_all_feed_types();

if(isset($_REQUEST['stp1Bttn']))
{
  $feed_type = trim($_REQUEST['feed_type']);
  $session = new session();
  $session->add_session( 'feed_type', $feed_type );
  header("Location: ".conf::SITE_URL."feed-settings.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title>Feed Step 1 | <?php echo conf::SITE_TITLE; ?></title>
<?php include('header-script.php'); ?>
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
                                    <small>Step 1</small>
                                    <h2>Feed Type</h2>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <!--breadcrumb-->
                        <div class="bredcum-sec">
                            <ul class="breadcrumb">
                                <li><a href="/">Home</a></li>
                                <li>Setup New Feed</li>
                            </ul>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="add-product-sec">
        <div class="container">
            <div class="row">
                <div class=" col-sm-8 col-md-8 col-lg-8 col-sm-offset-2 col-lg-offset-2">
                    <form action="" method="POST">
                        <div class="add-product">
                            <?php if( $feed_types ) {
                                ?>
                                <h3>I want to</h3>
                                <div class="custom-select">
                                    <select name="feed_type" class="custom-select select" id="feed_type">
                                        <option value="">click here <span><i class="fa fa-angle-down" aria-hidden="true"></i></span></option>
                                        <?php
                                        foreach ($feed_types as $key => $data) {
                                            ?>
                                            <option value="<?php echo $data->id; ?>"><?php echo $data->title; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php
                            }else{
                                ?>
                                <h3>Something is wrong! Please contact customer support.</h3>
                                <?php
                            }
                            ?>
                        </div>

                        <div class="select-content" id="feedstepCont" style="display: none;">
                            <div class="all_cont_sec" id="cont_sec_1">
                                <div class="product-sec">
                                    <ul class="product-info">
                                        <li><p>Import <b>NEW</b> products from any formatted files</p> </li>
                                        <li><p>Variant grouping is configurable</p></li>
                                        <li><p>Import product images that are publically hosted</p></li>
                                        <li><p>Credits will be returned when the process undo</p></li>
                                    </ul>
                                </div>
                                <div class="cost-sec">
                                    <h3>How much dose it cost me?</h3>
                                    <ul class="cost-info">
                                        <li><p>Lorem Ipsum is simply <b>dummy text</b> of the printing</p> </li>
                                        <li><p>Lorem Ipsum is simply dummy text</p></li>
                                        <li><p>Lorem Ipsum has been the industry's standard dummy</p></li>
                                        <li><p>It has survived not only five centuries</p></li>
                                    </ul>
                                </div>
                                <p>Latin literature from 45 BC</p>
                                <p>purchase Stock Sync Credits<a href="#" class="more">here</a></p>
                            </div>
                            <div class="all_cont_sec" id="cont_sec_2">
                              <div class="product-sec">
                                  <ul class="product-info">
                                      <li><p>Works right away by <b>matching SKU, Barcode or others</b> (no import needed)</p> </li>
                                      <li><p>Keep inventory quantity level same as supplier or warehouse</p></li>
                                      <li><p>Adjust product pricing with flexible price condition</p></li>
                                      <li><p><b>50+ connection types</b> and <b>8 file formats</b> supported</p></li>
                                  </ul>
                              </div>
                              <div class="cost-sec">
                                  <h3>How much dose it cost me?</h3>
                                  <ul class="cost-info">
                                      <li><p>Lorem Ipsum is simply <b>dummy text</b> of the printing</p> </li>
                                      <li><p>Lorem Ipsum is simply dummy text</p></li>
                                      <li><p>Lorem Ipsum has been the industry's standard dummy</p></li>
                                      <li><p>It has survived not only five centuries</p></li>
                                  </ul>
                              </div>
                              <p>Latin literature from 45 BC</p>
                              <p>purchase Stock Sync Credits<a href="#" class="more">here</a></p>
                            </div>
                            <div class="all_cont_sec" id="cont_sec_3">
                              <div class="product-sec">
                                  <ul class="product-info">
                                      <li><p>Remove discontinued products</p> </li>
                                      <li><p>Remove process can't be reverted</p></li>
                                  </ul>
                              </div>
                              <div class="cost-sec">
                                  <h3>How much dose it cost me?</h3>
                                  <ul class="cost-info">
                                      <li><p>Lorem Ipsum is simply <b>dummy text</b> of the printing</p> </li>
                                      <li><p>Lorem Ipsum is simply dummy text</p></li>
                                      <li><p>Lorem Ipsum has been the industry's standard dummy</p></li>
                                      <li><p>It has survived not only five centuries</p></li>
                                  </ul>
                              </div>
                              <p>Latin literature from 45 BC</p>
                              <p>purchase Stock Sync Credits<a href="#" class="more">here</a></p>
                            </div>

                            <div class="buttn-sec">
                                <a href="#" class="learn-more">Learn More</a>
                                <input type="submit" class="btn btn-primary continue" name="stp1Bttn" value="Continue">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include('footer-script.php'); ?>

<script>
    $( document ).ready(function() {
        $( "#feed_type" ).change(function() {
            let rev_sgnl = $( this ).val();
            $('#feedstepCont').hide();
            $('.all_cont_sec').hide();
            if( rev_sgnl ){
                $('#feedstepCont').show();
                $('#cont_sec_'+rev_sgnl).show();
            }
        });
    });
</script>

</body>
</html>