<?php defined('C5_EXECUTE') or die("Access Denied.");
$manageViews = array('view');
if (in_array($controller->getTask(), $manageViews)) {
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-info">
                <p><?=t('Promotions are flexible ways of offering discounts, rewards and more. ')?></p>
                <p><?=t('%sPromotion Rewards%s are what you\'re offering in your promotion. For example, you can reward them with a discount, a free product, or something else.', '<strong>', '</strong>')?></p>
                <p><?=t('%sPromotion Rules%s are the qualifications someone has to meet in order to receive a reward.', '<strong>', '</strong>')?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?php echo $form->label('name', t('Promotion Name'));
    ?>
                <?php echo $form->text('name');
    ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('label', t('Public Label %swhat the public will see%s', '<small class="text-muted">', '</small>'));
    ?>
                <?php echo $form->text('label');
    ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('enabled', t('Enabled'));
    ?>
                <?php echo $form->select('enabled', array(true=>'Enabled', false=>'Disabled'));
    ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?=t('Promotion Rewards')?> <small class="text-muted"><?=t('required')?></small>
                </div>
                <div class="panel-body" id="promotion-reward-list">

                </div>
                <div class="panel-footer add-to-panel-list">
                    <div class="panel panel-promotion">
                        <div class="panel-heading"><i class="fa fa-plus"></i> Add Promotion Reward</div>
                        <div class="panel-body">
                            <ul>
                                <?php foreach ($rewardTypes as $rewardType) {
    ?>
                                    <li><a href="#" data-promotion="reward-type" data-handle="<?=$rewardType->getHandle()?>"><?=$rewardType->getName()?></a></li>
                                <?php 
}
    ?>
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
                <div class="panel-body" id="promotion-rule-list">

                </div>
                <div class="panel-footer add-to-panel-list">
                    <div class="panel panel-promotion">
                        <div class="panel-heading"><i class="fa fa-plus"></i> Add Promotion Rule</div>
                        <div class="panel-body">
                            <ul>
                                <?php foreach ($ruleTypes as $ruleType) {
    ?>
                                    <li><a href="#" data-promotion="rule-type" data-handle="<?=$ruleType->getHandle()?>"><?=$ruleType->getName()?></a></li>
                                <?php 
}
    ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h3><?=t("Promotion Application")?></h3>
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?=$form->label('promotionApplication', t("Apply the  promotion automatically? or by a Code?"))?>
                <?=$form->select('promotionApplication',
                    array(
                        'automatic'=>t("Automatically applied at checkout"),
                        'promoCode'=>t("Requires a Promo Code")
                    )
                );
    ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?=$form->label('promoCode', t("Promo Code"));
    ?>
                <?=$form->text('promoCode')?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?=$form->label('exclusive', t("Can this be combined with other promotions?"))?>
                <?=$form->select('exclusive', array(1=>t("No"), 0=>t("Yes")));
    ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?=$form->label('marketPromotion', t("Show discounted price in Product/Product List %sif exists%s", '<small class="text-muted">', '</small>'));
    ?>
                <?=$form->select('marketPromotion', array(1=>t("Yes"), 0=>t("No")));
    ?>
            </div>
        </div>
    </div>
<div id="promotion-reward-forms">
    <?php foreach ($rewardTypes as $rewardType) {
    ?>
        <?=$rewardType->renderDashboardForm()?>
    <?php 
}
    ?>
</div>
<div id="promotion-rule-forms">
    <?php foreach ($ruleTypes as $ruleType) {
    ?>
        <div class="promotion-rule-form" id="<?=$ruleType->getHandle()?>-rule-type-form">
            <?=$ruleType->renderDashboardForm()?>
        </div>
    <?php 
}
    ?>
</div>
<script type="text/x-template" id="promotion-list-item">
    <div class="well well-sm promotion-item" data-handle="<%=handle%>">
        <i class="fa fa-close pull-right"></i>
        <%=content%>
    </div>
</script>
<?php 
} else {
    ?>
<?php 
} ?>
