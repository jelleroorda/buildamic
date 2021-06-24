<?php

namespace Michaelr0\Buildamic\Fieldtypes;

use Michaelr0\Buildamic\Fields\Field;

class BuildamicSection extends BuildamicBase
{
    protected static $handle = 'buildamic-section';
    protected $defaultValue = [];

    /**
     * $preProcess = true: Pre-process the data before it gets sent to the publish page.
     * $preProcess = true: Process the data before it gets saved.
     *
     * @param mixed $data
     * @param bool $preProcess
     * @return array
     */
    protected function processData($data, bool $preProcess = false)
    {
        $method = $preProcess ? 'preProcess' : 'process';

        return collect($data)->map(function ($row) use ($method) {
            $row['value'] = (new Field($row['uuid'], []))
                ->setConfig(array_merge(['type' => 'buildamic-row'], $row['config']))
                ->setParent($this->field()->parent())
                ->setParentField($this->field())
                ->setValue($row['value'])
                ->{$method}()
                ->value();

            return $row;
        })->toArray();
    }

    protected function performAugmentation($value, $shallow = false)
    {
        $method = $shallow ? 'shallowAugment' : 'augment';

        $value = collect($value)->map(function ($row) use ($method) {
            if (isset($row['config']['enabled']) && ! $row['config']['enabled']) {
                return;
            }

            return (new Field($row['uuid'], []))
                ->setConfig(array_merge(['type' => 'buildamic-row'], $row['config']))
                ->setParent($this->field()->parent())
                ->setParentField($this->field())
                ->setValue($row['value'])
                ->{$method}()->value();
        })->filter()->all();

        return $this->field()->setValue($value)->value();
    }
}