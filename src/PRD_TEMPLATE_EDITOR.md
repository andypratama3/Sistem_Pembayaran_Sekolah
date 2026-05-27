# Product Requirements Document: Template Editor System

**Product:** ProductSchool
**Feature:** Template Editor (Visual Document Designer)
**Version:** 2.0 (Post Konva.js Migration)
**Date:** 23 Mei 2026
**Status:** Final

---

## 1. Executive Summary

Template Editor adalah sistem visual drag-and-drop untuk mendesain dokumen sekolah (rapor, surat keterangan, sertifikat, dll). Sistem ini memungkinkan admin/guru mendesain layout dokumen secara visual menggunakan canvas editor, mendefinisikan variabel dinamis yang terisi otomatis dari data siswa, lalu menghasilkan dokumen PDF per siswa atau per kelas secara massal.

### 1.1 Problem Statement
- Sekolah membutuhkan dokumen-dokumen yang seragam formatnya (rapor, surat keterangan, sertifikat, ijazah)
- Setiap sekolah memiliki format dokumen yang berbeda-beda — tidak bisa menggunakan template statis
- Pembuatan dokumen secara manual (Microsoft Word) memakan waktu dan rawan inkonsistensi
- Pengisian data siswa ke dalam template dokumen membutuhkan waktu berjam-jam per kelas

### 1.2 Proposed Solution
Sistem template editor visual yang memungkinkan:
- Desain layout dokumen secara visual tanpa coding (drag-and-drop)
- Definisi field/variabel yang terisi otomatis dari database siswa
- Generate dokumen PDF massal untuk satu kelas penuh
- Workflow approval (draft → submitted → approved)
- Template dapat dibagikan secara global ke semua guru

---

## 2. Target Users & Personas

| Persona | Role | Needs |
|---------|------|-------|
| Admin Sekolah | Super admin | Membuat & mengelola semua template, mengatur kategori, mendesain layout |
| Guru/Wali Kelas | End user | Mengisi field template untuk siswa, generate dokumen per kelas |
| Kepala Sekolah | Approver | Menyetujui dokumen sebelum dicetak |
| Operator TU | Power user | Generate dokumen massal, export PDF |

---

## 3. Feature Requirements

### 3.1 Template Management (CRUD)

| ID | Fitur | Prioritas | Deskripsi |
|----|-------|-----------|-----------|
| TM-01 | Daftar Template | P0 | DataTable dengan filter kategori, status, search, pagination |
| TM-02 | Buat Template Baru | P0 | Form create dengan nama, kategori, deskripsi |
| TM-03 | Edit Template | P0 | Full canvas editor (React/Konva.js) |
| TM-04 | Detail Template | P1 | View detail + preview layout |
| TM-05 | Hapus Template | P0 | Soft delete dengan cascade ke instances |
| TM-06 | Clone/Duplikat | P1 | Duplikasi template beserta field definitions |
| TM-07 | Export/Import JSON | P2 | Export template structure sebagai JSON untuk backup |
| TM-08 | Publish/Unpublish | P1 | Toggle visibilitas template |
| TM-09 | Toggle Global | P1 | Template tersedia untuk semua guru |
| TM-10 | Starter Gallery | P2 | Galeri template siap pakai (rapor, surat, sertifikat) |

### 3.2 Template Categories

| ID | Fitur | Prioritas | Deskripsi |
|----|-------|-----------|-----------|
| TC-01 | CRUD Kategori | P0 | Grade Report, Attendance, Report Card, Certificate, Custom |
| TC-02 | Icon Kategori | P2 | Icon untuk visual identification |
| TC-03 | Filter by Category | P0 | Filter template list berdasarkan kategori |

### 3.3 Canvas Editor (Core)

