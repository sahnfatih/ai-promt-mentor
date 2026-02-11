# Proje Yapısını Değiştirme Rehberi

## 🎯 Amaç
`promptEngineer/laravel-app` yapısını `promptEngineer` olarak değiştirmek.

## ⚠️ ÖNEMLİ: Önce Yedek Alın!
1. Tüm projeyi yedekleyin (kopyalayın)
2. IDE'yi (Cursor/VS Code) kapatın
3. Laravel sunucusunu durdurun (`php artisan serve` çalışıyorsa)

## 📋 Adım Adım Talimatlar

### Yöntem 1: Manuel Taşıma (Önerilen)

1. **Cursor/VS Code'u kapatın**

2. **Windows Explorer'da:**
   - `C:\Users\sahnf\Desktop\promtEngineer\laravel-app` klasörüne gidin
   - **Ctrl+A** ile tüm dosyaları seçin
   - **Ctrl+X** ile kesin
   - Bir üst klasöre (`promtEngineer`) gidin
   - **Ctrl+V** ile yapıştırın

3. **`.git` klasörünü taşıyın:**
   - `laravel-app\.git` klasörünü bulun
   - `promtEngineer\.git` olarak taşıyın

4. **`laravel-app` klasörünü silin:**
   - Artık boş olan `laravel-app` klasörünü silin

### Yöntem 2: PowerShell ile (IDE kapalıyken)

PowerShell'i **Yönetici olarak** açın ve şu komutları çalıştırın:

```powershell
cd C:\Users\sahnf\Desktop\promtEngineer

# .git klasörünü taşı
Move-Item -Path "laravel-app\.git" -Destination ".git" -Force

# Tüm dosyaları taşı (git hariç)
Get-ChildItem -Path "laravel-app" -Force | 
    Where-Object { $_.Name -ne '.git' -and $_.Name -ne 'laravel-app' } | 
    Move-Item -Destination . -Force

# laravel-app klasörünü sil
Remove-Item -Path "laravel-app" -Recurse -Force
```

### Yöntem 3: Robocopy ile (En Güvenli)

PowerShell'i **Yönetici olarak** açın:

```powershell
cd C:\Users\sahnf\Desktop\promtEngineer

# .git klasörünü taşı
robocopy "laravel-app\.git" ".git" /E /MOVE

# Tüm dosyaları taşı
robocopy "laravel-app" "." /E /MOVE /XD .git laravel-app

# Boş klasörleri temizle
Remove-Item -Path "laravel-app" -Recurse -Force -ErrorAction SilentlyContinue
```

## ✅ Taşıma Sonrası Kontroller

1. **Git durumunu kontrol edin:**
```powershell
cd C:\Users\sahnf\Desktop\promtEngineer
git status
```

2. **Proje çalışıyor mu test edin:**
```powershell
php artisan --version
php artisan serve
```

3. **Dosyalar yerinde mi kontrol edin:**
- `app/` klasörü var mı?
- `public/` klasörü var mı?
- `composer.json` var mı?
- `.env` dosyası var mı?

## 🔧 Sorun Giderme

### "Permission denied" hatası
- PowerShell'i **Yönetici olarak** çalıştırın
- IDE'yi kapatın
- Laravel sunucusunu durdurun

### Git çalışmıyor
```powershell
# Git repository'yi yeniden başlat
git init
git add .
git commit -m "Restructure: Move files from laravel-app to root"
```

### Composer hatası
```powershell
composer install
```

## 📝 Notlar

- `.env` dosyası taşınacak ama Git'e eklenmeyecek (`.gitignore` sayesinde)
- `vendor/` klasörü taşınacak ama Git'te yok (`.gitignore` sayesinde)
- Tüm commit geçmişi korunacak (`.git` klasörü taşındığı için)
