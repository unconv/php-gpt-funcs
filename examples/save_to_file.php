<?php
require("../library/ChatGPT.php");

$chat_id = "test_chat_id"; // set to null for automatic ID

$chatgpt = new ChatGPT( getenv( "OPENAI_API_KEY" ), $chat_id );

$chatgpt->savefunction( function( $message, $chat_id ) {
    file_put_contents(
        "chat_" . $chat_id . ".txt",
        json_encode( $message ) . PHP_EOL,
        FILE_APPEND
    );
} );

$chatgpt->loadfunction( function( $chat_id ) {
    $messages = @file( "chat_" . $chat_id . ".txt" );
    return array_map( function( $message ) {
        return json_decode( $message );
    }, $messages ?: [] );
} );

$messages = $chatgpt->messages();
$message_count = count( $messages );

$chatgpt->umessage( "What is " . ( ( $message_count / 2 ) + 1 ) . " * 5?" );

// Prints 5, 10, 15, 20, 25, etc. on consecutive runs
echo $chatgpt->response()->content . PHP_EOL;
echo "Chat saved to chat_" . $chat_id . ".txt" . PHP_EOL;