| ID | Fitur | Prioritas | Deskripsi |
|----|-------|-----------|-----------|
| CE-01 | Visual Canvas | P0 | Canvas berbasis Konva.js untuk desain WYSIWYG |
| CE-02 | Multi-Page Support | P0 | Template bisa memiliki banyak halaman (A4, F4, Letter, A3) |
| CE-03 | Page Navigator | P0 | Thumbnail navigasi halaman + add/delete/reorder halaman |
| CE-04 | Undo/Redo | P0 | History-based undo/redo (unlimited) |
| CE-05 | Zoom In/Out | P1 | Zoom canvas (25% - 400%) |
| CE-06 | Grid & Snap | P1 | Gridlines + snap-to-grid untuk alignment |
| CE-07 | Rulers | P2 | Penggaris horizontal & vertikal |
| CE-08 | Layer System | P1 | Layer hierarchy (bring front, send back, grouping) |
| CE-09 | Context Menu | P1 | Right-click menu untuk aksi cepat (copy, paste, delete, bring to front) |
| CE-10 | Auto-Save | P0 | Menyimpan otomatis setiap 30 detik |
| CE-11 | Keyboard Shortcuts | P1 | Shortcuts: Ctrl+S, Ctrl+Z, Ctrl+C/V, Delete, dll |

### 3.4 Canvas Element Types

| ID | Fitur | Prioritas | Deskripsi |
|----|-------|-----------|-----------|
| ET-01 | Text Element | P0 | Text dengan font, size, bold, italic, underline, alignment, color |
| ET-02 | Shape Element | P0 | Rectangle, circle, line, arrow — fill, stroke, opacity |
| ET-03 | Image Element | P1 | Upload gambar (logo sekolah, foto, barcode) |
| ET-04 | Table Element | P1 | Dynamic table dengan merge cell, resize column, header styling |
| ET-05 | QR Code Element | P2 | QR code untuk verifikasi dokumen |
| ET-06 | Variable Placeholder | P0 | `{{student_name}}`, `{{grade_average}}` — resolved to actual data |
| ET-07 | Line/Border | P1 | Garis horizontal/vertikal untuk pemisah |
| ET-08 | Page Number | P2 | Auto page numbering |

### 3.5 Variable System

| ID | Fitur | Prioritas | Deskripsi |
|----|-------|-----------|-----------|
| VS-01 | System Variables | P0 | Variables from database: student_name, nisn, classroom, school_name, etc. |
| VS-02 | Custom Fields | P0 | User-defined fields: text, number, date, select, checkbox |
| VS-03 | Formula Fields | P0 | Auto-calculated: `(midterm * 0.4) + (final_exam * 0.6)` |
| VS-04 | Variable Library Panel | P1 | Panel browser untuk drag-and-drop variables ke canvas |
| VS-05 | AI Variable Suggestions | P2 | AI suggest mapping between canvas variables and available data |
| VS-06 | AI Layout Generation | P3 | Generate layout from natural language brief |

### 3.6 Field Types (Custom Fields)

| ID | Field Type | Prioritas | Deskripsi |
|----|-----------|-----------|-----------|
| FT-01 | Text | P0 | Single line text input |
| FT-02 | Number | P0 | Numeric input (grades, scores) |
| FT-03 | Date | P0 | Date picker |
| FT-04 | Select/Dropdown | P0 | Options from predefined list |
| FT-05 | Checkbox | P0 | Boolean (Yes/No) |
| FT-06 | Formula | P0 | Auto-calculated based on other fields |

### 3.7 Document Generation

| ID | Fitur | Prioritas | Deskripsi |
|----|-------|-----------|-----------|
| DG-01 | Single Student PDF | P0 | Generate PDF untuk 1 siswa |
| DG-02 | Bulk Class PDF | P0 | Generate PDF untuk semua siswa dalam 1 kelas |
| DG-03 | Queue Processing | P0 | Background job untuk batch besar (>10 siswa) |
| DG-04 | Preview HTML | P0 | Live preview sebelum generate PDF |
| DG-05 | Download PDF | P0 | Download generated PDF |
| DG-06 | PDF Paper Size | P0 | Auto-detect paper size from canvas dimensions (A4, F4, Letter) |
| DG-07 | Export Record | P1 | Audit trail setiap export (siapa, kapan, file size) |
| DG-08 | Batch Progress | P1 | Tracking status batch generation |

### 3.8 Workflow & Approval

