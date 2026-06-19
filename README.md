# ⚙️ Mix&Match - API

This is the backend for **Mix&Match**, a music compatibility platform. It connects users based on their Spotify listening history, calculates compatibility scores between profiles, and generates AI-powered playlists based on mood.

Built with **Laravel 13**, following the **Service Pattern** and **Agent-based AI orchestration**.

## 🧠 Core Responsibilities

- **Music Matching**: Compares users' top artists and tracks to calculate a compatibility score and persist match history.
- **AI Playlist Generation**: Interfaces with Google Gemini to transform user moods into curated track lists.
- **Spotify Integration**: Manages OAuth2 flows, fetches top listening data, and handles playlist creation on the user's account.
- **Dashboard**: Exposes each user's top artists and tracks across different time ranges.
- **Security**: Bearer token validation via Laravel Sanctum and secure Spotify token refresh logic.

## 🛠️ Tech Stack

- **Framework**: [Laravel 13](https://laravel.com/)
- **Language**: PHP 8.2+
- **Auth**: Laravel Sanctum
- **AI Engine**: [Google Gemini API](https://ai.google.dev/)
- **Music Data**: [Spotify Web API](https://developer.spotify.com/documentation/web-api)
- **Cache**: Redis
- **Tools**: Composer, Laravel HTTP Client

## 🚀 Installation & Setup

1. **Clone the repository**:
    ```bash
    git clone https://github.com/michaelleoliveir/mixmatch-api.git
    cd mixmatch-api
    ```

2. **Install PHP dependencies**:
    ```bash
    composer install
    ```

3. **Configure environment variables**:
    ```bash
    cp .env.example .env
    ```
    Required keys in `.env`:
    - `SPOTIFY_CLIENT_ID` and `SPOTIFY_CLIENT_SECRET`: From Spotify Developer Dashboard.
    - `SPOTIFY_REDIRECT_URI`: Your OAuth callback URL.
    - `GEMINI_API_KEY`: From Google AI Studio.
    - `DB_*`: Database credentials.
    - `REDIS_*`: Redis connection (used for caching).

4. **Generate app key**:
    ```bash
    php artisan key:generate
    ```

5. **Run migrations**:
    ```bash
    php artisan migrate
    ```

6. **Start the server**:
    ```bash
    php artisan serve
    ```

## 📂 Key Architecture Components

### `App\Services\MatchService`
Core matching algorithm. Compares two users' top 10 artists and tracks (stored from Spotify) using array intersection, calculates a compatibility percentage, and returns the matching items with full metadata.

### `App\Services\SpotifyService`
Handles all Spotify API communication: OAuth token management (with proactive refresh), fetching top artists/tracks, searching track URIs, and creating playlists. Heavy responses are cached via Redis.

### `App\Services\GeminiService`
Interfaces with the Gemini AI agent to convert a mood string into a structured list of tracks. Includes error handling for 503 (high demand) responses and caches results per mood for 6 hours.

### `App\Ai\Agents\PlaylistCurator`
Prompt engineering layer. Defines the constraints and JSON schema the AI must follow to return data compatible with Spotify's track search.

## 📡 API Endpoints

### Authentication
| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/auth/spotify/login` | Redirects to Spotify OAuth | No |
| `GET` | `/api/auth/spotify/callback` | Handles callback, issues Sanctum token | No |
| `POST` | `/api/logout` | Revokes current access token | Yes |
| `GET` | `/api/validate-session` | Returns current user name and icon | Yes |

### Dashboard
| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/dashboard` | Returns user profile + top artists + top tracks. Accepts `?time_range=short_term\|medium_term\|long_term` | Yes |

### Match
| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/match` | Generates and returns the user's unique shareable match link | Yes |
| `GET` | `/api/match/{match_code}` | Calculates compatibility between the link owner and the current user, saves result | Yes |
| `GET` | `/api/match/ranking` | Returns all matches for the current user, ordered by score | Yes |
| `GET` | `/api/match/ranking/{match_id}` | Returns full details of a specific match (score, matching tracks and artists) | Yes |

### Playlist
| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/playlist/preview` | Generates a track list based on mood via Gemini AI. Accepts `?mood=` | Yes |
| `POST` | `/api/playlist/create` | Creates the playlist on the user's Spotify account | Yes |

## 👩‍💻 Author

**Michaelle Oliveira** - Fullstack Developer
- GitHub: [@michaelleoliveir](https://github.com/michaelleoliveir)
- Status: **Available for new opportunities and collaborations!**
