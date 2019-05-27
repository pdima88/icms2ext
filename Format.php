<?php
namespace pdima88\icms2ext;

class Format {

    public static $durationStr = [
        'years' => '%d год|%d года|%d лет',
        'months' => '%d месяц|%d месяца|%d месяцев',
        'days' => '%d день|%d дня|%d дней',
    ];

    static function formatDuration($duration) {
        $d = $duration;
        if ($d == 0) {
            return '';
        } elseif ($d >= 365 && $d % 365 <= $d / 365) {
            $years = (int) $d / 365;
            return self::numCase(self::$durationStr['years'], $years);
        } elseif ($d >= 30 && $d % 30 <= (int) ($d / 30)) {
            $months = (int) ($d / 30);
            return self::numCase(self::$durationStr['months'], $months);
        } else {
            return self::numCase(self::$durationStr['days'], $d);
        }
    }

    static function numCase($s, $num) {
        $format = explode('|', $s);
        if (count($format) > 2) {
            return self::caseRu($num, $s);
        } elseif (count($format) == 2 && $num > 1) {
            return sprintf($format[1]);
        } else {
            return sprintf($format[0]);
        }
    }

    static function caseRu($number, $format) {
        if (is_string($format)) $format = explode('|', $format);
        if ($number == 0 && isset($format[3])) return sprintf($format[3], $number);

        if ((int)$number != $number) {
            $f = $format[1];
        } else {
            $num1 = $number % 100;
            if ($num1 >= 11 && $num1 <= 19) {
                $f = $format[2];
            } else {
                $i = $number % 10;
                switch ($i) {
                    case (1):
                        $f = $format[0];
                        break;
                    case (2):
                    case (3):
                    case (4):
                        $f = $format[1];
                        break;
                    default:
                        $f = $format[2];
                }
            }
        }
        return sprintf($f, $number);
    }

    /**
     * Функция возвращает окончание для множественного числа слова на основании числа и массива окончаний
     * param  $number Integer Число на основе которого нужно сформировать окончание
     * param  $endingsArray  Array Массив слов или окончаний для чисел (1, 4, 5),
     *         например array('яблоко', 'яблока', 'яблок')
     * return String
     */
    static function getNumWithEnding($number, $endingArray)
    {
        if ($number == 0 && isset($endingArray[3])) return $endingArray[3];
        return $number .' '. self::getNumEnding($number, $endingArray);
    }

    static function getNumEnding($number, $endingArray) {
        if (is_string($endingArray)) $endingArray = explode('|', $endingArray);
        if ($number == 0 && isset($endingArray[3])) return $endingArray[3];

        if ((int)$number != $number) {
            $ending = $endingArray[1];
        } else {
            $num1 = $number % 100;
            if ($num1 >= 11 && $num1 <= 19) {
                $ending = $endingArray[2];
            } else {
                $i = $number % 10;
                switch ($i) {
                    case (1):
                        $ending = $endingArray[0];
                        break;
                    case (2):
                    case (3):
                    case (4):
                        $ending = $endingArray[1];
                        break;
                    default:
                        $ending = $endingArray[2];
                }
            }
        }
        return $ending;
    }

    static function currencyToWordsRu($n, $stripkop=false) {
        $nol = 'ноль';
        $str[100]= array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот', 'восемьсот','девятьсот');
        $str[11] = array('','десять','одиннадцать','двенадцать','тринадцать', 'четырнадцать','пятнадцать','шестнадцать','семнадцать', 'восемнадцать','девятнадцать','двадцать');
        $str[10] = array('','десять','двадцать','тридцать','сорок','пятьдесят', 'шестьдесят','семьдесят','восемьдесят','девяносто');
        $gs = array(
            array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),// m
            array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять') // f
        );
        $forms = array(
            array('тийин', 'тийина', 'тийин', 0), // 10^-2
            array('сум', 'сума', 'сум',  0), // 10^ 0
            array('тысяча', 'тысячи', 'тысяч', 1), // 10^ 3
            array('миллион', 'миллиона', 'миллионов',  0), // 10^ 6
            array('миллиард', 'миллиарда', 'миллиардов',  0), // 10^ 9
            array('триллион', 'триллиона', 'триллионов',  0), // 10^12
        );
        $out = $tmp = array();
        // Поехали!
        $tmp = explode('.', str_replace(',','.', $n));
        $rub = number_format($tmp[ 0], 0,'','-');
        if ($rub== 0) $out[] = $nol;
        // нормализация копеек
        $kop = isset($tmp[1]) ? substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT), 0,2) : '00';
        $segments = explode('-', $rub);
        $offset = sizeof($segments);
        if ((int)$rub== 0) { // если 0 рублей
            $o[] = $nol;
            $o[] = self::getNumEnding( 0, array_slice($forms[1],0 ,3));
        }
        else {
            foreach ($segments as $k=>$lev) {
                $g= (int) $forms[$offset][3]; // определяем род
                $ri = (int) $lev; // текущий сегмент
                if ($ri== 0 && $offset>1) {// если сегмент==0 & не последний уровень(там Units)
                    $offset--;
                    continue;
                }
                // нормализация
                $ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
                // получаем циферки для анализа
                $r1 = (int)substr($ri, 0,1); //первая цифра
                $r2 = (int)substr($ri,1,1); //вторая
                $r3 = (int)substr($ri,2,1); //третья
                $r22= (int)$r2.$r3; //вторая и третья
                // разгребаем порядки
                if ($ri>99) $o[] = $str[100][$r1]; // Сотни
                if ($r22>20) {// >20
                    $o[] = $str[10][$r2];
                    $o[] = $gs[ $g ][$r3];
                }
                else { // <=20
                    if ($r22>9) $o[] = $str[11][$r22-9]; // 10-20
                    elseif($r22> 0) $o[] = $gs[ $g ][$r3]; // 1-9
                }
                // Рубли
                $o[] = self::getNumEnding($ri, array_slice($forms[$offset], 0, 3));
                $offset--;
            }
        }
        // Копейки
        if (!$stripkop && $kop > 0) {
            $o[] = $kop;
            $o[] = self::getNumEnding($kop, array_slice($forms[ 0], 0, 3));
        }
        return preg_replace('/\s{2,}/',' ',implode(' ',$o));
    }

    static function currencyUzs($amount) {
        return number_format($amount, 0, ',', ' ');
    }
}