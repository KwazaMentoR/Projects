<?php

namespace app\controllers;

use system\core\Db;

class BotController extends AppController
{
// если стоит пустой контроллер, то не пересылает на index.php
    public function indexAction()
    {

    }

    public function getApiAction($method = null, $req = []) // метод для подключения к yobit
    {
        $config = require CONF . '/api_config.php'; // require_once вызывал ошибку, второй раз вызывая функцию не подключался файл и не доставался ключ
        $nonce_num = $this->nonce();
        $req['method'] = $method;
        $req['nonce'] = $nonce_num;
        $post_data = http_build_query($req, '', '&');
        $sign = hash_hmac('sha512', $post_data, $config['api_secret']);

        $headers = [
            'Sign:' . $sign,
            'Key:' . $config['api_key'],
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, 'https://yobit.net/tapi/');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $res = curl_exec($ch);
        if ($res === false) {
            curl_close($ch);
            return null;
        }
        curl_close($ch);

        return $res;
    }

    public function tradeAction()
    {
        $coin = 'eth';
        $rur = 'rur';

        $res = $this->getApiAction('getInfo');
        // получение данных из кошелька. можно посмотреть в network priview. Посылается запрос и ответ получается сразу же при текущем срабатывании кода

        $res = json_decode($res);

        $serv_time = ''; //текущее время на сервере
        $budget = ''; //текущая сумма рублей на кошельке
        $coinfunds = ''; //текущее колличество торгуемого коина на кошельке

        if ($res->success === 1){
            $serv_time = $res->return->server_time; //текущее время на сервере

            if (array_key_exists($rur,(array)$res->return->funds)){ // (array) преобразовывает объект в массив
                //проверка есть ли на кошельке. если нет, то в funds валюты не будет, при обращении будет ошибка
                $budget = $res->return->funds->{$rur};
            }

            if (array_key_exists($coin,(array)$res->return->funds)){
                $coinfunds = $res->return->funds->{$coin};
            }
        }else{
            debug("Не пройдена проверка в строке " . __LINE__);
            exit(); //прерываем скрипт если ошибка извлечения
        }

        $pair = $coin . '_' . $rur; // устанавливаем интересующую пару

        $ticker = "https://yobit.net/api/3/ticker/{$pair}";
        $tickerdata = file_get_contents($ticker);
        $tickerdata = json_decode($tickerdata);
        $stat = '';
        if ($tickerdata){
            $stat = $tickerdata->{$pair}->avg; // получаем среднюю цену за сутки
        }else{
            debug("Не пройдена проверка в строке " . __LINE__);
            exit();
        }

        //Берем среднее значение первых пяти ордеров на покупку коина из правого стакана полученных из depth bids

        $depth = $this->depthAction($pair);

        $middle = $depth['middle']; // ср. знач. в rur последних 5 ордеров из правого стакана

        $first = $depth['first']; //первое значение со стакана

        //$etherscan = $this->etherscan();

        $etherscan = $this->cryptocompare($serv_time); //значение курса eth на cryptocompare
        //debug($etherscan);

        $growthcource = $this->growthCource($serv_time, $middle, $etherscan); // записываем через интервал времени значение курса на бирже и оф сайте в бд

        //debug($growthcource);
        //debug($etherscan-$middle);
        //debug($etherscan-$middle > $growthcource['differ']);
        //$savetime = $this->getCircle($serv_time, $middle); // отслеживаем резкий рост

        $savetime = $this->saveTime($serv_time,$middle);
        //debug($savetime);

        //юзаемые таблицы - bot cource profit ethtable

        $orderbuy = Db::query("SELECT id_order, type FROM bot WHERE type = ? ORDER BY id DESC LIMIT 1",['buy']);
        $ordersell = Db::query("SELECT id_order, type FROM bot WHERE type = ? ORDER BY id DESC LIMIT 1",['sell']);
        // извлекается последний массив ордера покупки или продажи ['id_order', 'type']

        if ($orderbuy) {
            foreach ($orderbuy as $v){
                $orderbuy = $v;
                debug($orderbuy);
            }
        }
        if ($ordersell) {
            foreach ($ordersell as $v){
                $ordersell = $v;
                debug($ordersell);
            }
        }

        $buyinfo = ''; //каждый раз при отработке скрипта заносим информацию по ордерам в БД
        if ($orderbuy){
            $buyinfo = $this->getOrder($orderbuy); // извлечение информации с йобита по ордеру записанному в БД

            if ($buyinfo){
                Db::myExecute("UPDATE bot SET amount = ?, status = ? WHERE id_order = ?",[$buyinfo['amount'],$buyinfo['status'],$orderbuy['id_order']]);
            }
            // метод query выдавал ошибку SQLSTATE[HY000]: General error из за \PDO::FETCH_ASSOC
            // дописываем обновленную инфу об ордере в БД

        }
        debug($buyinfo);

        $sellinfo = '';
        if ($ordersell){
            $sellinfo = $this->getOrder($ordersell);

            if ($sellinfo){
                Db::myExecute("UPDATE bot SET amount = ?, status = ? WHERE id_order = ?",[$sellinfo['amount'],$sellinfo['status'],$ordersell['id_order']]);
            }
            // тут мы заносим инфу в другу БД, что бы потом торговать с наваром
            if ($buyinfo['status'] === 1 && $sellinfo['status'] === 1 && $sellinfo['timestamp_created'] > $buyinfo['timestamp_created']) {

                // последние ордеры на покупку и на продажу закрыты и время продажи больше чем покупки, значит продался именно этот ордер

                $buyprof = $buyinfo['start_amount'] * $buyinfo['rate']*1.002; //цена по которой купили с учетом комиссии
                $sellprof = $sellinfo['start_amount'] * $sellinfo['rate'] * 0.998; //цена по которой продали с учетом комиссии

                $orderbuyprof = Db::query("SELECT id_order FROM profit WHERE type = ? ORDER BY id DESC LIMIT 1",['buy']);
                $ordersellprof = Db::query("SELECT id_order FROM profit WHERE type = ? ORDER BY id DESC LIMIT 1",['sell']);
                // берем id последнего ордера из таблицы profit

                if ($orderbuyprof[0]['id_order'] != $orderbuy['id_order']){
                    //если id не совпадает, то не приплюсовываем значение, потому что это пока тот же самый ордер на покупку

                    Db::myExecute("UPDATE profit SET profit = profit + ?, id_order = ? WHERE type = ?",[$buyprof,$orderbuy['id_order'],$buyinfo['type'],]);
                }
                if ($ordersellprof[0]['id_order'] != $ordersell['id_order']){

                    Db::myExecute("UPDATE profit SET profit = profit + ?, id_order = ? WHERE type = ?",[$sellprof,$ordersell['id_order'],$sellinfo['type'],]);
                }
            }
        }

        $profit = Db::query("SELECT type, profit FROM profit");
        $profit = $profit[1]['profit'] - $profit[0]['profit']; // извлекаем из БД суммированные значения успешных ордеров и считаем навар

        debug($sellinfo);

        $part = !empty($_GET['lot']) ? $_GET['lot'] : null; //$part равен значению lot, если оно не пустое иначе null. "На какую сумму торгуем, руб."
        $cash = !empty($_GET['percent']) ? $_GET['percent'] : null; // "Накрутка, %"
        $profcheck = !empty($_GET['profit']) ? $_GET['profit'] : null; // "Использовать навар"

        if (!isset($_COOKIE['botpart'])){
            setcookie('botpart', $part, time()+3600*24*7, '/'); //ставим куки на неделю, что бы заново не вбивать каждый раз значения
        }else{
            if ($part != $_COOKIE['botpart']){
                setcookie('botpart', $part, time()+3600*24*7, '/');
            }
        }
        if (!isset($_COOKIE['botpercent'])){
            setcookie('botpercent', $cash, time()+3600*24*7, '/');
        }else{
            if ($cash != $_COOKIE['botpercent']){
                setcookie('botpercent', $cash, time()+3600*24*7, '/');
            }
        }
        if (!isset($_COOKIE['botprof'])){
            setcookie('botprof', $profcheck, time()+3600*24*7, '/');
        }else{
            if ($profcheck != $_COOKIE['botprof']){
                setcookie('botprof', $profcheck, time()+3600*24*7, '/');
            }
        }
        if ($profcheck === 'true'){ //если галочка стоит, то торгуем с наваром
            $part = $part + $profit; //прибавляем навар
        }

        $cash = $cash/100; // переводим из процентов

        $orderwait = 1800; //время ожидания ордера

        $type = 'buy';
        $amount = $part/$middle; //колл-во коина которое можно купить исходя их части бюджета и усредненного курса
        $params = [
            'pair' => $pair, // пара (пример: eth_rur)
            'type' => $type, // тип операции (пример: buy - покупка или sell - продажа)
            'rate' => $middle, // курс eth в rur, по которому необходимо купить/продать
            'amount' => $amount // количество eth, которое необходимо купить/продать
        ];
/*
        $params['type'] = 'buy';
        $params['rate'] = 14000;
        $params['amount'] = 0.06;
/*
        $params['type'] = 'sell';
        $params['rate'] = 5000;
        $params['amount'] = $coinfunds;

        $gettrade = $this->getTrade('Trade', $params, $serv_time, 'buy', $params['rate']);
        debug($gettrade);
        exit();
*/

        // Классификация ордеров: Активный и Неактивный
        // Активный: 0 - активен, 3 - активен и частично исполнен.
        // Неактивный: 1 - исполнен и закрыт, 2 - отменен.

        if (!empty($orderbuy)){ //если есть запись в БД об ордере на покупку
            if (!empty($buyinfo)){
                if ($buyinfo['status'] != 0) { // если нет активных ордеров на закупку

                    $differ = null; //не используется
                    if (!empty($sellinfo)) {
                        $differ = $sellinfo['rate'] - 100;
                    }

                    if ($buyinfo['status'] === 1) { // если ордер на покупку был исполнен и закрыт
                        if (!empty($ordersell)){
                            // есть информация о продаже в БД и соответственно получена инфа об ордере
                            //именно $ordersell а не $sellinfo, иначе yobit может выдать ошибку но вернуть не пустое значение
                            if ($sellinfo['status'] === 1) { //нет активных ордеров на продажу, ошибка yobit уже не пройдет
                                if ($sellinfo['timestamp_created'] > $buyinfo['timestamp_created']) { //и время продажи позже чем покупки

                                    //ПОКУПАЕМ

                                    $gettrade = $this->buyCoin($part, $first, $middle, $stat, $differ, $savetime, $serv_time, $params, '', $ordersell['id_order'], $etherscan, $growthcource);
                                } else { //если время покупки больше чем время продажи, значит в послледний заход коин был куплен и еще не продан

                                    // ПРОДАЕМ

                                    $gettrade = $this->sellCoin($coinfunds, $buyinfo['rate'], $orderbuy['id_order'], $cash, $serv_time, $params);
                                }
                            }
                            if ($sellinfo['status'] === 0 || $sellinfo['status'] === 2 || $sellinfo['status'] === 3) { //если лот продажи активен или отменен
                                //продумать как при обычном режиме будет ордер стотять. Записать в отдельную БД что был рост на этерскане или не было
                                // Также туда записать рост на йобите
                                if (($etherscan-$middle) < $growthcource['differ']) {
                                    // проверка при ПАДЕНИИ на этерскане. Надо дорабатывать
                                    $halfcash = $buyinfo['rate'] + $buyinfo['rate'] * $cash / 2; //цена продажи лота с половинчатой накруткой
                                    // $buyinfo['rate'] = 20000 $halfcash = 20250
                                    if ($sellinfo['rate'] > $halfcash) {
                                        //проверка на случай если мы уже поставили ордер с половиной накрутки
                                        $cancelorder = (object)['success' => (int)0];
                                        if ($sellinfo['status'] != 2){
                                            $cancelorder = $this->cancelOrder($ordersell['id_order']);
                                        }
                                        if ($cancelorder->success === 1 || $sellinfo['status'] === 2) {
                                            // здесь status === 2 на случай если ордер уже был отменен
                                            if ($halfcash < $first) {
                                                // если цена продажи лота с половиной накрутки + запас, меньше первого из стакана,
                                                // то продаем  с половиной накрутки
                                                $cash = $cash / 2;
                                            } else {
                                                //по какой купили по такой и продаем без накрутки, что бы не зависнуть при падении на бирже
                                                $cash = 0;
                                            }
                                            $gettrade = $this->sellCoin($sellinfo['amount'], $buyinfo['rate'], $orderbuy['id_order'], $cash, $serv_time, $params);
                                            if ($gettrade->success === 1) {
                                                Db::myExecute("DELETE FROM bot WHERE id_order = ?;", [$ordersell['id_order']]);
                                                // удаляем отмененный текущий ордер на продажу в БД только после новой продажи

                                                if ($sellinfo['status'] === 2 && $sellinfo['status'] === 3) {

                                                    if ($sellinfo['amount'] < $sellinfo['start_amount']){
                                                        $sellprof = $sellinfo['start_amount'] * $sellinfo['rate'] * 0.998 - $sellinfo['amount'] * $sellinfo['rate'] * 0.998;
                                                        Db::myExecute("UPDATE profit SET profit = profit + ?, id_order = ? WHERE type = ?", [$sellprof, $ordersell['id_order'], $sellinfo['type'],]);
                                                    }
                                                }
                                            }
                                        }else{
                                            debug("Не пройдена проверка на отмену продажи в строке " . __LINE__);
                                        }
                                    }
                                }
                            }

                        }else{ //нет информации в БД о продаже, значит первый раз продаем

                            // ПРОДАЖА
                            $gettrade = $this->sellCoin($coinfunds, $buyinfo['rate'], $orderbuy['id_order'], $cash, $serv_time, $params);

                        }
                    }
                    // Очень интересная фишка - если ордер частично исполнен, то он приобретает статус 3 и остается активным. так было с ордером продажи
                    // Хотя на yobit написано "3 - отменен, но был частично исполнен". формулировка не верна
                    // А если этот ордер со статусом 3 отменить самому, то он приобретает статус 2 - отменен.
                    // Значит когда выполнится, то приобретет статус 1 - исполнен
                }

                if ($buyinfo['status'] === 0 || $buyinfo['status'] === 2 || $buyinfo['status'] === 3) {
                    //Если есть активные ордеры на покупку или частично выполненные. Дублируем условие вместо else иначе yobit может наебать
                    //например в $buyinfo придет хрен знает что и $buyinfo['status'] != 0 выдаст false и скрипт пойдет работать в else

                    // ОТМЕНА ОРДЕРА НА ПОКУПКУ

                    $highprice = $buyinfo['rate'] + 50; // цена из стакана выросла на 50 рублей, по сравнению с закупочной ценой
                    $botspam = $buyinfo['rate'] + 15; // например 12000+15=12015

                    //проверка на спам и перебивание, противодействие негодяям)
                    if ($first < $botspam && $first > $buyinfo['rate']){ // например 12005 < 12015 но 12005 > 12000
                        $this->checkBuy($orderbuy, $buyinfo, $part, $first, $middle, $stat, $savetime, $serv_time, $params, $etherscan, $growthcource);
                    }else{
                        if ($serv_time > $buyinfo['timestamp_created'] + $orderwait || $middle > $highprice){
                            //если прошло 30 минут и ордер все еще существует или средняя цена из стакана выросла больше
                            // чем на 50 рублей от $buyinfo['rate']
                            $this->checkBuy($orderbuy, $buyinfo, $part, $first, $middle, $stat, $savetime, $serv_time, $params, $etherscan, $growthcource);
                        }else{
                            debug("Не пройдена проверка на покупку в строке " . __LINE__);
                        }
                    }
                }
            }
        }else{ //самая первая покупка, записей в БД нет
            if ($coinfunds === 0) {
                $gettrade = $this->buyCoin($part, $first, $middle, $stat, $differ=0, $savetime, $serv_time, $params, $amount = '', 0, $etherscan, $growthcource);
            }
        }

        debug($etherscan);
        $color = ($etherscan-$middle) < $growthcource['differ'] ? 'color:red' : '';
        echo "<div style='$color'> Разница с Etherscan:" . ($etherscan-$middle) . "</div>";

        $color = $middle > $savetime['value']+300 ? 'color:red' : '';
        echo "<div style='$color'>Резкий рост : " . ($savetime['value']-$middle) . "</div>";

        $color = $middle > $stat*1.01 ? 'color:red' : '';
        echo "<div style='$color'>Среднее из пяти : " . $middle . "</div>";

        echo 'Среднее за сутки: ' . $stat . '<br>';
        echo 'Сколько коина на кошельке: ' . $coinfunds . '<br>';
        echo 'Сколько рублей на кошельке: ' . $budget . '<br>';
        echo 'Накопленный навар: ' . $profit . '<br>';

        if ($this->isAjax()){ // если был произведен ассинхронный запрос через ajax, то выводится ajax строка. Делать вместо ретурна, иначе начинает подгружать вид которого нет.
            exit();
        }
    }

