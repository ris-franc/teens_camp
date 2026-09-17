# Teen Camp Management System — User Accounts & Credentials

This document provides a complete directory of all seeded and active system users, portal login URLs, roles, and authentication credentials.

---

## 🔐 Authentication Overview

The system uses **4-digit PIN authentication** (hashed securely with Bcrypt) with two dedicated gateways:

| Gateway | Target Users | Login URL | Default PIN |
| :--- | :--- | :--- | :--- |
| **Public Gateway** | Parents & Teens | [`http://localhost:8080/login`](http://localhost:8080/login) | `1234` *(or `0000` for first-time login)* |
| **Staff & Backoffice** | Admins, Pastors, Registration, Campaign | [`http://localhost:8080/backoffice/login`](http://localhost:8080/backoffice/login) | `1234` |

> **First-Time Login Note:**  
> Newly registered parents and teens are assigned the initial default PIN **`0000`** with a prompt to set their own personal 4-digit PIN upon initial sign-in.

---

## 🛡️ 1. Back-Office & Staff Accounts

All staff and leadership sign in via the **Backoffice Portal**: [`http://localhost:8080/backoffice/login`](http://localhost:8080/backoffice/login)

| Role | Name | Email Address | 4-Digit PIN | Phone Number | Portal Access & Responsibilities |
| :--- | :--- | :--- | :---: | :--- | :--- |
| **Admin** | System Admin / Kariuki Juma | `karis123wer@gmail.com`<br>*(also `admin@church.org`)* | **`1234`** | `0113242722` | **Full Command Deck**: Season switcher, financial metrics, adopt-a-teen allocations, database search, export, live sync. |
| **Pastor** | Senior Pastor Michael Mwangi | `pastor@church.org` | **`1234`** | `+254 722 300 400` | **Executive Oversight**: Real-time camper statistics, pastoral care alerts, camp attendance reports. |
| **Registration** | Rachel Wanjiku | `registration@church.org` | **`1234`** | `+254 733 500 600` | **Registration Desk**: 2-minute express desk intake, check-in, dynamic form field builder, badge printing. |
| **Campaign Head**| Grace Achieng | `campaignhead@church.org` | **`1234`** | `+254 711 700 800` | **Campaign Leadership**: Weekly merchandise batches, sales audit, profit transfer to Adopt-a-Teen Kitty. |
| **Campaign Staff**| Caleb Kiprop | `campaign@church.org` | **`1234`** | `+254 720 900 100` | **Campaign POS**: Merchandise inventory tracking, daily sales entry (water bottles, hoodies, wristbands). |

---

## 👨‍👩‍👧 2. Parent / Guardian Accounts

Parents sign in via the **Public Gateway**: [`http://localhost:8080/login`](http://localhost:8080/login)

| Parent Name | Email Address | 4-Digit PIN | Phone Number | Linked Camper(s) | Features Available |
| :--- | :--- | :---: | :--- | :--- | :--- |
| **Sarah Muthoni** | `parent@church.org` | **`1234`** | `+254 712 345 678` | • **Ethan Kamau**<br>• **Chloe Wambui** | Pay camp fees via Lipa na M-Pesa, view official receipts, sign medical forms, manage digital wellness phone consent, apply for Adopt-a-Teen. |
| **Robert Otieno** | `robert.otieno@example.com` | **`1234`** | `+254 721 888 999` | • **Lucas Baraka** | Manage camper registration, view payment ledger, instant receipt downloads. |
| **Samuel Adams Kiprono** | `samuel.adams@test.org` | **`0000`**<br>*(First-login)* | `+254 722 987 654` | • **Benjamin Adams Kiprono** | First-time registration account; prompts to set custom PIN upon first login. |

---

## 🎒 3. Teen / Camper Accounts

Teens sign in via the **Public Gateway**: [`http://localhost:8080/login`](http://localhost:8080/login)

| Camper Name | Email Address | 4-Digit PIN | Gender & Age | Linked Parent | Features Available |
| :--- | :--- | :---: | :--- | :--- | :--- |
| **Ethan Kamau** | `teen@church.org` | **`1234`** | Male<br>*(DOB: 2010-04-15)* | Sarah Muthoni | Interactive packing checklist, submit required surveys/activity forms, view read-only camp fee status & schedule. |
| **Chloe Wambui** | `chloe.wambui@example.com` | **`1234`** | Female<br>*(DOB: 2012-08-20)* | Sarah Muthoni | View packing list items, submit cabin preference and dietary surveys. |
| **Lucas Baraka** | `newteen@church.org` | **`1234`** | Male<br>*(DOB: 2011-02-10)* | Robert Otieno | First-time login demo account; tested with pin reset workflows. |
| **Benjamin Adams Kiprono** | `benjamin.adams@test.org` | **`0000`**<br>*(First-login)* | Male | Samuel Adams Kiprono | Newly registered camper account. Prompts to set custom PIN upon login. |

---

## 💳 Lipa na M-Pesa Test Credentials

For testing camp fee payments and Adopt-a-Teen donations:

- **Paybill Number:** `880100`  
- **Account Number:** Camper's Full Name (e.g. `Ethan Kamau`)  
- **Buy Goods / Till Number:** `5412345`  
- **Sample M-Pesa Confirmation Codes:** `QHK78X9921`, `RJG82K9102`, `SHK99L2034`

---

## 🛠️ Re-Seeding & Database Commands

If you ever need to reset the database and restore all clean demo accounts and seasons:

```powershell
# In PowerShell / Command Prompt (inside project root)
& "C:\xampp\php\php.exe" artisan migrate:fresh --seed
```

To run all automated feature verification tests:

```powershell
& "C:\xampp\php\php.exe" artisan test --filter=CampSystemTest
```
