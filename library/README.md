# PHP ChatGPT Library with Function Calling

This is a simple library I have created for interacting with the ChatGPT API with function calling.

## Basic usage

Send a message and get response back:
```php
$chatgpt = new ChatGPT( "YOUR_API_KEY" );
$chatgpt->umessage( "Write a short story about a man named Mike" );

// Prints out the story
echo $chatgpt->response()->content;
```

## System message

You can set a system message to make ChatGPT behave in a specific manner.

```php
$chatgpt = new ChatGPT( "YOUR_API_KEY" );
$chatgpt->smessage( "You are a chatbot that only answers with riddles" );
$chatgpt->umessage( "What is the distance from the earth to the moon?" );

// Without numbers, I offer you a clue,
// As shadow at peak, or notch in night's hue.
// To calculate this gap is no simple boon,
// What's termed 'a round trip' by the light of the moon.
echo $chatgpt->response()->content;
```

## Streaming

You can easily stream the response from ChatGPT as plaintext or an event stream:

```php
$chatgpt = new ChatGPT( getenv("OPENAI_API_KEY") );
$chatgpt->umessage( "Write a short poem" );
$chatgpt->stream( StreamType::Plain );
```

See event stream example in [../examples/streaming_eventsource.php](../examples/streaming_eventsource.php)

## Function calling

You can pass PHP functions to the ChatGPT class with the `add_function` method. The function and parameter descriptions will be extracted automatically from the DocBlock comment. Parameter types will be extracted automatically from the function with `ReflectionFunction`.

By default, the function result will be sent back to ChatGPT and ChatGPT will respond with a message based on the function result.

```php
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

$chatgpt = new ChatGPT( "YOUR_API_KEY" );
$chatgpt->add_function( "get_current_weather" );

$chatgpt->umessage( "What's the weather like in California?" );
// It's nice and sunny in California
echo $chatgpt->response()->content . PHP_EOL;

$chatgpt->umessage( "What's the weather like in Alaska?" );
// It's cold and windy in Alaska
echo $chatgpt->response()->content . PHP_EOL;
```

## Raw function calling

Sometimes you only want to retrieve the JSON response of the function call and not actually call a local function and pass it to ChatGPT. You can set `raw_function_response` to `true` in the `response` method to get back only the raw response from ChatGPT.

```php
/**
 * Give a list of jokes to the user
 * @param array<string> $jokes The list of jokes
 */
function give_jokes( $jokes ) {
    // No function body, will not actually be called
}

$chatgpt = new ChatGPT( "YOUR_API_KEY" );
$chatgpt->add_function( "give_jokes" );

$chatgpt->umessage( "Tell me 5 jokes" );

$response = $chatgpt->response(
    raw_function_response: true
);

$arguments = json_decode( $response->function_call->arguments );

// Prints an array of jokes
print_r( $arguments->jokes );
```

## Assistants API

Since 2023-11-17, the library has basic support for the new Assistants API. You can use it as follows:

```php
$chatgpt = new ChatGPT( "YOUR_API_KEY" );
$chatgpt->assistant_mode( true );

// create and use assistant
$assistant = $chatgpt->create_assistant(
    model: "gpt-3.5-turbo"
);
$chatgpt->set_assistant( $assistant );

// create and use a thread
$thread = $chatgpt->create_thread();
$chatgpt->set_thread( $thread );

/* ... use the library as normal ... */
```

Once you have created an assistant and a thread, you can get their IDs with `$assistant->get_id()` and `$thread->get_id()` and pass the IDs into `$chatgpt->set_assistant()` and `$chatgpt->set_thread()` to load use a previously created assistant and thread.

## Saving chat history

You can save the chat history to a file (or a database) and load it from a there using the `savefunction` and `loadfunction` methods. Pass in your own function that handles the loading / saving in your preferred way.

```php
$chatgpt = new ChatGPT( "YOUR_API_KEY" );
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

// Prints out message objects
foreach( $messages as $message ) {
    print_r( $message );
}

$chatgpt->umessage( "What is " . ( ( $message_count / 2 ) + 1 ) . " * 5?" );
// Prints 5, 10, 15, 20, 25, etc. on consecutive runs
echo $chatgpt->response()->content . PHP_EOL;
```

## Custom parameters

You can use the `set_param` or `set_params` methods on the `ChatGPT` class to set your custom parameters, like `temperature` and `max_tokens`:

```php
$chatgpt = new ChatGPT( "YOUR_API_KEY" );
$chatgpt->set_params( [
    "temperature" => 0.9,
    "max_tokens" => 256,
] );
```

OR

```php
$chatgpt = new ChatGPT( "YOUR_API_KEY" );
$chatgpt->set_param( "temperature", 0.9 );
$chatgpt->set_param( "max_tokens", 256 );
```

You can use any parameters that the ChatGPT API accepts.
