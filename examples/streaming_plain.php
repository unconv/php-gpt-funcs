<?php
require( __DIR__ . "/../library/ChatGPT.php" );

$chatgpt = new ChatGPT( getenv("OPENAI_API_KEY") );
$chatgpt->umessage( "Write a short poem" );
$chatgpt->stream( StreamType::Plain );

echo PHP_EOL;
