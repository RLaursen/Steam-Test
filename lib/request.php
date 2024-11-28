<?php

$TEST_URL = 'http://localhost:8000';

include_once(__DIR__ . '/expect.php');

/**
 * request(string $endpoint, array $args=[], array $headers=[]): Request
 * 
 *  request() executes a request to a url containing the global constant CONFIG_TEST_URL:
 * 
 *         CONFIG_TEST_URL . '/services/' . $object . '.php?'
 * 
 *  $endpoint and $args are built into a query and appended to $url.
 * 
 *  $headers are sent as headers.
 * 
 *  request() returns an object with the following properties:
 * 
 *         ->response   - the response data JSON as an array
 * 
 *         ->info       - the response info fropm curl_getinfo
 * 
 *         ->curl_error - the error message from curl_error
 * 
 *  And a endpoint, ->expect(), with the following signatures:
 * 
 *      ->expect(array $arg1): $this;
 *             - expect response to contain $arg1 and/or strings
 *              matching regex pattern values of $arg1.
 * 
 *      ->expect(string $arg1): $this;
 *               - expect response['errors'] to contain a value matching $arg1.
 * 
 *      ->expect(int $arg1): $this;
 *             - expect status code $arg1.
 * 
 *      ->expect(string $arg1, string $arg2): $this;
 *             - expect response[$arg1] to match $arg2.
 * 
 *      ->expect(string $arg1, mixed $arg2): $this;
 *             - expect response[$arg1] to equal $arg2.
 * 
 *      ->expect(int $arg1, array | string $arg2): $this;
 *             - expect status code $arg1 and expect response['errors'] to contain
 *              a value matching $arg2 if $arg1 is an HTTP Client Error,
 *              the response to contain $arg2 if it's an HTTP Success.
 * 
 *     Expectations may also be set on the response, info, or curl_errors attached to the request object.
 * 
 *      $request->expect->response->to_contain(['key' => 'value']);
 * 
 *  Or keys of the response array
 * 
 *       // (response['key'] === 'value')
 *       $request(...)->expect->key->to_equal('value');
 * 
 *  Unmet expectations fail the enclosing test(). 
 * 
 *  Usage Example:    
 * 
 *         test('Test Name', function() {
 * 
 *              *  Expect a 200 status code, a response containing 'value' at 'key',
 *              *   and a string matching '/regex/' at 'key2'.
 * 
 *             request('endpoint', ['arg1' => 'value'])
 *                 ->expect(200, ['key' => 'value', 'key2' => '/regex/']);
 * 
 *         });
*/
function
request(string $endpoint, array $args=[], array $headers=[]): Request
{
    global $TEST_URL;
    
    // Convert associative headers array to a list of strings like 'key: value'.
    if (!array_is_list($headers)) {
        $headers = array_map(fn($key, $value) => $key . ': ' . $value, array_keys($headers), $headers);
    }
    $curl_opts = [
        CURLOPT_HTTPHEADER      => $headers, //  Send headers.
        CURLOPT_RETURNTRANSFER  => true,      //  Return the response as a string to decode as JSON.
    ];
    try {
        $object_url = $TEST_URL . $endpoint;
        $query      = http_build_query(['endpoint' => $endpoint] + $args);
        $url        = $object_url . $query;
        $curl       = curl_init($url);    
        curl_setopt_array($curl, $curl_opts);

        $res        = curl_exec($curl);
        $response   = json_decode($res, true) ?? ['errors' => [$res]];
        $info       = curl_getinfo($curl);
        $curl_error = curl_error($curl);
    } finally {
        curl_close($curl);
    }
    return (new Request($curl_error, $info, $response));
}

