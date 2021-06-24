<?php

namespace Michaelr0\Buildamic\Fieldtypes;

use Michaelr0\Buildamic\Fields\Field;

class BuildamicSet extends BuildamicBase
{
    protected static $handle = 'buildamic-set';

    protected function performAugmentation($value, $shallow = false)
    {
        $parent = $this;
        $method = $shallow ? 'shallowAugment' : 'augment';

        $buildamicConfig = $parent->field()->parentField()->parentField()->parentField()->parentField()->config();
        $setConfg = collect($buildamicConfig['sets'][$this->config('config.statamic_settings.handle')]['fields'] ?? []);

        $fields = [];

        foreach ($value as $handle => $value) {
            $fieldConfig = $setConfg->firstWhere('handle', $handle);
            $config = array_merge($fieldConfig['field'] ?? [], $this->config("config.statamic_settings.field.{$handle}") ?? []);

            $fields[] = (new Field($handle, []))
                ->setConfig($config)
                ->setBuildamicSettings($this->field()->buildamicSettings())
                ->setParent($parent->field()->parent())
                ->setParentField($parent->field())
                ->setValue($value ?? null)
                ->{$method}();
        }

        $value = (new Field($this->handle(), []))
            ->setConfig(array_merge($this->config() ?? [], ['type' => 'sets']))
            ->setBuildamicSettings($this->field()->buildamicSettings())
            ->setParent($parent->field()->parent())
            ->setParentField($parent->field())
            ->setValue($fields ?? null)
            ->{$method}();

        return $this->field()->setValue($value)->value();
    }
}