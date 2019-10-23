<?php

class RestTest extends  AbstractRest {



    public function execute($input, $request)
    {
        $this->statusCode = 200;
        return $request;
    }

    public function getAllowedMethod()
    {
        return "GET";
    }

    public function needsUserAuth()
    {
        return true;
    }

    public function needsSystemAuth()
    {
        return false;
    }
}