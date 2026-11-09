<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

abstract class SafeTextValueBinder extends DefaultValueBinder implements WithCustomValueBinder
{
    protected bool $escapeCsvFormulas = false;

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            if ($this->escapeCsvFormulas && preg_match('/^(?:[\s\p{Z}]*[=+\-@]|[\t\r\n])/u', $value)) {
                $value = "'".$value;
            }

            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