| ID | Fitur | Prioritas | Deskripsi |
|----|-------|-----------|-----------|
| WF-01 | Status: Draft | P0 | Initial state saat baru dibuat |
| WF-02 | Status: Submitted | P0 | Dikirim untuk approval |
| WF-03 | Status: Approved | P0 | Disetujui, siap cetak |
| WF-04 | Submit for Approval | P1 | Guru submit instance ke Kepsek |
| WF-05 | Approve | P1 | Kepsek menyetujui |
| WF-06 | Reject | P1 | Kepsek tolak dengan alasan → kembali ke draft |
| WF-07 | Document Numbering | P1 | Auto numbering format: `SK/2025/001` |

### 3.9 AI-Assisted Features

| ID | Fitur | Prioritas | Deskripsi |
|----|-------|-----------|-----------|
| AI-01 | Suggest Variable Mapping | P2 | AI menganalisis canvas_layout → suggest mapping ke data fields |
| AI-02 | Suggest Layout from Brief | P3 | Generate layout dari deskripsi natural language |
| AI-03 | Narasi Generation | P2 | Generate narasi rapor otomatis via AI |

---

## 4. Database Schema

### 4.1 Core Tables

**template_categories**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| slug | VARCHAR(255) UNIQUE | grade, attendance, report, certificate, custom |
| label | VARCHAR(255) | Grade Report, Attendance Record, etc. |
| icon_key | VARCHAR(50) | FontAwesome icon class |

**templates**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| category_id | UUID FK → template_categories | |
| created_by | UUID FK → users | |
| name | VARCHAR(255) | Nama template |
| description | TEXT | Deskripsi |
| canvas_layout | JSON | Fabric.js/Konva.js JSON layout |
| html_template | TEXT | Alternative HTML template |
| pages_json | JSON | Multi-page configuration |
| variable_map | JSON | Variable mappings |
| thumbnail_path | VARCHAR(500) | Preview thumbnail |
| is_published | BOOLEAN | |
| is_global | BOOLEAN | |
| document_type | VARCHAR(100) | report_card, letter_active, certificate |
| document_number_format | VARCHAR(100) | DOC/{YEAR}/{SEQ3} |
| requires_approval | BOOLEAN | |
| supports_bulk | BOOLEAN | |
| default_semester | VARCHAR(10) | |
| user_instructions | TEXT | |
| preview_thumbnail | TEXT | |
| timestamps | created_at, updated_at | |

**template_fields**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| template_id | UUID FK → templates | |
| field_key | VARCHAR(255) | Unique key per template |
| label | VARCHAR(255) | Display label |
| field_type | ENUM | text, number, date, select, checkbox, formula |
| options | JSON | For select type |
| formula | TEXT | For formula type |
| placeholder | VARCHAR(255) | |
| required | BOOLEAN | |
| sort_order | INTEGER | |
| timestamps | | |
| UNIQUE(template_id, field_key) | | |

**template_instances**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| template_id | UUID FK → templates | |
| student_id | UUID FK → students | |
| period_id | UUID FK → academic_years | |
| subject_id | UUID FK → subjects | |
| filled_by | UUID FK → users | |
| status | ENUM | draft, submitted, approved |
| document_number | VARCHAR(100) | |
| semester | VARCHAR(10) | |
| purpose | TEXT | |
| document_date | DATE | |
| approved_by | UUID FK → users | |
| approved_at | DATETIME | |
| submitted_by | UUID FK → users | |
| submitted_at | DATETIME | |
| rejected_by | UUID FK → users | |
| rejected_at | DATETIME | |
| rejection_reason | TEXT | |
| generated_pdf_path | VARCHAR(500) | |
| achievement_id | UUID FK → achievements | |
| notes | TEXT | |
| is_printed | BOOLEAN | |
| printed_at | DATETIME | |
| data_json | JSON | |
| verification_code | VARCHAR(100) | |
| file_path | VARCHAR(500) | |
| timestamps | | |

**field_values**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| instance_id | UUID FK → template_instances | |
| field_id | UUID FK → template_fields | |
| value | TEXT | |
| timestamps | | |
| UNIQUE(instance_id, field_id) | | |

**exports**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| instance_id | UUID FK → template_instances | |
| exported_by | UUID FK → users | |
| file_path | VARCHAR(500) | |
| format | ENUM | pdf, xlsx |
| original_filename | VARCHAR(255) | |
| file_size | INTEGER | |
| export_metadata | JSON | |
| timestamps | | |

