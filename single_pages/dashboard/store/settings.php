<?php defined('C5_EXECUTE') or die("Access Denied.");?>
	    
	    <div class="ccm-dashboard-header-buttons">
            <a href="<?php echo View::url('/dashboard/store/settings/shipping')?>" class="btn btn-primary"><i class="fa fa-gift"></i> <?php echo t("Shipping Methods")?></a>
            <a href="<?php echo View::url('/dashboard/store/settings/payment')?>" class="btn btn-primary"><i class="fa fa-money"></i> <?php echo t("Payment Methods")?></a>
        </div>
	    
	    <form method="post" action="<?=$view->action('save')?>">
	        
            <div class="row">
                
                <div class="col-sm-4">
                    
                    <div class="vivid-store-side-panel">
                    
                        <ul>
                            <li><a href="#settings-currency" data-pane-toggle class="active"><?=t('Currency')?></a></li>
                            <li><a href="#settings-tax" data-pane-toggle><?=t('Tax')?></a></li>
                            <li><a href="#settings-shipping" data-pane-toggle><?=t('Shipping')?></a></li>
                            <li><a href="#settings-payments" data-pane-toggle><?=t('Payments')?></a></li>
                            <li><a href="#settings-notifications" data-pane-toggle><?=t('Notifications')?></a></li>
                            <li><a href="#settings-products" data-pane-toggle><?=t('Products')?></a></li>
                        </ul>
                    
                    </div>
                    
                </div>
                
                <div class="col-sm-7 store-pane active" id="settings-currency">
                    
                    <div class="form-group">
                        <?php echo $form->label('symbol',t('Currency Symbol')); ?>
                        <?php echo $form->text('symbol',$pkgconfig->get('vividstore.symbol'),array("style"=>"width:60px;"));?>
                    </div>
                    <div class="form-group">
                        <?php echo $form->label('thousand',t('Thousands Separator %se.g. , or a space%s', "<small>", "</small>")); ?>
                        <?php echo $form->text('thousand',$pkgconfig->get('vividstore.thousand'),array("style"=>"width:60px;"));?>
                    </div>
                    <div class="form-group">
                        <?php echo $form->label('whole',t('Whole Number Separator %se.g. period or a comma%s', "<small>", "</small>")); ?>
                        <?php echo $form->text('whole',$pkgconfig->get('vividstore.whole'),array("style"=>"width:60px;")); ?>
                    </div>
            
                </div><!-- #settings-currency -->
                
                <div class="col-sm-7 store-pane" id="settings-tax" data-states-utility="<?=View::url('/checkout/getstates')?>">
                
                    <h3><?=t("Your Store Location")?></h3>
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <?php echo $form->label('taxEnabled',t('Enable Tax')); ?>
                                <?php echo $form->select('taxEnabled',array('no'=>t('No'),'yes'=>t('Yes')),$pkgconfig->get('vividstore.taxenabled')); ?>
                            </div>                            
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="taxCountry"><?=t("Country")?></label>
                                <?php $country = $pkgconfig->get('vividstore.taxcountry'); ?>
                                <?php echo $form->select('taxCountry',$countries,$country?$country:'US',array("onchange"=>"updateTaxStates()")); ?>
                            </div>                            
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="taxState"><?=t("State/Region")?></label>
                                <?php $state = $pkgconfig->get('vividstore.taxstate'); ?>
                                <?php echo $form->select('taxState',$states,$state?$state:""); ?>
                                <?php echo $form->hidden("savedTaxState",$state); ?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <?=$form->label('taxCity',t("City"))?>
                                <?=$form->text('taxCity',$pkgconfig->get('vividstore.taxcity'));?>
                            </div>
                        </div>    
                    </div>
                    <hr>
                    <h3><?=t("Tax Rules")?></h3>
                    <div id="tax-rules-group" class="form form-inline">
                        <div class="form-group">
                            <label for="taxAddress"><?=t("I charge sales tax if the")?></label>
                            <?php echo $form->select('taxAddress',array('shipping'=>t("Shipping Address"),'billing'=>t("Billing Address")),$pkgconfig->get('vividstore.taxAddress')); ?>
                        </div>   
                        <div class="form-group">
                            <label for="taxMatch"><?=t("matches our")?></label>
                            <?php echo $form->select('taxMatch',array('state'=>t("State/Region"),'city'=>t("City"),'country'=>t("Country")),$pkgconfig->get('vividstore.taxMatch')); ?>
                        </div>    
                        <div class="form-group">
                            <label for="taxMatch"><?=t("based on the")?></label>
                            <?php echo $form->select('taxBased',array('subtotal'=>t("Product Total"),'grandtotal'=>t("Product Total + Shipping")),$pkgconfig->get('vividstore.taxBased')); ?>
                        </div>   
                    </div>
                    <div class="form-group">
                        <?php echo $form->label('taxRate',t('Tax Rate %')); ?>
                        <div class="input-group" style="width: 150px;">
                            <?php echo $form->text('taxRate',$pkgconfig->get('vividstore.taxrate')); ?>
                            <div class="input-group-addon">%</div>
                        </div>
                    </div>
                    
                </div>
                                
                <div class="col-sm-7 store-pane" id="settings-shipping">
                
                    <h3><?=t("Terminology")?></h3>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('weightUnit',t('Units for Weight'));?>
                                <?php echo $form->select('weightUnit',array('lb'=>t('lb'),'kg'=>t('kg')),$pkgconfig->get('vividstore.weightUnit'));?>
                            </div>
                        </div> 
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('sizeUnit',t('Units for Size'));?>
                                <?php echo $form->select('sizeUnit',array('in'=>t('in'),'cm'=>t('cm')),$pkgconfig->get('vividstore.sizeUnit'));?>
                            </div>
                        </div>                        
                    </div>
                    
            
                </div><!-- #settings-shipping -->
                    
                <div class="col-sm-7 store-pane" id="settings-payments">
                    
                    <?php
                        if($installedPaymentMethods){
                            foreach($installedPaymentMethods as $pm){?>
                            
                            <div class="panel panel-default">
                            
                                <div class="panel-heading"><?=$pm->getPaymentMethodName()?></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <input type="hidden" name="paymentMethodHandle[<?=$pm->getPaymentMethodID()?>]" value="<?=$pm->getPaymentMethodHandle()?>">
                                        <label><?=t("Enabled")?></label>
                                        <?php
                                            echo $form->select("paymentMethodEnabled[".$pm->getPaymentMethodID()."]", array(0=>"No",1=>"Yes"),$pm->isEnabled());
                                        ?>
                                    </div>
                                    <div class="form-group">
                                        <label><?=t("Display Name (on checkout)")?></label>
                                        <?php echo $form->text('paymentMethodDisplayName['.$pm->getPaymentMethodID().']',$pm->getPaymentMethodDisplayName()); ?>
                                    </div>
                                    <?php
                                        $pm->renderDashboardForm();
                                    ?>
                                </div>
                            
                            </div>
                            
                        <?php        
                            }
                        } else {
                            echo t("No Payment Methods are Installed");
                        }
                    ?>                                    
                    
            
                </div><!-- #settings-notifications -->
                
                <div class="col-sm-7 store-pane" id="settings-notifications">
                
                    <div class="form-group">
                        <?php echo $form->label('notificationEmails',t('Enter Emails to Notify of New Orders %sseparate multiple emails with commas%s', '<small class="text-muted">','</small>')); ?>
                        <?php echo $form->text('notificationEmails',$pkgconfig->get('vividstore.notificationemails'));?>
                    </div>
                    
                    <div class="form-group">
                        <?php echo $form->label('emailAlert',t('Email address to send alerts from'));?>
                        <?php echo $form->text('emailAlert',$pkgconfig->get('vividstore.emailalerts')); ?>
                    </div>
            
                </div><!-- #settings-notifications -->
                
                <div class="col-sm-7 store-pane" id="settings-products">
                
                    <div class="form-group">
                        <?php echo $form->label('productPublishTarget',t('Page to Publish Product Pages Under'));?>
                        <?=$pageSelector->selectPage('productPublishTarget',$productPublishTarget)?>
                    </div>
            
                </div><!-- #settings-shipping -->
                
            </div><!-- .row -->
                
    	    <div class="ccm-dashboard-form-actions-wrapper">
    	        <div class="ccm-dashboard-form-actions">
    	            <button class="pull-right btn btn-success" type="submit" ><?=t('Save Settings')?></button>
    	        </div>
    	    </div>

	    </form>