<?php defined('C5_EXECUTE') or die("Access Denied.");

$listViews = array('view','updated','removed','success');
$addViews = array('add','edit','save');

if (in_array($controller->getTask(),$listViews)){ ?>

    <div class="ccm-dashboard-header-buttons">
        <a href="" class="btn btn-primary"><?=t('Add Promotion')?></a>
    </div>

    <div class="ccm-dashboard-content-full">

        <table class="ccm-search-results-table">
            <thead>
                <th><a><?php echo t('Promotion Name')?></a></th>
                <th><a><?php echo t('Public Label')?></a></th>
                <th><a><?php echo t('Status')?></a></th>
                <th><a><?php echo t('Actions')?></a></th>
            </thead>
            <tbody>
                <tr>
                    <td>2 for 1</td>
                    <td>Spring Blowout Holiday Sale!</td>
                    <td><span class="label label-default">Active</span></td>
                    <td>
                        <a href="" class="btn btn-default">Edit</a>
                        <a href="" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

<?php } elseif(in_array($controller->getTask(),$addViews)){ ?>

<?php } ?>
