<?php

namespace App\Traits;

/**
 * Auto-set created_at / updated_at before every save().
 *
 * Mix into any DB\SQL\Mapper subclass whose table has these two columns.
 * The DB schema uses DEFAULT CURRENT_TIMESTAMP / ON UPDATE CURRENT_TIMESTAMP
 * so MySQL would handle it anyway, but setting them explicitly from PHP keeps
 * the values consistent in mapper memory before the next SELECT.
 */
trait HasTimestamps
{
    public function save(): mixed
    {
        $now = date('Y-m-d H:i:s');

        if ($this->dry()) {
            $this->set('created_at', $now);
        }

        $this->set('updated_at', $now);

        return parent::save();
    }
}
