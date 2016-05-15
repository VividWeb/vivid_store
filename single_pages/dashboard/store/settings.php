<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
	    
	    <div class="ccm-dashboard-header-buttons">
            <a href="<?php echo View::url('/dashboard/store/settings/shipping')?>" class="btn btn-primary"><i class="fa fa-gift"></i> <?php echo t("Shipping Methods")?></a>
            <a href="<?php echo View::url('/dashboard/store/settings/tax')?>" class="btn btn-primary"><i class="fa fa-money"></i> <?php echo t("Tax Rates")?></a>
        </div>
	    
	    <form method="post" action="<?php echo $view->action('save')?>">
	        
            <div class="row">
                <div class="col-sm-4">
                    <div class="vivid-store-side-panel">
                        <ul>
                            <li><a href="#settings-currency" data-pane-toggle class="active"><?php echo t('Currency')?></a></li>
                            <li><a href="#settings-products" data-pane-toggle><?php echo t('Products')?></a></li>
                            <li><a href="#settings-tax" data-pane-toggle><?php echo t('Tax')?></a></li>
                            <li><a href="#settings-shipping" data-pane-toggle><?php echo t('Shipping')?></a></li>
                            <li><a href="#settings-payments" data-pane-toggle><?php echo t('Payments')?></a></li>
                            <li><a href="#settings-order-statuses" data-pane-toggle><?php echo t('Order Statuses')?></a></li>
                            <li><a href="#settings-notifications" data-pane-toggle><?php echo t('Notifications')?></a></li>
                            <li><a href="#settings-checkout" data-pane-toggle><?php echo t('Checkout')?></a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-sm-7 store-pane active" id="settings-currency">
                    <h3><?php echo t('Currency Settings');?></h3>

                    <div class="form-group">
                        <?php echo $form->label('symbol', t('Currency Symbol')); ?>
                        <?php echo $form->text('symbol', Config::get('vividstore.symbol'), array("style"=>"width:80px;"));?>
                    </div>
                    <div class="form-group">
                        <?php echo $form->label('thousand', t('Thousands Separator %se.g. , or a space%s', "<small>", "</small>")); ?>
                        <?php echo $form->text('thousand', Config::get('vividstore.thousand'), array("style"=>"width:60px;"));?>
                    </div>
                    <div class="form-group">
                        <?php echo $form->label('whole', t('Whole Number Separator %se.g. period or a comma%s', "<small>", "</small>")); ?>
                        <?php echo $form->text('whole', Config::get('vividstore.whole'), array("style"=>"width:60px;")); ?>
                    </div>
            
                </div><!-- #settings-currency -->
                
                <div class="col-sm-7 store-pane" id="settings-tax">
                    <h3><?php echo t('Tax Settings');?></h3>

                    <div class="form-group">
                        <label for="calculation"><?php echo t("Are Prices Entered with Tax Included?")?></label>
                        <?php echo $form->select('calculation', array('add'=>t("No, I will enter product prices EXCLUSIVE of tax"), 'extract'=>t("Yes, I will enter product prices INCLUSIVE of tax")), Config::get('vividstore.calculation')); ?>
                    </div>
                    
                </div>
                                                
                <div class="col-sm-7 store-pane" id="settings-shipping">
                
                    <h3><?php echo t("Shipping Units")?></h3>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('weightUnit', t('Units for Weight'));?>
                                <?php // do not add other units to this list. these are specific to making calculated shipping work ?>
                                <?php echo $form->select('weightUnit', array('lb'=>t('lb'), 'kg'=>t('kg'), 'g'=>t('g')), Config::get('vividstore.weightUnit'));?>
                            </div>
                        </div> 
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('sizeUnit', t('Units for Size'));?>
                                <?php // do not add other units to this list. these are specific to making calculated shipping work ?>
                                <?php echo $form->select('sizeUnit', array('in'=>t('in'), 'cm'=>t('cm'), 'mm'=>t('mm')), Config::get('vividstore.sizeUnit'));?>
                            </div>
                        </div>                        
                    </div>
                    
            
                </div><!-- #settings-shipping -->
                    
                <div class="col-sm-7 store-pane" id="settings-payments">
                    <h3><?php echo t("Payment Methods")?></h3>
                    <?php
                        if ($installedPaymentMethods) {
                            foreach ($installedPaymentMethods as $pm) {
                                ?>
                            
                            <div class="panel panel-default">
                            
                                <div class="panel-heading"><?php echo $pm->getPaymentMethodName()?></div>
                                <div class="panel-body">
                                    <div class="form-group paymentMethodEnabled">
                                        <input type="hidden" name="paymentMethodHandle[<?php echo $pm->getPaymentMethodID()?>]" value="<?php echo $pm->getPaymentMethodHandle()?>">
                                        <label><?php echo t("Enabled")?></label>
                                        <?php
                                            echo $form->select("paymentMethodEnabled[".$pm->getPaymentMethodID()."]", array(0=>"No", 1=>"Yes"), $pm->isEnabled());
                                ?>
                                    </div>
                                    <div id="paymentMethodForm-<?php echo $pm->getPaymentMethodID();
                                ?>" style="display:<?php echo $pm->isEnabled() ? 'block':'none';
                                ?>">
                                        <div class="form-group">
                                            <label><?php echo t("Display Name (on checkout)")?></label>
                                            <?php echo $form->text('paymentMethodDisplayName['.$pm->getPaymentMethodID().']', $pm->getPaymentMethodDisplayName());
                                ?>
                                        </div>
                                        <?php
                                            $pm->renderDashboardForm();
                                ?>
                                    </div>
                                </div>
                            
                            </div>
                            
                        <?php 
                            }
                        } else {
                            echo t("No Payment Methods are Installed");
                        }
                    ?>                                    
                    
                    <script>
                        $(function(){
                            $('.paymentMethodEnabled SELECT').on('change',function(){
                                $this = $(this);
                                if ($this.val()==1) {
                                    $this.parent().next().slideDown();
                                } else {
                                    $this.parent().next().slideUp();
                                }
                            });
                        });
                    </script>
                </div><!-- #settings-payments -->

                <div class="col-sm-7 store-pane" id="settings-order-statuses">
                    <h3><?php echo t("Order Statuses")?></h3>
                    <?php
                    if (count($orderStatuses)>0) {
                        ?>
                        <div class="panel panel-default">

                            <table class="table" id="orderStatusTable">
                                <thead>
                                <tr>
                                    <th rowspan="1">&nbsp;</th>
                                    <th rowspan="1"><?php echo t('Display Name');
                        ?></th>
                                    <th rowspan="1"><?php echo t('Default Status');
                        ?></th>
                                    <th colspan="2" style="display:none;"><?php echo t('Send Change Notifications to...');
                        ?></th>
                                </tr>
                                <tr style="display:none;">
                                    <th><?php echo t('Site');
                        ?></th>
                                    <th><?php echo t('Customer');
                        ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($orderStatuses as $orderStatus) {
    ?>
                                    <tr>
                                        <td class="sorthandle"><input type="hidden" name="osID[]" value="<?php echo $orderStatus->getID();
    ?>"><i class="fa fa-arrows-v"></i></td>
                                        <td><input type="text" name="osName[]" value="<?php echo $orderStatus->getName();
    ?>" placeholder="<?php echo $orderStatus->getReadableHandle();
    ?>" class="form-control ccm-input-text"></td>
                                        <td><input type="radio" name="osIsStartingStatus" value="<?php echo $orderStatus->getID();
    ?>"  <?php echo $orderStatus->isStartingStatus() ? 'checked':'';
    ?>></td>
                                        <td style="display:none;"><input type="checkbox" name="osInformSite[]" value="1" <?php echo $orderStatus->informsSite() ? 'checked':'';
    ?> class="form-control"></td>
                                        <td style="display:none;"><input type="checkbox" name="osInformCustomer[]" value="1" <?php echo $orderStatus->informsCustomer() ? 'checked':'';
    ?> class="form-control"></td>
                                    </tr>
                                <?php 
}
                        ?>
                                </tbody>
                            </table>
                            <script>
                                $(function(){
                                    $('#orderStatusTable TBODY').sortable({
                                        cursor: 'move',
                                        opacity: 0.5,
                                        handle: '.sorthandle'
                                    });

                                });

                            </script>

                        </div>

                    <?php

                    } else {
                        echo t("No Order Statuses are available");
                    }
                    ?>


                </div><!-- #settings-order-statuses -->

                <div class="col-sm-7 store-pane" id="settings-notifications">
                    <h3><?php echo t('Notification Emails');?></h3>

                    <div class="form-group">
                        <?php echo $form->label('notificationEmails', t('Send order notification to email %sseparate multiple emails with commas%s', '<small class="text-muted">', '</small>')); ?>
                        <?php echo $form->text('notificationEmails', Config::get('vividstore.notificationemails'), array('placeholder'=>t('Email Address')));?>
                    </div>

                    <h4><?php echo t('Emails Sent From');?></h4>

                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('emailAlert', t('From Email'));?>
                                <?php echo $form->text('emailAlert', Config::get('vividstore.emailalerts'), array('placeholder'=>t('From Email Address'))); ?>
                            </div>
                        </div>

                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('emailAlertName', t('From Name'));?>
                                <?php echo $form->text('emailAlertName', Config::get('vividstore.emailalertsname'), array('placeholder'=>t('From Name'))); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- #settings-products -->
                <div class="col-sm-7 store-pane" id="settings-products">
                    <h3><?php echo t("Order Statuses")?></h3>
                    <div class="form-group">
                        <?php echo $form->label('productPublishTarget', t('Page to Publish Product Pages Under'));?>
                        <?php echo $pageSelector->selectPage('productPublishTarget', $productPublishTarget)?>
                    </div>
            
                </div>

                <!-- #settings-customers -->
                <div class="col-sm-7 store-pane" id="settings-checkout">

                    <h3><?php echo t('Guest checkout');?></h3>
                    <div class="form-group">
                        <?php $guestCheckout =  Config::get('vividstore.guestCheckout');
                        $guestCheckout = ($guestCheckout ? $guestCheckout : 'off');
                        ?>
                        <label><?php echo $form->radio('guestCheckout', 'off', $guestCheckout == 'off' || $guestCheckout == ''); ?> <?php  echo t('Disabled'); ?></label><br />
                        <label><?php echo $form->radio('guestCheckout', 'option', $guestCheckout == 'option'); ?> <?php  echo t('Offer as checkout option'); ?></label><br />
                        <label><?php echo $form->radio('guestCheckout', 'always', $guestCheckout == 'always'); ?> <?php  echo t('Always (unless login required for products in cart)'); ?></label><br />

                    </div>

                </div>

            </div><!-- .row -->
                
    	    <div class="ccm-dashboard-form-actions-wrapper">
    	        <div class="ccm-dashboard-form-actions">
    	            <button class="pull-right btn btn-success" type="submit" ><?php echo t('Save Settings')?></button>
    	        </div>
    	    </div>

	    </form>