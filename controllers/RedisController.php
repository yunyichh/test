<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/29 0029
 * Time: 下午 5:48
 */

namespace app\controllers;
use yii\web\Controller;

class RedisController extends Controller
{
    /*
     * redis存储内容
     * @type 数据类型
     */
    public function actionIndex($type=null){
        $redis = \Yii::$app->redis;
        $keys = $redis->keys("*");
        foreach ($keys as $value){
            switch ($redis->type($value)){
                case 'string':
                    $value = [$value => $redis->get($value)];//一个键指向一个【字符串】
                    $result['string'][] = $value;
                    break;
                case 'hash':
                    $value = [$value => array_combine($redis->hkeys($value),$redis->hvals($value))];//一个键指向一个【字符串or数组or对象..】
                    $result['hash'][] = $value;
                    break;
                case 'list':
                    $list = $redis->lrange($value,0,$redis->llen($value));
                    $value = [$value => $list];//一个键指向一个【字符串列表】
                    $result['list'][] = $value;
                    break;
                case 'set':
                    $value = [$value => $redis->smembers($value)];//一个键指向一个【字符串集合】
                    $result['set'][] = $value;
                    break;
                case 'zset':
                    $members = $redis->zrange($value,0,$redis->zcard($value));
                    $member_with_score = '';
                    foreach ($members as $member){
                         $member_with_score[] = [$member => $redis->zscore($value,$member)];
                    }
                    $value = [$value => $member_with_score];//一个键指向一个【带分数的字符串集合】
                    $result['zset'][] = $value;
                    break;
            }
        }
       if(is_null($type))
         dump($result);
        else
         dump($result[$type]);
    }
    //key--16 command
    public function actionKey(){
        $this->actionIndex();
        $redis = \Yii::$app->redis;
        //获取所有键名
        $keys = $redis->keys("*");
        dump($keys);
        //键名是否存在
        dump($redis->exists("mykey"));
        //设置键值过期时间
        $redis->expire("mykey",86400);//pexpire 以毫秒计
        $redis->expireat("mykey",strtotime("+ 48hour",time()));//pexpireat 以毫秒计
        //查看键值过期时间
        dump($redis->ttl("mykey"));//pttl--以毫秒计
        //移除过期时间
        $redis->persist("mykey");//持久化
        //返回随机键
        $randomkey = $redis->randomkey();
        dump($randomkey);
        //键类型
        dump($redis->type("tutorial-list"));
        //键重命名
        //$redis->rename("mykey","mykeynewkey");//renamenx--newkey不存在时更名为newkey
        //键值序列化并返回
        $dumpkey = $redis->dump("mykeynewkey");
        dump($dumpkey);
        //删除键
        //$redis->del("mykeynewk");
        //移动键
        //$redis->move(key,db);
    }
    //string--20 command
    public function actionString(){
        $this->actionIndex("string");
        $redis = \Yii::$app->redis;
        //设置
        $redis->set("count",1);
        $redis->setbit("mykeys",3,1);
        $redis->setex("mykeys",86400,"mykeystringex");//setnx setrange mset msetnx psetex
        //获取
        $strings = $redis->mget("mykeystring","mykey2","mykeys3","mykeys4");//getrange
        dump($strings);
        $redis->getbit("mykeys",1);//getset
        //追加
        $redis->append('mykeys',"add");
        //长度
        $redis->strlen("mykeys");
        //数字增
        $redis->incr("count");//incrby incrbyfloat
        //数字减
        $redis->decr("count");//decrby
        $this->actionIndex("string");
    }
    //hash--14 command
    public function actionHash(){
        $this->actionIndex("hash");
        $redis = \Yii::$app->redis;
        //字段是否存在
        echo $redis->hexists("mykeyh","dsjakdl");
        //设置
        $redis->hset("mykeyh","count",1);//hmset hsetnx
        //获取
        echo $redis->hget("mykeyh","count");//hmget hgetall hkeys hvals
        //长度
        echo $redis->hlen("mykeyh");
        //删除
        //$redis->hdel("mykeyh");
        //数字增
        $redis->hincrby("mykey","count",1);//hincrbyfloat
         //迭代
         //hsacn--不懂
    }
    //list--17 command
    public function actionList(){
        $this->actionIndex("list");
        $redis = \Yii::$app->redis;
        //设置
        $redis->lset("mykeyl",2,"bdbb");
        //获取
        echo $redis->lindex("mykeyl",2);br();//lrange
        //插入
        $redis->lpush("mykeyl","hah","dsa","dsdasd","dsdfsa");//lpushx rpush rpushx linsert..before|after
        //弹出
        echo $redis->lpop("mykeyl");br();//rpop
        //阻塞式弹出
        dump($redis->blpop("mykeyl",5));//brpop
        //弹出并插入
        //rpoplpush
        //阻塞式弹出并插入
        //brpoplpush
        //长度
        echo $redis->llen("mykeyl");
        //删除
        //lrem ltrim

    }
    //set--15 command
    public function actionSet(){
        $this->actionIndex("set");
        $redis = \Yii::$app->redis;
        //成员是否存在
        echo $redis->sismember("mykeys2",1);
        //添加
        $redis->sadd("mykeys2",1,2,3,4,5);
        //获取
        dump($redis->smembers("mykeys2"));
        //弹出
        //spop
        //长度
        echo $redis->scard("mykeys2");
        //移动
        //smove
        //删除
        $redis->srem("mykeys2",2,3,4,5);
        //随机
        dump($redis->srandmember("mykeys2",4));
        //差集-交集-并集
        dump($redis->sdiff("mykeys2","mykeys3"));//sdiffstore--sinter sinterstore--sunion sunionstore
        //迭代
        //scan--不懂

    }
    //z-set--20 command
    public function actionZset(){
        $this->actionIndex("zset");
        $redis = \Yii::$app->redis;
        //添加
        //$redis->zadd("mykeyz",10,'aa',20,'bb',30,'cc',40,'dd');
        //获取指定范围
        dump($redis->zrange("mykeyz",0,100));//zrangebylex zrangebyscore zrevrange zrevrangebysocre
        //返回排名
        echo $redis->zrank("mykeyz",'bb');br();//zrevrank
        //返回分数
        echo $redis->zscore("mykeyz","bb");br();
        //成员数
        echo $redis->zcard("mykeyz");br();//zcount zlexcount
        //分数增
        $redis->zincrby("mykeyz",20,"bb");
        //删除
        $redis->zrem("mykeyz2","aa","bb","cc","dd");//zremrangebylex zremrangebyrank zremrangebyscore
        //交集--并集
        //zinterstore zunionstore
        //迭代
        //zscan--不懂
    }
    //hyperloglog
    public function actionHyperloglog(){
        $redis = \Yii::$app->redis;
        //添加
       // $redis->pfadd("mykeyhyperloglog",1,2,3,4,5,5,5,5,6,6,5,55,4,4,44);
        //$redis->pfadd("mykeyhyperloglog2",1,2,3,4,5,5,5,5,6,6,5,55,4,4,44);
        //基数
        echo $redis->pfcount("mykeyhyperloglog");
        //合并
        $redis->pfmerge("mykeyhyperloglog","mykeyhyperloglog2");
        dump($redis->keys("*"));
        echo $redis->pfcount("mykeyhyperloglog");
    }
    public function actionServer(){
        $redis = \Yii::$app->redis;
        $command = $redis->command();
        dump($command);
    }

}