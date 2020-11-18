<?php

namespace App\Traits;

trait SanitizeTrait
{

    protected function sanitizeAllFields($data){
        $data = (array)$data;

        foreach($data as $key => $value){
            if(is_array($value)){
                $data[$key] = $this->sanitizeAllFields($value);
            } else {
                $data[$key] = $this->sanitizeField($value);
            }
        }

        return $data;
    }

    protected function sanitizeField($string){
        
        if(!$string){
            return;
        }

        $string = strip_tags($string);
        $string = preg_replace( "/\r/", "", $string);
        $string = htmlspecialchars($string, ENT_QUOTES,'ISO-8859-1', false);
        
        return $string;
    }

}

?>