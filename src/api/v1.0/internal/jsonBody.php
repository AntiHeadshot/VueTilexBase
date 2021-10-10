<?php

$fileContent = file_get_contents('php://input');
$origPost = $_POST;
$_POST = json_decode($fileContent, true);

if (strlen($fileContent) > 0 && $_POST === null && json_last_error() !== JSON_ERROR_NONE) {
  Tilex::Error("JSON-Body contains errors: " . json_last_error_msg(), 503);
  return;
}

foreach ($origPost as $name => $value)
  $_POST[$name] = $value;
