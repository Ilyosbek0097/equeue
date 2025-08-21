<?php

namespace app\modules\equeue\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "ticket".
 *
 * @property int $id
 * @property int $service_id
 * @property string $ticket_number
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Service $service
 */
class Ticket extends ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_CALLED = 1;
    const STATUS_SERVING = 2;
    const STATUS_SERVED = 3;
    const STATUS_CANCELLED = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ticket}}';
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
            [['service_id', 'ticket_number'], 'required'],
            [['service_id', 'status'], 'integer'],
            [['ticket_number'], 'string', 'max' => 255],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::class, 'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_id' => 'Xizmat ID',
            'ticket_number' => 'Chipta raqami',
            'status' => 'Holati',
            'created_at' => 'Yaratilgan vaqti',
            'updated_at' => 'Yangilangan vaqti',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::class, ['id' => 'service_id']);
    }

    /**
     * Generates the next ticket number for a given service.
     * @param int $service_id
     * @return string
     */
    public static function generateNextTicketNumber($service_id)
    {
        $service = Service::findOne($service_id);
        if ($service === null) {
            return null;
        }

        $today_start = strtotime('today midnight');
        $today_end = strtotime('tomorrow midnight') - 1;

        $count = static::find()
            ->where(['service_id' => $service_id])
            ->andWhere(['between', 'created_at', $today_start, $today_end])
            ->count();

        $new_number = $count + 1;
        return $service->prefix . $new_number;
    }
}
