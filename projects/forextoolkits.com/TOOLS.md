# forextoolkits.com — টুলস & API

## ⭐ কাস্টম ফরেক্স টুলস (`/forex-api/`)

সাইটের মূল বৈশিষ্ট্য — WordPress-এর বাইরে আলাদা `/forex-api/` ফোল্ডারে রাখা।

| টুল | ফাইল | বর্ণনা |
|---|---|---|
| 🔴 Currency Strength Bars | `Currency-Strength-Bars.html` | স্ট্যান্ডঅলোন HTML টুল |
| 🔴 Forex Volatility Compression Scanner | `Forex-Volatility-Compression-Scanner.html` | ভোলাটিলিটি কম্প্রেশন এনালাইসিস |
| 🔴 Home Heatmap | `Home-Heatmap.html` | মার্কেট হিটম্যাপ |
| 🔴 Supertrend Live | `supertrend-live.html` | লাইভ সুপারট্রেন্ড সিগন্যাল |

## API ব্যাকএন্ড

| ফাইল | ফাংশন |
|---|---|
| `receiver.php` | লাইভ ডাটা রিসিভ/প্রসেস করার PHP API |
| `forex_data.json` | JSON ফরম্যাটে ফরেক্স ডাটা |
| `health_status.json` | API হেলথ চেক |
| `receiver_log.txt` | API কলের লগ |

## WordPress REST API

- **Endpoint:** `https://forextoolkits.com/wp-json/wp/v2/`
- **Auth:** Application Password (Agent@Zim) — ✅
- **পোস্ট তৈরি:** `POST /wp/v2/posts`
- **মিডিয়া আপলোড:** `POST /wp/v2/media`
- **ক্যাটাগরি:** শুধু "Uncategorized" — নতুন বানাতে হবে

## ডাটাবেস

- **Name:** `foresocz_wp875`
- **Prefix:** `wpni_`
- **Host:** localhost

## ফাইল আর্কিটেকচার

```
/ (WordPress Root)
├── wp-content/
│   ├── themes/kadence/
│   ├── plugins/ (kadence-blocks, litespeed-cache, updraftplus, wpforms-lite, site-offline, duplicate-post)
│   └── uploads/
├── forex-api/ ← ⭐ কাস্টম ফরেক্স টুলস
│   ├── Currency-Strength-Bars.html
│   ├── Forex-Volatility-Compression-Scanner.html
│   ├── Home-Heatmap.html
│   ├── supertrend-live.html
│   ├── receiver.php
│   └── *.json, *.txt
├── wp-config.php
└── .htaccess
```