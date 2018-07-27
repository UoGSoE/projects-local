<?php

namespace App\Imports;

class PlacementDataExtractor
{
    protected $row;

    public function __construct($row)
    {
        $this->row = $row;
    }

    public function extract()
    {
    }
}