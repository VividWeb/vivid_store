<?php
namespace Concrete\Package\VividStore\Block\VividUtilityLinks;

use \Concrete\Core\Block\BlockController;
use Core;
use View;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;

class controller extends BlockController
{
    protected $btTable = 'btVividUtilityLinks';
    protected $btInterfaceWidth = "450";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "400";
    protected $btDefaultSet = 'vivid_store';

    public function getBlockTypeDescription()
    {
        return t("Add your cart links for Vivid Store");
    }

    public function getBlockTypeName()
    {
        return t("Utility Links");
    }
    public function view()
    {
        $this->set("itemCount", StoreCart::getTotalItemsInCart());
        $this->set("total", StorePrice::format(StoreCalculator::getSubTotal()));
        $js = \Concrete\Package\VividStore\Controller::returnHeaderJS();
        $this->requireAsset('javascript', 'jquery');
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'vivid-store');
        $this->requireAsset('css', 'vivid-store');
    }
    public function save($args)
    {
        $args['showCartItems'] = isset($args['showCartItems']) ? 1 : 0;
        $args['showCartTotal'] = isset($args['showCartTotal']) ? 1 : 0;
        $args['showSignIn'] = isset($args['showSignIn']) ? 1 : 0;
        $args['showCheckout'] = isset($args['showCheckout']) ? 1 : 0;
        $args['showGreeting'] = isset($args['showGreeting']) ? 1 : 0;
        parent::save($args);
    }
    public function validate($args)
    {
        $e = Core::make("helper/validation/error");
        if ($args['cartLabel']=="") {
            $e->add(t('Cart Label must be set'));
        }
        if (strlen($args['cartLabel']) > 255) {
            $e->add(t('Cart Link Label exceeds 255 characters'));
        }
        if (strlen($args['itemsLabel']) > 255) {
            $e->add(t('Cart Items Label exceeds 255 characters'));
        }
        return $e;
    }
}
