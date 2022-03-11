<?php
 
namespace App\Libraries\Mongo;

use Config\Paths;
use Exception;

class MongoConnection {
    
	protected $_dataBase;
	protected $_conn;
	public $collection;
	public $clientEncryption;

	function __construct(string $dataBase = "") {
		$config = new \Config\MongoDbConfig();
		$this->_dataBase = $config->defaultDataBase;

		if (!empty($dataBase)) {
			$this->_dataBase = $dataBase;
		}

		try {
			if($config->authRequired === true) {
				$this->_conn = new Client('mongodb://' . $config->username . ':' . $config->password . '@' . $config->host. ':' . $config->port);
			} else {
				$this->_conn = new Client('mongodb://' . $config->host. ':' . $config->port);
			}
		} catch(Exception $ex) {
			throw new Exception("Couldn\'t _connect to mongodb: " . $ex->getMessage(), 500);
		}

		$this->_configureEncryptionKey();
	}

	/**
	 * 
	 * @param string $value Collection name
	 */
	public function setCollection(string $value, $nullableEncryption = false) {
		if (!$nullableEncryption) {
			$this->collection = $this->_conn->selectCollection($this->_dataBase, $value, [], $this->clientEncryption);
		} else {
			$this->collection = $this->_conn->selectCollection($this->_dataBase, $value, []);
		}
        
		return $this->collection;
    }

	protected function _configureEncryptionKey() {
		$paths = new Paths();
		$keyfile96bytes = file_get_contents(rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'keyfile96bytes2');
		// $localKey = new Binary(trim($keyfile96bytes), Binary::TYPE_GENERIC);
		$clientEncryptionOpts = [
			'keyVaultNamespace' => 'admin.datakeys',
			'kmsProviders' => [
				'local' => ['key' => $keyfile96bytes],
			],
		];

		$this->clientEncryption = $this->_conn->createClientEncryption($clientEncryptionOpts);
	}
}