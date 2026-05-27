<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppBotService - Meta WhatsApp Chatbot
 */
class WhatsAppBotService
{
    private const SESSION_TTL = 86400;

    private const SESSION_PREFIX = 'wa_session:';

    private const MAIN_MENU = [
        1 => ['label' => 'Cek Tagihan / SPP',            'intent' => 'check_payment', 'emoji' => '💳'],
        2 => ['label' => 'Cara Pembayaran',               'intent' => 'faq_payment',   'emoji' => '🏦'],
        3 => ['label' => 'Info NISN',                     'intent' => 'faq_nisn',      'emoji' => '🔢'],
        4 => ['label' => 'Tagihan Belum Update',          'intent' => 'faq_delayed',   'emoji' => '⏳'],
        5 => ['label' => 'Jadwal & Info Sekolah',         'intent' => 'faq_schedule',  'emoji' => '📅'],
        6 => ['label' => 'Pendaftaran Siswa Baru (PPDB)', 'intent' => 'faq_ppdb',      'emoji' => '🏫'],
        7 => ['label' => 'Izin Tidak Masuk',              'intent' => 'faq_leave',     'emoji' => '📝'],
        8 => ['label' => 'Ekstrakurikuler & Kegiatan',    'intent' => 'faq_activity',  'emoji' => '🎯'],
        9 => ['label' => 'Kontak & Jam Operasional TU',   'intent' => 'faq_contact',   'emoji' => '📞'],
        10 => ['label' => 'Lainnya / Tanya Bebas',         'intent' => 'free_ask',      'emoji' => '💬'],
    ];

    public function __construct(
        private readonly WhatsappMetaService $whatsapp
    ) {}

