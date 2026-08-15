<?php

namespace App\Support;

class Language
{
    public static function all()
    {
        return config('languages.supported', []);
    }

    public static function active()
    {
        return array_filter(static::all(), function ($language) {
            return ($language['active'] ?? true) === true;
        });
    }

    public static function activeCodes()
    {
        return array_keys(static::active());
    }

    public static function default()
    {
        $default = config('languages.default', config('app.locale', 'en'));

        if (static::isSupported($default)) {
            return $default;
        }

        return static::activeCodes()[0] ?? config('app.fallback_locale', 'en');
    }

    public static function fallback()
    {
        $fallback = config('languages.fallback', config('app.fallback_locale', static::default()));

        return static::isSupported($fallback) ? $fallback : static::default();
    }

    public static function isSupported($locale)
    {
        return is_string($locale) && in_array($locale, static::activeCodes(), true);
    }

    public static function resolve($locale = null)
    {
        return static::isSupported($locale) ? $locale : static::default();
    }

    public static function label($locale)
    {
        $language = static::all()[$locale] ?? null;

        if (!$language) {
            return $locale ?: static::label(static::default());
        }

        return $language['native_name'] ?? $language['name'] ?? $locale;
    }

    public static function options()
    {
        $options = [];

        foreach (static::active() as $locale => $language) {
            $options[$locale] = $language['native_name'] ?? $language['name'] ?? $locale;
        }

        return $options;
    }

    public static function translatedValue($values, $locale = null)
    {
        if (!is_array($values)) {
            return $values;
        }

        $locale = $locale ?: app()->getLocale();
        $fallback = static::fallback();
        $default = static::default();

        return $values[$locale]
            ?? $values[$fallback]
            ?? $values[$default]
            ?? $values['en']
            ?? reset($values)
            ?? '';
    }
}
