# TESTING DOCUMENTATION: Role & Permission Management

> Dokumen ini berisi rencana dan skenario pengujian untuk fitur Role & Permission Management pada modul IAM & Security.

---

## Header & Navigation

- [Back to Module Overview](../../modules/iam-security/overview.md)
- [Link to Feature Specification](../../modules/iam-security/role-permission-management.md)
- [Link to API Specification](../../api/iam-security/api-role-permission-management.md)

---

## 1. Test Overview

- **Module Name:** IAM & Security - Role & Permission Management
- **Scope of Testing:** Role creation, Permission assignment, RBAC enforcement, Monkey Testing.


---

## 2. Test Strategy

### 2.1 Unit Testing
- **Coverage Target:** >80%
- **Critical Components:** RBAC Middleware/Guard.

### 2.2 Integration Testing
- **API Endpoints:**
  - `GET /roles`
  - `POST /roles`
  - `GET /permissions`
- **Database Interactions:**
  - Verify role-permission mapping.

### 2.3 End-to-End (E2E) Testing
- **Key User Flows:**
  - Admin assigns "Editor" to User A -> User A logs in -> User A accesses Editor-only menu.

### 2.4 Monkey Testing
- **Objective:** Ensure RBAC stability under chaotic conditions.
- **Approach:** Fuzzing ID parameters, Role names, and concurrent assignment.

---

## 3. Test Scenarios (Backend / API)

### 3.1 Positive Cases (Happy Paths)
> Skenario sukses sesuai dengan alur bisnis yang diharapkan.

| ID           | Test Case                         | Pre-condition                  | Input Data               | Expected Result       | Priority |
| :----------- | :-------------------------------- | :----------------------------- | :----------------------- | :-------------------- | :------- |
| RBAC-POS-001 | **Create New Role**               | Admin Token                    | Role Name: "SuperEditor" | 201 Created           | High     |
| RBAC-POS-002 | **Assign Permission to Role**     | Admin Token, Role & Perm exist | RoleID, PermissionID     | 200 OK                | High     |
| RBAC-POS-003 | **Access Endpoint as Authorized** | User assigned Role X           | -                        | 200 OK / Success      | High     |
| RBAC-POS-004 | **List All Roles**                | Admin Token                    | -                        | 200 OK, List Returned | Medium   |
| RBAC-POS-005 | **Unassign Permission**           | Permission currently assigned  | RoleID, PermissionID     | 200 OK, Removed       | Medium   |
| RBAC-POS-006 | **Update Role**                   | Role Exists                    | Different Name/Desc      | 200 OK, Updated       | Medium   |
| RBAC-POS-007 | **Delete Role**                   | Role Unused                    | Target RoleID            | 204 No Content        | High     |

### 3.2 Negative Cases (Validation Rules)
> Skenario gagal untuk memvalidasi penanganan error dan input tidak valid.

| ID           | Test Case                          | Pre-condition                 | Input Data                  | Expected Result                    | Priority |
| :----------- | :--------------------------------- | :---------------------------- | :-------------------------- | :--------------------------------- | :------- |
| RBAC-NEG-001 | **Create Duplicate Role**          | Role "Editor" exists          | Role Name: "Editor"         | 409 Conflict                       | High     |
| RBAC-NEG-002 | **Access Endpoint Unauthorized**   | User lacks Role X             | -                           | 403 Forbidden                      | High     |
| RBAC-NEG-003 | **Assign Non-existent Permission** | -                             | Valid Role, Invalid Perm ID | 404 Not Found (or 400)             | Medium   |
| RBAC-NEG-004 | **Delete Role in Use**             | Role assigned to active users | Role ID                     | 409 Conflict (Must unassign first) | Medium   |

### 3.3 Monkey Tests (Chaos & Stability)
> Pengujian dengan input acak/kacau untuk menguji ketahanan sistem.

| ID           | Test Case                       | Approach                     | Input Data               | Expected Result                  | Priority |
| :----------- | :------------------------------ | :--------------------------- | :----------------------- | :------------------------------- | :------- |
| RBAC-MNK-001 | **Role Name Fuzzing**           | Max Length & Special Chars   | Name: 1000 chars, Emojis | 400 Bad Request / Truncated      | Low      |
| RBAC-MNK-002 | **Circular Assignment Probe**   | (If hierarchy exists)        | A->B, B->A               | 400 Bad Request (Cycle detected) | Low      |
| RBAC-MNK-003 | **Privilege Escalation Attack** | Regular User calls Admin API | POST /roles              | 403 Forbidden                    | Critical |
| RBAC-MNK-004 | **ID Fuzzing in URL**           | Random UUID/String in params | GET /roles/abc-xyz       | 400 Bad Request / 404 Not Found  | Low      |

---

## 4. Frontend Testing Scenarios

### 4.1 Component / Unit Testing

| ID          | Component       | Test Case                | Expected Behavior                               |
| :---------- | :-------------- | :----------------------- | :---------------------------------------------- |
| RBAC-FE-001 | RoleManager     | Add duplicate permission | Prevent selection or show "Already added"       |
| RBAC-FE-002 | PermissionGuard | User lacks permission    | Component returns null or Redirects to 403 Page |

### 4.2 E2E Testing (User Flows)

| ID           | Flow Name                | Steps                                                    | Expected Outcome                 |
| :----------- | :----------------------- | :------------------------------------------------------- | :------------------------------- |
| RBAC-E2E-001 | **Role Assignment Flow** | 1. Admin assigns "Editor" to User A<br>2. User A logs in | User A can see Editor-only menus |

---

## 5. Manual Testing Scenarios

| ID           | Scenario        | Steps                     | Expected Result                           |
| :----------- | :-------------- | :------------------------ | :---------------------------------------- |
| RBAC-MAN-001 | Menu Visibility | Log in as different roles | Menu items appear/disappear based on role |

---

## 6. Security Testing

- **Privilege Escalation:** Ensure regular users cannot assign Admin role to themselves.
- **Vertical Access Control:** Verify Admin only endpoints are sealed.
- **Horizontal Access Control:** Verify users cannot edit roles they don't manage (if tenancy exists).
