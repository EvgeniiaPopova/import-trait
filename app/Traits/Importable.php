<?php

namespace App\Traits;

trait Importable
{
    public function getFieldsToImport()
    {
        return $this->fields_to_import;
    }

    public function getForeigns()
    {
        return $this->foreigns;
    }

    public function getPivots()
    {
        return $this->pivots;
    }

    public function getRanges()
    {
        return $this->ranges;
    }

    public static function parse($path)
    {
        $data = \Excel::load($path)->get();

        return $data;
    }

    public static function import($data)
    {
        foreach ($data as $key => $datum) {
            $entity = new self();
            try {

                $formatedData = $datum->filter(function ($value, $key) use ($entity) {
                    return in_array($key, $entity->getFieldsToImport()) && $key !== 0;
                });

                if ($entity->getForeigns()) {
                    foreach ($entity->getForeigns() as $foreign => $class) {
                        $model = new $class();
                        $findModel = $model->where($model->import_search, $datum[$foreign])->get();
                        $formatedData[$foreign . '_id'] = is_null($findModel) ?: $findModel->first()->id;
                    }
                }

                if ($entity->getRanges()) {
                    foreach ($entity->getRanges() as $field => $options) {
                        $ranges = array_filter(array_unique(explode($options['separator'], preg_replace('/[^0-9+' . implode('', array_keys($options['format'])) . ']+/', $options['separator'], $datum->{$field}))), function ($value) {
                            return !empty($value);
                        });

                        if (empty($ranges)) {
                            continue;
                        }

                        $ranges = array_map(function ($range) use ($options) {
                            preg_match('/[0-9]+/', $range, $integers);
                            preg_match('/[A-z]+/', $range, $formats);
                            return $integers[0] * $options['format'][$formats[0]];
                        }, $ranges);

                        $formatedData[str_replace('*', 'min', $options['field'])] = min($ranges);
                        $formatedData[str_replace('*', 'max', $options['field'])] = min($ranges) === max($ranges) ? null : max($ranges);
                    }
                }

                $inv = $entity->fill($formatedData->all())->save();
            } catch (\Exception $exception) {
                \Log::error('Something went wrong, while importing line ' . ($key + 2));
                continue;
            }
            if ($inv && $entity->getPivots()) {
                foreach ($entity->getPivots() as $pivotField => $pivotOption) {
                    $types = array_filter(explode('|', $datum[$pivotField]), function ($piv) {
                        return !empty($piv);
                    });
                    if (empty($types)) {
                        continue;
                    }
                    $count = 0;
                    foreach ($types as $type) {
                        $array = [];
                        $class = new $pivotOption['model']();
                        $pivotModel = new $pivotOption['pivot_model']['class']();
                        $array[strtolower(class_basename(get_class($entity))) . '_id'] = $entity->id;
                        $pivotIdToAdd = $pivotModel::where($pivotModel->import_search, $type)->get();
                        if (!$pivotIdToAdd) {
                            \Log::info(sprintf('Cannot add %s = %s to %s (id=%s), because not such category', $pivotField, $type, strtolower(class_basename(get_class($entity))), $entity->id));
                            continue;
                        }
                        $array[$pivotOption['pivot_model']['field']] = $pivotIdToAdd->id;
                        $count++;
                        $class->fill($array)->save();

                    }
                }
            }
        }
    }
}
