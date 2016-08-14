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

	public function markDescriptionAsCompleted($id)
	{
	    $sql = "UPDATE todos SET completed = 1 WHERE id = '$id'";
	    $this->getDb()->executeUpdate($sql);
	}

	public function addDescription($user_id, $description, $completed)
	{
	    $sql = "INSERT INTO todos (user_id, description, completed) VALUES ('$user_id', '$description', '$completed')";
	    $this->getDb()->executeUpdate($sql);
	}

}
