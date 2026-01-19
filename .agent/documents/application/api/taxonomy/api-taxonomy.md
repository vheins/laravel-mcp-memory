# Spesifikasi API: Taxonomy

- [Kembali ke Ikhtisar Modul](../../../modules/taxonomy/overview.md)
 
 > Dokumen ini berisi spesifikasi teknis API untuk fitur Taxonomy & Classification.
 
 ---
 
 ## 1. Standar Global
 
 - **Base URL:** `/api/v1`
 - **Content-Type:** `application/vnd.api+json`
 
 ---
 
 ## 2. Endpoint
 
 ### 2.1 Ambil Taxonomies (Get Taxonomies)
 
 - **URL:** `GET /taxonomies`
 - **Deskripsi:** Mengambil daftar namespace taxonomy yang tersedia.
 
 #### Request

**Headers:**
```http
Accept: application/vnd.api+json
Authorization: Bearer <token>
```

#### Respons
 
 **Sukses (200):**
 ```json
 {
   "data": [
     {
       "type": "taxonomy",
       "id": "uuid",
       "attributes": {
         "code": "product_category",
         "name": "Product Category",
         "is_hierarchical": true
       }
     }
   ]
 }
 ```
 
 ---
 
 ### 2.2 Ambil Terms berdasarkan Taxonomy
 
 - **URL:** `GET /taxonomies/:code/terms`
 - **Deskripsi:** Mengambil daftar terms untuk taxonomy tertentu (struktur pohon jika hierarkis).
 
 #### Request

**Headers:**
```http
Accept: application/vnd.api+json
Authorization: Bearer <token>
```

#### Respons
 
 **Sukses (200):**
 ```json
 {
   "data": [
     {
       "type": "term",
       "id": "uuid",
       "attributes": {
         "code": "electronics",
         "label": "Electronics",
         "parent_id": null
       }
     }
   ]
 }
 ```
 
 ---
 
 ### 2.3 Lampirkan Term ke Entitas (Attach Term)
 
 - **URL:** `POST /attachments`
 - **Deskripsi:** Menghubungkan sebuah term ke entity arbitrer (Polymorphic).
 
 #### Request Body

**Headers:**
```http
Content-Type: application/vnd.api+json
Accept: application/vnd.api+json
Authorization: Bearer <token>
```
 
 ```json
 {
   "data": {
     "type": "attachment",
     "attributes": {
       "entity_type": "product",
       "entity_id": "product-uuid-123",
       "term_code": "electronics",
       "taxonomy_code": "product_category"
     }
   }
 }
 ```
 
 #### Respons
 
 **Sukses (201):**
 ```json
 {
   "meta": { "message": "Attached successfully" }
 }
 ```
