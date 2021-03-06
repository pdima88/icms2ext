<?php
use pdima88\icms2ext\FormHelper;

/** @var cmsTemplate $this */

$this->setPageTitle($title);

if (isset($breadcrumbs)) {
    foreach ($breadcrumbs as $breadcrumb) {
        $this->addBreadcrumb($breadcrumb['title'], $breadcrumb['url'] ?? '');
    }
}

$this->addBreadcrumb($title);

$this->addToolButton(array(
    'class' => 'save',
    'title' => LANG_SAVE,
    'href'  => "javascript:icms.forms.submit()"
));


$this->addToolButton([
    'class' => 'cancel',
    'title' => 'Отмена',
    'href'  => FormHelper::getBackURL()
]);
?>

    <h2><?= $title ?></h2>

<?php $this->renderForm($form, $item, array(
    'action' => '',
    'method' => 'post'
), $errors);