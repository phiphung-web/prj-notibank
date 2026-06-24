<?php

namespace app\repositories;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

abstract class BaseRepository
{
    protected ActiveRecord $model;

    public function __construct(ActiveRecord $model)
    {
        $this->model = $model;
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ActiveRecord
    {
        try {
            return $this->model::findOne($id);
        } catch (\Throwable $e) {
            Yii::error("Error finding record by ID: {$id}. Error: " . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to find record. Please try again later.');
        }
    }

    public function findAll(array $conditions = []): array
    {
        try {
            return $this->model::find()->where($conditions)->all();
        } catch (\Throwable $e) {
            Yii::error('Error finding records. Conditions: ' . json_encode($conditions) . '. Error: ' . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to retrieve records. Please try again later.');
        }
    }

    public function create(array $data): bool
    {
        try {
            $model = new $this->model();
            $model->attributes = $data;

            if (! $model->save()) {
                Yii::error('Failed to create record. Data: ' . json_encode($data) . '. Errors: ' . json_encode($model->errors), __METHOD__);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Yii::error('Error creating record. Data: ' . json_encode($data) . '. Error: ' . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to create record. Please try again later.');
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $model = $this->findById($id);

            if (! $model) {
                throw new Exception("Record with ID {$id} not found.");
            }

            $model->attributes = $data;

            if (! $model->save()) {
                Yii::error("Failed to update record with ID: {$id}. Data: " . json_encode($data) . '. Errors: ' . json_encode($model->errors), __METHOD__);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Yii::error("Error updating record with ID: {$id}. Data: " . json_encode($data) . '. Error: ' . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to update record. Please try again later.');
        }
    }

    /**
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        try {
            $model = $this->findById($id);

            if (! $model) {
                throw new Exception("Record with ID {$id} not found.");
            }

            return (bool) $model->delete();
        } catch (\Throwable $e) {
            Yii::error("Error deleting record with ID: {$id}. Error: " . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to delete record. Please try again later.');
        }
    }
}
