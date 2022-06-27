<?php

//namespace ashtokalo\currency\migrations;
use yii\db\Migration;

class m220530_151801_init extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%currency}}', [
            'id' => $this->primaryKey(),
            'code' => $this->integer()->notNull()
                ->comment('числовой код валюты ISO 4217, например `810` для российского рубля'),
            'alpha_code' => $this->char(3)->notNull()
                ->comment('буквенный код валюты ISO 4217, например `RUR` для российского рубля'),
            'fractional_size' => $this->tinyInteger()->notNull()
                ->comment('количество цифр после запятой, дробная часть валюты'),
            'name' => $this->string()->notNull()->comment('наименование валюты по-русски'),
        ], 'ENGINE=InnoDB CHARSET utf8 COLLATE utf8_general_ci');

        $this->createIndex('idx_currency_alpha_code', '{{%currency}}', 'alpha_code', true);
        $this->createIndex('idx_currency_code', '{{%currency}}', 'code', true);

        $this->insert('{{%currency}}', ['code' => 810, 'alpha_code' => 'RUR', 'fractional_size' => 2, 'name' => 'Российский рубль']);
        $this->insert('{{%currency}}', ['code' => 643, 'alpha_code' => 'RUB', 'fractional_size' => 2, 'name' => 'Российский рубль']);
        $this->insert('{{%currency}}', ['code' => 978, 'alpha_code' => 'EUR', 'fractional_size' => 2, 'name' => 'Евро']);
        $this->insert('{{%currency}}', ['code' => 840, 'alpha_code' => 'USD', 'fractional_size' => 2, 'name' => 'Доллар США']);

        $this->createTable('{{%currency_pair}}', [
            'id' => $this->primaryKey(),
            'next_currency_pair_id' => $this->integer()->defaultValue(null)
                ->comment('идентификатор валютной пары, которая действовала после данной'),
            'base_currency_id' => $this->integer()->notNull()->comment('идентификатор базовой валюты'),
            'quoted_currency_id' => $this->integer()->notNull()->comment('идентификатор котируемой валюты'),
            'rate' => $this->double()->notNull()->comment('отношение цен двух валют'),
            'origin' => $this->string()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->defaultValue(null),
            'locked_at' => $this->dateTime()->defaultValue(null)
                ->comment('дата блокировки, если для валютной пары заблокированы автоматические обновления'),
            'locked_by' => $this->integer()->defaultValue(null)
                ->comment('идентификатор пользователя, который выполнил блокировку'),
        ], 'ENGINE=InnoDB CHARSET utf8 COLLATE utf8_general_ci');

        $rur = $this->db->createCommand('select id from {{%currency}} where alpha_code="RUR"')->queryScalar();
        $eur = $this->db->createCommand('select id from {{%currency}} where alpha_code="EUR"')->queryScalar();
        $usd = $this->db->createCommand('select id from {{%currency}} where alpha_code="USD"')->queryScalar();

        $this->insert('{{%currency_pair}}', [
            'base_currency_id' => $eur,
            'quoted_currency_id' => $rur,
            'rate' => 69.4353,
            'created_at' => '2022-05-30',
            'origin' => 'migration',
        ]);
        $this->insert('{{%currency_pair}}', [
            'base_currency_id' => $usd,
            'quoted_currency_id' => $rur,
            'rate' => 66.4029,
            'created_at' => '2022-05-30',
            'origin' => 'migration',
        ]);

        $this->addForeignKey('fk_currency_pair_base', '{{%currency_pair}}', 'base_currency_id',
            '{{%currency}}', 'id');
        $this->addForeignKey('fk_currency_pair_quoted', '{{%currency_pair}}', 'quoted_currency_id',
            '{{%currency}}', 'id');
        $this->addForeignKey('fk_currency_pair_next', '{{%currency_pair}}', 'next_currency_pair_id',
            '{{%currency_pair}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%currency_pair}}');
        $this->dropTable('{{%currency}}');
    }
}
