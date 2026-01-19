- [Kembali ke Ikhtisar Modul](../../../modules/media-management/overview.md)

---

## 2. Endpoint

### 2.1 Upload File

- **URL:** `POST /upload`
- **Deskripsi:** Mengunggah file baru ke temporary storage atau langsung ke storage permanen.
- **Kontrol Akses:** Terautentikasi.

#### Request (Multipart/Form-Data)

**Headers:**
```http
Authorization: Bearer <token>
Accept: application/vnd.api+json
```

**Body:**
- `file`: (Binary File)
- `folder`: (String, optional) Folder tujuan, misal `avatars`.

#### Respons

**Sukses (201):**
```json
{
  "data": {
    "type": "media",
    "id": "uuid-media-123",
    "attributes": {
      "filename": "random-name.jpg",
      "original_name": "foto_liburan.jpg",
      "mime_type": "image/jpeg",
      "size": 102400,
      "url": "https://storage.cdn.com/avatars/random-name.jpg"
    }
  }
}
```

---

### 2.2 Attach Media ke Entitas

- **URL:** `POST /attach`
- **Deskripsi:** Menghubungkan media yang sudah diupload ke sebuah entitas (misal Product atau User).
- **Content-Type:** `application/json`

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
    "type": "media_attachment",
    "attributes": {
      "media_id": "uuid-media-123",
      "entity_type": "product",
      "entity_id": "uuid-product-999",
      "collection_name": "gallery"
    }
  }
}
```

#### Respons

**Sukses (200):**
```json
{
  "meta": { "message": "Media attached successfully" }
}
```

---

### 2.3 Hapus Media (Delete)

- **URL:** `DELETE /:id`
- **Deskripsi:** Menghapus file dari storage dan record database.

#### Respons

**Sukses (200):**
```json
{
  "meta": { "message": "Media deleted successfully" }
}
```
