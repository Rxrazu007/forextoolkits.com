# forextoolkits.com — ব্যাকআপ ইন্সট্রাকশন

## Last Updated: 2026-07-12

## 📁 Git-এ রাখা ফাইলসমূহ

| ফোল্ডার/ফাইল | বর্ণনা | Git Status |
|---|---|---|
| `forex-api/` | ⭐ কাস্টম ফরেক্স টুলস (HTML + PHP) | ✅ Git-এ |
| `.htaccess` | Apache রিরাইট রুলস | ✅ Git-এ |
| `wp-config-sample.php` | wp-config টেমপ্লেট (পাসওয়ার্ড ছাড়া) | ✅ Git-এ |
| `projects/` | প্রজেক্ট ডকুমেন্টেশন | ✅ Git-এ |

## ❌ Git-এ নেই (নিরাপত্তার জন্য)

| ফাইল | কেন বাদ |
|---|---|
| `wp-config.php` | DB পাসওয়ার্ড রয়েছে — লোকালি রাখা |
| `wp-admin/`, `wp-includes/` | WordPress core — WordPress.org থেকে পাওয়া যায় |
| `wp-content/uploads/` | মিডিয়া ফাইল — আলাদা ব্যাকআপ |
| `wp-content/cache/` | ক্যাশ — পুনরায় তৈরি হয় |

## 💾 ডাটাবেস ব্যাকআপ

প্রথম ব্যাকআপ নিতে cPanel → phpMyAdmin → Export → SQL ডাউনলোড করে রাখো।

নিয়মিত ব্যাকআপের জন্য **UpdraftPlus** প্লাগিন ইন্সটল করা আছে।

## 🚀 Auto-Deploy

GitHub Actions → FTP auto-deploy সক্রিয়। GitHub-এ commit করলে স্বয়ংক্রিয়ভাবে সার্ভারে আপডেট হবে।

## 📋 ইন্সটলড প্লাগিন

- kadence-blocks
- litespeed-cache
- updraftplus
- wpforms-lite
- site-offline
- duplicate-post

## 🎨 থিম

- Kadence v1.5.1 (স্ট্যান্ডার্ড — কোনো কাস্টম পরিবর্তন নেই)
