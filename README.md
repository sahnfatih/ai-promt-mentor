# AI Prompt Mentor & Generator

Modern, interaktif bir AI prompt mentor ve generator uygulaması. Gemini API kullanarak kullanıcıların basit fikirlerini profesyonel, teknik detaylı prompt'lara dönüştürür.

## 🚀 Özellikler

- **Interaktif Prompt Oluşturma**: Gemini API ile adım adım prompt geliştirme
- **Realism Meter**: Teknik terim yoğunluğuna göre dinamik gerçekçilik puanı
- **Negative Prompt Desteği**: Görselde istenmeyen öğeleri belirleme
- **Kullanıcı Yönetimi**: Laravel tabanlı çok kullanıcılı sistem
- **Chat Geçmişi**: ChatGPT/Gemini tarzı sohbet yönetimi
- **Admin Paneli**: Kapsamlı yönetim ve analitik paneli
- **Google OAuth**: Google hesabı ile hızlı giriş
- **Preset Templates**: Hızlı başlangıç şablonları
- **Prompt Varyasyonları**: Tek tıkla 3 farklı varyasyon üretme

## 📋 Gereksinimler

- PHP 8.2+
- Composer
- Node.js & NPM (opsiyonel, Tailwind için)
- MySQL/PostgreSQL/SQLite
- Gemini API Key

## 🔧 Kurulum

1. **Repository'yi klonlayın:**
```bash
git clone https://github.com/KULLANICI_ADI/REPO_ADI.git
cd REPO_ADI
```

2. **Bağımlılıkları yükleyin:**
```bash
composer install
npm install  # Opsiyonel
```

3. **Environment dosyasını oluşturun:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **`.env` dosyasını düzenleyin:**
```env
APP_NAME="AI Prompt Mentor"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prompt_mentor
DB_USERNAME=root
DB_PASSWORD=

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

GEMINI_API_KEY=your_gemini_api_key
```

5. **Veritabanını oluşturun ve migrate edin:**
```bash
php artisan migrate
```

6. **Storage linkini oluşturun:**
```bash
php artisan storage:link
```

7. **Sunucuyu başlatın:**
```bash
php artisan serve
```

## 🔑 API Anahtarları

### Gemini API Key
1. [Google AI Studio](https://makersuite.google.com/app/apikey) adresine gidin
2. Yeni bir API key oluşturun
3. `.env` dosyasına `GEMINI_API_KEY` olarak ekleyin

### Google OAuth (Opsiyonel)
1. [Google Cloud Console](https://console.cloud.google.com/) → APIs & Services → Credentials
2. OAuth 2.0 Client ID oluşturun
3. Redirect URI: `http://localhost:8000/auth/google/callback`
4. Client ID ve Secret'ı `.env` dosyasına ekleyin

## 👤 Varsayılan Admin Kullanıcı

Migration sonrası otomatik oluşturulur:
- **Email:** `admin@promptmentor.test`
- **Şifre:** `password`

⚠️ **Production'da mutlaka şifreyi değiştirin!**

## 📁 Proje Yapısı

```
laravel-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/ChatApiController.php    # Gemini API entegrasyonu
│   │   ├── AdminController.php          # Admin paneli
│   │   ├── ChatController.php           # Sohbet yönetimi
│   │   └── AuthController.php           # Kimlik doğrulama
│   └── Models/
│       ├── User.php
│       ├── Chat.php
│       └── Message.php
├── resources/views/
│   ├── dashboard.blade.php              # Ana kullanıcı arayüzü
│   └── admin/                           # Admin paneli görünümleri
├── public/js/
│   └── prompt-mentor.js                 # Frontend JavaScript
└── routes/
    └── web.php                           # Route tanımları
```

## 🎯 Kullanım

1. Kayıt olun veya giriş yapın
2. Yeni bir sohbet oluşturun
3. İlk fikrinizi yazın (örn: "ormanda bir aslan")
4. Gemini'nin sorularını yanıtlayarak prompt'unuzu geliştirin
5. Final prompt'u kopyalayıp Midjourney/DALL-E'de kullanın

## 🛠️ Teknolojiler

- **Backend:** Laravel 12
- **Frontend:** Vanilla JavaScript, Tailwind CSS
- **AI:** Google Gemini 2.5 Flash
- **Database:** MySQL/PostgreSQL/SQLite
- **Auth:** Laravel Breeze + Google OAuth

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 🤝 Katkıda Bulunma

Pull request'ler memnuniyetle karşılanır. Büyük değişiklikler için lütfen önce bir issue açarak neyi değiştirmek istediğinizi tartışın.

## 📧 İletişim

Sorularınız için issue açabilirsiniz.
