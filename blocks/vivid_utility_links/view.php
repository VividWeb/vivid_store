<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<div class="vivid-store-utility-links">
    <?php if($showSignIn){
        $u = new User();
        if($u->isLoggedIn()){
            $msg = "<span class=\"welcome-message\">".t("Welcome back")."</span>";
            $ui = UserInfo::getByID($u->getUserID());
            if($firstname = $ui->getAttribute('billing_first_name')){
                $msg = "<span class=\"welcome-message\">".t("Welcome back,")."<span class=\"first-name\">".$firstname."</span></span>";
            }
            echo $msg;
        } else {
            echo '<a href="'.URL::to('/login').'">'.t("Sign In").'</a>';
        }
    } ?>
    <?php if($showCartItems){?>
    <span class="items-in-cart"><?=$itemsLabel?> (<span class="items-counter"><?=$itemCount?></span>)</span>
    <?php } ?>
    <a href="<?=View::url('/cart')?>" class="cart-link"><?=$cartLabel?></a>
</div>