**batch_exports**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| template_id | UUID FK → templates | |
| period_id | UUID FK → academic_years | |
| subject_id | UUID FK → subjects | |
| exported_by | UUID FK → users | |
| batch_name | VARCHAR(255) | |
| total_instances | INTEGER | |
| successful_exports | INTEGER | |
| failed_exports | INTEGER | |
| status | ENUM | pending, processing, completed, failed |
| error_log | JSON | |
| timestamps | | |

### 4.2 Legacy Tables (DocumentTemplate)

**document_templates**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| category_id | UUID FK | |
| classroom_id | UUID FK → classrooms | |
| created_by | UUID FK → users | |
| name | VARCHAR(255) | |
| description | TEXT | |
| html_template | TEXT | |
| canvas_json | JSON | |
| pages_json | JSON | |
| variable_map | JSON | |
| table_config | JSON | |
| generate_mode | ENUM | perorang, daftar, statis |
| auto_nomor_surat | BOOLEAN | |
| nomor_surat_format | VARCHAR(255) | |
| is_public | BOOLEAN | |
| is_locked | BOOLEAN | |
| thumbnail_path | VARCHAR(500) | |
| timestamps | | |

**document_template_categories**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| name | VARCHAR(255) | |
| slug | VARCHAR(255) UNIQUE | |
| description | TEXT | |
| icon | VARCHAR(100) | |
| sort_order | INTEGER | |
| timestamps | | |

**document_template_tables**
| Column | Type | Description |
|--------|------|-------------|
| id | UUID PK | |
| template_id | UUID FK | |
| table_id | VARCHAR(100) | |
| sheet_name | VARCHAR(255) | |
| table_mode | ENUM | perorang, daftar, statis |
| linking_key | VARCHAR(100) | |
| col_names | JSON | |
| col_widths | JSON | |
| row_heights | JSON | |
| header_color | VARCHAR(20) | |
| stripe_color | VARCHAR(20) | |
| border_color | VARCHAR(20) | |
| timestamps | | |

---

## 5. API Endpoints

### 5.1 Template CRUD (Web Routes)

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/dashboard/templates` | Index (DataTable) |
| GET | `/dashboard/templates/create` | Create form |
| POST | `/dashboard/templates` | Store |
| GET | `/dashboard/templates/{templateRecord}` | Show |
| GET | `/dashboard/templates/{templateRecord}/edit` | Edit (React editor) |
| PUT | `/dashboard/templates/{templateRecord}` | Update |
| DELETE | `/dashboard/templates/{templateRecord}` | Destroy |
| GET | `/dashboard/templates/{templateRecord}/preview` | Preview HTML |
| POST | `/dashboard/templates/{templateRecord}/clone` | Clone template |
| POST | `/dashboard/templates/{templateRecord}/export-json` | Export as JSON |

### 5.2 Canvas Editor (AJAX)

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/dashboard/templates/{templateRecord}/canvas/save` | Save canvas layout |
| GET | `/dashboard/templates/{templateRecord}/canvas/get` | Get canvas layout |
| POST | `/dashboard/templates/{templateRecord}/canvas/auto-save` | Auto-save (30s interval) |
| POST | `/dashboard/templates/{templateRecord}/canvas/download-pdf` | Download PDF from canvas |
| POST | `/dashboard/templates/{templateRecord}/canvas/download-image` | Download PNG from canvas |
| POST | `/dashboard/templates/{templateRecord}/preview-canvas` | Live preview render |
| POST | `/dashboard/templates/{templateRecord}/export-pdf-canvas` | Export PDF live |

