<?php

require_once("config.php");

$link = new LinkShortener();
$s = $link->shortURL("https://github.com/mikecao/shorty/blob/master/index.php");
echo $s;

class LinkShortener {

    public mysqli $db;

    public string $salt;

    public array $allowed;

    public string $domain;

    public string $domain_uri;

    public function __construct() {
        $this->db = $this->connect_database();

        // Init config
        $cfg = new Config();
        $this->setSalt($cfg->salt);
        $this->setAllowed($cfg->allowed);
        $this->setDomain($cfg->domain);
        $this->domain_uri = $cfg->domain_uri;
    }

    public function setSalt(string $salt) {
        $this->salt = $salt;
    }

    public function getSalt() : string {
        return $this->salt;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function getDomain() : string {
        return $this->domain;
    }

    public function setAllowed(array $allowed) {
        $this->allowed = $allowed;
    }

    public function getAllowed() : array {
        return $this->allowed;
    }

    public function isAllowed($uri) {
        return in_array($uri, $this->allowed);
    }

    public function connect_database() {
        $servername = "localhost";
        $username = "root";
        $pwd = "";
        $db = "linkshortener";
        
        $con = new mysqli($servername, $username, $pwd, $db);
        return $con;
    }

    public function addURL($url, $key) {
        $sql = "INSERT INTO `urls`(`url`, `key`) VALUES ('$url', '$key')";
        $res = $this->db->query($sql, MYSQLI_STORE_RESULT);
        return $res;
    }

    public function getURL($key) {
        $sql = "SELECT * FROM `urls` WHERE `key`='$key'";
        $res = $this->db->query($sql, MYSQLI_STORE_RESULT);
        $fetch = $res->fetch_assoc();
        return $fetch;
    }

    public function shortURL($url) {
        $shortened_url = "";
        $urischeme = $this->getUriScheme($url);
        if($this->isAllowed($urischeme)) {
            $key = $this->generateKey($url);
            $this->addURL($url, $key);
            $shortened_url = $this->build_short($key);
        } else {

        }
        return $shortened_url;
    }

    public function execute() {
        if(!isset($_GET["k"]))return false;

        $key = str_replace('/', '', $_GET['k']);

        if($fetch = $this->getURL($key)) {
            $url = $fetch["url"];
            $this->redirect($url);
        }
    }

    public function build_short($key) {
        $build = "";
        $build .= $this->domain_uri;
        $build .= "://";
        $build .= $this->getDomain();
        $build .= "/".$key;

        return $build;
    }

    public function redirect($url) {
        header("Location: ".$url);
        exit(0);
    }

    public function generateKey($url) {
        $key = substr(md5($url.uniqid()), 0, 6);
        return $key;
    }

    public function getUriScheme($url) {
        $regex = '/(?:([^\:]*)\:\/\/)?(?:([^\:\@]*)(?:\:([^\@]*))?\@)?(?:([^\/\:]*)\.(?=[^\.\/\:]*\.[^\.\/\:]*))?([^\.\/\:]*)(?:\.([^\/\.\:]*))?(?:\:([0-9]*))?(\/[^\?#]*(?=.*?\/)\/)?([^\?#]*)?(?:\?([^#]*))?(?:#(.*))?/m';
        preg_match_all($regex, $url, $matches, PREG_SET_ORDER, 0);
        $uri = $matches[0][1];
        return $uri;
    }
}