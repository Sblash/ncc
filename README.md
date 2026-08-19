# Nomi Cose Città - Laravel + Livewire

Un gioco online di "Nomi Cose Città" sviluppato con Laravel, Livewire, JWT Authentication e SQLite.

## 🎯 Caratteristiche

- **Autenticazione JWT** con Laravel Sanctum
- **Database SQLite** per persistenza dati
- **Frontend reattivo** con Livewire e Alpine.js
- **Sincronizzazione in tempo reale** tramite polling
- **Sistema di punteggio** completo con parole uniche, doppie e invalide
- **Sistema di votazione** per validare le parole
- **Classifiche e statistiche** utente
- **Test unit e feature** con SQLite in-memory

## 🏗️ Architettura

| Layer | Tecnologia | Responsabilità |
|-------|------------|----------------|
| Frontend | Livewire + Blade + Alpine.js | UI reattiva, form, timer, polling |
| Backend | Laravel (API REST) | Logica di gioco, autenticazione, database |
| Real-time | Polling (Livewire) | Aggiornamenti stato round/partita |
| Database | SQLite | Persistenza dati |
| Test | PHPUnit + SQLite in-memory | Test unit e feature |

## 📁 Struttura Progetto

```
nomi-cose-citta/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API REST (Auth, Game, Round, Word, Vote)
│   │   └── Livewire/             # Componenti frontend
│   │       ├── Auth/             # Login, Register
│   │       └── Game/             # ListGames, CreateGame, GameLobby, GameRound, GameVoting, GameResults, GameStats
│   ├── Models/                  # User, Game, Round, PlayerGame, Word, Vote, Category
│   ├── Services/                # ScoreCalculator, GameService
│   └── Console/                 # Comandi Artisan
├── database/
│   ├── migrations/              # Migrazioni SQLite
│   ├── seeders/                 # Dati di test
│   └── database.sqlite          # Database SQLite
├── resources/
│   ├── views/
│   │   └── livewire/             # Blade templates per Livewire
│   └── css/                     # Stili (app.css)
├── routes/
│   ├── api.php                  # Rotte API REST
│   ├── web.php                  # Rotte Livewire
│   └── console.php              # Comandi Artisan
├── tests/
│   ├── Feature/                 # Test feature (API, flusso utente)
│   └── Unit/                    # Test unit (logica, punteggi)
├── config/                      # Configurazioni Laravel
├── .env                         # Configurazione ambiente
├── composer.json
└── README.md
```

## 🚀 Installazione

### Prerequisiti

- PHP 8.1 o superiore
- Composer
- Node.js (opzionale, per asset compilation)
- SQLite

### Passaggi

1. **Clonare il repository**
```bash
cd /path/to/project
git clone https://github.com/Sblash/ncc.git
cd ncc
```

2. **Installare le dipendenze**
```bash
composer install
npm install  # Opzionale, se vuoi compilare gli asset
```

3. **Configurare l'ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Creare il database SQLite**
```bash
mkdir -p database
touch database/database.sqlite
chmod 775 database/database.sqlite
```

5. **Eseguire le migrazioni**
```bash
php artisan migrate
```

6. **Popolare il database (opzionale)**
```bash
php artisan db:seed
```

7. **Avviare il server di sviluppo**
```bash
php artisan serve
```

8. **Configurare il cron job per lo scheduler**
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 🎮 Come Giocare

1. **Registrati** o **accedi** al tuo account
2. **Crea una nuova partita** o **unisciti** a una partita esistente
3. **Attendi** che il creatore avvii la partita
4. **Inserisci le parole** per ogni categoria quando è il tuo turno
5. **Vota le parole** degli altri giocatori
6. **Vedi i risultati** e la classifica finale

## 🔌 API REST

### Autenticazione
- `POST /api/register` - Registrazione utente
- `POST /api/login` - Login (ritorna JWT)
- `POST /api/logout` - Logout (invalida token)
- `GET /api/user` - Dati utente autenticato

### Partite
- `GET /api/games` - Lista partite (filtri: status, my_games)
- `POST /api/games` - Crea partita
- `GET /api/games/{game}` - Dettagli partita
- `POST /api/games/{game}/join` - Unisciti a partita
- `POST /api/games/{game}/leave` - Abbandona partita
- `POST /api/games/{game}/start` - Avvia partita (solo creatore)

### Round
- `GET /api/games/{game}/rounds` - Lista round di una partita
- `GET /api/rounds/{round}` - Dettagli round
- `POST /rounds/{round}/start` - Avvia round
- `POST /rounds/{round}/end` - Termina round
- `POST /rounds/{round}/complete` - Completa round

### Parole
- `POST /api/rounds/{round}/words` - Inserisci parola
- `GET /api/rounds/{round}/words` - Lista parole di un round
- `PUT /api/words/{word}` - Modifica parola
- `DELETE /api/words/{word}` - Elimina parola

### Votazioni
- `POST /api/words/{word}/vote` - Vota una parola
- `GET /api/words/{word}/votes` - Lista voti di una parola

### Statistiche
- `GET /api/users/{user}/stats` - Statistiche utente
- `GET /api/stats/leaderboard` - Classifica globale

## 🧪 Test

### Eseguire i test
```bash
php artisan test
```

### Tipologie di test
- **Unit Test**: Logica pura (calcolo punteggi, validazione parole)
- **Feature Test**: Endpoint API, flusso utente, autenticazione

## ⚙️ Configurazione

### Variabili d'ambiente
```env
APP_NAME=NomiCoseCitta
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database/database.sqlite

SANCTUM_STATEFUL_DOMAINS=localhost
SANCTUM_TOKEN_EXPIRATION=1440
```

## 📊 Regole del Gioco

### Punteggio
- **Parola unica**: +10 punti
- **Parola doppia**: +5 punti
- **Parola sbagliata**: -15 punti

### Validazione Parole
- Deve iniziare con la lettera del round
- Deve appartenere alla categoria corretta
- Deve essere una parola valida (solo lettere, minimo 2 caratteri)

### Votazione
- Ogni giocatore vota le parole degli altri
- La validità è determinata dalla maggioranza
- Non si può votare la propria parola

## 🎯 Decisioni Chiave

| Scelta | Motivazione |
|--------|-------------|
| **Polling vs WebSocket** | Polling è più semplice, non richiede infrastruttura (Redis/Pusher), Livewire lo supporta nativamente |
| **JWT vs Session** | JWT è stateless, ideale per API REST e separazione frontend/backend |
| **SQLite** | Semplice da configurare, perfetto per sviluppo e test |
| **Livewire** | Combina frontend reattivo e backend in PHP, senza bisogno di Vue/React |
| **Scheduler Laravel** | Garantisce sincronizzazione round anche se i client non sono connessi |

## 🤝 Contributing

1. Fork del repository
2. Creare un branch per la feature (`git checkout -b feature/nome-feature`)
3. Commit delle modifiche (`git commit -m 'Aggiunta nuova feature'`)
4. Push del branch (`git push origin feature/nome-feature`)
5. Aprire una Pull Request

## 📄 Licenza

MIT License

## 🙏 Ringraziamenti

- Laravel
- Livewire
- Sanctum
- Tailwind CSS
- Alpine.js
