<?php

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $objects = $this->getObjectQuery();
        $searchTerm = trim($this->request->getPost('query'));

        $this->view->setLayout('app');
        $this->view->setVar('objects', $objects->getQuery()->execute());
        $this->view->setVar('search_autofocus', true);
        $this->view->setVar('search_term', $searchTerm);
    }

    public function errorAction($message = 'Not found')
    {
        $this->response->setStatusCode(404, 'Not found');
        $this->view->setVar('status', 404);
        $this->view->setVar('message', $message);
    }

    /**
     * @return Phalcon\Mvc\Model\Query\Builder
     */
    protected function getObjectQuery()
    {
        $query = $this->modelsManager->createBuilder();
        $query->addFrom('Object');
        $query->orderBy('created DESC');
        $query->where('user_id = :user_id: AND parent_id IS NULL', [
            'user_id' => Security::getCurrentUserId()
        ]);

        if ($search = trim($this->request->getPost('query'))) {
            
            // Crude search for each term using LIKE
            foreach (explode(' ', $search) as $term) {
                $query->andWhere('title LIKE :term: OR description LIKE :term:', [
                    'term' => "%$term%"
                ]);
            }
        }

        return $query;
    }

}

