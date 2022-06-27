<?php

namespace ashtokalo\currency\behaviors;

use Yii;
use yii\base\InvalidCallException;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

/**
 * EditorBehavior automatically fills the specified attributes with the current user id.
 *
 * To use EditorBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * use ashtokalo\currency\models\TimestampBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         EditorBehavior::className(),
 *     ];
 * }
 * ```
 *
 * By default, TimestampBehavior will fill the `created_by` and `updated_by` attributes with the current user
 * id when the associated AR object is being inserted; it will fill the `updated_by` attribute with the
 * current user id when the AR object is being updated. The current user id is obtained from application.
 *
 * Because attribute values will be set automatically by this behavior, they are usually not user input and
 * should therefore not be validated, i.e. `created_by` and `updated_by` should not appear in the
 * [[\yii\base\Model::rules()|rules()]] method of the model.
 *
 * For the above implementation to work with MySQL database, please declare the columns(`created_by`,
 * `updated_by`) as int(11) for being user id.
 *
 * If your attribute names are different or you want to use a different way of calculating the timestamp,
 * you may configure the [[authorAtAttribute]], [[editorAtAttribute]] and [[value]] properties like the following:
 *
 * ```php
 * use yii\db\Expression;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => TimestampBehavior::className(),
 *             'authorAtAttribute' => 'author_id',
 *             'editorAtAttribute' => 'editor_id',
 *         ],
 *     ];
 * }
 * ```
 *
 * EditorBehavior also provides a method named [[touch()]] that allows you to assign the current
 * user id to the specified attribute(s) and save them to the database. For example,
 *
 * ```php
 * $model->touch('author_id');
 * ``` 
 */
class EditorBehavior extends AttributeBehavior
{
    /**
     * @var string the attribute that will receive user id value
     * Set this property to false if you do not want to record author id.
     */
    public $authorAttribute = 'created_by';
    /**
     * @var string the attribute that will receive user id value.
     * Set this property to false if you do not want to record the editor id.
     */
    public $editorAttribute = 'updated_by';

    /**
     * {@inheritdoc}
     *
     * In case, when the value is `null`, user id from application will be used as value.
     */
    public $value;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes))
        {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->authorAttribute, $this->editorAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->editorAttribute,
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($event)
    {
        if ($this->value === null) return Yii::$app->user->id ?? null ?: null;

        return parent::getValue($event);
    }

    /**
     * Updates an attribute to the current user id.
     *
     * ```php
     * $model->touch('editor_id');
     * ```
     *
     * @param string $attribute the name of the attribute to update.
     *
     * @throws InvalidCallException if owner is a new record
     */
    public function touch($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord())
        {
            throw new InvalidCallException('Updating the user id is not possible on a new record.');
        }
        $owner->updateAttributes(array_fill_keys((array) $attribute, $this->getValue(null)));
    }
}