    public function buyCoin($part,$first,$middle,$stat,$differ,$savetime,$serv_time,$params,$amount = '',$id_order = 0, $etherscan, $growthcource)
        // метод покупки с условиями
    {
        $gettrade = (object)['success' => (int)0];
            if ($middle < $stat * 1.01 || $middle > $savetime['value'] + 300 || ($etherscan-$middle) > $growthcource['differ'] /*$middle < $differ*/) {
                //Проверка при покупке, надо дорабатывать
                //не сработает только если $middle больше $stat или нет резкого скачка на бирже
                //если есть рост на оф. сайте
                if ($first < ($middle + 50)) { //здесь берем по первому из стакана,но если он не сильно отрывается от среднего знач
                    $params['rate'] = $first + 0.00000001;
                    if (!empty($amount)) {
                        $params['amount'] = $amount;
                    } else {
                        $params['amount'] = $part / $params['rate'];
                    }
                    $gettrade = $this->getTrade('Trade', $params, $serv_time, 'buy', $params['rate']);
                    if ($id_order && $gettrade->success === 1) {
                        Db::myExecute("UPDATE bot SET time_over = ? WHERE id_order = ?", [$serv_time, $id_order]);
                        //после покупки записываем в БД время исполнения ордера на продажу
                    }

                } else { // иначе берем по middle
                    if (!empty($amount)) {
                        $params['amount'] = $amount;
                    } else {
                        $params['amount'] = $part / $middle;
                    }
                    $gettrade = $this->getTrade('Trade', $params, $serv_time, 'buy', $middle);
                    if ($id_order && $gettrade->success === 1) {
                        Db::myExecute("UPDATE bot SET time_over = ? WHERE id_order = ?", [$serv_time, $id_order]);
                        //после покупки записываем в БД время исполнения ордера на продажу,
                        //так как сразу после после покупки, через 5 сек, бот будет продавать
                    }
                }
            }
        return $gettrade;
    }

