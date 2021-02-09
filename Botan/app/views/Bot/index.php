<?php use \app\controllers\BotController;?>
<div class="logo">
    <div class="centerbar">
        <div class="leftbar">
            <div>На какую сумму торгуем, руб.</div>
            <?php $part = isset($_COOKIE['botpart']) ? $_COOKIE['botpart'] : 0;?>
            <?php $percent = isset($_COOKIE['botpercent']) ? $_COOKIE['botpercent'] : 0;?>
            <?php $profcheck = isset($_COOKIE['botprof']) ? $_COOKIE['botprof'] : 0;?>
            <?php $check = $profcheck === 'true' ? 'checked' : '';?>
            <input type="number" id="numb" value="<?= $part ?>" min="0" max="30000" step="10">
            <input type="range" id="range" value="<?= $part ?>" min="0" max="30000" step="10"><br>
            <div>
                <div class="leftbar">
                    Использовать </br>
                    навар <input type="checkbox" name="check" <?= $check ?>>
                </div>
                <div id="clear" class="middlebar">
                    <div>Удалить</br> из БД</div>
                </div>
                <div id="refresh" class="rightbar">
                    <div>Обновить</div>
                    <div class="ref" style="color: #0000ff"></div>
                </div>
            </div></br>
            <div class="leftbar">Накрутка, % </br>
                <input type="number" name="num1" value="<?= $percent ?>" min="0" max="5" step="0.1">
            </div>
            <div class="middlebar">от среднего из 5, руб </br>
                <div class="middle" style="color: green"></div>
            </div>
            <div class="rightbar">учитывая 0.4% yobit, руб </br>
                <div class="middleyo" style="color: red"></div>
            </div>
            <input type="range" name="range1" value="<?= $percent ?>" min="0" max="5" step="0.1"><br>
        </div>
        <div class="rightbar">
            <div><a id="getData">Запуск бота</a></div>
            <div><a id="getDataOff">Остановить</a></div>
        </div>
    </div>
    <div class="centerbar2">
        <div class="but">
            <button id="getChart" class="chart" value="0">Мониторить график</button>
        </div>
        <canvas id="myChart"></canvas>

        <canvas id="myChart1"></canvas>
    </div>

    <table style="width: 500px; margin: 30px auto; padding: 100px 0 100px 0px;">
        <thead>
        <tr>
            <th class="th-style">Транзакция</th>
            <th class="th-style">Цена</th>
            <th class="th-style">Кол-во</th>
        </tr>
        </thead>
        <tbody class="table-orders"></tbody>
    </table>
</div>