### 5.3 Template Fields

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/dashboard/templates/validate-field` | Validate formula syntax |
| GET | `/dashboard/templates/{template}/variables` | Get available variables |

### 5.4 Template Instances

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/dashboard/template-instances` | Index (DataTable) |
| GET | `/dashboard/template-instances/create` | Create form |
| POST | `/dashboard/template-instances` | Store |
| GET | `/dashboard/template-instances/{templateInstanceRecord}` | Show |
| GET | `/dashboard/template-instances/{templateInstanceRecord}/edit` | Edit |
| PUT | `/dashboard/template-instances/{templateInstanceRecord}` | Update |
| DELETE | `/dashboard/template-instances/{templateInstanceRecord}` | Destroy |
| GET | `/dashboard/template-instances/{templateInstanceRecord}/preview` | Preview HTML |
| GET | `/dashboard/template-instances/{templateInstanceRecord}/pdf` | Download PDF |
| POST | `/dashboard/template-instances/bulk-generate` | Bulk generate by classroom |
| POST | `/dashboard/template-instances/{templateInstanceRecord}/submit` | Submit for approval |
| POST | `/dashboard/template-instances/{templateInstanceRecord}/approve` | Approve |
| POST | `/dashboard/template-instances/{templateInstanceRecord}/reject` | Reject |
| POST | `/dashboard/template-instances/fields/{template}` | Get template fields for form |

### 5.5 AI Features

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/dashboard/templates/{templateRecord}/ai/variables` | AI suggest variable mapping |
| POST | `/dashboard/templates/ai/layout` | AI generate layout from brief |

### 5.6 Template Categories

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/dashboard/template-categories` | Index |
| GET | `/dashboard/template-categories/create` | Create form |
| POST | `/dashboard/template-categories` | Store |
| GET | `/dashboard/template-categories/{templateCategoryRecord}/edit` | Edit |
| PUT | `/dashboard/template-categories/{templateCategoryRecord}` | Update |
| DELETE | `/dashboard/template-categories/{templateCategoryRecord}` | Destroy |

---

## 6. Backend Architecture

### 6.1 Controllers

| Controller | Responsibility |
|------------|---------------|
| `TemplateController` | CRUD + Canvas save/load + PDF export + Preview + Clone |
| `TemplateCategoryController` | Category CRUD |
| `TemplateInstanceController` | Instance CRUD + Generate PDF + Bulk + Workflow (submit/approve/reject) |
| `TemplateAiController` | AI variable suggestions + AI layout generation |
| `NarasiController` | AI narasi generation for report cards |
| (Legacy) `DocumentTemplateController` | Legacy document template CRUD + generate |

### 6.2 Services

| Service | Responsibility |
|---------|---------------|
| `TemplateService` | CRUD, syncFields, clone, publish, reorderFields, validate |
| `TemplateGeneratorService` | createInstance, generateHtmlPreview, generatePdf, generateBulk, updateInstance, deleteInstance |
| `VariableResolver` | Resolve all `{{variables}}` from database → actual values |
| `FormulaEvaluatorService` | Parse & evaluate formula expressions |
| `RenderService` | Legacy HTML rendering from template + student data |
| `CanvasHtmlRenderer` | Convert canvas_layout JSON → HTML for DomPDF |
| `PdfRenderingService` | DomPDF wrapper for HTML → PDF conversion |
| `DocumentNumberService` | Auto-generate document numbers |
| `PdfStyles` | Paper size detection & PDF style utilities |
| `AiTemplateProvider` | AI provider for variable suggestions & layout generation |

### 6.3 Jobs (Queue)

| Job | Description |
|-----|-------------|
| `GenerateDocumentPdfJob` | Generate PDF for single instance (queued) |
| `ProcessBulkTemplateGenerationJob` | Batch create instances for whole classroom |
| `GenerateBulkDocumentsJob` | Legacy bulk document generation |
| `GenerateNarasiJob` | Generate AI narasi for report cards |

### 6.4 Policies

| Policy | Permissions |
|--------|-------------|
| `TemplatePolicy` | viewAny, view, create, update, delete, clone, publish, submit, approve, reject |

---

## 7. Frontend Architecture

### 7.1 Tech Stack
- **Framework:** React 19
- **Canvas Library:** Konva.js (migrated from Fabric.js)
- **State Management:** Zustand v5 + subscribeWithSelector
- **Build Tool:** Vite
- **CSS:** Custom CSS modules

### 7.2 Component Tree

