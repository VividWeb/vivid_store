<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Controller;
use User;
use Database;
use URL;

class ProductFinder extends Controller
{
    public function getProductMatch()
    {
        $u = new User();
        if (!$u->isLoggedIn()) {
            echo "Access Denied";
            exit;
        }
        if (!$_POST['query']) {
            echo "Access Denied";
            exit;
        } else {
            $query = $_POST['query'];
            $db = Database::get();
            $results = $db->query('SELECT * FROM VividStoreProducts WHERE pName LIKE "%'.$query.'%"');
            
            if ($results) {
                foreach ($results as $result) {
                    ?>
        
                <li data-product-id="<?=$result['pID']?>"><?=$result['pName']?></li>
        
            <?php 
                } //for each
            } else { //if no results ?>
                <li><?=t("I can't find a product by that name")?></li>
            <?php 
            }
        }
    }
    public function renderProductSearchForm($formName=null, $savedValue=null)
    {
        $randomValue = rand(1, 1000);
        if (!$formName) {
            $formName = 'pID';
        }
        return '
            <div class="product-search-form" id="product-search-form-'.$randomValue.'" data-ajax-url="'.URL::to('/productfinder').'">
                <label class="form-label" for="productSearch-'.$randomValue.'">'.t("Search for a product").'</label>
                <input class="form-control product-search-input" onkeyup="searchForProduct('.$randomValue.')" id="productSearch-'.$randomValue.'" type="text">
                <input type="hidden" class="product-id-field" name="'.$formName.'">
                <div class="product-search-results">
                    <ul class="results-list">
    
                    </ul>
                </div>
                <div class="alert alert-info">
                    <strong>'.t("Selected Product:").'</strong>
                    <span class="selected-product"></span>
                </div>
            </div>
        ';
    }
}
