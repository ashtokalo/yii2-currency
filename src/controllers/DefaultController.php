<?php

namespace ashtokalo\yii2\currency\controllers;

use ashtokalo\yii2\currency\models\CurrencyPair;
use Yii;
use ashtokalo\yii2\currency\forms\ExchangeSearch;
use ashtokalo\yii2\currency\forms\Exchange;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ExchangeController implements the CRUD actions for Exchange model.
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    /*public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'except' => [
                    'error',
                    'login',
                    'recovery',
                ],
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'roles' => [Access::EXCHANGE_VIEW],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['lock', 'unlock'],
                        'roles' => [Access::EXCHANGE_LOCK],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['update'],
                        'roles'   => [Access::EXCHANGE_UPDATE],
                        'allow'   => true,
                    ],
                ],
            ],
        ];
    }*/

    /**
     * Lists all CurrencyPair models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExchangeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CurrencyPair model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->next_currency_pair_id)
        {
            $latest = Exchange::find()->pair($model->baseCurrency, $model->quotedCurrency)->latest()->one();
            if ($latest) $this->redirect(['view', 'id' => $latest->id]);
        }

        $searchModel = new ExchangeSearch();
        $dataProvider = $searchModel->search([],
            Exchange::find()->orderBy('created_at desc')->andWhere([
                'base_currency_id'   => $model->base_currency_id,
                'quoted_currency_id' => $model->quoted_currency_id,
            ]));

        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLock($id)
    {
        $model = $this->findModel($id);
        $model->lock();
        $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionUnlock($id)
    {
        $model = $this->findModel($id);
        $model->unlock();
        $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Updates an existing CurrencyPair model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $newModel = new CurrencyPair([
                'base_currency_id'   => $model->base_currency_id,
                'quoted_currency_id' => $model->quoted_currency_id,
                'rate'               => $model->rate,
                'locked'             => $model->locked ? true : false,
            ]);
            $newModel->save();
            $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the CurrencyPair model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Exchange the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Exchange::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
