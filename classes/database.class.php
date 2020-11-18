<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/conf.class.php');

class database
{

	private static $dbh = null;
	private $error;
	private $stmt;
	private static $query = null;
	private static $binds = null;

	public function __construct($host = null, $db = null, $username = null, $pw = null)
	{
		if (!self::$dbh) {
			$this->connect($host, $db, $username, $pw);
		}
	}

	private function connect($host = null, $db = null, $username = null, $pw = null, $port = 3306)
	{
		if (!$host) {
			$host = conf::DATABASE_HOST;
			$db = conf::DATABASE_NAME;
			$username = conf::DATABASE_USERNAME;
			$pw = conf::DATABASE_PASSWORD;
			$port = conf::DATABASE_PORT;
		}
		// Set DSN
		$dsn = 'mysql:host=' . $host . ';dbname=' . $db. ';port=' .$port;
		// Set options
		$options = array(
			PDO::ATTR_PERSISTENT       => false,
			PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION
		);
		// Create a new PDO instanace
		try {
			self::$dbh = new PDO($dsn, $username, $pw, $options);
		}
		// Catch any errors
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}


	public function getErrorMsg()
	{
		return $this->error;
	}

	public function getHandle()
	{
		return self::$dbh;
	}

	public function query($query)
	{
        self::$query = $query;
	    self::$binds = null;
		if (self::$dbh === null) {
			$this->connect();
		}
		$this->stmt = self::$dbh->prepare($query);
	}

	public function bind($param, $value, $type = null)
	{
        self::$binds[$param] = $value;
		if (is_null($type)) {
			switch (true) {
				case is_int($value):
					$type = PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = PDO::PARAM_NULL;
					break;
				default:
					$type = PDO::PARAM_STR;
			}
		}

		$this->stmt->bindValue($param, $value, $type);
	}

	public function execute()
	{
		return $this->stmt->execute();
	}

	public function getLastID()
	{
		if (!$this->execute()) {
			return false;
		}
		return self::$dbh->lastInsertId();
	}

	public function resultset($fetch = PDO::FETCH_ASSOC)
	{
		$this->execute();
		return $this->stmt->fetchAll($fetch);
	}

	public function single()
	{
		$this->execute();
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function resultsetObj($fetch = PDO::FETCH_OBJ)
	{
		$this->execute();
		return $this->stmt->fetchAll($fetch);
	}

	public function singleObj()
	{
		$this->execute();
		return $this->stmt->fetch(PDO::FETCH_OBJ);
	}

	public function column()
	{
		$this->execute();
		return $this->stmt->fetchColumn();
	}

	public function rowCount()
	{
		$this->execute();
		return $this->stmt->rowCount();
	}

	public function beginTransaction()
	{
		return self::$dbh->beginTransaction();
	}

	public function endTransaction()
	{
		return self::$dbh->commit();
	}

	public function cancelTransaction()
	{
		return self::$dbh->rollBack();
	}

	public function debugDumpParams()
	{
		$errorinfo = $this->stmt->errorInfo();
		$dumpinfo = $this->stmt->debugDumpParams();

		echo '<pre>', print_r($errorinfo, true);
		echo '<pre>', print_r($dumpinfo, true);
	}

	public function getLastQuery()
    {
        if(!self::$binds) {
            return self::$query;
        }
        $keys = array();

        foreach (self::$binds as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/'.$key.'/';
            } else {
                $keys[] = '/[?]/';
            }
        }

        return preg_replace($keys, self::$binds, self::$query, 1, $count);
    }

	public function insert($table, $data, $fields = array())
	{
		if (empty($data) || empty($table) || !is_array($data)) {
			return false;
		}

		if ($fields) {
			foreach ($data as $key => $value) {
				if (!in_array($key, $fields)) {
					unset($data[$key]);
				}
			}
		}

		$q = 'INSERT INTO `' . $table . '` (`';
		$fields_data = array_keys($data);
		$q .= implode('`, `', $fields_data);
		$q .= '`) VALUES(:';
		$q .= implode(', :', $fields_data) . ')';
		$this->query($q);

		foreach ($data as $field => $value) {
			$this->bind(':' . $field, $value);
		}

		return $this->getLastID();
	}

	public function update($table, $data, $condition, $fields = array())
	{
		if (empty($data) || empty($table) || !is_array($data)) {
			return false;
		}

		if ($fields) {
			foreach ($data as $key => $value) {
				if (!in_array($key, $fields)) {
					unset($data[$key]);
				}
			}
		}

		$binds = array();

		$values = array();
		foreach ($data as $field => $value) {
			$values[] = "`{$field}` = :{$field}";
			$binds[$field] = $value;
		}

		$conditions_str = '';
		if (is_array($condition)) {
			$conditions = array();
			foreach ($condition as $field => $value) {
				$conditions[] = "`{$field}` = :{$field}";
				$binds[$field] = $value;
				$conditions_str = implode(' AND ', $conditions);
			}
		} else {
			$conditions_str = $condition;
		}

		$q = "UPDATE `{$table}` SET " . implode(', ', $values) . " WHERE {$conditions_str}";
		$this->query($q);

		foreach ($binds as $field => $value) {
			$this->bind(':' . $field, $value);
		}
		return $this->execute();
	}

	public function delete($table, $condition)
	{
		if (empty($condition) || empty($table)) {
			return false;
		}

		$q = 'DELETE FROM `' . $table . '` WHERE ' . $condition;
		$this->query($q);

		return $this->execute();
	}

	public function disconnect()
	{
		self::$dbh = null;
	}

	public function quote($string)
	{
		return self::$dbh->quote($string);
	}
}
