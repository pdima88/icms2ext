<?php

namespace pdima88\icms2ext;

use pdgrid\Grid;

abstract class crudAction extends \cmsAction {

    protected $formName = '';
    protected $tableName = '';
    protected $messages = '';

    public function run($do = null) {
        $res = false;
        if (isset($do)) {
            if (method_exists($this, $do)) {
                $res = call_user_func([$this, $do]);
            }
        }
        if (!$res) {
            $res = call_user_func([$this, 'index']);
        }
        if ($res['tpl'] ?? false) {
            return \cmsTemplate::getInstance()->render($res['tpl'], $res['data'] ?? []);
        } else {
            \cmsCore::error404();
        }
    }

    public function add(){

        $errors = false;

        /** @var \cmsForm $form */
        $form = $this->getForm($this->formName);

        $is_submitted = $this->request->has('submit');

        $item = $form->parse($this->request, $is_submitted);

        if ($is_submitted){

            $errors = $form->validate($this, $item);

            if (!$errors){
                $this->model->insert($this->tableName, $item);
                \cmsUser::addSessionMessage($this->messages['add'] ?? 'Добавлено успешно', 'success');
                $this->redirectToAction($this->current_action);
            }

            if ($errors){
                \cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
            }

        }

        return [
            'tpl' => 'backend/form',
            'data' => [
                'title' => 'Добавление',
                'form' => $form,
                'errors' => $errors,
                'item' => $item
            ]
        ];

    }

    public function edit() {
        
    }

    public function index() {
        $grid = new Grid($this->getGrid());

        if ($this->request->has('export')) {
            $grid->export('csv', $this->title, $this->current_action, true);
        }

        if ($this->request->isAjax()) {
            $grid->ajax();
        }

        return [
            'tpl' => 'backend/'.$this->current_action,
            'data' => [
                'grid' => $grid
            ]
        ];
    }

    abstract function getGrid();
}