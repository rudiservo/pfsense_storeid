#!/usr/local/bin/php -q
<?php
/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Rudi Servo
 */

/*
This is a CLI application made for PfSense and Squid 3
the idea is to use the already installed php in pfsense to do
the storeid_helper.
has of PfSense 2.2.6 php is on version 5.5.30 and Squid 3.4

Altough php has a bad reputation for being a continuous running application
it has become more and more stable since version 5.5
now with version 7.0 it is not only stable has has many performance improvements
that surpass most comon scripting languages.
So there is no problem with php running this.

Usage you can call out the script with many rewrite files to it or folders containing
rewrite rules with .conf termination.
inside the file it must have a hard tab between the match rule and and internal squid resolve
 */

#include a small config file, for debug and just in case something else comes up
include 'conf/storeid.conf.php';

if ($_DEBUG) {
    file_put_contents($_LOG_FILE, 'Worker Spawn @'.date('Y-m-d H-i-s')."\n",  FILE_APPEND );
}

function addRules(&$rules, $filePath) {
    $file = fopen($filePath, 'r');
    while (($line = fgets($file)) !== false) {
        $read = preg_split('/\s+/', $line);
        $rules['/'.$read[0].'/']=$read[1];
    }
    fclose($file);
}

$rules = array();
$size = sizeof($argv);
for ($i = 1 ; $i < $size ; $i++) {
    if (is_dir($argv[$i])) {
        $path = $argv[$i];
        $files = scandir($path);
        foreach ($files as $file) {
            $p_info = pathinfo($file);
            if ($p_info['extension']=='conf') {
                addRules($rules, $path.'/'.$file);
            }
        }
    } else {
        addRules($rules, $argv[$i]);
    }
}

if (!empty($rules)) {
    $stdin = fopen('php://stdin', 'r');
    $i_url = null;
    while (false !== ($url = rtrim(fgets($stdin), "\n\r")) && $url!='quit') {
        $found = false;
        foreach ($rules as $rule => $target) {
            if (preg_match($rule, $url, $matches)) {
                $i_url = $target;
                for ($i = 1 ; $i < sizeof($matches); $i++) {
                    $i_url = "OK store-id=".preg_replace('/\$'.$i.'/',$matches[$i], $i_url)."\n";
                }
                $found = true;
                break;
            }
        }
        if (!$found) {
            $i_url = "ERR\n";
        }
        echo $i_url;
        if ($_DEBUG) {
            file_put_contents($_LOG_FILE, $i_url,  FILE_APPEND );
        }
    }
    fclose($stdin);
    if ($_DEBUG) {
        file_put_contents($_LOG_FILE, 'Worker Closed @ '.date('Y-m-d H-i-s')."\n",  FILE_APPEND );
    }
}
