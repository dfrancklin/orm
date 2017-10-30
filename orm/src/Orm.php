<?php

namespace ORM;

use ORM\Core\Shadow;
use ORM\Core\Annotation;
use ORM\Core\EntityManager;

use ORM\Interfaces\IEntityManager;

class Orm {

	private static $instance;

	private $shadows;

	private $connections;

	private $defaultConnection;

	protected function __construct() {
		$this->shadows = [];
		$this->connections = [];
		$this->defaultConnection = 'default';
	}

	public static function getInstance() : Orm {
		if (is_null(self::$instance)) {
			self::$instance = new Orm();
		}

		return self::$instance;
	}

	public function setConnection(String $name = 'default') {
		$this->addConnection($name);
		$this->defaultConnection = $name;
	}

	public function addConnection(String $name) {
		$config = $this->getConfiguration($name);
		$this->connections[$name] = $this->createConnection($config);
		$this->loadDriver($config['db'], $config['version']);
	}

	public function setDefaultConnection(String $name) {
		$this->defaultConnection = $name;
	}


	private function loadDriver($db, $version) {
		$driver = __DIR__ . '/drivers/' . $db . '-' . $version . '.php';

		if (!file_exists($driver)) {
			throw new \Exception('The driver file for "' . $db . '" on version "' . $version . '" was not found!');
		}

		require_once $driver;
	}

	private function createConnection(Array $config) : \PDO {
		foreach(['db', 'host', 'schema', 'user', 'pass'] as $field) {
			if (!isset($config[$field])) {
				throw new \Exception("O campo $config[$field] não foi definido na definição de conexão");
			}
		}

		$dsn = "$config[db]:host=$config[host];dbname=$config[schema]";

		$pdo = new \PDO($dsn, $config['user'], $config['pass']);
		$pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_EMPTY_STRING);

		return $pdo;
	}

	private function getConfiguration(String $name = 'default') : Array {
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

	protected function getConnection(String $name = null) : \PDO {
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

	public function getShadow(String $class) : Shadow {
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

	public function createEntityManager(String $connectionName=null) : IEntityManager {
		return new EntityManager($this->getConnection($connectionName));
	}

}
