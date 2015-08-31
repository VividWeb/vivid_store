<?php
defined('C5_EXECUTE') or die("Access Denied.");

$subject = $siteName.' - '.t('access');

/**
 * HTML BODY START
 */
ob_start();

?>
    <h2><?php echo t('Thank you for your order at') ?> <?php echo $siteName ?></h2>

<?php if ($link) { ?>
    <p>You can now access <?php echo $link; ?></p>
<?php } ?>

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
<?php echo t('Thank you for your order at') ?> <?php echo $siteName ?>

<?php if ($link) { ?>
    You can now access <?php echo $link; ?>
<?php } ?>

<?php

$body = ob_get_clean();
ob_end_clean();

// plain text and html emails not currently working, fix coming for 5.7
$body = '';

/**
 * PLAIN TEXT BODY END
 */
