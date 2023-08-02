<?php

class Config {

    public String $salt = ""; // NOT WORKING

    public $domain = "localhost";

    public $domain_uri = "http";

    public array $allowed = [
        "http",
        "https"
    ];
}