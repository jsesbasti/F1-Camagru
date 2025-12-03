<?php
$db_server = getenv("DB_SERVER", true) ?: getenv("DB_SERVER") ?: null;
$db_name = getenv("MYSQL_DATABASE", true) ?: getenv("MYSQL_DATABASE") ?: null;
$db_username = getenv("MYSQL_USER", true) ?: getenv("MYSQL_USER") ?: null;
$db_password = getenv("MYSQL_PASSWORD",  true) ?: getenv("MYSQL_PASSWORD") ?: null;
$dsn = "mysql:host=$db_server;dbname=$db_name";
function connectDb() {
	global $dsn, $db_username, $db_password;
	try {
		$conn = new PDO($dsn, $db_username, $db_password, array(PDO::ATTR_PERSISTENT => true));
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return ($conn);
	} catch (PDOException $e) {
		die(http_response_code(403));
	}
}
function makeQuery(string $sql, array $params = []) {
	$conn = connectDb();
	if (empty($params)) {
		$sth = $conn->query($sql);
		return $sth;
	}
	$sth = $conn->prepare($sql);
	$sth->execute($params);
	return $sth;
}
function createDB() {
	try {
		$conn = connectDb(); 
		$sql = "CREATE TABLE IF NOT EXISTS users (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		firstname VARCHAR(30) NOT NULL,
		lastname VARCHAR(30) NOT NULL,
		username VARCHAR(10) NOT NULL,
		'password' password() NOT NULL,
		email VARCHAR(50),
		reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
		$conn->exec($sql);
		$conn = null;
	}
	catch (PDOException $e) {
		die();
	}
}
?>