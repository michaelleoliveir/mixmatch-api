# ⚙️ Mix&Match - AI Orchestration API

This is the backend engine for **Mix&Match**, a smart playlist curator. It acts as a bridge between the Google Gemini AI and the Spotify Web API, handling authentication, prompt engineering, and track synchronization.

Built with **Laravel 13**, this API follows modern software architecture patterns like **Service Pattern** and **Agent-based AI orchestration** to ensure scalability and clean code.

## 🧠 Core Responsibilities

-   **AI Agent Management**: Interfaces with Google Gemini to transform user moods into structured music data.
-   **Spotify Integration**: Manages OAuth2 flows and communicates with Spotify's REST API for track searching and playlist management.
-   **Security**: Implements Bearer token validation and secure environment handling.
-   **Error Handling**: Centralized management of third-party API spikes (503 errors) and rate limiting.

## 🛠️ Tech Stack

-   **Framework**: [Laravel 13](https://laravel.com/)
-   **Language**: PHP 8.2+
-   **AI Engine**: [Google Gemini API](https://ai.google.dev/)
-   **Music Data**: [Spotify Web API](https://developer.spotify.com/documentation/web-api)
-   **Tools**: Composer, Guzzle/HTTP Client (with retry mechanisms).

## 🚀 Installation & Setup

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/michaelleoliveir/mixmatch-api.git
    cd mixmatch-api
    ```

2.  **Install PHP dependencies**:
    ```bash
    composer install
    ```

3.  **Configure Environment Variables**:
    Copy the example file and fill in your credentials:
    ```bash
    cp .env.example .env
    ```
    Required keys in `.env`:
    - `GEMINI_API_KEY`: Your Google AI Studio key.
    - `SPOTIFY_CLIENT_ID`: From Spotify Developer Dashboard.
    - `SPOTIFY_CLIENT_SECRET`: From Spotify Developer Dashboard.

4.  **Generate App Key**:
    ```bash
    php artisan key:generate
    ```

5.  **Start the server**:
    ```bash
    php artisan serve
    ```

## 📂 Key Architecture Components

### `App\Services\GeminiService`
Handles the interaction with the Gemini AI Agent. It includes custom `try/catch` blocks to manage "High Demand" (503) errors from Google's servers, ensuring the frontend receives user-friendly feedback.

### `App\Ai\Agents\PlaylistCurator`
A specialized class responsible for prompt engineering. It defines the constraints and the JSON structure that the AI must return to maintain compatibility with Spotify track URIs.

### `App\Http\Controllers\PlaylistController`
The entry point for the frontend, orchestrating the flow between the user's mood input, the AI recommendations, and the final Spotify library update.

## 📡 API Endpoints (Quick Look)

### Authentication
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/auth/spotify/login` | Redirects user to Spotify OAuth | No |
| `GET` | `/api/auth/spotify/callback` | Handles Spotify response & issues Sanctum token | No |
| `POST` | `/api/logout` | Revokes the current access token | Yes (Sanctum) |

### Playlist Management
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/validate-session` | Returns current user name and icon | Yes (Sanctum) |
| `GET` | `/api/playlist/preview` | Generates tracks based on mood (via Gemini) | Yes (Sanctum) |
| `POST` | `/api/playlist/create` | Persists the playlist to the user's Spotify | Yes (Sanctum) |

## 👩‍💻 Author

**Michaelle Oliveira** - Fullstack Developer
-   GitHub: [@michaelleoliveir](https://github.com/michaelleoliveir)
-   Status: **Available for new opportunities and collaborations!**
