<?php

namespace App\Helpers;

class InputBlueprints
{
    protected static array $blueprints = [
        'button' => "<input type='button' name='{name}' value='{value}' {attrs}>",
        'checkbox' => "<input type='checkbox' name='{name}' value='{value}' {checked} {attrs}>",
        'color' => "<input type='color' name='{name}' value='{value}' {attrs}>",
        'date' => "<input type='date' name='{name}' value='{value}' {attrs}>",
        'datetime-local' => "<input type='datetime-local' name='{name}' value='{value}' {attrs}>",
        'email' => "<input type='email' name='{name}' value='{value}' {attrs}>",
        'file' => "<input type='file' name='{name}' {attrs}>",
        'hidden' => "<input type='hidden' name='{name}' value='{value}'>",
        'image' => "<input type='image' name='{name}' src='{value}' {attrs}>",
        'month' => "<input type='month' name='{name}' value='{value}' {attrs}>",
        'number' => "<input type='number' name='{name}' value='{value}' {attrs}>",
        'password' => "<input type='password' name='{name}' value='{value}' {attrs}>",
        'radio' => "<input type='radio' name='{name}' value='{value}' {checked} {attrs}>",
        'range' => "<input type='range' name='{name}' value='{value}' {attrs}>",
        'reset' => "<input type='reset' value='{value}' {attrs}>",
        'search' => "<input type='search' name='{name}' value='{value}' {attrs}>",
        'submit' => "<input type='submit' value='{value}' {attrs}>",
        'tel' => "<input type='tel' name='{name}' value='{value}' {attrs}>",
        'text' => "<input type='text' name='{name}' value='{value}' {attrs}>",
        'time' => "<input type='time' name='{name}' value='{value}' {attrs}>",
        'url' => "<input type='url' name='{name}' value='{value}' {attrs}>",
        'week' => "<input type='week' name='{name}' value='{value}' {attrs}>",
        'textarea' => "<textarea name='{name}' {attrs}>{value}</textarea>",
        'select' => "<select name='{name}' {attrs}>{options}</select>",
    ];

    protected static array $custom = [];

    public static function registerCustom(string $type, callable $callback)
    {
        self::$custom[$type] = $callback;
    }

    public static function get(string $type, array $options): string
    {
        $name  = $options['name'] ?? '';
        $value = $options['value'] ?? '';
        $attrs = $options['attrs'] ?? '';

        switch($type) {
            case 'textarea':
                return "<textarea name='{$name}' class='{$attrs}'>{$value}</textarea>";
            case 'select':
                $optionsHtml = $options['options'] ?? '';
                return "<select name='{$name}' class='{$attrs}'>{$optionsHtml}</select>";
            case 'checkbox':
                $checked = $value ? 'checked' : '';
                return "<input type='checkbox' name='{$name}' value='1' class='{$attrs}' {$checked}>";
            default: // text, email, number, password, file, datetime, etc.
                return "<input type='{$type}' name='{$name}' value='{$value}' class='{$attrs}'>";
        }
    }
}