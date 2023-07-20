<?php
require("../library/ChatGPT.php");

/**
 * Gets the current weather information
 * @param string $location The location for which to get the weather
 */
function get_current_weather( string $location ) {
    if( $location === "California" ) {
        return "It's nice and sunny";
    } else {
        return "It's cold and windy";
    }
}

$chatgpt = new ChatGPT( getenv("OPENAI_API_KEY") );
$chatgpt->add_function( "get_current_weather" );

$chatgpt->umessage( "What's the weather like in California?" );
// It's nice and sunny in California
echo $chatgpt->response()->content . PHP_EOL;

$chatgpt->umessage( "What's the weather like in Alaska?" );
// It's cold and windy in Alaska
echo $chatgpt->response()->content . PHP_EOL;
