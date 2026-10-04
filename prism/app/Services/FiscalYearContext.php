<?php

namespace App\Services;

class FiscalYearContext
{
    public ?int $year = null;
    public bool $filterReads = false;
    public bool $capturing = false;
}
