<?php
$header_avatar = null;
$header_pict = get_active_user('pict');
if (!empty($header_pict)) {
    $header_pict = ltrim(str_replace('\\', '/', $header_pict), '/');
    if (preg_match('#^uploads/(files|photos)/[^/]+\.(jpe?g|png|webp)$#i', $header_pict)) {
        $header_file = realpath(ROOT . $header_pict); $header_root = realpath(ROOT . 'uploads');
        if ($header_file && $header_root && strpos($header_file, $header_root . DIRECTORY_SEPARATOR) === 0 && is_file($header_file)) { $header_avatar = get_link($header_pict); }
    }
}
?>
<div id="topbar" class="navbar navbar-expand-md fixed-top navbar-dark bg-primary">
    <div class="container-fluid">
        <?php 
        if(user_login_status() == true ){ 
        ?>
        <button type="button" id="sidebarCollapse" class="btn btn-kalbe-hamburger mr-3">
            <span class="navbar-toggler-icon"></span>
        </button>
        <?php 
        } 
        ?>
        <a class="navbar-brand" href="<?php print_link(HOME_PAGE) ?>">
            <img class="img-responsive" src="<?php print_link(SITE_LOGO . '?v=' . time()); ?>" style="max-height: 38px; margin-right: 8px; vertical-align: middle;" /> <?php echo SITE_NAME ?>
        </a>
        <?php 
        if(user_login_status() == true ){ 
        ?>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target=".navbar-responsive-collapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse navbar-responsive-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        <span class="avatar-icon"><?php if ($header_avatar) { ?><img src="<?php echo htmlspecialchars($header_avatar, ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover"><?php } else { ?><i class="fa fa-user"></i><?php } ?></span>
                        <span><?php echo ucwords(get_active_user('nama', USER_NAME)); ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <a class="dropdown-item" href="<?php print_link('account') ?>"><i class="fa fa-user"></i> My Account</a>
                        <a class="dropdown-item" href="<?php print_link('index/logout?csrf_token=' . Csrf::$token) ?>"><i class="fa fa-sign-out"></i> Logout</a>
                    </ul>
                </li>
            </ul>
        </div>
        <?php 
        } 
        ?>
    </div>
</div>
<?php 
if(user_login_status() == true ){ 
?>
<nav id="sidebar">
    <div class="sidebar-menu-wrapper pt-3">
        <?php Html :: render_menu(Menu :: $navbarsideleft  , "nav navbar-nav w-100 flex-column align-self-start"  , "collapse"); ?>
    </div>
</nav>
<?php 
} 
?>