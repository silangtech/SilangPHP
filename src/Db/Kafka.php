<?php
/*LICENSE
+-----------------------------------------------------------------------+
| SilangPHP Framework                                                   |
+-----------------------------------------------------------------------+
| This program is free software; you can redistribute it and/or modify  |
| it under the terms of the GNU General Public License as published by  |
| the Free Software Foundation. You should have received a copy of the  |
| GNU General Public License along with this program.  If not, see      |
| http://www.gnu.org/licenses/.                                         |
| Copyright (C) 2020. All Rights Reserved.                              |
+-----------------------------------------------------------------------+
| Supports: http://www.github.com/silangtech/SilangPHP                  |
+-----------------------------------------------------------------------+
*/
declare(strict_types=1);
namespace SilangPHP\Db;
class Kafka
{
    protected $prefix = 'kafkaQueue';
    protected $redis = null;
    protected $key = '';
    protected $conf;
    protected $producer;
    protected $consumer;
    protected $producer_topic = [];
    protected $consumer_topic = [];
    protected $consumer_topic_partition = [];

    public function __construct($queue = '', $config = [])
    {
        $this->key = $this->prefix . $queue;
        $this->config = $config;
        $this->broker_str = $this->config['brokers'];
    }

    public function new_comsumer_topic($name)
    {
        $this->consumer_topic[$name] = $this->consumer->newTopic($name, $this->topicConf);
    }

    public function getTask($name = '' , $partition = 0 , $timeout = 100)
    {
        if(!$this->consumer)
        {
            $this->conf = new \RdKafka\Conf();
            // $this->conf->setDrMsgCb(function ($kafka, $message) {
            //     file_put_contents(PS_RUNTIME_PATH."/c_dr_cb.log", var_export($message, true), FILE_APPEND);
            // });
            $this->conf->setErrorCb(function ($kafka, $err, $reason) {
                file_put_contents(PS_RUNTIME_PATH."/err_cb.log", sprintf("Kafka error: %s (reason: %s)", \rd_kafka_err2str($err), $reason).PHP_EOL, FILE_APPEND);
            });
            // $this->conf->set('log_level', (string)LOG_DEBUG);
            // $this->conf->set('debug', 'all');
            if(empty($this->config['groupid']))
            {
                $this->conf->set('group.id', $this->key);
            }else{
                $this->conf->set('group.id', $this->config['groupid']);
            }
            $this->topicConf = new \RdKafka\TopicConf();
            $this->topicConf->set('auto.commit.interval.ms', '100');
            $this->topicConf->set('offset.store.method', 'broker');
            $this->topicConf->set('auto.offset.reset', 'earliest');
            $this->consumer = new \RdKafka\Consumer($this->conf);
            $this->consumer->addBrokers($this->broker_str);
        }
        if(!isset($this->consumer_topic[$name]))
        {
            $this->new_comsumer_topic($name);
        }
        if(!isset($this->consumer_topic_partition[$name."_".$partition]))
        {
	        $this->consumer_topic[$name]->consumeStart($partition, \RD_KAFKA_OFFSET_STORED);
            $this->consumer_topic_partition[$name."_".$partition] = $partition;
        }
        $msg = $this->consumer_topic[$name]->consume($partition, $timeout);
        // 为NUll没数据的情况
        if (null === $msg || $msg->err === \RD_KAFKA_RESP_ERR__PARTITION_EOF) {
            return false;
        } elseif ($msg->err && $msg->err != \RD_KAFKA_RESP_ERR_NO_ERROR) {
            // return $msg->errstr();
            return false;
        } else {
            return $msg->payload;
        }
    }

    public function new_producer_topic($name)
    {
        // $cf = new RdKafka\TopicConf();
        // // -1必须等所有brokers同步完成的确认 1当前服务器确认 0不确认，这里如果是0回调里的offset无返回，如果是1和-1会返回offset
        // // 我们可以利用该机制做消息生产的确认，不过还不是100%，因为有可能会中途kafka服务器挂掉
        // $cf->set('request.required.acks', 0);
        $t =  $this->producer->newTopic($name);
        $this->producer_topic[$name] = $t;
    }

    public function addTask($name, $payload, $partition = \RD_KAFKA_PARTITION_UA)
    {
        $this->conf = new \RdKafka\Conf();
        $this->conf->set('metadata.broker.list', $this->broker_str);
        if(!$this->producer)
        {
            $this->producer = new \RdKafka\Producer($this->conf);
            $this->producer->addBrokers($this->broker_str);
        }
        if(!isset($this->producer_topic[$name]))
        {
            // $this->producer->newTopic('reportlog');
            $this->new_producer_topic($name);
        }
        $this->producer_topic[$name]->produce($partition, 0, $payload);
        $this->producer->poll(0);
        $result = $this->producer->flush(10000);

        if (\RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            return false;
        }else{
            return true;
        }
    }

    public function stats()
    {
        $all = $this->consumer->metadata(true, NULL, 15 * 1000);
        $topics = $all->getTopics();
        foreach ($topics as $topic) 
        {
            $topicName = $topic->getTopic();
            if ($topicName == "__consumer_offsets") 
            {
                continue ;
            }
            $partitions = $topic->getPartitions();
            foreach ($partitions as $partition) 
            {
                $topPartition = new \RdKafka\TopicPartition($topicName, $partition->getId());
                echo "topic: ".$topPartition->getTopic()." - partition: ".$partition->getId()." - "."offset: ".$topPartition->getOffset().PHP_EOL;
            }
        }
    }

    public function run(callable $func = null)
    {
        if($func)
        {
            //每次只取一条任务
            while($task = $this->getTask())
            {
                $func($task);
            }
        }else{
            return $this->getTask();
        }
        return false;
    }

}