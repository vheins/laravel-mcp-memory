# TESTING OVERVIEW: IAM & Security

> Dokumen ini memberikan ringkasan cakupan pengujian untuk modul IAM & Security.

---

## Header & Navigation

- [Back to Module Overview](../../modules/iam-security/overview.md)

---

## Testing Scope

Modul ini mencakup pengujian untuk:

1. **Authentication**
   - [Lihat Rencana Tes](./test-authentication.md)
   - Login, Register, Forgot Password.

2. **User Management**
   - [Lihat Rencana Tes](./test-user-management.md)
   - CRUD User, Profiling.

3. **Role & Permission Management**
   - [Lihat Rencana Tes](./test-role-permission-management.md)
   - RBAC, Assignment Role.

---

## General Strategy

- **Unit Testing:** Wajib untuk semua business logic.
- **Integration Testing:** Wajib untuk semua API Endpoints.
- **Security Testing:** Kritis untuk modul ini (Injection, Auth Bypass).