class
Request
{
    public function
    __construct(
        public string $curl_error,
        public array  $info,
        public array  $response
    ){}
    
    /**  Overload otherwise invalid calls to endpoints.
     * Currently just a passthrough for ->expect() to enable it to be a property as well.
     */
     public function
    __call($endpoint, $args)
    {
        return (match($endpoint) {
            'expect'
                => $this->expect(...$args),
            default
                => throw new BadendpointCallException('No endpoint called ' . $endpoint . '.')
        });
    }

    /**
     * Overload otherwise invalid property access.
     * 
     * Allows ->expect to be a property as well as a endpoint.
     * This Exposes:
     *  ->expect->curl_error,
     *  ->expect->info,
     *  ->expect->response
     *  ->expect-><key> for each <key> in $response.
     * All act as expect($value) with the corresponding value as its argument.
     * They also pass through the _Request object to after expect() endpoint is executed.
     * 
     * Additionally, values of the request array are available at their keys.
     *
     * Examples:
     *  $request('endpoint', [], $headers)
     *      ->expect->response->to_contain(['key' => 'value']);
     *
     *  $request('endpoint', [], $headers)
     *      ->expect->info->to_satisfy(fn($info) => $info['content-type'] === 'application/json');
     *
     *  $request('endpoint', [], $headers)
     *      ->expect->status->to_equal(200);
     * 
     *  (response['key'] === 'value')
     *  $value = $request('endpoint', [], $headers)
     *      ->key['value']
     */
    public function
    __get($name)
    {
        return (match($name) {
            'expect'
                => (object) [
                    'curl_error' => expect($this->curl_error,     $this),
                    'info'       => expect($this->info,           $this),
                    'response'   => expect($this->response,       $this),
                    ...array_reduce(array_keys($this->response),  fn($acc, $key) => [ 
                        ...$acc,
                        $key =>     expect($this->response[$key], $this)
                    ], [])
            ],
            default
                => $this->response[$name] ??
                    throw new BadendpointCallException('Attempting to access ' . $$name . ' on response failed.')
        });
    }

    // int $arg1, null | array | string $arg2
    private function
    http_status_code(int $arg1, null | array | string $arg2=null): void
    {
        try {
            // int $arg1, mixed $arg2
            expect($arg1)
                // $arg1 === http status code.
                ->to_equal($this->info['http_code']);
        } catch (Exception | AssertionError $e) {
            throw new ErrorException(implode('\n\n', $this->response['errors'] ?? []) . "\n\n" . $e->getMessage());
        }
        match(true) {

        // int $arg1
        is_null($arg2) =>
            // No op.
            null,

        // HTTP Success
        200 <= $arg1 && $arg1 < 300 => match(true) {
            // int $arg1, array | string $arg2
            is_array($arg2) || is_string($arg2) =>
                // response contains same or matching values as array $arg1 or matching string $arg1.
                expect($this->response)->to_contain($arg2),

            default => $this->invalid_arg2_with_http_status_code($arg2)
        },

        // HTTP Client Error
        400 <= $arg1 && $arg1 < 500 => match(true) {
            // int $arg1, array | string $arg2
            is_array($arg2) || is_string($arg2) =>
                // response['errors'] contains same or matching values as array $arg1 or matching string $arg1.
                expect($this->response['errors'])->to_contain($arg2),

            default => $this->invalid_arg2_with_http_status_code($arg2)
        },

        default => $this->unexpected_http_status_code($arg1)
        };
    }
    
    private function
    invalid_arg2_with_http_status_code(mixed $arg2): never
    {
        throw new InvalidArgumentException('$arg2 of->expect($arg1, $arg2) must be null, string, or array when $arg1 is an HTTP status code, not ' . gettype($arg2) . '.');
    }

    // string $arg1, mixed $arg2
    private function
    string_arg1(string $arg1, mixed $arg2=null): void
    {
        match (true) {

        // string $arg1
        is_null($arg2) =>
            // reponse contains error matching $arg1.
            expect($this->response['errors'])->to_contain($arg1),

        // string $arg1, string $arg2
        is_string($arg2) =>
            // response[$arg1] matches $arg2.
            expect($this->response[$arg1])->to_match($arg2),

        // string $arg1, mixed $arg2
        default =>
            // response[$arg1] === $arg2.
            expect($this->response[$arg1])->to_equal($arg2)
        };
    }
    
    private function
    unexpected_http_status_code(int $arg1): never
    {
        throw new UnexpectedValueException('Unexpected HTTP Status Code argument: ' . $arg1);
    }

    private function
    expect(array | int | string $arg1, mixed $arg2=null): Request
    {
        match (true) {

        // array $arg1
        is_array($arg1) && is_null($arg2) =>
            // response contains same or matching values as $arg1.
            expect($this->response)->to_contain($arg1),

        // int $arg1, null | array | string $arg2
        is_int($arg1) =>
            // $arg1 === http status code and check $arg2 if present.
            $this->http_status_code($arg1, $arg2),

        // string $arg1, mixed $arg2
        is_string($arg1) => 
            // Check for values matching $arg2 at response[$arg1] or, with null $arg2, match $arg1 with an error.
            $this->string_arg1($arg1, $arg2),

        default => throw new InvalidArgumentException(
            'Invalid arguments for request->expect():' . "\n\t" . '$arg1: ' . gettype($arg1) . "\n\t" . '$arg2: ' . gettype($arg2)
        )
        };
        return ($this);
    }

    /**
     *  Prints human readable response with ->print() in the call chain.
     * 
     *  With boolean true as an argument, prints curl_error and curl_getinfo.
     * 
     *  Example:
     * 
     *         request('endpoint', [], $headers)
     *             ->print()
     *             ->expect(200);
     * 
     *  Output:
     * 
     *         Array
     *        (
     *            [status] => 200
     *        )
     * 
     */
    public function
    print($verbose=false): Request
    {
        print_r($this->response);
        if ($verbose) {
            echo "\n";
            print_r($this->info);
            echo "\n";
            print_r($this->curl_error);
        }
        return ($this);
    }
}

?>
