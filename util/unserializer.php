<?php

$args = $argv;
array_shift($args);

if(count($args) !== 2) {
	echo "ERROR: You must pass a file as the first argument and an output filename as the second argument.\n";
	echo "php -f unserializer.php -- INPUT_FILE OUTPUT_FILE\n";
	exit(1);
}

if(!file_exists($args[0])) {
	echo "ERROR: the input file ('{$args[0]}') is not a real file. Is the path correct?";
	exit(1);
}

if(file_exists($args[1])) {
	echo "ERROR: the output file ('{$args[1]}') already exists. I don't want to overwrite things.";
	exit(1);
}

// Read data
$lines = array();
$handle = fopen($args[0], "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $result = unserialize($line);
        if(!$result) {
        	echo "Could not unserialize line: '{$line}'. Is it the right format?";
        	exit(1);
        }

        array_push($lines, $result);
    }

    fclose($handle);
} else {
	echo "ERROR: Could not open input file '{$args[0]}'";
    exit(1);
} 

echo "--- Read {count($lines)} line(s) ---";

$handle = fopen($args[1], "w");