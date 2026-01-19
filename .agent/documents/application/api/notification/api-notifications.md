# API Specification: Notification

> Dokumen ini berisi spesifikasi teknis API untuk modul Notification.
> Semua endpoint **WAJIB** mengikuti standar **JSON:API** (https://jsonapi.org).

---

## 1. Global Standards

- **Base URL:** `/api/v1`
- **Content-Type:** `application/vnd.api+json`
- **Accept:** `application/vnd.api+json`

---

## 2. Endpoints

### 2.1 Send Notification

- **URL:** `POST /notifications/send`
- **Description:** Mengirim notifikasi (biasanya dipanggil oleh service lain, atau admin).
- **Access Control:** Authenticated (Service-to-Service atau Admin).

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Authorization: Bearer <token>
```

**Body:**
```json
{
  "data": {
    "type": "notification_requests",
    "attributes": {
      "user_id": "123e4567-e89b-12d3-a456-426614174000",
      "template_code": "OTP_EMAIL",
      "channel": "EMAIL",
      "payload": {
        "code": "123456",
        "name": "Jules"
      }
    }
  }
}
```

#### Response

**Success (202 Accepted):**
```json
{
  "data": {
    "type": "notification_jobs",
    "id": "job-uuid-1234",
    "attributes": {
      "status": "QUEUED",
      "created_at": "2023-10-27T10:00:00Z"
    },
    "links": {
      "self": "/api/v1/notifications/jobs/job-uuid-1234"
    }
  }
}
```

---

### 2.2 List Notifications

- **URL:** `GET /notifications`
- **Description:** Mendapatkan daftar notifikasi milik user yang sedang login (In-App).
- **Access Control:** Authenticated (User).

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Authorization: Bearer <token>
```

**Query Parameters:**
- `page[number]`: Page number.
- `page[size]`: Items per page.
- `filter[status]`: `unread` (optional).

#### Response

**Success (200 OK):**
```json
{
  "data": [
    {
      "type": "notifications",
      "id": "notif-uuid-1",
      "attributes": {
        "title": "Pesanan Diterima",
        "body": "Pesanan #123 sedang diproses.",
        "status": "SENT",
        "is_read": false,
        "created_at": "2023-10-27T09:00:00Z"
      },
      "links": {
        "self": "/api/v1/notifications/notif-uuid-1"
      }
    }
  ],
  "links": {
    "self": "/api/v1/notifications?page[number]=1",
    "next": "/api/v1/notifications?page[number]=2"
  },
  "meta": {
    "total_count": 50,
    "unread_count": 5
  }
}
```

---

### 2.3 Mark as Read

- **URL:** `PATCH /notifications/{id}`
- **Description:** Menandai notifikasi sebagai sudah dibaca.
- **Access Control:** Authenticated (User Owner).

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Authorization: Bearer <token>
```

**Body:**
```json
{
  "data": {
    "type": "notifications",
    "id": "notif-uuid-1",
    "attributes": {
      "is_read": true
    }
  }
}
```

#### Response

**Success (200 OK):**
```json
{
  "data": {
    "type": "notifications",
    "id": "notif-uuid-1",
    "attributes": {
      "is_read": true,
      "status": "READ"
    },
    "links": {
      "self": "/api/v1/notifications/notif-uuid-1"
    }
  }
}
```
