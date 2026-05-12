<?php

$conn = new PDO('mysql:host=localhost;dbname=mine_permit_laravel_dev', 'root', '');
$stmt = $conn->query('SELECT id, name FROM internal_companies WHERE password_hash IS NULL ORDER BY name');
foreach ($stmt->fetchAll() as $row) {
    echo $row['id'].': '.$row['name'].PHP_EOL;
}
