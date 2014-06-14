<?php

use Stecman\Passnote\ReadableEncryptedContent;
use Stecman\Passnote\ReadableEncryptedContentTrait;

class ObjectController extends ControllerBase
{
    public static function getObjectUrl(Object $object)
    {
        return 'object/' . $object->id;
    }

    public function indexAction($id)
    {
        $object = $this->getObjectWithId($id);

        if ($object) {
            $content = $this->decryptContent($object);

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

            $this->view->setLayout('object');
            $this->view->setVar('object', $object);
            $this->view->setVar('versions', $versions);
        } else {
            $this->handleAs404('Object not found');
        }
    }

    public function showVersionAction($objectId, $versionId)
    {
        $version = $this->modelsManager->executeQuery(
            'SELECT ObjectVersion.* FROM ObjectVersion'
            .' LEFT JOIN Object ON ObjectVersion.object_id = Object.id'
            .' WHERE ObjectVersion.id = :version_id: AND ObjectVersion.object_id = :object_id: AND Object.user_id = :user_id:',
            [
                'version_id' => (int) $versionId,
                'object_id' => (int) $objectId,
                'user_id' => Security::getCurrentUserId()
            ]
        )->getFirst();

        if ($version) {
            $content = $this->decryptContent($version);

            $this->view->setVar('object', $version->master);
            $this->view->setVar('version', $version);
            $this->view->setVar('next_version', $version->getSibling(ObjectVersion::NEWER_VERSION));
            $this->view->setVar('prev_version', $version->getSibling(ObjectVersion::OLDER_VERSION));
            $this->view->setVar('decrypted_content', $content);
        } else {
            $this->handleAs404('Object or version not found');
        }
    }

    public function editAction($id)
    {
        $object = $this->getObjectWithId($id);
        $form = new ObjectForm(null, Security::getCurrentUser());

        if ($object) {
            $content = $this->decryptContent($object);
            $form->setBody($content);
            $form->setEntity($object);
            $this->view->setVar('object', $object);
        }


        $this->view->setVar('form', $form);

        if ($this->request->isPost() && $form->isValid($_POST)) {
            if (!$this->security->checkToken()) {
                $this->flash->error('Invalid security token. Please try submitting the form again.');
                return;
            }

            $savedObject = $form->handleSubmit();

            if (!$object) {
                $this->response->redirect( self::getObjectUrl($savedObject) );
            }
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