    public function checkBuy($orderbuy, $buyinfo, $part, $first, $middle, $stat, $savetime, $serv_time, $params, $etherscan, $growthcource)
    { //метод проверки условий покупки/отмены ордера
        $cancelorder = (object)['success' => (int)0];
        if ($buyinfo['status'] != 2){
            $cancelorder = $this->cancelOrder($orderbuy['id_order']);
        }
        if ($cancelorder->success === 1 || $buyinfo['status'] === 2) {
            // ПОКУПАЕМ на оставшееся количество коина $buyinfo['amount'] после отмены ордера
            $gettrade = $this->buyCoin($part, $first, $middle, $stat, $differ = 0, $savetime, $serv_time, $params, $buyinfo['amount'], 0, $etherscan,$growthcource);
            //берем сразу после отмены, иначе промежуток большой перед повторной установкой ордера при перебивании
            if ($gettrade->success === 1) {
                Db::myExecute("DELETE FROM bot WHERE id_order = ?;", [$orderbuy['id_order']]);
                // удаляем отмененный ордер в БД только после покупки

                if ($buyinfo['status'] === 2 || $buyinfo['status'] === 3) {
                    if ($buyinfo['amount'] < $buyinfo['start_amount']) {
                        $buyprof = $buyinfo['start_amount'] * $buyinfo['rate'] * 1.002 - $buyinfo['amount'] * $buyinfo['rate'] * 1.002;
                        // находится в $gettrade->success === 1, потому что иначе отмененный будет покупать с излишним профитом
                        // плюсуем в profit отмененный частично выполненный ордер. Его также учитываем, так как он мог быть до этого со статусом 3

                        Db::myExecute("UPDATE profit SET profit = profit + ?, id_order = ? WHERE type = ?", [$buyprof, $orderbuy['id_order'], $buyinfo['type'],]);
                    }
                }
            }else{
                debug("Не пройдена проверка, покупка после отмены не совершена. Строка " . __LINE__);
            }

        }else{
            debug("Не пройдена проверка на отмену покупки в строке " . __LINE__);
        }
    }

