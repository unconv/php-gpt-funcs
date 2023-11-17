<?php
require("../library/ChatGPT.php");

/**
 * Tell a joke to the user
 * @param string $joke The joke
 */
function tell_joke( $joke ) {}
// ^ define a function, just for ChatGPT

// initialize library and add function
$chatgpt = new ChatGPT( getenv("OPENAI_API_KEY") );
$chatgpt->set_model( "gpt-3.5-turbo-1106" );
$chatgpt->add_function( "tell_joke" );

// send a message
$chatgpt->umessage( "Tell me 5 jokes" );

// ask for only the function result
$response = $chatgpt->response(
    raw_function_response: true
);

// print the jokes
foreach( $response->tool_calls as $tool_call ) {
    // parse the JSON arguments
    $arguments = json_decode( $tool_call->function->arguments );

    // print the joke
    echo $arguments->joke . "\n\n";
}
