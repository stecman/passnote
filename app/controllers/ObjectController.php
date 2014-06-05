<?php

use Stecman\Passnote\ReadableEncryptedContent;
use Stecman\Passnote\ReadableEncryptedContentTrait;

class ObjectController extends ControllerBase
{
    public function indexAction($id)
    {
        $object = $this->getObjectWithId($id);

        if ($object) {
            $content = $this->decryptContent($object);

            $this->view->setLayout('app');
            $this->view->setVar('object', $object);
            $this->view->setVar('decrypted_content', $content);
        } else {
            $this->handleAs404('Object not found');
        }
    }

    public function findAction()
    {
        $query = $this->request->getPost('query', 'trim');

        echo $query;
    }

    public function versionsAction($id)
    {
        $object = $this->getObjectWithId($id);

        if ($object) {
            $versions = [];
            $differ = new \SebastianBergmann\Diff\Differ('');

            $prevContent = '';
            foreach ($object->versions as $version) {
                $nextContent = $this->decryptContent($version);
                $diff = $differ->diff($prevContent, $nextContent);
                $prevContent = $nextContent;

                $version->_diff = $this->formatDiff($diff);
                $versions[] = $version;
            }

            $object->_diff = $this->formatDiff($differ->diff(
                $prevContent,
                $this->decryptContent($object)
            ));
            $versions[] = $object;



            krsort($versions);

            $this->view->setLayout('app');
            $this->view->setVar('object', $object);
            $this->view->setVar('versions', $versions);
        } else {
            $this->handleAs404('Object not found');
        }
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

    public function editAction($id)
    {
        $this->view->setLayout('app');

        $object = $this->getObjectWithId($id);
        $content = $this->decryptContent($object);

        $form = new ObjectForm($object, Security::getCurrentUser());
        $form->setBody($content);
        $this->view->setVar('form', $form);
        $this->view->setVar('object', $object);

        if ($this->request->isPost() && $form->isValid($_POST)) {
            if (!$this->security->checkToken()) {
                $this->flash->error('Invalid security token. Please try submitting the form again.');
                return;
            }

            $form->handleSubmit();
        }
    }

    protected function decryptContent(ReadableEncryptedContent $object)
    {
        $user = Security::getCurrentUser();

        if ($object->getKeyId() === $user->accountKey_id) {
            $keyService = new \Stecman\Passnote\AccountKeyService();
            return $keyService->decryptObject($object);
        } else {
            // Prompt for decryption passphrase
        }
    }

    /**
     * @param $id
     * @return \Object
     */
    protected function getObjectWithId($id)
    {
        return Object::findFirst([
            'id = :id: AND user_id = :user_id:',
            'bind' => [
                'id' => (int) $id,
                'user_id' => Security::getCurrentUserId()
            ]
        ]);
    }

    protected function formatDiff($diff)
    {
        $diff = preg_replace('/(^-.*$)/m', '<span class="diff-del">$1</span>', $diff);
        $diff = preg_replace('/(^\+.*$)/m', '<span class="diff-add">$1</span>', $diff);
        return $diff;
    }

}