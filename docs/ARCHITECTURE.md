# Licensing System Architecture

## Overview

This system provides a multi-layer protection mechanism for software applications. It combines server-side license verification with client-side hardware fingerprinting to ensure that software is used only by authorized customers on authorized machines.

## 🏗️ Core Modules

### 1. Admin Control Center (High-Density UI)
- **SPA Architecture**: Uses JavaScript for instantaneous view switching (Dashboard, Licenses, Machines, Analytics).
- **KPI Engine**: Calculates real-time metrics for activations, pings, and suspicious movements.
- **Data Viz**: Integrates Chart.js for trend analysis and status distribution.

### 2. Licensing Server (API Gateway)
- **SdkController**: Handles secure `/sdk/activate`, `/sdk/validate`, and `/sdk/report` endpoints.
- **AdminController**: Manages license lifecycle and simulation engines.
- **Security**: Implements SHA-256 hashing for all sensitive identifiers.

### 3. Client Portal (Premium Access)
- **Glassmorphism UI**: High-end UX for end-user activation.
- **System Discovery**: Automatically detects OS and environment details to build the initial hardware profile.

## 🔐 Hardware Fingerprinting

The system generates a unique 64-character hash based on:
- Operating System version and kernel string.
- Machine hostname and hardware type.
- Environment identifiers (PHP/OS build strings).

This fingerprint ensures that the **Activation ID** generated during the first run is bound to the physical hardware, preventing simple "copy-paste" of activated instances.

## 📈 Database Design

- **License**: Holds the source of truth (Key, Status, Max Activations).
- **MachineActivation**: Tracks the link between a License and a unique Fingerprint.
- **ActivityReport**: Logs every interaction (Validation, Usage pulse) for the analytics engine.

## 🚀 Future Roadmap
- Integration of floating licenses (token-based).
- Automated fraud detection via machine learning on activity patterns.
- Multi-factor activation (Email/SMS verification).