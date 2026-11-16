<?php

namespace App\Support;

class PriceNormalizer
{
    /**
     * Parse and normalize price or cost inputs.
     * Supports period and comma decimal separators, thousands separators,
     * Khmer numerals (០-៩), Khmer khan (។), and currency symbols.
     *
     * @param mixed $input
     * @return array{valid: bool, value: ?float, formatted: string, numeric_string: string, clean: string, error: ?string}
     */
    public static function parse($input): array
    {
        if ($input === null || $input === '') {
            return [
                'valid' => false,
                'value' => null,
                'formatted' => '',
                'numeric_string' => '',
                'clean' => '',
                'error' => 'required',
            ];
        }

        $str = trim((string) $input);

        // Convert Khmer numerals (០-៩)
        $khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
        $asciiDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $str = str_replace($khmerDigits, $asciiDigits, $str);

        // Map Khmer khan (។) to period
        $str = str_replace('។', '.', $str);

        // Remove currency symbols & whitespace
        $str = str_replace(['$', '៛', ' '], '', $str);

        if ($str === '') {
            return [
                'valid' => false,
                'value' => null,
                'formatted' => '',
                'numeric_string' => '',
                'clean' => '',
                'error' => 'required',
            ];
        }

        if (str_starts_with($str, '-')) {
            return [
                'valid' => false,
                'value' => null,
                'formatted' => '',
                'numeric_string' => '',
                'clean' => '',
                'error' => 'min',
            ];
        }

        // Allowed characters: only digits, dots, commas
        if (!preg_match('/^[0-9.,]+$/', $str)) {
            return [
                'valid' => false,
                'value' => null,
                'formatted' => '',
                'numeric_string' => '',
                'clean' => '',
                'error' => 'invalid_chars',
            ];
        }

        // Must contain at least one digit
        if (!preg_match('/[0-9]/', $str)) {
            return [
                'valid' => false,
                'value' => null,
                'formatted' => '',
                'numeric_string' => '',
                'clean' => '',
                'error' => 'format',
            ];
        }

        $hasDot = str_contains($str, '.');
        $hasComma = str_contains($str, ',');

        $clean = '';

        if ($hasDot && $hasComma) {
            $lastDot = strrpos($str, '.');
            $lastComma = strrpos($str, ',');

            if ($lastDot > $lastComma) {
                // Comma is thousands separator, dot is decimal separator (e.g. 1,500.50)
                if (substr_count($str, '.') > 1) {
                    return [
                        'valid' => false,
                        'value' => null,
                        'formatted' => '',
                        'numeric_string' => '',
                        'clean' => '',
                        'error' => 'format',
                    ];
                }
                $parts = explode('.', $str);
                $thousandPart = $parts[0];
                $decimalPart = $parts[1];

                $groups = explode(',', $thousandPart);
                if (strlen($groups[0]) < 1 || strlen($groups[0]) > 3) {
                    return [
                        'valid' => false,
                        'value' => null,
                        'formatted' => '',
                        'numeric_string' => '',
                        'clean' => '',
                        'error' => 'format',
                    ];
                }
                for ($i = 1; $i < count($groups); $i++) {
                    if (strlen($groups[$i]) !== 3) {
                        return [
                            'valid' => false,
                            'value' => null,
                            'formatted' => '',
                            'numeric_string' => '',
                            'clean' => '',
                            'error' => 'format',
                        ];
                    }
                }
                $clean = implode('', $groups) . '.' . $decimalPart;
            } else {
                // Dot is thousands separator, comma is decimal separator (e.g. 1.500,50)
                if (substr_count($str, ',') > 1) {
                    return [
                        'valid' => false,
                        'value' => null,
                        'formatted' => '',
                        'numeric_string' => '',
                        'clean' => '',
                        'error' => 'format',
                    ];
                }
                $parts = explode(',', $str);
                $thousandPart = $parts[0];
                $decimalPart = $parts[1];

                $groups = explode('.', $thousandPart);
                if (strlen($groups[0]) < 1 || strlen($groups[0]) > 3) {
                    return [
                        'valid' => false,
                        'value' => null,
                        'formatted' => '',
                        'numeric_string' => '',
                        'clean' => '',
                        'error' => 'format',
                    ];
                }
                for ($i = 1; $i < count($groups); $i++) {
                    if (strlen($groups[$i]) !== 3) {
                        return [
                            'valid' => false,
                            'value' => null,
                            'formatted' => '',
                            'numeric_string' => '',
                            'clean' => '',
                            'error' => 'format',
                        ];
                    }
                }
                $clean = implode('', $groups) . '.' . $decimalPart;
            }
        } elseif ($hasComma) {
            $commaCount = substr_count($str, ',');
            if ($commaCount > 1) {
                // Multiple commas -> thousands (e.g. 1,500,000)
                $groups = explode(',', $str);
                if (strlen($groups[0]) < 1 || strlen($groups[0]) > 3) {
                    return [
                        'valid' => false,
                        'value' => null,
                        'formatted' => '',
                        'numeric_string' => '',
                        'clean' => '',
                        'error' => 'format',
                    ];
                }
                for ($i = 1; $i < count($groups); $i++) {
                    if (strlen($groups[$i]) !== 3) {
                        return [
                            'valid' => false,
                            'value' => null,
                            'formatted' => '',
                            'numeric_string' => '',
                            'clean' => '',
                            'error' => 'format',
                        ];
                    }
                }
                $clean = implode('', $groups);
            } else {
                // Exactly 1 comma
                $parts = explode(',', $str);
                $before = $parts[0];
                $after = $parts[1];

                if ($before === '') {
                    $before = '0';
                }

                // If after is 3 digits and before is 1-3 digits (not '0'), it is thousands separator: e.g. 1,500
                if (strlen($after) === 3 && strlen($before) >= 1 && strlen($before) <= 3 && $before !== '0') {
                    $clean = $before . $after;
                } else {
                    // E.g. 10,50 -> 10.50, 10,5 -> 10.5, 0,75 -> 0.75
                    $clean = $before . ($after !== '' ? '.' . $after : '');
                }
            }
        } elseif ($hasDot) {
            $dotCount = substr_count($str, '.');
            if ($dotCount > 1) {
                // Multiple dots -> thousands (e.g. 1.500.000)
                $groups = explode('.', $str);
                if (strlen($groups[0]) < 1 || strlen($groups[0]) > 3) {
                    return [
                        'valid' => false,
                        'value' => null,
                        'formatted' => '',
                        'numeric_string' => '',
                        'clean' => '',
                        'error' => 'format',
                    ];
                }
                for ($i = 1; $i < count($groups); $i++) {
                    if (strlen($groups[$i]) !== 3) {
                        return [
                            'valid' => false,
                            'value' => null,
                            'formatted' => '',
                            'numeric_string' => '',
                            'clean' => '',
                            'error' => 'format',
                        ];
                    }
                }
                $clean = implode('', $groups);
            } else {
                // Exactly 1 dot e.g. 10.50
                $parts = explode('.', $str);
                $before = $parts[0] === '' ? '0' : $parts[0];
                $after = $parts[1];
                $clean = $before . ($after !== '' ? '.' . $after : '');
            }
        } else {
            $clean = $str;
        }

        $clean = rtrim($clean, '.');

        if ($clean === '' || !is_numeric($clean)) {
            return [
                'valid' => false,
                'value' => null,
                'formatted' => '',
                'numeric_string' => '',
                'clean' => '',
                'error' => 'format',
            ];
        }

        $val = (float) $clean;
        if ($val < 0) {
            return [
                'valid' => false,
                'value' => null,
                'formatted' => '',
                'numeric_string' => '',
                'clean' => '',
                'error' => 'min',
            ];
        }

        if (str_contains($clean, '.')) {
            $formatted = number_format($val, 2, '.', '');
        } else {
            $formatted = (string) $val;
        }

        return [
            'valid' => true,
            'value' => $val,
            'formatted' => $formatted,
            'numeric_string' => number_format($val, 2, '.', ''),
            'clean' => $clean,
            'error' => null,
        ];
    }

    /**
     * Returns standard numeric string (e.g. "10.50" or "1500.50") with period decimal separator,
     * or null if invalid.
     */
    public static function normalize($input): ?string
    {
        $res = self::parse($input);
        return $res['valid'] ? $res['numeric_string'] : null;
    }

    /**
     * Check if the input is a valid price.
     */
    public static function isValid($input): bool
    {
        return self::parse($input)['valid'];
    }
}
