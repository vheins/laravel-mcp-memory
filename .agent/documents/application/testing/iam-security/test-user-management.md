# TESTING DOCUMENTATION: User Management

> Dokumen ini berisi rencana dan skenario pengujian untuk fitur User Management pada modul IAM & Security.

---

## Header & Navigation

- [Back to Module Overview](../../modules/iam-security/overview.md)
- [Link to Feature Specification](../../modules/iam-security/user-management.md)
- [Link to API Specification](../../api/iam-security/api-user-management.md)

---

## 1. Test Overview

- **Module Name:** IAM & Security - User Management
- **Scope of Testing:** CRUD operations for users, Profile management, Monkey Testing.


---

## 2. Test Strategy

### 2.1 Unit Testing
- **Coverage Target:** >80%
- **Critical Components:** User Service, Profile Validator.

### 2.2 Integration Testing
- **API Endpoints:**
  - `GET /users` (List Users)
  - `GET /users/:id` (Get User)
  - `PATCH /users/:id` (Update User)
  - `DELETE /users/:id` (Delete User)

### 2.3 End-to-End (E2E) Testing
- **Key User Flows:**
  - Admin deletes a user -> User can no longer login.
  - User updates profile -> Changes reflected in UI.

### 2.4 Monkey Testing
- **Objective:** Test robustness against malformed profile data and ID guessing.
- **Approach:** Fuzzing profile fields, random ID requests.

---

## 3. Test Scenarios (Backend / API)

### 3.1 Positive Cases (Happy Paths)
> Skenario sukses sesuai dengan alur bisnis yang diharapkan.

| ID          | Test Case                   | Pre-condition | Input Data                       | Expected Result              | Priority |
| :---------- | :-------------------------- | :------------ | :------------------------------- | :--------------------------- | :------- |
| USR-POS-001 | **Get User List (Admin)**   | Admin Token   | `page[size]=10`                  | 200 OK, List of 10 users     | High     |
| USR-POS-002 | **Get User Profile (Self)** | User Token    | -                                | 200 OK, Own details returned | High     |
| USR-POS-003 | **Update Own Profile**      | User Token    | Bio: "New Bio", Name: "New Name" | 200 OK, Details updated      | Medium   |
| USR-POS-004 | **Delete User (Admin)**     | Admin Token   | Target User ID                   | 204 No Content / 200 OK      | High     |
| USR-POS-005 | **Search User (Audit)**     | Admin Token   | `filter[status]=SUSPENDED`       | 200 OK, Only Suspended users | Medium   |
| USR-POS-006 | **Block User (Suspend)**    | Admin Token   | Update Status -> SUSPENDED       | 200 OK, User cannot login    | High     |
| USR-POS-007 | **Re-activate User**        | Admin Token   | Update Status -> ACTIVE          | 200 OK, User can login       | High     |

### 3.2 Negative Cases (Validation Rules)
> Skenario gagal untuk memvalidasi penanganan error dan input tidak valid.

| ID          | Test Case                       | Pre-condition          | Input Data                 | Expected Result                   | Priority |
| :---------- | :------------------------------ | :--------------------- | :------------------------- | :-------------------------------- | :------- |
| USR-NEG-001 | **Access List as Regular User** | User Token (Non-Admin) | -                          | 403 Forbidden                     | High     |
| USR-NEG-002 | **Update Other User (IDOR)**    | User A Token           | ID: User_B_ID, Bio: "Hack" | 403 Forbidden / 404 Not Found     | High     |
| USR-NEG-003 | **Update Invalid Email Format** | User Token             | Email: "invalid_format"    | 422 Unprocessable Entity          | Medium   |
| USR-NEG-004 | **Delete Self (Admin)**         | Admin Token            | ID: Own_Admin_ID           | 400 Bad Request (Prevent suicide) | Low      |
| USR-NEG-005 | **Get Non-existent User**       | Admin Token            | ID: Random UUID            | 404 Not Found                     | Medium   |
| USR-NEG-006 | **Blocked User Login**          | User Suspended         | Valid Credentials          | 403 Forbidden (Account Suspended) | High     |

### 3.3 Monkey Tests (Chaos & Stability)
> Pengujian dengan input acak/kacau untuk menguji ketahanan sistem.

| ID          | Test Case                 | Approach               | Input Data                                | Expected Result                     | Priority |
| :---------- | :------------------------ | :--------------------- | :---------------------------------------- | :---------------------------------- | :------- |
| USR-MNK-001 | **Bio Field Fuzzing**     | Max Length & Injection | Bio: 5000 chars, `<script>alert</script>` | 400/422 (Too long) or Sanitized     | Low      |
| USR-MNK-002 | **ID Enumeration (UUID)** | Random UUID probing    | GET /users/{random-uuid}                  | 404 Not Found (Should not crash)    | Low      |
| USR-MNK-003 | **Concurrent Updates**    | Race Condition Check   | 2 Requests updating same user diff fields | Last write wins OR Version Conflict | Low      |
| USR-MNK-004 | **Pagination Bomb**       | Request huge page size | `page[size]=1000000`                      | 422 Error or Cap at max (e.g. 100)  | Medium   |

---

## 4. Frontend Testing Scenarios

### 4.1 Component / Unit Testing

| ID         | Component    | Test Case                | Expected Behavior                                 |
| :--------- | :----------- | :----------------------- | :------------------------------------------------ |
| USR-FE-001 | UserTable    | Render list of users     | Table shows columns: Name, Email, Status, Actions |
| USR-FE-002 | UserProfile  | Edit mode toggle         | Clicking "Edit" enables form fields               |
| USR-FE-003 | AvatarUpload | Upload invalid file type | Show error "Images only"                          |

### 4.2 E2E Testing (User Flows)

| ID          | Flow Name              | Steps                                                             | Expected Outcome                                |
| :---------- | :--------------------- | :---------------------------------------------------------------- | :---------------------------------------------- |
| USR-E2E-001 | **Admin Deletes User** | 1. Admin Login<br>2. Go to User List<br>3. Click Delete on User X | User X removed from list, Success toast appears |

---

## 5. Manual Testing Scenarios

| ID          | Scenario           | Steps                           | Expected Result                                  |
| :---------- | :----------------- | :------------------------------ | :----------------------------------------------- |
| USR-MAN-001 | Pagination UI      | Navigate through pages of users | Correct data loads, Previous/Next buttons work   |
| USR-MAN-002 | Profile Image Crop | Upload image, try cropping      | Crop tool works smoothly, final image is cropped |

---

## 6. Security Testing

- **Authorization:** Ensure users cannot update other users' profiles (IDOR check).
- **Access Control:** Ensure only Admins can list all users or delete users.
- **Data Leakage:** Profile endpoint should not return password hashes or sensitive internal flags.
