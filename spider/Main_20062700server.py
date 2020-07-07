#!/usr/bin/python3
# encoding:utf-8
import requests
import re
import time
import ssl
import urllib3
import json
import pymysql

conn = None
cursor = None
# db_connect 函数用于连接数据库，函数返回一个数据库操作名柄，连接信息在函数中定义
def db_connect():
    # conn为全局变量，用于在函数外关闭数据库连接
    global conn
    global cursor
    database_host = '127.0.0.1'         # mysql地址
    database_user = 'root'              # mysql用户名
    database_pass = 'QQspider2020'              # mysql用户密码
    database_name = 'goldcard'           # 数据库名，不存在会自动创建

    # 连接数据库
    conn = pymysql.connect(database_host, database_user, database_pass, charset='utf8')
    # 使用cursor()方法获取操作游标
    cursor = conn.cursor()
    # 使用execute方法执行SQL语句
    cursor.execute("SELECT VERSION()")
    # 使用 fetchone() 方法获取一条数据
    db_data = cursor.fetchone()
    print ("Connect Database Success!! Database version : %s " % db_data)

    # 数据库不存在则创建：
    cursor.execute("create database if not exists " + database_name + ";")
    cursor.execute("use " + database_name + ";")
    # 创建数据库表：
    sql_data_table = """CREATE TABLE IF NOT EXISTS `gold_data` (
            `key_id` INT NOT NULL AUTO_INCREMENT,
            `create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP comment '创建时间',
            `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP comment '更新时间',
            `name` VARCHAR(255) NOT NULL,
            `lucky_number` VARCHAR(255) NOT NULL,
            `nick` VARCHAR(255) NOT NULL,
            `legend_gcards` INT NOT NULL,
            `legend_ncards` INT NOT NULL,
            `legend_type` INT NOT NULL,
            `start_ts` VARCHAR(255) NOT NULL,
            `end_ts` VARCHAR(255) NOT NULL,
            `source_data` VARCHAR(420) NOT NULL,
            primary key(key_id)
            )ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"""
    cursor.execute(sql_data_table)
    return cursor

# 数据插入函数
def db_operation(gold_data):
    # time.sleep(200)
    # 先检测是否已断开mysql连接，断开则重连
    try:
        conn.ping()
    except:
        db_connect()

    # 插入数据前先查重
    cursor.execute("select max(name) from gold_data;" )
    temp_fetchall = cursor.fetchall()[0][0]
    if not temp_fetchall or str(gold_data[0]) > temp_fetchall :     # 首先判断数据是否是空的，防止第一次建立数据库时出错
        # 准备插入数据:期号，号码，欧皇，金卡收益，开盘时间，收盘时间，源数据，银卡收益，最高收益类型
        # print("insert into gold_data(name,legend_type,nick,legend_gcards,start_ts,end_ts,source_data) values('"+gold_data[0]+"','"+str(gold_data[1])+"','"+gold_data[2]+"','"+str(gold_data[3])+"',"+str(gold_data[4])+",'"+str(gold_data[5])+"',"+'''"'''+gold_data[6]+'''");''')
        sql_insert_data = "insert into gold_data(name,lucky_number,nick,legend_gcards,start_ts,end_ts,source_data,legend_ncards,legend_type) values('"+gold_data[0]+"','"+str(gold_data[1])+"','"+gold_data[2]+"','"+str(gold_data[3])+"',"+str(gold_data[4])+",'"+str(gold_data[5])+"',"+'''"'''+gold_data[6]+'''"'''+",'"+str(gold_data[7])+"','"+str(gold_data[8])+"');"
        # print(sql_insert_data)
        # exit();
        try:
            cursor.execute(sql_insert_data)
            # 默认开启事务，插入需要执行一次commit
            conn.commit()
            # 输出插入的数据
            # print(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))+ ' : 数据插入成功!'+str(gold_data[0:-1]))
            with open (log_name, 'a') as log_handle:
                log_handle.write(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))+',数据写入成功,'+str(gold_data)+'\n')
            return True
        except Exception as e:
            print(e)
            with open (log_name, 'a') as log_handle:
                log_handle.write(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))+',数据写入失败,'+str(gold_data)+'\n')
            conn.rollback()
            return False
    else:
        return False
        # print('数据重复!')



headers = {'content-type': 'charset=utf8'}
ssl._create_default_https_context = ssl._create_unverified_context
urllib3.disable_warnings()

