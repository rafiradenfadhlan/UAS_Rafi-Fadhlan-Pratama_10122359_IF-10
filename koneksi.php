<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "db_kepegawaian";

$koneksi = new mysqli($host, $user, $password, $database);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>