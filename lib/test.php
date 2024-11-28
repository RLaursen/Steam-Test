<?php

/*

 Usage Example:

    test_group('Group Name', function() {
        
        before_each(function() {
            set_up_test();
        });

        after_each(fn() => 
            tear_down_test()
        );

        test('Test Name', function() {
            assert(1 === 1);
        });

        failing_test('Test Name 2', function() {
            assert(1 === 2);
        });
    });

 Output:

    => Group Name:
            => Test Name: ✓
            => Test Name 2: ✗✓
    Ran 2 Tests in Group "Group Name." Passed: 2, Failed: 0.

 test_group()s may contain test_group()s and test()s, test()s may not contain other test()s.

 Which test_groups are running and the total number of tests run, passed, and failed are tracked.

 If a test fails, the test name is printed along with the error message and a stack trace.

 Within any $callback, the shared static $this->context object is available as $this. It contains the following properties:

        ->after_each  - array of arrays of functions to run after every test.
        ->before_each - array of arrays of functions to run before every test.
        ->failed      - total number of tests that have failed.
        ->in_test     - whether currently executing a test().
        ->init        - the first test_group() or test() initializes the test and should exit upon completion.
         ->levels      - array of the names of tests and test groups that are currently running.
        ->total       - total number of tests run.

 Program will exit with code 1 if any tests fail, otherwise it will exit with code 0. */

/**
 * Run a callback before every test enclosed by the current test_group().
 *
 * Example:
 *
 * before_each(function() {
 *     // Set up test.
 * });
 * 
 * Within the callback, a shared static context object is available as 
 * $this which persists as long as the current root level test() or test_group().
 * 
 * $this within the callback contains the following properties:
 * 
 * ->after_each  - array of arrays of functions to run after every test.
 * ->before_each - array of arrays of functions to run before every test.
 * ->failed      - total number of tests that have failed.
 * ->in_test     - whether currently executing a test().
 * ->init        - the first test_group() or test() initializes the test and should exit upon completion.
 * ->levels      - array of the names of tests and test groups that are currently running.
 * ->total       - total number of tests run.
 * 
 */
function
before_each(Closure $callback)
{
    global $_tester;
    return ($_tester->before_each($callback));
}

/**
 * Run a callback after every test enclosed by the current test_group().
 *
 * Example:
 *
 * after_each(function() {
 *     // Tear down test.
 * });
 * 
 * Within the callback, a shared static context object is available as 
 * $this which persists as long as the current root level test() or test_group().
 * 
 * $this within the callback contains the following properties:
 * 
 * ->after_each  - array of arrays of functions to run after every test.
 * 
 * ->before_each - array of arrays of functions to run before every test.
 * 
 * ->failed      - total number of tests that have failed.
 * 
 * ->in_test     - whether currently executing a test().
 * 
 * ->init        - the first test_group() or test() initializes the test and should exit upon completion.
 * 
 * ->levels      - array of the names of tests and test groups that are currently running.
 * 
 * ->total       - total number of tests run.
 * 
 */
function
after_each(Closure $callback)
{
    global $_tester;
    return ($_tester->after_each($callback));
}

/**
 * Run a test that is expected to fail.
 * Return true if the test fails, false if it passes.
 * 
 * Example:
 *
 * failing_test('Test Name', function() {
 *     expect(1)->to_equal(2);
 * });
 * 
 * Within the callback, a shared static context object is available as 
 * $this which persists as long as the current root level test() or test_group().
 * 
 * $this within the callback contains the following properties:
 * 
 * ->after_each  - array of arrays of functions to run after every test.
 * 
 * ->before_each - array of arrays of functions to run before every test.
 * 
 * ->failed      - total number of tests that have failed.
 * 
 * ->in_test     - whether currently executing a test().
 * 
 * ->init        - the first test_group() or test() initializes the test and should exit upon completion.
 * 
 * ->levels      - array of the names of tests and test groups that are currently running.
 * 
 * ->total       - total number of tests run.
 * 
 */
function
failing_test(string $test_name, Closure $callback): bool
{
    global $_tester;
    return ($_tester->test($test_name, $callback, true));
}

