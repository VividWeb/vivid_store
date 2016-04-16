<?php defined('C5_EXECUTE') or die("Access Denied.");?>

    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-info">
                <p><?=t('Promotions are flexible ways of offering discounts, rewards and more. ')?></p>
                <p><?=t('%sPromotion Rewards%s are what you\'re offering in your promotion. For example, you can reward them with a discount, a free product, or something else.','<strong>','</strong>')?></p>
                <p><?=t('%sPromotion Rules%s are the qualifications someone has to meet in order to receive a reward.','<strong>','</strong>')?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?php echo $form->label('name', t('Promotion Name'));?>
                <?php echo $form->text('name');?>
            </div>
            <div class="form-group">
                <?php echo $form->label('label', t('Public Label %swhat the public will see%s','<small class="text-muted">','</small>'));?>
                <?php echo $form->text('label');?>
            </div>
            <div class="form-group">
                <?php echo $form->label('enabled', t('Enabled'));?>
                <?php echo $form->select('enabled',array(true=>'Enabled',false=>'Disabled'));?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?=t('Promotion Rewards')?> <small class="text-muted"><?=t('required')?></small>
                </div>
                <div class="panel-body">
                    <div class="well well-sm">10% discount on Subtotal</div>
                    <div class="well well-sm">Free <strong>Product Name</strong></div>
                </div>
                <div class="panel-footer add-to-panel-list">
                    <div class="panel panel-promotion">
                        <div class="panel-heading"><i class="fa fa-plus"></i> Add Promotion Reward</div>
                        <div class="panel-body">
                            <ul>
                                <li><a href="">Discount</a></li>
                                <li><a href="">Free Product</a></li>
                                <li><a href="">Free Shipping</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?=t('Promotion Rules')?>
                </div>
                <div class="panel-body">

                </div>
                <div class="panel-footer add-to-panel-list">
                    <div class="panel panel-promotion">
                        <div class="panel-heading"><i class="fa fa-plus"></i> Add Promotion Reward</div>
                        <div class="panel-body">
                            <ul>
                                <li><a href="">Discount</a></li>
                                <li><a href="">Free Product</a></li>
                                <li><a href="">Free Shipping</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
