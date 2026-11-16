<?php

namespace Tests\Unit;

use App\Support\PriceNormalizer;
use PHPUnit\Framework\TestCase;

class ProductPriceNormalizationTest extends TestCase
{
    public function test_supports_period_and_comma_decimal_separators(): void
    {
        // 10.50 -> 10.50
        $dot = PriceNormalizer::parse('10.50');
        $this->assertTrue($dot['valid']);
        $this->assertSame(10.50, $dot['value']);
        $this->assertSame('10.50', $dot['formatted']);
        $this->assertSame('10.50', $dot['numeric_string']);

        // 10,50 -> 10.50
        $comma = PriceNormalizer::parse('10,50');
        $this->assertTrue($comma['valid']);
        $this->assertSame(10.50, $comma['value']);
        $this->assertSame('10.50', $comma['formatted']);
        $this->assertSame('10.50', $comma['numeric_string']);

        // 1,500.50 -> 1500.50
        $usFormat = PriceNormalizer::parse('1,500.50');
        $this->assertTrue($usFormat['valid']);
        $this->assertSame(1500.50, $usFormat['value']);
        $this->assertSame('1500.50', $usFormat['formatted']);
        $this->assertSame('1500.50', $usFormat['numeric_string']);

        // 1.500,50 -> 1500.50
        $euFormat = PriceNormalizer::parse('1.500,50');
        $this->assertTrue($euFormat['valid']);
        $this->assertSame(1500.50, $euFormat['value']);
        $this->assertSame('1500.50', $euFormat['formatted']);
        $this->assertSame('1500.50', $euFormat['numeric_string']);
    }

    public function test_handles_thousands_separators_and_whole_numbers(): void
    {
        $whole = PriceNormalizer::parse('1500');
        $this->assertTrue($whole['valid']);
        $this->assertSame(1500.0, $whole['value']);
        $this->assertSame('1500', $whole['formatted']);
        $this->assertSame('1500.00', $whole['numeric_string']);

        $commaThousand = PriceNormalizer::parse('1,500');
        $this->assertTrue($commaThousand['valid']);
        $this->assertSame(1500.0, $commaThousand['value']);
        $this->assertSame('1500', $commaThousand['formatted']);
        $this->assertSame('1500.00', $commaThousand['numeric_string']);

        $multipleCommas = PriceNormalizer::parse('1,500,000');
        $this->assertTrue($multipleCommas['valid']);
        $this->assertSame(1500000.0, $multipleCommas['value']);
        $this->assertSame('1500000', $multipleCommas['formatted']);
        $this->assertSame('1500000.00', $multipleCommas['numeric_string']);

        $multipleDots = PriceNormalizer::parse('1.500.000');
        $this->assertTrue($multipleDots['valid']);
        $this->assertSame(1500000.0, $multipleDots['value']);
        $this->assertSame('1500000', $multipleDots['formatted']);

        $multipleDotsDecimal = PriceNormalizer::parse('1.500.000,50');
        $this->assertTrue($multipleDotsDecimal['valid']);
        $this->assertSame(1500000.50, $multipleDotsDecimal['value']);
        $this->assertSame('1500000.50', $multipleDotsDecimal['formatted']);

        $multipleCommasDecimal = PriceNormalizer::parse('1,500,000.50');
        $this->assertTrue($multipleCommasDecimal['valid']);
        $this->assertSame(1500000.50, $multipleCommasDecimal['value']);
        $this->assertSame('1500000.50', $multipleCommasDecimal['formatted']);
    }

    public function test_handles_single_separator_decimals(): void
    {
        $dotOne = PriceNormalizer::parse('10.5');
        $this->assertTrue($dotOne['valid']);
        $this->assertSame(10.50, $dotOne['value']);
        $this->assertSame('10.50', $dotOne['formatted']);

        $commaOne = PriceNormalizer::parse('10,5');
        $this->assertTrue($commaOne['valid']);
        $this->assertSame(10.50, $commaOne['value']);
        $this->assertSame('10.50', $commaOne['formatted']);

        $dotZero = PriceNormalizer::parse('0.75');
        $this->assertTrue($dotZero['valid']);
        $this->assertSame(0.75, $dotZero['value']);

        $commaZero = PriceNormalizer::parse('0,75');
        $this->assertTrue($commaZero['valid']);
        $this->assertSame(0.75, $commaZero['value']);

        $leadDot = PriceNormalizer::parse('.5');
        $this->assertTrue($leadDot['valid']);
        $this->assertSame(0.50, $leadDot['value']);
        $this->assertSame('0.50', $leadDot['formatted']);

        $leadComma = PriceNormalizer::parse(',5');
        $this->assertTrue($leadComma['valid']);
        $this->assertSame(0.50, $leadComma['value']);
        $this->assertSame('0.50', $leadComma['formatted']);
    }

