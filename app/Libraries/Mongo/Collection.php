<?php
 
namespace App\Libraries\Mongo;

use MongoDB\Driver\ClientEncryption;
 
class Collection extends \MongoDB\Collection {
    
    protected $_encryptionOpts;
    protected $_clientEncryption;

    public function insertMany(array $documents, array $options = []) {
        foreach($documents as &$document) {
            $this->_encryptArrayField($document);
        }

        return parent::insertMany($documents, $options);
    }

    public function insertOne($document, array $options = []) {
        $this->_encryptArrayField($document);
        return parent::insertOne($document, $options);
    }

    public function findOne($filter = [], array $options = []) {
        $this->_encryptArrayField($filter);
        $document = parent::findOne($filter, $options);
        if (empty($document)) return [];
        
        $document = $this->_decryptArrayField($document);
        return $document;
    }

    public function find($filter = [], array $options = []) {
        $this->_encryptArrayField($filter);
        $documents = parent::find($filter, $options);
        if (empty($documents)) return [];
        
        $dados = array();
        foreach($documents as $document) {
            $dados[] = $this->_decryptArrayField($document);
        }
        return $dados;
    }

    public function updateOne($filter, $update, array $options = []) {
        $this->_encryptArrayForUpdate($filter, $update);
        return parent::updateOne($filter, $update, $options);
    }

    public function updateMany($filter, $update, array $options = []) {
        $this->_encryptArrayForUpdate($filter, $update);
        return parent::updateMany($filter, $update, $options);
    }

    protected function _encryptArrayForUpdate(&$filter, &$update) {
        $this->_encryptArrayField($filter);
        foreach($update as &$upd) {
            $this->_encryptArrayField($upd);
        }
    }

    protected function _encryptArrayField(&$document) {
        // self::_iterateAndEncrypt($document);
        foreach ($document as $key => &$value) {
            if (!is_array($value) && self::_isFieldEncrypted($key)) { 
                $value = $this->_clientEncryption->encrypt($value, $this->_encryptionOpts);
            }
        }
    }

    // private static function _iterateAndEncrypt(&$array) {

    // }

    protected function _decryptArrayField(&$document) {
        foreach($document as $key => &$value) {
            if (self::_isFieldEncrypted($key)) {
                $value = $this->_clientEncryption->decrypt($value);
            }
            if (is_string($value)) {
                $value = htmlentities($value);
            }
        }

        return $document;
    }

    protected static function _isFieldEncrypted($field) {
        return substr($field, 0, 4) == "enc_";
    }

    public function setClientEncryption($value) {
        $this->_clientEncryption = $value;
        $this->_encryptionOpts = array('algorithm' => ClientEncryption::AEAD_AES_256_CBC_HMAC_SHA_512_DETERMINISTIC);

        $_mongo = new MongoConnection('admin');
        $_mongo->setCollection('datakeys', true);

        if (empty($_mongo->collection->findOne(['keyAltNames' => ['$all' => ['fieldskey']]]))) {
            $this->_encryptionOpts['keyId'] = $this->_clientEncryption->createDataKey('local', ['keyAltNames' => ['fieldskey']]);
        } else {
            $this->_encryptionOpts['keyAltName'] = 'fieldskey';
        }
    }
}