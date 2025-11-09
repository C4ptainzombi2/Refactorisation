<?php
function loadStructures() {
  $json = @file_get_contents(JSON_FILE);
  return $json ? json_decode($json, true) : ['structures' => []];
}

function saveStructures($data) {
  file_put_contents(JSON_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
