# Spesifikasi API: [Nama Fitur]
 
 > Dokumen ini berisi spesifikasi teknis API untuk fitur [Nama Fitur].
 > Semua endpoint **WAJIB** mengikuti standar **JSON:API** (https://jsonapi.org).
 
 ---
 
 ## 1. Standar Global
 
 - **Base URL:** `/api/v1`
 - **Content-Type:** `application/vnd.api+json`
 - **Accept:** `application/vnd.api+json`
 - **Format Tanggal:** ISO 8601 (`YYYY-MM-DDTHH:mm:ssZ`)
 
 ---
 
 ## 2. Endpoints
 
 ### 2.1 [Nama Endpoint]
 
 - **URL:** `[METHOD] /resource-name`
 - **Deskripsi:** [Deskripsi singkat endpoint]
 - **Kontrol Akses:** [Publik / Terautentikasi / Peran: Admin]
 
 #### Request (Permintaan)
 
 **Headers:**
 ```http
 Content-Type: application/vnd.api+json
 Accept: application/vnd.api+json
 Authorization: Bearer <token>
 ```
 
 **Query Parameters:**
 - `page[number]`: Nomor halaman (default: 1)
 - `page[size]`: Item per halaman (default: 10)
 - `filter[field]`: Nilai filter
 - `include`: Resource terkait untuk disertakan
 
 **Body:**
 ```json
 {
   "data": {
     "type": "resource_name",
     "attributes": {
       "field_1": "value",
       "field_2": 123
     },
     "relationships": {
       "related_resource": {
         "data": { "type": "related_type", "id": "uuid" }
       }
     }
   }
 }
 ```
 
 #### Response (Jawaban)
 
 **Sukses (2xx):**
 ```json
 {
   "data": {
     "type": "resource_name",
     "id": "uuid",
     "attributes": {
       "field_1": "value",
       "field_2": 123,
       "created_at": "2023-01-01T00:00:00Z"
     },
     "relationships": {
       "related_resource": {
         "links": {
           "self": "/api/v1/resource_name/uuid/relationships/related_resource",
           "related": "/api/v1/resource_name/uuid/related_resource"
         }
       }
     },
     "links": {
       "self": "/api/v1/resource_name/uuid"
     }
   },
   "included": [
     {
       "type": "related_type",
       "id": "uuid",
       "attributes": { ... }
     }
   ]
 }
 ```
 
 **Error (4xx/5xx):**
 ```json
 {
   "errors": [
     {
       "status": "422",
       "source": { "pointer": "/data/attributes/field_1" },
       "title": "Atribut Tidak Valid",
       "detail": "Field_1 harus berupa string."
     }
   ]
 }
 ```
 
 ---
 
 ### 2.2 [Nama Endpoint 2]
 
 ---

 ### 2.x Change Password (Example Action)
 - **URL:** `POST /resource/change-password`
 - **Description:** Mengganti password pengguna.
 - **Access Control:** Authenticated
 
 #### Request
 
 **Body:**
 ```json
 {
   "data": {
     "type": "resource_change_password",
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