    public function sellCoin($coinfunds,$rate,$id_order,$cash,$serv_time,$params) // метод продажи с условиями
    {
        $gettrade = '';
        if ($coinfunds > 0){
            $params['type'] = 'sell';
            $params['rate'] = $rate + $rate * $cash;
            //Вытаскиваем из кэша цену, по которой в последний раз закупали и продаем с наценкой
            $params['amount'] = $coinfunds; // Берем заданное количество коина
            $gettrade = $this->getTrade('Trade', $params, $serv_time, 'sell', $params['rate']);
            if ($gettrade->success === 1){
                Db::myExecute("UPDATE bot SET time_over = ? WHERE id_order = ?",[$serv_time,$id_order]);
                //записываем в БД время исполнения ордера на покупку
            }
        }
        return $gettrade;
    }

    public function getTrade($method, $params, $serv_time, $type, $price) // непосредственная операция покупки/продажи
    {
        $gettrade = $this->getApiAction($method, $params);
        // здесь может произойти баг и $gettrade вернуть ошибку, тогда перед записью в БД надо это проверить и мы проверяем ниже

        $gettrade = json_decode($gettrade);
        if ($gettrade->success === 1){
            $id = $gettrade->return->order_id;
            Db::insertData('bot',[$type,$id,$params['amount'],$price,$serv_time],'type, id_order, start_amount, rate, timestamp_created');
        }

        return $gettrade;
    }