# log_name 用于获取日期当作日志文件名
log_name = time.strftime("%Y-%m-%d", time.localtime()) + '-goldcard-log.csv'
def main():
    global log_name

    # 设置脚本在凌晨2点到早上9点54分时间段内休眠
    while 1:
        start_time = time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))
        hour = start_time[11:13]
        minute = start_time[14:16]
        if hour >= '02' and hour <= '09' :
            if hour == '09' :
                if minute <= '54':
                    time.sleep(300)
                    print(str(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time())))+ ' :' + 'Script is Running!')
                    continue
                else:
                    time.sleep(30)
                    print(str(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time())))+ ' :' + 'Script is Running!')
                    continue
            else:
                time.sleep(3300)
                print(str(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time())))+ ' :' + 'Script is Running!')
                continue

        # 连接并初始化数据库信息
        cursor = db_connect()
        flag = True
        ctrl_time = int(str(time.time())[0:10]) - 179
        time_totle = 0
        number_first = 1
        while 1:
            # 凌晨两点自动休眠
            start_time = time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))
            if start_time[11:13] >= '02' and start_time[11:13] <= '09' and start_time[14:16] >= '05':
                break

            # flag 为布尔值，用以标记上次循环是否有插入数据，是则休眠三分钟
            if flag :
                try:
                    time.sleep(180 - (int(str(time.time())[0:10]) - int(ctrl_time)))
                    time_totle += 180
                except:
                    flag = False

            log_name = time.strftime("%Y-%m-%d", time.localtime()) + '-log.csv'
            nowTime = int(round(time.time() * 1000))
            header = {
                'Origin':       'https://cdn.egame.qq.com',
                'Referer':      'https://cdn.egame.qq.com/pgg-h5-cdn/module/golden-result.html',
                'User-Agent':   'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
                'Accept':       'application/json',
                'Connection':   'close'
            }
            url = 'https://share.egame.qq.com/cgi-bin/pgg_async_fcgi?_=%d' % (nowTime + 3000)
            if number_first == 1 :
                data = {
                    'param':    '{"0":{"param":{"start":0,"num":650},"module":"pgg.card_legend_srf_svr.DefObj","method":"GetHistoryIssue"}}',
                    'app_info': '{"platform":4,"terminal_type":4,"version_code":"undefined","version_name":"undefined","pvid":"4388754432","ssid":"1939556352","imei":"0"}'
                }
                number_first = 0
            else:
                data = {
                    'param':    '{"0":{"param":{"start":0,"num":3},"module":"pgg.card_legend_srf_svr.DefObj","method":"GetHistoryIssue"}}',
                    'app_info': '{"platform":4,"terminal_type":4,"version_code":"undefined","version_name":"undefined","pvid":"4388754432","ssid":"1939556352","imei":"0"}'
                }
            
            try:
                html = requests.post(url, data=data, headers=header, verify=False, timeout=8)
                # print(html.text)
            except:
                with open (log_name, 'a') as log_handle:
                    log_handle.write(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))+',爬取数据失败，请检查网络及目标网站是否正常,'+'-\n')
                time.sleep(2)
                time_totle += 10
                continue

            # 如果返回的数据不存在data节点，直接放弃
            try:
                msg = json.loads(html.text)['data']['0']['retBody']['data']['data']
            except:
                print ('数据错误：不存在data节点！！')
                flag = False
                with open (log_name, 'a') as log_handle:
                    log_handle.write(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))+',数据格式出错,'+html.text+'\n')
            # 根据msg判断数据是否为空
            if msg :
                # print(msg)
                # 先按期号排序，原本数据是倒序的
                try:
                    new_msg = sorted(msg,key=lambda x:(x['name']))
                    # print (new_msg)
                    
                except:
                    print ('数据错误：不存在name节点！！')
                
                for msg_list in new_msg :
                    gold_data = []
                    # print(msg_list['legend_ncards'])
                    # exit()
                    try:
                        # print(msg_list)
                        gold_data.append(msg_list['name'])                  # 0 期号
                        gold_data.append(msg_list['lucky_number'])           # 1 号码
                        gold_data.append(msg_list['user_info']['nick'])     # 2 欧皇
                        gold_data.append(msg_list['legend_gcards'])         # 3 金卡收益
                        gold_data.append(msg_list['start_ts'])              # 4 开盘时间
                        gold_data.append(msg_list['end_ts'])                # 5 封盘时间
                        gold_data.append(str(msg_list))                     # 6 源数据
                        gold_data.append(msg_list['legend_ncards'])         # 7 银卡收益
                        gold_data.append(msg_list['legend_type'])           # 8 最大收益类型，1为金卡，2为银卡
                        
                    except:
                        pass
                    if len(gold_data) == 9:
                        flag = db_operation(gold_data)
                        ctrl_time = msg_list['end_ts']
                    else:
                        print ('数据错误：数据不完整！！')
                        flag = False
                        with open (log_name, 'a') as log_handle:
                            log_handle.write(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))+',数据格式出错,'+html.text+'\n')

            else:
                with open (log_name, 'a') as log_handle:
                    log_handle.write(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))+',数据为空,'+html.text+'\n')
                print ('数据错误：不存在data节点！！')
                flag = False

            if not flag:
                time.sleep(5)
                time_totle += 5
            # 每隔大约10分钟输出一次脚本运行情况
            if time_totle >= 360:
                print(str(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time())))+ ' :' + 'Script is Running!')
                time_totle = 0

        # 关闭数据库连接
        if conn:
            conn.close()
            print ('数据库连接已关闭！')
        else:
            print("没有活跃的数据库连接可关闭!")


if __name__ == '__main__':
    main()
