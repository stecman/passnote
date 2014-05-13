<?php


class ObjectTask  extends BaseTask
{
    public function importAction($email, $file)
    {
        if (!$user = $this->getUserByEmail($email)) {
            die("No user found for $email\n");
        }

        if (!file_exists($file)) {
            die("File '$file' not found\n");
        }

        if (!preg_match('/\.json$/', $file)) {
            die("File must be in JSON format\n");
        }

        // Log in
        $password = $this->promptInput('User\'s password:', true);
        if (!$user->validatePassword( $password )) {
            die("Password incorrect\n");
        }
        unset($password);

        $raw = file_get_contents($file);
        $data = json_decode($raw, true);

        if (!$data) {
            die("File failed to parse as JSON.");
        }

        $map = $this->promptUserForMapping($data);
        $key = $user->getAccountKey();

        $objects = [];

        foreach ($data as $incoming) {
            $object = new Object();
           // $object->
        }
    }

    /**
     * Prompt user to map the columns in $data to the fields of an Object
     *
     * @param array $data
     * @return array
     */
    protected function promptUserForMapping(array $data)
    {
        $keys = array_keys($data[0]);
        $map = [
            'created' => null,
            'title' => null,
            'description' => null,
            'content' => null,
        ];

        echo "Keys in first item:\n" . print_r($keys, true) ."\n";

        foreach ($map as $key => &$value) {
            $value = $this->promptInput("Key number to map as '$key': ");
        }

        if (!$map['title'] || !$map['content']) {
            die("Error: Mappings for Title and Content are required.\n");
        }

        return array_filter($map);
    }
}
