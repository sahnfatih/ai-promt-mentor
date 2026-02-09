# GitHub'a Gönderme Adımları

## ✅ Tamamlanan Adımlar
- ✅ Git repository başlatıldı
- ✅ Dosyalar eklendi (git add .)
- ✅ İlk commit yapıldı

## 📋 Yapılacaklar

### 1. GitHub'da Repository Oluşturun
1. https://github.com adresine gidin
2. Sağ üstteki **"+"** → **"New repository"**
3. Repository adı: `ai-prompt-mentor` (veya istediğiniz isim)
4. **"Initialize this repository with a README"** seçeneğini **İŞARETLEMEYİN** (zaten README var)
5. **"Create repository"** tıklayın

### 2. Terminal'de Şu Komutları Çalıştırın

**PowerShell'de (Windows):**
```powershell
cd laravel-app

# GitHub repository URL'inizi buraya yazın (örnek aşağıda)
git remote add origin https://github.com/KULLANICI_ADI/REPO_ADI.git

# Branch adını main yapın (GitHub'ın varsayılanı)
git branch -M main

# GitHub'a gönderin
git push -u origin main
```

**Örnek:**
```powershell
git remote add origin https://github.com/sahnf/ai-prompt-mentor.git
git branch -M main
git push -u origin main
```

### 3. Kimlik Doğrulama

İlk push'ta GitHub kullanıcı adı ve şifre isteyebilir. Eğer 2FA aktifse:

**Seçenek 1: Personal Access Token (Önerilen)**
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. "Generate new token" → "repo" yetkisini seçin
3. Token'ı kopyalayın
4. Şifre yerine bu token'ı kullanın

**Seçenek 2: GitHub Desktop**
- GitHub Desktop uygulamasını kullanarak görsel arayüzle push edebilirsiniz

**Seçenek 3: SSH Key (Gelişmiş)**
- SSH key oluşturup GitHub'a ekleyerek şifre girmeden push edebilirsiniz

## 🔒 Önemli Notlar

### `.env` Dosyası Git'e Eklenmedi ✅
`.gitignore` dosyası `.env` dosyasını zaten hariç tutuyor, bu yüzden API key'leriniz GitHub'a gitmeyecek.

### İlk Push Sonrası
- GitHub repository sayfanızda tüm dosyalarınızı göreceksiniz
- README.md otomatik olarak ana sayfada görünecek
- `.env.example` dosyası var, kullanıcılar kendi `.env` dosyalarını oluşturabilir

## 📝 Sonraki Adımlar (Opsiyonel)

### GitHub Actions (CI/CD) Eklemek
```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: php artisan test
```

### GitHub Pages (Dokümantasyon)
- README.md zaten var
- Wiki özelliğini açabilirsiniz
- GitHub Pages ile dokümantasyon sitesi oluşturabilirsiniz

## 🆘 Sorun Giderme

### "remote origin already exists" Hatası
```powershell
git remote remove origin
git remote add origin https://github.com/KULLANICI_ADI/REPO_ADI.git
```

### "Permission denied" Hatası
- Personal Access Token kullanın
- SSH key kullanın
- GitHub Desktop kullanın

### "Branch 'main' does not exist" Hatası
```powershell
git branch -M main
```
