<?php

use Doctrine\DBAL\Connection;

/* main class wrapping users and descriptions into SQL requests */
class UserDao
{
	private $db;

	public function __construct(Connection $db)
	{
		$this->db = $db;
	}

	protected function getDb()
	{
		return $this->db;
	}

	public function findUser($username, $password)
	{
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $this->getDb()->fetchAssoc($sql);
        return $user;
	}

	public function findDescriptionById($id)
	{
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $this->getDb()->fetchAssoc($sql);
        return $todo;
	}

	public function findDescriptionByUser($user)
	{
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $this->getDb()->fetchAll($sql);
        return $todos;
	}

	public function getDescription($id)
	{
	    $sql = "SELECT * FROM todos WHERE id = '$id'";
	    $todo = $this->getDb()->fetchAssoc($sql);
	    return $todo["description"];
	}

	public function deleteDescription($id)
	{
	    $sql = "DELETE FROM todos WHERE id = '$id'";
	    $this->getDb()->executeUpdate($sql);
	}

	public function addDescription($user_id, $description)
	{
	    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
	    $this->getDb()->executeUpdate($sql);
	}

}
