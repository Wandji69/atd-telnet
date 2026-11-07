# Software Requirements Specification (SRS)

## Off-Grid Resilient Intern Attendance & Activity System

## 1. Introduction

### 1.1 Purpose

This document specifies the software requirements for an enterprise-grade Intern Attendance and Activity Tracking System. The system is designed to operate seamlessly under extreme off-grid conditions, including up to 48 hours of continuous power failure and multi-day internet outages, while maintaining zero-trust anti-proxy security and real-time manager visibility.

### 1.2 Scope

The system encompasses hardware-integrated attendance terminals (Android POS devices and NFC-enabled smartphones running React Native with WatermelonDB), encrypted smart card read/write management, local offline-first transactional reactive storage, real-time and asynchronous facial verification, remote/offsite geofenced check-in modules, activity logging, and microservice-driven analytics and notification dispatches built with intern-friendly backend stacks (Node.js / Laravel).

### 1.3 Target Operational Metrics

* **Peak Load Capacity**: 50 interns per terminal station during peak check-in windows (7:30 AM – 9:00 AM).

* **Power Autonomy**: Minimum 48-hour continuous local operation during power outages via internal battery backups and optimized low-power operating states.

* **Network Independence**: Full local operational autonomy up to 30 days without cloud synchronization.

## 2. System Architecture & Tech Stack

### 2.1 Technology Stack Architecture

```
+-----------------------------------------------------------------------+
|                          EDGE LAYER (On-Premise / Offsite)            |
|                                                                       |
|  +-----------------------------------------------------------------+  |
|  | React Native Terminal Application (Android POS / NFC Phone)     |  |
|  | - Encrypted NFC Read/Write Module (AES-256)                      |  |
|  | - Camera Module (Facial Snapshot & Liveness Check)               |  |
|  | - Fallback Authenticator (PIN + Photo Capture)                   |  |
|  | - WatermelonDB (Offline-First Reactive Database Engine)          |  |
|  | - WatermelonDB Sync Engine (Delta Sync & Conflict Resolution)   |  |
|  +-----------------------------------------------------------------+  |
+---------------------------------------------------|-------------------+
                                                    | (HTTPS/gRPC - Asynchronous Sync)
                                                    v
+-----------------------------------------------------------------------+
|                       CLOUD PLATFORM & MICROSERVICES                  |
|                                                                       |
|  +-----------------------------------------------------------------+  |
|  | API Gateway & Routing Layer (Nginx / Express / Laravel Gateway)   |  |
|  +-----------------------------------------------------------------+  |
|                                   |                                   |
|   +-------------------+  +--------------------+  +-----------------+  |
|   | Attendance Svc    |  | Sync & Ingestion   |  | Facial AI /     |  |
|   | (Node.js/Laravel) |  | (Watermelon Protocol) |  | Fraud Svc       |  |
|   +-------------------+  +--------------------+  +-----------------+  |
|   | Remote Geofence   |  | Activity & Task    |  | Alerting Svc    |  |
|   | Verification Svc  |  | Tracking Svc       |  | (SMS/WhatsApp)  |  |
|   +-------------------+  +--------------------+  +-----------------+  |
|                                   |                                   |
|   +-----------------------------------------------------------------+ |
|   | Database Tier (PostgreSQL Primary, Redis Cache, S3 Evidence)    | |
|   +-----------------------------------------------------------------+ |
|                                   |                                   |
|   +-----------------------------------------------------------------+ |
|   | Manager Cloud Dashboard (Web / Mobile Admin Portal)             | |
|   +-----------------------------------------------------------------+ |
+-----------------------------------------------------------------------+
```

### 2.2 Tech Stack Selection & Strategy for Intern Developers

To ensure high developer velocity and manageable learning curves for software interns, the backend architecture is streamlined:

* **Edge Application**: React Native + **WatermelonDB** (lazy-loaded, high-performance reactive database built on SQLite natively for multi-thousand record synchronization).
* **Backend Framework Comparison & Choice**:
  * **Option A: Node.js (TypeScript / Express / NestJS)**
    * *Pros*: Full-stack JavaScript paradigm (shares TS/JS types directly between React Native edge app and microservices). Single language ecosystem lowers cognitive load for interns.
    * *Cons*: Requires disciplined architecture patterns (e.g., NestJS) to prevent unstructured code.
  * **Option B: Laravel (PHP)**
    * *Pros*: Out-of-the-box authentication, queue system (Laravel Queues for async WhatsApp/SMS notifications), eloquent ORM, and built-in REST/Sync tooling. Extremely accessible for rapid onboarding.
    * *Cons*: Context switching between JS/TS on mobile and PHP on backend.

## 3. Detailed Functional Requirements

### 3.1 Device & Card Authentication

* **FR-3.1.1 Encrypted NFC Smart Card Read/Write**:
  * Terminal devices (POS or NFC smartphones running React Native) must read from and write directly to internal encrypted sectors of the intern's NFC smart card using AES-256 keys.
  * Card payload must store: Intern UUID, encrypted rolling security token, dynamic cumulative offline log count, and last timestamp.
  * Upon card tap, the terminal updates the card's internal chip memory with the current status (e.g., `CHECKED_IN`, `ON_BREAK`, `CHECKED_OUT`) to maintain hardware-level state redundancy.

* **FR-3.1.2 Anti-Proxy Photo Verification & AI Analysis**:
  * **Real-time Capture**: NFC tap or barcode scan triggers an automated camera snapshot.
  * **Local Processing**: Terminal executes immediate basic liveness detection (detecting a 3D face vs. a printed photograph/screen display).
  * **Async Cloud AI**: When synced, the Fraud Microservice performs facial vector comparison against the registered profile reference image. Discrepancies generate anti-fraud flags.

