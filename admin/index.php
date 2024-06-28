<?php

require "../framework/Database.php";
require "../framework/Autoloader.php";

session_start();

require "../setup/constants.php";
require "setup/functions.php";
require "../setup/config.php";

require "routers/index.php";