# MCP Memory Server

> MCP Memory Server adalah layanan backend terpusat yang berfungsi sebagai *long-term memory persistence layer* untuk AI Agent.

---

## Header & Navigation

- [Back to Module List](../../../README.md)
- [Link to Testing Scenario](../../testing/memory/test-memory.md)

---

## 1. Module Introduction

### 1.1 Brief Description
Sistem ini menjembatani sifat *stateless* LLM dengan kebutuhan penyimpanan konteks yang persisten, terstruktur, dan dapat diaudit. Berperan sebagai **Single Source of Truth** untuk knowledge base organisasi dan **Context Provider** bagi AI Agent.

### 1.2 Position & Role
- **Type:** Core
- **Value:** Hallucination Prevention, Context Continuity, Auditability, and Multi-Context Isolation.

---

## 2. Feature List
 
 Modul ini terdiri dari fitur-fitur berikut. Silakan klik nama fitur untuk melihat spesifikasi detail ("User Stories" dan "Acceptance Criteria").
 
 | Fitur                      | Deskripsi                                                                 | Sub-Modul               |
 | :------------------------- | :------------------------------------------------------------------------ | :---------------------- |
 | **Memory Persistence**     | Penyimpanan dan pencarian memori berbasis repository scope.               | [Feature](./feature.md) |
 | **Human Management**       | Dashboard Filament untuk verifikasi, editing, dan penguncian memori.      | [Feature](./feature.md) |
 | **Audit & Accountability** | Pencatatan riwayat perubahan untuk transparansi keputusan AI dan manusia. | [Feature](./feature.md) |

 ---

 ## 3. High-Level Architecture
 
 ```mermaid
 flowchart TB
     User["AI Agent / Human Admin"]
     
     subgraph Memory_Module ["Memory Module (MCP)"]
         direction TB
         Service["Memory Service<br>(Scope Resolution)"]
         MCP["MCP Interface<br>(JSON-RPC)"]
         Dashboard["Filament Dashboard<br>(Management)"]
         
         MCP --> Service
         Dashboard --> Service
     end

     DB[(Database<br>PostgreSQL)]
     
     User -->|Request| MCP
     User -->|Management| Dashboard
     Service --> DB
 ```

---

## 4. Global Dependencies

- **Database:** PostgreSQL (Standard Laravel Support)
- **Services:** Spatie Activity Log (Audit Trail)

---
