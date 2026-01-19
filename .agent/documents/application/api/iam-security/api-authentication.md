# API Specification: Authentication

> Dokumen ini berisi spesifikasi teknis API untuk fitur Authentication.
> Semua endpoint **WAJIB** mengikuti standar **JSON:API** (https://jsonapi.org).

---

## 1. Global Standards

- **Base URL:** `/api/v1`
- **Content-Type:** `application/vnd.api+json`
- **Accept:** `application/vnd.api+json`
- **Date Format:** ISO 8601 (`YYYY-MM-DDTHH:mm:ssZ`)

---

## 2. Endpoints

### 2.1 Register (Create User)
- **URL:** `POST /auth/register`
- **Description:** Mendaftarkan pengguna baru.
- **Access Control:** Public

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Accept: application/vnd.api+json
```

**Body:**
```json
{
  "data": {
    "type": "auth_register",
    "attributes": {
      "email": "user@example.com",
      "password": "securePassword123",
      "full_name": "John Doe"
    }
  }
}
```

#### Response

**Success (201 Created):**
```json
{
  "data": {
    "type": "users",
    "id": "uuid-1234",
    "attributes": {
      "email": "user@example.com",
      "full_name": "John Doe",
      "status": "ACTIVE",
      "created_at": "2023-10-10T10:00:00Z"
    },
    "links": {
      "self": "/api/v1/users/uuid-1234"
    }
  }
}
```

---

### 2.2 Login (Auth Login)
- **URL:** `POST /auth/login`
- **Description:** Melakukan login dan mendapatkan token akses.
- **Access Control:** Public

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Accept: application/vnd.api+json
```

**Body:**
```json
{
  "data": {
    "type": "auth_login",
    "attributes": {
      "email": "user@example.com",
      "password": "securePassword123"
    }
  }
}
```

#### Response

**Success (200 OK):**
```json
{
  "data": {
    "type": "auth_session",
    "id": "uuid-token-session",
    "attributes": {
      "access_token": "eyJhbG...",
      "refresh_token": "eyJhbG...",
      "expires_in": 3600,
      "token_type": "Bearer"
    },
    "links": {
      "self": "/api/v1/auth/session"
    }
  }
}
```

---
 
 ### 2.3 Get Profile (Me)
 - **URL:** `GET /auth/me`
 - **Description:** Mendapatkan profil user yang sedang login.
 - **Access Control:** Authenticated
 
 #### Request
 
 **Headers:**
 ```http
 Authorization: Bearer <token>
 ```
 
 #### Response
 
 **Success (200 OK):**
 ```json
 {
   "data": {
     "type": "users",
     "id": "uuid-me",
     "attributes": {
       "email": "user@example.com",
       "full_name": "John Doe",
       "role": "EDITOR"
     },
     "links": {
       "self": "/api/v1/users/uuid-me"
     }
   }
 }
 ```
 
 ---
 
 ### 2.3 Logout (Revoke Session)
 - **URL:** `POST /auth/logout`
 - **Description:** Mengakhiri sesi pengguna (Revoke Token).
 - **Access Control:** Authenticated
 
 #### Request
 
 **Headers:**
 ```http
 Authorization: Bearer <token>
 ```
 
 #### Response
 
 **Success (204 No Content):**
 *(Empty Body)*
 
 ---
 
 ### 2.4 Activate Account
 - **URL:** `POST /auth/activate`
 - **Description:** Mengaktifkan akun pengguna baru via token email.
 - **Access Control:** Public
 
 #### Request
 
 **Body:**
 ```json
 {
   "data": {
     "type": "auth_activate",
     "attributes": {
       "token": "activation-token-xyz"
     }
   }
 }
 ```
 
 #### Response
 
 **Success (200 OK):**
 ```json
 {
   "meta": { "message": "Account activated successfully." }
 }
 ```
 
 ---
 
 ### 2.5 Change Password
 - **URL:** `POST /auth/change-password`
 - **Description:** Mengganti password saat user sedang login.
 - **Access Control:** Authenticated
 
 #### Request
 
 **Body:**
 ```json
 {
   "data": {
     "type": "auth_change_password",
     "attributes": {
       "current_password": "oldPassword123",
       "new_password": "newSecurePassword1"
     }
   }
 }
 ```
 
 #### Response
 
 **Success (204 No Content):**
 *(Empty Body)*
 
 ---

### 2.3 Forgot Password (Request Reset)
- **URL:** `POST /auth/forgot-password`
- **Description:** Meminta link reset password via email.
- **Access Control:** Public

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Accept: application/vnd.api+json
```

**Body:**
```json
{
  "data": {
    "type": "auth_forgot_password",
    "attributes": {
      "email": "user@example.com"
    }
  }
}
```

#### Response

**Success (202 Accepted):**
```json
{
  "meta": {
    "message": "If the email exists, a reset link has been sent."
  },
  "data": null
}
```

---

### 2.4 Reset Password (Execute Reset)
- **URL:** `POST /auth/reset-password`
- **Description:** Mengatur ulang password menggunakan token reset.
- **Access Control:** Public

#### Request

**Headers:**
```http
Content-Type: application/vnd.api+json
Accept: application/vnd.api+json
```

**Body:**
```json
{
  "data": {
    "type": "auth_reset_password",
    "attributes": {
      "token": "reset-token-xyz",
      "new_password": "newSecurePassword1"
    }
  }
}
```

#### Response

**Success (200 OK):**
```json
{
   "meta": {
      "message": "Password updated successfully."
   },
   "data": null
}
```
