<?php

function Validate($required_params, $args){
    $diff = array_diff($required_params, array_keys($args));
    if(!empty($diff))
        throw new Exception("The following parameters are missing: " . implode(", ", $diff), 1);
}