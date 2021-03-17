<?php

namespace dbmigrations\libs;

use Faker\Generator;

/**
 * Class Utils
 * @author Adeyemi Olaoye <yemi@cottacush.com>
 * @package dbmigrations\libs
 */
class Utils
{
    /**
     * Reads a CSV file
     * @credit http://www.codedevelopr.com/articles/reading-csv-files-into-php-array/
     * @param $csvFile
     * @return array
     */
    public static function readCSV($csvFile): array
    {
        ini_set('auto_detect_line_endings', true);
        $line_of_text = [];
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 1024);
        }
        fclose($file_handle);
        return $line_of_text;
    }


    /**
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param Generator $faker
     * @return string
     */
    public static function getRandomNigerianPhoneNumber(Generator $faker): string
    {
        return '+234' .
            $faker->numberBetween(7, 9) .
            $faker->numberBetween(0, 1) .
            $faker->numberBetween(10000000, 99999999);
    }

    /**
     * @author Olawale Lawal <wale@cottacush.com>
     * @author Adeyemi Olaoye <yemi@cottacush.com>
     * @param int $length
     * @param Generator $faker
     * @return array
     */
    public static function getUniqueNames(int $length, Generator $faker): array
    {
        $names = [];

        while (count($names) <= $length) {
            $names[] = $faker->colorName;
            $names = array_unique($names);
        }

        return array_values($names);
    }
}
