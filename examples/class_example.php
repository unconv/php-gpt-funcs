<?php
require("../library/ChatGPT.php");

class MyGPTClass {
    protected $chatgpt;

    public function __construct() {
        $this->chatgpt = new ChatGPT( getenv("OPENAI_API_KEY") );
        $this->chatgpt->add_function( [$this, "get_current_weather"] );
    }

    /**
     * Gets the current weather information
     * @param string $location The location for which to get the weather
     */
    public function get_current_weather( string $location ) { // <-- must be public
        if( $location === "California" ) {
            return "It's nice and sunny";
        } else {
            return "It's cold and windy";
        }
    }

    public function get_california_weather(): string {
        $this->chatgpt->umessage( "What's the weather like in California?" );
        return $this->chatgpt->response()->content;
    }

    public function get_alaska_weather(): string {
        $this->chatgpt->umessage( "What's the weather like in Alaska?" );
        return $this->chatgpt->response()->content;
    }
}

$mygpt = new MyGPTClass();

echo $mygpt->get_california_weather() . PHP_EOL;
echo $mygpt->get_alaska_weather() . PHP_EOL;
