<?php 
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\VividStore\Src\VividStore\Discount\DiscountRule;

$form = Core::make('helper/form');
$date = Core::make('helper/form/date_time');
$dfh = Core::make('helper/date');

$listViews = array('view','updated','removed','success', 'deleted');
$addViews = array('add','edit','save');
$codeViews = array('codes', 'addcodes');

$currencySymbol = Config::get('vividstore.symbol');

?>


<?php if (in_array($controller->getTask(), $listViews)){ ?>
    <div class="ccm-dashboard-header-buttons">
        <a href="<?php echo View::url('/dashboard/store/discounts/', 'add')?>" class="btn btn-primary"><?php echo t("Add Discount Rule")?></a>
	</div>


    <div class="ccm-dashboard-content-full">
        <table class="ccm-search-results-table">
            <thead>
                <th><a><?=t('Name')?></a></th>
                <th><a><?=t('Display')?></a></th>
                <th><a><?=t('Discount')?></a></th>
                <th><a><?=t('Applies')?></a></th>
                <th><a><?=t('Availability')?></a></th>
                <th><a><?=t('Enabled')?></a></th>
                <th><a><?=t('Actions')?></a></th>
            </thead>
            <tbody>

                <?php if(count($discounts)>0) {
                    foreach ($discounts as $d) {
                        ?>
                        <tr>
                            <td><strong><a href="<?php echo View::url('/dashboard/store/discounts/edit/', $d->drID)?>"><?= h($d->drName); ?></a></strong></td>
                            <td><?= h($d->drDisplay); ?></td>
                            <td>
                                <?php if ($d->drDeductType == 'percentage') {
                                   echo  h($d->drPercentage) . '% ' . t('from') . ' ' . h($d->drDeductFrom);
                                } else {
                                    echo $currencySymbol .  h($d->drValue) . ' ' . t('from') . ' ' . h($d->drDeductFrom);
                                }
                                ?>
                            </td>
                            <td><?php

                                if ($d->drTrigger == 'auto') {
                                    echo '<span class="label label-warning">' . t('automatically') . '</span><br />';
                                } else {



                                    if ($d->drSingleUseCodes) {
                                        echo '<span class="label label-primary">' . t('when single use code entered'). '</span><br />';
                                        echo '<span class="label ' .  ($d->availableCodes <= 0 ? 'label-danger' : 'label-primary'). '">' . $d->availableCodes . ' ' . t('of') . ' ' . $d->totalCodes . ' ' . t('codes available') . '</span><br />';
                                    } else {
                                        echo '<span class="label label-primary">' . t('when code entered'). '</span><br />';
                                        echo '<span class="label ' .  ($d->availableCodes <= 0 ? 'label-danger' : 'label-primary') . '">' . $d->availableCodes . ' ' . ($d->availableCodes == 1 ? t('code') : t('codes')) .' '.  t('configured') . '</span><br />';
                                    }

                                }

                                if ($d->drExclusive) {
                                    echo '<span class="label label-info">' . t('exclusively') . '</span>';
                                }

                                 ?></td>
                            <td>
                                <?php
                                $restrictions = '';

                                if ($d->drValidFrom > 0) {
                                    $restrictions .= ' ' . t('starts') . ' ' . $dfh->formatDateTime($d->drValidFrom);
                                }

                                if ($d->drValidTo > 0) {
                                    $restrictions .= ' '. t('expires') . ' ' . $dfh->formatDateTime($d->drValidTo);
                                }

                                if (!$restrictions) {
                                    $restrictions = t('always');
                                }


                                echo trim($restrictions);

                                ?>


                            </td>
                            <td>
                                <?php if($d->drEnabled){ ?>
                                    <span class="label label-danger"><?=t('Disabled')?></span>
                                <?php } else { ?>
                                    <span class="label label-success"><?=t('Enabled')?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <p><a class="btn btn-default" href="<?php echo View::url('/dashboard/store/discounts/edit/', $d->drID)?>"><i class="fa fa-pencil"></i></a></p>
                                <?php
                                if ($d->drTrigger == 'code') {
                                    echo '<p>' . '<a class="btn btn-default btn-sm" href="'.View::url('/dashboard/store/discounts/codes/', $d->drID).'">'.t('Manage Codes').'</a></p>';
                                } ?>

                            </td>
                        </tr>
                    <?php }
                }?>
            </tbody>
        </table>


        <?php if ($paginator->getTotalPages() > 1) { ?>
            <?= $pagination ?>
        <?php } ?>

    </div>


<style>
    @media (max-width: 992px) {
        div#ccm-dashboard-content div.ccm-dashboard-content-full {
            margin-left: -20px !important;
            margin-right: -20px !important;
        }
    }
</style>

<?php } ?>

