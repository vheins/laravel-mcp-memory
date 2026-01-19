# API Specification: Role & Permission Management

> Dokumen ini berisi spesifikasi teknis API untuk fitur Role & Permission Management.
> Semua endpoint **WAJIB** mengikuti standar **JSON:API** (https://jsonapi.org).

---

## 1. Global Standards

- **Base URL:** `/api/v1`
- **Content-Type:** `application/vnd.api+json`
- **Accept:** `application/vnd.api+json`
- **Date Format:** ISO 8601 (`YYYY-MM-DDTHH:mm:ssZ`)

---

## 2. Endpoints

### 2.1 Create Role
- **URL:** `POST /roles`
- **Description:** Membuat role baru.
- **Access Control:** Authenticated (Admin)

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
    "type": "roles",
    "attributes": {
      "name": "EDITOR",
      "description": "Can edit content"
    }
  }
}
```

#### Response

**Success (201 Created):**
```json
{
  "data": {
    "type": "roles",
    "id": "role-uuid-1",
    "attributes": {
      "name": "EDITOR",
      "description": "Can edit content"
    },
    "links": {
      "self": "/api/v1/roles/role-uuid-1"
    }
  }
}
```

---
 
 ### 2.2 Update Role
 - **URL:** `PATCH /roles/:id`
 - **Description:** Mengubah nama atau deskripsi role.
 - **Access Control:** Authenticated (Admin)
 
 #### Request
 
 **Body:**
 ```json
 {
   "data": {
     "type": "roles",
     "id": "role-uuid-1",
     "attributes": {
       "name": "EDITOR_LEAD",
       "description": "Lead Editor with publish rights"
     }
   }
 }
 ```
 
 #### Response
 
 **Success (200 OK):**
 ```json
 {
   "data": {
     "type": "roles",
     "id": "role-uuid-1",
     "attributes": {
       "name": "EDITOR_LEAD",
       "description": "Lead Editor with publish rights"
     },
     "links": { "self": "/api/v1/roles/role-uuid-1" }
   }
 }
 ```
 
 ---
 
 ### 2.3 Delete Role
 - **URL:** `DELETE /roles/:id`
 - **Description:** Menghapus role (Soft/Hard delete).
 - **Access Control:** Authenticated (Admin)
 
 #### Response
 
 **Success (204 No Content):**
 *(Empty Body)*
 
 ---

### 2.2 List Roles
- **URL:** `GET /roles`
- **Description:** Mendapatkan daftar semua role.
- **Access Control:** Authenticated (Admin)

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
  "data": [
    {
      "type": "roles",
      "id": "role-uuid-1",
      "attributes": {
        "name": "EDITOR",
        "description": "Can edit content"
      },
      "links": {
          "self": "/api/v1/roles/role-uuid-1"
      }
    }
  ],
  "links": {
      "self": "/api/v1/roles"
  }
}
```

---

### 2.3 List Permissions
- **URL:** `GET /permissions`
- **Description:** Mendapatkan daftar semua permission yang tersedia.
- **Access Control:** Authenticated (Admin)

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
  "data": [
    {
      "type": "permissions",
      "id": "perm-uuid-1",
      "attributes": {
        "code": "ARTICLE:CREATE",
        "description": "Can create articles"
      },
      "links": {
          "self": "/api/v1/permissions/perm-uuid-1"
      }
    }
  ],
  "links": {
      "self": "/api/v1/permissions"
  }
}
```

---

### 2.4 Assign Permissions to Role (Update Relationship)
- **URL:** `PATCH /roles/:id/relationships/permissions`
- **Description:** Mengupdate daftar permission untuk role tertentu.
- **Access Control:** Authenticated (Admin)

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
  "data": [
    { "type": "permissions", "id": "perm-uuid-1" },
    { "type": "permissions", "id": "perm-uuid-2" }
  ]
}
```

#### Response

**Success (204 No Content):**
*(Empty Body)*

---

### 2.5 Assign Roles to User (Update Relationship)
- **URL:** `PATCH /users/:id/relationships/roles`
- **Description:** Mengupdate daftar role untuk user tertentu.
- **Access Control:** Authenticated (Admin)

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
  "data": [
    { "type": "roles", "id": "role-uuid-1" }
  ]
}
```

#### Response

**Success (204 No Content):**
*(Empty Body)*
