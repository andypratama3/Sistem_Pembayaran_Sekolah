{{-- resources/views/dashboard/payroll/partials/diagram-alur.blade.php --}}
<div class="payroll-diagram-wrapper" style="font-family: 'Inter', sans-serif; padding: 24px; background: #f8f9fa; border-radius: 16px;">

  <p style="text-align:center; color:#888; font-size:13px; margin-bottom:4px;">
    ★ = komponen khusus tenaga pendidik | klik node untuk detail
  </p>

  <div class="pd-flow">

    {{-- Layer 1: Input --}}
    <div class="pd-row" id="layer-input">
      @foreach([
        ['label' => 'Data karyawan',   'key' => 'karyawan'],
        ['label' => 'Grade / UMK',     'key' => 'grade'],
        ['label' => 'Absensi & JP',    'key' => 'absensi'],
        ['label' => 'Sertifikasi PPG', 'key' => 'ppg'],
      ] as $node)
      <div class="pd-node pd-node--input" data-key="{{ $node['key'] }}">
        {{ $node['label'] }}
      </div>
      @endforeach
    </div>

    {{-- Connector: Layer 1 → Layer 2 --}}
    <div class="pd-connectors pd-connectors--4"></div>

    {{-- Layer 2: Kalkulasi Komponen Gaji --}}
    <div class="pd-container">
      <span class="pd-container-label">Kalkulasi komponen gaji</span>

      <div class="pd-sublabel">Komponen tetap</div>
      <div class="pd-row">
        @foreach($diagramComponents['tetap'] ?? [
          ['label' => 'Gaji pokok',    'sub' => 'Grade atau UMK',    'type' => 'fixed'],
          ['label' => 'T. masa kerja', 'sub' => 'Seniority × rate',  'type' => 'fixed'],
          ['label' => 'T. pendidikan', 'sub' => 'Level pendidikan',  'type' => 'fixed'],
          ['label' => 'T. struktural', 'sub' => 'Posisi jabatan',    'type' => 'fixed'],
        ] as $node)
        <div class="pd-node pd-node--{{ $node['type'] }}">
          <strong>{{ $node['label'] }}</strong>
          <small>{{ $node['sub'] }}</small>
        </div>
        @endforeach
      </div>

      <div class="pd-sublabel">Komponen variabel</div>
      <div class="pd-row">
        @foreach($diagramComponents['variabel'] ?? [
          ['label' => 'T. fungsional',  'sub' => 'Σ jabatan aktif',    'type' => 'variable', 'star' => false],
          ['label' => 'Jam mengajar',   'sub' => 'JP × tarif/jam',     'type' => 'special', 'star' => true],
          ['label' => 'T. keluarga',    'sub' => 'Tanggungan × tarif', 'type' => 'variable', 'star' => false],
          ['label' => 'T. profesi',     'sub' => 'Rp 2jt/bln (PPG)',   'type' => 'special', 'star' => true],
        ] as $node)
        <div class="pd-node pd-node--{{ $node['type'] === 'special' ? 'variable-special' : 'variable-general' }}">
          <strong>{{ $node['label'] }}{{ $node['star'] ? ' ★' : '' }}</strong>
          <small>{{ $node['sub'] }}</small>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Connector: Layer 2 → Bruto --}}
    <div class="pd-connector-single"></div>

    {{-- Layer 3: Bruto --}}
    <div class="pd-row pd-row--centered">
      <div class="pd-node pd-node--bruto pd-node--wide">
        <strong>Akumulasi gaji bruto</strong>
        <small>Σ gaji pokok + semua tunjangan</small>
      </div>
    </div>

    {{-- Connector: Bruto → Bersih (LANGSUNG, tanpa potongan) --}}
    <div class="pd-connector-single"></div>

    {{-- Layer 4: Gaji Bersih --}}
    <div class="pd-row pd-row--centered">
      <div class="pd-node pd-node--bersih pd-node--wide">
        <strong>Gaji bersih (take home pay)</strong>
        <small>Bruto – total potongan</small>
      </div>
    </div>

    {{-- Connector: Bersih → Dokumen --}}
    <div class="pd-connectors pd-connectors--3"></div>

    {{-- Layer 5: Output Dokumen --}}
    <div class="pd-row" id="layer-output">
      @foreach([
        ['label' => 'Slip gaji (PDF)', 'route' => 'dashboard.payroll.slip'],
        ['label' => 'Rekap bulanan',   'route' => 'dashboard.payroll.index'], {{-- Fallback route --}}
        ['label' => 'Transfer bank',   'route' => '#'],
      ] as $doc)
      <a href="{{ $doc['route'] !== '#' && Route::has($doc['route']) ? route($doc['route'], $employee->id ?? '#') : '#' }}"
         class="pd-node pd-node--output">
        {{ $doc['label'] }}
      </a>
      @endforeach
    </div>

    {{-- Legend --}}
    <div class="pd-legend">
      <span class="pd-legend-item pd-legend-item--general">Komponen umum</span>
      <span class="pd-legend-item pd-legend-item--special">★ Khusus pendidik</span>
      <span class="pd-legend-item pd-legend-item--output">Net / output</span>
    </div>

  </div>{{-- /.pd-flow --}}
