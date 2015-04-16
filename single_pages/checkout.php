<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<?php if($controller->getTask() == "view" || $controller->getTask() == "failed"){?>

<div class="clearfix">

    <div class="checkout-form-shell">
        
        <h1><?=t("Checkout")?></h1>
        
        <?php 
            $u = new User();
            if(!$u->isLoggedIn()){
        ?>
        <div class="checkout-form-group">
            
            <h2><?=t("Sign in or Register")?></h2>
            <p><?=t("In order to proceed, you'll need to either register, or login with your existing account.")?></p>
            <a class="btn btn-default" href="<?=View::url('/login')?>"><?=t("Login")?></a>
            <a class="btn btn-default" href="<?=View::url('/register')?>"><?=t("Register")?></a>
            
        </div>
        <?php } else { 
            $ui = UserInfo::getByID($u->getUserID());   
        ?>
        
        <form class="checkout-form-group" id="checkout-form-group-billing" action="">
            
            <h2><?=t("Billing Address")?></h2>
            <div class="checkout-form-group-body col-container clearfix">
                <div class="clearfix">
                    <div class="vivid-store-col-2">
                        <div class="form-group">
                            <label for="checkout-billing-first-name"><?=t("First Name")?></label>
                            <?php echo $form->text('checkout-billing-first-name',$ui->getAttribute("billing_first_name")); ?>
                        </div>
                   </div>     
                   <div class="vivid-store-col-2">
                        <div class="form-group">
                            <label for="checkout-billing-last-name"><?=t("Last Name")?></label>
                            <?php echo $form->text('checkout-billing-last-name',$ui->getAttribute("billing_last_name"),array("required"=>"required")); ?>
                        </div>
                    </div>
                </div>
                <div class="clearfix">
                    <div class="vivid-store-col-2">
                        <div class="form-group">
                            <label for="checkout-billing-phone"><?=t("Phone")?></label>
                            <?php echo $form->telephone('checkout-billing-phone',$ui->getAttribute("billing_phone"),array("required"=>"required")); ?>
                        </div>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-address-1"><?=t("Address 1")?></label>
                        <?php echo $form->text('checkout-billing-address-1',$ui->getAttribute("billing_address")->address1,array("required"=>"required")); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-address-1"><?=t("Address 2")?></label>
                        <?php echo $form->text('checkout-billing-address-2',$ui->getAttribute("billing_address")->address2); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-country"><?=t("Country")?></label>
                        <?php $country = $ui->getAttribute("billing_address")->country; ?>
                        <?php echo $form->select('checkout-billing-country',$countries,$country?$country:'US',array("onchange"=>"vividStore.updateBillingStates()")); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-city"><?=t("City")?></label>
                        <?php echo $form->text('checkout-billing-city',$ui->getAttribute("billing_address")->city,array("required"=>"required")); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-state"><?=t("State")?></label>
                        <?php $billingState = $ui->getAttribute("billing_address")->state_province; ?>
                        <?php echo $form->select('checkout-billing-state',$states,$billingState?$billingState:""); ?>
                        <input type="hidden" id="checkout-saved-billing-state" value="<?=$billingState?>">
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-billing-zip"><?=t("Postal Code")?></label>
                        <?php echo $form->text('checkout-billing-zip',$ui->getAttribute("billing_address")->postal_code,array("required"=>"required")); ?>
                    </div>
                </div>
                
                <div class="checkout-form-group-buttons">
                    <input type="submit" class="btn btn-default btn-next-pane" value="<?=t("Next")?>">
                </div>
                
            </div>
            
        </form>
        
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
                        <?php echo $form->text('checkout-shipping-first-name',$ui->getAttribute("shipping_first_name"),array("required"=>"required")); ?>
                    </div>
               </div>     
               <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-last-name"><?=t("Last Name")?></label>
                        <?php echo $form->text('checkout-shipping-last-name',$ui->getAttribute("shipping_last_name"),array("required"=>"required")); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-address-1"><?=t("Address 1")?></label>
                        <?php echo $form->text('checkout-shipping-address-1',$ui->getAttribute("shipping_address")->address1,array("required"=>"required")); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-address-1"><?=t("Address 2")?></label>
                        <?php echo $form->text('checkout-shipping-address-2',$ui->getAttribute("shipping_address")->address2); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-country"><?=t("Country")?></label>
                        <?php $country = $ui->getAttribute("shipping_address")->country; ?>
                        <?php echo $form->select('checkout-shipping-country',$countries,$country?$country:'US',array("onchange"=>"vividStore.updateShippingStates()")); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-city"><?=t("City")?></label>
                        <?php echo $form->text('checkout-shipping-city',$ui->getAttribute("shipping_address")->city,array("required"=>"required")); ?>
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-state"><?=t("State")?></label>
                        <?php $shippingState = $ui->getAttribute("shipping_address")->state_province; ?>
                        <?php echo $form->select('checkout-shipping-state',$states,$shippingState?$shippingState:""); ?>
                        <input type="hidden" id="checkout-saved-shipping-state" value="<?=$shippingState?>">
                    </div>
                </div>
                <div class="vivid-store-col-2">
                    <div class="form-group">
                        <label for="checkout-shipping-zip"><?=t("Postal Code")?></label>
                        <?php echo $form->text('checkout-shipping-zip',$ui->getAttribute("shipping_address")->postal_code,array("required"=>"required")); ?>
                    </div>
                </div>
                
                <div class="checkout-form-group-buttons">
                    <a href="javascript:;" class="btn btn-default btn-previous-pane"><?=t("Previous")?></a>
                    <input type="submit" class="btn btn-default btn-next-pane" value="<?=t("Next")?>">
                </div>
                
            </div>
            
        </form>
        
        <!--
        <div class="checkout-form-group" id="checkout-form-group-shipping-method">
            
            <h2><?=t("Shipping Method")?></h2>
            
        </div>
        -->
        
        <form class="checkout-form-group" id="checkout-form-group-payment" method="post" action="<?=View::url('/checkout/submit')?>">
            
            <h2><?=t("Payment")?></h2>
            
            <div class="checkout-form-group-body">
                
                <?php
                    if($enabledPaymentMethods){
                ?>
                <div class="col-container clearfix">
                    <div id="checkout-payment-method-options" class="vivid-store-col-1 <?php echo count($enabledPaymentMethods) == 1 ? "hidden" : ""; ?>">
                        <?php  
                            $i = 1;
                            foreach($enabledPaymentMethods as $pm):
                            if($i==1){
                                $props = array('data-payment-method-id'=>$pm->getPaymentMethodID(),'checked'=>'checked');    
                            } else {
                                $props = array('data-payment-method-id'=>$pm->getPaymentMethodID());       
                            }
                        ?>
                            <div class='radio'>
                                <label>  
                                    <?php $pmsess = Session::get('paymentMethod');   ?>
                                    <?=$form->radio('payment-method',$pm->getPaymentMethodHandle(),$pmsess[$pm->getPaymentMethodID()],$props)?>
                                    <?=$pm->getPaymentMethodDisplayName()?>
                                </label>
                            </div>       
                         <?php 
                            $i++;
                            endforeach;?>
                    </div>
                </div>
                <div class="alert alert-danger payment-errors <?php if($controller->getTask()=='view'){echo "hidden";} ?>"><?=$paymentErrors?></div>
                <?php
                        foreach($enabledPaymentMethods as $pm){
                            echo "<div class=\"payment-method-container hidden\" data-payment-method-id=\"{$pm->getPaymentMethodID()}\">";
                            $pm->renderCheckoutForm();
                            echo "</div>";
                        }
                    }//if payment methods
                ?>              
                
                <div class="clearfix checkout-form-group-buttons">
                    <a href="javascript:;" class="btn btn-default btn-previous-pane"><?=t("Previous")?></a>
                    <input type="submit" class="btn btn-default btn-complete-order" value="<?=t("Complete Order")?>">
                </div>
                
            </div>
            
        </form>
        
        <?php } ?>
        
    </div><!-- .checkout-form-shell -->
    
    <div class="checkout-cart-view">
        
        <h2><?=t("Cart Total")?></h2>
        <p>
            <strong><?=t("Items Subtotal")?>:</strong> <?=$subtotal?><br>
            <strong><?=t("Tax")?>:</strong> <span class="tax-amount"><?=$taxtotal?></span><br>
            <strong><?=t("Shipping")?>:</strong> <?=$shippingtotal?>
        </p>
        <p><strong><?=t("Grand Total")?>:</strong> <span class="total-amount"><?=$total?></span></p>
        
    </div>

</div>

<?php } else { ?>
    Hey. How did you get here?
<?php } ?>
