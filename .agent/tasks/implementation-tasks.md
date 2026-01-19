# Daftar Tugas Implementasi (Implementation Tasks)

Dokumen ini berisi gabungan seluruh tugas implementasi dari modul-modul sistem. Gunakan ini sebagai *backlog* utama untuk pengembangan.

---

## Modul: IAM & Security (`iam-security`)

**Deskripsi:** Modul IAM & Security bertanggung jawab atas manajemen identitas pengguna, otentikasi (verifikasi identitas), dan otorisasi (hak akses) dalam sistem.

**Dokumentasi:**
- [Overview](../documents/application/modules/iam-security/overview.md)
- [API Spec](../documents/application/api/iam-security/api-authentication.md)
- [Testing](../documents/application/testing/iam-security/test-authentication.md)

### Backend

| ID Tugas  | Component  | Status | Deskripsi                                                       |
| :-------- | :--------- | :----- | :-------------------------------------------------------------- |
| IAM-BE-01 | Migration  | Todo   | Create `users` table with standard timestamps & soft deletes.   |
| IAM-BE-02 | Seeder     | Todo   | Create `UserSeeder` for Admin & Public role.                    |
| IAM-BE-03 | Model      | Todo   | Setup `User` model arguments (fillable, cast, relations).       |
| IAM-BE-04 | Repository | Todo   | Implement `UserRepository` (Interface & Implementation).        |
| IAM-BE-05 | Service    | Todo   | Implement `UserService` (Business Logic for Suspend/Activate).  |
| IAM-BE-06 | Controller | Todo   | Implement `UserController` (Resource Controller) with JSON:API. |
| IAM-BE-07 | Routes     | Todo   | Register routes in `api.php`.                                   |
| IAM-BE-08 | Tests      | Todo   | Create Feature Test (Positive/Negative/Permission).             |

### Frontend

| ID Tugas  | Component   | Status | Deskripsi                                              |
| :-------- | :---------- | :----- | :----------------------------------------------------- |
| IAM-FE-01 | State       | Todo   | Setup Pinia Store / Context for User.                  |
| IAM-FE-02 | API Service | Todo   | Create `UserService` (Axios wrapper).                  |
| IAM-FE-03 | Component   | Todo   | Create `UserListTable` component.                      |
| IAM-FE-04 | Component   | Todo   | Create `UserForm` component (Add/Edit).                |
| IAM-FE-05 | Page        | Todo   | Implement `UserIndex`, `UserCreate`, `UserEdit` pages. |
| IAM-FE-06 | Integration | Todo   | Connect UI to API & Handle errors.                     |

---

## Modul: Taxonomy (`taxonomy`)

**Deskripsi:** Modul Taxonomy menyediakan sistem klasifikasi terpusat.

**Dokumentasi:**
- [Overview](../documents/application/modules/taxonomy/overview.md)
- [API Spec](../documents/application/api/taxonomy/api-taxonomy.md)
- [Testing](../documents/application/testing/taxonomy/test-taxonomy.md)

### Backend

| ID Tugas  | Component  | Status | Deskripsi                                               |
| :-------- | :--------- | :----- | :------------------------------------------------------ |
| TAX-BE-01 | Migration  | Todo   | Create `taxonomies` and `terms` tables with indices.    |
| TAX-BE-02 | Migration  | Todo   | Create `entity_terms` polymorphic pivot table.          |
| TAX-BE-03 | Model      | Todo   | Setup `Taxonomy` & `Term` models with relations.        |
| TAX-BE-04 | Service    | Todo   | Implement `TaxonomyService` (CRUD & Attachment Logic).  |
| TAX-BE-05 | Controller | Todo   | Implement `TaxonomyController` with JSON:API standards. |
| TAX-BE-06 | Routes     | Todo   | Register API routes.                                    |
| TAX-BE-07 | Tests      | Todo   | Create Unit & Feature tests for Taxonomy ops.           |

### Frontend

