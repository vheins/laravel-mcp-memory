# TESTING DOCUMENTATION: Configuration Module

> Dokumen ini berisi rencana dan skenario pengujian untuk fitur System Configuration.

---

## Header & Navigation

- [Back to Module Overview](../../modules/configuration/overview.md)
- [Link to API Specification](../../api/configuration/api-configurations.md)

---

## 1. Test Overview

- **Module Name:** Configuration System
- **Scope of Testing:** CRUD Configuration, Caching mechanism, Public access control.


---

## 2. Test Strategy

### 2.1 Unit Testing
- **Coverage Target:** >80%
- **Critical Components:** Config Service, Caching Service.

### 2.2 Integration Testing
- **API Endpoints:**
  - `GET /configurations`
  - `PUT /configurations/:key`
- **Infrastructure:**
  - Verify Redis Cache Hit/Miss logic.

### 2.3 End-to-End (E2E) Testing
- **Key User Flows:**
  - Admin changes config -> FE App reflects change immediately (or after cache expiry).

### 2.4 Monkey Testing
- **Objective:** Test cache consistency and type safety.
- **Approach:** Concurrent updates, Type mismatch injection.

---

## 3. Test Scenarios (Backend / API)

### 3.1 Positive Cases (Happy Paths)
> Skenario sukses sesuai dengan alur bisnis yang diharapkan.

| ID          | Test Case                   | Pre-condition   | Input Data           | Expected Result                            | Priority |
| :---------- | :-------------------------- | :-------------- | :------------------- | :----------------------------------------- | :------- |
| CFG-POS-001 | **Update Config Value**     | Admin Logged In | `value: "new_value"` | 200 OK, Value updated in DB                | High     |
| CFG-POS-002 | **Get Public Configs**      | None (Public)   | `group=public`       | 200 OK, Only `is_public=true` items        | High     |
| CFG-POS-003 | **Get All Configs (Admin)** | Admin Logged In | -                    | 200 OK, All configs returned               | Medium   |
| CFG-POS-004 | **Cache Invalidation**      | Config cached   | Update Value         | Next GET returns new value (Cache cleared) | High     |

### 3.2 Negative Cases (Validation Rules)
> Skenario gagal untuk memvalidasi penanganan error dan input tidak valid.

| ID          | Test Case                  | Pre-condition          | Input Data                | Expected Result                 | Priority |
| :---------- | :------------------------- | :--------------------- | :------------------------ | :------------------------------ | :------- |
| CFG-NEG-001 | **Prevent Duplicate Key**  | Key "SITE_NAME" exists | Create "SITE_NAME"        | 409 Conflict                    | Medium   |
| CFG-NEG-002 | **Get All Configs (User)** | User Logged In         | -                         | 403 Forbidden                   | High     |
| CFG-NEG-003 | **Invalid Type Update**    | Type: Boolean          | `value: "This is string"` | 400 Bad Request (Type Mismatch) | Medium   |

### 3.3 Monkey Tests (Chaos & Stability)
> Pengujian dengan input acak/kacau untuk menguji ketahanan sistem.

| ID          | Test Case          | Approach                      | Input Data                   | Expected Result                       | Priority |
| :---------- | :----------------- | :---------------------------- | :--------------------------- | :------------------------------------ | :------- |
| CFG-MNK-001 | **JSON Malformed** | Update JSON Config            | `value: "{ bad_json: "`      | 400 Bad Request                       | Low      |
| CFG-MNK-002 | **Cache Stampede** | Concurrent Reads              | 1000 concurrent GETs on miss | Only 1 DB call, others wait or queue  | High     |
| CFG-MNK-003 | **Key Fuzzing**    | Create Key with Special Chars | `Key: "DEBUG/MODE???"`       | 400 Bad Request (Snake_case enforced) | Low      |

---

## 4. Frontend Testing Scenarios

### 4.1 Component / Unit Testing

| ID         | Component    | Test Case       | Expected Behavior                       |
| :--------- | :----------- | :-------------- | :-------------------------------------- |
| CFG-FE-001 | ConfigEditor | Type Validation | Input Text in Number Config shows error |

### 4.2 E2E Testing (User Flows)

| ID          | Flow Name       | Steps                    | Expected Outcome             |
| :---------- | :-------------- | :----------------------- | :--------------------------- |
| CFG-E2E-001 | **Edit Config** | Admin click edit -> Save | Toast Success, Table updated |

---

## 5. Security Testing

- **Data Protection:** Ensure sensitive configs (Internal Keys) are marked `is_public=false` and never returned to public endpoint.
- **Audit:** Ensure every update logs the actor and timestamp.
