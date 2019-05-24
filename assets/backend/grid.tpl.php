<?php
use pdima88\phpassets\Assets;
/**
 * @var cmsTemplate $this
 * @var \pdgrid\Grid $grid
 */
$this->addJS('templates/default/js/jquery-cookie.js');
$this->addJS('templates/default/js/admin-content.js');

$this->setPageTitle($page_title);

$this->addBreadcrumb($page_title, $page_url);

$this->addToolButton(array(
    'class' => 'excel',
    'title' => 'Экспорт',
    'href'  => $grid->appendSortAndFilterParams($page_url.'?export=csv'),
    'target' => '_blank',
));

?>

<script type="text/javascript">
    $(function(){
        $('.cp_toolbar .excel a').addClass('s4y-grid-<?= $grid->id ?>-export')
            .attr('data-url', $.pdgrid.appendUrlParams($('#s4y_grid_<?= $grid->id ?>').attr('data-url'), {export:'csv'}));
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



