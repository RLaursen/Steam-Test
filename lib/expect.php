<?php

// Handles request.php's expectations.

function
_array_contains_other_array(array $arr, array $other_arr): bool
{
    return array_reduce(
        array_keys($other_arr),
        fn($match, $key) => $match = $match && match(true) {
            !array_key_exists($key, $arr)
                => false,
            is_array($other_arr[$key])
                => _array_contains_other_array($arr[$key], $other_arr[$key]),
            _is_regex($other_arr[$key])
                => preg_match($other_arr[$key], $arr[$key]) > 0,
            default => $arr[$key] === $other_arr[$key]
        },
        true
    ) || array_reduce(
            array_keys($arr),
            fn($match, $value) => is_array($arr[$value]) 
                ? $match || _array_contains_other_array($arr[$value], $other_arr)
                : $match,
            false
        );
}

class
_Expect
{
    public function
    __construct(
        private mixed $value,
        private mixed $passthrough = null
    ){}

    public function
    all(callable $callback)
    {    
        foreach ($this->value as $value) {
            assert($callback($value), 'Expected all values in ' . var_export($this->value, true) . ' to satisfy callback.');
        }
        return $this->passthrough;
    }

    public function
    any(callable $callback)
    {
        assert(
            array_sum(array_map(
                fn($value)=>$callback($value),
                $this->value
            )), 
            'Expected any value in ' . var_export($this->value, true) . ' to satisfy callback.'
        );
        return $this->passthrough;
    }

    public function
    to_contain(mixed $other_value)
    {
        assert(match(true) {
            is_array($other_value) 
                => _array_contains_other_array($this->value, $other_value),
            is_string($other_value) && _is_regex($other_value)
                => !!preg_grep($other_value, $this->value),
            default
                => in_array($other_value, $this->value, true)
        }, 'Expected ' . var_export($this->value, true) . ' to contain ' . var_export($other_value, true) . '.');
        return $this->passthrough;
    }

    public function
    to_equal(mixed $other_value)
    {
        assert(
            $other_value === $this->value,
            'Expected ' . var_export($this->value, true) . ', got ' . var_export($other_value, true) . '.'
        );
        return $this->passthrough;
    }

    public function
    to_match(string $other_value)
    {
        assert(match(true) {
            _is_regex($other_value)
                => preg_match($other_value, $this->value) > 0,
            default
                => $other_value === $this->value
        }, 'Expected ' . var_export($this->value, true) . ' to match ' . var_export($other_value, true) . '.');
        return $this->passthrough;
    }

    public function
    to_not_equal(mixed $other_value)
    {
        assert(
            $other_value !== $this->value,
            'Expected anything but ' . var_export($this->value, true) . ', got ' . var_export($other_value, true) . '.'
        );
        return $this->passthrough;
    }

    public function
    to_satisfy(callable $callback)
    {
        assert($callback($this->value), 'Expected ' . var_export($this->value, true) . ' to satisfy callback.');
        return $this->passthrough;
    }
}

function
_is_regex(string $value): bool
{
    return (@preg_match($value, '') !== false);
}

function
expect(mixed $value, mixed $passthrough=null): _Expect
{
    return (new _Expect($value,  $passthrough));
}

?>
