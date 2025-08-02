<?php

namespace App\ValueObjects;

abstract class SequencedObject extends AbstractObject
{
    public int $_sequence = 0;

    abstract public function getSequence() : string;

    public function hasSequence() : bool
    {
        return ($this->_sequence > 0);
    }
}
