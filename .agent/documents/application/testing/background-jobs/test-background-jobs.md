# TESTING DOCUMENTATION: Background Jobs

> Dokumen ini berisi rencana dan skenario pengujian untuk fitur Background Jobs.

---

## Header & Navigation

- [Back to Module Overview](../../modules/background-jobs/overview.md)
- [Link ke API Specification](../../api/background-jobs/api-background-jobs.md)

---

## 1. Test Overview

- **Module Name:** Background Jobs Module
- **Scope of Testing:** Queue Processing, Job States (Success/Fail/Retry), Concurrency.
- **Test Tools:** Jest, BullMQ Mock, Redis Mock.

---

## 2. Test Strategy

### 2.1 Unit Testing
- **Critical Components:** Worker Processor Logic (idempotency checks).

### 2.2 Integration Testing
- **Infrastructure:** Verify Redis connection and Job enqueuing/dequeuing.

### 2.3 Monkey Testing
- **Objective:** Menguji ketahanan antrian saat load tinggi atau Redis down.

---

## 3. Test Scenarios (Backend / API)

### 3.1 Positive Cases (Happy Paths)

| ID          | Test Case           | Pre-condition     | Input Data    | Expected Result                  | Priority |
| :---------- | :------------------ | :---------------- | :------------ | :------------------------------- | :------- |
| JOB-POS-001 | **Enqueue Job**     | Redis Up          | Payload Valid | 202 Accepted, Job ID returned    | High     |
| JOB-POS-002 | **Process Success** | Job in Queue      | -             | Worker executes -> Job Completed | High     |
| JOB-POS-003 | **Scheduled Job**   | Cron Time reached | -             | Job added to queue automatically | Medium   |
| JOB-POS-004 | **Retry Job API**   | Job Failed        | Job ID        | 200 OK, Job Active again         | High     |

### 3.2 Negative Cases (Validation Rules)

| ID          | Test Case                | Pre-condition        | Input Data     | Expected Result                      | Priority |
| :---------- | :----------------------- | :------------------- | :------------- | :----------------------------------- | :------- |
| JOB-NEG-001 | **Process Fail & Retry** | Job designed to fail | -              | Job Failed -> Retried N times -> DLQ | High     |
| JOB-NEG-002 | **Processor Crash**      | Worker code Error    | -              | Worker restarts, Job marked failed   | High     |
| JOB-NEG-003 | **Get Invalid Job**      | -                    | Invalid Job ID | 404 Not Found                        | Low      |

### 3.3 Monkey Tests (Chaos & Stability)

| ID          | Test Case           | Approach                      | Input Data     | Expected Result                      | Priority |
| :---------- | :------------------ | :---------------------------- | :------------- | :----------------------------------- | :------- |
| JOB-MNK-001 | **Redis Kill**      | Matikan Redis saat processing | Pending jobs   | Worker pauses, resumes when Redis UP | Critical |
| JOB-MNK-002 | **Queue Flooding**  | Push 10k jobs                 | Simple payload | Queue handles load, no memory leak   | High     |
| JOB-MNK-003 | **Payload Fuzzing** | Kirim malformed JSON          | Random bytes   | 400 Bad Request (API level)          | Low      |

---

## 4. Security Testing

- **Access Control:** Pastikan dashboard Queue (e.g. Bull Board) diproteksi password/auth.
- **Job Isolation:** Pastikan job user A tidak bisa melihat detail job user B (jika tenant based).
- **Input Validation:** Payload injection check pada create job endpoint.

