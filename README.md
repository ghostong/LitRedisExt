# RedisExt

通过redis实现一些常用功能

## 安装

```
composer require lit/redis-ext
```

## 初始化Redis

也可以使项目中已初始化好的redisHandler

````php
//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.163");
````

## 循环计数器

### 场景说明

```
某些情况下, 我们需要知道周期内某些行为执行了多少次, 但是又不需要把它长久保存下来.
例如: 
    1. 欢迎您: 2021年1月1日第 x 位访问者.
    2. 判断周期内执行过多少次,下一周期自动重置.
    3. 整点限流器.
```

### 示例

#### 初始化

````php
//初始化计数器
Lit\RedisExt\LoopCounter::init($redisHandler);
````

#### 分钟计数器

````php
/*
 * 分钟计数器: 实现每分钟5次限流器
 * 参数1 计数器redis key 可以根据使用维度自行设置
 * 参数2 计数器生命周期, 单位: 分钟
 * 参数3 是否完整分钟, true:完整的 x 分钟, false:从第一次计数开始 x 分钟)
 * 参数4 每次增加数量
 * */
if( Lit\RedisExt\LoopCounter::everyMinutes("test1", 1, false, 1) > 5 ) {
    //超限流
}else{
    //未超限流
}
````

#### 小时计数器

````php
/*
 * 小时计数器: 实现记录2小时之内第几次访问
 * 参数1 计数器redis key 可以根据使用维度自行设置
 * 参数2 计数器生命周期, 单位: 小时
 * 参数3 是否完整分钟, true:完整的 x 小时 false:从第一次计数开始 x 小时)
 * 参数4 每次增加数量
 * */
var_dump(Lit\RedisExt\LoopCounter::everyHours("test2", 2, true, 1));
````

#### 日期计数器

````php
//日期计数器 从当前开始3天
var_dump(Lit\RedisExt\LoopCounter::everyDays("test3", 3, false, 1));
````

#### 自定义计数器

````php
//指定时间计数器 指定某时间过期
var_dump(Lit\RedisExt\LoopCounter::nextRoundAt("test4", time() + 3600 * 7));
````

#### 其他操作

````php
//销毁一个计数器
var_dump(Lit\RedisExt\LoopCounter::destroy("test3"));

//获取一个计数器数值
var_dump(Lit\RedisExt\LoopCounter::get("test4"));
````

## 固定集合

### 场景说明

```
某些情况下, 我们需要记录固定条数的数据
例如: 
    1. 最后充值的 x 位用户.
    2. 最后 x 位访客.
    3. 最新 x 条动态.
```

### 示例

#### 初始化

````php
//初始化固定集合
Lit\RedisExt\CappedCollections::init($redisHandler);
````

#### 固定集合写入数据

````php
/*
 * 参数1 固定集合的key
 * 参数2 要记录的数据
 * 参数3 固定集合限制条数
 * */
var_dump(Lit\RedisExt\CappedCollections::set("cappedKey", uniqid(), 20));
````

#### 获取固定集合数据条数

````php
/*
 * 参数1 固定集合的key
 * */
var_dump(Lit\RedisExt\CappedCollections::size("cappedKey"));
````

#### 获取固定集合数据

注意: 此方法在并发量大的时候,会造成翻页获取数据不准确.

````php
/*
 * 参数1 固定集合的key
 * 参数2 偏移量
 * 参数3 获取数据条数限制
 * */
var_dump(Lit\RedisExt\CappedCollections::get("cappedKey", 15, 5));
````

#### 销毁固定集合

````php
/*
 * 参数1 固定集合的key
 * */
var_dump(Lit\RedisExt\CappedCollections::destroy("cappedKey"));
````

## 循环限流器

### 场景说明

```
假设我们某个程序需要限流 '相对周期内' 只能访问5次.
也就是说:
    1) 第 1,2,3,4,5 次没有限制.
    2) 第6次与第1次的间隔要大于等于1分种.
    3) 第7次与第2次的间隔要大于等于1分种.
    4) 如果周期内不访问, 过期的 3,4,5 将不会限制 8,9,10 的访问. 依次类推
    循环限流器可以保证, 第一次访问和下一次解除限制之间的时间是完整的相对周期.
举个例子:
    运输公司的卡车数量不限, 但是只有5个集装箱, 每个集装箱的单次使用周期是1天, 到期后准时归还.
    假设所有卡车都遵循此规则, 集装箱的流转过程就是循环限流器要实现的场景. 
    卡车能拿到集装箱, 说明不限流, 拿不到说明限流. 我们要做的只是调整集装箱的数量.
```

### 示例

#### 初始化

````php
//初始化限流器
Lit\RedisExt\LoopThrottle::init($redisHandler);
````

#### 访问并增加访问次数

````php
/*
 * 参数1 限流器key
 * 参数2 限流次数
 * 参数2 限流周期
 * 返回 true: 可以访问, false: 被限流
 * */
var_dump(Lit\RedisExt\LoopThrottle::attempt("tKey1", 2, 10));
````

#### 查询限流

````php
/*
 * 参数1 限流器key
 * 参数2 限流周期
 * 返回 周期内有效访问次数
 * */
var_dump(Lit\RedisExt\LoopThrottle::count("tKey1", 300));
````

#### 销毁限流器

````php

/*
 * 参数1 限流器key
 * */
var_dump(Lit\RedisExt\LoopThrottle::destroy("tKey1"));
````

## 异步调用方法

### 场景说明

```
接口要执行很长一段时间的逻辑, 由于很轻量化, 不想再使用消息队列.可以试一下异步调用方法.
此方法需要启动一个定时任务或者守护进程.
```

### 示例

#### 初始化链接

````php
\Lit\RedisExt\AsyncMethod::init($redisHandler);
````

#### 增加一个异步调用

````php
/**
 * 参数1: 固定的RedisKey
 * 参数2: 被实例化的对象
 * 参数3: 要执行的方法名称
 * 参数4: 调用的所有参数 (注意,此参数会在执行是增加一个 _uniqId 下标的唯一ID,供使用者对进程进行监控, 详见demo)
 */
\Lit\RedisExt\AsyncMethod::add("testKey", new \Demo\DemoClass(), 'staticClass', ["a" => 1, "b" => 3]);

````

#### 执行一条异步调用

````php
/**
 * 参数1: 固定的RedisKey
 * 返回值: array 
 *      callable: 函数调用信息
 *      return: 异步方法返回值
 */

\Lit\RedisExt\AsyncMethod::run("testKey");
````