    public function test_handles_khmer_numerals_and_symbols(): void
    {
        // Khmer digits with dot: ១០.៥០ -> 10.50
        $khmerDot = PriceNormalizer::parse('១០.៥០');
        $this->assertTrue($khmerDot['valid']);
        $this->assertSame(10.50, $khmerDot['value']);
        $this->assertSame('10.50', $khmerDot['formatted']);

        // Khmer digits with comma: ១០,៥០ -> 10.50
        $khmerComma = PriceNormalizer::parse('១០,៥០');
        $this->assertTrue($khmerComma['valid']);
        $this->assertSame(10.50, $khmerComma['value']);
        $this->assertSame('10.50', $khmerComma['formatted']);

        // Khmer digits with both separators: ១,៥០០.៥០ -> 1500.50
        $khmerFull = PriceNormalizer::parse('១,៥០០.៥០');
        $this->assertTrue($khmerFull['valid']);
        $this->assertSame(1500.50, $khmerFull['value']);
        $this->assertSame('1500.50', $khmerFull['formatted']);

        // Khmer punctuation mark khan: 10។50 -> 10.50
        $khmerKhan = PriceNormalizer::parse('10។50');
        $this->assertTrue($khmerKhan['valid']);
        $this->assertSame(10.50, $khmerKhan['value']);
        $this->assertSame('10.50', $khmerKhan['formatted']);

        // Currency symbols and whitespace
        $currencyDollar = PriceNormalizer::parse('$ 1,500.50');
        $this->assertTrue($currencyDollar['valid']);
        $this->assertSame(1500.50, $currencyDollar['value']);

        $currencyRiel = PriceNormalizer::parse('1500 ៛');
        $this->assertTrue($currencyRiel['valid']);
        $this->assertSame(1500.0, $currencyRiel['value']);
    }

    public function test_rejects_invalid_values(): void
    {
        $this->assertFalse(PriceNormalizer::parse('abc')['valid']);
        $this->assertSame('invalid_chars', PriceNormalizer::parse('abc')['error']);

        $this->assertFalse(PriceNormalizer::parse('10.50.20')['valid']);
        $this->assertSame('format', PriceNormalizer::parse('10.50.20')['error']);

        $this->assertFalse(PriceNormalizer::parse('10,,50')['valid']);
        $this->assertSame('format', PriceNormalizer::parse('10,,50')['error']);

        $this->assertFalse(PriceNormalizer::parse('-10')['valid']);
        $this->assertSame('min', PriceNormalizer::parse('-10')['error']);

        $this->assertFalse(PriceNormalizer::parse('-10.50')['valid']);
        $this->assertSame('min', PriceNormalizer::parse('-10.50')['error']);

        $this->assertFalse(PriceNormalizer::parse('.')['valid']);
        $this->assertSame('format', PriceNormalizer::parse('.')['error']);

        $this->assertFalse(PriceNormalizer::parse(',')['valid']);
        $this->assertSame('format', PriceNormalizer::parse(',')['error']);

        $this->assertFalse(PriceNormalizer::parse('')['valid']);
        $this->assertSame('required', PriceNormalizer::parse('')['error']);

        $this->assertFalse(PriceNormalizer::parse(null)['valid']);
        $this->assertSame('required', PriceNormalizer::parse(null)['error']);
    }

    public function test_normalize_returns_null_for_invalid_and_string_for_valid(): void
    {
        $this->assertSame('10.50', PriceNormalizer::normalize('10,50'));
        $this->assertSame('1500.50', PriceNormalizer::normalize('1.500,50'));
        $this->assertNull(PriceNormalizer::normalize('invalid'));
        $this->assertNull(PriceNormalizer::normalize(''));
    }
}
