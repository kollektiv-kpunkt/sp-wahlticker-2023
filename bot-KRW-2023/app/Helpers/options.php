<?php
use App\Models\Option;

function get_option($key, $default = null)
{
    $option = Option::where("key", $key)->first();
    if ($option) {
        return $option->value;
    }
    return $default;
}

function set_option($key, $value)
{
    $option = Option::where("key", $key)->first();
    if ($option) {
        $option->value = $value;
        $option->save();
    } else {
        $option = new Option();
        $option->key = $key;
        $option->value = $value;
        $option->save();
    }
}
