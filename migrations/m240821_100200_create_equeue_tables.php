<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%equeue_tables}}`.
 */
class m240821_100200_create_equeue_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Service table
        $this->createTable('{{%service}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'prefix' => $this->string(10)->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Ticket table
        $this->createTable('{{%ticket}}', [
            'id' => $this->primaryKey(),
            'service_id' => $this->integer()->notNull(),
            'ticket_number' => $this->string()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0), // 0: new, 1: called, 2: served, 3: cancelled
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Add foreign key for table `ticket`
        $this->addForeignKey(
            'fk-ticket-service_id',
            '{{%ticket}}',
            'service_id',
            '{{%service}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `ticket`
        $this->dropForeignKey(
            'fk-ticket-service_id',
            '{{%ticket}}'
        );

        $this->dropTable('{{%ticket}}');
        $this->dropTable('{{%service}}');
    }
}