/**
 * Run a test.
 *
 * test('Test Name', function() {
 *     expect(1)->to_equal(1);
 * });
 * 
 * Within the callback, a shared static context object is available as 
 * $this which persists as long as the current root level test() or test_group().
 * 
 * $this within the callback contains the following properties:
 * 
 * ->after_each  - array of arrays of functions to run after every test.
 * 
 * ->before_each - array of arrays of functions to run before every test.
 * 
 * ->failed      - total number of tests that have failed.
 * 
 * ->in_test     - whether currently executing a test().
 * 
 * ->init        - the first test_group() or test() initializes the test and should exit upon completion.
 * 
 * ->levels      - array of the names of tests and test groups that are currently running.
 * 
 * ->total       - total number of tests run.
 * 
 */
function
test(string $test_name, Closure $callback): bool
{
    global $_tester;
    return ($_tester->test($test_name, $callback));
}

/**
 * Group tests together. Groups may be nested.
 * 
 * Returns true if all tests have passed so far within the test_group()'s hierarchy.
 * 
 * Example:
 *
 * test_group('Group Name', function() {
 *     test('Test Name 1', function() {
 *         expect(1)->to_equal(1);
 *     });
 *     test('Test Name 2', function() {
 *         expect(2)->to_equal(2);
 *     });
 * });
 * 
 * Within the callback, a shared static context object is available as 
 * $this which persists as long as the current root level test() or test_group().
 * 
 * $this within the callback contains the following properties:
 * 
 * ->after_each  - array of arrays of functions to run after every test.
 * 
 * ->before_each - array of arrays of functions to run before every test.
 * 
 * ->failed      - total number of tests that have failed.
 * 
 * ->in_test     - whether currently executing a test().
 * 
 * ->init        - the first test_group() or test() initializes the test and should exit upon completion.
 * 
 * ->levels      - array of the names of tests and test groups that are currently running.
 * 
 * ->total       - total number of tests run.
 * 
 */
function
test_group(string $group_name, Closure $callback): bool
{
    global $_tester;
    return ($_tester->group($group_name, $callback));
}

$_any_test_failed = false;
$_tester = new _Tester;

register_shutdown_function(fn() => exit((int) $_any_test_failed));

function
_get_and_format_ob(string $indent): string
{
    $logged = strtr(ob_get_contents(), [
                "\n" => "\n" . $indent
            ]);
    ob_end_clean();
    return ($logged);
}

