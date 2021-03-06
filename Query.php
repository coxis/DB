<?php
namespace Asgard\Db;

/**
 * Query objects.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
class Query {
	/**
	 * Database instance.
	 * @var \PDO
	 */
	protected $pdo;
	/**
	 * Query's ressource.
	 * @var \PDOStatement
	 */
	protected $rsc;

	protected $sql;
	protected $parameters;

	/**
	 * Constructor.
	 * @param \PDO    $pdo
	 * @param string  $sql
	 * @param array   $args
	 */
	public function __construct(\PDO $pdo, $sql, array $args=[]) {
		$this->pdo = $pdo;
		$this->sql = $sql;
		$this->parameters = $args;
		try {
			if($args) {
				$this->rsc = $pdo->prepare($sql);
				$this->rsc->execute($args);
			}
			else
				$this->rsc = $pdo->query($sql);
		} catch(\PDOException $e) {
			$exception = new DBException;
			$exception->feedPDOException($e);
			$exception->setSQL($sql, $args);
			throw $exception;
		}
	}

	/**
	 * Return the ressource.
	 * @api
	 */
	public function rsc() {
		return $this->rsc;
	}

	/**
	 * Return the next row.
	 * @return array
	 * @api
	 */
	public function next() {
		return $this->rsc->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Return the number of affected rows.
	 * @return integer
	 * @api
	 */
	public function affected() {
		return $this->rsc->rowCount();
	}

	/**
	 * Count the results.
	 * @return integer
	 * @api
	 */
	public function count() {
		return $this->rsc->rowCount();
	}

	/**
	 * Return the first row only.
	 * @return array
	 * @api
	 */
	public function first() {
		return $this->rsc->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Return all rows at once.
	 * @return array
	 * @api
	 */
	public function all() {
		return $this->rsc->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getSQL() {
		return $this->sql;
	}

	public function getParameters() {
		return $this->parameters;
	}
}