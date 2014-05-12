<?php

class ObjectController extends ControllerBase
{

    public function findAction()
    {
        $query = $this->request->getPost('query', 'trim');

        echo $query;
    }

}

