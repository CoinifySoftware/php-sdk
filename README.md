# php-sdk
A PHP wrapper for Coinify merchant API and callbacks

This PHP-SDK consists of two classes, `CoinifyApi` and `CoinifyCallback`, which are designed to make it easier for you,
the developer, to utilize the [Coinify API](https://coinify.com/docs/api) and validate [IPN callbacks](https://coinify.com/docs/api/#callbacks) from Coinify, respectively. 

## CoinifyAPI

### Creating an instance
The `CoinifyAPI` class is instantiated as follows:

```
$api_key = "<my_api_key>";
$api_secret = "<my_api_secret>";
$api = new CoinifyAPI($api_key, $api_secret);
```

### Response format
The `CoinifyAPI` returns responses as they are described in [Response format](https://coinify.com/docs/api/#response-format) in the API documentation.

The JSON responses are automatically decoded into PHP associative arrays, so you can for example do the following:

```
$response = $api->invoicesList();
if ( $response === false ) {
    /*
     * A false response means a curl error
     */
    return "cURL error: " . $api->last_curl_error . " (" . $api->last_curl_errno . ")";
}

if ( !$response['success'] ) {
    $api_error = $response['error'];
    return "API error: " . $api_error['message'] . " (" . $api_error['code'] . ")";
}

$invoices = $response['data'];
```

### Invoices
With the [Coinify invoice API](https://coinify.com/docs/api/#invoices), you can *list* all your invoices, *create* new invoices, *get* a specific invoice and *update* an existing invoice as follows:

#### Listing all invoices
```
$response = $api->invoicesList();
```

#### Creating a new invoice
**Example:** Create an invoice for 20 USD.

```
$plugin_name = 'MyPlugin';
$plugin_version = '1';

$response = $api->invoiceCreate(20.0, "USD", $plugin_name, $plugin_version);
```

#### Get a specific invoice
```
$invoice_id = 12345;
$response = $api->invoiceGet($invoice_id);
```

#### Update an existing invoice
```
$invoice_id = 12345;
$response = $api->invoiceGet($invoice_id);
```

### Catching errors
As touched upon in the "Response format" section, if the responses from the API calls are `false`, an error occurred with cURL trying to communicate with the API. The last cURL error and error number can be retrieved with `$api->last_curl_error` and `$api->last_curl_errno`.

If the response is not `false`, but instead an (associative) array, the [response format](https://coinify.com/docs/api/#response-format) from the API documentation is used, which can communicate an error (if `$response['success']` is `false`) or a successful API call (if `$response['success']` is `true`).


## Validating callbacks
If you choose to receive HTTP callbacks for when your invoice state changes and handle them with PHP code, you can use the `CoinifyCallback` class to validate the callback - i.e. to make sure that the callback came from Coinify, and not some malicious entity:

```
$ipn_secret = '<my_ipn_secret>';
$callback_validator = new CoinifyCallback($ipn_secret);

$postdata_raw = file_get_contents('php://input');

$is_valid = $callback_validator->validateCallback($postdata_raw);
```