* **FR-3.1.3 Fallback Authentication (Forgotten/Lost Card)**:
  * In the absence of an NFC card, the system must allow entry via a unique 6-digit Personal Identification Number (PIN).
  * PIN authentication **must enforce** mandatory high-resolution photo capture and auto-flag the entry for manager review.

### 3.2 Remote & Offsite Intern Tracking

* **FR-3.2.1 Remote Check-In Protocol**:
  * Authorized remote/field interns access an encrypted mobile module within the React Native app.
  * Check-in requires multi-factor proof: GPS coordinate capture, local cell-tower triangulation, and a real-time timestamped selfie photo.

* **FR-3.2.2 Geofence Boundary Enforcement**:
  * Remote check-ins validate coordinates against a defined geographic radius ($\pm X\text{ meters}$) around assigned field locations.
  * Out-of-bounds check-ins trigger soft-rejections or conditional logs requiring supervisor override.

### 3.3 Power & Offline Operations Engine (WatermelonDB Integration)

* **FR-3.3.1 48-Hour Battery & Power Optimization**:
  * Terminal applications must feature aggressive power management modes (dimming screen timeout $< 10\text{ seconds}$, suspending non-essential background services, disabling cellular modems when offline queue is idle).
  * Device hardware must operate continuously on internal/external battery backups for up to 48 hours without grid power.

* **FR-3.3.2 WatermelonDB Reactive Sync Mechanism**:
  * Transactions (attendance logs, status markers, photo metadata) write reactively to local **WatermelonDB** models.
  * The edge app implements WatermelonDB’s standard Sync protocol (`synchronize()` function):
    * **Pull Phase**: Fetches changes from cloud (e.g., new intern profiles, key revocations) since `lastPulledAt`.
    * **Push Phase**: Sends locally created/updated records (`created`, `updated`) to the ingestion endpoint.
  * Fast query response times are guaranteed even with thousands of local logs due to WatermelonDB’s lazy-loading architecture.

## 4. Edge Cases & Exception Handling Matrix

| Case ID | Edge Case Scenario | System Behavior / Mitigation |
| ----- | ----- | ----- |
| **EC-01** | **48-Hour Continuous Blackout** | Terminal throttles hardware peripherals, stores up to 5,000 local events in WatermelonDB, and operates uninterrupted on battery management. |
| **EC-02** | **NFC Card Corruption / Cryptographic Fail** | Terminal flags card error, falls back to PIN + mandatory photo capture mode, and alerts admin upon sync to re-issue/re-encrypt card sectors. |
| **EC-03** | **Remote GPS Spoofing Attempt** | Mobile client checks OS mock-location flags, cross-references cell-tower IDs with GPS, and rejects fake locations. |
| **EC-04** | **Time Tampering on Terminal** | Monotonic uptime clock detects manual system clock changes. Discrepancies lock terminal time markers until cryptographic cloud re-sync. |
| **EC-05** | **Buddy Punching (Scanning for another)** | Camera snapshot captured at card scan. Asynchronous cloud AI compares facial vectors; if match score $< 85\%$, manager receives immediate alert. |
| **EC-06** | **Sync Conflict (Duplicate Offline Scans)** | WatermelonDB sync endpoint uses server-wins or custom timestamp-ordering strategy to deduplicate push events cleanly. |

## 5. Non-Functional Requirements (NFRs)

* **NFR-5.1 Security & Encryption**:
  * Card Storage: AES-256 sector-level encryption.
  * Database: Encrypted WatermelonDB adapter storage for local edge devices.
  * API Transit: TLS 1.3 with Certificate Pinning on terminal clients.

* **NFR-5.2 Performance**:
  * Edge card scan, NFC write, photo capture, and WatermelonDB reactive UI response must complete in $< 450\text{ ms}$.

* **NFR-5.3 System Scalability**:
  * Backend API gateway and event ingestion services must handle batch sync payload pushes gracefully using background queue workers (e.g., Redis + BullMQ for Node.js, or Laravel Queues/Horizon).

## 6. Microservice & Onboarding Architecture for Intern Developers

### 6.1 Recommended Backend Selection Guidelines

1. **Choose Node.js (TypeScript / Express or NestJS)** if:
   * The team wants to share TypeScript interfaces/types between the React Native app (WatermelonDB models) and the backend API.
   * You want interns to master asynchronous I/O and modern JavaScript backend architecture.

2. **Choose Laravel (PHP)** if:
   * You want built-in administrative tools, rapid ORM database modeling (Eloquent), out-of-the-box authentication (Sanctum/Breeze), and effortless queue handling for SMS/WhatsApp dispatches without setting up custom boilerplate.

### 6.2 Service Breakdown

```
+------------------+--------------------------------------------------------+
| Service Name     | Responsibilities                                       |
+------------------+--------------------------------------------------------+
| Gateway Service  | Key exchange, API rate limiting, routing.              |
| Attendance Svc   | Core attendance logic, state transitions.              |
| Ingestion & Sync | WatermelonDB `pull/push` sync endpoint & deduplication.|
| Fraud Detection  | Async facial feature comparison & proxy detection.     |
| Geofencing Svc   | GPS boundary calculation & map spatial verification.   |
| Notification Svc | Integration with SMS (Twilio/Infobip) & WhatsApp APIs. |
+------------------+--------------------------------------------------------+
```