// Bot

/*
function bot() { //срабатывает скрипт при клике по по элементу body с указанным классом
    var pairs = $('.chosen-single span').html();
    console.log(pairs);
    $.ajax({
        url: '/bot/bot', // Срабатывает метод botAction класса BotController
        data: {pairs: pairs},
        type: 'GET',
        success: function(crypto){ // функция принимет данные ajax запроса в виде строки после отработки метода
            var data = JSON.parse(crypto), // парсятся данные через jquery и превращаются в массив
                order;
            //console.log(data);
            $('.table-orders').empty(); // очищается содержимое тега
            for (var i = 0; i < data[pairs].length; i++){ // создаем цикл
                //console.log(data[pairs][i]['type']);
                if (data[pairs][i]['type'] == 'ask'){
                    order = '<td style="color: red">SELL</td>';
                }else {
                    order = '<td style="color: green">BUY</td>';
                }
                $('.table-orders').append('<tr>'+order+'<td>'+data[pairs][i]['price']+'</td><td>'+data[pairs][i]['amount']+'</td></tr>');
            }
            //console.log(data);
        },
        error: function(e){
            //alert('Ошибка! Попробуйте позже')
        }
    })
};
*/

function api(){
    var lot = $("#numb").val(); //передаем значение торгуемой суммы
    var percent = $(".leftbar input[name='num1']").val(); //передаем знаение процента
    var profit = $(".leftbar input[name='check']").is(':checked'); //передаем торгуем ли мы с наваром
    console.log(profit);

    $.ajax({
        url: '/bot/trade', // Срабатывает метод getApiAction класса BotController
        data: {lot: lot, percent: percent, profit: profit},
        type: 'GET',
        success: function(crypto){ // функция принимет данные ajax запроса в виде строки после отработки метода
            //var data = JSON.parse(crypto); // парсятся данные через jquery и превращаются в массив
            //console.log(crypto);
        },
        error: function(e){
            console.log(e);
            //alert('Ошибка! Попробуйте позже')
        }
    })
}

$('#getData').on('click', function (e) { //срабатывает скрипт при клике по id getData

    e.preventDefault(); // не дает переходить по ссылке при клике
    //setInterval(bot,2000); //зацикливает функцию и повторяет выполнение через время

    api(); //просто запускает функцию
    var intervalID = setInterval(api,5000);
    $('#getDataOff').on('click', function (e) { //срабатывает скрипт при клике по id getData
        e.preventDefault();
        console.log(intervalID);
        clearInterval(intervalID);
    })
})

function middle(range) { //для извлечения ср из 5 и подстановки в значение процента в рублях
    $.ajax({
        url: '/bot/middle',
        type: 'GET',
        success: function(middle){
            var mid = JSON.parse(middle);
            console.log(mid['middle']);
            $('.middle').text(Math.round(mid['middle']*range/100));
            $('.middleyo').text(Math.round(mid['middle']*(range/100 - 0.4/100)));
        },
        error: function(e){
            alert('Ошибка! Попробуйте позже')
        }
    })
}

function profit() { //для извлечения ср из 5 и подстановки в значение процента в рублях
    $.ajax({
        url: '/bot/profit',
        type: 'GET',
        success: function(profit){
            var pro = JSON.parse(profit);
            console.log(pro.toFixed(2));
            $("#refresh .ref").text(pro.toFixed(2)+' руб.');
        },
        error: function(e){
            alert('Ошибка! Попробуйте позже')
        }
    })
}

function delprof() { //для извлечения ср из 5 и подстановки в значение процента в рублях
    $.ajax({
        url: '/bot/delprof',
        type: 'GET'
    })
}

$("#clear").on('click', function () { // удаляет
    var del = confirm("Вы действительно хотите удалить значение навара из БД?");
    console.log(del);
    if (del){
        delprof();
    }
})

$("#refresh").on('click', function () { // обновляет значение навара
    profit();
})

$(".leftbar input[name='range1']").on('input', function (){ //для накрутки
    var range = $(this).val();
    $(".leftbar input[name='num1']").val(range);
})

$(".leftbar input[name='num1']").change(function(){ //для накрутки
    var range = $(this).val();
    middle(range); //запускаем функцию чтоб подставить значение
    $(".leftbar input[name='range1']").val(range);
})

$("#range ").on('input', function () { //оба варика рабочие
    var range1 = $(this).val();
    $("#numb").val(range1);
    //console.log(range1);
})

