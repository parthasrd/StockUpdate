<header id="js-header">
    <nav id="stuck_container" class="navbar navbar-inverse stuck_container">
        <div class="container">
            <div class="nav-header">
                <div class="navbar-header">
                    <!--<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">-->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="slide-collapse" data-target="#slide-navbar-collapse" aria-expanded="false">
                         <span class="navTrigger">
                            <i></i><i></i><i></i>
                         </span>
                    </button>
                    <a class="navbar-brand" href="<?php echo conf::SITE_URL; ?>"><img class="img-responsive" src="assets/images/sync-itz-logo.png" alt="logo"></a>
                </div>
                  <!-- /.navbar-collapse -->
                <div class="collapse navbar-collapse" id="slide-navbar-collapse">
                   <div class="btn-group profile">
                       <div class="avtar-img" style="background-image: url(assets/images/avter-profile-img.jpg)"></div>
                        <button type="button" class="dropdown-toggle profile-buttn" data-toggle="dropdown"><?php echo $store_name; ?> <span><i class="fa fa-angle-down" aria-hidden="true"></i></span></button>
                        <div class="dropdown-menu">
                            <a href="profile.php" class="dropdown-item">Store Infomation</a>
                            <a href="signout.php" class="dropdown-item">Sign Out</a>
                        </div>
                   </div>
                   <a class="navbar-brand mobile_view_logo" href="<?php echo conf::SITE_URL; ?>"><img class="img-responsive" src="assets/images/sync-itz-logo.png" alt="logo"></a>
                </div>
                <div class="menu-overlay"></div>
            </div>
         </div>
    </nav>
</header>