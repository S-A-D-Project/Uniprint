<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PricingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingEngineSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $pricingEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingEngine = new PricingEngine();
    }

    /** @test */
    public function test_safe_math_evaluation_basic_operations()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('safeMathEvaluate');
        $method->setAccessible(true);

        // Test basic arithmetic
        $this->assertEquals(10, $method->invoke($this->pricingEngine, '5 + 5'));
        $this->assertEquals(15, $method->invoke($this->pricingEngine, '3 * 5'));
        $this->assertEquals(2, $method->invoke($this->pricingEngine, '10 / 5'));
        $this->assertEquals(5, $method->invoke($this->pricingEngine, '10 - 5'));
    }

    /** @test */
    public function test_formula_evaluation_with_placeholders()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('evaluateFormula');
        $method->setAccessible(true);

        // Test formula with subtotal placeholder
        $result = $method->invoke($this->pricingEngine, '{subtotal} * 0.1', 100);
        $this->assertEquals(10, $result);

        // Test more complex formula
        $result = $method->invoke($this->pricingEngine, '{subtotal} + 5', 50);
        $this->assertEquals(55, $result);
    }

    /** @test */
    public function test_malicious_code_injection_prevention()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('evaluateFormula');
        $method->setAccessible(true);

        // Test that malicious code is rejected
        $maliciousInputs = [
            'system("rm -rf /")',
            'exec("malicious command")',
            'file_get_contents("/etc/passwd")',
            'phpinfo()',
            '<?php echo "hack"; ?>',
            'eval("malicious code")',
            'shell_exec("ls")',
            'passthru("whoami")',
        ];

        foreach ($maliciousInputs as $input) {
            $result = $method->invoke($this->pricingEngine, $input, 100);
            // Should return 0 for invalid/malicious input
            $this->assertEquals(0, $result, "Malicious input was not properly blocked: {$input}");
        }
    }

    /** @test */
    public function test_invalid_characters_rejected()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('safeMathEvaluate');
        $method->setAccessible(true);

        $invalidInputs = [
            'abc + 5',
            '5 + $variable',
            '5 + @function()',
            '5 + [array]',
            '5 + {object}',
            '5 + "string"',
            "5 + 'string'",
            '5 + ;DROP TABLE users;',
        ];

        foreach ($invalidInputs as $input) {
            try {
                $method->invoke($this->pricingEngine, $input);
                $this->fail("Expected exception for invalid input: {$input}");
            } catch (\Exception $e) {
                $this->assertStringContains('Invalid mathematical expression', $e->getMessage());
            }
        }
    }

    /** @test */
    public function test_division_by_zero_handling()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('evaluateTokens');
        $method->setAccessible(true);

        try {
            $method->invoke($this->pricingEngine, [10, '/', 0]);
            $this->fail('Expected exception for division by zero');
        } catch (\Exception $e) {
            $this->assertStringContains('Division by zero', $e->getMessage());
        }
    }

    /** @test */
    public function test_complex_mathematical_expressions()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('safeMathEvaluate');
        $method->setAccessible(true);

        // Test order of operations
        $this->assertEquals(14, $method->invoke($this->pricingEngine, '2 + 3 * 4'));
        $this->assertEquals(20, $method->invoke($this->pricingEngine, '2 * 5 + 10'));
        $this->assertEquals(8, $method->invoke($this->pricingEngine, '20 / 5 + 4'));
    }

    /** @test */
    public function test_decimal_number_handling()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('safeMathEvaluate');
        $method->setAccessible(true);

        $this->assertEquals(7.5, $method->invoke($this->pricingEngine, '5.5 + 2'));
        $this->assertEquals(11.25, $method->invoke($this->pricingEngine, '2.25 * 5'));
        $this->assertEquals(2.5, $method->invoke($this->pricingEngine, '10 / 4'));
    }

    /** @test */
    public function test_empty_formula_handling()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('evaluateFormula');
        $method->setAccessible(true);

        // Empty formula should return 0
        $this->assertEquals(0, $method->invoke($this->pricingEngine, '', 100));
        $this->assertEquals(0, $method->invoke($this->pricingEngine, null, 100));
    }

    /** @test */
    public function test_tokenization_accuracy()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('tokenizeExpression');
        $method->setAccessible(true);

        $tokens = $method->invoke($this->pricingEngine, '10.5 + 2.25 * 3');
        $expected = [10.5, '+', 2.25, '*', 3.0];
        
        $this->assertEquals($expected, $tokens);
    }

    /** @test */
    public function test_performance_with_large_numbers()
    {
        $reflection = new \ReflectionClass($this->pricingEngine);
        $method = $reflection->getMethod('evaluateFormula');
        $method->setAccessible(true);

        // Test with large numbers
        $result = $method->invoke($this->pricingEngine, '{subtotal} * 0.001', 1000000);
        $this->assertEquals(1000, $result);

        // Test performance doesn't degrade significantly
        $start = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            $method->invoke($this->pricingEngine, '{subtotal} + 10 * 2', 500);
        }
        $end = microtime(true);
        
        // Should complete 100 calculations in under 1 second
        $this->assertLessThan(1.0, $end - $start);
    }
}
