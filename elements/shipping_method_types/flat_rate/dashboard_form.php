<?php extract($vars); ?>

<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?php echo $form->label('basePrice',t("Base Price")); ?>
            <?php echo $form->text('basePrice'); ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?php echo $form->label('perItem',t("Additional Price Per Item")); ?>
            <?php echo $form->text('perItem'); ?>
        </div>
    </div>
</div>  