```
TemplateEditor (Root)
├── TemplateEditorContent (Main Layout)
│   ├── Toolbar
│   │   ├── Element buttons (Text, Shape, Image, Table, QR)
│   │   ├── Zoom controls
│   │   ├── Undo/Redo
│   │   └── Save/Export
│   ├── CanvasPage (Konva Stage)
│   │   ├── LayerManager
│   │   │   ├── TextLayer
│   │   │   ├── ShapeLayer
│   │   │   ├── ImageLayer
│   │   │   ├── TableLayer
│   │   │   └── VariableLayer
│   │   └── ContextMenu
│   ├── PageNavigator (Page thumbnails)
│   ├── PropertiesPanel
│   │   ├── GeneralProps
│   │   ├── TextProps
│   │   ├── AppearanceProps
│   │   ├── TransformProps
│   │   ├── TableStructureProps
│   │   ├── TableCellEditor
│   │   ├── TableMergeTool
│   │   └── QrCodeProps
│   ├── VariableLibrary
│   ├── FieldList
│   ├── LayersPanel
│   ├── StatusBar
│   ├── StarterGalleryModal
│   ├── PreviewModal
│   └── ShortcutsHelpModal
```

### 7.3 Store Structure (Zustand)

| Slice | State |
|-------|-------|
| `pageSlice` | pages[], currentPageIndex, addPage, removePage, reorderPages, updatePage |
| `uiSlice` | zoom, gridEnabled, snapToGrid, selectedObjectIds, isSaving, panelVisibility |
| `historySlice` | past[], future[], pushState, undo, redo, clearHistory |
| `fieldSlice` | fields[], fieldValues{}, addField, updateField, removeField |

### 7.4 Canvas Engines

| Engine | Responsibility |
|--------|---------------|
| `CanvasEngine.js` | Factory for Konva Stage/Layer creation & configuration |
| `CanvasSerializer.js` | Serialize/Deserialize canvas state ↔ JSON for persistence |
| `CanvasTable.js` | Real table rendering (extends Konva.Group) with cell management |
| `CanvasTableData.js` | Table data model with cell merging, sizing |
| `CanvasTableEditor.js` | Inline table cell editing |
| `CanvasTableRenderer.js` | Table rendering to HTML for PDF |
| `CanvasTableResizeHandles.js` | Column/row resize handles |
| `LayerManager.js` | Layer ordering, visibility, grouping |
| `SchemaRenderer.js` | Convert canvas JSON → HTML for DomPDF output |
| `VariableEngine.js` | Resolve `{{variables}}` in canvas text objects |
| `DynamicTableEngine.js` | Dynamic table data binding for batch generation |

### 7.5 Hooks

| Hook | Responsibility |
|------|---------------|
| `useCanvasEngine.js` | Stage lifecycle management (mount, resize, destroy) |
| `useCanvasEvents.js` | Centralized event handling (click, dblclick, drag, transform) |
| `useCanvasSync.js` | Bidirectional sync: Zustand store ↔ Konva canvas objects |
| `useAutoSave.js` | Auto-save timer (30s) with dirty flag |
| `useKeyboardShortcuts.js` | Global keyboard shortcut registration |
| `useContextMenu.js` | Right-click context menu state & positioning |

### 7.6 Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| Ctrl+S | Save |
| Ctrl+Z | Undo |
| Ctrl+Shift+Z / Ctrl+Y | Redo |
| Ctrl+C | Copy selected |
| Ctrl+V | Paste |
| Ctrl+X | Cut |
| Delete / Backspace | Delete selected |
| Ctrl+A | Select all |
| Ctrl+D | Duplicate |
| Ctrl+G | Group selected |
| Ctrl+Shift+G | Ungroup |
| + / - | Zoom in/out |
| Ctrl+0 | Fit to screen |
| Ctrl+P | Preview |
| Arrow keys | Nudge 1px |
| Shift+Arrow keys | Nudge 10px |

---

## 8. Data Flow

### 8.1 Document Generation Flow

