<?php

require "framework/Database.php";
require "framework/Autoloader.php";

require "setup/global-errors.php";

session_start();

require "setup/cors.php";
require "setup/constants.php";
require "setup/functions.php";
require "setup/config.php";

require "routers/index.php";