    public function getOrder($order) //запрос полной информации об ордере по его id на yobit
    {
        $orderinf = '';

        if (!empty($order)){
            $param['order_id'] = $order['id_order'];
            $orderinfo = $this->getApiAction('OrderInfo',$param);
            $orderinfo = json_decode($orderinfo);
            if ($orderinfo->success === 1){ //yobit из за нагруженности может тупануть и ничего не извлечь
                $orderinf = (array)$orderinfo->return->{$order['id_order']};
            }
        }
        return $orderinf;
    }

    public function cancelOrder($id_order) //метод отмены ордера
    {
        $param['order_id'] = $id_order;
        $cancelorder = $this->getApiAction('CancelOrder', $param);
        $cancelorder = json_decode($cancelorder);
        if ($cancelorder->success === 1) {
            return $cancelorder;
        } else {
            return null;
        }
    }

    public function getHistory($pair) //не используется
    {
        // записывать id покупки с которой начнем выводить историю сделок, что бы потом понять сколько было заработано с этого момента
        // на моменте покупки/продажи списывается комиссия 0,4% не от суммы прибыли, а от торгуемой суммы.
        // 30 р это будет комиссия 0,2% от 15000р при покупке например 1,5 ETH за 10000р. Потом продаем 1,5 ETH за 10100
        // и получаем конечную сумму 15150р. От нее будет также комиссия 0,2% то есть 30,3р. По итогу прибыль : 15150-1500-30-30,3 = 89,7
        // 15150*0,998 - 15000*1,002 = 15119,7 - 15030 = 89,7
        // sell(start_amount*rate*0,998) - buy(start_amount*rate*1,002) = прибыль чистая с торга
        $params = [
            //'from_id' => 2500112592536658,
            //'since' => 1575504000, // с какого времени вывод
            'pair' => $pair,
        ];
        $gethistory = $this->getApiAction('TradeHistory', $params);
        $gethistory = json_decode($gethistory);
        if ($gethistory->success === 1){
            return $gethistory;
        }
    }

