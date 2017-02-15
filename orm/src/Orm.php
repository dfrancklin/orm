<?php
namespace ORM;

use ORM\Core\Annotation;
use ORM\Builders\Query;

class Orm {

	private static $shadows = [];

	private static $connections = [];

	private static $defaultConnection;

	public static function setConnection($name = 'default') {
		$config = self::getConfiguration($name);
		self::$connections[$name] = self::createConnection($config);
		self::$defaultConnection = $name;
	}

	public static function addConnection($name) {
		$config = self::getConfiguration($name);
		self::$connections[$name] = self::createConnection($config);
	}

	public static function setDefaultConnection($name) {
		self::$defaultConnection = $name;
	}

	private static function createConnection($config) {
		foreach(['db', 'host', 'schema', 'user', 'pass'] as $field) {
			if (!isset($config[$field])) {
				throw new \Exception("O campo $config[$field] não foi definido na definição de conexão", 1);
			}
		}

		$dsn = "$config[db]:host=$config[host];dbname=$config[schema]";

		return new \PDO($dsn, $config['user'], $config['pass']);
	}

	private static function getConfiguration($name) {
		$configFile = __DIR__ . '/../connection.config.php';

		if (!file_exists($configFile)) {
			throw new \Exception('Arquivo de configuração de conexão não encontrado', 1);
		}

		require $configFile;

		if (is_null($name) || empty(trim($name))) {
			$name = 'default';
		}

		if (!isset($connections[$name])) {
			throw new \Exception("Configuração de conexão \"$name\" não definida", 1);
		}

		return $connections[$name];
	}

	protected static function getConnection($name = '') {
		if (!count(self::$connections)) {
			self::setConnection($name);
			return self::getConnection($name);
		}

		if ($name && isset(self::$connections[$name])) {
			return self::$connections[$name];
		} elseif ($name) {
			throw new \Exception("Não foram encontradas conexões definidas para \"$name\"", 1);
		}

		if (self::$defaultConnection && isset(self::$connections[self::$defaultConnection])) {
			return self::$connections[self::$defaultConnection];
		}

		throw new \Exception('Não foram encontradas conexões definidas', 1);
	}

	protected static function getShadow($class) {
		if (!$class) {
			throw new \Exception('Necessário informar o nome da classe', 1);
		}

		if (!array_key_exists($class, self::$shadows)) {
			$annotation = new Annotation($class);
			$shadow = $annotation->mapper();
			self::$shadows[$class] = $shadow;
		}

		return self::$shadows[$class];
	}

	public static function query($connection = '') {
		return new Query(self::getConnection($connection));
	}

}