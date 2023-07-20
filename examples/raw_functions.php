<?php
require("../library/ChatGPT.php");

/**
 * Give a list of jokes to the user
 * @param array<string> $jokes The list of jokes
 */
function give_jokes( $jokes ) {}
// ^ define a function, just for ChatGPT

// initialize library and add function
$chatgpt = new ChatGPT( getenv("OPENAI_API_KEY") );
$chatgpt->add_function( "give_jokes" );

// send a message
$chatgpt->umessage( "Tell me 5 jokes" );

// ask for only the function result
$response = $chatgpt->response(
    raw_function_response: true
);

// parse the JSON arguments
$arguments = json_decode( $response->function_call->arguments );

// print the jokes
print_r( $arguments->jokes );
