<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Controller;
use Core;

class States extends Controller
{
    public function getStateList()
    {
        $countryCode = $_POST['country'];
        $selectedState = $_POST['selectedState'];
        $type = $_POST['type'];
        $list = Core::make('helper/lists/states_provinces')->getStateProvinceArray($countryCode);
        if ($list) {
            if ($type=="tax") {
                echo "<select name='taxState' id='taxState' class='form-control'>";
            } else {
                echo "<select name='checkout-{$type}-state' id='checkout-{$type}-state' ccm-passed-value='' class='form-control'>";
            }
            foreach ($list as $code=>$country) {
                if ($code == $selectedState) {
                    echo "<option selected value='{$code}'>{$country}</option>";
                } else {
                    echo "<option value='{$code}'>{$country}</option>";
                }
            }
            echo "<select>";
        } else {
            if ($type=="tax") {
                echo "<input name='taxState' id='taxState' class='form-control'>";
            } else {
                echo "<input name='checkout-{$type}-state' id='checkout-{$type}-state' value='{$selectedState}' class='form-control'>";
            }
        }
    }
}
