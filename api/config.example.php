<?php
// ────────────────────────────────────────────────────────────
//  PLN backend konfiguratsiyasi — NAMUNA
//  Bu faylni "config.php" nomi bilan nusxalang va o'z ma'lumotlaringiz
//  bilan to'ldiring. config.php git'ga yuklanmaydi (maxfiy).
//
//  cp config.example.php config.php
// ────────────────────────────────────────────────────────────
return [
  // Timeweb MySQL ma'lumotlari (boshqaruv panelidan "Базы данных" bo'limidan oling)
  'db' => [
    'host' => 'localhost',          // Timeweb'da odatda 'localhost'
    'name' => 'cq55659_pln',        // bazangiz nomi
    'user' => 'cq55659_pln',        // bazaga kirish login
    'pass' => 'BAZA_PAROLI',        // bazaga kirish paroli
    'charset' => 'utf8mb4',
  ],

  // Anthropic (Claude) API kaliti — AI chat uchun.
  // Bo'sh qoldirilsa, AI soxta (kalit so'z) javoblar beradi.
  // Kalit: https://console.anthropic.com/ → API Keys
  'anthropic_api_key' => '',
  'anthropic_model'   => 'claude-sonnet-4-6',

  // Token amal qilish muddati (kun)
  'token_days' => 30,

  // CORS — frontend boshqa domende bo'lsa shu yerga yozing.
  // Frontend va backend bir domenda bo'lsa (tavsiya), '' qoldiring.
  'cors_origin' => '',
];
