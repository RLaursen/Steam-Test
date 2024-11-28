An API testing library with a fluent API similar to supertest and jest.

Designed for a specific API and according to very specific project requirements, but can be modified to suit other needs.

Zero dependencies.

## Usage Example:    
```php
test('Test Name', function() {
    // Expect a 200 status code, a response containing 'value' at 'key',
    // and a string matching '/regex/' at 'key2'.
    request('endpoint', ['arg1' => 'value'])
        ->expect(200, ['key' => 'value', 'key2' => '/regex/']);
});
```
 
 See modules for further documentation:

[`test()`/`test_group()`/`before_each()`/`after_each()`/`failing_test()`](lib/test.php)

[`request()`](lib/request.php)
