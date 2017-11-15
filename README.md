# php-sdk
A PHP wrapper for Coinify merchant API and callbacks

This PHP-SDK consists of two classes, `CoinifyApi` and `CoinifyCallback`, which are designed to make it easier for you,
the developer, to utilize the [Coinify API](https://merchant.coinify.com/docs/api) and validate [IPN callbacks](https://merchant.coinify.com/docs/api/#callbacks) from Coinify, respectively. 

## CoinifyAPI

### Creating an instance
The `CoinifyAPI` class is instantiated as follows:

```php
$api_key = "<my_api_key>";
$api_secret = "<my_api_secret>";
$api = new CoinifyAPI($api_key, $api_secret);
```

### Response format
The `CoinifyAPI` returns responses as they are described in [Response format](https://merchant.coinify.com/docs/api/#response-format) in the API documentation.

The JSON responses are automatically decoded into PHP associative arrays, so you can for example do the following:

```php
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

### Rates
With the [Coinify rates API](https://merchant.coinify.com/docs/api/#rates) you can *list* the current exchange rates for all supported currencies. 
Returned rates will define the exchange rate for the number of fiat currency units equivalent to one BTC.

This end-point is public and no API key/secret is needed.

`buy` is the rate for buying BTC from Coinify.

`sell` is the rate for selling BTC to Coinify.

#### Listing rates for all currencies
```php
$response = $api->ratesGet();
```

#### Listing rates for a specific currency
```php
// f.e. $currency = 'USD';
$response = $api->ratesGet($currency);
```

### Account
With the [Coinify account API](https://merchant.coinify.com/docs/api/#account) you can execute operations or get data regarding your merchant account.

#### Check account balance
```php
$response = $api->balanceGet();
```

### Invoices
With the [Coinify invoice API](https://merchant.coinify.com/docs/api/#invoices), you can *list* all your invoices, *create* new invoices, *get* a specific invoice and *update* an existing invoice as follows:

#### Listing all invoices
```php
$response = $api->invoicesList();
```

The interface for the `invoiceList` method is the following:

```php
public function invoicesListGet();
```

#### Creating a new invoice
**Example:** Create an invoice for 20 USD.

```php
$plugin_name = 'MyPlugin';
$plugin_version = '1';

$response = $api->invoiceCreate(20.0, "USD", $plugin_name, $plugin_version);
```

The interface for the `invoiceCreate` method is the following:

```php
public function invoiceCreate($amount, $currency, 
    $plugin_name, $plugin_version,
    $description=null, $custom=null, 
    $callback_url=null, $callback_email=null, 
    $return_url=null, $cancel_url=null,
    $input_currency=null, $input_return_address=null);
```

#### Get a specific invoice
```php
$invoice_id = 12345;
$response = $api->invoiceGet($invoice_id);
```

The interface for the `invoiceGet` method is the following:

```php
public function invoiceGet($invoice_id);
```

#### Update an existing invoice
```php
$invoice_id = 12345;
$response = $api->invoiceUpdate($invoice_id, 'Updated description');
```

The interface for the `invoiceUpdate` method is the following:

```php
public function invoiceUpdate($invoice_id, $description=null, $custom=null);
```

#### Pay with another input currency
```php
$invoice_id = 12345;
$currency = 'LTC';
$return_address = 'Ler4HNAEfwYhBmGXcFP2Po1NpRUEiK8km2';
$response = $api->invoiceInputCreate($invoice_id, $currency, $return_address);
```

The interface for the `invoiceInputCreate` method is the following:

```php
public function invoiceInputCreate($invoice_id, $currency, $return_address);
```

### Buy orders
With the [Coinify Buy order API](https://merchant.coinify.com/docs/api/#buy-orders), *preapproved* merchants
can use their fiat account balance to buy bitcoins. The API exposes methods
for *listing* all buy orders, *getting* a specific buy order, and *create* and *confirm*
new buy orders:


#### Listing all buy orders
```php
$response = $api->buyOrdersList();
```

The interface for the `buyOrdersList` method is the following:

```php
public function buyOrdersList();
```

#### Get a specific buy order
```php
$buy_order_id = 12345;
$response = $api->buyOrderGet($buy_order_id);
```

The interface for the `buyOrderGet` method is the following:

```php
public function buyOrderGet($buy_order_id);
```

#### Creating a new buy order
**Example:** Buy bitcoins for 100 USD.

```php
$amount = 100;
$currency = 'USD';
$btc_address = '<my_bitcoin_address>';

$response = $api->buyOrderCreate( $amount, $currency, $btc_address );
```

The interface for the `buyOrderCreate` method is the following:

```php
public function buyOrderCreate( $amount, $currency, $btc_address, 
    $instant_order=null, $callback_url=null, $callback_email=null );
```

#### Confirming a buy order
```php
$buy_order_id = 12345;
$response = $api->buyOrderConfirm($buy_order_id);
```

The interface for the `buyOrderConfirm` method is the following:

```php
public function buyOrderConfirm($buy_order_id);
```

### Input currencies
Apart from receiving payments in Bitcoin (`BTC`), we also support a range of other input currencies such as Litecoin (`LTC`), Ether (`ETH`), and Dogecoin (`DOGE`).

#### Supported input currencies
```php
$response = $api->inputCurrenciesList();
```

### Catching errors
As touched upon in the "Response format" section, if the responses from the API calls are `false`, an error occurred with cURL trying to communicate with the API. The last cURL error and error number can be retrieved with `$api->last_curl_error` and `$api->last_curl_errno`.

If the response is not `false`, but instead an (associative) array, the [response format](https://merchant.coinify.com/docs/api/#response-format) from the API documentation is used, which can communicate an error (if `$response['success']` is `false`) or a successful API call (if `$response['success']` is `true`).


## Validating callbacks
If you choose to receive HTTP callbacks for when your invoice state changes and handle them with PHP code, you can use the `CoinifyCallback` class to validate the callback - i.e. to make sure that the callback came from Coinify, and not some malicious entity:

```php
$ipn_secret = '<my_ipn_secret>';
$callback_validator = new CoinifyCallback($ipn_secret);

$postdata_raw = file_get_contents('php://input');

$is_valid = $callback_validator->validateCallback($postdata_raw);
```