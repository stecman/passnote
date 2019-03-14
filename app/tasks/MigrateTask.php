<?php

use Stecman\Passnote\Migration\McryptToOpensslMigration;

class MigrateTask extends BaseTask
{
    /**
     * Apply database schema patches
     */
    public function runAction()
    {
        $this->updateSchemaTable();

        foreach ($this->getUnappliedPatches() as $patchName) {
            echo "Applying patch '$patchName'\n";
            $this->applyPatch($patchName);
        }
    }

    /**
     * Migrate from non-standard mcrypt AES encryption to openssl
     *
     * See docs/upgrade-notes/
     *
     * @param string $email
     */
    public function mcrypt_opensslAction($email)
    {
        /** @var User $user */
        $user = User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);

        if ($user) {
            $password = $this->promptInput('Password: ', true);

            $migration = new McryptToOpensslMigration($this->di, $user, $password);
            $migration->run();

        } else {
            die("No user found for $email\n");
        }
    }

    /**
     * Return a list of patch files that have not been applied
     *
     * @return string[]
     */
    public function getUnappliedPatches()
    {
        $all = $this->getAllPatches();
        $applied = $this->getAppliedPatches();

        return array_diff($all, $applied);
    }

    /**
     * Return a list of all available patch files
     *
     * @return string[]
     */
    protected function getAllPatches()
    {
        $files = glob($this->getMigrationsPath() . '/*.sql');
        return array_map('basename', $files);;
    }

    /**
     * Return a list of all patch files that have been applied
     *
     * @return string[]
     */
    protected function getAppliedPatches()
    {
        $db = $this->getDatabase();

        $result = $db->query('SELECT patch FROM schema_patches');
        $result->setFetchMode(PDO::FETCH_COLUMN, 0);

        return $result->fetchAll();
    }

    /**
     * Run a patch file against the database and register it as applied
     *
     * @param string $filename
     */
    protected function applyPatch($filename)
    {
        $path = $this->getMigrationsPath() . "/$filename";

        if (!is_file($path)) {
            throw new \RuntimeException("No such patch '$filename'");
        }

        $db = $this->getDatabase();
        $db->begin();
        $db->execute(file_get_contents($path));
        $db->execute('INSERT INTO schema_patches VALUES (:patch, :applied)', [
            'patch' => $filename,
            'applied' => time()
        ]);
        $db->commit();
    }

    /**
     * Generate the schema_patches table
     */
    protected function updateSchemaTable()
    {
        $db = $this->getDatabase();

        // Create the table if it doesn't exist
        if (!$db->tableExists('schema_patches')) {
            echo "Creating schema_patches table..\n";

            $db->createTable('schema_patches', null, [
                'columns' => [
                    new \Phalcon\Db\Column('patch', [
                        'type' => \Phalcon\Db\Column::TYPE_VARCHAR,
                        'size' => 100,
                        'notNull' => true,
                        'primary' => true
                    ]),

                    new \Phalcon\Db\Column('applied', [
                        'type' => \Phalcon\Db\Column::TYPE_INTEGER,
                        'size' => 11,
                        'notNull' => true
                    ])
                ]
            ]);
        }
    }

    /**
     * @return Phalcon\Db\AdapterInterface
     */
    protected function getDatabase()
    {
        return $this->di->get('db');
    }

    /**
     * @return string
     */
    protected function getMigrationsPath()
    {
        return dirname(APPLICATION_PATH) . '/docs/mysql/migrations';
    }
}
