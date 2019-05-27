<?php
use pdima88\phpassets\Assets;
use pdima88\icms2ext\ToolbarHelper;
/**
 * @var cmsTemplate $this
 *
 * @var string $page_title
 * @var string $page_url
 * @var array $tree
 * @var string $treeitem_detail_url
 * @var \pdgrid\Grid $grid
 * @var array $toolbar
 * @var string $toolbar_hook
 */

$this->addJS('templates/default/js/datatree.js');
$this->addCSS('templates/default/css/datatree.css');

$this->setPageTitle($page_title);

$this->addBreadcrumb($page_title, $page_url);

if (isset($toolbar)) {
    $toolbar = new ToolbarHelper($toolbar);
    $toolbar->addToolButtons();
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
                    <?php if (isset($toolbar)) {
                        echo $toolbar->toolbarButtonsInitScript($grid);
                     } ?>
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

                            <?php if (isset($treeitem_detail_url)) { ?>
                            var $divItemDetail = $('#divItemDetail');
                            if (key+0) {
                                var itemDetailLoadUrl = "<?= $treeitem_detail_url ?>" + keyPath;
                                $divItemDetail.load(itemDetailLoadUrl);
                            } else {
                                $divItemDetail.html('');
                            }
                            <?php }

                            if (isset($toolbar)) {
                                echo $toolbar->idReplaceScript();
                            }
                            ?>

                            if (!postInit) {
                                var loadUrl = "<?= $page_url ?>" + keyPath;
                                $('#pdgrid_<?= $grid->id ?>').attr('data-url', loadUrl).attr('data-ajax', loadUrl);
                                $('.pdgrid-<?= $grid->id ?>-export').attr('data-url', $.pdgrid.appendUrlParams(loadUrl, {export: $('.pdgrid-<?= $grid->id ?>-export').attr('data-export')}));

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
            <div id="divItemDetail"></div>

            <?php $this->renderAsset('icms2ext/backend/grid', [
                'grid' => $grid
            ]);
            ?>
        </td>
    </tr>
</table>


