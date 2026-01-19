# API Specification: Configuration

> Dokumen ini berisi spesifikasi teknis API untuk modul Configuration.
> Semua endpoint **WAJIB** mengikuti standar **JSON:API** (https://jsonapi.org).

---

## 1. Global Standards

- **Base URL:** `/api/v1`
- **Content-Type:** `application/vnd.api+json`
- **Accept:** `application/vnd.api+json`

---

## 2. Endpoints

### 2.1 List Configurations (Admin)

- **URL:** `GET /configurations`
- **Description:** Mengambil semua konfigurasi (termasuk yang private).
- **Access Control:** Authenticated (Role: Super Admin).

#### Request

**Headers:**
```http
Accept: application/vnd.api+json
Authorization: Bearer <token>
```

**Query Parameters:**
- `page[number]`: Page number.
- `page[size]`: Items per page.
- `filter[group]`: Filter by group (e.g., `SYSTEM`).
- `filter[key]`: Search by key.

#### Response

**Success (200 OK):**
```json
{
  "data": [
    {
      "type": "configurations",
      "id": "cfg-uuid-1",
      "attributes": {
        "key": "SYSTEM_MAINTENANCE_MODE",
        "value": "false",
        "data_type": "BOOLEAN",
        "is_public": true,
        "description": "Put system in maintenance mode"
      },
      "links": {
        "self": "/api/v1/configurations/cfg-uuid-1"
      }
    }
  ],
  "meta": {
    "total_count": 100
  }
}
```

---

### 2.2 List Public Configurations (Public/Client)

- **URL:** `GET /configurations/public`
- **Description:** Mengambil konfigurasi yang ditandai `is_public=true`. Digunakan untuk inisialisasi aplikasi Frontend/Mobile.
- **Access Control:** Public.

#### Response

**Success (200 OK):**
```json
{
  "data": [
    {
      "type": "configurations",
      "id": "cfg-uuid-2",
      "attributes": {
        "key": "CONTACT_PHONE",
        "value": "+62812345678",
        "data_type": "STRING"
      }
    }
  ]
}
```

---

### 2.3 Update Configuration

- **URL:** `PATCH /configurations/{id}`
- **Description:** Mengubah nilai konfigurasi.
- **Access Control:** Authenticated (Role: Super Admin).

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Accept: application/vnd.api+json
Authorization: Bearer <token>
```

**Body:**
```json
{
  "data": {
    "type": "configurations",
    "id": "cfg-uuid-1",
    "attributes": {
      "value": "true"
    }
  }
}
```

#### Response

**Success (200 OK):**
```json
{
  "data": {
    "type": "configurations",
    "id": "cfg-uuid-1",
    "attributes": {
      "key": "SYSTEM_MAINTENANCE_MODE",
      "value": "true",
      "updated_at": "2023-10-27T12:00:00Z"
    }
  }
}
```