<?php if (in_array($controller->getTask(), $addViews)){ ?>

    <?php if ($controller->getTask() == 'edit') { ?>
        <div class="ccm-dashboard-header-buttons">
            <form method="post" id="deleterule" action="<?php echo View::url('/dashboard/store/discounts/delete/')?>">
                <input type="hidden" name="drID" value="<?= $d->drID; ?>" />
                <button class="btn btn-danger" ><?php echo t('Delete'); ?></button>
            </form>
        </div>
    <?php } ?>


    <form method="post" action="<?php echo $this->action('save')?>" id="discount-add">


    <div class="ccm-pane-body">

        <?php if(!is_object($d)){
            $d = new DiscountRule(); //does nothing other than shutup errors.
            $d->drTrigger = 'auto';
            $d->drDeductType = 'percentage';
        }
        ?>

        <input type="hidden" name="drID" value="<?=$d->drID?>"/>

        <div class="form-group">
            <?php echo $form->label('drName', t('Name'))?>
            <?php echo $form->text('drName', $d->drName, array('class' => '', 'required'=>'required'))?>
        </div>

        <div class="form-group">
            <?php echo $form->label('drEnabled', t('Enabled'))?>
            <?php echo $form->select('drEnabled', array('1'=>t('Enabled'), '0'=>t('Disabled')), $d->drEnabled, array('class' => ''))?>
        </div>

        <div class="form-group">
            <?php echo $form->label('drDisplay', t('Display Text'))?>
            <?php echo $form->text('drDisplay', $d->drDisplay, array('class' => '', 'required'=>'required'))?>
        </div>



        <div class="form-group">
            <div class="row">
                <div class="col-md-3">
                    <?php echo $form->label('drDeductType', t('Deduction Type'))?>
                    <div class="radio"><label><?php echo $form->radio('drDeductType', 'percentage', ($d->drDeductType == 'percentage'))?> <?php echo t('Percentage'); ?></label></div>
                    <div class="radio"><label><?php echo $form->radio('drDeductType', 'value', ($d->drDeductType == 'value'))?> <?php echo t('Value'); ?></label></div>
                </div>
                <div class="col-md-9 row">
                    <div class="form-group col-md-4"  id="percentageinput"  <?php echo ($d->drDeductType == 'value' ? 'style="display: none;"' : ''); ?>>
                        <?php echo $form->label('drPercentage', t('Percentage Discount'))?>
                        <div class="input-group">
                            <?php echo $form->text('drPercentage', $d->drPercentage, array('class' => ''))?>
                            <div class="input-group-addon">%</div>
                        </div>
                    </div>

                    <div class="form-group col-md-4" id="valueinput" <?php echo ($d->drDeductType == 'percentage' ? 'style="display: none;"' : ''); ?>>
                        <?php echo $form->label('drValue', t('Value Discount'))?>
                        <div class="input-group">
                            <div class="input-group-addon"><?php echo $currencySymbol; ?></div>
                            <?php echo $form->text('drValue', $d->drValue, array('class' => ''))?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="form-group">
            <?php echo $form->label('drDeductFrom', t('Deduct From'))?>
            <?php
            // commenting out following until product and product group matching is implemented
            //echo $form->select('drDeductFrom', array('total' => t('Total, including shipping'), 'subtotal'=>'Items Sub-total', 'shipping' => t('Shipping'), 'product'=> t('Specific Product'), 'group'=> t('Products in Product Group')), $d->drDeductFrom, array('class' => ''))?>
            <?php echo $form->select('drDeductFrom', array('total' => t('Total, including shipping'), 'subtotal'=>'Items Sub-total', 'shipping' => t('Shipping')), $d->drDeductFrom, array('class' => ''))?>
        </div>

        <div class="form-group">
            <?php echo $form->label('drTrigger', t('Apply'))?>
            <div class="radio"><label><?php echo $form->radio('drTrigger', 'auto', ($d->drTrigger == 'auto'))?> <?php echo t('Automatically, (when matching all restrictions)'); ?></label></div>
            <div class="radio"><label><?php echo $form->radio('drTrigger', 'code', ($d->drTrigger == 'code'))?> <?php echo t('When valid code entered'); ?></label></div>
        </div>

        <div id="codefields" <?php echo ($d->drTrigger == 'auto' ? 'style="display: none;"' : ''); ?>>
            <div class="form-group">
                <label for="drSingleUseCodes"><?php echo $form->checkbox('drSingleUseCodes', '1',$d->drSingleUseCodes)?> <?php echo t('Single use codes'); ?></label>
            </div>
            <?php if (!$d->drID) { ?>
            <p class="alert alert-info"><?= t('Codes can be entered after creating rule');?></p>
            <?php } ?>
        </div>

<!--       <field name="drCurrency" type="C" size="20"></field>-->

        <fieldset><legend><?= t('Restrictions');?></legend>

        <div class="form-group">
            <?php echo $form->checkbox('drExclusive', '1', $d->drExclusive)?>
            <?php echo $form->label('drExclusive', t('Discount is Exclusive'))?>
            <span class="help-block"><?= t('When checked, discount cannot be used in conjunction with another discount'); ?></span>
        </div>

        <div class="form-group">

            <?php echo $form->label('drValidFrom', t('Starts'))?>
            <div class="row">
                <div class="col-md-4">
                    <?php echo $form->select('validFrom', array('0'=>t('Immedately'), '1'=>t('From a specified date')), ($d->drValidFrom > 0 ? '1' : '0'), array('class' => 'col-md-4'))?>
                </div>
                <div class="col-md-8" id="fromdate" <?php echo ($d->drValidFrom ? '' : 'style="display: none;"'); ?>>
                    <?php echo $date->datetime('drValidFrom', $d->drValidFrom)?>
                </div>
            </div>

        </div>

        <div class="form-group">
            <?php echo $form->label('drValidTo', t('Ends'))?>
            <div class="row">
                <div class="col-md-4">
                    <?php echo $form->select('validTo', array('0'=>t('Never'), '1'=>t('At a specified date')),  ($d->drValidTo > 0 ? '1' : '0'), array('class' => 'col-md-4'))?>
                </div>
                <div class="col-md-8" id="todate" <?php echo ($d->drValidTo ? '' : 'style="display: none;"'); ?>>
                     <?php echo $date->datetime('drValidTo', $d->drValidTo)?>
                </div>
            </div>
        </div>

<!--        <h4>--><?php //echo t('Users / Groups');?><!--</h4>-->
<!--        <p><em>To be implemented</em></p>-->

        </fieldset>

        <div class="form-group">
            <?php echo $form->label('drDescription', t('Description / Notes'))?>
            <?php echo $form->textarea('drDescription', $d->drDescription, array('class' => 'span5'))?>
        </div>



    </div>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?php echo URL::to('/dashboard/store/discounts')?>" class="btn btn-default"><?php echo t('Cancel')?></a>
            <button class="pull-right btn btn-primary" type="submit"><?php echo ($d->drID > 0 ? t('Update') : t('Add'))?></button>
        </div>
    </div>

</form>

    <script>
        $(function(){
            $('#deleterule').submit(function(e){
                return confirm("<?= t('Are you sure you want to delete this discount rule?');?>");
            });

            $('#drDeductType1').click(function() {
                if($('#drDeductType1').is(':checked')) {
                    $('#percentageinput').show();
                    $('#valueinput').hide();
                }
            });

            $('#drDeductType2').click(function() {
                if($('#drDeductType2').is(':checked')) {
                    $('#percentageinput').hide();
                    $('#valueinput').show();
                }
            });

            $('#drTrigger3').click(function() {
                if($('#drTrigger3').is(':checked')) {
                    $('#codefields').hide();
                }
            });

            $('#drTrigger4').click(function() {
                if($('#drTrigger4').is(':checked')) {
                    $('#codefields').show();
                }
            });

            $('#validFrom').change(function() {
                if ($(this).val() == '1') {
                    $('#fromdate').show();
                } else {
                    $('#fromdate').hide();
                }
            });

            $('#validTo').change(function() {
                if ($(this).val() == '1') {
                    $('#todate').show();
                } else {
                    $('#todate').hide();
                }
            });
        });
    </script>


<?php } ?>



