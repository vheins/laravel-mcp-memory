# API Specification: Background Jobs

> Dokumen ini berisi spesifikasi teknis API untuk fitur Background Jobs & Queue management.
> Sebagian besar interaksi melalui Redis, namun API HTTP disediakan untuk monitoring/trigger manual.

---

## 1. Global Standards

- **Base URL:** `/api/v1/jobs`
- **Content-Type:** `application/vnd.api+json`
- **Accept:** `application/vnd.api+json`

---

## 2. Endpoints

### 2.1 Get Job Status
- **URL:** `GET /:queueName/:jobId`
- **Description:** Mengambil status job tertentu (Completed/Failed/Active).
- **Access Control:** Authenticated (Admin/System).

#### Request

**Headers:**
```http
Accept: application/vnd.api+json
Authorization: Bearer <token>
```

#### Response

**Success (200 OK):**
```json
{
  "data": {
    "type": "job_status",
    "id": "123",
    "attributes": {
        "status": "completed",
        "progress": 100,
        "result": { "exported_file": "https://..." }
    },
    "links": {
        "self": "/api/v1/jobs/export/123"
    }
  }
}
```

---

### 2.2 Retry Failed Job
- **URL:** `POST /:queueName/:jobId/retry`
- **Description:** Menjalankan ulang job yang gagal.
- **Access Control:** Authenticated (Admin)

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Accept: application/vnd.api+json
Authorization: Bearer <token>
```

#### Response

**Success (200 OK):**
```json
{
  "meta": {
    "message": "Job retried successfully"
  },
  "data": null
}
```

---

### 2.3 Create Export Job (Trigger)
- **URL:** `POST /jobs/export`
- **Description:** Memicu job export data (US-JOB-03).
- **Access Control:** Authenticated

#### Request

**Body:**
```json
{
  "data": {
    "type": "job_export",
    "attributes": {
      "format": "pdf",
      "date_range": "2023-01"
    }
  }
}
```

#### Response

**Success (202 Accepted):**
```json
{
  "data": {
    "type": "jobs",
    "id": "job-uuid-new",
    "attributes": {
      "status": "waiting",
      "queue": "export_queue"
    }
  }
}
```
