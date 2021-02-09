
package javarushbegin;

// 21_11_2020 Начал JavaRush
// 20_12_2020 достиг 7 уровня
// 27_12_2020 8 ур.
// 07_01_2020 10 ур.

import java.io.*;
import java.nio.file.DirectoryStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.LocalDate;
import java.time.Month;
import java.time.ZoneId;
import java.util.*;
import java.util.function.Consumer;
import java.util.stream.Collectors;
import java.util.stream.Stream;


public class JavaRushBegin {
    
    /* 
    Полезные ссылки 
    https://javarush.ru/groups/posts/1940-klass-hashmap- HashMap в Java
    https://javarush.ru/groups/posts/2181-vlozhennihe-vnutrennie-klassih Вложенные внутренние классы
    https://javarush.ru/groups/posts/2743-rukovodstvo-po-klassu-java-integer Класс обертка Integer примитивного типа
    https://javarush.ru/quests/lectures/questsyntaxpro.level12.lecture01 Еще про autoboxing и кэширование
    http://espressocode.top/character-hashcode-in-java-with-examples/ про хэш код символа
    https://javarush.ru/groups/posts/615-idea-hot-keys горячие клавиши intellij

    Горячие клавиши Intellij через tab
    sout — System.out.println
    psfs - public static final String
    fori - цикл for

    Ctrl+Alt+T оборачивает код в обертку из if или while итд
    Ctrl+Alt+L автоформатирование кода
    Ctrl+D дублирует строку
    Ctrl+shift+down перемещает строчку вниз

    */
    
    public static int id;
    
