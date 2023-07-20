# PHP GPT-4 Function Calling Example

This is an example code for using the OpenAI API's function calling capability. There is a chatbot demo in the repostiory, which is a command line chatbot that you can use to interact with ChatGPT.

There is also a ChatGPT PHP library in the `library/ChatGPT.php` file that can work with function calling.

The chatbot demo has been implemented both with and without the library.

The chatbot has been programmed, using GPT function calling, to answer to queries about the weather with "It's nice and sunny". It also has a shopping cart, into which you can add products and list the contents of.

## Usage

Export your OpenAI API key:

```console
$ export OPENAI_API_KEY=YOUR_API_KEY
```

Run the chatbot:
```
$ php chatbot_with_library.php
```

## Support

If this code is helpful, consider [buying me more OpenAI tokens](https://buymeacoffee.com/unconv) or subscribing to my [YouTube channel](https://youtube.com/@unconv).
