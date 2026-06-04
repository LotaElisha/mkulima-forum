# MkulimaForum Mobile App

MkulimaForum - Agricultural Super App for East Africa

## Features

- **Soko** (Marketplace) - Buy and sell agricultural products
- **Jukwaa** (Forum) - Connect with other farmers and experts
- **Kagua Mimea** (Disease Scanner) - AI-powered plant disease detection
- **Mtaalamu wa AI** (AI Agronomist) - Get farming advice from AI
- **Wasifu** (Profile) - Manage your account and orders

## Getting Started

### Prerequisites
- Flutter SDK 3.5.0+
- Android Studio / Xcode
- Dart SDK

### Installation

1. Clone the repository
2. Navigate to the app directory:
   ```bash
   cd mkulima_app
   ```

3. Install dependencies:
   ```bash
   flutter pub get
   ```

4. Run code generation:
   ```bash
   flutter pub run build_runner build --delete-conflicting-outputs
   ```

5. Run the app:
   ```bash
   flutter run
   ```

### Building for Production

**Android APK:**
```bash
flutter build apk --release
```

**Android App Bundle:**
```bash
flutter build appbundle --release
```

**iOS:**
```bash
flutter build ios --release
```

## Project Structure

```
lib/
├── main.dart                    # App entry point
├── models/                      # Data models
│   ├── product.dart
│   ├── user.dart
│   └── order.dart
├── providers/                   # State management
│   ├── auth_provider.dart
│   └── connectivity_provider.dart
├── screens/                     # UI screens
│   ├── splash_screen.dart
│   ├── login_screen.dart
│   ├── register_screen.dart
│   ├── home_screen.dart
│   ├── marketplace_screen.dart
│   ├── product_detail_screen.dart
│   ├── cart_screen.dart
│   ├── orders_screen.dart
│   ├── forum_screen.dart
│   ├── scanner_screen.dart
│   ├── agronomist_screen.dart
│   └── profile_screen.dart
├── services/                    # Business logic
│   ├── api_service.dart
│   └── local_database.dart
└── widgets/                     # Reusable widgets
```

## API Configuration

The app connects to the MkulimaForum backend API at:
- Base URL: `http://76.13.56.180:8000/api`

Update the URL in `lib/main.dart` if needed.

## Tech Stack

- **Flutter** - UI framework
- **Provider** - State management
- **Dio** - HTTP client
- **Drift** - SQLite database (offline-first)
- **Freezed** - Code generation for models
- **Image Picker** - Camera/gallery access
- **Connectivity Plus** - Network status monitoring

## License

MIT License - MkulimaForum
