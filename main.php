#!/usr/bin/env php
<?php

// --- [ KONFIGURASI TAMPILAN ] ---
$red = "\033[91m"; $green = "\033[92m"; $yellow = "\033[93m";
$cyan = "\033[96m"; $white = "\033[97m"; $reset = "\033[0m";

$banner = "
$cyan __      __.___         ___________.___ 
$cyan/  \    /  \   |        \_   _____/|   |
$cyan\   \/\/   /   |  ______ |    __)  |   |
$cyan \        /|   | /_____/ |     \   |   |
$cyan  \__/\  / |___|         \___  /   |___|
$cyan       \/                    \/$reset
\n";
// --- [ AKHIR KONFIGURASI ] ---

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

while (true) {
    system('clear'); echo $banner;
    echo $yellow . "==================================================\n";
    echo "       MENU UTAMA APLIKASI     \n";
    echo "==================================================\n" . $reset;
    echo $cyan . " 1. " . $white . "Input Sumber Karakter " . $yellow . "(source.json)\n";
    echo $cyan . " 2. " . $white . "Generate 10 Password " . $yellow . "(password.json)\n";
    echo $cyan . " 3. " . $white . "Jalankan Login Otomatis " . $yellow . "(skrip terpisah)\n";
    echo $cyan . " 4. " . $white . "Keluar\n" . $reset;
    echo $yellow . "==================================================\n" . $reset;
    echo $white . "Masukkan pilihan Anda: " . $reset;
    $pilihan = trim(fgets(STDIN));

    switch ($pilihan) {
        case '1':
            system('clear'); echo $banner;
            $sourceFile = 'source.json';
            while (true) { echo $white."-> Masukkan Huruf Besar: ".$reset; $hurufBesar = trim(fgets(STDIN)); if (empty($hurufBesar) || !ctype_alpha($hurufBesar) || $hurufBesar !== strtoupper($hurufBesar)) { echo $red."   ❗️ Gagal: Harap masukkan HURUF BESAR semua.\n".$reset; continue; } break; }
            while (true) { echo $white."-> Masukkan Angka: ".$reset; $angka = trim(fgets(STDIN)); if (empty($angka) || !is_numeric($angka)) { echo $red."   ❗️ Gagal: Harap masukkan ANGKA saja.\n".$reset; continue; } break; }
            while (true) { echo $white."-> Masukkan Huruf Kecil: ".$reset; $hurufKecil = trim(fgets(STDIN)); if (empty($hurufKecil) || !ctype_alpha($hurufKecil) || $hurufKecil !== strtolower($hurufKecil)) { echo $red."   ❗️ Gagal: Harap masukkan huruf kecil semua.\n".$reset; continue; } break; }
            $data = [ 'timestamp' => date('Y-m-d H:i:s'), 'detail_input' => [ 'huruf' => $hurufBesar, 'angka' => $angka, 'huruf_kecil' => $hurufKecil ]];
            file_put_contents($sourceFile, json_encode($data, JSON_PRETTY_PRINT));
            echo $green."\n✅ Sukses! Sumber karakter telah disimpan ke '$sourceFile'.\n\n".$reset;
            echo $white."(Tekan Enter untuk kembali ke menu)".$reset; fgets(STDIN);
            break;
        case '2':
            $sourceFile = 'source.json'; $outputFile = 'password.json'; $lastChoiceFile = 'last_choice.json';
            if (!file_exists($sourceFile) || filesize($sourceFile) == 0) { echo $red."\n❗️ File '$sourceFile' kosong. Jalankan Menu 1.\n\n".$reset; sleep(2); continue 2; }
            $jsonString = file_get_contents($sourceFile); $dataSumber = json_decode($jsonString, true);
            $sumberHurufBesar = $dataSumber['detail_input']['huruf']; $sumberAngka = $dataSumber['detail_input']['angka']; $sumberHurufKecil = $dataSumber['detail_input']['huruf_kecil'];
            while (true) {
                system('clear'); echo $banner;
                echo $yellow."----------------------------------------\n";
                echo "          SUBMENU: GENERATE PASSWORD\n";
                echo "----------------------------------------\n".$reset;
                echo $cyan." 1.".$white." Format (2 Huruf Besar, 2 Angka)\n"; echo $cyan." 2.".$white." Format (3 Huruf Besar, 2 Angka)\n"; echo $cyan." 3.".$white." Format (3 Huruf Besar, 3 Angka)\n";
                echo $cyan." 4.".$white." Format (4 Huruf Besar, 3 Angka)\n"; echo $cyan." 5.".$white." Format (4 Huruf Kecil)\n"; echo $cyan." 6.".$white." Format (5 Huruf Kecil)\n";
                echo " 7.".$white." Format (4 Angka)\n"; echo " 8.".$white." Format (5 Angka)\n"; echo " 9.".$white." Format (6 Angka)\n"; echo $cyan." 10.".$white." Kembali ke Menu Utama\n".$reset;
                echo $yellow."----------------------------------------\n".$reset;
                echo $white."Pilih format password: ".$reset;
                $pilihanSubMenu = trim(fgets(STDIN)); $params = null;
                switch ($pilihanSubMenu) {
                    case '1': $params = [2, 0, 2]; break; case '2': $params = [3, 0, 2]; break; case '3': $params = [3, 0, 3]; break; case '4': $params = [4, 0, 3]; break;
                    case '5': $params = [0, 4, 0]; break; case '6': $params = [0, 5, 0]; break; case '7': $params = [0, 0, 4]; break; case '8': $params = [0, 0, 5]; break;
                    case '9': $params = [0, 0, 6]; break; case '10': break 2; default: echo $red."\n   ❗️ Pilihan tidak valid.\n".$reset; sleep(1); continue 2;
                }
                if ($params) {
                    file_put_contents($lastChoiceFile, json_encode(['params' => $params]));
                    $passwords = []; for ($i = 0; $i < 10; $i++) { $passwords[] = generatePassword($params[0], $params[1], $params[2], $sumberHurufBesar, $sumberHurufKecil, $sumberAngka); }
                    $outputData = ['timestamp' => date('Y-m-d H:i:s'), 'generated_passwords' => $passwords];
                    file_put_contents($outputFile, json_encode($outputData, JSON_PRETTY_PRINT));
                    echo $green."\n✨ 10 password berhasil dibuat.\n".$reset;
                    echo $white."(Tekan Enter)".$reset; fgets(STDIN);
                }
            }
            break;
        case '3':
            system('clear'); echo $banner;
            echo $cyan."\n🚀 Menjalankan skrip login otomatis (login_script.php)...\n".$reset;
            echo $yellow."----------------------------------------------------------\n".$reset;
            passthru('php login.php');
            echo $yellow."----------------------------------------------------------\n".$reset;
            echo $green."✅ Skrip login selesai. Kembali ke menu utama.\n".$reset;
            echo $white."(Tekan Enter untuk melanjutkan)".$reset;
            fgets(STDIN);
            break;
        case '4':
            system('clear'); echo $banner;
            echo $cyan."\nTerima kasih! Keluar dari program.\n".$reset;
            exit;
        default:
            echo $red."\n-> Pilihan tidak valid! Silakan coba lagi.\n\n".$reset;
            sleep(1);
            break;
    }
}
