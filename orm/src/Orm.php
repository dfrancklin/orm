<?php

namespace ORM;

use ORM\Core\Annotation;
use ORM\Core\Connection;
use ORM\Core\Driver;
use ORM\Core\EntityManager;
use ORM\Core\Shadow;

use ORM\Builders\TableManager;

use ORM\Interfaces\IConnection;
use ORM\Interfaces\IEntityManager;

class Orm {

	private static $instance;

	private $shadows;

	private $connections;

	private $defaultConnection;

	protected function __construct()
	{
		$this->shadows = [];
		$this->connections = [];
		$this->defaultConnection = 'default';
	}

	public static function getInstance() : Orm
	{
		if (is_null(self::$instance)) {
			self::$instance = new Orm();
		}

		return self::$instance;
	}

	public function setConnection(String $name = 'default', Array $config = [])
	{
		$this->addConnection($name, $config);
		$this->defaultConnection = $name;
	}

	public function addConnection(String $name, Array $config = [])
	{
		$this->connections[$name] = $this->createConnection($name);

		if (!empty($config) && isset($config['create']) && $config['create']) {
			if (!isset($config['namespace'])) {
				throw new \Exception('In order to drop (optional) and create the "namespace" must be informed');
			}

			if (!isset($config['modelsFolder'])) {
				throw new \Exception('In order to drop (optional) and create the "models folder" must be informed');
			}

			$table = new TableManager($this->connections[$name], $config['namespace'], $config['modelsFolder']);

			if (!empty($config) && isset($config['drop']) && $config['drop']) {
				$table->drop();
			}

			$table->create();
		}
	}

	public function setDefaultConnection(String $name)
	{
		$this->defaultConnection = $name;
	}

	private function createConnection(String $name) : IConnection
	{
		$config = $this->getConfiguration($name);
		$dsn = $this->getDSN($config);
		$driver = $this->loadDriver($config['db'], $config['version'] ?? null);

		$pdo = new \PDO($dsn, $config['user'], $config['pass']);
		$pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_EMPTY_STRING);

		return new Connection($pdo, $driver, $config['schema']);
	}

	private function getConfiguration(String $name = 'default') : Array
	{
		$configFile = __DIR__ . '/../connection.config.php';

		if (!file_exists($configFile)) {
			throw new \Exception('Arquivo de configuração de conexão não encontrado');
		}

		require $configFile;

		if (empty(trim($name))) {
			$name = 'default';
		}

		if (!isset($connections[$name])) {
			throw new \Exception("Configuração de conexão \"$name\" não definida");
		}

		return $connections[$name];
	}

	private function loadDriver(String $db, String $version) : Driver
	{
		$ds = DIRECTORY_SEPARATOR;
		$driverFile = __DIR__ . $ds . 'drivers' . $ds . $db;

		if (!empty($version)) {
			$driverFile .= '-' . $version;
		}

		$driverFile .= '.php';

		if (!file_exists($driverFile)) {
			$message = 'The driver file for "' . $db . '"';

			if (!empty($version)) {
				$message .= ' on version "' . $version . '"';
			}

			$message .= ' was not found!';

			throw new \Exception($message);
		}

		require $driverFile;

		if (!isset($driver)) {
			throw new \Exception('The driver instance was not found on driver file "' . $driverFile . '"');
		}

		if (!($driver instanceof Driver)) {
			throw new \Exception('The driver instance must be an implementation of "' . Driver::class . '"');
		}

		return $driver;
	}

	private function getDSN(Array $config) : String
	{
		switch ($config['db']) {
			case 'mysql':
				if ($this->validateFields(['db', 'host', 'schema', 'user', 'pass'])) {
					return "$config[db]:host=$config[host];dbname=$config[schema]";
				}
				break;
			// case 'sqlite':
			// case 'sqlite2':
			// 	if ($this->validateFields(['db', 'file'])) {
			// 		return "$config[db]:$config[file]";
			// 	}
		}

		throw new \Exception('The database "' . $config['db'] . '" is not supported yet');
	}

	private function validateFields(Array $fields) : bool
	{
		foreach($fields as $field) {
			if (!isset($config[$field])) {
				throw new \Exception("O campo $config[$field] não foi definido na definição de conexão");
			}
		}

		return true;
	}

	public function getConnection(String $name = null) : Connection
	{
		if (empty($name)) {
			$name = $this->defaultConnection;
		}

		if (empty($this->connections)) {
			$this->setConnection($name);
		}

		if (!array_key_exists($name, $this->connections)) {
			$this->addConnection($name);
		}

		if (isset($this->connections[$name])) {
			return $this->connections[$name];
		}

		throw new \Exception("Não foram encontradas conexões definidas para \"$name\"");
	}

	public function getShadow(String $class) : Shadow
	{
		if (!$class) {
			throw new \Exception('Necessário informar o nome da classe');
		}

		if (!array_key_exists($class, $this->shadows)) {
			$annotation = new Annotation($this, $class);
			$shadow = $annotation->mapper();
			$this->shadows[$class] = $shadow;
		}

		return $this->shadows[$class];
	}

	public function createEntityManager(String $connectionName = null) : IEntityManager
	{
		return new EntityManager($this->getConnection($connectionName));
	}

}
