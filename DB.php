<?php
namespace Asgard\Db;

/**
 * Database.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DB implements DBInterface {
	/**
	 * \PDO instance.
	 * @var \PDO
	 */
	protected $pdo;
	/**
	 * Configuration.
	 * @var array
	 */
	protected $config;

	protected $conn;

	/**
	 * Constructor.
	 * @param array $config database configuration
	 * @param \PDO  $db database connection
	 * @api
	*/
	public function __construct(array $config, \PDO $pdo=null) {
		if(!isset($config['driver']))
			$config['driver'] = 'mysql';
		$this->config = $config;
		if($pdo !== null) {
			$this->pdo = $pdo;
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function dal() {
		return new DAL($this);
	}

	/**
	 * Get the PDO instance.
	 * @param  array $config
	 * @return \PDO
	 */
	public function getPDO() {
		if(!$this->pdo) {
			$config = $this->config;
			$driver = $config['driver'];
			$user = isset($config['user']) ? $config['user']:'root';
			$password = isset($config['password']) ? $config['password']:'';

			switch($driver) {
				case 'pgsql':
					$parameters = 'pgsql:host='.$config['host'].(isset($config['port']) ? ' port='.$config['port']:'').(isset($config['database']) ? ' dbname='.$config['database']:'');
					$this->pdo = new \PDO($parameters, $user, $password);
					break;
				case 'mssql':
					$parameters = 'mssql:host='.$config['host'].(isset($config['database']) ? ';dbname='.$config['database']:'');
					$this->pdo = new \PDO($parameters, $user, $password);
					break;
				case 'sqlite':
					$this->pdo = new \PDO('sqlite:'.$config['database']);
					break;
				default:
					$parameters = 'mysql:host='.$config['host'].(isset($config['port']) ? ';port='.$config['port']:'').(isset($config['database']) ? ';dbname='.$config['database']:'');
					$this->pdo = new \PDO($parameters, $user, $password, [\PDO::MYSQL_ATTR_FOUND_ROWS => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
			}
		}

		return $this->pdo;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * {@inheritDoc}
	*/
	public function query($sql, array $args=[]) {
		return new Query($this->getPDO(), $sql, $args);
	}

	/**
	 * {@inheritDoc}
	*/
	public function id() {
		return $this->getPDO()->lastInsertId();
	}

	/**
	 * {@inheritDoc}
	*/
	public function beginTransaction() {
		$this->getPDO()->beginTransaction();
	}

	/**
	 * {@inheritDoc}
	*/
	public function commit() {
		$this->getPDO()->commit();
	}

	/**
	 * {@inheritDoc}
	*/
	public function rollback() {
		$this->getPDO()->rollback();
	}


	/**
	 * {@inheritDoc}
	*/
	public function close() {
		unset($this->pdo);
	}

	public function getSchema() {
		return new Schema($this);
	}

	public function getConn() {
		if(!$this->conn) {
			$driver = $this->getConfig()['driver'];

			if($driver == 'mysql')
				$driver = 'pdo_mysql';
			elseif($driver == 'mssql')
				$driver = 'pdo_sqlsrv';
			elseif($driver == 'pgsql')
				$driver = 'pdo_pgsql';
			elseif($driver == 'sqlite')
				$driver = 'pdo_sqlite';
			else
				throw new \Exception('Invalid driver: '.$driver);

			$c = new \Doctrine\DBAL\Configuration;
			$params = [
				'pdo' => $this->getPDO()
			];
			$this->conn = \Doctrine\DBAL\DriverManager::getConnection($params, $c);
		}

		return $this->conn;
	}
}