</div>

<style>
.pd-flow { display: flex; flex-direction: column; align-items: center; gap: 0; }
.pd-row { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.pd-row--centered { justify-content: center; }
.pd-node {
  border-radius: 10px;
  padding: 12px 18px;
  color: #fff;
  font-size: 14px;
  text-align: center;
  cursor: default;
  transition: transform .15s, box-shadow .15s;
  min-width: 140px;
}
.pd-node:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.25); }
.pd-node strong { display: block; font-weight: 600; }
.pd-node small  { display: block; font-size: 11px; opacity: .8; margin-top: 3px; }
.pd-node--wide  { min-width: 320px; }

.pd-node--input           { background: #1a3a5c; }
.pd-node--fixed           { background: #2d2d7a; }
.pd-node--variable-general{ background: #3a2d7a; }
.pd-node--variable-special { background: #1a4a2a; }
.pd-node--bruto           { background: #7a3a00; }
.pd-node--bersih          { background: #1a4a1a; }
.pd-node--output          { background: #3a3a3a; text-decoration: none; color: #fff; }

.pd-container {
  border: 2px dashed #6a6aaa;
  border-radius: 14px;
  padding: 16px 20px;
  width: 100%;
  max-width: 680px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  position: relative;
  margin: 4px 0;
}
.pd-container-label {
  position: absolute;
  top: -12px;
  left: 20px;
  background: #f8f9fa;
  padding: 0 8px;
  font-size: 12px;
  color: #6a6aaa;
  font-weight: 600;
}
.pd-sublabel { font-size: 12px; color: #999; font-weight: 500; margin-bottom: -4px; }

.pd-connector-single {
  width: 2px;
  height: 32px;
  background: #ccc;
  margin: 0 auto;
}
.pd-connectors--4 {
  display: flex;
  gap: 120px;
  height: 28px;
  align-items: flex-end;
}
.pd-connectors--4::before,
.pd-connectors--4::after { content:''; width:2px; height:100%; background:#ccc; }
.pd-connectors--3 {
  display: flex;
  gap: 100px;
  height: 28px;
  align-items: flex-end;
}
.pd-connectors--3::before { content:''; width:2px; height:100%; background:#ccc; }
.pd-connectors--3::after  { content:''; width:2px; height:100%; background:#ccc; }

.pd-legend {
  display: flex;
  gap: 20px;
  margin-top: 16px;
  font-size: 12px;
  flex-wrap: wrap;
  justify-content: center;
}
.pd-legend-item { display: flex; align-items: center; gap: 6px; }
.pd-legend-item::before {
  content: '';
  width: 14px;
  height: 14px;
  border-radius: 3px;
  display: inline-block;
}
.pd-legend-item--general::before  { background: #3a2d7a; }
.pd-legend-item--special::before  { background: #1a4a2a; }
.pd-legend-item--output::before   { background: #1a4a1a; }
</style>
