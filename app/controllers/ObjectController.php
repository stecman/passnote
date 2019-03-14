<?php

use Stecman\Passnote\Object\ReadableEncryptedContent;
use Stecman\Passnote\Object\ReadableEncryptedContentTrait;

class ObjectController extends ControllerBase
{
    public static function getObjectUrl(StoredObject $object)
    {
        return 'object/' . $object->getUuid();
    }

    public function indexAction($uuid)
    {
        $object = $this->getObjectById($uuid);

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

    public function versionsAction($uuid)
    {
        $object = $this->getObjectById($uuid);

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

    public function showVersionAction($objectUuid, $versionUuid)
    {
        $version = $this->getObjectVersion($objectUuid, $versionUuid);

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

    public function editAction($uuid)
    {
        $object = $this->getObjectById($uuid);
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
            $this->response->redirect( self::getObjectUrl($savedObject) );
        }
    }

    public function deleteAction($objectUuid, $versionUuid = null)
    {
        if ($versionUuid) {
            $object = $this->getObjectVersion($objectUuid, $versionUuid);
        } else {
            $object = $this->getObjectById($objectUuid);
        }

        $this->view->setVar('object', $object);
        $this->view->setVar('isVersion', $object instanceof ObjectVersion);

        if (!$object) {
            return $this->handleAs404('Object not found');
        }

        if ($this->request->isPost()) {
            if (!$this->security->checkToken()) {
                $this->flash->error('Invalid security token. Please try submitting the form again.');
                return;
            }

            $object->delete();
            $this->flashSession->success("Deleted object $objectUuid");
            $this->response->redirect('');
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
     * @param string $uuid
     * @return \StoredObject
     */
    protected function getObjectById($uuid)
    {
        return StoredObject::findFirst([
            'uuid = :uuid: AND user_id = :user_id:',
            'bind' => [
                'uuid' => $uuid,
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

    /**
     * Fetch an object version from the database
     *
     * @param string $objectUuid
     * @param string $versionUuid
     * @return ObjectVersion|null
     */
    protected function getObjectVersion($objectUuid, $versionUuid) {
        return $this->modelsManager->executeQuery(
            'SELECT ObjectVersion.* FROM ObjectVersion'
            .' LEFT JOIN StoredObject Object ON ObjectVersion.object_id = Object.id'
            .' WHERE ObjectVersion.uuid = :version_uuid: AND Object.uuid = :object_uuid: AND Object.user_id = :user_id:',
            [
                'version_uuid' => $versionUuid,
                'object_uuid' => $objectUuid,
                'user_id' => Security::getCurrentUserId()
            ]
        )->getFirst();
    }
}
