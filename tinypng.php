<?php
/**
* @file tinypng.php
*
* @version 1.1
*
* A script that uses the TinyPNG.com API (free to a point), to compress png
* files via command line scripting. Based on example code from TinyPNG.com.
*
* USAGE: one png file must be on the command line
*
* @example:  $ php  $PATH_TO/tinypng.php  example.png
*
* @requires TinyPNG API key, php cli. 
*
* @author TinyPNG.com + minor modifications @dbsinteractive 2014-05-31
*
* NOTES:
*
* - This can be a relatively slow process if trying to process many files.
* - Running this script multiple times, results in additional shrinkage and (loss of quality)
*
* @license free using Unlicense see: http://unlicense.org/
*/

$key = 'YOUR TinyPNG.com API KEY HERE';
$file = $argv[1];
$verbose = true;

// Sanity checks
if ( empty( $file ) ) die( 'Sorry, put a png file on the command line: $ php PATH/tinypng.png $filename' . "\n" );
if ( ! is_file( $file ) ) die( 'Sorry, cannot find the file:' . " $file\n" );

// We will keep the filename the same for both input and output. Having backups is recommended.
$input = $output = $file;

// mostly from tinypng.com example API code: 
$request = curl_init();
curl_setopt_array( $request, array(
	CURLOPT_URL => "https://api.tinypng.com/shrink",
	CURLOPT_USERPWD => "api:" . $key,
	CURLOPT_POSTFIELDS => file_get_contents($input),
	CURLOPT_BINARYTRANSFER => true,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_HEADER => false,
	/* Uncomment below if you have trouble validating our SSL certificate.
		 Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
	// CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
	CURLOPT_SSL_VERIFYPEER => true
));

$response = curl_exec($request);

// for verbose output, @dbsinteractive
$results = json_decode( $response );
$input_size = $results->input->size;
$output_size = $results->output->size;
$percent = 100 - $results->output->ratio * 100 . "%";

if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
	/* Compression was successful, retrieve output from Location header. */
	$headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
	foreach (explode("\r\n", $headers) as $header) {
		if (substr($header, 0, 10) === "Location: ") {
			$request = curl_init();
			curl_setopt_array($request, array(
				CURLOPT_URL => substr($header, 10),
				CURLOPT_RETURNTRANSFER => true,
				/* Uncomment below if you have trouble validating our SSL certificate. */
				// CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
				CURLOPT_SSL_VERIFYPEER => true
			));
			file_put_contents($output, curl_exec($request));
		}
	}
	// show some stats on how well we did
	if ( $verbose ) {
		echo "$file: $input_size => $output_size - $percent\n";
	}
} else {
	print(curl_error($request));
	/* Something went wrong! */
	print("Compression failed\n");
}