    /**
     * Main entry point for handling messages
     */
    public function handle(string $phone, string $messageText, string $profileName = 'Bapak/Ibu'): void
    {
        $startTime = microtime(true);

        try {
            Log::channel('whatsapp')->info('🤖 Bot handle', [
                'phone' => $this->maskPhone($phone),
                'message' => mb_substr($messageText, 0, 50),
            ]);

            $session = $this->getSession($phone);
            $intent = $this->detectIntent($messageText, $session['state']);

            Log::channel('whatsapp')->info('🧠 Intent detected', [
                'state' => $session['state'],
                'intent' => $intent,
            ]);

            $replyMessage = $this->route($intent, $phone, $messageText, $profileName, $session);

            $this->whatsapp->sendMessage($phone, $replyMessage);

            $ms = (int) ((microtime(true) - $startTime) * 1000);
            Log::channel('whatsapp')->info('✅ Bot reply sent', ['ms' => $ms]);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Bot handle error', [
                'error' => $e->getMessage(),
                'phone' => $this->maskPhone($phone),
            ]);
        }
    }

    /**
     * Detect user intent from message text
     */
    private function detectIntent(string $text, string $state): string
    {
        $clean = trim($text);
        $lower = mb_strtolower($clean);

        // Exact 10 digits = NISN
        if (preg_match('/^\d{10}$/', $clean)) {
            return 'nisn_input';
        }

        // Menu selection (1-10)
        if (preg_match('/^(10|[0-9])$/', $clean)) {
            return $clean === '0' ? 'back_to_menu' : 'menu_selection';
        }

        // Greetings
        if ($this->matchKeywords($lower, ['assalamualaikum', 'assalamu', 'aslm'])) {
            return 'islamic_greeting';
        }

        if ($this->matchKeywords($lower, ['halo', 'hai', 'hello', 'pagi', 'siang', 'sore', 'malam'])) {
            return 'greeting';
        }

        // Payment keywords
        if ($this->matchKeywords($lower, ['spp', 'tagihan', 'bayar', 'cek'])) {
            return $state === 'verified' ? 'recheck' : 'check_payment';
        }

        // Help/guidance
        if ($this->matchKeywords($lower, ['bantuan', 'help', 'cara', 'gimana', 'bagaimana'])) {
            return 'help';
        }

        // Thanks
        if ($this->matchKeywords($lower, ['terima kasih', 'makasih', 'thanks', 'jazakallah'])) {
            return 'thanks';
        }

        // Goodbye
        if ($this->matchKeywords($lower, ['sampai jumpa', 'bye', 'dadah', 'wassalam'])) {
            return 'goodbye';
        }

        // Fallback
        return $state === 'verified' ? 'unknown_verified' : 'unknown_new';
    }

    /**
     * Simple keyword matching helper
     */
    private function matchKeywords(string $text, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Route to appropriate handler based on intent
     */
    private function route(
        string $intent,
        string $phone,
        string $messageText,
        string $profileName,
        array $session
    ): string {
        return match ($intent) {
            'islamic_greeting' => $this->handleIslamicGreeting($phone, $profileName, $session),
            'greeting' => $this->handleGreeting($phone, $profileName, $session),
            'menu_selection' => $this->handleMenuSelection($phone, $messageText, $session),
            'back_to_menu' => $this->handleBackToMenu($phone, $session),
            'nisn_input' => $this->handleNisnInput($phone, $messageText, $session),
            'check_payment' => $this->handleCheckPayment($phone, $profileName, $session),
            'recheck' => $this->handleRecheck($phone, $session),
            'faq_payment',
            'faq_nisn',
            'faq_delayed',
            'faq_schedule',
            'faq_ppdb',
            'faq_leave',
            'faq_activity',
            'faq_contact' => $this->handleFaqMenu($phone, $intent, $session),
            'free_ask' => $this->handleFreeAsk($phone, $session),
            'help' => $this->handleHelp($phone, $session),
            'thanks' => $this->handleThanks($phone, $session),
            'goodbye' => $this->handleGoodbye($phone, $session),
            'unknown_verified' => $this->handleUnknownVerified($phone, $session),
            default => $this->handleUnknownNew($phone, $profileName, $session),
        };
    }

    // =========================================================================
    // HANDLERS
    // =========================================================================

    private function handleIslamicGreeting(string $phone, string $profileName, array $session): string
    {
        $this->updateSession($phone, ['state' => 'menu']);

        return $this->buildWelcomeMessage($profileName)."\n\n".$this->buildMainMenu();
    }

    private function handleGreeting(string $phone, string $profileName, array $session): string
    {
        $this->updateSession($phone, ['state' => 'menu']);

        return $this->buildWelcomeMessage($profileName)."\n\n".$this->buildMainMenu();
    }

    private function handleMenuSelection(string $phone, string $messageText, array $session): string
    {
        $no = (int) trim($messageText);
        $item = self::MAIN_MENU[$no] ?? null;

        if (! $item) {
            return 'Pilihan tidak valid. Silakan pilih angka *1–10*.';
        }

        $intent = $item['intent'];

        if ($intent === 'check_payment') {
            return $this->handleCheckPayment($phone, 'Bapak/Ibu', $session);
        }

        if ($intent === 'free_ask') {
            return $this->handleFreeAsk($phone, $session);
        }

        return $this->handleFaqMenu($phone, $intent, $session);
    }

    private function handleBackToMenu(string $phone, array $session): string
    {
        $this->updateSession($phone, ['state' => 'menu']);

        return "📋 *Menu Utama*\n\n".$this->buildMainMenu();
    }

    private function handleCheckPayment(string $phone, string $profileName, array $session): string
    {
        if ($session['state'] === 'verified' && $session['nisn']) {
            return $this->buildBillingInfoMessage($session['nisn']);
        }

        $this->updateSession($phone, ['state' => 'waiting_nisn']);

        return "💳 *Cek Tagihan Sekolah*\n\nSilakan kirimkan *NISN* putra/putri Anda (10 digit).\n\n_Contoh: 1234567890_";
    }

    private function handleNisnInput(string $phone, string $messageText, array $session): string
    {
        $nisn = trim($messageText);

        if (! preg_match('/^\d{10}$/', $nisn)) {
            return "❌ Format NISN tidak valid. Harus 10 digit angka.\n\n_Contoh: 1234567890_";
        }

        $this->updateSession($phone, [
            'state' => 'verified',
            'nisn' => $nisn,
        ]);

        return $this->buildBillingInfoMessage($nisn);
    }

    private function handleRecheck(string $phone, array $session): string
    {
        if (! $session['nisn']) {
            $this->updateSession($phone, ['state' => 'waiting_nisn']);

            return 'Sesi Anda berakhir. Silakan kirimkan *NISN* kembali.';
        }

        return $this->buildBillingInfoMessage($session['nisn'], true);
    }

    private function buildBillingInfoMessage(string $nisn, bool $isRecheck = false): string
    {
        $student = Student::where('nisn', $nisn)->first();

        if (! $student) {
            return "❌ *Siswa tidak ditemukan*\n\nMaaf, NISN *{$nisn}* tidak terdaftar di sistem kami. Mohon periksa kembali atau hubungi Tata Usaha.";
        }

        $payments = Payment::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'expired'])
            ->with('paymentTitle')
            ->orderBy('created_at', 'desc')
            ->get();

        $header = $isRecheck ? '🔄 *Informasi Tagihan Terbaru*' : '✅ *NISN Terverifikasi!*';
        $msg = "{$header}\n\n";
        $msg .= "Nama: *{$student->name}*\n";
        $msg .= "NISN: *{$student->nisn}*\n";
        $msg .= 'Kelas: *'.($student->classroom->name ?? '-')."*\n";
        $msg .= "━━━━━━━━━━━━━━━━━\n\n";

        if ($payments->isEmpty()) {
            $msg .= "🎉 *Alhamdulillah!*\nSaat ini tidak ada tagihan tertunggak untuk siswa tersebut.\n\n";
        } else {
            $msg .= "📌 *Daftar Tagihan:*\n";
            $total = 0;
            foreach ($payments as $idx => $p) {
                $num = $idx + 1;
                $title = $p->paymentTitle->name ?? 'Pembayaran';
                $amount = number_format($p->gross_amount, 0, ',', '.');
                $total += $p->gross_amount;
                $msg .= "{$num}. *{$title}*\n   💰 Rp {$amount}\n";
            }
            $msg .= "\n💵 *Total Tunggakan:* \n   *Rp ".number_format($total, 0, ',', '.')."*\n\n";
            $msg .= "📍 *Cara Bayar:*\nKetik *2* untuk melihat instruksi pembayaran.";
        }

        $msg .= "\n\n━━━━━━━━━━━━━━━━━\n";
        $msg .= 'Ketik *0* atau *menu* untuk kembali.';

        return $msg;
    }

    private function handleFaqMenu(string $phone, string $intent, array $session): string
    {
        $answers = [
            'faq_payment' => "🏦 *Cara Pembayaran*\n\n1️⃣ Kirim NISN ke bot ini\n2️⃣ Cek nomor Virtual Account (VA)\n3️⃣ Transfer ke bank yang ditentukan\n4️⃣ Atau bayar tunai ke TU sekolah\n\n✅ Konfirmasi otomatis dalam 1x24 jam",

            'faq_nisn' => "🔢 *Info NISN*\n\nNISN = Nomor Induk Siswa Nasional (10 digit unik)\n\n📍 Bisa ditemukan di:\n• Rapor semester\n• Kartu pelajar\n• Situs: nisn.data.kemdikbud.go.id\n\n❓ Lupa NISN? Hubungi Tata Usaha",

            'faq_delayed' => "⏳ *Pembayaran Belum Terupdate?*\n\nVerifikasi memerlukan 1x24 jam.\n\n✅ Jika sudah >24 jam:\n• Kirim bukti transfer ke TU\n• Hubungi Tata Usaha langsung",

            'faq_schedule' => "📅 *Jadwal & Info Sekolah*\n\n📍 Cek melalui:\n• Wali kelas via WhatsApp\n• Grup WhatsApp kelas\n• Tata Usaha sekolah\n\n❓ Hubungi wali kelas untuk info terbaru",

            'faq_ppdb' => "🏫 *Pendaftaran Siswa Baru (PPDB)*\n\n📄 Dokumen yang perlu:\n• Akta kelahiran\n• Kartu Keluarga\n• Pas foto 3x4\n• Ijazah/STTB TK\n\n📍 Daftar ke Tata Usaha sekolah",

            'faq_leave' => "📝 *Izin Tidak Masuk*\n\nCara izin:\n1️⃣ Hubungi wali kelas via WA\n2️⃣ Sampaikan alasan & durasi\n3️⃣ Sakit >2 hari: surat dokter\n4️⃣ Lainnya: surat izin tertulis",

            'faq_activity' => "🎯 *Ekstrakurikuler & Kegiatan*\n\nSD Muhammadiyah 3 memiliki berbagai ekskul untuk pengembangan bakat.\n\n📍 Info dan pendaftaran:\n• Tanya wali kelas\n• Tata Usaha sekolah\n\n⏰ Diumumkan di awal tahun ajaran",

            'faq_contact' => "📞 *Kontak & Jam Operasional*\n\n🏫 *SD Muhammadiyah 3 Samarinda*\n📍 Samarinda, Kalimantan Timur\n\n⏰ Jam TU:\nSenin - Jumat | 08:00 - 15:00\n\n📱 Hubungi langsung ke sekolah",
        ];

        $answer = $answers[$intent] ?? 'Informasi tidak tersedia.';

        $answer .= "\n\n━━━━━━━━━━━━━━━━━\n";
        $answer .= 'Ketik *0* atau *menu* untuk kembali.';

        return $answer;
    }

    private function handleFreeAsk(string $phone, array $session): string
    {
        return "💬 *Tanya Bebas*\n\nSilakan ketik pertanyaan Anda. Kami akan berusaha membantu.\n\n📍 Atau langsung hubungi Tata Usaha untuk pertanyaan khusus.\n\nKetik *0* atau *menu* untuk kembali.";
    }

    private function handleHelp(string $phone, array $session): string
    {
        $status = $session['nisn']
            ? "✅ Terverifikasi (NISN: {$session['nisn']})"
            : '❌ Belum verifikasi';

        return "📌 *Panduan Penggunaan*\n\n*Status:* {$status}\n\n*Fitur:*\n1️⃣ Pilih menu 1-10 dengan angka\n2️⃣ Kirim NISN untuk cek tagihan\n3️⃣ Ketik *0* untuk kembali ke menu\n4️⃣ Ketik *bantuan* untuk panduan\n\nKetik *menu* untuk daftar pilihan.";
    }

    private function handleThanks(string $phone, array $session): string
    {
        return "Sama-sama! 😊 Senang bisa membantu.\n\nKetik *0* atau *menu* jika masih ada yang bisa dibantu.";
    }

    private function handleGoodbye(string $phone, array $session): string
    {
        return "Baik, terima kasih sudah menghubungi kami. 🙏\n\nSampai jumpa kembali!\n\n_🏫 SD Muhammadiyah 3 Samarinda_";
    }

    private function handleUnknownVerified(string $phone, array $session): string
    {
        return "Maaf, perintah tidak dikenali. 🙏\n\nKetik:\n• *cek* = lihat tagihan\n• *menu* = daftar pilihan\n• *bantuan* = panduan";
    }

    private function handleUnknownNew(string $phone, string $profileName, array $session): string
    {
        $this->updateSession($phone, ['state' => 'menu']);

        return $this->buildWelcomeMessage($profileName)."\n\n".$this->buildMainMenu();
    }

    // =========================================================================
    // BUILDERS
    // =========================================================================

    private function buildWelcomeMessage(string $profileName): string
    {
        $greeting = $this->getTimeGreeting();

        return "{$greeting}, *{$profileName}* 👋\n\nSelamat datang di layanan\n🏫 *SD Muhammadiyah 3 Samarinda*";
    }

    private function buildMainMenu(): string
    {
        $menu = "Silakan pilih layanan _(ketik angka)_\n━━━━━━━━━━━━━━━\n\n";

        foreach (self::MAIN_MENU as $no => $item) {
            $menu .= "{$item['emoji']} *{$no}.* {$item['label']}\n";
        }

        $menu .= "\n━━━━━━━━━━━━━━━\n";
        $menu .= "_Atau kirim NISN (10 digit) untuk cek tagihan_\n";
        $menu .= '_Ketik *0* untuk kembali ke menu kapan saja_';

        return $menu;
    }

    // =========================================================================
    // SESSION MANAGEMENT
    // =========================================================================

    private function getSession(string $phone): array
    {
        $key = self::SESSION_PREFIX.$phone;
        $session = Cache::get($key);

        if (! $session) {
            $session = [
                'state' => 'new',
                'nisn' => null,
                'created_at' => now()->timestamp,
            ];
            Cache::put($key, $session, self::SESSION_TTL);
        }

        return $session;
    }

    private function updateSession(string $phone, array $data): void
    {
        $key = self::SESSION_PREFIX.$phone;
        $session = $this->getSession($phone);
        $session = array_merge($session, $data);
        Cache::put($key, $session, self::SESSION_TTL);
    }

    public function resetSession(string $phone): void
    {
        Cache::forget(self::SESSION_PREFIX.$phone);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function maskPhone(string $phone): string
    {
        if (strlen($phone) > 8) {
            return substr($phone, 0, 4).'****'.substr($phone, -4);
        }

        return '****';
    }

    private function getTimeGreeting(): string
    {
        $hour = (int) now('Asia/Makassar')->format('H');

        return match (true) {
            $hour >= 4 && $hour < 12 => 'Selamat pagi',
            $hour >= 12 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 19 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