class
_Tester
{
    public $context;
    
    public function
    __construct()
    {
        $this->reset_context();
    }

    private function
    echo_sanitized_stacktrace(array $stacktrace, string $indent): void
    {
        $context_stack = [...$this->context->levels];
        $stacktrace = array_filter(
            $stacktrace,
            fn($trace) =>
                !is_null(@$trace["file"])
                    && (dirname(@$trace["file"] ?? '') !== dirname(__FILE__))
                    && ($trace['function'] !== '{closure}')
        );
        array_walk(
            $stacktrace,
            function(array $trace) use ($indent, &$context_stack) {
                static $max_clas_method_len = 0;
                static $max_file_len        = 0;
                
                if ($trace['function'] === 'test' || $trace['function'] === 'test_group')
                    $trace['function'] = $trace['function']. ': ' . array_pop($context_stack) ?? '';
                
                $class_method = (array_key_exists('class', $trace) ? $trace['class'] . '->' : '')
                . (array_key_exists('function', $trace) ? $trace['function'] . ' ' : '');

                $max_clas_method_len = max($max_clas_method_len, mb_strlen($class_method));
                $max_file_len        = max($max_file_len,        mb_strlen($trace['file'] ?? ''));

                echo $indent . str_pad($class_method, $max_clas_method_len + 1)
                . str_pad($trace['file'] ??'', $max_file_len + 3) . str_pad($trace['line'] 
                ?? '', 4, ' ', STR_PAD_LEFT) . "\n";
            }
        );
    }

    private function
    init(): bool
    {
        if (!$this->context->init) {
            $this->context->init = true;
            return true;
        }
        return false;
    }

    private function
    reset_context()
    {
        $this->context = (object) [
            'after_each'  => [[]],
            'before_each' => [[]],
            'failed'      => 0,
            'in_test'     => false,
            'init'        => false,
            'levels'      => [],
            'total'       => 0
        ];
    }

    public function
    after_each(Closure $callback): void
    {    
        $this->context->after_each[count($this->context->after_each) - 1][] = $callback;
        if ($this->context->in_test)
            throw new LogicException('after_each() in test() detected. Use test_group() or global scope to define after_each()s.');
    }

    public function
    before_each(Closure $callback): void
    {    
        $this->context->before_each[count($this->context->before_each) - 1][] = $callback;
        if ($this->context->in_test)
            throw new LogicException('before_each() in test() detected. Use test_group() or global scope to define before_each()s.');
    }

    public function test(string $name, Closure $callback, $failing_test = false): bool
    {
        if ($this->context->in_test)
            throw new LogicException('Nested test()s detected. Use test_group()s to group test()s and other test_group()s.');
    
        global $_any_test_failed;

        $failed                  = false;
        $init                       = $this->init();
        $this->context->levels[] = $name;
        $this->context->in_test  = true;
        $this->context->total++;

        $indent = str_repeat("\t", count($this->context->levels) - 1);
        echo $indent . '=> ' . $name . ': ';
        ob_start();
        $start = microtime(true);
        try {
            array_walk_recursive(
                $this->context->before_each,
                fn(Closure $be) => Closure::bind($be, $this->context)()
            );
            $start = microtime(true);
            Closure::bind($callback, $this->context)();
            $time = microtime(true) - $start;
            array_walk_recursive(
                $this->context->after_each,
                fn(Closure $ae) => Closure::bind($ae, $this->context)()
            );
        } catch (AssertionError | Exception $error) {
            $failed = $_any_test_failed = !$failing_test;
        }
        $logged = _get_and_format_ob($indent . "\t\t") ?? '';
        echo (string) floor(($time ?? (microtime(true) - $start)) * 1000) . ' ms ';
        if (!$failed) {
            if ($failing_test)
                echo "\u{2717}";
            echo "\u{2713}";
        } else {
            $this->context->failed++;
            $failed = $_any_test_failed = true;
            if (!$failing_test) {
                echo "\u{2717}";
                echo "\n\n" . $indent . "\t";
                echo strtr($error->getMessage(), [
                    "\n" => "\n" . $indent . "\t"
                ]);
                echo "\n\n";
                $this->echo_sanitized_stacktrace($error->getTrace(), $indent . "\t");
            } else {
                echo "\u{2713}\u{2717}";
                echo "\n\n" . $indent . "\t" 
                 . 'Expected test to fail, but it passed.' . "\n";
            }
        }

        if ($logged !== '')
            echo "\n\n" . $indent . "\t" . 'Logged During Test:' 
               . "\n\n" . $indent . "\t\t" . $logged . "\n";

        $this->context->in_test = false;
        $current_level = array_pop($this->context->levels);

        if ($init) {
            echo "\n" . 'Test ' . '"' . $current_level . '" '
             . ($failed ? 'Failed.' : 'Passed.') . "\n";
            $this->reset_context();
        } else {
            echo "\n";
        }

        return !$failed;
    }

    public function group(string $name, Closure $callback): bool
    {
        global $_any_test_failed;

        $init                           = $this->init();
        $this->context->after_each[]  = [];
        $this->context->before_each[] = [];
        $this->context->in_test       = false;
        $this->context->levels[]      = $name;

        $indent = str_repeat("\t", count($this->context->levels) - 1);
        echo $indent . '=> ' . $name . ': ';
        echo "\n";

        Closure::bind($callback, $this->context)();

        $current_level = array_pop($this->context->levels);
        array_pop($this->context->after_each);
        array_pop($this->context->before_each);

        if ($init) {
            echo 'Ran ' . $this->context->total . ' Test'
             . ($this->context->total !== 1 ? 's' : '') . ' in Group '  
             . '"' . $current_level . '." Passed: '
             . $this->context->total - $this->context->failed
             . ', Failed: ' . $this->context->failed . ".\n";
            $this->reset_context();
        } else {
            echo "\n";
        }

        if ($this->context->failed > 0)
            return false;
        return true;
    }
}
?>
