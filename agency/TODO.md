# DelegAI Agency — TODO

## ✅ Zrobione (20 maja 2026)

### Landing Page & Cennik
- [x] Ceny: Content 249 netto, Automation 499 netto, Max 800 netto (zamiast Full Stack 999)
- [x] Wszędzie dopisek "netto/m" przy cenach
- [x] Oszczędzasz 251/m przy Max vs osobno
- [x] Hero: "AI marketing i automatyzacja dla polskich firm"

### Rejestracja & Panel klienta
- [x] Formularz rejestracji (email + hasło + wybór planu)
- [x] auth.php — endpointy: register, login, status, plans
- [x] Przechowywanie: data/users.json, bcrypt hasła
- [x] Trial 7 dni dla wszystkich planów
- [x] Panel dashboard: Przegląd (statystyki, postęp), Ustawienia (imię, firma, hasło), Subskrypcja (zmiana planu), Widget (kod embed)

### Flow użytkownika
- [x] Demo → onboard (wybór branży/problemu) → 15 wiadomości czatu → blokada IP 2h → zachęta do rejestracji
- [x] Po rejestracji: pełny panel zamiast pustego popupu
- [x] Cennik po kliknięciu "Wybierz" → otwiera formularz kontaktowy z wybranym planem

### Projekty połączone
- [x] Projekt 3 (Content SEO) włączony do agency jako Content plan
- [x] Content + Automation + Full Stack w jednej ofercie

### Wizualne (CSS przez kimi)
- [x] Poprawione: karty cennika równej wysokości (align-items:stretch, flex na kartach, margin-top:auto na przyciskach)
- [x] Poprawione: ujednolicone cienie (zmienne CSS --sh, --sh-h)
- [x] Poprawione: "netto/m" czytelne (14px, font-weight 500)
- [x] Zweryfikowane wizualnie przez kimi — wszystkie sekcje spójne

### Runtime & Backend
- [x] Telegram webhook → long polling (stabilne, zero watchdoga)
- [x] launchd persistence dla delegai_server (przeżywa reboot)
- [x] Plik ~/Library/LaunchAgents/com.neos.delegai_server.plist

## ⏳ Czeka na Ciebie

### Priorytet 1 — Bez tego agency nie ruszy
- [ ] **SMTP** — żebym wysyłał maile: follow-up leadów, powiadomienia, odzyskiwanie porzuconych koszyków. Potrzebne: serwer SMTP lub SendGrid/Mailgun API key
- [ ] **KYC / Payment provider** — LemonSqueezy, Paddle lub Stripe. KYC na Ciebie, żeby klienci mogli płacić. Potrzebne: założenie konta i weryfikacja
- [ ] **DNS delegai.pl** — propagacja trwa. Jeśli już działa, podpiąć do InfinityFree jako addon domain

### Priorytet 2 — Gdy powyższe gotowe
- [ ] **Test full flow** — rejestracja → trial 7 dni → koniec triala → płatność → dostęp
- [ ] **SMTP konfiguracja** — podpiąć pod long polling (wysyłanie follow-up po kwalifikacji leada)
- [ ] **Content marketing** — pierwsze 5 artykułów SEO (technova.buzz/blog/)

### Priorytet 3 — Rozwój
- [ ] **n8n workflow** — automatyzacja onboardingu klienta po rejestracji
- [ ] **Trader Binance** — wpłata $20-50 na Binance, przełączenie z paper na live
- [ ] **Cold outreach** — 211 maili gotowych, czeka na SMTP