$("#numb").on('change', function () { //для суммы торгов
    var range1 = $(this).val();
    $("#range").val(range1);
    console.log(range1);
})


function Charts() {
    let time = [],
        yobit = [],
        crypto = [],
        datasets = [{
            label: 'Yobit',
            backgroundColor: 'red',
            borderColor: 'red',
            data: yobit,
            fill: false
        }, {
            label: 'Cryptocompare',
            fill: false,
            backgroundColor: 'green',
            borderColor: 'green',
            data: crypto
        }],
        min = 0,
        max = 20000;

    function params(time, yobit, crypto, datasets, min, max) {
        //console.log(time);
        let param = { //https://www.chartjs.org

            type: 'line',
            data: {
                //labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                labels: time,
                datasets: datasets,
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Зависимость между yobit и cryptocompare'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Время'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Значение курса'
                        },
                        ticks: {
                            min: min-300,
                            max: max+300,

                            // forces step size to be 5 units
                            stepSize: 10
                        }
                    }]
                }
            }
        };
        return param;
    }

    $.ajax({
        url: '/bot/chart',
        type: 'GET',

        success: function (chart){
            let pro = JSON.parse(chart),
                stat = pro[0],
                deal = pro[1];
            console.log(stat);
            console.log(deal);

            for (key in stat){ //в цикле достаем значения
                //time = time + ', ' + pro[key].time;
                //time = pro[key].time;

                let date = new Date(stat[key].time*1000), //создаем объект времени с миллисекндами
                    hours = date.getHours(), //получаем из этого часы
                    minutes = "0" + date.getMinutes(),
                    formattedTime = hours + ':' + minutes.substr(-2); //получаем удобный формат времени

                time[time.length] = formattedTime; // добавляем значение в конец пустого массива
                yobit[yobit.length] = stat[key].yobit;
                crypto[crypto.length] = stat[key].ethscan;
            }
/*
            Array.min = function(e){ //функция для получения max и min из массива
                return Math.min.apply(Math, e);
            };
            Array.max = function(e){ //функция для получения max и min из массива
                return Math.max.apply(Math, e);
            };
            var yomin = Array.min(yobit); //находим минимальное и максимальное значение курса чтоб построить график в этих пределах
            var yomax = Array.max(yobit);
            var ethmin = Array.min(crypto);
            var ethmax = Array.max(crypto);

            if (yomin < ethmin){
                min = yomin;
            }else{
                min = ethmin
            }
            if (yomax > ethmax){
                max = yomax;
            }else{
                max = ethmax
            }
*/
            min = Math.min(...yobit, ...crypto); // spread оператор для массивов. позволяет Math.min найти минимальное значение из массива
            max = Math.max(...yobit, ...crypto); // так же можно найти мин или макс значение из нескольких массивов сразу. 2 строчки кода вместо 20)

            //console.log(min);
            //console.log(max);

            let ctx = document.getElementById('myChart').getContext('2d'), //переменная для графиков
                chartjs = new Chart(ctx, params(time, yobit, crypto, datasets, min, max)); // функция построения графиков
            //console.log(params());
            datasets = [{
                label: 'Покупка',
                backgroundColor: 'red',
                borderColor: 'red',
                data: yobit,
                fill: false
            }, {
                label: 'Продажа',
                fill: false,
                backgroundColor: 'green',
                borderColor: 'green',
                data: crypto
            }];

            let ct1x = document.getElementById('myChart1').getContext('2d'), //переменная для графиков
                chartjs1 = new Chart(ctx1, params(time, yobit, crypto, datasets, min, max)); // функция построения графиков
        },
        error: function(e){
            alert('Ошибка! Попробуйте позже')
        }
    })
}
$('#getChart').on('click', function (e) { //срабатывает скрипт при клике по id getData
    Charts();
})



/*
    api();
    var intervalID = setInterval(api,5000);
    $('#getDataOff').on('click', function (e) { //срабатывает скрипт при клике по id getData
        e.preventDefault();
        console.log(intervalID);
        clearInterval(intervalID);
    })*/


/*
$(function pairs() {
    $.ajax({
        url: '/bot/bot',
        type: 'GET',
        success: function (crypto) {
            var data = JSON.parse(crypto);
            //console.log(data['pairs']);
            $.each(data['pairs'], function (value) {
                //console.log(value);
                $('.table-pairs').prepend('<tr><td>'+value+'</td></tr>');
            })
        },
        error: function (e) {
            alert('Ошибка! Попробуйте позже')
        }
    })
});
*/