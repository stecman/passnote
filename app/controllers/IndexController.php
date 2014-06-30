<?php

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $paginatedObjects = new Phalcon\Paginator\Adapter\QueryBuilder([
            'builder' => $this->getObjectQuery(),
            'limit' => 20,
            'page' => 1
        ]);

        $this->view->setLayout('app');
        $this->view->setVar('objects', $paginatedObjects);
    }

    public function errorAction()
    {
        $this->response->setStatusCode(404, 'Not found');
        $this->view->setVar('status', 404);
        $this->view->setVar('message', 'Not found');
    }

    /**
     * @return Phalcon\Mvc\Model\Query\Builder
     */
    protected function getObjectQuery()
    {
        $query = $this->modelsManager->createBuilder();
        $query->addFrom('Object');
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

