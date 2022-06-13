<?php

namespace Endropie\LumenRestApi\Support;

use Endropie\LumenRestApi\Http\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Stringable;

class Filterable
{
    protected $filterable;

    protected $builder;

    protected $request;

    public function __construct(Filter $filterable)
    {
        $this->filterable = $filterable;
    }

    public function builder(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->getParameters() as $name => $value) {

            if (gettype($value) == 'array') {
                foreach ($value as $key => $val) $this->setBuilder($name, $val, $key);
                continue;
            }
            $this->setBuilder($name, (string) $value);
        }

        return $this->builder;
    }

    public function setBuilder($name, $value, $key = null)
    {
        $columns = $this->getColumns();

        $function = (string) $this->stringable($name)->camel();


        if (method_exists($this->filterable, $function)) {

            $this->filterable->$function($value ?? null, $key);

        } else if (strlen($value) && in_array($name, $columns)) {

            $operator = $this->getOperator($name, $key);

            $this->builder->where($name, $operator, $value);
        }
    }

    protected function getOperator($name, $key = null)
    {

        $append = "$key"  ? ("." . $key) : "";

        if ($operator = $this->filterable->getRequest()->input($name . "__operator" . $append)) {

            $operators = $this->builder->getQuery()->operators;

            if (in_array($operator, $operators)) return $operator;
        }

        return "=";
    }

    public function getColumns()
    {
        $tableName = $this->builder->getQuery()->from;
        return app('db')->getSchemaBuilder()->getColumnListing($tableName);
    }

    public function getParameters()
    {
        return  $this->filterable->getRequest()->except($this->filterable->getExcept());
    }

    public function stringable($str)
    {
        return new Stringable($str);
    }
}
