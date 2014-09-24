<?php
namespace Asgard\Db;

/**
 * Database.
 */
class DB {
	/**
	 * \PDO instance.
	 * @var \PDO
	 */
	protected $db;
	/**
	 * Configuration.
	 * @var array
	 */
	protected $config;
	
	/**
	 * Constructor.
	 * @param array $config database configuration
	 * @param \PDO  $db database connection
	*/
	public function __construct(array $config, \PDO $db=null) {
		$this->config = $config;
		if($db === null)
			$this->db = $this->getPDO($config);
		else
			$this->db = $db;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Return a DAL instance.
	 * @return DAL
	 */
	public function getDAL() {
		return new DAL($this);
	}

	/**
	 * Get the PDO instance.
	 * @param  array $config
	 * @return \PDO
	 */
	protected function getPDO(array $config) {
		$driver = isset($config['driver']) ? $config['driver']:'mysql';
		$user = isset($config['user']) ? $config['user']:'root';
		$password = isset($config['password']) ? $config['password']:'';

		switch($driver) {
			case 'pgsql':
				$parameters = 'pgsql:host='.$config['host'].(isset($config['port']) ? ' port='.$config['port']:'').(isset($config['database']) ? ' dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password);
			case 'mssql':
				$parameters = 'mssql:host='.$config['host'].(isset($config['database']) ? ';dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password);
			case 'sqlite':
				return new \PDO('sqlite:'.$config['database']);
			default:
				$parameters = 'mysql:host='.$config['host'].(isset($config['port']) ? ';port='.$config['port']:'').(isset($config['database']) ? ';dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password, [\PDO::MYSQL_ATTR_FOUND_ROWS => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
		}
	}

	/**
	 * Return the configuration.
	 * @return array
	 */
	public function getConfig() {
		return $this->config;
	}
	
	/**
	 * Return the database instance.
	 * @return \PDO
	*/
	public function getDB() {
		return $this->db;
	}
	
	/**
	 * Execute an SQL query.
	 * @param string $sql SQL query
	 * @param array $args SQL parameters
	 * @return Asgard\Db\Query Query object
	*/
	public function query($sql, array $args=[]) {
		return new Query($this->db, $sql, $args);
	}
	
	/**
	 * Return the last inserted id.
	 * @return integer Last inserted id
	*/
	public function id() {
		return $this->db->lastInsertId();
	}
	
	/**
	 * Start a new SQL transaction.
	*/
	public function beginTransaction() {
		$this->db->beginTransaction();
	}
	
	/**
	 * Commit the SQL transaction.
	*/
	public function commit() {
		$this->db->commit();
	}
	
	/**
	 * Roll back the SQL transaction.
	*/
	public function rollback() {
		$this->db->rollback();
	}
}