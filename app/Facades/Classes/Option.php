<?php

namespace App\Facades\Classes;

use App\Models\Option as OptionModel;

class Option{
    
    public function update($key, $value, $lang = null){
        $option = OptionModel::where('option_key', $key);
        if ($lang) {
            $option->where('lang_code', $lang);
        } else {
            $option->whereNull('lang_code');
        }
        $option = $option->first();
        if (!$option) {
            $option = new OptionModel();
        }
        $option->option_key = $key;
        $option->value = $value;
        $option->lang_code = $lang;
        return $option->save();
    }
    
    public function get($key, $lang = null, $default = null){
       $value =  OptionModel::getOption($key, $lang);
       if (!$value) {
           return $default;
       }
       return $value;
    }
}

