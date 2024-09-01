<?php
// include library
require_once( "library/ChatGPT.php" );

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

// set system message
$chatgpt->smessage( "You are a chatbot on an online store. You can add products to cart by specifying a product name and a quantity to add. You can also get the current contents of the cart with the get_cart_contents function. You can provide the user with the contents of the cart when they ask" );

// add functions
$chatgpt->add_function( "get_current_weather" );
$chatgpt->add_function( "add_to_cart" );
$chatgpt->add_function( "get_cart_contents" );

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
