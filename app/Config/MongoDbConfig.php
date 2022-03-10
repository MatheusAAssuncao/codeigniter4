<?php
 
namespace Config;
 
use CodeIgniter\Config\BaseConfig;
 
class MongoDbConfig extends BaseConfig {
             
    public $host = 'localhost';
    public $port = 27017;
    public $username = 'admin';
    public $password = '123';
    public $authRequired = true;
    public $defaultDataBase = 'salesgroup';
}