    public function depthAction($pair = 'eth_rur') //получаем среднее из 5 и первое из стакана
    {
        $path = "https://yobit.net/api/3/depth/{$pair}?limit=5";
        $data = file_get_contents($path);
        $data = json_decode($data);
        $info = '';
        if ($data){
            $info = $data->{$pair}->bids;;
        }else{
            debug("Не пройдена проверка в строке " . __LINE__);
            exit();
        }
        $bids = 0;

        foreach ($info as $v) {
            $bids += $v[0]; //все 5 вытащенных значений складываются в одно
        }

        $middle = $bids/5;
        $first = $info[0][0];
        $result = ['middle' => $middle,'first' => $first];

        return $result;
    }

    public function middleAction () // метод для вытаскивания инфы что бы отобразить на экране
    {
        $middle = $this->depthAction();
        if ($this->isAjax()){ // если был произведен ассинхронный запрос через ajax, то выводится ajax строка. Иначе выдает ошибку типа не найден вид
            exit(json_encode($middle)); // отправляем ввиде json данных, как просто массив не передаст
        }
    }

    public function profitAction () //метод для отображения навара, который наторговался и который можно использовать
    {
        $profit = Db::query("SELECT type, profit FROM profit");
        $profit = $profit[1]['profit'] - $profit[0]['profit'];
        if ($this->isAjax()){ // если был произведен ассинхронный запрос через ajax, то выводится ajax строка. Иначе выдает ошибку типа не найден вид
            exit($profit);
        }
    }

