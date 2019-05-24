<?php
use pdima88\phpassets\Assets;
/**
 * @var cmsTemplate $this
 *
 * @var string $page_title
 * @var string $page_url
 * @var array $tree
 * @var \pdgrid\Grid $grid
 * @var array $toolbar
 * @var string $toolbar_hook
 */
$this->addJS('templates/default/js/jquery-cookie.js');
$this->addJS('templates/default/js/datatree.js');
$this->addCSS('templates/default/css/datatree.css');
$this->addJS('templates/default/js/admin-content.js');

$this->setPageTitle($page_title);

$this->addBreadcrumb($page_title, $page_url);

$export = [];

foreach ($toolbar as $toolButtonId => $toolButton) {
    if (isset($toolButton['export'])) {
        if (!isset($toolButton['class'])) $toolButton['class'] = $toolButtonId;
        $export[] = $toolButton;
    }
    $toolButtonClass = $toolButton['class'] ?? $toolButtonId;
    $toolButton['class'] = $toolButtonClass;
    $this->addToolButton($toolButton);
}

if (isset($toolbar_hook)) {
    $this->applyToolbarHook($toolbar_hook);
}
?>

<h1><?= $page_title ?></h1>

<table class="layout">
    <tr>
        <td class="sidebar" valign="top">

            <div id="datatree">
                <ul id="treeData" style="display: none">
                    <?php foreach($tree as $treeid=>$treeitem){ ?>
                        <li id="<?php echo $treeitem['id'];?>" class="folder"><?php echo $treeitem['title']; ?></li>
                    <?php } ?>
                </ul>
            </div>

            <script type="text/javascript">
                $(function(){
                    <?php foreach ($export as $exportButton) { ?>
                    $('.cp_toolbar .<?= $exportButton['class'] ?> a').addClass('pdgrid-<?= $grid->id ?>-export')
                        .attr('data-url', $.pdgrid.appendUrlParams($('#pdgrid_<?= $grid->id ?>').attr('data-url'), {export:'<?= $exportButton['export'] ?>'}));
                    <?php } ?>
                    var postInit = false;
                    $("#datatree").dynatree({

                        onPostInit: function(isReloading, isError){
                            postInit = true;
                            var key = <?= (int) $id ?>;
                            var node = $("#datatree").dynatree("getTree").getNodeByKey(''+key);
                            if (node) node.activate();
                            var sb = $('.sidebar');
                            $(sb).after('<td id="slide_cell"></td>');
                            $('#slide_cell').on('click', function (){
                                if($(sb).is(':visible')){
                                    $(sb).hide();
                                    $(this).addClass('unslided');
                                } else {
                                    $(sb).show();
                                    $(this).removeClass('unslided');
                                }
                            });
                            $(window).on('resize', function(){
                                if(!$(sb).is(':visible')){
                                    $('#slide_cell').addClass('unslided');
                                }
                            }).triggerHandler('resize');
                            postInit = false;
                        },

                        onActivate: function(node){
                            node.expand();
                            var key = node.data.key;
                            var keyPath = node.getKeyPath();

                            <?php if (isset($treeitem_info_url)) { ?>
                            var $divInfo = $('#divItemInfo');
                            if (key+0) {
                                var infoLoadUrl = "<?= $treeitem_info_url ?>" + keyPath;
                                $divInfo.load(infoLoadUrl);
                            } else {
                                $divInfo.html('');
                            }
                            <?php } ?>

                            if (key == 0) {

                                <?php foreach ($toolbar as $toolButtonId => $toolButton) {
                                    $toolButtonClass = $toolButton['class'] ?? $toolButtonId;
                                    if ($toolButton['treeitem_hide'] ?? false) {
                                    ?>
                                        $('.cp_toolbar .<?= $toolButtonClass ?> a').hide();
                                    <?php }
                                    if ($toolButton['treeitem_href_suffix'] ?? false) {
                                    ?>
                                        $('.cp_toolbar .<?= $toolButtonClass ?> a').attr('href', "<?= $toolButton['href'] ?>");
                                    <?php }
                                }
                                ?>
                            } else {
                                <?php foreach ($toolbar as $toolButtonId => $toolButton) {
                                    $toolButtonClass = $toolButton['class'] ?? $toolButtonId;
                                    if ($toolButton['treeitem_href_suffix'] ?? false) {
                                    ?>
                                    $('.cp_toolbar .<?= $toolButtonClass ?> a').attr('href', "<?=
                                        $toolButton['href'].$toolButton['treeitem_href_suffix'] ?>".replace(/\{id\}/, key));
                                    <?php }
                                    if ($toolButton['treeitem_hide'] ?? false) {
                                    ?>
                                        $('.cp_toolbar .<?= $toolButtonClass ?> a').show();
                                    <?php }
                                }
                                ?>
                            }

                            if (!postInit) {
                                var loadUrl = "<?= $page_url ?>" + keyPath;
                                $('#pdgrid_<?= $grid->id ?>').attr('data-url', loadUrl).attr('data-ajax', loadUrl);
                                $('.pdgrid-<?= $grid->id ?>-export').attr('data-url', $.pdgrid.appendUrlParams(loadUrl, {export: ''}));

                                $.pdgrid.load($.pdgrid.$gridById('<?= $grid->id ?>'), {
                                    url: loadUrl, ajax: loadUrl
                                });
                            }

                        }

                    });
                });
            </script>

        </td>
        <td class="main" valign="top">
            <div id="divItemInfo"></div>

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


        </td>
    </tr>
</table>


