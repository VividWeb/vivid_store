<?php defined('C5_EXECUTE') or die("Access Denied.");?>

<div class="checkbox">
    <label>
        <?php echo $form->checkbox('showCartItems', 1, isset($showCartItems)?$showCartItems:1);?>
        <?=t('Show Amount of Items in Cart')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?php echo $form->checkbox('showCartTotal', 1, isset($showCartTotal)?$showCartTotal:1);?>
        <?=t('Show Cart SubTotal')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?php echo $form->checkbox('showSignIn', 1, isset($showSignIn)?$showSignIn:1);?>
        <?=t('Show Sign-in Link')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?php echo $form->checkbox('showGreeting', 1, isset($showSignIn)?$showSignIn:1);?>
        <?=t('Show Greeting  (Welcome back, FirstName)')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?php echo $form->checkbox('showCheckout', 1, isset($showCheckout)?$showCheckout:1);?>
        <?=t('Show Checkout Link')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?php echo $form->checkbox('popUpCart', 1, isset($popUpCart)?$popUpCart:1);?>
        <?=t('Link to popup cart instead of cart page')?>
    </label>
</div>

<div class="form-group">
    <?php echo $form->label('cartLabel', t('Cart Link Label'));?>
    <?php echo $form->text('cartLabel', $cartLabel?$cartLabel:t("View Cart"));?>
</div>
<div class="form-group">
    <?php echo $form->label('itemsLabel', t('Cart Items Label'));?>
    <?php echo $form->text('itemsLabel', $itemsLabel?$itemsLabel:t("Items in Cart"));?>
</div>