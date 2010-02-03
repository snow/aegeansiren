<?php

abstract class AgsLucifer extends CComponent
{
    public function init()
    {
        echo '<pre>';
        $this->doEvil();
        echo '</pre>';
    }

    /**
     * write evil codes here
     */
    abstract protected function doEvil();
}