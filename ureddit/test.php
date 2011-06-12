<?php

require_once("URedditUser.class.php");

$u = new URedditUser("tylermenezes");
print_r($u->GetTaughtClasses());