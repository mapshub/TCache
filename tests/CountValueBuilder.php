<?php

namespace TCacheTest;


use TCache\Criterias\Criteria\ValuesBuilder;

class CountValueBuilder extends ValuesBuilder
{
    public function castValueSid($id)
    {
        echo "(" . gettype($id) . ")" . $id . "=>";
        if (gettype($id) != "double") {
            $id = (double)$id;
        }
        echo "(" . gettype($id) . ")" . $id . "\n";
        return $id;
    }
}