```
User clicks "Generate for Student" (or "Bulk Generate")
  │
  ▼
TemplateInstanceController::store() / bulkGenerateByClass()
  │
  ▼
TemplateGeneratorService::createInstance()
  ├── validateRequiredFields()
  ├── DB::transaction → INSERT template_instances + field_values
  └── dispatch(GenerateDocumentPdfJob)
        │
        ▼ (Queue Worker)
    GenerateDocumentPdfJob::handle()
      ├── load instance + template + student
      ├── TemplateGeneratorService::generateHtmlPreview()
      │     ├── resolveInstanceVariables() → VariableResolver
      │     └── CanvasHtmlRenderer::renderCanvasLayout()
      │           ├── SchemaRenderer → canvas JSON → HTML
      │           └── VariableEngine → replace {{vars}}
      ├── TemplateGeneratorService::generatePdf()
      │     ├── DocumentNumberService::generate()
      │     ├── PdfStyles::extractPageDimensions()
      │     ├── PdfStyles::resolvePaperSize()
      │     └── PdfRenderingService::renderAndStore()
      └── update instance status + path
```

### 8.2 Canvas Editor Auto-Save Flow

```
User edits canvas (drag, resize, type)
  │
  ▼
Zustand store updates (pageSlice)
  │
  ▼
useAutoSave hook detects dirty state
  │ (30s debounce)
  ▼
POST /dashboard/templates/{id}/canvas/auto-save
  │ body: { canvas_layout: pages[] }
  ▼
TemplateController::autoSaveCanvasLayout()
  ├── normalizeCanvasLayout()
  ├── validateCanvasLayout()
  ├── DB::transaction → lockForUpdate() → update
  └── return { success: true, timestamp }
```

---

## 9. Variable System

### 9.1 System Variables (Auto-Resolved)

| Variable | Source | Description |
|----------|--------|-------------|
| `{{student_name}}` | students.name | Nama siswa |
| `{{student_nisn}}` | students.nisn | NISN |
| `{{student_nis}}` | students.nis | NIS |
| `{{student_gender}}` | students.gender | Laki-laki/Perempuan |
| `{{student_birth_place}}` | students.birth_place | Tempat lahir |
| `{{student_birth_date}}` | students.birth_date | Tanggal lahir |
| `{{student_address}}` | students.address | Alamat |
| `{{student_religion}}` | students.religion | Agama |
| `{{student_parent_name}}` | students.parent_name | Nama orang tua |
| `{{classroom_name}}` | classrooms.name | Nama kelas |
| `{{class_level}}` | classrooms.level | Tingkat kelas |
| `{{academic_year}}` | academic_years.name | Tahun ajaran |
| `{{homeroom_teacher}}` | teachers/employees | Wali kelas |
| `{{school_name}}` | sekolah.name | Nama sekolah |
| `{{school_address}}` | sekolah.address | Alamat sekolah |
| `{{school_phone}}` | sekolah.phone | Telepon sekolah |
| `{{school_email}}` | sekolah.email | Email sekolah |
| `{{principal_name}}` | sekolah.kepala_sekolah | Nama Kepsek |
| `{{nip_kepsek}}` | sekolah.nip_kepsek | NIP Kepsek |
| `{{grade_average}}` | computed | Rata-rata nilai |
| `{{attendance_hadir}}` | aggregated | Jumlah hadir |
| `{{attendance_izin}}` | aggregated | Jumlah izin |
| `{{attendance_sakit}}` | aggregated | Jumlah sakit |
| `{{attendance_alpa}}` | aggregated | Jumlah alpa |
| `{{letter_number}}` | auto-generated | Nomor surat |
| `{{letter_date}}` | auto-generated | Tanggal surat |
| `{{current_date}}` | now() | Tanggal sekarang |
| `{{current_year}}` | now() | Tahun sekarang |
| `{{current_month}}` | now() | Bulan sekarang |
| `{{logo}}` | sekolah.logo | Logo sekolah |
| `{{logo_sekolah}}` | sekolah.logo | Logo sekolah |
| `{{barcode_signature}}` | computed | Barcode verifikasi |
| `{{nomor_surat}}` | auto-generated | Nomor surat otomatis |

### 9.2 Formula Syntax

Formula fields support arithmetic expressions with references to other fields:

```
(midterm * 0.4) + (final_exam * 0.6)
(uts + uas) / 2
(tugas1 + tugas2 + tugas3) / 3
(nilai_akhir >= 75) ? "LULUS" : "TIDAK LULUS"
```

