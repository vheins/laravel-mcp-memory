# API Specification — MCP Memory

> Dokumentasi ini mendefinisikan kontrak API untuk interaksi AI Agent (MCP) dengan Memory Server. Mengikuti standar JSON:API.

---

## Header & Navigation

- [Back to Feature Doc](../../application/modules/memory/feature.md)
- [Back to Module Overview](../../application/modules/memory/overview.md)

---

## 1. Base URL
`{{PAAS_URL}}/api/v1/mcp`

---

## 2. Endpoints

### 2.1 Memory Write
Digunakan oleh AI Agent untuk menyimpan fakta atau log keputusan.

- **Method:** `POST`
- **Path:** `/memory`
- **Payload:**
```json
{
  "data": {
    "type": "memories",
    "attributes": {
      "content": "Gunakan rounding PHP_ROUND_HALF_UP untuk kalkulasi pajak.",
      "memory_type": "decision_log",
      "repository_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
      "tags": ["tax", "backend"]
    }
  }
}
```
- **Response (201 Created):**
```json
{
  "data": {
    "type": "memories",
    "id": "e4dd631e-d243-460f-a2ce-b8a2fe741022",
    "attributes": {
      "status": "unverified",
      "created_at": "2026-01-19T14:00:00Z"
    }
  }
}
```

### 2.2 Memory Search (RAG)
Mencari memori relevan berdasarkan query dan konteks repository.

- **Method:** `GET`
- **Path:** `/memory/search`
- **Query Params:**
  - `filter[query]`: String pencarian.
  - `filter[repository_id]`: UUID repository aktif.
  - `page[limit]`: Default 5.
- **Response (200 OK):**
```json
{
  "data": [
    {
      "type": "memories",
      "id": "...",
      "attributes": {
        "content": "...",
        "type": "business_rule",
        "scope_badge": "Organization",
        "reliability_score": 0.95
      }
    }
  ]
}
```

### 2.3 Memory Delete
Menghapus memori (Soft Delete).

- **Method:** `DELETE`
- **Path Code:** `/memory/{id}`
- **Payload:**
```json
{
  "meta": {
    "reason": "Sudah tidak relevan dengan arsitektur baru."
  }
}
```

---

## 3. Error Handling

| Code | Title                | Detail                                             |
| :--- | :------------------- | :------------------------------------------------- |
| 403  | Forbidden            | AI mencoba memodifikasi memori berstatus `Locked`. |
| 422  | Unprocessable Entity | Validasi `repository_id` atau `content` gagal.     |
| 404  | Not Found            | Memori ID tidak ditemukan dalam scope repository.  |

---