    public function delprofAction () //метод для удаления из БД инфы о наваре
    {
        $delprof = Db::myExecute("UPDATE profit SET id_order = 0, profit = 0 WHERE 1");
        if ($this->isAjax()){ // если был произведен ассинхронный запрос через ajax, то выводится ajax строка. Иначе выдает ошибку типа не найден вид
            exit();
        }
    }

    public function saveTime($serv_time, $middle) // метод для записи в БД в одно поле значения и времени ср/из/5
    {
        $cource = Db::query("SELECT time, value FROM cource WHERE type = ?",['yobit']); // ранее записанные данные извлекаются
        $time = $cource[0]['time'] + 1800; //полчаса
        if ($serv_time > $time){ // смотрим прошло ли полчаса
            Db::myExecute("UPDATE cource SET time = ?, value = ? WHERE type = ?",[$serv_time, $middle, 'yobit']);
            // записываем значение ср/из/5 в базу что бы отследить резкий рост
        }
        $savetime = $cource[0]; //достаем курс йобита, записываемый раз в полчаса
        return $savetime;
    }

    public function growthCource ($serv_time, $middle, $etherscan)
        // метод записывает значения курса с биржи и с криптокомпаре а также разницу между ними через заданное время(10мин)
        // за определенный рассматриваемый промежуток времени(24 часа)
    {
        $timeover = 86400; // время через которое удаляем записи, 24 часа
        $interval = 600; // интервал времени, через который записываем значение в БД
        $dif = $timeover/$interval;

        $deltime = $serv_time - $timeover; // если запись старее определенного времени, то удалить их все
        $deltime = Db::myExecute("DELETE FROM ethtable WHERE time < ?;", [$deltime]);

        //$etherdata = Db::query("SELECT time, differ FROM ethtable ORDER BY id DESC LIMIT $dif"); извлечь все записи
        $etherdata = Db::query("SELECT time, yobit, ethscan, differ FROM ethtable ORDER BY id DESC LIMIT $dif"); // записи за последнее установленное время
        $dif = 0;
        $yobmid = 0;
        $ethmid = 0;
        foreach ($etherdata as $v) {
            $dif += $v['differ']; // среднее значение разницы с etherscan
            $yobmid += $v['yobit']; // среднее значение yobit
            $ethmid += $v['ethscan'];
        }

        $time = 0;
        if (!empty($etherdata)){
            $dif = $dif/count($etherdata); //ср знач разницы за установл время
            $yobmid = $yobmid/count($etherdata);
            $ethmid = $ethmid/count($etherdata);
            $time = $etherdata[0]['time'] + $interval; // 15 минут 900
        }

        // извлечь среднее по этерскану и текущее и фиксировать рост идет там или падение

        $differ = $etherscan - $middle;
        if ($serv_time > $time){
            Db::myExecute("INSERT INTO ethtable (yobit, ethscan, differ, time) VALUES (?,?,?,?)",[$middle, $etherscan, $differ, $serv_time]);
            //Db::insertData('cource', ['etherscan', $serv_time, $etherscan], 'type, time, value');
            // через каждые 15 минут записываем значение ср/из/5 в базу что бы отследить резкий рост
        }

        $return = ['yobmid' => $yobmid, 'ethmid' => $ethmid, 'differ' => $dif];

        return $return;
    }

    public function chartAction() //метод для вытаскивания инфы для графика
    {
        $etherdata = Db::query("SELECT time, yobit, ethscan FROM ethtable ORDER BY id");
        $deal = Db::query("SELECT type, rate, timestamp_created, time_over FROM bot ORDER BY id");
        $etherdata = [$etherdata, $deal];

        //debug($etherdata);

        if ($this->isAjax()){ // если был произведен ассинхронный запрос через ajax, то выводится ajax строка. Иначе выдает ошибку типа не найден вид
            exit(json_encode($etherdata)); // отправляем ввиде json данных, как просто массив не передаст
        }
    }

