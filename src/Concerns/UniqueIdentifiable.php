<?php

namespace Endropie\LumenRestApi\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait  UniqueIdentifiable
{
	static function bootUniqueIdentifiable(): void
	{
		static::creating(function (Model $model) {

            if (!$model->getKeyUUID())
            {
                $model->setAttribute($model->getKeyNameUUID(), Str::uuid()->toString());
            }


            return $model;
        });
	}

    public function getKeyNameUUID(): string
    {
        return $this->getKeyName();
    }

    public function getKeyUUID(): ?string
    {
        return $this->getKey();
    }

    public function getIncrementing()
    {
        return false;
    }
}
