<?php

class ObjectController extends ControllerBase
{

    public function findAction()
    {
        $query = $this->request->getPost('query', 'trim');

        echo $query;
    }

    public function newAction()
    {
        $this->view->setLayout('app');

        $form = new ObjectForm(null, Security::getCurrentUser());
        $this->view->setVar('form', $form);

        if ($this->request->isPost() && $form->isValid($_POST)) {
            if (!$this->security->checkToken()) {
                $this->flash->error('Invalid security token. Please try submitting the form again.');
                return;
            }

            $form->handleSubmit();
        }
    }

}