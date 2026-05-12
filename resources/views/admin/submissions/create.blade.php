<?php
$error = session('error');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Buat Pengajuan Baru | LCM Safety Portal</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=3.5">
    <link rel="stylesheet" href="<?= e(asset('assets/admin-premium.css')) ?>?v=1.1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <style>
        .form-section-title {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--admin-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--admin-border);
        }
    </style>
</head>
<body class="admin-page">
<header class="topbar-admin">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <a class="brand" href="<?= e(route('admin.dashboard.php')) ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="<?= e(asset('assets/branding/remote/lcm-logo.png')) ?>" alt="Logo" width="40" height="40">
            <span style="color: white; font-weight: 700; font-size: 1.1rem; letter-spacing: -0.02em;">Safety Portal</span>
        </a>
    </div>
</header>

<main class="container page-main" style="max-width: 850px; padding: 2rem 0;">
    <div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #f1f5f9; padding-bottom: 1.5rem;">
        <div>
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #eff6ff; color: #2563eb; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                <i class="ti ti-file-plus"></i> Inisialisasi Baru
            </span>
            <h1 style="font-size: 2.25rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.04em;">Formulir Pengajuan</h1>
            <p style="color: #64748b; font-size: 0.95rem; margin-top: 8px; font-weight: 500;">Lengkapi data di bawah ini untuk memulai proses verifikasi dokumen.</p>
        </div>
        <a href="<?= e(route('admin.submissions.index')) ?>" class="sp-btn" style="padding: 10px 20px; border-radius: 8px; font-weight: 600; background: #fff; color: #475569; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05); text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($errors->any()): ?>
        <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 1.25rem 1.5rem; margin-bottom: 2.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px;">
                <i class="ti ti-alert-circle" style="font-size: 1.2rem;"></i> Terdapat Kesalahan Input
            </div>
            <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.9rem; font-weight: 500;">
                <?php foreach ($errors->all() as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= e(route('admin.submissions.store')) ?>" method="post">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        
        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
            
            <!-- Section 1: Core Info -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
                <div style="background: #f8fafc; padding: 1.25rem 2rem; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="ti ti-category" style="color: var(--admin-primary);"></i> Spesifikasi Pengajuan
                    </h2>
                </div>
                <div style="padding: 2rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">Kategori Utama <span style="color: #ef4444;">*</span></label>
                            <select id="category_id" name="category_id" class="form-control" onchange="updateFormFields()" style="width: 100%; padding: 14px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 500; color: #0f172a; transition: all 0.2s; cursor: pointer;">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat->id) ?>" data-code="<?= e($cat->code) ?>"><?= e($cat->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label id="label_item_type" style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">Jenis Item <span style="color: #ef4444;">*</span></label>
                            <select id="item_type" name="item_type" class="form-control" style="width: 100%; padding: 14px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 500; color: #0f172a; transition: all 0.2s; cursor: pointer;">
                                <option value="">Pilih kategori terlebih dahulu</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Applicant Info -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
                <div style="background: #f8fafc; padding: 1.25rem 2rem; border-bottom: 1px solid #e2e8f0;">
                    <h2 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="ti ti-user" style="color: var(--admin-primary);"></i> Identitas Pemohon
                    </h2>
                </div>
                <div style="padding: 2rem;">
                    <div class="form-group" style="margin: 0;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">Nama Lengkap Karyawan <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="applicant_name" class="form-control" style="width: 100%; padding: 14px; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 500; color: #0f172a; transition: border-color 0.2s;" placeholder="Contoh: Ahmad Budianto" required>
                    </div>
                </div>
            </div>

            <div style="padding-top: 1rem; margin-bottom: 2rem;">
                <button type="submit" class="sp-btn sp-btn--primary" style="width: 100%; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; background-color: var(--admin-primary); color: #ffffff; border: none; cursor: pointer;">
                    <i class="ti ti-rocket"></i> Inisialisasi Pengajuan
                </button>
            </div>
        </div>
    </form>
</main>

<script>
const typeOptions = {
    'SIMPER_PERMIT': ['SIMPER', 'PERMIT'],
    'pengajuan_nomer_lambung': ['Unit Baru', 'Perpanjangan', 'Perubahan Unit'],
    'pengajuan_rambu': ['Rambu Keselamatan', 'Rambu Larangan', 'Rambu Peringatan', 'Rambu Petunjuk'],
    'pengajuan_commisioning': ['Unit Baru', 'Re-Commissioning', 'Paska Perbaikan']
};

const labels = {
    'SIMPER_PERMIT': { type: 'Pilih Jenis' },
    'pengajuan_nomer_lambung': { type: 'Tujuan Pengajuan' },
    'pengajuan_rambu': { type: 'Kategori Rambu' },
    'pengajuan_commisioning': { type: 'Jenis Commissioning' }
};

function updateFormFields() {
    const select = document.getElementById('category_id');
    
    // Auto-select if only 1 option besides the placeholder
    if (select.options.length === 2 && select.value === "") {
        select.selectedIndex = 1;
    }

    const selectedOption = select.options[select.selectedIndex];
    const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
    const typeSelect = document.getElementById('item_type');
    const labelType = document.getElementById('label_item_type');
    
    typeSelect.innerHTML = '<option value="">-- Pilih --</option>';
    
    if (code && typeOptions[code]) {
        typeOptions[code].forEach(opt => {
            const el = document.createElement('option');
            el.value = opt;
            el.textContent = opt;
            typeSelect.appendChild(el);
        });
    }
    
    if (code && labels[code]) {
        labelType.textContent = labels[code].type;
    } else {
        labelType.textContent = 'Jenis Item';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateFormFields();
});
</script>
</body>
</html>
