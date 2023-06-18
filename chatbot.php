<?php
$api_key = getenv( "OPENAI_API_KEY" );

$cart = [];

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

function function_is_available( $function_name, $functions ) {
    foreach( $functions as $function ) {
        if( $function["name"] == trim( $function_name ) ) {
            return true;
        }
    }

    return false;
}

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

function send_message( array $message, $functions, $api_key, array $messages = [] ) {
    if( empty( $messages ) ) {
        $messages[] = [
            "role" => "system",
            "content" => "You are a chatbot on an online store. You can add products to cart by specifying a product name and a quantity to add. You can also get the current contents of the cart with the get_cart_contents function. You can provide the user with the contents of the cart when they ask"
        ];
    }

    $messages[] = $message;

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

    $curl_exec = curl_exec( $ch );
    $response = json_decode( $curl_exec );

    if( ! isset( $response->choices[0]->message ) ) {
        if( isset( $response->choices[0]->error ) ) {
            $error = $response->choices[0]->error;
        } else {
            $error = $curl_exec;
        }
        throw new \Exception( "Error in OpenAI request: " . $error );
    }

    $messages[] = $response->choices[0]->message;

    return $messages;
}

echo "ChatGPT: What would you like to know?\n";
echo "You: ";
$prompt = fgets( fopen( "php://stdin", "r" ) );

$messages = send_message( [
    "role" => "user",
    "content" => $prompt,
], $functions, $api_key );

$message = $messages[count($messages)-1];

while( true ) {
    if( isset( $message->function_call ) ) {
        $function_call = $message->function_call;
        $function_name = $function_call->name;
        $arguments = json_decode( $function_call->arguments, true );
    
        if( function_is_available( $function_name, $functions ) ) {
            $result = $function_name(...array_values($arguments));
        } else {
            $result = "Function '$function_name' unavailable.";
        }
    
        $message = [
            "role" => "function",
            "content" => $result,
            "name" => $function_name,
        ];
    
        $messages = send_message( $message, $functions, $api_key, $messages );
    
        $message = $messages[count($messages)-1];
    } else {
        echo "ChatGPT: " . $message->content . "\n";
        echo "You: ";
        $prompt = fgets( fopen( "php://stdin", "r" ) );
        
        $messages = send_message( [
            "role" => "user",
            "content" => $prompt,
        ], $functions, $api_key );
        
        $message = $messages[count($messages)-1];
    }
}
