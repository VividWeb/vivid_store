<?php
defined('C5_EXECUTE') or die("Access Denied.");

$subject = $siteName.' - '.t('account Created');

/**
 * HTML BODY START
 */
ob_start();

?>
    <h2><?php echo t('Thank you for your order, an account has been created for you at') ?> <?php echo $siteName ?></h2>

    <p>Your username is: <strong><?php echo $username; ?></strong></p>
    <p>Your password is: <strong><?php echo $password; ?></strong></p>

<?php if ($link) {
    ?>
    <p>You can now access <?php echo $link;
    ?></p>
<?php 
} ?>

<?php
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END
 *
 * =====================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>
<?php echo t('An account has been created for you at') ?> <?php echo $siteName ?>

    Your username is: <?php echo $username; ?>
    Your password is: <?php echo $password; ?>

<?php if ($link) {
    ?>
    You can now access <?php echo $link;
    ?>
<?php 
} ?>

<?php

$body = ob_get_clean();
ob_end_clean();

// plain text and html emails not currently working, fix coming for 5.7
$body = '';

/**
 * PLAIN TEXT BODY END
 */
