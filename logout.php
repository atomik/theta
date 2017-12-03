<?php

session_start();
ob_start();

unset($_SESSION['steam']);

header('Location: ./');