# Licensing Project

This is a licensing system built with PHP and Symfony. It provides a robust, end-to-end solution for software protection through secure license management, hardware fingerprinting, and real-time activity monitoring.

## 🚀 Key Features

- **Enterprise Control Center**: A high-density Admin Dashboard with real-time KPIs, activation trends, and live activity feeds.
- **Premium Client Portal**: A modern, glassmorphism-style web portal for end-users to activate their products securely.
- **Hardware Binding**: SHA-256 fingerprinting that uniquely ties each license to a specific machine configuration.
- **Business Intelligence**: Advanced analytics using Chart.js to visualize activation trends and license distribution.
- **Full Management**: Search, filter, and revoke licenses or machine activations instantly via the admin interface.

## 📁 Repository Structure

- `server/`: Symfony-based Licensing Server (API & Admin Dashboard).
- `web_app/`: Premium Client Activation Portal.
- `clients/php/`: Professional PHP SDK and console demonstration.
- `docs/`: Technical architecture and implementation guides.

## 🛠️ Server Setup

1.  Navigate to the server directory:
    ```bash
    cd server
    ```
2.  Install dependencies:
    ```bash
    composer install
    ```
3.  Start the licensing server:
    ```bash
    php -S localhost:8000 -t public
    ```
4.  Access the Admin Dashboard: `http://localhost:8000/admin/dashboard`

## 💻 Client Portal Setup

1.  Navigate to the `web_app` directory.
2.  Start a local server (or use the provided `launch` scripts):
    ```bash
    php -S localhost:8080
    ```
3.  Access the Portal: `http://localhost:8080`

## 🔒 Security Concepts

- **One-Way Hashing**: License keys are never stored in plain text (SHA-256).
- **Node-to-Node Security**: Deep hardware fingerprinting prevents license cloning.
- **Proactive Monitoring**: Real-time activity reports detect suspicious usage patterns.

## 📊 Evaluation & Testing

For demonstration purposes, the Admin Dashboard includes a **"Quick Simulation"** tool. Clicking this button will instantly populate the database with diverse license data (active, revoked, expired) and activity logs, allowing for an immediate review of the analytics and management capabilities.

---
*Built for excellence in software protection.*
