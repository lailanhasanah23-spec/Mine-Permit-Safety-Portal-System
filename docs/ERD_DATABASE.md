# Entity-Relationship Diagram (ERD)
# Mine Permit & Safety Portal

Dokumen ini menjelaskan struktur relasional database yang digunakan pada sistem **Mine Permit & Safety Portal**. Diagram berikut menampilkan entitas inti, sedangkan tabel operasional pendukung dijelaskan setelah diagram agar dokumentasi tetap ringkas dan mudah divalidasi.

## Diagram ERD

```mermaid
erDiagram
    USERS ||--o{ SUBMISSIONS : "creates"
    USERS ||--o{ SUBMISSION_FILES : "uploads"
    USERS ||--o{ FORMS : "creates"
    USERS {
        bigint id PK
        string name
        string email
        string password
        string role "admin, she, hrga, tod, paramedic, subcon"
        timestamp email_verified_at
    }

    CATEGORIES ||--o{ SUBMISSIONS : "has many"
    CATEGORIES ||--o{ FORMS : "has many"
    CATEGORIES {
        bigint id PK
        string code "e.g., SIMPER"
        string name "e.g., Pengajuan SIMPER"
        text description
        integer sort_order
        boolean is_active
    }

    FORMS {
        bigint id PK
        bigint category_id FK
        string title
        string purpose
        string form_url "Google Form URL"
        string link_scope
        text notes
        date effective_start
        date effective_end
        boolean is_active
        bigint created_by FK
        bigint updated_by FK
    }

    SUBMISSIONS ||--o{ SUBMISSION_FILES : "contains"
    SUBMISSIONS {
        bigint id PK
        bigint category_id FK
        string applicant_name
        string applicant_nik
        string company_name
        string item_type
        string item_identifier
        json item_details
        string status "pending_hrga, pending_paramedic, pending_tod, pending_she, approved"
        string gdrive_folder_id
        text she_notes
        text paramedic_notes
        timestamp rejected_at
        timestamp approved_at
        timestamp paramedic_verified_at
        bigint created_by FK
    }

    SUBMISSION_FILES {
        bigint id PK
        bigint submission_id FK
        string uploader_role
        string file_type "ktp, mcu, sim, tes_teori, dsb"
        string file_path
        string file_name
        bigint uploaded_by FK
    }

    AUDIT_LOGS {
        bigint id PK
        bigint actor_user_id FK
        string action
        string entity_type
        bigint entity_id
        json before_state
        json after_state
        string ip_address
        string user_agent
        timestamp created_at
    }

    EMAIL_SUBMISSION_TEMPLATES {
        bigint id PK
        string template_code
        string template_name
        string submission_type
        string recipient_cc
        string recipient_bcc
        string subject_template
        longtext body_template
        boolean is_active
        bigint created_by FK
        bigint updated_by FK
    }

    EMAIL_SUBMISSIONS {
        bigint id PK
        string submission_type
        string applicant_name
        string company_name
        string reference_no
        string recipient_to
        string recipient_cc
        string recipient_bcc
        bigint template_id FK
        string email_subject
        longtext email_body
        string delivery_channel
        string status
        timestamp sent_at
        text last_error
        bigint created_by FK
        bigint updated_by FK
    }

    EMAIL_SUBMISSION_ATTACHMENTS {
        bigint id PK
        bigint submission_id FK
        string doc_type
        string original_filename
        string stored_path
        string mime_type
        bigint file_size
        string checksum_sha256
        timestamp created_at
    }
```

## Deskripsi Tabel Utama

*   **`users`**: Menyimpan data autentikasi dan peran (*Role-Based Access Control*). Peran yang dipakai pada implementasi saat ini adalah `admin`, `she`, `hrga`, `tod`, `paramedic`, dan `subcon`.
*   **`categories`**: Tabel *master* yang mendefinisikan jenis pengajuan atau layanan di portal, termasuk kategori SIMPER, pengajuan internal, dan monitoring turunan.
*   **`forms`**: Menyimpan *link* formulir eksternal yang dipetakan ke kategori tertentu, beserta tujuan formulir (`pengajuan` atau `monitoring`) dan masa aktifnya.
*   **`submissions`**: Tabel inti transaksional yang mencatat setiap pengajuan, status verifikasi *multi-stage*, catatan peninjau, penanda waktu penolakan/persetujuan, serta *folder ID* Google Drive terkait.
*   **`submission_files`**: Menyimpan riwayat berkas yang diunggah untuk setiap pengajuan, dikelompokkan berdasarkan jenis file (`file_type`) dan peran pengunggah (`uploader_role`).
*   **`audit_logs`**: Menyimpan jejak perubahan penting seperti persetujuan, penolakan, perubahan master data, dan aktivitas administrasi lain yang memerlukan pelacakan forensik.
*   **`auth_login_attempts`**: Menyimpan hitungan percobaan login dan masa *lockout* untuk mendukung pembatasan percobaan masuk yang gagal.
*   **`email_submission_templates`**, **`email_submissions`**, dan **`email_submission_attachments`**: Menyimpan template email, data pengiriman, serta lampiran untuk workflow email SIMPER yang dikelola admin.

## Entitas Pendukung Operasional

Selain tabel inti di atas, migrasi juga menyediakan tabel referensi untuk kebutuhan operasional dan penyelarasan data kategori, yaitu `internal_company_groups`, `internal_companies`, `required_documents`, `sapkon_companies`, dan `sapkon_form_buckets`. Tabel-tabel ini dipakai untuk memastikan data form, perusahaan internal, dan dokumen yang dipersyaratkan tetap konsisten dengan proses bisnis yang berjalan.
