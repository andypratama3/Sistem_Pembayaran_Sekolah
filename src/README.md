# 🤖 Template Editor — OpenCode Full Audit System

Sistem multi-agent untuk melakukan audit penuh dan perbaikan bug pada Template Editor menggunakan OpenCode.

---

## 📦 CARA INSTALL

### Step 1 — Copy File ke Project Root
```bash
cp -r .opencode/       /path/to/your/laravel-project/.opencode/
cp opencode.json       /path/to/your/laravel-project/opencode.json
cp AGENTS.md           /path/to/your/laravel-project/AGENTS.md
```

### Step 2 — Masuk ke Project
```bash
cd /path/to/your/laravel-project
opencode
```

### Step 3 — Jalankan Audit

**Cara 1 — Via Custom Command (REKOMENDASI):**
```
/audit-template-editor
```

**Cara 2 — Pilih Agent AuditMaster lalu ketik:**
```
Lakukan full audit dan perbaikan seluruh sistem Template Editor sesuai protokol.
```

**Cara 3 — Invoke sub-agent langsung (untuk audit parsial):**
```
@code-architect Audit TemplateController.php untuk bugs
@frontier-js    Audit template-editor.js untuk memory leaks
@security-hunter Scan seluruh sistem untuk vulnerabilities
```

---

## 🗂️ STRUKTUR FILE

```
.opencode/
├── agents/
│   ├── audit-master.md          ← Orchestrator utama
│   ├── code-architect.md        ← PHP specialist
│   ├── frontier-js.md           ← JavaScript specialist
│   ├── blade-guardian.md        ← Blade specialist
│   ├── route-warden.md          ← Routes specialist
│   ├── db-sentinel.md           ← Database specialist
│   ├── security-hunter.md       ← Security specialist
│   └── integration-validator.md ← Integration specialist
└── commands/
    └── audit-template-editor.md ← Custom /command
opencode.json                    ← Agent configuration
AGENTS.md                        ← Project context
```

---

## 🎯 AGENT ROLES

| Agent | Spesialisasi | Invoke Untuk |
|-------|-------------|-------------|
| `AuditMaster` | Orchestrator | Full system audit |
| `@code-architect` | PHP/Laravel | Controller bugs, validation |
| `@frontier-js` | JavaScript | Canvas bugs, memory leaks |
| `@blade-guardian` | Blade | XSS, script loading, layout |
| `@route-warden` | Routes | Middleware, naming, breadcrumbs |
| `@db-sentinel` | Database | N+1, relationships, integrity |
| `@security-hunter` | Security | CSRF, IDOR, injection |
| `@integration-validator` | Integration | JS↔PHP contract |

---

## ⚡ QUICK TIPS

- Gunakan **Tab key** untuk switch antara primary agents
- Gunakan **@agent-name** untuk invoke sub-agent dari dalam conversation
- Setelah `/audit-template-editor`, AuditMaster akan otomatis invoke semua sub-agents secara paralel
- Jika audit terlalu lama, invoke per-agent satu per satu
- Gunakan `/undo` jika ada perubahan yang tidak diinginkan