<?php if (in_array($controller->getTask(), $codeViews)){ ?>
<div class="ccm-dashboard-header-buttons">
    <a href="<?php echo View::url('/dashboard/store/discounts/edit', $d->drID)?>" class="btn btn-default"><?php echo t("Edit Discount Rule")?></a>
</div>


<?php if (isset($successCount)) { ?>
<p class="alert alert-success"><?= $successCount . ' ' . ($successCount == 1 ? t('code added') : t('codes added')); ?></p>
<?php } ?>

<?php if (isset($failedcodes) && count($failedcodes) > 0 ) { ?>
    <p class="alert alert-warning"><?= t('The following codes are were invalid or are already active:')  ?><br />
        <strong><?= implode('<br />', $failedcodes); ?></strong>
    </p>
<?php } ?>



<fieldset><legend><?= t('Current Codes'); ?></legend></fieldset>

<p class="alert alert-info">
    <?php if ($d->drSingleUseCodes) { ?>
        <?= t('Single Use Codes'); ?></p>
    <?php } else { ?>
        <?= t('Codes can be repeatedly used'); ?>
    <?php } ?>
</p>


<?php if (!empty($codes)) { ?>
        <table class="table table-bordered">
            <tr><th><?= t('Code'); ?></th>

                <?php if ($d->drSingleUseCodes) { ?>
                <th><?=  t('Used'); ?></th>
                <?php } ?>

                <th></th></tr>

            <?php foreach($codes as $code) { ?>
                    <?php if ($d->drSingleUseCodes) { ?>

                        <?php if ($code['oDate']) { ?>
                            <tr>
                                <td><strike><?= $code['dcCode']; ?></strike></td>
                                <td><?php echo $code['oDate']; ?> <a class="btn btn-default btn-xs" href="<?= View::url('/dashboard/store/orders/order/', $code['oID']); ?>"><?= t('View Order'); ?></a></td>
                                <td></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td><?= $code['dcCode']; ?></td>
                                <td><span class="label label-success"><?= t('Available'); ?></span></td>
                            <td>
                                <form method="post" action="<?= View::url('/dashboard/store/discounts/deletecode/')?>">
                                    <input type="hidden" name="dcID" value="<?= $code['dcID'];?>" />
                                    <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                                </td>
                                </tr>
                        <?php }  ?>
                    <?php } else { ?>
                        <tr>
                            <td><?= $code['dcCode']; ?></td>
                            <td>
                                <form method="post" action="<?php echo View::url('/dashboard/store/discounts/deletecode/')?>">
                                    <input type="hidden" name="dcID" value="<?php echo $code['dcID']?>" />
                                    <button class="btn btn-danger" ><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>


            <?php } ?>

        </table>

    <?php } else { ?>
    <p><?= t('No codes specified');?></p>

    <?php } ?>
<br />
<form method="post" action="<?php echo View::url('/dashboard/store/discounts/addcodes', $d->drID)?>" id="codes-add">
<fieldset><legend><?= t('Add Codes'); ?></legend>

    <div class="form-group">
        <?php echo $form->label('codes', t('Code(s)'))?>
        <?php echo $form->textarea('codes', '', array('class' => ''))?>
        <span class="help-block"><?= t('Seperate codes via lines or commas. Codes are case-insensitive.'); ?></span>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success"><?= t('Add Codes'); ?></button>
    </div>

</fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?php echo URL::to('/dashboard/store/discounts')?>" class="btn btn-default"><?php echo t('Return to Discount Rules')?></a>
        </div>
    </div>

<?php } ?>


