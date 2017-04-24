# README

## Unserializer

You can use the unserializer to get a pretty version of serialized arrays in
PHP.

It outputs a CSV with two columns: the size of the array and the contents of
the array (separated by semicolons).

### Usage

`php -f unserializer.php -- INPUT_FILE OUTPUT_FILE`