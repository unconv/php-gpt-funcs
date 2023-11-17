<?php
// include library
require_once( "library/ChatGPT.php" );
require_once( "library/Assistant.php" );
require_once( "library/Thread.php" );
require_once( "library/Run.php" );

// get api key
$api_key = getenv( "OPENAI_API_KEY" );

// global cart array
$cart = [];

###############################################
#           FUNCTIONS FOR CHATGPT             #
###############################################

/**
 * Gets the current weather information
 *
 * @param string $location Location for which to get the weather information
 */
function get_current_weather( $location ) {
    echo "DEBUG: Getting current weather\n";
    return "The weather is nice and sunny";
}

/**
 * Adds a product to cart
 *
 * @param string $product Name of the product
 * @param int $quantity Quantity to add to cart
 */
function add_to_cart( $product, $quantity ) {
    global $cart;

    if( ! isset( $cart[$product] ) ) {
        $cart[$product] = 0;
    }

    $cart[$product] += $quantity;

    echo "DEBUG: Added product '$product' to cart.\n";
    return "Added product '$product' to cart.";
}

/**
 * Get the current products in the shopping cart
 *
 * @param string $param Always set to 'cart'
 */
function get_cart_contents( $param ) {
    global $cart;

    if( count( $cart ) == 0 ) {
        echo "DEBUG: Cart is empty\n";
        return "The cart is empty.";
    }

    $contents = ["cart" => []];

    foreach( $cart as $product => $quantity ) {
        $contents["cart"][] = [
            "name" => $product,
            "quantity" => $quantity,
        ];
    }

    echo "DEBUG: Cart contents: " . json_encode( $contents ) . "\n";
    return "Cart contents: " . json_encode( $contents );
}


###############################################
#      TERMINAL CHATBOT IMPLEMENTATION        #
###############################################

// initialize library
$chatgpt = new ChatGPT( $api_key );
$chatgpt->assistant_mode( true );

// create an assistant
$assistant = $chatgpt->create_assistant(
    name: "Library Test",
    model: "gpt-3.5-turbo-1106",

    // give system message to assistant
    instructions: "You are a chatbot on an online store. You can add products to cart by specifying a product name and a quantity to add. You can also get the current contents of the cart with the get_cart_contents function. You can provide the user with the contents of the cart when they ask",

    // give functions to assistant
    functions: [
        "get_current_weather",
        "add_to_cart",
        "get_cart_contents",
    ]
);

// use created assistant
$chatgpt->set_assistant( $assistant );

// create a thread
$thread = $chatgpt->create_thread();

// use created thread
$chatgpt->set_thread( $thread );

// print info
echo "Assistant ID: " . $assistant->get_id() . "\n";
echo "Thread ID: " . $thread->get_id() . "\n\n";

// ask for user message
echo "ChatGPT: How can I assist you today?\n";
echo "You: ";
$prompt = fgets( fopen( "php://stdin", "r" ) );
echo "\n";

// chat loop
while( true ) {
    // send user message
    $chatgpt->umessage( $prompt );

    // get response from ChatGPT
    $message = $chatgpt->response()->content;

    // ask for user message
    echo "ChatGPT: ".$message."\n";
    echo "You: ";
    $prompt = fgets( fopen( "php://stdin", "r" ) );
    echo "\n";
}
