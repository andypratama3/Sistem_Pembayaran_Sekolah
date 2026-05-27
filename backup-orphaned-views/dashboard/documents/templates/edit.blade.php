@extends('layouts.editor')

@section('title', 'Template Editor — ' . $template->name)

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/js/editor/editor-modules.css') }}">
    <style>
        .canvas-bg {
            background-color : #f0f4f8;
            background-image : radial-gradient(#d1d5db 1px, transparent 1px);
            background-size  : 28px 28px;
        }
        #page-navigator::-webkit-scrollbar { width: 4px; }
        #page-navigator::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 999px; }
        .prop-input {
            border-radius    : 10px !important;
            border-color     : #f1f5f9 !important;
            background-color : #f8fafc !important;
            font-weight      : 600 !important;
            font-size        : 11px !important;
        }
        .prop-input:focus {
            border-color     : #6366f1 !important;
            background-color : #ffffff !important;
            box-shadow       : 0 0 0 3px rgba(99,102,241,0.1) !important;
        }
    </style>
@endpush

@section('content')

{{-- ══════════════════════════ HEADER ══════════════════════════ --}}
<header class="z-50 flex items-center justify-between flex-shrink-0 px-6 bg-white border-b border-gray-100 select-none h-14">

    {{-- Left: back + title --}}
    <div class="flex items-center flex-1 gap-4">
        <a href="{{ route('dashboard.document-templates.index') }}"
           class="p-2 text-gray-400 transition-colors hover:bg-gray-100 rounded-xl hover:text-gray-600">
            <i class="text-sm feather-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-sm font-black text-gray-900 leading-tight truncate max-w-[200px]">{{ $template->name }}</h1>
            <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest">Canvas Designer v3</p>
        </div>
    </div>

    {{-- Center: Toolbar --}}
    <div class="flex items-center justify-center flex-1">
        <div class="flex items-center bg-gray-100/80 p-1 rounded-2xl border border-gray-200/50 shadow-sm gap-0.5">

            {{-- Undo / Redo --}}
            <button id="undoBtn" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-white rounded-xl transition-all btn-icon-hover" title="Undo (Ctrl+Z)">
                <i class="text-base feather-corner-up-left"></i>
            </button>
            <button id="redoBtn" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-white rounded-xl transition-all btn-icon-hover" title="Redo (Ctrl+Y)">
                <i class="text-base feather-corner-up-right"></i>
            </button>

            <div class="w-px h-6 bg-gray-300 mx-1"></div>

            {{-- Add Elements --}}
            <button class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-white rounded-xl transition-all btn-icon-hover" onclick="toggleAddMenu()" title="Tambah Elemen">
                <i class="text-base feather-plus-square"></i>
            </button>

            {{-- Add Table --}}
            <button class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-white rounded-xl transition-all btn-icon-hover" onclick="openTableModal()" title="Tambah Tabel">
                <i class="text-base feather-grid"></i>
            </button>

            {{-- Zoom --}}
            <div class="flex items-center gap-1 px-2">
                <button class="p-1 text-gray-400 hover:text-indigo-600" onclick="zoomOut()">
                    <i class="text-sm feather-minus"></i>
                </button>
                <span id="zoomLevel" class="text-xs font-bold text-gray-600 min-w-[40px] text-center">100%</span>
                <button class="p-1 text-gray-400 hover:text-indigo-600" onclick="zoomIn()">
                    <i class="text-sm feather-plus"></i>
                </button>
            </div>

            <div class="w-px h-6 bg-gray-300 mx-1"></div>

            {{-- Page Nav --}}
            <button class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-white rounded-xl transition-all" onclick="addNewPage()" title="Tambah Halaman Baru">
                <i class="text-base feather-file-plus"></i>
            </button>
            <button class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-white rounded-xl transition-all" onclick="deleteCurrentPage()" title="Hapus Halaman">
                <i class="text-base feather-trash-2"></i>
            </button>
        </div>
    </div>

    {{-- Right: Save/Export --}}
    <div class="flex items-center justify-end flex-1 gap-3">
        <button onclick="saveTemplate()" class="btn btn-primary btn-hover-shadow btn-sm">
            <i class="text-sm feather-save"></i>
            <span>Simpan</span>
        </button>
        <div class="relative">
            <button onclick="toggleExportMenu()" class="btn btn-outline-secondary btn-sm dropdown-toggle">
                <i class="text-sm feather-download"></i>
                <span>Export</span>
            </button>
            <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50">
                <button onclick="previewTemplate()" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <i class="text-sm feather-eye mr-2"></i> Preview
                </button>
                <button onclick="downloadExcelTemplate()" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <i class="text-sm feather-file-text mr-2"></i> Download Excel Template
                </button>
                <button onclick="exportPDF()" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <i class="text-sm feather-file mr-2"></i> Export PDF
                </button>
            </div>
        </div>
    </div>
</header>

{{-- ══════════════════════════ MAIN CONTENT ══════════════════════════ --}}
<div class="flex flex-1 overflow-hidden">

    {{-- LEFT: Tools Panel --}}
    <aside class="w-56 bg-white border-r border-gray-100 flex flex-col overflow-y-auto">

        {{-- Page Navigator --}}
        <div class="p-3 border-b border-gray-100">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Halaman</div>
            <div id="page-navigator" class="flex flex-col gap-2 max-h-40 overflow-y-auto">
                {{-- Page thumbnails rendered here --}}
            </div>
            <button onclick="addNewPage()" class="mt-2 w-full py-1.5 text-xs font-bold text-indigo-600 border border-dashed border-indigo-200 rounded-lg hover:bg-indigo-50">
                + Tambah Halaman
            </button>
        </div>

        {{-- Add Elements Menu --}}
        <div class="p-3 border-b border-gray-100">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Elemen</div>
            <div id="element-menu" class="hidden grid grid-cols-2 gap-2">
                <button onclick="addElement('text')" class="p-2 text-xs text-center border rounded-lg hover:bg-gray-50">
                    <i class="text-base feather-type block mx-auto mb-1"></i>
                    Text
                </button>
                <button onclick="addElement('rect')" class="p-2 text-xs text-center border rounded-lg hover:bg-gray-50">
                    <i class="text-base feather-square block mx-auto mb-1"></i>
                    Kotak
                </button>
                <button onclick="addElement('line')" class="p-2 text-xs text-center border rounded-lg hover:bg-gray-50">
                    <i class="text-base feather-minus block mx-auto mb-1"></i>
                    Garis
                </button>
                <button onclick="addElement('image')" class="p-2 text-xs text-center border rounded-lg hover:bg-gray-50">
                    <i class="text-base feather-image block mx-auto mb-1"></i>
                    Gambar
                </button>
                <button onclick="addElement('variable')" class="p-2 text-xs text-center border rounded-lg hover:bg-gray-50">
                    <i class="text-base feather-box block mx-auto mb-1"></i>
                    Variabel
                </button>
                <button onclick="addElement('table')" class="p-2 text-xs text-center border rounded-lg hover:bg-gray-50">
                    <i class="text-base feather-grid block mx-auto mb-1"></i>
                    Tabel
                </button>
            </div>
        </div>

        {{-- Variables --}}
        <div class="p-3 border-b border-gray-100">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Variabel Tersedia</div>
            <div id="variable-list" class="flex flex-col gap-1 text-xs">
                @foreach($variables as $var)
                    <div class="flex items-center gap-1 p-1.5 bg-gray-50 rounded cursor-move" draggable="true" ondragstart="dragVariable(event, '{{ $var }}')">
                        <span class="font-mono text-indigo-600">{{ '{' . $var . '}' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Table Config (if tables exist) --}}
        <div class="p-3 border-b border-gray-100">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Konfigurasi Tabel</div>
            <div id="table-config-list" class="flex flex-col gap-2 text-xs">
                {{-- Table configurations rendered here --}}
            </div>
        </div>

    </aside>

    {{-- CENTER: Canvas Area --}}
    <main class="flex-1 flex flex-col bg-gray-100 overflow-hidden relative">
        <div id="canvas-wrapper" class="flex-1 overflow-auto flex items-start justify-center p-8">
            <div id="canvas-container" class="shadow-2xl bg-white" style="width: 595px; height: 842px;">
                <canvas id="canvas-main"></canvas>
            </div>
        </div>
    </main>

    {{-- RIGHT: Properties Panel --}}
    <aside class="w-72 bg-white border-l border-gray-100 flex flex-col overflow-y-auto">
        <div class="p-3">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Properties</div>
            <div id="properties-panel" class="text-sm text-gray-500 text-center py-8">
                Pilih elemen untuk mengedit properties
            </div>
        </div>
    </aside>

</div>

{{-- Hidden Data --}}
<input type="hidden" id="templateId" value="{{ $template->id }}">
<input type="hidden" id="templateData" value="{{ json_encode($template->canvas_json) }}">
<input type="hidden" id="tableConfig" value="{{ json_encode($template->table_config) }}">
<input type="hidden" id="generateMode" value="{{ $template->generate_mode }}">
<input type="hidden" id="saveUrl" value="{{ route('dashboard.document-templates.update', $template) }}">

@push('scripts')
<script src="{{ asset('assets/js/template-editor.js') }}"></script>
<script>
    // Table Modal Functionality
    function openTableModal() {
        Swal.fire({
            title: 'Tambah Tabel',
            html: `
                <div class="grid grid-cols-1 gap-4 p-2 text-left">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Mode Tabel</label>
                        <select id="tblMode" class="w-full px-3 py-2 border rounded-lg">
                            <option value="perorang">Per Orang (1 row = 1 PDF)</option>
                            <option value="daftar">Daftar (semua row dalam 1 PDF)</option>
                            <option value="statis">Statis (tanpa data)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Nama Sheet Excel</label>
                        <input type="text" id="tblSheet" class="w-full px-3 py-2 border rounded-lg" value="Data">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Jumlah Baris</label>
                            <input type="number" id="tblRows" class="w-full px-3 py-2 border rounded-lg" value="5" min="1" max="50">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Jumlah Kolom</label>
                            <input type="number" id="tblCols" class="w-full px-3 py-2 border rounded-lg" value="4" min="1" max="20">
                        </div>
                    </div>
                    <div id="linkKeyContainer">
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Linking Key (misal: nisn)</label>
                        <input type="text" id="tblLinkKey" class="w-full px-3 py-2 border rounded-lg" value="nisn">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Tambah Tabel',
            cancelButtonText: 'Batal',
            didOpen: () => {
                const mode = document.getElementById('tblMode');
                const linkKeyContainer = document.getElementById('linkKeyContainer');
                mode.addEventListener('change', (e) => {
                    linkKeyContainer.style.display = e.target.value === 'perorang' ? 'block' : 'none';
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const mode = document.getElementById('tblMode').value;
                const sheet = document.getElementById('tblSheet').value || 'Data';
                const rows = parseInt(document.getElementById('tblRows').value) || 5;
                const cols = parseInt(document.getElementById('tblCols').value) || 4;
                const linkKey = mode === 'perorang' ? (document.getElementById('tblLinkKey').value || 'nisn') : null;

                // Call editor's table function
                if (typeof window.templateEditor !== 'undefined') {
                    window.templateEditor.addTable({
                        mode: mode,
                        sheetName: sheet,
                        rows: rows,
                        cols: cols,
                        linkKey: linkKey
                    });
                }
            }
        });
    }

    function toggleAddMenu() {
        document.getElementById('element-menu').classList.toggle('hidden');
    }

    function toggleExportMenu() {
        document.getElementById('exportMenu').classList.toggle('hidden');
    }

    function previewTemplate() {
        window.open('{{ route('dashboard.document-templates.preview', $template) }}', '_blank');
    }

    function downloadExcelTemplate() {
        window.location.href = '{{ route('dashboard.document-templates.excel-template', $template) }}';
    }

    function exportPDF() {
        alert('Export PDF functionality - implement according to needs');
    }

    function dragVariable(event, variable) {
        event.dataTransfer.setData('text/plain', '{{{{' + variable + '}}}}');
    }

    // Initialize editor after DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const templateData = document.getElementById('templateData').value;
        const tableConfig = document.getElementById('tableConfig').value;

        window.templateEditor = new TemplateEditor({
            templateId: document.getElementById('templateId').value,
            canvasJson: templateData ? JSON.parse(templateData) : null,
            tableConfig: tableConfig ? JSON.parse(tableConfig) : {},
            saveUrl: document.getElementById('saveUrl').value,
            generateMode: document.getElementById('generateMode').value
        });
    });
</script>
@endpush
@endsection