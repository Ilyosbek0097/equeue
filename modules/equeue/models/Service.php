<?php

namespace app\modules\equeue\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "service".
 *
 * @property int $id
 * @property string $name
 * @property string $prefix
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Service extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%service}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'prefix'], 'required'],
            [['status'], 'integer'],
            [['name', 'prefix'], 'string', 'max' => 255],
            [['prefix'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Xizmat nomi',
            'prefix' => 'Prefiks',
            'status' => 'Holati',
            'created_at' => 'Yaratilgan vaqti',
            'updated_at' => 'Yangilangan vaqti',
        ];
    }
}