| ID Tugas  | Component   | Status | Deskripsi                                 |
| :-------- | :---------- | :----- | :---------------------------------------- |
| TAX-FE-01 | State       | Todo   | Setup Taxonomy Store (Pinia/Context).     |
| TAX-FE-02 | API         | Todo   | Create `TaxonomyService` wrapper.         |
| TAX-FE-03 | Component   | Todo   | Create `TaxonomyManager` UI for Admin.    |
| TAX-FE-04 | Component   | Todo   | Create reusable `TagInput` component.     |
| TAX-FE-05 | Integration | Todo   | Integrate `TagInput` into existing forms. |

---

## Modul: Background Jobs (`background-jobs`)

**Deskripsi:** Modul Background Jobs bertanggung jawab untuk menangani pemrosesan tugas asinkron.

**Dokumentasi:**
- [Overview](../documents/application/modules/background-jobs/overview.md)
- [API Spec](../documents/application/api/background-jobs/api-background-jobs.md)
- [Testing](../documents/application/testing/background-jobs/test-background-jobs.md)

### Backend

| ID Tugas  | Component      | Status | Deskripsi                                       |
| :-------- | :------------- | :----- | :---------------------------------------------- |
| JOB-BE-01 | Infrastructure | Todo   | Setup Queue Driver (Redis/Bull) & Config.       |
| JOB-BE-02 | Service        | Todo   | Implement `JobService` (Dispatch logic).        |
| JOB-BE-03 | Worker         | Todo   | Implement Base Worker Class & Error Handling.   |
| JOB-BE-04 | Worker         | Todo   | Create `MonthlyReportJob` (Scheduled).          |
| JOB-BE-05 | Worker         | Todo   | Create `DataExportJob` (Long running).          |
| JOB-BE-06 | Dashboard      | Todo   | Setup Bull Board (Authentication middleware).   |
| JOB-BE-07 | Routes         | Todo   | Register API logs inspection routes (Optional). |
| JOB-BE-08 | Tests          | Todo   | Create job processing tests.                    |

### Frontend

| ID Tugas  | Component    | Status | Deskripsi                                          |
| :-------- | :----------- | :----- | :------------------------------------------------- |
| JOB-FE-01 | Monitoring   | Todo   | Integrate Bull Board into Admin Panel iframe/link. |
| JOB-FE-02 | Notification | Todo   | Handle "Export Ready" notification.                |

---

## Modul: Media Management (`media-management`)

**Deskripsi:** Modul Media Management menyediakan layanan terpusat untuk menangani pengunggahan aset digital.

**Dokumentasi:**
- [Overview](../documents/application/modules/media-management/overview.md)
- [API Spec](../documents/application/api/media-management/api-media-management.md)
- [Testing](../documents/application/testing/media-management/test-media-management.md)

### Backend

| ID Tugas  | Component      | Status | Deskripsi                                            |
| :-------- | :------------- | :----- | :--------------------------------------------------- |
| MED-BE-01 | Infrastructure | Todo   | Setup Flysystem (S3/Local) & Multer/Formidable.      |
| MED-BE-02 | Migration      | Todo   | Create `media` and `media_attachments` tables.       |
| MED-BE-03 | Model          | Todo   | Setup `Media` model with accessors (URL).            |
| MED-BE-04 | Service        | Todo   | Implement `MediaService` (Upload, Optimize, Delete). |
| MED-BE-05 | Controller     | Todo   | Implement `MediaController` (Upload Endpoint).       |
| MED-BE-06 | Routes         | Todo   | Register API routes.                                 |
| MED-BE-07 | Tests          | Todo   | Create Upload & Storage Tests.                       |

### Frontend

| ID Tugas  | Component | Status | Deskripsi                                      |
| :-------- | :-------- | :----- | :--------------------------------------------- |
| MED-FE-01 | Component | Todo   | Create `FileUploader` (Drag & Drop, Progress). |
| MED-FE-02 | Component | Todo   | Create `ImageGallery` selector.                |
| MED-FE-03 | Service   | Todo   | Create `MediaService` api wrapper.             |