---

## 10. PDF Generation & Styling

### 10.1 PDF Pipeline
1. Canvas layout (JSON) → SchemaRenderer → HTML
2. VariableEngine replaces all `{{variables}}` with actual data
3. DomPDF converts HTML → PDF with configured paper size
4. PDF stored to storage/app/templates/{template_id}/{instance_id}/
5. Export record created with audit trail

### 10.2 Paper Size Support
- A4 (595 x 842 px at 72 DPI / 794 x 1123 px at 96 DPI)
- F4/Folio (816 x 1344 px at 96 DPI)
- Letter (816 x 1056 px at 96 DPI)
- Custom dimensions from canvas_layout

### 10.3 Security Config (DomPDF)
- `isRemoteEnabled: true` (for images)
- `isJavascriptEnabled: false`
- `isPhpEnabled: false`
- `isHtml5ParserEnabled: true`
- `dpi: 96` (match canvas native DPI)

---

## 11. Migration & Legacy

### 11.1 Two Template Systems
The system has two parallel template implementations:

| Aspect | Template (New) | DocumentTemplate (Legacy) |
|--------|---------------|--------------------------|
| Canvas Library | Konva.js | Fabric.js |
| Frontend | React 19 | Blade + Alpine.js |
| Table Support | Native Konva tables | DocumentTemplateTable model |
| Generate Modes | Per-student only | perorang, daftar, statis |
| Workflow | draft → submitted → approved | Direct generate |
| Variable System | VariableResolver service | extractVariables() on model |

### 11.2 Migration Path
Future work should consolidate DocumentTemplate into the Template system:
- DocumentTemplate.tables → Template.canvas_layout (table objects)
- DocumentTemplate.generate_mode → Template.supports_bulk + document_type
- DocumentTemplate pages → Template pages_json

---

## 12. Non-Functional Requirements

### 12.1 Performance
- Template list page: < 2s load time for 500 templates
- Canvas editor: < 500ms for save operation
- PDF generation (single): < 5s
- PDF generation (bulk 30 students): < 2 minutes
- Canvas load: < 3s for complex layouts (10+ pages, 100+ objects)

### 12.2 Security
- Authorization via TemplatePolicy + permissions (viewAny, view, create, update, delete)
- Formula evaluation: server-side only, no eval() on client
- Canvas layout validation: structural validation before save
- File download: basename() + regex sanitization
- XSS prevention: strip_tags() on formula output, e() on Blade output
- Race condition prevention: row-level locking (lockForUpdate) on canvas save

### 12.3 Data Integrity
- Field values cascade delete when template field is removed
- Template instances cascade delete with template
- Batch export tracks individual item status
- All UUID primary keys (no auto-increment)
- Field values upsert by (instance_id, field_id) unique

### 12.4 Reliability
- Auto-save every 30 seconds prevents data loss
- PDF generation runs in queue jobs (non-blocking)
- Batch operations split into individual jobs
- AbortController on save/export for cleanup on unmount

---

## 13. Future Roadmap

| Phase | Features |
|-------|----------|
| Phase 1 (Current) | CRUD Templates, Canvas Editor, Field System, PDF Generation, Variable System, Basic Tables |
| Phase 2 (Next) | DocumentTemplate → Template migration, Advanced Table Editor, Batch Export UI, AI Narasi |
| Phase 3 (Future) | AI Layout Generation, Template Marketplace, Real-time Collaboration, Version History, Advanced Formula Engine |

---

## 14. Glossary

| Term | Definition |
|------|------------|
| Template | Definisi layout dokumen (canvas + fields) |
| Template Field | Definisi input field (text, number, formula, etc.) |
| Template Instance | Satu dokumen yang sudah diisi untuk satu siswa |
| Field Value | Nilai spesifik untuk satu field dalam satu instance |
| Canvas Layout | JSON representasi visual dari layout dokumen |
| Variable | Placeholder `{{var}}` yang akan diisi dengan data nyata |
| Formula | Ekspresi matematika yang dihitung otomatis |
| Batch Export | Generate massal PDF untuk satu kelas |
| Workflow | State machine: draft → submitted → approved |
