<?php
require( __DIR__ . "/../library/ChatGPT.php" );

if( isset( $_GET['stream'] ) ) {
    header( "Content-Type: text/event-stream" );
    $chatgpt = new ChatGPT( getenv("OPENAI_API_KEY") );
    $chatgpt->umessage( "Write a short poem" );
    $chatgpt->stream( StreamType::Event );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streaming Demo</title>
    <script>
    function stream_response() {
        const eventSource = new EventSource( "streaming_eventsource.php?stream=true" );

        eventSource.addEventListener( "message", function( event ) {
            let json = JSON.parse( event.data );
            document.querySelector("#response").innerHTML += json.content;
        } );

        eventSource.addEventListener( "stop", async function( event ) {
            eventSource.close();
        } );
    }
    </script>
</head>
<body>
    <button onclick="stream_response();">Stream</button>
    <pre id="response"></pre>
</body>
</html>
