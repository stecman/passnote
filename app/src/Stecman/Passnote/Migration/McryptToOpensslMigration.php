<?php

namespace Stecman\Passnote\Migration;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di;
use Stecman\Passnote\Encryptor;

class McryptToOpensslMigration
{
    /**
     * User account being operated on
     * @var \User
     */
    protected $user;

    /**
     * Password of the user to use for re-encrypting with openssl
     * @var string
     */
    protected $password;

    /**
     * @var Di
     */
    protected $di;

    public function __construct(Di $di, \User $user, $password)
    {
        $this->di = $di;

        $this->user = $user;
        $this->password = $password;

        if (!extension_loaded('mcrypt')) {
            die("The mcrypt extension is required for this migration but does't appear to be loaded.\n");
        }
    }

    /**
     * Try to run the migration
     */
    public function run()
    {
        // Disable warnings from use of deprecated mcrypt functions for this migration
        error_reporting(error_reporting() & ~E_DEPRECATED);

        // Check this is the right password before trying anything
        if (!$this->user->validatePassword($this->password)) {
            throw new \RuntimeException("Password incorrect");
        }

        // Switch out default encryptor with a modified version that decrypts using mcrypt
        /** @var Encryptor $opensslEncryptor */
        $opensslEncryptor = $this->di->get('encryptor');
        $migrationEncryptor = new _Legacy_Encryptor($opensslEncryptor->getKdfIterations());
        $this->di->set('encryptor', $migrationEncryptor);

        /** @var Mysql $database */
        $database = $this->di->get('db');
        $database->begin();

        $accountKeyPassphrase = $this->user->getAccountKeyPassphrase($this->password);

        echo "Re-encrypting objects and versions...\n";
        /** @var \StoredObject $object */
        foreach ($this->user->objects as $object) {
            echo "  Object {$object->title}\n";

            // Ignore objects not using the account key (these shouldn't exist at this point in time)
            if ($object->getKeyId() != $this->user->getAccountKey()->id) {
                echo "Warning: Skipping migration for non-account key object\n";
                continue;
            }

            // Re-encrypt the current version of the object
            $currentContent = $object->getContent($accountKeyPassphrase);
            $object->setContent($currentContent);

            // Call the internal versionless save method
            $class = new \ReflectionClass($object);
            $method = $class->getMethod('saveWithoutVersion');
            $method->setAccessible(true);
            $method->invoke($object);

            /** @var \ObjectVersion $version */
            foreach ($object->versions as $version) {
                echo "    Version {$version->id}\n";

                $versionContent = $version->getContent($accountKeyPassphrase);

                // Abuse API to rewrite the contents of object versions
                // (Versions are supposed to be immutable, but apparently they're not)
                $object->setContent($versionContent);
                $object->copyStateToVersion($version);

                $version->save();

            }
        }

        echo "Re-encrypting account key\n";
        $this->user->changePassword($this->password, $this->password);

        // Save changes to user and account key
        $this->user->save();
        $this->user->getAccountKey()->save();

        echo "Comitting changes to the database...\n";
        $database->commit();
    }
}

/**
 * Modified encryptor implementation that decrypts using mcrypt and encrypts using the current implementation
 * This should not be used anywhere outside of this migration
 */
class _Legacy_Encryptor extends Encryptor
{
    public function decrypt($data, $key, $iv)
    {
        $legacyIvSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

        // Sanity check
        if (strlen($iv) != $legacyIvSize) {
            throw new \Exception(
                'IV does not look like a legacy mcrypt IV (not the old length). Have you already run this migration?'
            );
        }

        $value = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv);

        // Strip null padding bytes
        return rtrim($value, "\0");
    }
}
