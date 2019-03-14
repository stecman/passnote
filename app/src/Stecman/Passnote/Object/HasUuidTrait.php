<?php


namespace Stecman\Passnote\Object;


use Ramsey\Uuid\Uuid;

trait HasUuidTrait
{
    /**
     * Unique, read-only identifier for addressing this object
     * @var string
     */
    protected $uuid;

    /**
     * Get the unique identifier for this object
     * @return string
     */
    public function getUuid()
    {
        if (!$this->uuid) {
            // Create a value if it doesn't exist yet
            $this->generateNewUuid();
        }

        return $this->uuid;
    }

    protected function generateNewUuid()
    {
        $this->uuid = Uuid::uuid4();
    }
}
