<?php

$range = range(1, 20);
shuffle($range);
$originalArray = array_splice(array_values($range), 0, 10);

$filtered = array_filter($originalArray, fn($value) => $value < 10);

echo "Filtered Array: \n";
print_r($filtered);
echo "\n";
echo "Original Array: \n";
print_r($originalArray);