    public function cryptocompare($serv_time) //извлечение информации с сайта cryptocompare о курсе eth
    {
        $compare = Db::query("SELECT time, value FROM cource WHERE type = ?",['cryptocompare']);
        $time = $compare[0]['time'] + 60; // записываем значение eth с compare 25 секунд это 100к обр в месяц

        if ($serv_time > $time){
            $eth = "https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=USD,RUB&extraParams=b1ba5c49f8e9e59b6b24276cf6348f1a0b8d170b51968639964064032192f31e"; //вытаскиваем значение eth в usd с etherscan
            $geteth = 0;
            if (file_get_contents($eth)){
                $geteth = json_decode(file_get_contents($eth));
            }
            Db::myExecute("UPDATE cource SET time = ?, value = ? WHERE type = ?",[$serv_time, $geteth->RUB, 'cryptocompare']);
        }
        $compare = $compare[0]['value'];
        return $compare;
    }

    public function nonce()
    {
        $file = APP . "/views/Bot/nonce";
        $nonce = file_get_contents($file);
        $nonce_num = file_put_contents($file, $nonce + 1);
        return $nonce;
    }
/*
    public function decode($res, $arr1 = null, $arr2 = null)
    {
        $object = json_decode($res, true); // второй параметр true возвращает ассоц. массив
        $arr = [];
        foreach ($object as $obj) {
            $arr = $obj;
        }
        if ($arr1){
            if ($arr2){
                return $arr[$arr1][$arr2];
            }
            return $arr[$arr1];
        }
        return $arr;
    }
*/

    public function etherscan() //извлечение информации с сайта etherscan о курсе eth. Устаревший метод
    {
        $eth = "https://api.etherscan.io/api?module=stats&action=ethprice&apikey=NT9FANV4JRB8VUJYKZ3RZGGYHG437A58AB"; //вытаскиваем значение eth в usd с etherscan
        $geteth = 0;
        if (file_get_contents($eth)){
            $geteth = json_decode(file_get_contents($eth));
        }
        if (!empty($geteth)){
            $etherscan = 0; //в случае ошибки парсинга, закупка не произойдет
            if ($geteth->status != 0){
                $ethprice = $geteth->result->ethusd;
                $usdrur = $this->getCourseUsd()->Valute->USD->Value; //вытаскиваем курс usd/rur
                $etherscan = $ethprice * $usdrur; //получаем eth в rur с etherscan
            }
            return $etherscan;
        }
        return $geteth;
    }


    function getCourseUsd() //получение курса доллара. Устаревший метод
    {
        $json_daily_file = APP . '/views/Bot/funds/daily.json';
        if (!is_file($json_daily_file) || filemtime($json_daily_file) < time() - 3600) {
            if ($json_daily = file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js')) {
                file_put_contents($json_daily_file, $json_daily);
            }
        }
        return json_decode(file_get_contents($json_daily_file));
    }

    public function getCircle ($serv_time, $middle) //устаревший метод, не используется
    {
        $circle = ['time' => $serv_time, 'value' => $middle];  // через каждые полчаса записываем значение из стакана чтоб отследить рост

        if (empty($this->getKey('circle'))){
            $this->setKey('circle', $circle);
        }

        $savetime = $this->getKey('circle');
        $time = $savetime['time'] + 1800;
        if ($serv_time > $time){
            $this->setKey('circle', $circle);
        }
        return $savetime;
    }


    public function getFile($key) // Устаревший метод, для записи в файл
    {
        $file =  APP . '/views/Bot/funds/' . $key . '.txt'; //хэширует данные и указывает путь
        return $file;
    }

    public function setKey($key, $data) // Устаревший метод, для записи в файл
    {
        $content['data'] = $data;
        file_put_contents(self::getFile($key), serialize($content));
    }

    public function getKey($key) // Устаревший метод, для записи в файл
    {
        if(file_exists(self::getFile($key))){
            $content = unserialize(file_get_contents(self::getFile($key)));
            return $content['data'];
        }
        return false;
    }

    public function deleteKey($key)
    {
        if(file_exists(self::getFile($key))){
            unlink(self::getFile($key)); //удаляем файл
        }
    }
}