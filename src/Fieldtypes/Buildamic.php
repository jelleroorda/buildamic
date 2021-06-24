<?php

namespace Michaelr0\Buildamic\Fieldtypes;

use Michaelr0\Buildamic\BuildamicRenderer;
use Michaelr0\Buildamic\Fields\Field;
use Michaelr0\Buildamic\Fields\Fields;

class Buildamic extends BuildamicBase
{
    protected static $handle = 'buildamic';
    protected $selectable = true;
    protected $defaultValue = [];

    public function fields()
    {
        return new Fields($this->config('fields'), $this->field()->parent(), $this->field());
    }

    public function set(string $setHandle)
    {
        return new Fields($this->config("sets.{$setHandle}.fields"), $this->field()->parent(), $this->field());
    }

    protected function configFieldItems(): array
    {
        return [
            'fields' => [
                'display' => __('Fields'),
                'type' => 'fields',
            ],
            'sets' => [
                'display' => __('Sets'),
                'type' => 'sets',
            ],
        ];
    }

    /**
     * Load data into meta.
     *
     * @param mixed $data
     * @return array
     */
    public function preload()
    {
        $instance = $this;

        return [
            'fields' => collect($this->fields()->all())->map(function ($field) {
                return [
                    'handle' => $field->handle(),
                    'meta' => $field->meta(),
                    'value' => $field->fieldtype()->preProcess($field->defaultValue()),
                    'config' => $field->config(),
                ];
            })->toArray(),
            'sets' => collect($this->config('sets'))->map(function ($set, $handle) use ($instance) {
                $fields = [
                    'handle' => $handle,
                    'display' => $set['display'],
                    'fields' => [],
                ];

                foreach ($instance->set($handle)->all() as $field) {
                    $fields['fields'][] = [
                        'handle' => $field->handle(),
                        'meta' => $field->meta(),
                        'value' => $field->fieldtype()->preProcess($field->defaultValue()),
                        'config' => $field->config(),
                    ];
                }

                return $fields;
            })->toArray(),
        ];
    }

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

        return collect($data)->map(function ($section) use ($method) {
            $section['value'] = (new Field($section['uuid'], []))
                ->setConfig(array_merge(['type' => 'buildamic-section'], $section['config']))
                ->setParent($this->field()->parent())
                ->setParentField($this->field())
                ->setValue($section['value'])
                ->{$method}()
                ->value();

            return $section;
        })->toArray();
    }

    protected function performAugmentation($value, $shallow = false)
    {
        $parent = $this;

        $method = $shallow ? 'shallowAugment' : 'augment';

        $value = collect($value)->map(function ($section) use ($parent, $method) {
            if (! $section['config']['enabled'] ?? false) {
                return;
            }

            return (new Field($section['uuid'], []))
                ->setConfig(array_merge(['type' => 'buildamic-section'], $section['config']))
                ->setParent($parent->field()->parent())
                ->setParentField($parent->field())
                ->setValue($section['value'])
                ->{$method}()->value();
        })->filter()->all();

        $this->field()->setValue($value);

        return new BuildamicRenderer($this, $shallow);
    }
}