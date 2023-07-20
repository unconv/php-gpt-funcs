<?php
// get api key
$api_key = getenv( "OPENAI_API_KEY" );

// global cart array
$cart = [];

###############################################
#      "REAL" FUNCTIONS FOR CHATGPT           #
###############################################

function get_current_weather( $location ) {
    return "The weather is nice and sunny";
}

function add_to_cart( $product, $quantity ) {
    global $cart;

    if( ! isset( $cart[$product] ) ) {
        $cart[$product] = 0;
    }

    $cart[$product] += $quantity;

    return "Added product '$product' to cart.";
}

function get_cart_contents( $param ) {
    global $cart;

    if( count( $cart ) == 0 ) {
        return "The cart is empty.";
    }

    $contents = ["cart" => []];

    foreach( $cart as $product => $quantity ) {
        $contents["cart"][] = [
            "name" => $product,
            "quantity" => $quantity,
        ];
    }

    return "Cart contents: " . json_encode( $contents );
}

###############################################
#      FUNCTION DEFINITIONS FOR CHATGPT       #
###############################################

$functions = [
    [
        "name" => "get_current_weather",
        "description" => "Gets the current weather information",
        "parameters" => [
            "type" => "object",
            "properties" => [
                "location" => [
                    "type" => "string",
                    "description" => "Location for which to get the weather information",
                ],
            ],
            "required" => ["location"],
        ]
    ],
    [
        "name" => "add_to_cart",
        "description" => "Adds a product to cart",
        "parameters" => [
            "type" => "object",
            "properties" => [
                "product" => [
                    "type" => "string",
                    "description" => "Name of the product",
                ],
                "quantity" => [
                    "type" => "integer",
                    "description" => "Quantity to add to cart",
                ],
            ],
            "required" => ["product", "quantity"],
        ],
    ],
    [
        "name" => "get_cart_contents",
        "description" => "Get the current products in the shopping cart",
        "parameters" => [
            "type" => "object",
            "properties" => [
                "cart" => [
                    "type" => "string",
                    "description" => "Always set to 'cart'",
                ],
            ],
            "required" => [],
        ],
    ]
];


###############################################
#              HELPER FUNCTION                #
###############################################

function function_is_available( $function_name, $functions ) {
    foreach( $functions as $function ) {
        if( $function["name"] == trim( $function_name ) ) {
            return true;
        }
    }

    return false;
}


###############################################
#     FUNCTION TO INTERACT WITH CHATGPT       #
###############################################

function send_message( array $message, $functions, $api_key, array $messages = [] ) {
    // set system message on first call
    if( empty( $messages ) ) {
        $messages[] = [
            "role" => "system",
            "content" => "You are a chatbot on an online store. You can add products to cart by specifying a product name and a quantity to add. You can also get the current contents of the cart with the get_cart_contents function. You can provide the user with the contents of the cart when they ask"
        ];
    }

    // add user message to message list
    $messages[] = $message;

    // make ChatGPT API request
    $ch = curl_init( "https://api.openai.com/v1/chat/completions" );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $api_key"
    ] );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode(
        [
            "model" => "gpt-4-0613",
            "messages" => $messages,
            "functions" => $functions,
            "function_call" => "auto",
        ]
    ) );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    // get ChatGPT reponse
    $curl_exec = curl_exec( $ch );
    $response = json_decode( $curl_exec );

    // somewhat handle errors
    if( ! isset( $response->choices[0]->message ) ) {
        if( isset( $response->error ) ) {
            $error = trim( $response->error->message . " (" . $response->error->type . ")" );
        } else {
            $error = $curl_exec;
        }
        throw new \Exception( "Error in OpenAI request: " . $error );
    }

    // add response to messages
    $messages[] = $response->choices[0]->message;

    // return old messages + user message + chatgpt response
    return $messages;
}

###############################################
#      TERMINAL CHATBOT IMPLEMENTATION        #
###############################################

// ask for user message
echo "ChatGPT: How can I assist you today?\n";
echo "You: ";
$prompt = fgets( fopen( "php://stdin", "r" ) );
echo "\n";

// send message to ChatGPT
$messages = send_message( [
    "role" => "user",
    "content" => $prompt,
], $functions, $api_key );

// get response from ChatGPT
$message = $messages[count($messages)-1];

// chat loop
while( true ) {
    // if received message was a function call
    if( isset( $message->function_call ) ) {
        // get function name and arguments
        $function_call = $message->function_call;
        $function_name = $function_call->name;
        $arguments = json_decode( $function_call->arguments, true );
    
        // call function if it is available
        if( function_is_available( $function_name, $functions ) ) {
            $result = $function_name(...array_values($arguments));
        } else {
            $result = "Function '$function_name' unavailable.";
        }
    
        // create function result message
        $message = [
            "role" => "function",
            "content" => $result,
            "name" => $function_name,
        ];
    
        // send function result to ChatGPT
        $messages = send_message( $message, $functions, $api_key, $messages );
    
        // save ChatGPT response for the chat loop
        $message = $messages[count($messages)-1];
    } else {
        // if we received a normal message, show the message
        echo "ChatGPT: " . $message->content . "\n";

        // and ask for a user message
        echo "You: ";
        $prompt = fgets( fopen( "php://stdin", "r" ) );
        echo "\n";
        
        // send user message to ChatGPT
        $messages = send_message( [
            "role" => "user",
            "content" => $prompt,
        ], $functions, $api_key );
        
        // save ChatGPT response for the chat loop
        $message = $messages[count($messages)-1];
    }
}