---

## Modul: Configuration (`configuration`)

**Deskripsi:** Modul Configuration menyediakan mekanisme terpusat untuk mengelola pengaturan sistem.

**Dokumentasi:**
- [Overview](../documents/application/modules/configuration/overview.md)
- [API Spec](../documents/application/api/configuration/api-configurations.md)
- [Testing](../documents/application/testing/configuration/overview.md)

### Backend

| ID Tugas  | Component  | Status | Deskripsi                                                    |
| :-------- | :--------- | :----- | :----------------------------------------------------------- |
| CFG-BE-01 | Migration  | Todo   | Create `configurations` and `configuration_audits` tables.   |
| CFG-BE-02 | Model      | Todo   | Setup `Configuration` model (Casts, Auditable).              |
| CFG-BE-03 | Service    | Todo   | Implement `ConfigService` with Redis Caching (Read-through). |
| CFG-BE-04 | Controller | Todo   | Implement `ConfigController` (CRUD Admin & Public Fetch).    |
| CFG-BE-05 | Routes     | Todo   | Register API routes.                                         |
| CFG-BE-06 | Tests      | Todo   | Create Unit Test (Cache Logic) and Feature Test.             |

### Frontend

| ID Tugas  | Component   | Status | Deskripsi                                           |
| :-------- | :---------- | :----- | :-------------------------------------------------- |
| CFG-FE-01 | API Service | Todo   | Create `ConfigService` wrapper.                     |
| CFG-FE-02 | State       | Todo   | Setup Global Config Store (Pinia) to fetch on boot. |
| CFG-FE-03 | Page        | Todo   | Create Admin `ConfigManager` page.                  |
| CFG-FE-04 | Component   | Todo   | Create `ConfigEditor` form with type validation.    |

---

## Modul: Notification (`notification`)

**Deskripsi:** Modul Notification mengelola dan mengirimkan pesan kepada pengguna melalui berbagai saluran (Email, Push, SMS, In-App) dengan pengelolaan template terpusat.

**Dokumentasi:**
- [Overview](../documents/application/modules/notification/overview.md)
- [API Spec](../documents/application/api/notification/api-notifications.md)
- [Testing](../documents/application/testing/notification/test-notification.md)

### Backend

| ID Tugas  | Component  | Status | Deskripsi                                                                    |
| :-------- | :--------- | :----- | :--------------------------------------------------------------------------- |
| NOT-BE-01 | Migration  | Todo   | Create `notifications` table (UUID, polymorphic) & `notification_templates`. |
| NOT-BE-02 | Seeder     | Todo   | Seed default templates (OTP, Welcome Email).                                 |
| NOT-BE-03 | Model      | Todo   | Setup `Notification` model with cast & relationships.                        |
| NOT-BE-04 | Service    | Todo   | Implement `NotificationService` (Channel Strategy Pattern).                  |
| NOT-BE-05 | Queue      | Todo   | Setup `SendNotificationJob` with retry logic.                                |
| NOT-BE-06 | Controller | Todo   | Implement `NotificationController` (Send, List, Mark Read).                  |
| NOT-BE-07 | Routes     | Todo   | Register API routes.                                                         |
| NOT-BE-08 | Tests      | Todo   | Create Feature Test (Mocking Providers).                                     |

### Frontend

| ID Tugas  | Component   | Status | Deskripsi                                              |
| :-------- | :---------- | :----- | :----------------------------------------------------- |
| NOT-FE-01 | State       | Todo   | Setup Notification Store (Pinia) & WebSocket listener. |
| NOT-FE-02 | API Service | Todo   | Create `NotificationService` (Axios wrapper).          |
| NOT-FE-03 | Component   | Todo   | Create `NotificationBell` with badge count.            |
| NOT-FE-04 | Component   | Todo   | Create `NotificationList` dropdown/page.               |
| NOT-FE-05 | Integration | Todo   | Connect Bell & List to Realtime Store.                 |
