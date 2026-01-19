# TESTING DOCUMENTATION: Authentication

> Dokumen ini berisi rencana dan skenario pengujian untuk fitur Authentication pada modul IAM & Security.

---

## Header & Navigation

- [Back to Module Overview](../../modules/iam-security/overview.md)
- [Link ke Feature Specification](../../modules/iam-security/authentication.md)
- [Link ke API Specification](../../api/iam-security/api-authentication.md)

---

## 1. Test Overview

- **Module Name:** IAM & Security - Authentication
- **Scope of Testing:** Unit Testing (Services), Integration Testing (API Endpoints), E2E (Login/Register Flows), Security Testing, Monkey Testing.


---

## 2. Test Strategy

### 2.1 Unit Testing
- **Coverage Target:** >80%
- **Critical Components:**
  - Password Hashing (Bcrypt/Argon2)
  - JWT Generation & Verification
  - Input Validation Logic

### 2.2 Integration Testing
- **API Endpoints:**
  - `POST /auth/login` (Login)
  - `POST /auth/register` (Register)
  - `POST /auth/forgot-password` (Forgot Password)
- **Database Interactions:**
  - Verify user creation
  - Verify token storage (refresh tokens)

### 2.3 End-to-End (E2E) Testing
- **Key User Flows:**
  - User Registration -> Login -> Access Protected Route
  - Forgot Password -> Email Link -> Reset Password -> Login

### 2.4 Monkey Testing
- **Objective:** Test system stability under chaotic/random inputs.
- **Approach:** Fuzz testing API endpoints and random UI interactions.

---

## 3. Test Scenarios (Backend / API)

### 3.1 Positive Cases (Happy Paths)
> Skenario sukses sesuai dengan alur bisnis yang diharapkan.

| ID           | Test Case                        | Pre-condition          | Input Data                                           | Expected Result                         | Priority |
| :----------- | :------------------------------- | :--------------------- | :--------------------------------------------------- | :-------------------------------------- | :------- |
| AUTH-POS-001 | **Register New User**            | Email belum terdaftar  | Valid Name, Email, Password (>8 chars, alphanumeric) | 201 Created, User Data returned         | High     |
| AUTH-POS-002 | **Login with Valid Credentials** | User terdaftar & aktif | Valid Email & Correct Password                       | 200 OK, Access + Refresh Token returned | High     |
| AUTH-POS-003 | **Token Refresh**                | Refresh Token valid    | Valid Refresh Token                                  | 200 OK, New Access Token returned       | High     |
| AUTH-POS-004 | **Forgot Password Request**      | User terdaftar         | Valid Registered Email                               | 202 Accepted, Reset Email sent          | High     |
| AUTH-POS-005 | **Reset Password Success**       | Token reset valid      | Valid Token, New Password                            | 200 OK, Password Updated                | High     |
| AUTH-POS-006 | **Logout Success**               | Login & punya token    | Valid Refresh Token                                  | 200 OK, Refesh Token di-revoke          | Medium   |
| AUTH-POS-007 | **Activate Account**             | Token email valid      | Valid Activation Token                               | 200 OK, User Status -> ACTIVE           | High     |
| AUTH-POS-008 | **Change Password**              | User Login             | Old Pass & New Pass                                  | 204 No Content, Password Updated        | High     |

### 3.2 Negative Cases (Validation Rules)
> Skenario gagal untuk memvalidasi penanganan error dan input tidak valid.

