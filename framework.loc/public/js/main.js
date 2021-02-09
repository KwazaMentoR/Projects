// Добавление в корзину. Делигирование событий с помощью события on
$('body').on('click', '.add-to-cart-link', function (event) { //срабатывает скрипт при клике по элементу body с указанным классом
    event.preventDefault(); // не дает переходить по ссылке при клике
    var id = $(this).data('id'), //записывает значение кликнутого элемента
        qty = $('.quantity input').val() ? $('.quantity input').val() : 1, // значение тега input класса quantity или 1. Колл-во товара
        mod = $('.available select').val(); // выбраная модификация товара
    $.ajax({
        url: '/cart/add', // Срабатывает метод addAction класса CartController
        data: {id: id, qty: qty, mod: mod}, //в гет запросе передаются все эти данные
        type: 'GET',
        success: function(cart){ // cart - ответ который приходит от сервера
            showCart(cart);
            console.log(cart);
        },
        error: function(){
            alert('Ошибка! Попробуйте позже')
        }
    })
});

function showCart(cart){
    if($.trim(cart) == '<h3>Корзина пуста</h3>'){
        $('#cart .modal-footer a, #cart .modal-footer .btn-danger').css('display', 'none');
    }else{
        $('#cart .modal-footer a, #cart .modal-footer .btn-danger').css('display', 'inline-block');
    }
    $('#cart .modal-body').html(cart);
    $('#cart').modal(); //функция бутстрапа, показывает модальное окно которое заполняется в watches из cart_modal
    if($('.cart-sum').text()){ // обновляет общую сумму в корзине у иконки корзины при добавлении в корзину
        $('.simpleCart_total').html($('#cart .cart-sum').text());
    }else{
        $('.simpleCart_total').text('Empty Cart');
    }
}


function getCart() {
    $.ajax({
        url: '/cart/show', // Срабатывает метод addAction класса CartController
        type: 'GET',
        success: function (cart) { // cart - ответ который приходит от сервера
            showCart(cart);
        },
        error: function () {
            alert('Ошибка! Попробуйте позже')
        }
    })
}

$('#cart .modal-body').on('click', '.del-item', function () {
    var id = $(this).data('id');
    $.ajax({
        url: '/cart/delete',
        data: {id:id},
        type: 'GET',
        success: function (res) {
            showCart(res);
        },
        error: function () {
            alert('Error');
        }
    })
});


$('#currency').change(function () {
    window.location = 'currency/change?curr=' + $(this).val();
});


$('.available select').on('change', function () {// тег select в классе .available
    // при изменении модификатора воспринимает значения классов value data-title и data-price тега option. Эти классы специально создаются под js
    var modId = $(this).val(),
        color = $(this).find('option').filter(':selected').data('title'), // записывает в переменную значение класса
        price = $(this).find('option').filter(':selected').data('price'),
        basePrice = $('#base-price').data('base'); // берет значение класса data-base по id = base-price
    if (price){
        $('#base-price').text(price); // перезаписывает значение заключенное в теге
    }else{
        $('#base-price').text(basePrice);
    }
});

/*
$(document).on('click', function(e) { // показывает в консоли кликнутый элемент
    console.log(e.target)
});
*/


// Bot

$(".chosen-select").chosen({
    width: "10%",
    disable_search: false,
    disable_search_threshold: 5,
    enable_split_word_search: false,
    max_selected_options: 10,
    no_results_text: "Ничего не найдено",
    placeholder_text_multiple: "Выберите несколько параметров",
    placeholder_text_single: "Выберите параметр",
    search_contains: true,
    display_disabled_options: false,
    display_selected_options: false,
    inherit_select_classes: true,
    max_shown_results: Infinity
});

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

function api(){
    var lot = $("#numb").val(); //берем значение торгуемой суммы
    var percent = $(".leftbar input[name='num1']").val(); //берем знаение процента
    var profit = $(".leftbar input[name='check']").is(':checked');
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
    bot(); //просто запускает функцию
    //setInterval(bot,2000); //зацикливает функцию и повторяет выполнение через время
    api();
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
    var time = [];
    var yobit = [];
    var crypto = [];
    var min = 0;
    var max = 20000;

    function params() {
        //console.log(time);
        var param = { //https://www.chartjs.org

            type: 'line',
            data: {
                //labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                labels: time,
                datasets: [{
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
                }]
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
                            min: min-1000,
                            max: max+1000,

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
            var pro = JSON.parse(chart);
            //console.log(pro);
            for (key in pro){ //в цикле достаем значения
                //time = time + ', ' + pro[key].time;
                //time = pro[key].time;

                var date = new Date(pro[key].time*1000); //создаем объект времени с миллисекндами
                var hours = date.getHours(); //получаем из этого часы
                var minutes = "0" + date.getMinutes();
                var formattedTime = hours + ':' + minutes.substr(-2); //получаем удобный формат времени

                time[time.length] = formattedTime; // добавляем значение в конец пустого массива
                yobit[yobit.length] = pro[key].yobit;
                crypto[crypto.length] = pro[key].ethscan;
            }

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
            //console.log(min);
            //console.log(max);

            var ctx = document.getElementById('myChart').getContext('2d'); //переменная для графиков
            var chartjs = new Chart(ctx, params()); // функция построения графиков
            //console.log(params());
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