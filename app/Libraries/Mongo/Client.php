<?php
 
namespace App\Libraries\Mongo;

class Client extends \MongoDB\Client {

    public function selectCollection($databaseName, $collectionName, array $options = [], $clientEncryption = null)
    {
        $options += ['typeMap' => $this->getTypeMap()];
        $collection = new Collection($this->getManager(), $databaseName, $collectionName, $options);
        if ($clientEncryption) {
            $collection->setClientEncryption($clientEncryption);
        }

        return $collection;
    }
}