| ID           | Test Case                         | Pre-condition         | Input Data                      | Expected Result                                            | Priority |
| :----------- | :-------------------------------- | :-------------------- | :------------------------------ | :--------------------------------------------------------- | :------- |
| AUTH-NEG-001 | **Register Existing Email**       | Email sudah terdaftar | Valid Data, Existing Email      | 409 Conflict, Error "Email already exists"                 | High     |
| AUTH-NEG-002 | **Register Weak Password**        | -                     | Password "12345" (short/simple) | 422 Unprocessable Entity, Error validation                 | Medium   |
| AUTH-NEG-003 | **Register Invalid Email Format** | -                     | Email "user.com" (no @)         | 422 Unprocessable Entity, Error validation                 | Medium   |
| AUTH-NEG-004 | **Login Wrong Password**          | User terdaftar        | Valid Email, Wrong Password     | 401 Unauthorized, Error "Invalid credentials"              | High     |
| AUTH-NEG-005 | **Login Unregistered Email**      | -                     | Random Email, Random Password   | 401 Unauthorized, Error "Invalid credentials" (No leakage) | High     |
| AUTH-NEG-006 | **Reset Password Expired Token**  | Token expired         | Expired Token, New Password     | 400 Bad Request, Error "Token expired"                     | High     |
| AUTH-NEG-007 | **Reset Password Invalid Token**  | -                     | Random String Token             | 400 Bad Request, Error "Invalid token"                     | Medium   |
| AUTH-NEG-008 | **Login Missing Fields**          | -                     | Empty Body / Partial Fields     | 400 Bad Request, Validation Error                          | Medium   |
| AUTH-NEG-009 | **Activate Invalid Token**        | -                     | Random/Expired Token            | 400 Bad Request / 404 Not Found                            | High     |
| AUTH-NEG-010 | **Change Pass Wrong Old Pass**    | User Login            | Wrong Old Pass                  | 401 Unauthorized / 400 Bad Request                         | High     |
| AUTH-NEG-011 | **Change Pass Same as Old**       | User Login            | New Pass = Old Pass             | 400 Bad Request (Security Best Practice)                   | Low      |

### 3.3 Monkey Tests (Chaos & Stability)
> Pengujian dengan input acak/kacau untuk menguji ketahanan sistem (Crash Free Users).

| ID           | Test Case                   | Approach                                        | Input Data                                 | Expected Result                                      | Priority |
| :----------- | :-------------------------- | :---------------------------------------------- | :----------------------------------------- | :--------------------------------------------------- | :------- |
| AUTH-MNK-001 | **Fuzzing Register Fields** | Kirim random byte/string panjang di semua field | Name: 10k chars, Email: Emojis, Pass: Null | 400/422 Error, Tidak 500/Crash                       | Low      |
| AUTH-MNK-002 | **Rapid Fire Login (Spam)** | Hit endpoint login 100x dalam 1 detik           | Valid & Invalid Creds concurrently         | 429 Too Many Requests (Rate Limit)                   | Medium   |
| AUTH-MNK-003 | **Token Tampering**         | Ubah 1 karakter di JWT string                   | Malformed JWT Token                        | 401/403 Unauthorized, Signature verification failure | High     |
| AUTH-MNK-004 | **SQL Injection Probe**     | Coba payload SQLi di field login                | Email: `' OR 1=1;--`                       | 400/401/422, Query tidak tereksekusi                 | Critical |
| AUTH-MNK-005 | **Payload Type Mismatch**   | Kirim tipe data salah                           | Email: `123` (int), Password: `{}` (obj)   | 400 Bad Request, Validation Error                    | Low      |

---

## 4. Frontend Testing Scenarios

### 4.1 Component / Unit Testing

| ID          | Component    | Test Case            | Expected Behavior                       |
| :---------- | :----------- | :------------------- | :-------------------------------------- |
| AUTH-FE-001 | LoginForm    | Submit empty form    | Display "Email/Password required" error |
| AUTH-FE-002 | LoginForm    | Submit invalid email | Display "Invalid email format" error    |
| AUTH-FE-003 | RegisterForm | Password mismatch    | Display "Passwords do not match" error  |

### 4.2 E2E Testing (User Flows)

| ID           | Flow Name                  | Steps                                                   | Expected Outcome                          |
| :----------- | :------------------------- | :------------------------------------------------------ | :---------------------------------------- |
| AUTH-E2E-001 | **Login Success Flow**     | 1. Go to Login<br>2. Fill Valid Email/Pass<br>3. Submit | Redirect to Dashboard, Token saved        |
| AUTH-E2E-002 | **Error Handling Display** | 1. Go to Login<br>2. Fill Wrong Pass<br>3. Submit       | Show "Invalid email or password" snackbar |

---

## 5. Security Testing

- **Password Storage:** Verify Database stores Hashed Password (not Plaintext).
- **No Sensitive Data Leaks:** Endpoint responses never contain password/hash.
- **Rate Limit:** 5 gagal login -> Block IP sementara (jika ada fitur ini).

---

## 6. Test Data References

- **Valid User:** `testuser@example.com` / `P@ssw0rd123`
- **Locked User:** `locked@example.com`
- **Expired Token:** `eyJhbGc...` (pre-generated expired JWT)
