<?php

namespace app\controllers;

use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\di\ServiceLocator;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\EntryForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionEntryForm(){
        $model = new EntryForm();
        $model->scenario = 'login';
        // $model->attributes = Yii::$app->request->post();
        
        if($model->load(Yii::$app->request->post())&&$model->validate()){         
            debug(Yii::$app->request->post());
        return $this->render('entryForm',['model'=>$model]);
        }else{
            if(!empty($model->errors))
            debug($model->errors);   
         return $this->render('entryForm',['model'=>$model]);
        }
    }

    public function actionSay($message='hello'){
        $arr = ['message'=>$message];
        return $this->render('say',$arr);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }
    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout(){
        return $this->render('about');
    }
    public function actionUrl(){
        $url = Url::to(["post/index","id"=>5]);
        echo $url;
    }
    public function actionRequest(){
        $request = Yii::$app->request;
        //等价于
        $request = Yii::$app->getRequest();
        $get = $request->get();
        echo "get:";
        dump($get);
        $post = $request->post();
        echo "post:";
        dump($post);
        echo $request->userIP;
        echo $request->userHost;
    }
    public function actionResponse(){
        $response = Yii::$app->response;
        $response = Yii::$app->getResponse();
        $response->format = \Yii\web\Response::FORMAT_JSON;
        $response->data = ['response'=>'response'];
        return $response;
    }
    public function actionSession(){
        $session = Yii::$app->session;
        $session = Yii::$app->getSession();
        echo "isActive:";
        dump($session->isActive);
        $session->close();
        //session操作自动检测session是否开启，未开启时自动开启
        $session->set('language','en-us');
        echo  $session->get('language');
        $session->setFlash("flash","flash");
        echo $session->getFlash("flash");
        //session存储数组
        //修改数组内容--不支持直接修改数组中的单元项
        $names = ["张","李","王"];
        $session->set("names",$names);
        dump($session->get("names",$names));
        $names = $session->get("names");
        $names[1] = "刘";
        $session->set("names",$names);
        dump($session->get("names"));
    }
    public function actionCookie(){
        //这里的cookie read-only
        $cookie = Yii::$app->request->cookies;
        dump($cookie);
        //这里的cookie rx
        $cookie = Yii::$app->response->cookies;
        $cookie->add(new \yii\web\cookie([
            'name' => 'language',
            'value' => 'zh-CN'
        ]));
        dump($cookie);
        $cookie->remove("language");
        dump($cookie);
    }
    public function actionLog(){
        //日志默认记录在 runtime/log/app.log
        //日常写代码可以用它来调试，很舒服
        Yii::trace("end a log",__METHOD__);//已废弃，不可用
        Yii::info("a info log",__METHOD__);
        Yii::warning("a warn log",__METHOD__);
        Yii::error('a error log',__METHOD__);
    }
    public function actionPath(){
        //Yii::$app提供了很多get/set方法，常用的路径或者预定义数组都可以在里面访问
        $path = Yii::getAlias("@app")."\config";
        dump($path);
        dump(Yii::$app->viewPath);
        dump(Yii::$app->getViewPath());
        dump(Yii::$app->basePath);
        dump(str_replace("/",'\\',Yii::$app->controllerPath));//为什么里面路径有‘/’搞不懂
        dump(Yii::$app->layoutPath);
        dump(Yii::$app->vendorPath);
        dump(Yii::$app->runtimePath);
    }
    public function actionAlias(){
        dump(Yii::getAlias("@yii"));
        dump(Yii::getAlias("@app"));
        dump(Yii::getAlias("@runtime"));
        $webroot = Yii::getAlias("@webroot");//为什么里面路径是‘/’搞不懂
        $webroot = preg_replace("/[\/]/","\\",$webroot);
        dump($webroot);
        dump(Yii::getAlias("@web"));
        dump(Yii::getAlias("@vendor"));
    }
    public function actionServicelocator(){
        $locator = new ServiceLocator();
        /*if(false==$locator->has('db')){
            $locator->set('db',[
                'class' => 'yii\db\Connection',
                'dsn' =>'mysql:host=xxxx;dbname=xxxx',
                'username'=>"xx",
                'password'=>"xx"
            ]);
            dump($locator->get("db"));
        }*/
            dump(Yii::$app->get('db'));
    }
    public function actionPDO(){
        //查询语句
        $user_id =1670;
        $user = Yii::$app->db->createCommand("select * from {{%users}} where [[user_id]] = $user_id")->queryOne();
        dump($user);
        $user = Yii::$app->db->createCommand("select * from {{%users}} where [[user_id]] = $user_id")->queryAll();
        dump($user);
        $user = Yii::$app->db->createCommand("select * from {{%users}} where [[user_id]] = $user_id")->queryColumn();
        dump($user);
        $user = Yii::$app->db->createCommand("select * from {{%users}} where [[user_id]] = $user_id")->queryScalar();
        dump($user);
        //bindValue,bingValues,bindParam
        $user = Yii::$app->db->createCommand("select * from {{%users}} where [[user_id]] = :user_id and [[user_name]] like :user_name and [[user_money]] > :user_money and [[level]] > :level")
            ->bindValues([':user_id'=>$user_id,':user_name'=>'__12',':user_money'=>0,':level'=>0])
            ->queryAll();
        dump($user);
        //非查询语句
        //excute
        //update,insert,delete
        //batchinsert+excute,upsert+excute
    }
    public function actionQueryBuilder(){

    }
}