    public static void main(String[] args) throws IOException {

        System.out.printf("Я %d-й .\n", id); // можно так вставлять переменные
        String string = "12.84";
        System.out.println(Math.round(Double.parseDouble(string)));

        "".valueOf(12); // видит как String.valueOf(12);
        // Внутренний класс это обычный класс, а вложенный класс это статический
        
        //Outer outer = new Outer();
        //Outer.Inner inner = outer.new Inner(); // можно так создать экземпляр внутреннего класса
        
        Outer.Inner inner = new Outer().new Inner(); // а можно и так
        
        Outer.Nested nested = new Outer.Nested(); // а так создается вложенный класс
        
        // В файле с расширением java всегда должен быть класс, имя которого совпадает с именем файла, и у него есть модификатор public.

        Scanner sc = new Scanner(System.in);
        System.out.println("Введите число:");
        
        Integer x = new Integer(25);
        System.out.println(x);
        Integer a = Integer.valueOf(10);
        System.out.println(a);
        Integer b = Integer.valueOf("5");
        System.out.println(b);
        Integer c = 7;
        System.out.println(c); // это все одинаковые способы упаковки примитивов в объект

        Integer first = 1000;
        Integer second = 1000;
        int third = 1000;
    
        System.out.println(first.equals(second)); // делаем так из за кэширования
        System.out.println(third == second);
        System.out.println(third == first); 
        
        Character first1 = 'ы';
        Character second1 = 'ы';
        
        System.out.println(first1.equals(second1)); // для Character тоже есть кэш, потому лучше через equals
        
        String[] elements = new String[10];
        elements[0] = "Привет";
        elements = Arrays.copyOf(elements, 15); // копируем значения массива старого в новый с раширеенным колличеством
        
        ArrayList<String> stringArrayList = new ArrayList<>();                                                  
        Stack<String> stack = new Stack<>();                                                  
        //Map<String, ArrayList<String>> map = new HashMap<String, ArrayList<String>>();                                                  
        Map<String, ArrayList<String>> map1 = new HashMap<>();                                                  
        var exceptionsList = new ArrayList<Exception>();                                                  
        var filesStack = new Stack<File>();  // оператор <> может справа быть пустым, если слева указан тип, кроме случаев с var
        
        var strings = new ArrayList<String>() // можно даже так задать массив
        {{
            add("Так");
            add("тоже");
            add("можно");
            add("делать");
            add("!");
        }}; 
        
        var elements1 = new ArrayList<>();     // Если в ArrayList лежат разные типы данных                                      
        elements1.add("Привет");                                                  
        elements1.add(10);                                                  
        elements1.add(new Integer[15]);                                                  
        elements1.add(new LinkageError());
        
        for (int i = 0; i < elements1.size()-1; i++) {  
        // то можно извлечь каждое из них проверив объект с помощью instanceof, чтоб понять какому типу данных оно принадлежит
            if (elements1.get(i) instanceof String) {
                String s = (String) elements1.get(i);        //(String) оператор преобразования типа
                System.out.println(s);
            }else if(elements1.get(i) instanceof Integer){
                Integer s = (Integer) elements1.get(i);
                System.out.println(s);
            }else if(elements1.get(i) instanceof Integer[]){
                Integer[] s = (Integer[]) elements1.get(i);
                System.out.println(Arrays.toString(s));
            }else if (elements1.get(i) instanceof Object){
                LinkageError s = (LinkageError) elements1.get(i);
                System.out.println(s);
            }
        }

        // Нельзя удалить элемент из ArrayList во время цикла foreach так как элементы сдвигаются, можно юзать цикл for

        String[] wordsArray = "Думаю, это будет новой фичей.".split(" "); // это массив
        ArrayList<String> wordsList = new ArrayList<>(Arrays.asList(wordsArray)); // это уже ArrayList
        HashSet<String> wordsHashSet = new HashSet<>(wordsList); // это HashSet из ArrayList

        wordsList.forEach(System.out::println); //так можно зафоричить
        wordsHashSet.forEach(System.out::println);

        ArrayList<String> words = new ArrayList<>();
        words.add("Hello world!");
        words.add("Amigo");
        words.add("Elly");
        words.add("Kerry");
        words.add("Bug");

        words.removeIf("Elly"::equalsIgnoreCase); // вот так можно удалить элемент из коллекции

        for (int i = 0; i < words.size(); i++) { // и вот так через for
            if ("bug".equalsIgnoreCase(words.get(i))){
                words.remove(i);
                i--;
            }
        }
        Iterator<String> it = words.iterator(); // и вот так через итератор
        while (it.hasNext()){
            String str = it.next();
            if ("bug".equalsIgnoreCase(str)){
                it.remove();
            }
        }
        ArrayList<String> arr = new ArrayList<>(words); // и вот так через копию коллекции
        for (String aa:arr) {
            if ("bug".equalsIgnoreCase(aa)){
                words.remove(aa);
            }
        }

        //Collections
        ArrayList<String> arr1 = new ArrayList<String>();
        arr1.add("Yo");
        arr1.add("Motherfuck");
        ArrayList<String> arra1 = new ArrayList<String>();
        arra1.add("Yoo");
        arra1.add("Motherfucker");
        Collections.copy(arr1,arra1); // А вот так можно копировать все элементы из одного списка в другой
        String st = "Привет";
        String st1 = "Мазафака";
        Collections.addAll(arr1,st,st1); // Вот так добавляем элементы
        Collections.replaceAll(arr1,"Yoo",st); // Замена значений

        // HashMap коллекция
        HashMap<String, Integer> map = new HashMap<String, Integer>();
        map.put("Серега", 21);
        map.put("Николай", 22);
        map.put("Иван Петрович", 48);
        map.put("Анюта", null);

        for (String key:map.keySet()) {
            Integer value = map.get(key);
            System.out.println(key + " --> " + value); //выводим ключ -> значение
        }

        Set<Map.Entry<String, Integer>> entries = map.entrySet();
        for(Map.Entry<String, Integer> pair: entries)
        { // здесь выводим то же самое, но еще дополнительно можем использовать пару pair {key->value} как объект
            String key = pair.getKey();
            Integer value = pair.getValue();
            System.out.println(key + " --> " + value);
        }

        String OUTPUT_FORMAT = "Метод %s вызван из строки %s класса %s в файле %s.\n";
        for (StackTraceElement info : Thread.currentThread().getStackTrace()) {
            System.out.printf(OUTPUT_FORMAT,info.getMethodName(),info.getLineNumber(),info.getClassName(),info.getFileName());
            // с помощью printf можно удобно выводить строку с аргументами
        }

        // Про считывание с клавиатуры https://javarush.ru/groups/posts/1919-schitihvanie-s-klaviaturih--riderih
        // вывод текста с помощью BufferedReader
        Scanner scanner1 = new Scanner(System.in);
        FileInputStream reader2 = new FileInputStream(scanner1.nextLine());
        BufferedReader buffer = new BufferedReader(new InputStreamReader(reader2));
        String line2= buffer.readLine();
        while (line2!=null){
            System.out.println(line2);
            line2= buffer.readLine();
        }
        reader2.close();
        buffer.close();

        // здесь используется try-with-resources. что бы не писать reader.close();
        // заносим в скобки то что нужно закрыть в случае возникновения исключения
        try (BufferedReader reader = new BufferedReader(new InputStreamReader(System.in))){
            String line = reader.readLine();
            System.out.println(line.toLowerCase());
        } catch (IOException e) {
            System.out.println("Something went wrong : " + e);
        }

        // указать путь к файлу через консоль и записать через нее строки, exit - выход
        Scanner scanner3 = new Scanner(System.in);
        Path outputFileName = Path.of(scanner3.nextLine());
        try (
                BufferedWriter bufferedWriter = Files.newBufferedWriter(outputFileName);
                BufferedReader reader = new BufferedReader(new InputStreamReader(System.in))
        ){
            String line;
            while (true){
                line = reader.readLine();
                bufferedWriter.write(line + "\n");
                if (line.equals("exit")){
                    break;
                }
            }
        }catch (IOException e) {
            System.out.println("Something went wrong : " + e);
        }

        // вот то же самое только попроще
        BufferedReader reader = new BufferedReader(new InputStreamReader(System.in));
        String name = reader.readLine();
        BufferedWriter bufferedWriter = new BufferedWriter(new FileWriter(name));
        String line;
        while (true){
            line = reader.readLine();
            bufferedWriter.write(line + "\n");
            if (line.equals("exit")){
                break;
            }
        }
        bufferedWriter.close();

        // Считать из консоли файлик и вывести в консоли только четные, отсортированные по возрастанию числа.
        // https://javarush.ru/groups/posts/593-bufferedreader-i-bufferedwritter
        FileInputStream stream4 = new FileInputStream(new Scanner(System.in).nextLine());
        BufferedReader breader = new BufferedReader(new InputStreamReader(stream4));
        ArrayList<Integer> list = new ArrayList();
        String line3;
        while ((line3 = breader.readLine()) != null){
            Integer integ = Integer.parseInt(line3);
            if (integ % 2 == 0){
                list.add(integ);
            }
        }
        Collections.sort(list);
        for (Integer l : list) {
            System.out.println(l);
        }
        stream4.close();

        // ввести из консоли путь к файлу и скопировать байты информации через поток в другой файл поменяв при этом местами
        // символы  1и2, 3и4 и так далее
        try (Scanner scanner = new Scanner(System.in);
             InputStream input = Files.newInputStream(Path.of(scanner.nextLine()));
             OutputStream output = Files.newOutputStream(Path.of(scanner.nextLine())) ){
            byte[] inBuffer = input.readAllBytes();
            byte[] outBuffer = new byte[inBuffer.length];
            for (int j = 0; j < inBuffer.length; j+=2) {
                if (j < inBuffer.length - 1) {
                    outBuffer[j] = inBuffer[j+1];
                    outBuffer[j+1] = inBuffer[j];
                } else {
                    outBuffer[j] = inBuffer[j];
                }
            }
            output.write(outBuffer);
        } catch (IOException e) {
            System.out.println("Something went wrong : " + e);
        }

        // почитал вот тут https://askdev.ru/q/kak-udalit-opredelennye-simvoly-iz-stroki-v-java-71155/
        // и тут https://javarush.ru/groups/posts/2275-files-path?post=full#discussion
        Scanner scanner = new Scanner(System.in);
        List<String> lines = Files.readAllLines(Paths.get(scanner.nextLine())); //читаем все строки файла в массив
        for (String l : lines) {
            System.out.println(l.replaceAll("[,. ]","")); //заменяем символы
        }

        // читаем строку из консоли через буфер
        try (InputStream stream = System.in;
             InputStreamReader reader1 = new InputStreamReader(stream);
             BufferedReader buff = new BufferedReader(reader1)) {
            String line1 = buff.readLine();
            char[] chars = line1.toCharArray();
            for (int i = 0; i < chars.length; i++) {
                if (i % 2 == 1) {
                    System.out.print(String.valueOf(chars[i]).toUpperCase());
                } else {
                    System.out.print(String.valueOf(chars[i]).toLowerCase());
                }
            }
        } catch (IOException e) {
            System.out.println("Something went wrong : " + e);
        }

        // копирование файла из одной дирректории в другую
        Scanner scanner2 = new Scanner(System.in);
        Path sourceDirectory = Path.of(scanner2.nextLine());
        Path targetDirectory = Path.of(scanner2.nextLine());

        try (DirectoryStream<Path> files = Files.newDirectoryStream(sourceDirectory)){
            for (Path p : files) {
                if (Files.isRegularFile(p)) { //если путь это файл то
                    Path name2 = p.getFileName(); // получаем имя файла или дирректории
                    Files.copy(p, targetDirectory.resolve(name2)); // добавляем к пути второй дирректории имя файла и копируем по новому пути
                }
                System.out.println(p.getFileName());
                System.out.println(targetDirectory.resolve(p.getFileName()));
            }
        }

        //второй варик. отличие в том, что он проходится и по всем внутренним дирректориям,а первый только по самой дирректории
        Files.walk(sourceDirectory).forEach(path -> {
            try (DirectoryStream<Path> files = Files.newDirectoryStream(sourceDirectory)){
                if (Files.isRegularFile(path)) {
                    Path name1 = path.getFileName(); // получаем имя файла или дирректории
                    Files.copy(path, targetDirectory.resolve(name1)); // добавляем к пути второй дирректории имя файла и копируем по новому пути
                }
            } catch (IOException e) {
                e.printStackTrace();
            }
        });

        //копирование директории со всеми находящимися в ней файлами и директориями в другую директорию
        Files.walk(sourceDirectory).forEach(path -> { // проходимся итератором по всем внутренним директориям и получаем каждый путь по отдельности
            try (DirectoryStream<Path> files = Files.newDirectoryStream(sourceDirectory)){

                if (Files.isDirectory(path)){ // если директория
                    Path relative = sourceDirectory.relativize(path);
                    // извлекаем разницу между путями заданной дирректории и дирректории находящейся в ней "example1"
                    Path resolve = targetDirectory.resolve(relative);
                    // добавляем нужный путь к папке назначения "example1" и получаем "...example2\example1"
                    if (Files.notExists(resolve)){ //если такой дирректории не существует, создаем ее путем копирования
                        Files.copy(path, resolve);
                    }
                }
                if (Files.isRegularFile(path)){ //если файл
                    Path relative = sourceDirectory.relativize(path); //извлекаем разницу
                    Path resolve = targetDirectory.resolve(relative); // добавляем к пути назначения в конец
                    /*if (Files.notExists(resolve)){ //так как директория там уже существует, копируем файл если он еще не существует
                        Files.copy(path, resolve);
                    }*/
                    Files.copy(path, resolve);
                }
            } catch (IOException e) {
                e.printStackTrace();
            }
        });

        // Добавить все временные зоны без прохождения итератором в коллекцию SET
        TreeSet<String> zoneSet = new TreeSet<String>(ZoneId.getAvailableZoneIds());

        // В задаче task1802 используется Comparator с помощью интерфейса для сортировки массива без использования лямбда выражений
        Comparator<String> comparator = (String obj1, String obj2) ->
        {
            return obj1.length() - obj2.length();
        };
        /* У компилятора не возникнет проблем с определением метода, т.к. лямбда-выражение можно писать только для интерфейсов,
        у которых метод один. Впрочем есть способ обойти это правило, но об этом вы узнаете, когда начнете
        изучать ООП активнее (мы говорим о default-методах).*/

        var numbers = new ArrayList<Integer>();

        Collections.addAll(numbers, 123, -6, 12, 0, 44, 5678, -350);

        // Вместо этого
        Comparator<Integer> comparator1 = new Comparator<Integer>() {
            @Override
            public int compare(Integer i1, Integer i2) {
                return i1 - i2;
            }
        };
        Collections.sort(numbers, comparator1);

        // Используем сразу лямбда выражение
        Collections.sort(numbers, (i1,i2) -> i1 - i2 );

        //вместо этого
        numbers.forEach(new Consumer<Integer>() {
                            @Override
                            public void accept(Integer integer) {
                                System.out.println(integer);
                            }
                        }
        );

        // используем лямбда
        numbers.forEach( s -> System.out.println(s) );

        // а если еще короче то
        numbers.forEach(System.out::println);

        // Статейка про Java list to array
        // https://javarush.ru/groups/posts/2863-java-list-to-array-preobrazuem-spisok-ehlementov-v-massiv

        var strings1 = new ArrayList<String>();
        Collections.addAll(strings1, "Ты", "ж", "программист");
        String[] arr5 = strings1.toArray(new String[0]); // способ перевести list в массив
        String[] arr6 = strings1.toArray(String[]::new); // это то же самое

        // поиск строки максимальной длины с помощью потока
        String max = strings1.stream().max((s1, s2)-> s1.length()-s2.length()).get();

        // по поводу сортировки элементов массива через поток .stream() и с помощью метода .compareTo
        // задачи с task1812
        // https://metanit.com/java/tutorial/10.8.php
        // https://javarush.ru/groups/posts/1885-metod-compareto

        numbers.stream().sorted((a1,b1) -> a1.compareTo(b1)); // сортирует массив и располагает элементы в порядке возрастания или убывания

        // cars.stream().filter(x -> x.isElectric());  фильтрует эдементы в массиве и возвращает поток с только отфильтрованными элементами

        // words.stream().distinct(); убирает повторяющиеся элементы

        // accounts.stream().map(Account::getEmail); изменяет массив и возвращает только нужные элементы

        Stream<Integer> stream1 = Stream.of(10, -22, 3, 12, -85, 0, 142);

        stream1.filter(x2 -> x2%2 == 0).anyMatch(x2 -> x2 < 0); // находим есть ли хоть одно отрицательное четное число в потоке
        stream1.anyMatch(x2 -> (x2 < 0) && (x2%2 == 0)); // или вот так

        // task1819
        // Служебный класс Optioanal для сравнения двух объектов учитывая что один из них может быть null что бы не словить NullPointExeption
        // Статья про Optioanal https://habr.com/ru/post/225641/

        List<String> strings2 = new ArrayList<>();
        Collections.addAll(strings2, "first", "second", null, "fourth", "fifth");
        String text = "Этот элемент равен null";
        strings2.forEach(s -> {
            Optional<String> str = Optional.ofNullable(s);
            System.out.println(str.orElse(text)); //если строка равна null то выводим текст
        });

        // Про stream().min() stream().max() и т.д.
        // https://metanit.com/java/tutorial/10.11.php

        // task1820
        // return cars.filter(x -> x.getPrice()>mostExpensiveCar.getPrice()).findFirst();
        // сначала фильтруем, сравниваем элементы массива с переданным значением, а потом выводим первый подходящий

        var numbers3 = Stream.of(-1, 10, 43, 0, -32, -4, 22);
        numbers3.filter(x3 -> x3>0).collect(Collectors.toList()); // фильируем значения больше нуля и передаем их в список, на выходе ArrayList

        var stringStream = Stream.of("JavaRush", "CodeGym", "Amigo", "Elly", "Kim", "Risha");
        stringStream.collect(Collectors.toMap(c1 -> c1, c1 -> c1.length()));
        // засовываем элементы потока в мапу с ключом и значением в виде числа символов

        // Интерфейсы
        System.out.println(Hobby.HOBBY.toString()); // вот так можно обратиться к методу через интерфейс
        System.out.println(new Hobby().toString()); // а вот так, создавая экземпляр класса

        // В абстрактном классе не обязательно реализовывать метод имплементированного интерфейса

    }

    interface Dream {
        public static Hobby HOBBY = new Hobby(); // статическая кконстанта что бы можно было обратиться из статического класса Hobby
    }

    static class Hobby implements Dream {
        static int INDEX = 1;

        @Override
        public String toString() { // не статический, что бы можно было обратиться из экземпляра
            INDEX++;
            return "" + INDEX;
        }
    }
}


