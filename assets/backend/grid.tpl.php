<?php
use pdima88\icms2ext\ToolbarHelper;
use pdima88\phpassets\Assets;
/**
 * @var cmsTemplate $this
 * @var \pdgrid\Grid $grid
 */
$this->addJS('templates/default/js/jquery-cookie.js');
$this->addJS('templates/default/js/admin-content.js');

if (isset($page_title)) {
    $this->setPageTitle($page_title);
    $this->addBreadcrumb($page_title, $page_url);
}
$initToolbar = false;
if (isset($toolbar) && !($toolbar instanceof ToolbarHelper)) {
    $toolbar = new ToolbarHelper($toolbar);
    $toolbar->addToolButtons();
    $initToolbar = true;
}
?>

<script type="text/javascript">
    $(function(){
        <?php if ($initToolbar) {
            echo $toolbar->toolbarButtonsInitScript($grid);
        } ?>
    });
</script>

<div class="cp_toolbar">
    <?php $this->toolbar(); ?>
</div>
<?php

$gridStr = $grid->render();
Assets::addStyle('display:none', '.pdgrid-action-btn');

$this->addHead(Assets::getCss());
$this->addOutput(Assets::getJs());

echo $gridStr;
?>



