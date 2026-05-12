# Analisis Monitoring SIMPER Permit: HRGA vs Subcon/Vendor

## 1. ROLE MAPPING & AUTHENTICATION

### HRGA (Admin Internal)
- **Authentication**: Email + Password → `users.role = 'hrga'`
- **Session Key**: `auth_user.id` (numeric user ID)
- **Session Marker**: `auth_user.vendor_type` NOT set
- **Route Access**: `Route::middleware(['legacy.admin.auth', 'legacy.password.rotation'])`

### Subcon/Vendor (Mitra)
- **Authentication**: Company Name only (passwordless when `VENDOR_PASSWORDLESS=true`)
- **Session Key**: `auth_user.id = 'vendor_' + internal_companies.id`
- **Session Marker**: `auth_user.vendor_type = 'subcon'`, `auth_user.vendor_id` (numeric)
- **Route Access**: Same as HRGA (same `legacy.admin.auth` middleware)

### Permission Mapping

```
Middleware: simper.role:hrga,subcon
├─ HRGA (admin user with role='hrga')
│  ├─ View: All submissions
│  ├─ Upload: HRGA files (KTP, MCU, SIM, Foto)
│  └─ Status: pending_hrga → pending_paramedic
│
└─ Subcon/Vendor (vendor session with vendor_type='subcon')
   ├─ View: Only own submissions (vendor_id filter)
   ├─ Upload: HRGA files for own submissions
   └─ Status: Same as HRGA
```

## 2. FILTERING LOGIC & VISIBILITY

### In SubmissionController::index()

```php
if ($isVendor && $vendorId) {
    $query->where('vendor_id', $vendorId);  // Vendor sees ONLY own submissions
}
// HRGA sees ALL submissions (no filter)
```

**Current Implementation:**
- ✅ Vendor submissions are created with `vendor_id` and `submitted_by_vendor=true`
- ✅ Vendor filtering applied to prevent cross-vendor viewing
- ✅ HRGA sees vendor submissions in monitoring list
- ✅ Audit trail shows "Vendor/Subcon" label with company name

### Database Schema

```sql
-- Submission table columns (relevant)
submissions:
  ├─ id (PK)
  ├─ category_id → SIMPER_PERMIT
  ├─ status (pending_hrga|pending_paramedic|pending_tod|pending_she|approved|rejected)
  ├─ applicant_name (employee name, not company)
  ├─ created_by → users.id (NULL for vendor)
  ├─ vendor_id → internal_companies.id (NULL for HRGA-created)
  ├─ submitted_by_vendor (boolean flag)
  └─ created_at, updated_at

-- Vendor account table
internal_companies:
  ├─ id (PK)
  ├─ group_id → internal_company_groups.id
  ├─ company_name (matching sapkon_name for SAPKON vendors)
  ├─ password_hash (NULL for passwordless SAPKON vendors)
  └─ code (SAPKON code for sync'd vendors)
```

## 3. SIMPER PERMIT WORKFLOW & STATUS TRANSITIONS

### Status Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    SIMPER PERMIT WORKFLOW                        │
└─────────────────────────────────────────────────────────────────┘

1. pending_hrga (⬜ Perlu Dilengkapi)
   ├─ Created by: HRGA staff OR Vendor/Subcon
   ├─ Action: HRGA/Vendor uploads KTP, MCU, SIM, Foto Diri
   ├─ Completion: All 4 HRGA files uploaded
   └─ Next: pending_paramedic

2. pending_paramedic (⬜ Menunggu Verifikasi Medis)
   ├─ Assigned to: Paramedic role
   ├─ Action: Verify MCU file
   ├─ Completion: Paramedic approval
   └─ Next: pending_tod

3. pending_tod (⬜ Menunggu Verifikasi Teknis)
   ├─ Assigned to: TOD role
   ├─ Action: Upload technical test results
   ├─ Completion: Technical docs uploaded
   └─ Next: pending_she

4. pending_she (⬜ Menunggu Persetujuan SHE)
   ├─ Assigned to: SHE role
   ├─ Action: Final review & approval/rejection
   ├─ Approve: → approved
   └─ Reject: → rejected (with notes)

5. approved (✅ Disetujui)
   └─ Final status (no further changes)

6. rejected (❌ Ditolak)
   └─ Requires resubmission (status stays rejected)
