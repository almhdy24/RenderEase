<?php
//require "path/to/renderease/RenderEase.php";
require "../vendor/autoload.php";
// Initialize the renderer
$renderer = new Almhdy\RenderEase\RenderEase();

// Set variables
$renderer->set("title", "Welcome Page");
$renderer->setMultiple([
  "username" => "John",
  "message" => "This is your dashboard.",
]);

// Render template
try {
  $output = $renderer->render("welcome");
  echo $output;
} catch (Exception $e) {
  // Handle exception (optional)
  echo "An error occurred: " . $e->getMessage();
}
