# Bot Automation App

A React application for managing automation tasks with user authentication and form submissions.

## Features
- User authentication system with role-based access (ADMIN, AVSEC, PK-PPK, BANGLAND, LISTRIK)
- Form management for action plans and submissions
- Bootstrap UI with responsive design
- Environment variable configuration for user credentials

## Tech Stack
- **Frontend**: React 18 with Vite
- **UI Framework**: Bootstrap 5.3.8
- **Styling**: Custom CSS + Bootstrap classes
- **State Management**: React hooks
- **Build Tool**: Vite with HMR
- **Linting**: ESLint

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   npm install
   ```
3. Copy environment variables:
   ```bash
   cp .env.example .env
   ```
4. Fill in the actual user credentials in `.env` file
5. Start the development server:
   ```bash
   npm run dev
   ```

## Environment Configuration

The application uses environment variables for user credentials. All variables must be prefixed with `VITE_` for Vite to expose them to the client.

### Required Environment Variables

- `VITE_API_BASE_URL`: Base URL for API endpoints
- User credentials in format: `VITE_USER_{UNIT}_{CATEGORY}_{NAME}_{USERNAME/PASSWORD}`

Example:
```env
VITE_API_BASE_URL=http://127.0.0.1:8008
VITE_USER_ADMIN_PNS_SEGAF_USERNAME=123
VITE_USER_ADMIN_PNS_SEGAF_PASSWORD=123
```

### User Structure

The application supports 5 organizational units:
- **ADMIN** (7 users: 6 PNS, 1 PPPK)
- **AVSEC** (14 users: 5 PNS, 9 PPPK)  
- **PK-PPK** (15 users: 9 PNS, 6 PPPK)
- **BANGLAND** (7 users: 5 PNS, 2 PPPK)
- **LISTRIK** (11 users: 4 PNS, 7 PPPK)

## Security

- User credentials are stored in environment variables (not in source code)
- `.env` file is gitignored to prevent accidental commits
- Use `.env.example` as template for deployment

## Development

```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint
```

## Project Structure

```
src/
├── components/          # React components
├── data/               # Data configuration (now using env vars)
├── config/             # API configuration
├── assets/             # Static assets
├── App.jsx             # Main application component
└── main.jsx            # Application entry point
```

## API Integration

The application connects to a backend API for form submissions and data management. Configure the API URL in the `.env` file.
