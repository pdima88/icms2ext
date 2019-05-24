<?php

namespace pdima88\icms2ext;

use pdgrid\Grid;
use cmsCore;
use cmsTemplate;
use cmsUser;

abstract class crudAction extends \cmsAction {

    protected $formName = '';
    protected $tableName = '';
    protected $doAction = '';
    protected $pageTitle = '';
    protected $pageUrl = '';
    protected $titles = [];
    protected $messages = [];

    public function run($do = null) {
        $res = false;
        if (isset($do)) {
            $methodName = 'action'.string_to_camel('_',$do);
            if (method_exists($this, $methodName)) {
                $this->doAction = $do;
                $res = call_user_func([$this, $methodName]);
            }
        }
        if (!$res) {
            $this->doAction = 'index';
            $res = call_user_func([$this, 'actionIndex']);
        }
        if ($res['tpl'] ?? false) {
            return cmsTemplate::getInstance()->render($res['tpl'], $res['data'] ?? []);
        } else {
            cmsCore::error404();
        }
    }

    public function getParam($i = 0, $default = null) {
        $c = 0; $paramId = false;
        if (!is_numeric($i)) {
            $paramId = true;
        }
        foreach ($this->params as $param) {
            if ($c == 0 && $param == $this->doAction) {
                continue;
            }
            if ($paramId) {
                if ($param == $i) { // found param identifier
                    $paramId = false;
                    $c = $i;
                    // next param is param value
                }
                continue;
            }
            if ($i == $c) return $param;
            $c++;
        }
        return $default;
    }

    public function actionAdd(){

        $errors = false;

        /** @var \cmsForm $form */
        $form = $this->getForm($this->formName);

        $is_submitted = $this->request->has('submit');

        $item = $form->parse($this->request, $is_submitted);

        if ($is_submitted){

            $errors = $form->validate($this, $item);

            if (!$errors){
                $this->save(null, $item);
                cmsUser::addSessionMessage($this->messages[$this->doAction] ?? 'Добавлено успешно', 'success');
                $this->redirectBack();
            }

            if ($errors){
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
            }

        }

        return [
            'tpl' => 'backend/form',
            'data' => [
                'breadcrumbs' => [
                    [
                        'title' => $this->pageTitle,
                        'url' => $this->pageUrl
                    ]
                ],
                'title' => $this->titles[$this->doAction] ?? 'Добавление',
                'form' => $form,
                'errors' => $errors,
                'item' => $item
            ]
        ];

    }

    public function actionEdit($id = null, $item = null) {
        if (!isset($id)) {
            $id = $this->getParam();
            if (!$id) {
                cmsCore::error404();
            }
        }

        if (!isset($item)) {
            $item = $this->model->getItemById($this->tableName, $id);

            if (!$item) {
                cmsUser::addSessionMessage($this->messages['error_edit_no_item'] ?? 'Запись не найдена', 'error');
                $this->redirectBack();
            }
        }
        $errors = false;

        /** @var \cmsForm $form */
        $form = $this->getForm($this->formName);

        $is_submitted = $this->request->has('submit');

        if ($is_submitted) {

            $item = $form->parse($this->request, $is_submitted);

            $errors = $form->validate($this, $item);

            if (!$errors) {
                $this->save($id, $item);
                cmsUser::addSessionMessage($this->messages[$this->doAction] ?? 'Изменения сохранены', 'success');
                $this->redirectBack();
            } else {
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
            }

        }
        return [
            'tpl' => 'backend/form',
            'data' => [
                'breadcrumbs' => [
                    [
                        'title' => $this->pageTitle,
                        'url' => $this->pageUrl
                    ]
                ],
                'title' => $this->titles[$this->doAction] ?? 'Редактирование',
                'form' => $form,
                'errors' => $errors,
                'item' => $item
            ]
        ];
    }

    public function actionIndex() {
        $grid = new Grid($this->getGrid());

        if ($this->request->has('export')) {
            $grid->export('csv', $this->pageTitle, $this->current_action, true);
        }

        if ($this->request->isAjax()) {
            $grid->ajax();
        }

        return [
            'tpl' => 'backend/'.$this->current_action,
            'data' => [
                'page_title' => $this->pageTitle,
                'page_url' => $this->pageUrl,
                'grid' => $grid
            ]
        ];
    }

    abstract function getGrid();

    function save($id, $data) {
        if (!isset($id)) {
            $this->model->insert($this->tableName, $data);
        } else {
            $this->model->update($this->tableName, $id, $data);
        }
    }
}