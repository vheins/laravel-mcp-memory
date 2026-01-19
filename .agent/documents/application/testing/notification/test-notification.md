# TESTING DOCUMENTATION: Notification Module

> Dokumen ini berisi rencana dan skenario pengujian untuk fitur Notification (Email, Push, In-App).

---

## Header & Navigation

- [Back to Module Overview](../../modules/notification/overview.md)
- [Link to API Specification](../../api/notification/api-notifications.md)

---

## 1. Test Overview

- **Module Name:** Notification System
- **Scope of Testing:** Sending notifications (Email, Push), Template rendering, Queue processing.


---

## 2. Test Strategy

### 2.1 Unit Testing
- **Coverage Target:** >80%
- **Critical Components:** Template Engine, Provider Factories (Email/Push adapters).

### 2.2 Integration Testing
- **API Endpoints:**
  - `POST /notifications/send`
  - `GET /notifications`
- **Infrastructure:**
  - Redis Queue (Job Enqueueing).

### 2.3 End-to-End (E2E) Testing
- **Key User Flows:**
  - Trigger Action -> Notification Sent -> Received in Mock/Inbox.

### 2.4 Monkey Testing
- **Objective:** Test queue resilience and provider failure handling.
- **Approach:** Simulate provider downtimes, spam requests.

---

## 3. Test Scenarios (Backend / API)

### 3.1 Positive Cases (Happy Paths)
> Skenario sukses sesuai dengan alur bisnis yang diharapkan.

| ID          | Test Case                   | Pre-condition       | Input Data                     | Expected Result                             | Priority |
| :---------- | :-------------------------- | :------------------ | :----------------------------- | :------------------------------------------ | :------- |
| NOT-POS-001 | **Send Email Successfully** | Valid Token         | `type: email`, `template: OTP` | 202 Accepted, Job ID returned               | High     |
| NOT-POS-002 | **List Notifications**      | User has notifs     | `page[size]=10`                | 200 OK, List of 10 items                    | High     |
| NOT-POS-003 | **Mark as Read**            | User has unread     | `is_read: true`                | 200 OK, attribute updated                   | Medium   |
| NOT-POS-004 | **Send to Opt-out User**    | User disabled email | `type: email`                  | 202 Accepted (Job handled silently/skipped) | Low      |

### 3.2 Negative Cases (Validation Rules)
> Skenario gagal untuk memvalidasi penanganan error dan input tidak valid.

| ID          | Test Case                      | Pre-condition | Input Data               | Expected Result               | Priority |
| :---------- | :----------------------------- | :------------ | :----------------------- | :---------------------------- | :------- |
| NOT-NEG-001 | **Send with Invalid Template** | Valid Token   | `template: INVALID_CODE` | 422 Unprocessable Entity      | Medium   |
| NOT-NEG-002 | **Send Missing Recipient**     | -             | `to: null`               | 400 Bad Request               | High     |
| NOT-NEG-003 | **Get Notification of Others** | User A Token  | ID: Notif_User_B         | 403 Forbidden / 404 Not Found | High     |

### 3.3 Monkey Tests (Chaos & Stability)
> Pengujian dengan input acak/kacau untuk menguji ketahanan sistem.

| ID          | Test Case              | Approach                    | Input Data                             | Expected Result                                | Priority |
| :---------- | :--------------------- | :-------------------------- | :------------------------------------- | :--------------------------------------------- | :------- |
| NOT-MNK-001 | **Template Injection** | Inject logic code in params | `data: { "name": "{{ system.env }}" }` | Rendered literal / Sanitized (No Code Exec)    | Critical |
| NOT-MNK-002 | **Queue Flooding**     | Send 1000 requests/sec      | Valid Payload                          | 202 Accepted (Queue fills), Workers auto-scale | High     |
| NOT-MNK-003 | **Huge Payload**       | Send large body text        | Body: 10MB Lorem Ipsum                 | 413 Payload Too Large / 400                    | Medium   |

---

## 4. Frontend Testing Scenarios

### 4.1 Component / Unit Testing

| ID         | Component        | Test Case           | Expected Behavior                  |
| :--------- | :--------------- | :------------------ | :--------------------------------- |
| NOT-FE-001 | NotificationItem | Render Unread State | Background highlighted / Bold text |
| NOT-FE-002 | BellIcon         | Count > 99          | Show "99+" badge                   |

### 4.2 E2E Testing (User Flows)

| ID          | Flow Name              | Steps                          | Expected Outcome                      |
| :---------- | :--------------------- | :----------------------------- | :------------------------------------ |
| NOT-E2E-001 | View Notification List | Click Bell Icon                | Dropdown opens, list loads            |
| NOT-E2E-002 | Realtime Update        | Trigger BE API -> Socket Event | Badge count increases without refresh |

---

## 5. Manual Testing Scenarios

| ID          | Scenario                  | Steps               | Expected Result                     |
| :---------- | :------------------------ | :------------------ | :---------------------------------- |
| NOT-MAN-001 | Push Notification Receive | Send Push to device | Notification appears in System Tray |

---

## 6. Security Testing

- **Rate Limiting:** Ensure users cannot spam email sending endpoint.
- **Access Control:** User can only see their own notifications.
