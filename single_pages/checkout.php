<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;

if ($controller->getTask() == "view" || $controller->getTask() == "failed") {
    ?>

<div class="clearfix">

    <div class="checkout-form-shell">
        <h1><?=t("Checkout")?></h1>

        <?php if ($controller->showLoginScreen) {
    ?>
        <div class="checkout-form-group active-form-group" id="checkout-form-group-signin">

            <h2><?=$controller->hasGuestCheckout() ? t('Sign in, Register or Checkout as Guest') : t('Sign in or Register')?></h2>
            <div class="checkout-form-group-body col-container clearfix">

                <div class="vivid-store-col-2">
                    <p><?=t("In order to proceed, you'll need to either register, or sign in with your existing account.")?></p>
                    <a class="btn btn-default" href="<?=View::url('/login')?>"><?=t("Sign In")?></a>
                    <?php if (Config::get('concrete.user.registration.enabled')) {
    ?>
                    <a class="btn btn-default" href="<?=View::url('/register')?>"><?=t("Register")?></a>
                    <?php 
}
    ?>
                </div>
                <?php if ($controller->hasGuestCheckout()) {
    ?>
                <div class="vivid-store-col-2">
                    <p><?=t("Or optionally, you may choose to checkout as a guest.")?></p>
                    <a class="btn btn-default" href="<?=View::url('/checkout/?guest=1')?>"><?=t("Checkout as Guest")?></a>
                </div>
                <?php 
}
    ?>

            </div>

        </div>
        <?php 
} else {
    ?>
        <form class="checkout-form-group active-form-group" id="checkout-form-group-billing" action="">

            <h2><?=t("Billing Address")?></h2>
            <div class="checkout-form-group-body col-container clearfix">
                <div class="clearfix">
                    <div class="vivid-store-col-2">
                        <div class="form-group">
                            <label for="checkout-billing-first-name"><?=t("First Name")?></label>
                            <?php echo $form->text('checkout-billing-first-name', $customer->getValue("billing_first_name"), array("required"=>"required"));
    ?>
                        </div>
                   </div>
                   <div class="vivid-store-col-2">
                        <div class="form-group">
                            <label for="checkout-billing-last-name"><?=t("Last Name")?></label>
                            <?php echo $form->text('checkout-billing-last-name', $customer->getValue("billing_last_name"), array("required"=>"required"));
    ?>
                        </div>
                    </div>
                </div>
                <div class="clearfix">
                    <div class="vivid-store-col-2">
                        <div class="form-group">
                            <label for="checkout-billing-first-name"><?=t("Company Name")?></label>
                            <?php echo $form->text('checkout-billing-company-name', $customer->getValue("billing_company_name"));
    ?>
                        </div>
                    </div>
                </div>
                <div class="clearfix">
                    <?php if ($customer->isGuest()) {
    ?>
                    <div class="vivid-store-col-2">
                        <div class="form-group">
                            <label for="email"><?=t("Email")?></label>
                            <?php echo $form->email('email', $customer->getEmail(), array("required"=>"required"));
    ?>
                        </div>
                    </div>
                    <?php 
}
    ?>
                    <div class="vivid-store-col-2">
                        <div class="form-group">
                            <label for="checkout-billing-phone"><?=t("Phone")?></label>
                            <?php echo $form->telephone('checkout-billing-phone', $customer->getValue("billing_phone"), array("required"=>"required"));
    ?>
                        </div>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-address-1"><?=t("Address 1")?></label>
                        <?php echo $form->text('checkout-billing-address-1', $customer->getValue("billing_address")->address1, array("required"=>"required"));
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-address-1"><?=t("Address 2")?></label>
                        <?php echo $form->text('checkout-billing-address-2', $customer->getValue("billing_address")->address2);
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-country"><?=t("Country")?></label>
                        <?php $country = $customer->getValue("billing_address")->country;
    ?>
                        <?php echo $form->select('checkout-billing-country', $billingCountries, $country?$country:($defaultBillingCountry ? $defaultBillingCountry : 'US'), array("onchange"=>"vividStore.updateBillingStates()"));
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-city"><?=t("City")?></label>
                        <?php echo $form->text('checkout-billing-city', $customer->getValue("billing_address")->city, array("required"=>"required"));
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-state"><?=t("State")?></label>
                        <?php $billingState = $customer->getValue("billing_address")->state_province;
    ?>
                        <?php echo $form->select('checkout-billing-state', $states, $billingState?$billingState:"");
    ?>
                        <input type="hidden" id="checkout-saved-billing-state" value="<?=$billingState?>">
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-zip"><?=t("Postal Code")?></label>
                        <?php echo $form->text('checkout-billing-zip', $customer->getValue("billing_address")->postal_code, array("required"=>"required"));
    ?>
                    </div>
                </div>

                <div class="checkout-form-group-buttons">
                    <input type="submit" class="btn btn-default btn-next-pane" value="<?=t("Next")?>">
                </div>

            </div>

            <div class="checkout-form-group-summary col-container clearfix ">
                <div class="vivid-store-col-2">
                    <label><?= t('Name');
    ?></label>
                    <p>
                        <span class="summary-name"></span><br>
                        <span class="summary-company"></span>
                    </p>

                    <label><?= t('Email');
    ?></label>
                    <p class="summary-email"></p>

                    <label><?= t('Phone');
    ?></label>
                    <p class="summary-phone"></p>
                </div>

                <div class="vivid-store-col-2">
                    <label><?= t('Address');
    ?></label>
                    <p class="summary-address"></p>
                </div>
            </div>

        </form>
        <?php if ($shippingEnabled) {
    ?>
        <form class="checkout-form-group" id="checkout-form-group-shipping">

            <h2><?=t("Shipping Address")?></h2>
            <div class="checkout-form-group-body col-container clearfix">
                <div class="vivid-store-col-1">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="ckbx-copy-billing">
                            <?=t("Same as Billing Address")?>
                        </label>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-first-name"><?=t("First Name")?></label>
                        <?php echo $form->text('checkout-shipping-first-name', $customer->getValue("shipping_first_name"), array("required"=>"required"));
    ?>
                    </div>
               </div>
               <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-last-name"><?=t("Last Name")?></label>
                        <?php echo $form->text('checkout-shipping-last-name', $customer->getValue("shipping_last_name"), array("required"=>"required"));
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-first-name"><?=t("Company Name")?></label>
                        <?php echo $form->text('checkout-shipping-company-name', $customer->getValue("shipping_company_name"));
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-address-1"><?=t("Address 1")?></label>
                        <?php echo $form->text('checkout-shipping-address-1', $customer->getValue("shipping_address")->address1, array("required"=>"required"));
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-address-1"><?=t("Address 2")?></label>
                        <?php echo $form->text('checkout-shipping-address-2', $customer->getValue("shipping_address")->address2);
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-country"><?=t("Country")?></label>
                        <?php $country = $customer->getValue("shipping_address")->country;
    ?>
                        <?php echo $form->select('checkout-shipping-country', $shippingCountries, $country?$country: ($defaultShippingCountry ? $defaultShippingCountry : 'US'), array("onchange"=>"vividStore.updateShippingStates()"));
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-city"><?=t("City")?></label>
                        <?php echo $form->text('checkout-shipping-city', $customer->getValue("shipping_address")->city, array("required"=>"required"));
    ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-state"><?=t("State")?></label>
                        <?php $shippingState = $customer->getValue("shipping_address")->state_province;
    ?>
                        <?php echo $form->select('checkout-shipping-state', $states, $shippingState?$shippingState:"");
    ?>
                        <input type="hidden" id="checkout-saved-shipping-state" value="<?=$shippingState?>">
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-zip"><?=t("Postal Code")?></label>
                        <?php echo $form->text('checkout-shipping-zip', $customer->getValue("shipping_address")->postal_code, array("required"=>"required"));
    ?>
                    </div>
                </div>

                <div class="checkout-form-group-buttons">
                    <a href="javascript:;" class="btn btn-default btn-previous-pane"><?=t("Previous")?></a>
                    <input type="submit" class="btn btn-default btn-next-pane" value="<?=t("Next")?>">
                </div>

            </div>
            <div class="checkout-form-group-summary col-container clearfix">
                <div class="vivid-store-col-2">
                    <label><?= t('Name');
    ?></label>
                    <p>
                        <span class="summary-name"></span><br>
                        <span class="summary-company"></span>
                    </p>

                </div>

                <div class="vivid-store-col-2">
                    <label><?= t('Address');
    ?></label>
                    <p class="summary-address"></p>
                </div>
            </div>
        </form>

        <form class="checkout-form-group" id="checkout-form-group-shipping-method">

            <h2><?=t("Shipping Method")?></h2>

            <div class="checkout-form-group-body">

                <div id="checkout-shipping-method-options">

                    <?php
                        /* shipping options are loaded in via ajax,
                         * since we dont know which shipping methods are available
                         * until after the shipping address fields are filled out.
                         */
                     ?>

                </div>

                <div class="checkout-form-group-buttons">
                    <a href="javascript:;" class="btn btn-default btn-previous-pane"><?=t("Previous")?></a>
                    <input type="submit" class="btn btn-default btn-next-pane" value="<?=t("Next")?>">
                </div>

            </div>

            <div class="checkout-form-group-summary col-container clearfix">
                <div class="vivid-store-col-2">
                    <p class="summary-shipping-method"></p>
                </div>
            </div>

        </form>

        <?php 
}
    ?>

        <form class="checkout-form-group" id="checkout-form-group-payment" method="post" action="<?=View::url('/checkout/submit')?>">

            <h2><?=t("Payment")?></h2>

            <div class="checkout-form-group-body">

                <?php
                    if ($enabledPaymentMethods) {
                        ?>
                <div class="col-container clearfix">
                    <div id="checkout-payment-method-options" class="vivid-store-col-1 <?php echo count($enabledPaymentMethods) == 1 ? "hidden" : "";
                        ?>">
                    <?php
                        $i = 1;
                        foreach ($enabledPaymentMethods as $pm) {
                            $props = array('data-payment-method-id' => $pm->getPaymentMethodID());
                            if ($i == 1) {
                                $props['checked'] = 'checked';
                            }
                            ?>
                            <div class='radio'>
                                <label>
                                    <?php
                                        $pmsess = Session::get('paymentMethod');
                            echo $form->radio('payment-method', $pm->getPaymentMethodHandle(), $pmsess[$pm->getPaymentMethodID()], $props);
                            echo $pm->getPaymentMethodDisplayName();
                            ?>
                                </label>
                            </div>
                    <?php
                            $i++;
                        }
                        ?>
                    </div>
                </div>
                <div class="alert alert-danger payment-errors <?php if ($controller->getTask()=='view') {
    echo "hidden";
}
                        ?>"><?=$paymentErrors?></div>
                <?php
                        foreach ($enabledPaymentMethods as $pm) {
                            echo "<div class=\"payment-method-container hidden\" data-payment-method-id=\"{$pm->getPaymentMethodID()}\">";
                            $pm->renderCheckoutForm();
                            echo "</div>";
                        }
                    } else {  //if payment methods
                ?>
                <p class="alert alert-warning"><?= t('There are currently no payment methods available to process your order.');
                        ?></p>
                <?php 
                    }
    ?>

                <div class="clearfix checkout-form-group-buttons">
                    <a href="javascript:;" class="btn btn-default btn-previous-pane"><?=t("Previous")?></a>

                    <?php if ($enabledPaymentMethods) {
    ?>
                    <input type="submit" class="btn btn-default btn-complete-order" value="<?=t("Complete Order")?>">
                    <?php 
}
    ?>
                </div>

            </div>

        </form>

        <?php 
}
    ?>

    </div><!-- .checkout-form-shell -->

    <div class="checkout-cart-view">
        <h2><?=t("Your Cart")?></h2>

        <?php $controller->getCartListElement();
    ?>

        <ul class="checkout-totals-line-items">
            <li class="line-item sub-total">
                <strong><?=t("Items Subtotal")?>:</strong> <?=Price::format($subtotal);
    ?>
                <?php if ($calculation == 'extract') {
    echo '<small class="text-muted">'.t("inc. taxes")."</small>";
}
    ?>
            </li>
            <div class="taxes">
                <?php
                    if ($taxtotal > 0) {
                        foreach ($taxes as $tax) {
                            if ($tax['taxamount']>0) {
                                ?>
                                <li class="line-item tax-item">
                                    <strong><?=($tax['name'] ? $tax['name'] : t("Tax"))?>:</strong>
                                    <span class="tax-amount"><?=Price::format($tax['taxamount']);
                                ?></span>
                                </li>
                            <?php 
                            }
                        }
                    }
    ?>
            </div>

            <?php if ($shippingEnabled) {
    ?>
            <li class="line-item shipping">
                <strong><?=t("Shipping")?>:</strong>
                <span id="shipping-total"><?=Price::format($shippingtotal);
    ?></span>
            </li>
            <?php 
}
    ?>
            <li class="line-item grand-total">
                <strong><?=t("Grand Total")?>:</strong>
                <span class="total-amount"><?=Price::format($total)?>
                </span>
            </li>
        </ul>

    </div>

</div>

<?php 
} elseif ($controller->getTask() == "external") {
    ?>
    <form id="checkout-redirect-form" action="<?=$action?>" method="post">
        <?php
        $pm->renderRedirectForm();
    ?>
        <input type="submit" class="btn btn-primary" value="<?=t('Click Here if You\'re not Redirected')?>">
    </form>
    <script type="text/javascript">
        $(function(){
           $("#checkout-redirect-form").submit();
        });
    </script>
<?php 
} else {
    ?>
    Hey. How did you get here?
<?php 
} ?>