```

### Permission Matrix by Status & Role

| Status | HRGA | Vendor | SHE | Paramedic | TOD | Admin |
|--------|------|--------|-----|-----------|-----|-------|
| pending_hrga | Upload ✅ | Upload ✅ | View | View | View | All |
| pending_paramedic | View | View ✅ | View | Verify | View | All |
| pending_tod | View | View ✅ | View | View | Upload | All |
| pending_she | View | View ✅ | Approve/Reject | View | View | All |
| approved | View | View ✅ | View | View | View | All |
| rejected | Upload ✅ | Upload ✅ | View | View | View | All |

## 4. FILE UPLOAD PERMISSIONS

### Route Protection

```php
Route::middleware(['simper.role:hrga,subcon'])->group(function () {
    Route::post('/submissions/{id}/upload-hrga', [SubmissionHrgaController::class, 'upload'])
        ->name('admin.submissions.upload-hrga');
});
```

### Upload Controller Logic (SubmissionHrgaController::upload)

```php
// Who can upload?
if (!in_array($userRole, ['hrga', 'subcon', 'admin', 'she'])) {
    abort(403); // Only HRGA, Vendor, Admin, or SHE
}

// What's the submission status?
if ($submission->status === 'approved') {
    abort(403); // No uploads after approval
}

// Can HRGA/Vendor upload in current status?
if (!in_array($userRole, ['admin', 'she'])) {
    // HRGA/Vendor restricted to specific statuses
    if (!in_array($submission->status, ['pending_hrga', 'pending_paramedic', 'pending_tod', 'pending_she', 'rejected'])) {
        abort(403);
    }
}
```

### File Types

```
HRGA Files (4 required for completion):
├─ KTP (Kartu Tanda Penduduk)
├─ MCU (Medical Check-Up)
├─ SIM (Surat Izin Mengemudi)
└─ Foto Diri (Self Portrait)

Max size: 10 MB per file
Accepted formats: PDF, JPG, PNG, GIF, WebP, BMP, TIF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV
```

## 5. MONITORING LIST DISPLAY

### View: admin/submissions/index.blade.php

```blade
Column: Pemohon (Applicant Name)
- Displays: applicant_name + initials avatar

Column: Perusahaan (Company/Submitter)
- If vendor: GREEN badge + company_name + "Vendor/Subcon" label
  Example: "🟢 ANUGRAH FAJAR ALAM - Vendor/Subcon"
- If staff: BLUE badge + creator->full_name + "Admin/Staff" label
  Example: "🔵 budiyanto - Admin/Staff"

Column: Status
- Display: status_label (e.g., "Perlu Dilengkapi")
- Color: status_color (warning/success/danger)
- Admin/SHE only: Shows next pending role hint

Row Filtering:
- HRGA: Sees all submissions
- Vendor: Sees only own (vendor_id matching session vendor_id)
```

## 6. VENDOR ACCESS CONTROL VALIDATION

### Current Checks

```php
// 1. In SubmissionController::show()
if ($isVendor && $submission->vendor_id !== $vendorId) {
    abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
}

// 2. In SubmissionController::index()
if ($isVendor && $vendorId) {
    $query->where('vendor_id', $vendorId);
}

