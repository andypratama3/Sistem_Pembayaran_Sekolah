/**
 * starterTemplates.js — Built-in starter blueprints for the Template Editor
 *
 * Each starter is a fully-formed page descriptor with pre-positioned objects
 * and field definitions. Selecting a starter from the gallery resets the
 * editor's pages and fields to these values.
 */

export const STARTER_TEMPLATES = [
  // ═══════════════════════════════════════════════════════════
  // SURAT (Letters)
  // ═══════════════════════════════════════════════════════════
  {
    id: 'kop_surat',
    title: 'Surat Keterangan Kelakuan Baik',
    desc: 'Surat resmi dengan kop sekolah, garis pemisah, dan tanda tangan kepala sekolah.',
    category: 'Surat',
    pageSize: 'f4_portrait',
    icon: 'feather-file-text',
    color: 'from-blue-500 to-indigo-600',
    fields: [
      { id: 'f_doc_num', label: 'Nomor Surat', field_key: 'letter_number', field_type: 'text' },
    ],
    objects: [
      {
        type: 'textbox', left: 97, top: 60, width: 600,
        text: 'PEMERINTAH PROVINSI KALIMANTAN TIMUR\nDINAS PENDIDIKAN DAN KEBUDAYAAN\nSMA NEGERI 1 SAMARINDA',
        fontSize: 16, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.2,
      },
      {
        type: 'textbox', left: 97, top: 130, width: 600,
        text: 'Jl. Pendidikan No. 1, Samarinda | Telp: (0541) 123456\nEmail: info@sman1-smd.sch.id | Website: www.sman1-smd.sch.id',
        fontSize: 10, textAlign: 'center', fontFamily: 'Arial', fill: '#475569', lineHeight: 1.3,
      },
      { type: 'line', left: 97, top: 175, stroke: '#0f172a', strokeWidth: 3, x1: 0, y1: 0, x2: 600, y2: 0 },
      { type: 'line', left: 97, top: 180, stroke: '#0f172a', strokeWidth: 1, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 230, width: 600,
        text: 'SURAT KETERANGAN KELAKUAN BAIK\nNomor: {{letter_number}}',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 97, top: 310, width: 600,
        text: 'Yang bertanda tangan di bawah ini Kepala SMA Negeri 1 Samarinda, menerangkan bahwa:\n\nNama Lengkap  : {{student_name}}\nNISN / NIS          : {{nisn}}\nKelas                  : {{classroom_name}}\n\nAdalah benar siswa aktif pada lembaga pendidikan kami dan selama menempuh pembelajaran berkelakuan baik serta mematuhi seluruh peraturan sekolah.\n\nDemikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.',
        fontSize: 12, textAlign: 'justify', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.6,
      },
      {
        type: 'textbox', left: 420, top: 680, width: 280,
        text: 'Samarinda, {{date}}\nKepala Sekolah,\n\n\n\n\n{{nama_kepsek}}\nNIP. {{nip_kepsek}}',
        fontSize: 12, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#0f172a', lineHeight: 1.4,
      },
    ],
  },

  {
    id: 'surat_keterangan_aktif',
    title: 'Surat Keterangan Siswa Aktif',
    desc: 'Surat keterangan bahwa siswa masih aktif bersekolah, untuk keperluan beasiswa atau administrasi.',
    category: 'Surat',
    pageSize: 'f4_portrait',
    icon: 'feather-user-check',
    color: 'from-sky-500 to-blue-600',
    fields: [
      { id: 'f_purpose', label: 'Keperluan', field_key: 'purpose', field_type: 'text' },
    ],
    objects: [
      {
        type: 'textbox', left: 97, top: 60, width: 600,
        text: 'PEMERINTAH PROVINSI KALIMANTAN TIMUR\nDINAS PENDIDIKAN DAN KEBUDAYAAN\nSMA NEGERI 1 SAMARINDA',
        fontSize: 16, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.2,
      },
      {
        type: 'textbox', left: 97, top: 130, width: 600,
        text: 'Jl. Pendidikan No. 1, Samarinda | Telp: (0541) 123456\nEmail: info@sman1-smd.sch.id',
        fontSize: 10, textAlign: 'center', fontFamily: 'Arial', fill: '#475569', lineHeight: 1.3,
      },
      { type: 'line', left: 97, top: 170, stroke: '#0f172a', strokeWidth: 3, x1: 0, y1: 0, x2: 600, y2: 0 },
      { type: 'line', left: 97, top: 175, stroke: '#0f172a', strokeWidth: 1, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 220, width: 600,
        text: 'SURAT KETERANGAN AKTIF\nNomor: {{letter_number}}',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 97, top: 300, width: 600,
        text: 'Yang bertanda tangan di bawah ini Kepala SMA Negeri 1 Samarinda, dengan ini menerangkan bahwa:\n\nNama                  : {{student_name}}\nNISN                    : {{nisn}}\nTempat/Tgl Lahir : {{student_birth_place}}, {{student_birth_date}}\nKelas                  : {{classroom_name}}\nTahun Ajaran     : {{tahun_ajaran}}\n\nAdalah benar-benar siswa aktif pada sekolah kami.\n\nSurat keterangan ini dibuat untuk keperluan: {{purpose}}\n\nDemikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.',
        fontSize: 12, textAlign: 'left', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.6,
      },
      {
        type: 'textbox', left: 420, top: 720, width: 280,
        text: 'Samarinda, {{date}}\nKepala Sekolah,\n\n\n\n\n{{nama_kepsek}}\nNIP. {{nip_kepsek}}',
        fontSize: 12, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#0f172a', lineHeight: 1.4,
      },
    ],
  },

  {
    id: 'surat_izin_keluar',
    title: 'Surat Izin Meninggalkan Sekolah',
    desc: 'Surat izin siswa meninggalkan sekolah lebih awal karena keperluan tertentu.',
    category: 'Surat',
    pageSize: 'a4_portrait',
    icon: 'feather-log-out',
    color: 'from-violet-500 to-purple-600',
    fields: [
      { id: 'f_reason', label: 'Alasan', field_key: 'reason', field_type: 'text' },
      { id: 'f_time', label: 'Jam Keluar', field_key: 'exit_time', field_type: 'text' },
    ],
    objects: [
      {
        type: 'textbox', left: 97, top: 60, width: 600,
        text: 'SMA NEGERI 1 SAMARINDA\nSURAT IZIN MENINGGALKAN SEKOLAH',
        fontSize: 16, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.3,
      },
      { type: 'line', left: 97, top: 110, stroke: '#0f172a', strokeWidth: 2, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 140, width: 600,
        text: 'Nama Siswa       : {{student_name}}\nKelas                  : {{classroom_name}}\nHari/Tanggal     : {{date}}\nJam Keluar        : {{exit_time}}\nAlasan                : {{reason}}\n\nDengan ini siswa tersebut diizinkan meninggalkan sekolah pada jam pelajaran.',
        fontSize: 12, fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.8,
      },
      {
        type: 'textbox', left: 97, top: 380, width: 200,
        text: 'Orang Tua/Wali,\n\n\n\n\n(______________________)',
        fontSize: 11, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 400, top: 380, width: 200,
        text: 'Guru Piket,\n\n\n\n\n(______________________)',
        fontSize: 11, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.4,
      },
    ],
  },

  // ═══════════════════════════════════════════════════════════
  // SERTIFIKAT (Certificates)
  // ═══════════════════════════════════════════════════════════
  {
    id: 'sertifikat_elegan',
    title: 'Piagam Penghargaan Siswa',
    desc: 'Piagam penghargaan dengan border elegan untuk prestasi siswa.',
    category: 'Sertifikat',
    pageSize: 'a4_landscape',
    icon: 'feather-award',
    color: 'from-amber-500 to-yellow-600',
    fields: [
      { id: 'f_cert_ach', label: 'Prestasi', field_key: 'achievement_title', field_type: 'text' },
      { id: 'f_cert_date', label: 'Tanggal Piagam', field_key: 'award_date', field_type: 'text' },
    ],
    objects: [
      { type: 'rect', left: 30, top: 30, width: 1063, height: 734, fill: 'transparent', stroke: '#b45309', strokeWidth: 3 },
      { type: 'rect', left: 40, top: 40, width: 1043, height: 714, fill: 'transparent', stroke: '#d97706', strokeWidth: 1 },
      {
        type: 'textbox', left: 111, top: 100, width: 900,
        text: 'PIAGAM PENGHARGAAN',
        fontSize: 38, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman', fill: '#1e1b4b',
      },
      {
        type: 'textbox', left: 111, top: 170, width: 900,
        text: 'Diberikan Kepada Peserta Didik Terbaik:',
        fontSize: 16, fontStyle: 'italic', textAlign: 'center', fontFamily: 'Times New Roman', fill: '#475569',
      },
      {
        type: 'textbox', left: 111, top: 240, width: 900,
        text: '{{student_name}}',
        fontSize: 32, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman', fill: '#b45309', underline: true,
      },
      {
        type: 'textbox', left: 161, top: 320, width: 800,
        text: 'Sebagai penghargaan atas prestasi gemilang yang diraih dalam kategori:\n\n" {{achievement_title}} "\n\nDengan dedikasi luar biasa dan nilai akademik terbaik selama tahun pembelajaran aktif sekolah.',
        fontSize: 14, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.5,
      },
      {
        type: 'textbox', left: 111, top: 510, width: 900,
        text: 'Diberikan pada tanggal {{award_date}}',
        fontSize: 12, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#64748b',
      },
      {
        type: 'textbox', left: 160, top: 570, width: 250,
        text: 'Wali Kelas,\n\n\n\n{{wali_kelas}}',
        fontSize: 12, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 700, top: 570, width: 250,
        text: 'Kepala Sekolah,\n\n\n\n{{nama_kepsek}}\nNIP. {{nip_kepsek}}',
        fontSize: 12, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.4,
      },
    ],
  },

  {
    id: 'sertifikat_kelulusan',
    title: 'Surat Keterangan Lulus',
    desc: 'Surat keterangan kelulusan siswa dengan data lengkap dan nilai rata-rata.',
    category: 'Sertifikat',
    pageSize: 'f4_portrait',
    icon: 'feather-check-circle',
    color: 'from-green-500 to-emerald-600',
    fields: [
      { id: 'f_grad_date', label: 'Tanggal Lulus', field_key: 'graduation_date', field_type: 'date' },
      { id: 'f_final_avg', label: 'Nilai Rata-rata', field_key: 'final_average', field_type: 'number' },
    ],
    objects: [
      {
        type: 'textbox', left: 97, top: 60, width: 600,
        text: 'PEMERINTAH PROVINSI KALIMANTAN TIMUR\nDINAS PENDIDIKAN DAN KEBUDAYAAN\nSMA NEGERI 1 SAMARINDA',
        fontSize: 16, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.2,
      },
      {
        type: 'textbox', left: 97, top: 130, width: 600,
        text: 'Jl. Pendidikan No. 1, Samarinda | Telp: (0541) 123456',
        fontSize: 10, textAlign: 'center', fontFamily: 'Arial', fill: '#475569',
      },
      { type: 'line', left: 97, top: 160, stroke: '#0f172a', strokeWidth: 3, x1: 0, y1: 0, x2: 600, y2: 0 },
      { type: 'line', left: 97, top: 165, stroke: '#0f172a', strokeWidth: 1, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 210, width: 600,
        text: 'SURAT KETERANGAN LULUS\nNomor: {{letter_number}}',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 97, top: 290, width: 600,
        text: 'Kepala SMA Negeri 1 Samarinda menerangkan bahwa:\n\nNama                    : {{student_name}}\nNISN                      : {{nisn}}\nTempat/Tgl Lahir  : {{student_birth_place}}, {{student_birth_date}}\nOrang Tua/Wali    : {{student_parent_name}}\n\nTelah dinyatakan LULUS dari SMA Negeri 1 Samarinda pada tanggal {{graduation_date}} dengan nilai rata-rata {{final_average}}.\n\nDemikian surat keterangan ini diberikan untuk dipergunakan sebagaimana mestinya.',
        fontSize: 12, textAlign: 'left', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.7,
      },
      {
        type: 'textbox', left: 420, top: 680, width: 280,
        text: 'Samarinda, {{graduation_date}}\nKepala Sekolah,\n\n\n\n\n{{nama_kepsek}}\nNIP. {{nip_kepsek}}',
        fontSize: 12, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#0f172a', lineHeight: 1.4,
      },
    ],
  },

  // ═══════════════════════════════════════════════════════════
  // RAPOR (Report Cards)
  // ═══════════════════════════════════════════════════════════
  {
    id: 'rapor_nilai',
    title: 'Rapor Nilai Akademik',
    desc: 'Lembar rapor dengan tabel nilai mata pelajaran, data siswa, dan tanda tangan.',
    category: 'Rapor',
    pageSize: 'a4_portrait',
    icon: 'feather-grid',
    color: 'from-emerald-500 to-teal-600',
    fields: [
      { id: 'f_rap_sem', label: 'Semester', field_key: 'semester', field_type: 'text' },
    ],
    objects: [
      {
        type: 'textbox', left: 97, top: 50, width: 600,
        text: 'LAPORAN HASIL BELAJAR PESERTA DIDIK (RAPOR)\nSMA NEGERI 1 SAMARINDA',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Arial', fill: '#0f172a', lineHeight: 1.3,
      },
      { type: 'line', left: 97, top: 95, stroke: '#334155', strokeWidth: 1, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 110, width: 300,
        text: 'Nama Siswa  : {{student_name}}\nNISN / NIS      : {{nisn}}',
        fontSize: 11, fontFamily: 'Arial', fill: '#334155', lineHeight: 1.6,
      },
      {
        type: 'textbox', left: 420, top: 110, width: 280,
        text: 'Kelas          : {{classroom_name}}\nSemester    : {{semester}}\nThn Ajaran : {{tahun_ajaran}}',
        fontSize: 11, fontFamily: 'Arial', fill: '#334155', lineHeight: 1.6,
      },
      {
        type: 'table', left: 97, top: 190, rows: 8, cols: 4,
        columnWidths: [40, 260, 100, 200],
        rowHeights: [32, 32, 32, 32, 32, 32, 32, 32],
        data: [
          ['No', 'Mata Pelajaran', 'Nilai', 'Capaian Kompetensi'],
          ['1', 'Pendidikan Agama', '{{nilai_agama}}', '{{capaian_agama}}'],
          ['2', 'PKn', '{{nilai_ppkn}}', '{{capaian_ppkn}}'],
          ['3', 'Bahasa Indonesia', '{{nilai_indo}}', '{{capaian_indo}}'],
          ['4', 'Matematika', '{{nilai_mtk}}', '{{capaian_mtk}}'],
          ['5', 'Bahasa Inggris', '{{nilai_inggris}}', '{{capaian_inggris}}'],
          ['6', 'Seni Budaya', '{{nilai_seni}}', '{{capaian_seni}}'],
          ['7', 'PJOK', '{{nilai_pjok}}', '{{capaian_pjok}}'],
        ],
        borderColor: '#64748b', tableFontSize: 10, tableFontFamily: 'Arial',
      },
      {
        type: 'textbox', left: 97, top: 500, width: 600,
        text: 'Rata-rata Nilai: {{grade_average}}',
        fontSize: 12, fontWeight: 'bold', fontFamily: 'Arial', fill: '#0f172a',
      },
      {
        type: 'textbox', left: 97, top: 560, width: 200,
        text: 'Orang Tua / Wali,\n\n\n\n(______________________)',
        fontSize: 11, textAlign: 'center', fontFamily: 'Arial', fill: '#1e293b', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 420, top: 560, width: 280,
        text: 'Wali Kelas,\n\n\n\n{{wali_kelas}}\nNIP. {{nip_wali_kelas}}',
        fontSize: 11, textAlign: 'center', fontFamily: 'Arial', fill: '#1e293b', lineHeight: 1.4,
      },
    ],
  },

  {
    id: 'rapor_absensi',
    title: 'Rekap Kehadiran Siswa',
    desc: 'Tabel rekap kehadiran siswa per semester dengan jumlah hadir, sakit, izin, dan alpa.',
    category: 'Rapor',
    pageSize: 'a4_portrait',
    icon: 'feather-calendar',
    color: 'from-cyan-500 to-teal-600',
    fields: [],
    objects: [
      {
        type: 'textbox', left: 97, top: 50, width: 600,
        text: 'REKAP KEHADIRAN PESERTA DIDIK\nSMA NEGERI 1 SAMARINDA',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Arial', fill: '#0f172a', lineHeight: 1.3,
      },
      { type: 'line', left: 97, top: 95, stroke: '#334155', strokeWidth: 1, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 110, width: 600,
        text: 'Nama: {{student_name}}    |    Kelas: {{classroom_name}}    |    Semester: {{semester}}',
        fontSize: 11, fontFamily: 'Arial', fill: '#334155',
      },
      {
        type: 'table', left: 97, top: 160, rows: 5, cols: 3,
        columnWidths: [200, 200, 200],
        rowHeights: [35, 35, 35, 35, 35],
        data: [
          ['Keterangan', 'Jumlah Hari', 'Persentase'],
          ['Hadir', '{{attendance_hadir}}', '-'],
          ['Sakit', '{{attendance_sakit}}', '-'],
          ['Izin', '{{attendance_izin}}', '-'],
          ['Tanpa Keterangan (Alpa)', '{{attendance_alpa}}', '-'],
        ],
        borderColor: '#64748b', tableFontSize: 11, tableFontFamily: 'Arial',
      },
      {
        type: 'textbox', left: 97, top: 380, width: 600,
        text: 'Catatan Wali Kelas:\n________________________________________________________________________\n________________________________________________________________________',
        fontSize: 11, fontFamily: 'Arial', fill: '#334155', lineHeight: 1.8,
      },
      {
        type: 'textbox', left: 420, top: 500, width: 280,
        text: 'Samarinda, {{date}}\nWali Kelas,\n\n\n\n{{wali_kelas}}',
        fontSize: 11, textAlign: 'center', fontFamily: 'Arial', fill: '#1e293b', lineHeight: 1.4,
      },
    ],
  },

  // ═══════════════════════════════════════════════════════════
  // ADMINISTRASI (Administration)
  // ═══════════════════════════════════════════════════════════
  {
    id: 'kartu_pelajar',
    title: 'Kartu Pelajar / ID Card',
    desc: 'Kartu identitas siswa dengan foto, data diri, dan QR code verifikasi.',
    category: 'Administrasi',
    pageSize: 'a4_landscape',
    icon: 'feather-credit-card',
    color: 'from-indigo-500 to-blue-600',
    fields: [],
    objects: [
      { type: 'rect', left: 50, top: 50, width: 500, height: 300, fill: 'transparent', stroke: '#1e40af', strokeWidth: 2 },
      {
        type: 'textbox', left: 70, top: 70, width: 460,
        text: 'KARTU PELAJAR\nSMA NEGERI 1 SAMARINDA',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Arial', fill: '#1e3a5f', lineHeight: 1.3,
      },
      { type: 'line', left: 70, top: 110, stroke: '#1e40af', strokeWidth: 1, x1: 0, y1: 0, x2: 460, y2: 0 },
      { type: 'rect', left: 80, top: 130, width: 100, height: 130, fill: '#f1f5f9', stroke: '#94a3b8', strokeWidth: 1 },
      {
        type: 'textbox', left: 200, top: 130, width: 300,
        text: 'Nama    : {{student_name}}\nNISN     : {{nisn}}\nKelas    : {{classroom_name}}\nAlamat  : {{student_address}}\nThn Ajaran : {{tahun_ajaran}}',
        fontSize: 10, fontFamily: 'Arial', fill: '#1e293b', lineHeight: 1.7,
      },
      {
        type: 'textbox', left: 80, top: 270, width: 100,
        text: 'Foto 3x4',
        fontSize: 9, textAlign: 'center', fontFamily: 'Arial', fill: '#94a3b8',
      },
      {
        type: 'textbox', left: 380, top: 300, width: 140,
        text: 'Kepala Sekolah,\n\n{{nama_kepsek}}',
        fontSize: 9, textAlign: 'center', fontFamily: 'Arial', fill: '#1e293b', lineHeight: 1.3,
      },
    ],
  },

  {
    id: 'surat_pindah',
    title: 'Surat Keterangan Pindah Sekolah',
    desc: 'Surat keterangan pindah sekolah dengan data lengkap siswa dan alasan kepindahan.',
    category: 'Administrasi',
    pageSize: 'f4_portrait',
    icon: 'feather-navigation',
    color: 'from-rose-500 to-pink-600',
    fields: [
      { id: 'f_dest_school', label: 'Sekolah Tujuan', field_key: 'destination_school', field_type: 'text' },
      { id: 'f_transfer_reason', label: 'Alasan Pindah', field_key: 'transfer_reason', field_type: 'text' },
    ],
    objects: [
      {
        type: 'textbox', left: 97, top: 60, width: 600,
        text: 'PEMERINTAH PROVINSI KALIMANTAN TIMUR\nDINAS PENDIDIKAN DAN KEBUDAYAAN\nSMA NEGERI 1 SAMARINDA',
        fontSize: 16, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.2,
      },
      { type: 'line', left: 97, top: 130, stroke: '#0f172a', strokeWidth: 2, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 170, width: 600,
        text: 'SURAT KETERANGAN PINDAH SEKOLAH\nNomor: {{letter_number}}',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 97, top: 250, width: 600,
        text: 'Yang bertanda tangan di bawah ini menerangkan bahwa:\n\nNama                    : {{student_name}}\nNISN                      : {{nisn}}\nKelas                    : {{classroom_name}}\nAlamat                  : {{student_address}}\nNama Orang Tua  : {{student_parent_name}}\n\nDengan ini dinyatakan PINDAH dari SMA Negeri 1 Samarinda.\n\nSekolah Tujuan   : {{destination_school}}\nAlasan Pindah     : {{transfer_reason}}\n\nDemikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.',
        fontSize: 12, textAlign: 'left', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.6,
      },
      {
        type: 'textbox', left: 420, top: 700, width: 280,
        text: 'Samarinda, {{date}}\nKepala Sekolah,\n\n\n\n\n{{nama_kepsek}}\nNIP. {{nip_kepsek}}',
        fontSize: 12, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#0f172a', lineHeight: 1.4,
      },
    ],
  },

  {
    id: 'undangan_ortu',
    title: 'Undangan Orang Tua / Wali',
    desc: 'Surat undangan rapat orang tua atau pengambilan rapor.',
    category: 'Administrasi',
    pageSize: 'a4_portrait',
    icon: 'feather-mail',
    color: 'from-orange-500 to-red-500',
    fields: [
      { id: 'f_event', label: 'Acara', field_key: 'event_name', field_type: 'text' },
      { id: 'f_event_date', label: 'Tanggal Acara', field_key: 'event_date', field_type: 'date' },
      { id: 'f_event_time', label: 'Waktu', field_key: 'event_time', field_type: 'text' },
    ],
    objects: [
      {
        type: 'textbox', left: 97, top: 60, width: 600,
        text: 'SMA NEGERI 1 SAMARINDA',
        fontSize: 16, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman', fill: '#0f172a',
      },
      {
        type: 'textbox', left: 97, top: 85, width: 600,
        text: 'Jl. Pendidikan No. 1, Samarinda | Telp: (0541) 123456',
        fontSize: 10, textAlign: 'center', fontFamily: 'Arial', fill: '#475569',
      },
      { type: 'line', left: 97, top: 110, stroke: '#0f172a', strokeWidth: 2, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 140, width: 300,
        text: 'Nomor    : {{letter_number}}\nLampiran : -\nHal          : Undangan',
        fontSize: 11, fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.5,
      },
      {
        type: 'textbox', left: 97, top: 220, width: 600,
        text: 'Kepada Yth.\nBapak/Ibu Orang Tua/Wali dari:\n\nNama  : {{student_name}}\nKelas  : {{classroom_name}}\n\nDengan hormat,\nBersama ini kami mengundang Bapak/Ibu untuk hadir pada:\n\nAcara      : {{event_name}}\nHari/Tgl  : {{event_date}}\nWaktu     : {{event_time}}\nTempat   : Aula SMA Negeri 1 Samarinda\n\nDemikian undangan ini kami sampaikan. Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih.',
        fontSize: 12, textAlign: 'left', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.6,
      },
      {
        type: 'textbox', left: 420, top: 620, width: 280,
        text: 'Samarinda, {{date}}\nKepala Sekolah,\n\n\n\n{{nama_kepsek}}\nNIP. {{nip_kepsek}}',
        fontSize: 11, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#0f172a', lineHeight: 1.4,
      },
    ],
  },

  // ═══════════════════════════════════════════════════════════
  // EKSKUL & KEGIATAN (Extracurricular)
  // ═══════════════════════════════════════════════════════════
  {
    id: 'sertifikat_ekskul',
    title: 'Sertifikat Ekstrakurikuler',
    desc: 'Sertifikat keikutsertaan siswa dalam kegiatan ekstrakurikuler.',
    category: 'Ekskul',
    pageSize: 'a4_landscape',
    icon: 'feather-activity',
    color: 'from-fuchsia-500 to-purple-600',
    fields: [
      { id: 'f_ekskul_name', label: 'Nama Ekskul', field_key: 'ekskul_name', field_type: 'text' },
      { id: 'f_ekskul_grade', label: 'Predikat', field_key: 'ekskul_grade', field_type: 'text' },
    ],
    objects: [
      { type: 'rect', left: 30, top: 30, width: 1063, height: 734, fill: 'transparent', stroke: '#7c3aed', strokeWidth: 2 },
      {
        type: 'textbox', left: 111, top: 80, width: 900,
        text: 'SMA NEGERI 1 SAMARINDA',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Arial', fill: '#475569',
      },
      {
        type: 'textbox', left: 111, top: 130, width: 900,
        text: 'SERTIFIKAT',
        fontSize: 36, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman', fill: '#4c1d95',
      },
      {
        type: 'textbox', left: 111, top: 190, width: 900,
        text: 'Diberikan kepada:',
        fontSize: 14, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#475569',
      },
      {
        type: 'textbox', left: 111, top: 240, width: 900,
        text: '{{student_name}}',
        fontSize: 28, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman', fill: '#1e1b4b',
      },
      {
        type: 'textbox', left: 161, top: 310, width: 800,
        text: 'Atas partisipasi aktif dalam kegiatan Ekstrakurikuler:\n\n{{ekskul_name}}\n\nDengan predikat: {{ekskul_grade}}',
        fontSize: 14, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#334155', lineHeight: 1.6,
      },
      {
        type: 'textbox', left: 111, top: 490, width: 900,
        text: 'Tahun Ajaran {{tahun_ajaran}}',
        fontSize: 11, textAlign: 'center', fontFamily: 'Arial', fill: '#64748b',
      },
      {
        type: 'textbox', left: 160, top: 550, width: 250,
        text: 'Pembina Ekskul,\n\n\n\n(______________________)',
        fontSize: 11, textAlign: 'center', fontFamily: 'Arial', fill: '#1e293b', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 700, top: 550, width: 250,
        text: 'Kepala Sekolah,\n\n\n\n{{nama_kepsek}}\nNIP. {{nip_kepsek}}',
        fontSize: 11, textAlign: 'center', fontFamily: 'Arial', fill: '#1e293b', lineHeight: 1.4,
      },
    ],
  },

  {
    id: 'surat_rekomendasi',
    title: 'Surat Rekomendasi Siswa',
    desc: 'Surat rekomendasi dari sekolah untuk keperluan beasiswa atau pendaftaran perguruan tinggi.',
    category: 'Surat',
    pageSize: 'f4_portrait',
    icon: 'feather-thumbs-up',
    color: 'from-teal-500 to-cyan-600',
    fields: [
      { id: 'f_rec_purpose', label: 'Tujuan Rekomendasi', field_key: 'recommendation_purpose', field_type: 'text' },
    ],
    objects: [
      {
        type: 'textbox', left: 97, top: 60, width: 600,
        text: 'PEMERINTAH PROVINSI KALIMANTAN TIMUR\nDINAS PENDIDIKAN DAN KEBUDAYAAN\nSMA NEGERI 1 SAMARINDA',
        fontSize: 16, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.2,
      },
      { type: 'line', left: 97, top: 130, stroke: '#0f172a', strokeWidth: 2, x1: 0, y1: 0, x2: 600, y2: 0 },
      {
        type: 'textbox', left: 97, top: 170, width: 600,
        text: 'SURAT REKOMENDASI\nNomor: {{letter_number}}',
        fontSize: 14, fontWeight: 'bold', textAlign: 'center', fontFamily: 'Times New Roman',
        fill: '#0f172a', lineHeight: 1.4,
      },
      {
        type: 'textbox', left: 97, top: 250, width: 600,
        text: 'Yang bertanda tangan di bawah ini:\n\nNama      : {{nama_kepsek}}\nJabatan  : Kepala SMA Negeri 1 Samarinda\nNIP         : {{nip_kepsek}}\n\nDengan ini memberikan rekomendasi kepada:\n\nNama                    : {{student_name}}\nNISN                      : {{nisn}}\nKelas                    : {{classroom_name}}\nNilai Rata-rata     : {{grade_average}}\n\nSiswa tersebut memiliki prestasi akademik yang baik, berkelakuan baik, dan aktif dalam kegiatan sekolah. Kami merekomendasikan yang bersangkutan untuk:\n\n{{recommendation_purpose}}\n\nDemikian surat rekomendasi ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.',
        fontSize: 12, textAlign: 'left', fontFamily: 'Times New Roman', fill: '#1e293b', lineHeight: 1.6,
      },
      {
        type: 'textbox', left: 420, top: 780, width: 280,
        text: 'Samarinda, {{date}}\nKepala Sekolah,\n\n\n\n\n{{nama_kepsek}}\nNIP. {{nip_kepsek}}',
        fontSize: 12, textAlign: 'center', fontFamily: 'Times New Roman', fill: '#0f172a', lineHeight: 1.4,
      },
    ],
  },
];

/**
 * Default fields injected into every starter (always present at the top of the field list).
 */
export const STARTER_DEFAULT_FIELDS = [
  { id: 'f_std_name', label: 'Nama Siswa', field_key: 'student_name', field_type: 'text' },
  { id: 'f_std_nisn', label: 'NISN', field_key: 'nisn', field_type: 'text' },
  { id: 'f_std_yr', label: 'Tahun Ajaran', field_key: 'tahun_ajaran', field_type: 'text' },
  { id: 'f_std_date', label: 'Tanggal Cetak', field_key: 'date', field_type: 'text' },
];