// 3. In RequireSimperRole middleware
if (LegacyAuth::isVendor()) {
    $userRole = 'subcon'; // Map vendor to subcon role
}
```

## 7. POTENTIAL ISSUES & OBSERVATIONS

### ✅ Working Correctly

1. **Vendor Passwordless Login**: Vendors can login with company name only (SAPKON synced)
2. **Vendor Session Mapping**: Vendor session correctly mapped to role='subcon' in middleware
3. **Vendor ID Filtering**: Vendors can only see own submissions (vendor_id filter)
4. **Cross-Vendor Access Prevention**: 403 abort if vendor tries to access other vendor's submission
5. **Audit Trail**: "Vendor/Subcon" label displayed with vendor company name
6. **File Upload**: Both HRGA and Vendor can upload HRGA files during pending_hrga status
7. **Status Workflow**: Correct progression from pending_hrga → pending_paramedic → pending_tod → pending_she

### ⚠️ Areas Requiring Verification

1. **Vendor Authorization in SubmissionHrgaController**
   - ✅ Upload route is protected by `simper.role:hrga,subcon`
   - ✅ Controller checks `in_array($userRole, ['hrga', 'subcon', 'admin', 'she'])`
   - ⚠️ **Missing**: No explicit check that vendor only uploads to own submissions

2. **File Download Security**
   - ✅ downloadFile method exists
   - ⚠️ **Missing**: No vendor authorization check (should prevent vendor from downloading other vendor's files)

3. **Submission Update (applicant_name, item_type)**
   - ✅ Protected by `simper.role:hrga,subcon`
   - ⚠️ **Missing**: No vendor ownership check in update() method

4. **Delete Permission**
   - ✅ Only admin/she can delete (correct)
   - ⚠️ No audit trail of who deleted what

5. **Status Transitions**
   - ✅ HRGA→paramedic: Automatic on 4-file completion (or forced forward)
   - ❓ **Unclear**: forwardHrga route exists but implementation not shown
   - ❓ **Unclear**: How does status actually transition between stages?

### 🔴 Critical Gaps

1. **SubmissionHrgaController::upload() - Missing Vendor Ownership Check**

```php
// VULNERABILITY: Vendor can upload to ANY submission, not just own!
$submission = Submission::findOrFail($id);  // No vendor check

// SHOULD BE:
if ($isVendor && $submission->vendor_id !== $vendorId) {
    abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
}
```

2. **SubmissionController::update() - Missing Vendor Ownership Check**

```php
// VULNERABILITY: Vendor can modify ANY submission
$submission = Submission::findOrFail($id);  // No vendor check

// SHOULD BE:
if ($isVendor && $submission->vendor_id !== $vendorId) {
    abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
}
```

3. **SubmissionController::downloadFile() - Missing All Access Checks**

```php
// VULNERABILITY: No authentication/authorization at all!
$file = $submission->files()->findOrFail($fileId);

// SHOULD BE:
if ($isVendor && $submission->vendor_id !== $vendorId) {
    abort(403);
}
```

## 8. RECOMMENDATIONS

### HIGH PRIORITY (Security)

1. **Add vendor ownership checks to upload, update, download, delete-file methods**
2. **Add audit logging for file uploads (who, what, when)**
3. **Validate that vendor_id matches session vendor_id in all submission operations**

### MEDIUM PRIORITY (UX/Clarity)

1. **Show only relevant status hints for vendor (hide "pending_tod" internal routing labels)**
2. **Add status explanation tooltips for vendor users**
3. **Implement email notifications when submission moves between stages**

### LOW PRIORITY (Optimization)

1. **Cache vendor company names in session to reduce DB queries**
2. **Implement submission search filters (status, date range)**
3. **Add bulk download option for vendor's all files**

## 9. TESTING CHECKLIST

### Vendor Access Control

- [ ] Vendor A cannot see Vendor B's submissions
- [ ] Vendor cannot edit another vendor's applicant_name
- [ ] Vendor cannot upload files to another vendor's submission
- [ ] Vendor cannot download another vendor's files
- [ ] Vendor cannot delete submissions
- [ ] Vendor CAN create new submission (sets vendor_id automatically)
- [ ] Vendor CAN upload to own pending_hrga submission
- [ ] Vendor CAN view own submission in all statuses

### HRGA Access Control

- [ ] HRGA sees all submissions (no vendor_id filter)
- [ ] HRGA can upload files to any submission
- [ ] HRGA cannot approve/reject (SHE only)
- [ ] HRGA can create submission (sets created_by, no vendor_id)

### Status Transitions

- [ ] pending_hrga → pending_paramedic (after 4 files or force_forward)
- [ ] pending_paramedic → pending_tod (after paramedic verify)
- [ ] pending_tod → pending_she (after TOD upload)
- [ ] pending_she → approved (SHE approval) OR rejected (SHE rejection)
- [ ] rejected → pending_hrga (resubmission for relisting)

### Audit Trail

- [ ] Vendor submissions show "Vendor/Subcon" badge with company name
- [ ] HRGA submissions show "Admin/Staff" badge with staff name
- [ ] File uploads log who uploaded and when
- [ ] Status changes logged

---

**Document Version**: 1.0 (May 11, 2026)
**Last Updated**: 2026-05-11
**Author**: System Analysis
**Status**: Ready for Implementation
